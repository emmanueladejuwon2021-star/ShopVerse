<?php
/**
 * Storage abstraction — users / orders / accounts.
 *
 * Backend selection (in order):
 *   1. TURSO_DB_URL + TURSO_AUTH_TOKEN set  → Turso cloud (libsql HTTP API)
 *   2. otherwise                            → local SQLite file via PDO
 *      (Vercel's filesystem is read-only/ephemeral, so production MUST use
 *       Turso; the SQLite fallback keeps zero-config local development easy.)
 *
 * The previous JSON-file storage was a deployment blocker on Vercel and had
 * no referential integrity — flagged in the audit and fixed here.
 */

declare(strict_types=1);

require_once __DIR__ . '/http.php';

/**
 * Storage capability probe — evaluated once per request.
 *
 * Priority: Turso (env creds present + network layer available)
 *           → local SQLite via PDO (pdo_sqlite loaded)
 *           → JSON-file fallback (read-only-safe serverless filesystems
 *             such as Vercel simply fail writes with a flash error instead
 *             of throwing "no such driver").
 */
function db_backend(): string
{
    static $backend = null;
    if ($backend !== null) return $backend;

    $hasTursoCreds = getenv('TURSO_DB_URL')
        && (getenv('TURSO_AUTH_TOKEN') || getenv('TURSO_DATABASE_TOKEN'));

    if ($hasTursoCreds) {
        $backend = 'turso';
    } elseif (extension_loaded('pdo_sqlite')) {
        $backend = 'sqlite';
    } else {
        $backend = 'json';
    }
    return $backend;
}

/** Whether the current backend supports durable writes on this host. */
function db_writable(): bool
{
    return db_backend() !== 'json' || is_dir(STORAGE_DIR) && is_writable(STORAGE_DIR);
}

/* ------------------------------------------------------------------ */
/* Local SQLite (PDO) — dev fallback                                   */
/* ------------------------------------------------------------------ */

function sqlite_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $dir = __DIR__ . '/../storage';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $pdo = new PDO('sqlite:' . $dir . '/shopverse.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode = WAL;');
    foreach (db_schema() as $sql) $pdo->exec($sql);
    return $pdo;
}

/* ------------------------------------------------------------------ */
/* Schema                                                              */
/* ------------------------------------------------------------------ */

/** @return string[] portable DDL statements */
function db_schema(): array
{
    return [
        "CREATE TABLE IF NOT EXISTS users (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            passhash TEXT NOT NULL,
            joined_at TEXT NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS orders (
            id TEXT PRIMARY KEY,
            user_id TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            total REAL NOT NULL,
            shipping_address TEXT NOT NULL,
            items TEXT NOT NULL,
            created_at TEXT NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_orders_user ON orders(user_id)",
        "CREATE TABLE IF NOT EXISTS accounts (
            user_id TEXT PRIMARY KEY,
            phone TEXT NOT NULL DEFAULT '',
            bio TEXT NOT NULL DEFAULT '',
            addresses TEXT NOT NULL DEFAULT '[]',
            cards TEXT NOT NULL DEFAULT '[]',
            notifications TEXT NOT NULL DEFAULT '{}'
        )",
        "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            email TEXT PRIMARY KEY,
            created_at TEXT NOT NULL
        )",
    ];
}

/** One-time bootstrap used by scripts/init-db.php (Turso path). */
function db_migrate(): void
{
    foreach (db_schema() as $sql) {
        turso_exec($sql);
    }
}

/* ------------------------------------------------------------------ */
/* JSON-file fallback (last resort — dev hosts without pdo_sqlite)     */
/* ------------------------------------------------------------------ */

/** Read a JSON collection file as an assoc array keyed by id/email. */
function json_read(string $file, string $keyField): array
{
    $path = STORAGE_DIR . '/' . $file;
    if (!is_file($path)) return [];
    $data = json_decode((string) @file_get_contents($path), true);
    if (!is_array($data)) return [];
    $out = [];
    foreach ($data as $row) {
        if (is_array($row) && isset($row[$keyField])) {
            $out[(string) $row[$keyField]] = $row;
        }
    }
    return $out;
}

function json_write(string $file, array $rows): bool
{
    if (!is_dir(STORAGE_DIR)) @mkdir(STORAGE_DIR, 0775, true);
    return @file_put_contents(
        STORAGE_DIR . '/' . $file,
        json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
        LOCK_EX
    ) !== false;
}

/* ------------------------------------------------------------------ */
/* Users                                                               */
/* ------------------------------------------------------------------ */

function db_find_user_by_email(string $email): ?array
{
    if (db_backend() === 'turso') {
        $rows = turso_select('SELECT * FROM users WHERE email = ?', [$email]);
    } elseif (db_backend() === 'sqlite') {
        $st = sqlite_pdo()->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $u = json_read('users.json', 'email')[$email] ?? null;
        return $u;
    }
    return $rows[0] ?? null;
}

function db_insert_user(array $user): void
{
    if (db_backend() === 'json') {
        $users = json_read('users.json', 'email');
        $users[$user['email']] = $user;
        if (!json_write('users.json', $users)) {
            throw new RuntimeException('Storage is not writable.');
        }
        return;
    }
    if (db_backend() === 'turso') {
        turso_exec(
            'INSERT INTO users (id, name, email, passhash, joined_at) VALUES (?, ?, ?, ?, ?)',
            [$user['id'], $user['name'], $user['email'], $user['passhash'], $user['joinedDate']]
        );
    } else {
        $st = sqlite_pdo()->prepare('INSERT INTO users (id, name, email, passhash, joined_at) VALUES (:id, :name, :email, :passhash, :joined_at)');
        $st->execute([
            ':id' => $user['id'], ':name' => $user['name'], ':email' => $user['email'],
            ':passhash' => $user['passhash'], ':joined_at' => $user['joinedDate'],
        ]);
    }
}

/* ------------------------------------------------------------------ */
/* Orders                                                              */
/* ------------------------------------------------------------------ */

/** @return array<int,array<string,mixed>> newest first */
function db_user_orders(string $userId): array
{
    if (db_backend() === 'json') {
        $rows = array_values(array_filter(
            json_read('orders.json', 'id'),
            fn($o) => ($o['userId'] ?? null) === $userId
        ));
        usort($rows, fn($a, $b) => strcmp((string) $b['date'], (string) $a['date']));
        return $rows;
    }
    if (db_backend() === 'turso') {
        $rows = turso_select('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC', [$userId]);
    } else {
        $st = sqlite_pdo()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $st->execute([$userId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    }
    foreach ($rows as &$r) {
        $r['items'] = json_decode((string) $r['items'], true);
        $r['shippingAddress'] = json_decode((string) $r['shipping_address'], true);
        $r['date'] = $r['created_at'];
        $r['userId'] = $r['user_id'];
    }
    return $rows;
}

function db_insert_order(array $order): void
{
    if (db_backend() === 'json') {
        $orders = json_read('orders.json', 'id');
        $orders[$order['id']] = $order;
        if (!json_write('orders.json', $orders)) {
            throw new RuntimeException('Storage is not writable.');
        }
        return;
    }
    if (db_backend() === 'turso') {
        turso_exec(
            'INSERT INTO orders (id, user_id, status, total, shipping_address, items, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $order['id'], $order['userId'] ?? '', $order['status'], $order['total'],
                json_encode($order['shippingAddress']),
                json_encode($order['items']),
                $order['date'],
            ]
        );
    } else {
        $st = sqlite_pdo()->prepare('INSERT INTO orders (id, user_id, status, total, shipping_address, items, created_at) VALUES (:id, :user_id, :status, :total, :shipping_address, :items, :created_at)');
        $st->execute([
            ':id' => $order['id'], ':user_id' => $order['userId'] ?? '', ':status' => $order['status'],
            ':total' => $order['total'],
            ':shipping_address' => json_encode($order['shippingAddress']),
            ':items' => json_encode($order['items']), ':created_at' => $order['date'],
        ]);
    }
}

/* ------------------------------------------------------------------ */
/* Account settings (profile / addresses / cards / notifications)      */
/* ------------------------------------------------------------------ */

function db_get_account(string $userId): array
{
    if (db_backend() === 'json') {
        $row = json_read('accounts.json', 'user_id')[$userId] ?? null;
        return normalise_account($userId, $row);
    }
    if (db_backend() === 'turso') {
        $rows = turso_select('SELECT * FROM accounts WHERE user_id = ?', [$userId]);
    } else {
        $st = sqlite_pdo()->prepare('SELECT * FROM accounts WHERE user_id = ?');
        $st->execute([$userId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    }
    $row = $rows[0] ?? null;
    return normalise_account($userId, $row);
}

function db_save_account(string $userId, array $acct): void
{
    if (db_backend() === 'json') {
        $accounts = json_read('accounts.json', 'user_id');
        $accounts[$userId] = [
            'user_id' => $userId,
            'phone' => (string) ($acct['phone'] ?? ''),
            'bio' => (string) ($acct['bio'] ?? ''),
            'addresses' => $acct['addresses'] ?? [],
            'cards' => $acct['cards'] ?? [],
            'notifications' => $acct['notifications'] ?? [],
        ];
        if (!json_write('accounts.json', $accounts)) {
            throw new RuntimeException('Storage is not writable.');
        }
        return;
    }
    $values = [
        $userId,
        (string) ($acct['phone'] ?? ''),
        (string) ($acct['bio'] ?? ''),
        json_encode($acct['addresses'] ?? []),
        json_encode($acct['cards'] ?? []),
        json_encode($acct['notifications'] ?? []),
    ];
    $sql = 'INSERT INTO accounts (user_id, phone, bio, addresses, cards, notifications) VALUES (?, ?, ?, ?, ?, ?)
             ON CONFLICT(user_id) DO UPDATE SET phone = excluded.phone, bio = excluded.bio,
             addresses = excluded.addresses, cards = excluded.cards, notifications = excluded.notifications';
    if (db_backend() === 'turso') {
        turso_exec($sql, $values);
    } else {
        sqlite_pdo()->prepare($sql)->execute($values);
    }
}

function normalise_account(string $userId, ?array $row): array
{
    $defaults = [
        'phone' => '', 'bio' => '', 'addresses' => [], 'cards' => [],
        'notifications' => array_fill_keys(
            ['orderUpdates', 'promotions', 'newsletter', 'securityAlerts', 'productRecommendations', 'smsNotifications'],
            true
        ),
    ];
    if (!$row) return $defaults + ['user_id' => $userId];
    return [
        'user_id' => $userId,
        'phone' => (string) ($row['phone'] ?? ''),
        'bio' => (string) ($row['bio'] ?? ''),
        'addresses' => json_decode((string) ($row['addresses'] ?? '[]'), true) ?: [],
        'cards' => json_decode((string) ($row['cards'] ?? '[]'), true) ?: [],
        'notifications' => json_decode((string) ($row['notifications'] ?? '{}'), true) ?: $defaults['notifications'],
    ];
}

/* ------------------------------------------------------------------ */
/* Newsletter                                                          */
/* ------------------------------------------------------------------ */

function db_subscribe(string $email): void
{
    if (db_backend() === 'json') {
        $subs = json_read('subscribers.json', 'email');
        $key = strtolower($email);
        if (!isset($subs[$key])) {
            $subs[$key] = ['email' => $key, 'created_at' => date('c')];
            if (!json_write('subscribers.json', $subs)) {
                throw new RuntimeException('Storage is not writable.');
            }
        }
        return;
    }
    $sql = 'INSERT INTO newsletter_subscribers (email, created_at) VALUES (?, ?)
             ON CONFLICT(email) DO NOTHING';
    $params = [strtolower($email), date('c')];
    if (db_backend() === 'turso') {
        turso_exec($sql, $params);
    } else {
        sqlite_pdo()->prepare($sql)->execute($params);
    }
}
