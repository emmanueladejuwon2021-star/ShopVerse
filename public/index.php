<?php
/**
 * ShopVerse — Front Controller (PHP stack)
 * Replaces the React SPA entry (main.tsx + App.tsx + react-router).
 */

declare(strict_types=1);

// Sessions: on serverless hosts (Vercel) the default save path may not be
// writable, which makes session_start() emit a PHP warning — that warning is
// what surfaces as Vercel's "An unexpected internal error occurred". Point
// sessions at /tmp and fail silently instead of crashing.
$sessionPath = sys_get_temp_dir() . '/shopverse-sessions';
if (!is_dir($sessionPath)) @mkdir($sessionPath, 0700, true);
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ---- Security response headers ----
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; upgrade-insecure-requests");

require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/cart.php';

// ---- CSRF protection ----
if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(400);
        exit('Invalid or missing CSRF token. Please go back and try again.');
    }
}

// ---- Route dispatch ----
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path = rtrim($uri, '/') ?: '/';

$routes = [
    '/'            => 'home',
    '/products'    => 'products',
    '/product'     => 'product_detail',
    '/cart'        => 'cart',
    '/checkout'    => 'checkout',
    '/login'       => 'login',
    '/logout'      => 'logout',
    '/orders'      => 'orders',
    '/account'     => 'account',
];

$page = $routes[$path] ?? null;
$params = [];

if ($page === null && preg_match('#^/product/([^/]+)$#', $path, $m)) {
    $page = 'product_detail';
    $params['id'] = $m[1];
}

// Auth guards
$protected = ['checkout', 'orders', 'account'];
if (in_array($page, $protected, true) && !is_logged_in()) {
    $_SESSION['flash_error'] = 'Please sign in to continue.';
    header('Location: /login?redirect=' . urlencode($path));
    exit;
}

// POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === null) $page = 'home';
    handle_post_actions();
}

if ($page === null) {
    http_response_code(404);
    render('404', ['title' => 'Page Not Found']);
    exit;
}

// Cart drawer one-shot logic (unchanged)
$cartOpenOnce = (($_COOKIE['cart_open_once'] ?? '') === '1');
$_justAddedToCart = (($_SESSION['flash'] ?? null) === 'Added to cart.');
$_drawerOpenAllowed = $cartOpenOnce && $_justAddedToCart && $page !== 'cart';

unset($_SESSION['cart_open'], $_SESSION['cart_open_requested']);
if (isset($_COOKIE['cart_open_once'])) {
    $isHttps = !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    setcookie('cart_open_once', '', [
        'path'     => '/',
        'expires'  => time() - 3600,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $isHttps,
    ]);
    unset($_COOKIE['cart_open_once']);
}

render_page($page, $params + ['cart_drawer_open' => $_drawerOpenAllowed]);
