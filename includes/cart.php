<?php
/**
 * Cart — replaces src/context/CartContext.tsx (localStorage → PHP session).
 * Reducer semantics preserved: add increments qty & opens sidebar,
 * update qty<=0 removes item, clear empties items.
 *
 * Audit fixes applied here:
 *  - isOpen is UI-only now; it is NOT persisted inside the cart state.
 *  - addToCart adds quantity in one operation (no per-unit loop).
 *  - Server-side stock check and quantity cap (max 20 per item).
 */

declare(strict_types=1);

const CART_MAX_QTY = 20;

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_add(string $productId, int $qty = 1): void
{
    $product = find_product($productId);
    if (!$product || empty($product['inStock'])) {
        $_SESSION['flash_error'] = 'This product is out of stock.';
        return;
    }

    $qty = max(1, min(CART_MAX_QTY, $qty));

    $cart = cart_items();
    $current = (int) ($cart[$productId]['quantity'] ?? 0);
    $newQty = min(CART_MAX_QTY, $current + $qty);

    if ($newQty <= 0) return;

    if (isset($cart[$productId])) {
        $cart[$productId]['quantity'] = $newQty;
    } else {
        $cart[$productId] = ['product' => $product, 'quantity' => $newQty];
    }
    $_SESSION['cart'] = $cart;

    // UI-only, one-shot drawer-open flag. It lives in a COOKIE that we also
    // mirror into $_COOKIE immediately, because on serverless hosts (Vercel)
    // the "add to cart" POST ends in redirect() → exit, and headers/cookies
    // queued via setcookie() are discarded when the script exits before the
    // response is flushed. The in-request copy guarantees the PRG redirect
    // target renders with the drawer open exactly once; the real cookie is a
    // belt-and-braces fallback for clients that don't follow instantly.
    // NOTE: the array key must be 'expires' (timestamp) — 'maxage' is NOT a
    // valid setcookie() option and threw an uncaught ValueError (HTTP 500) on
    // every add-to-cart.
    $_COOKIE['cart_open_once'] = '1';
    setcookie('cart_open_once', '1', [
        'path'     => '/',
        'expires'  => time() + 30,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
    ]);
}

function cart_remove(string $productId): void
{
    $cart = cart_items();
    unset($cart[$productId]);
    $_SESSION['cart'] = $cart;
}

function cart_update_qty(string $productId, int $quantity): void
{
    $quantity = max(0, min(CART_MAX_QTY, $quantity));
    if ($quantity <= 0) {
        cart_remove($productId);
        return;
    }

    $product = find_product($productId);
    if (!$product || empty($product['inStock'])) {
        cart_remove($productId);
        $_SESSION['flash_error'] = 'This product is no longer available.';
        return;
    }

    $cart = cart_items();
    if (isset($cart[$productId])) {
        $cart[$productId]['quantity'] = $quantity;
        $_SESSION['cart'] = $cart;
    }
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

function cart_total_items(): int
{
    return array_sum(array_column(cart_items(), 'quantity'));
}

function cart_total_price(): float
{
    $sum = 0.0;
    foreach (cart_items() as $item) {
        $sum += $item['product']['price'] * $item['quantity'];
    }
    return $sum;
}

/** Free shipping over $50; otherwise $4.99 flat. Unified boundary (audit fix: was >50 vs >=50). */
function cart_shipping(float $subtotal): float
{
    return $subtotal >= 50 ? 0.0 : 4.99;
}

function cart_tax(float $subtotal): float
{
    return round($subtotal * 0.08, 2); // Checkout used 8% tax
}

/**
 * POST action router — replaces the client-side context mutations.
 * Every mutating form posts an <input name="action"> value.
 */
function handle_post_actions(): void
{
    $action = $_POST['action'] ?? '';
    // Audit fix: never trust HTTP_REFERER as a redirect target; only same-site
    // paths supplied explicitly by our own forms are allowed.
    $redirect = validate_redirect((string) ($_POST['redirect'] ?? '/'));

    switch ($action) {
        case 'add_to_cart':
            cart_add((string) ($_POST['product_id'] ?? ''), (int) ($_POST['quantity'] ?? 1));
            if (!isset($_SESSION['flash_error'])) {
                $_SESSION['flash'] = 'Added to cart.';
            }
            break;

        case 'remove_from_cart':
            cart_remove((string) ($_POST['product_id'] ?? ''));
            break;

        case 'update_quantity':
            cart_update_qty((string) ($_POST['product_id'] ?? ''), (int) ($_POST['quantity'] ?? 0));
            break;

        case 'clear_cart':
            cart_clear();
            break;

        case 'place_order':
            place_order_from_post();
            return; // redirects internally

        case 'newsletter':
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            if ($email) {
                try {
                    db_subscribe((string) $email);
                    $_SESSION['flash'] = 'Thanks for subscribing to the newsletter!';
                } catch (Throwable) {
                    $_SESSION['flash_error'] = 'Could not save your subscription right now. Please try again.';
                }
            } else {
                $_SESSION['flash_error'] = 'Please enter a valid email address.';
            }
            redirect($redirect);
            return;

        case 'login':
            $redirectTo = validate_redirect((string) ($_POST['redirect'] ?? '/'));
            $res = login_user((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));
            if ($res['ok']) {
                $_SESSION['flash'] = 'Welcome back, ' . $res['user']['name'] . '!';
                redirect($redirectTo);
            }
            $_SESSION['flash_error'] = 'Invalid email or password.';
            redirect(login_url($redirectTo));
            return;

        case 'register':
            $redirectTo = validate_redirect((string) ($_POST['redirect'] ?? '/'));
            $res = register_user(
                (string) ($_POST['name'] ?? ''),
                (string) ($_POST['email'] ?? ''),
                (string) ($_POST['password'] ?? '')
            );
            if ($res['ok']) {
                $_SESSION['flash'] = 'Account created. Welcome to ShopVerse!';
                redirect($redirectTo);
            }
            $errors = in_array('Storage is unavailable right now. Please try again later.', $res['errors'], true)
                ? $res['errors']
                : array_map(static fn($e) => strtr($e, ['already exists' => 'could not be used']), $res['errors']);
            $_SESSION['flash_error'] = implode(' ', $errors);
            redirect(login_url($redirectTo, 'register'));
            return;

        case 'logout':
            logout_user();
            $_SESSION['flash'] = 'You have been signed out.';
            redirect('/');
            return;
    }

    if ($action === 'add_to_cart') {
        redirect($redirect);
    }

    redirect($redirect);
}

function place_order_from_post(): void
{
    $items = cart_items();
    if (!$items) {
        $_SESSION['flash_error'] = 'Your cart is empty.';
        redirect('/cart');
    }

    // Validate shipping fields
    $required = ['fullName', 'street', 'city', 'state', 'zipCode', 'country'];
    foreach ($required as $f) {
        if (trim((string) ($_POST[$f] ?? '')) === '') {
            $_SESSION['flash_error'] = 'Please fill in all shipping fields.';
            redirect('/checkout');
        }
    }

    // Basic demo payment validation (card data is never stored or charged)
    $card = preg_replace('/\D/', '', (string) ($_POST['cardNumber'] ?? ''));
    $expiry = trim((string) ($_POST['expiry'] ?? ''));
    $cvc = trim((string) ($_POST['cvc'] ?? ''));
    if (strlen($card) < 13 || strlen($card) > 19 || !preg_match('/^\d{2}\/\d{2}$/', $expiry) || strlen($cvc) < 3 || strlen($cvc) > 4) {
        $_SESSION['flash_error'] = 'Please enter valid payment details (card number, expiry MM/YY, and CVC).';
        redirect('/checkout');
    }

    $subtotal = cart_total_price();
    $total = $subtotal + cart_shipping($subtotal) + cart_tax($subtotal);

    $order = save_order(array_values($items), $total, [
        'fullName' => trim((string) $_POST['fullName']),
        'street' => trim((string) $_POST['street']),
        'city' => trim((string) $_POST['city']),
        'state' => trim((string) $_POST['state']),
        'zipCode' => trim((string) $_POST['zipCode']),
        'country' => trim((string) $_POST['country']),
    ]);

    if (!$order['ok']) {
        $_SESSION['flash_error'] = implode(' ', $order['errors']);
        redirect('/checkout');
    }
    $order = $order['order'];

    cart_clear();
    $_SESSION['last_order_id'] = $order['id'];
    $_SESSION['flash'] = 'Order placed successfully! Confirmation: ' . $order['id'];
    redirect('/orders');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}
