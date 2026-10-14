<?php
/**
 * ShopVerse — Front Controller & Vercel Serverless Function entry point.
 *
 * Handles routing, session initialization, security headers, Paystack webhook/callback,
 * and dispatching to view templates and API endpoints.
 */

declare(strict_types=1);

// Normalize REQUEST_URI for Vercel Serverless environment:
// When Vercel rewrites requests to /api/index.php, HTTP_X_MATCHED_PATH contains the original requested path.
if (!empty($_SERVER['HTTP_X_MATCHED_PATH'])) {
    $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_MATCHED_PATH'] . $queryString;
}

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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");

require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/cart.php';
require __DIR__ . '/../includes/payments.php';

// ---- Route dispatch ----
$rawUri = $_SERVER['HTTP_X_MATCHED_PATH'] ?? $_SERVER['REQUEST_URI'] ?? '/';
$uri = rawurldecode(parse_url($rawUri, PHP_URL_PATH) ?: '/');
if ($uri === '/api/index.php' || $uri === '/api/index' || $uri === '/index.php') {
    $uri = '/';
}
$path = rtrim($uri, '/') ?: '/';

// ---- Paystack webhook receiver (POST /payments/webhook) ----
// Signature-verified, idempotent event processor. Authenticity comes from the
// HMAC-SHA512 x-paystack-signature header (not session CSRF), so this runs
// before the CSRF gate below.
if ($path === '/payments/webhook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_paystack_webhook(); // exits
}

function handle_paystack_webhook(): never
{
    header('Content-Type: application/json');
    $raw = file_get_contents('php://input') ?: '';
    $signature = (string) ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '');

    if (!paystack_is_configured() || !paystack_verify_webhook_signature($raw, $signature)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'invalid signature']);
        exit;
    }

    $event = json_decode($raw, true);
    if (!is_array($event)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'malformed payload']);
        exit;
    }

    // Completion itself is idempotent (paymentStatus guard), so duplicate
    // deliveries are safe; we still log the event id for replay auditing.
    $eventId = (string) ($event['id'] ?? '');
    $result = paystack_handle_webhook($event, $eventId ?: null);
    error_log('ShopVerse Paystack webhook: ' . json_encode([
        'event' => $event['event'] ?? '?', 'id' => $eventId, 'result' => $result,
    ]));
    echo json_encode(['ok' => true] + $result);
    exit;
}

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

// ---- API Endpoints ----
if ($path === '/api/search') {
    header('Content-Type: application/json');
    $q = trim((string) ($_GET['q'] ?? ''));
    if ($q === '') {
        echo json_encode([]);
        exit;
    }
    $results = array_slice(filter_products(['search' => $q]), 0, 6);
    $out = array_map(function ($p) {
        return [
            'id' => $p['id'],
            'name' => $p['name'],
            'brand' => $p['brand'] ?? '',
            'price' => money($p['price']),
            'raw_price' => $p['price'],
            'image' => $p['image'],
            'category' => $p['category'],
            'url' => '/product/' . $p['id'],
            'badge' => $p['badge'] ?? null,
        ];
    }, $results);
    echo json_encode($out);
    exit;
}

if ($path === '/api/cart') {
    header('Content-Type: application/json');
    ob_start();
    include __DIR__ . '/../views/partials/cart-sidebar.php';
    $sidebarHtml = ob_get_clean();
    echo json_encode([
        'ok' => true,
        'cartCount' => cart_total_items(),
        'cartSubtotal' => money(cart_total_price()),
        'sidebarHtml' => $sidebarHtml,
    ]);
    exit;
}

$routes = [
    '/'             => 'home',
    '/products'     => 'products',
    '/product'      => 'product_detail',   // /product/{id}
    '/cart'         => 'cart',
    '/wishlist'     => 'wishlist',
    '/track'        => 'track',
    '/checkout'     => 'checkout',         // requires login
    '/order-success'=> 'order_success',    // requires login
    '/payment/callback' => 'payment_return', // Paystack redirect target (requires login)
    '/login'        => 'login',
    '/logout'       => 'logout',
    '/orders'       => 'orders',           // requires login
    '/account'      => 'account',          // requires login
];

$page = $routes[$path] ?? null;
$params = [];

if ($page === null && preg_match('#^/product/([^/]+)$#', $path, $m)) {
    $page = 'product_detail';
    $params['id'] = $m[1];
}

// Auth guards (mirror the ProtectedRoute behaviour of Checkout/Orders/Account).
// The Paystack callback is handled specially below (it verifies payments even
// for expired sessions), so it is exempt here.
$protected = ['checkout', 'orders', 'account'];
if (in_array($page, $protected, true) && !is_logged_in()) {
    $_SESSION['flash_error'] = 'Please sign in to continue.';
    header('Location: /login?redirect=' . urlencode($path));
    exit;
}

// ---- Paystack return handler (GET /payment/callback?reference=SV-...) ----
// The customer lands back here from checkout.paystack.co. We NEVER trust the
// redirect alone: the reference is re-verified against the Paystack API
// server-side before the order is marked paid. Idempotent on refresh.
if ($page === 'payment_return') {
    $reference = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) ($_GET['reference'] ?? ''));

    if ($reference === '') {
        $_SESSION['flash_error'] = 'Payment could not be completed: missing transaction reference.';
        redirect(is_logged_in() ? '/checkout' : '/cart');
    }

    $verify = paystack_verify_transaction((string) $reference);
    $order  = find_order_by_reference((string) $reference);

    if (!$order) {
        http_response_code(404);
        render('pages/payment_return', [
            'title' => 'Payment — ShopVerse',
            'state' => 'unknown',
            'reference' => $reference,
            'auto_open_cart' => false,
        ]);
        exit;
    }

    // Reconcile the payment regardless of session state — money is money, and
    // the webhook may not have arrived yet. This is idempotent.
    if ($verify['ok'] && $verify['paid']) {
        paystack_complete_payment((string) $reference, is_array($verify['data']) ? $verify['data'] : []);
        unset($_SESSION['paystack_return_order']);
    }

    $fresh = find_order_by_reference((string) $reference) ?? $order;
    $isPaid = (($fresh['shippingAddress']['paymentStatus'] ?? '') === 'paid');

    // Success → receipt page (protected; login first if the session expired).
    if ($isPaid) {
        if (!is_logged_in()) {
            $_SESSION['flash'] = 'Payment confirmed! Sign in to view your order and receipt.';
            header('Location: /login?redirect=' . urlencode('/order-success?id=' . rawurlencode((string) $fresh['id'])));
            exit;
        }
        $_SESSION['last_order'] = array_merge($_SESSION['last_order'] ?? [], $fresh);
        redirect('/order-success?id=' . urlencode((string) $fresh['id']));
    }

    // Not paid yet: either failed/abandoned, or still pending verification
    // (webhook may finalise shortly). Keep the cart intact so we can retry.
    $status = is_array($verify['data']) ? (string) ($verify['data']['status'] ?? 'failed') : 'pending';
    if ($status === 'failed' || $status === 'error') {
        paystack_mark_status((string) $order['id'], 'payment_failed');
    }
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Your payment is ' . ($status === 'pending' ? 'still being confirmed' : 'not completed') . '. Sign in to review it.';
        header('Location: /login?redirect=' . urlencode('/payment/callback?reference=' . rawurlencode($reference)));
        exit;
    }
    render('layout', [
        'page' => 'payment_return',
        'params' => [
            'state' => $status === 'success' ? 'processing' : ($status === 'pending' ? 'pending' : 'failed'),
            'reference' => $reference,
            'order' => $order,
            'auto_open_cart' => false,
        ],
        'title' => 'Payment Status — ShopVerse',
    ]);
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

// UI-only: sidebar auto-open flag is consumed by the layout on this response.
$autoOpenCart = !empty($_SESSION['cart_open']) && $page !== 'cart' && $page !== 'checkout';
unset($_SESSION['cart_open']);
render_page($page, $params + ['auto_open_cart' => $autoOpenCart]);
