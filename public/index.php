<?php
/**
 * ShopVerse — Front Controller (PHP stack)
 * Replaces the React SPA entry (main.tsx + App.tsx + react-router).
 */

declare(strict_types=1);
session_start();

require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/cart.php';

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

// POST actions (server-side replacements for the React context mutations)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_post_actions();
}

if ($page === null) {
    http_response_code(404);
    render('404', ['title' => 'Page Not Found']);
    exit;
}

// UI-only: sidebar auto-open flag is consumed by the layout on this response.
unset($_SESSION['cart_open']);
render_page($page, $params);
