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
// Audit pass 1: hardened cookie (renamed away from PHPSESSID, HttpOnly,
// SameSite=Lax, Secure on HTTPS) — configured in includes/auth.php.
require __DIR__ . '/../includes/auth.php';
configure_session_cookie();
session_start();

// ---- Security response headers (audit fix) ----
$isHttps = !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()'); // audit pass 1
if ($isHttps) {
    // Audit pass 3: HSTS only on HTTPS so local http dev is unaffected.
    header('Strict-Transport-Security: max-age=15552000; includeSubDomains');
}
// Audit pass 1: dropped blanket 'unsafe-inline' for scripts. The theme
// bootstrap moved to an external file with a fixed hash; the remaining
// layout script ships its own per-request hash below.
$cspBootstrap = "script-src 'sha256-" . base64_encode(
    hash('sha256', (string) file_get_contents(__DIR__ . '/assets/theme-bootstrap.js'), true)
) . "'";
define('SHOPVERSE_CSP_SCRIPT_SRC', $cspBootstrap);
header("Content-Security-Policy: default-src 'self'; {$cspBootstrap}; style-src 'self' 'unsafe-inline'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'");

require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/cart.php';

// ---- CSRF protection (audit fix) ----
if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        // Audit pass 1: reject with no body + a no-store directive so a stale
        // cached token can never be replayed after a re-deploy.
        header('Cache-Control: no-store');
        http_response_code(400);
        exit;
    }
    // Audit pass 2: one-time tokens. Rotate immediately after validation so
    // a captured token cannot be reused (double-submit/replay). Forms that
    // are submitted twice in a row re-read the fresh token from the session.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---- Route dispatch (mirrors the routes in src/App.tsx) ----
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
// Audit pass 1: path-traversal hardening. Reject any decoded segment that is
// '.' / '..' or contains NUL before it can reach render() ("pages/" . $page).
if (str_contains($uri, "\0")) { http_response_code(400); exit; }
foreach (explode('/', $uri) as $seg) {
    if ($seg === '.' || $seg === '..') { http_response_code(400); exit; }
}
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

// Audit pass 1: session idle timeout — anonymous sessions live 7 days at most,
// logged-in sessions expire after 2 hours of inactivity.
$idleLimit = is_logged_in() ? 2 * 3600 : 7 * 24 * 3600;
$last = (int) ($_SESSION['last_seen'] ?? 0);
if ($last && time() - $last > $idleLimit) {
    $_SESSION = [];
    session_regenerate_id(true);
    if (isset($_COOKIE['cart_open_once'])) unset($_COOKIE['cart_open_once']); // don't auto-open after expiry
}
$_SESSION['last_seen'] = time();

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
$_drawerOpenAllowed = $cartOpenOnce && $_justAddedToCart && $page !== 'cart' && $page !== 'home';

// Purge legacy state from earlier buggy deploys so it can never resurface.
unset($_SESSION['cart_open'], $_SESSION['cart_open_requested']);
if (isset($_COOKIE['cart_open_once'])) {
    setcookie('cart_open_once', '', [
        'path'     => '/',
        'expires'  => time() - 3600,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $isHttps,
    ]);
    unset($_COOKIE['cart_open_once']);
}

// Audit pass 1: storefront data is per-user (cart/session), so nothing here
// may be cached by shared caches; prevents stale CSRF tokens & drawer flags.
header('Cache-Control: no-store');

render_page($page, $params + ['cart_drawer_open' => $_drawerOpenAllowed]);
