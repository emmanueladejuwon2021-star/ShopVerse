<?php
/**
 * Auth — replaces src/context/AuthContext.tsx.
 *
 * Audit note: the React version accepted ANY credentials with no validation.
 * This PHP port does real server-side validation + password hashing and
 * stores orders per user (fixing the "orders not scoped per user" finding).
 */

declare(strict_types=1);

if (!defined('STORAGE_DIR')) {
    define('STORAGE_DIR', __DIR__ . '/../storage');
}
if (!is_dir(STORAGE_DIR)) {
    @mkdir(STORAGE_DIR, 0775, true);
}

const USERS_FILE = STORAGE_DIR . '/users.json';
const ORDERS_FILE = STORAGE_DIR . '/orders.json';

function storage_read(string $file): array
{
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function storage_write(string $file, array $data): void
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']['id']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function register_user(string $name, string $email, string $password): array
{
    $errors = [];
    if (trim($name) === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    $users = storage_read(USERS_FILE);
    foreach ($users as $u) {
        if (strcasecmp($u['email'], $email) === 0) {
            $errors[] = 'An account with this email already exists.';
        }
    }
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $user = [
        'id' => bin2hex(random_bytes(8)),
        'name' => trim($name),
        'email' => strtolower(trim($email)),
        'passhash' => password_hash($password, PASSWORD_DEFAULT),
        'joinedDate' => date('c'),
    ];
    $users[] = $user;
    storage_write(USERS_FILE, $users);

    unset($user['passhash']);
    $_SESSION['user'] = $user;
    return ['ok' => true, 'user' => $user];
}

function login_user(string $email, string $password): array
{
    $users = storage_read(USERS_FILE);
    foreach ($users as $u) {
        if (strcasecmp($u['email'], $email) === 0 && password_verify($password, $u['passhash'])) {
            unset($u['passhash']);
            $_SESSION['user'] = $u;
            return ['ok' => true, 'user' => $u];
        }
    }
    return ['ok' => false, 'errors' => ['Invalid email or password.']];
}

function logout_user(): void
{
    unset($_SESSION['user']);
}

/** @return array<int,array<string,mixed>> orders belonging to the logged-in user */
function user_orders(): array
{
    $uid = current_user()['id'] ?? null;
    if (!$uid) return [];
    $orders = storage_read(ORDERS_FILE);
    return array_values(array_filter($orders, fn($o) => ($o['userId'] ?? null) === $uid));
}

function save_order(array $items, float $total, array $shippingAddress, string $status = 'pending'): array
{
    $orders = storage_read(ORDERS_FILE);
    $order = [
        'id' => 'SV-' . strtoupper(bin2hex(random_bytes(3))),
        'userId' => current_user()['id'] ?? null,
        'items' => $items,
        'total' => $total,
        'status' => $status,
        'date' => date('c'),
        'shippingAddress' => $shippingAddress,
    ];
    $orders[] = $order;
    storage_write(ORDERS_FILE, $orders);
    return $order;
}
