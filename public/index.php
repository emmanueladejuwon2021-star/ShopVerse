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

// ---- Security response headers (audit fix) ----
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");

require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/cart.php';

// ---- CSRF protection (audit fix) ----
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

// ---- Route dispatch (mirrors the routes in src/App.tsx) ----
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path = rtrim($uri, '/') ?: '/';

$routes = [
    '/'            => 'home',
    '/products'    => 'products',
    '/product'     => 'product_detail',   // /product/{id}
    '/cart'        => 'cart',
    '/checkout'    => 'checkout',         // requires login
    '/login'       => 'login',
    '/logout'      => 'logout',
    '/orders'      => 'orders',           // requires login
    '/account'     => 'account',          // requires login
];

$page = $routes[$path] ?? null;
$params = [];

if ($page === null && preg_match('#^/product/([^/]+)$#', $path, $m)) {
    $page = 'product_detail';
    $params['id'] = $m[1];
}

// Auth guards (mirror the ProtectedRoute behaviour of Checkout/Orders/Account)
$protected = ['checkout', 'orders', 'account'];
if (in_array($page, $protected, true) && !is_logged_in()) {
    $_SESSION['flash_error'] = 'Please sign in to continue.';
    header('Location: /login?redirect=' . urlencode($path));
    exit;
}

// POST actions (server-side replacements for the React context mutations).
// Unknown actions redirect to a safe same-site target instead of falling
// through, so a POST can never render an unrelated page or leak form data.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === null) $page = 'home'; // unknown route → send back somewhere valid
    handle_post_actions();
}

if ($page === null) {
    http_response_code(404);
    render('404', ['title' => 'Page Not Found']);
    exit;
}

// UI-only: one-shot flag set by cart_add() via a short-lived cookie. It is
// consumed (deleted) below on this request — never stored in $_SESSION,
// because PHP's shutdown session write-back resurrected the old
// $_SESSION['cart_open'] key and left the drawer stuck open over every page.
$cartOpenOnce = (($_COOKIE['cart_open_once'] ?? '') === '1');

// Cart drawer policy for THIS response — decided server-side, per request.
//
// The drawer can ONLY ever be rendered open when ALL of these hold:
//   1. This was a GET (never on POST/redirect responses),
//   2. The user just added an item ("Added to cart." flash, consumed below),
//   3. The one-shot cookie set by cart_add() is present (double safety net).
// Anything else — stale cookies, resurrected session keys from old deploys,
// browser back/forward cache restores, direct visits to "/" — renders CLOSED.
$_justAddedToCart = (($_SESSION['flash'] ?? null) === 'Added to cart.');
$_drawerOpenAllowed = $cartOpenOnce && $_justAddedToCart && $page !== 'cart';

// Purge legacy state from earlier buggy deploys so it can never resurface.
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
