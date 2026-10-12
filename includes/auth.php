<?php
/**
 * Auth — replaces src/context/AuthContext.tsx.
 *
 * Audit notes:
 *  - The React version accepted ANY credentials; this does real validation +
 *    password hashing (bcrypt via password_hash).
 *  - Orders are scoped per user.
 *  - Storage now goes through includes/db.php (Turso cloud in production,
 *    local SQLite for development) instead of writable JSON files, which
 *    cannot work on Vercel's read-only serverless filesystem.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (!defined('STORAGE_DIR')) {
    define('STORAGE_DIR', __DIR__ . '/../storage');
}

/** Wrap a DB write so a broken/unwritable backend degrades gracefully. */
function guard_write(callable $fn): array
{
    try {
        return $fn();
    } catch (Throwable $e) {
        error_log('ShopVerse storage error: ' . $e->getMessage());
        return ['ok' => false, 'errors' => ['Storage is unavailable right now. Please try again later.']];
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']['id']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/** Wrap a DB read so a broken backend returns a safe default. */
function guard_read(callable $fn): array
{
    try {
        return ['ok' => true, 'result' => $fn()];
    } catch (Throwable $e) {
        error_log('ShopVerse storage error: ' . $e->getMessage());
        return ['ok' => false, 'errors' => ['Storage is unavailable right now. Please try again later.']];
    }
}

function register_user(string $name, string $email, string $password): array
{
    $errors = [];
    if (trim($name) === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    $email = strtolower(trim($email));
    if ($errors === []) {
        $lookup = guard_read(fn() => db_find_user_by_email($email));
        if (!$lookup['ok']) {
            return ['ok' => false, 'errors' => $lookup['errors']];
        }
        if ($lookup['result'] !== null) {
            $errors[] = 'An account with this email already exists.';
        }
    }
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $user = [
        'id' => bin2hex(random_bytes(8)),
        'name' => trim($name),
        'email' => $email,
        'passhash' => password_hash($password, PASSWORD_DEFAULT),
        'joinedDate' => date('c'),
    ];
    $insert = guard_write(function () use ($user) {
        db_insert_user($user);
        return ['ok' => true];
    });
    if (!$insert['ok']) {
        return $insert;
    }

    unset($user['passhash']);
    session_regenerate_id(true); // audit fix: prevent session fixation on login
    $_SESSION['user'] = $user;
    return ['ok' => true, 'user' => $user];
}

/**
 * Brute-force throttle (audit fix): max 8 failed attempts per email+IP per
 * 10 minutes. State lives in the session, which is server-side — good enough
 * for this app's scale without needing an external rate limiter.
 */
function login_throttled(string $email): bool
{
    $key = 'login_fail_' . md5(strtolower($email) . '|' . ($_SERVER['REMOTE_ADDR'] ?? ''));
    $now = time();
    $rec = $_SESSION[$key] ?? ['count' => 0, 'first' => $now];
    if ($now - (int) $rec['first'] > 600) {
        $rec = ['count' => 0, 'first' => $now]; // window expired
    }
    $_SESSION[$key] = $rec;
    return $rec['count'] >= 8;
}

function login_fail_record(string $email): void
{
    $key = 'login_fail_' . md5(strtolower($email) . '|' . ($_SERVER['REMOTE_ADDR'] ?? ''));
    $now = time();
    $rec = $_SESSION[$key] ?? ['count' => 0, 'first' => $now];
    if ($now - (int) $rec['first'] > 600) {
        $rec = ['count' => 0, 'first' => $now];
    }
    $rec['count']++;
    $_SESSION[$key] = $rec;
}

function login_user(string $email, string $password): array
{
    if (login_throttled($email)) {
        return ['ok' => false, 'errors' => ['Too many sign-in attempts. Please try again in a few minutes.']];
    }
    $lookup = guard_read(fn() => db_find_user_by_email(strtolower(trim($email))));
    if (!$lookup['ok']) {
        return ['ok' => false, 'errors' => $lookup['errors']];
    }
    $u = $lookup['result'];
    if ($u && password_verify($password, (string) $u['passhash'])) {
        unset($_SESSION['login_fail_' . md5(strtolower($email) . '|' . ($_SERVER['REMOTE_ADDR'] ?? ''))]);
        $user = [
            'id' => $u['id'],
            'name' => $u['name'],
            'email' => $u['email'],
            'joinedDate' => $u['joined_at'] ?? date('c'),
        ];
        session_regenerate_id(true); // audit fix: prevent session fixation on login
        $_SESSION['user'] = $user;
        return ['ok' => true, 'user' => $user];
    }
    login_fail_record($email);
    return ['ok' => false, 'errors' => ['Invalid email or password.']];
}

function logout_user(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

/** @return array<int,array<string,mixed>> orders belonging to the logged-in user */
function user_orders(): array
{
    $uid = current_user()['id'] ?? null;
    if (!$uid) return [];
    $res = guard_read(fn() => db_user_orders((string) $uid));
    return $res['ok'] ? $res['result'] : [];
}

/** @return array{ok:bool,...} ok=false carries a user-safe storage error. */
function save_order(array $items, float $total, array $shippingAddress, string $status = 'pending'): array
{
    $order = [
        'id' => 'SV-' . strtoupper(bin2hex(random_bytes(3))),
        'userId' => current_user()['id'] ?? null,
        'items' => $items,
        'total' => $total,
        'status' => $status,
        'date' => date('c'),
        'shippingAddress' => $shippingAddress,
    ];
    $res = guard_write(function () use ($order) {
        db_insert_order($order);
        return ['ok' => true, 'order' => $order];
    });
    return $res['ok'] ? $res : ['ok' => false, 'errors' => $res['errors']];
}
