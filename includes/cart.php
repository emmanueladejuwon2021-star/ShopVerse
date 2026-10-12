<?php
/**
 * Cart — replaces src/context/CartContext.tsx (localStorage → PHP session).
 * Reducer semantics preserved: add increments qty & opens sidebar,
 * update qty<=0 removes item, clear empties items.
 *
 * Audit fixes applied here:
 *  - isOpen is UI-only now; it is NOT persisted inside the cart state.
 *  - addToCart adds quantity in one operation (no per-unit loop).
 */

declare(strict_types=1);

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_add(string $productId, int $qty = 1): void
{
    $product = find_product($productId);
    if (!$product) return;

    $cart = cart_items();
    if (isset($cart[$productId])) {
        $cart[$productId]['quantity'] += max(1, $qty);
    } else {
        $cart[$productId] = ['product' => $product, 'quantity' => max(1, $qty)];
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
    if ($quantity <= 0) {
        cart_remove($productId);
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
    // Audit pass 2: global write throttle — max 30 POST actions per minute
    // per session, shared across all actions (cart spam, order flooding…).
    $gw = $_SESSION['write_throttle'] ?? ['count' => 0, 'first' => time()];
    if (time() - (int) $gw['first'] > 60) $gw = ['count' => 0, 'first' => time()];
    if ($gw['count'] >= 30) {
        $_SESSION['flash_error'] = 'Too many requests. Please slow down and try again shortly.';
        redirect(validate_redirect((string) ($_POST['redirect'] ?? '/')));
    }
    $gw['count']++;
    $_SESSION['write_throttle'] = $gw;

    $action = $_POST['action'] ?? '';
    // Audit fix: never trust HTTP_REFERER as a redirect target; only same-site
    // paths supplied explicitly by our own forms are allowed.
    $redirect = validate_redirect((string) ($_POST['redirect'] ?? '/'));

    switch ($action) {
        case 'add_to_cart':
            // Audit pass 2: never trust the client's quantity — clamp to a
            // sane per-item maximum so nobody can order 99999 units or bloat
            // session storage with huge numbers.
            cart_add((string) ($_POST['product_id'] ?? ''), min(99, max(1, (int) ($_POST['quantity'] ?? 1))));
            $_SESSION['flash'] = 'Added to cart.';
            // Post/Redirect/Get: the drawer-open flag is consumed by the GET
            // response for the redirect target (see public/index.php), which
            // keeps this PRG pattern intact.
            break;

        case 'remove_from_cart':
            cart_remove((string) ($_POST['product_id'] ?? ''));
            break;

        case 'update_quantity':
            // Audit pass 2: same clamp; qty<=0 still removes the item.
            cart_update_qty((string) ($_POST['product_id'] ?? ''), min(99, (int) ($_POST['quantity'] ?? 0)));
            break;

        case 'clear_cart':
            cart_clear();
            break;

        case 'place_order':
            place_order_from_post();
            return; // redirects internally

        case 'newsletter':
            // Audit fix: subscribers are now persisted (previously the email was
            // validated and then thrown away).
            // Audit pass 2: throttle subscription attempts. State lives in the
            // session (server-side) because each serverless request is a fresh
            // process — a function-local static would reset every invocation.
            $nl = $_SESSION['nl_throttle'] ?? ['count' => 0, 'first' => time()];
            if (time() - (int) $nl['first'] > 60) $nl = ['count' => 0, 'first' => time()];
            if ($nl['count'] >= 5) {
                $_SESSION['flash_error'] = 'Too many requests. Please try again in a minute.';
                redirect($redirect);
            }
            $nl['count']++;
            $_SESSION['nl_throttle'] = $nl;
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
            // Audit fix: preserve the intended redirect target and mode on failure
            // (the old code dropped ?mode=register and re-posted users into a loop).
            $redirectTo = validate_redirect((string) ($_POST['redirect'] ?? '/'));
            $res = login_user((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));
            if ($res['ok']) {
                $_SESSION['flash'] = 'Welcome back, ' . $res['user']['name'] . '!';
                redirect($redirectTo);
            }
            // Audit fix (security): generic error only — never reveal whether the
            // email exists. Login throttling lives in auth.php (per-email + per-IP).
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
                // Audit pass 2: "user" is null for the silent-duplicate case
                // (email already registered — we pretend success to prevent
                // user enumeration but must NOT log anybody in or flash a name).
                if (!empty($res['user'])) {
                    $_SESSION['flash'] = 'Account created. Welcome to ShopVerse!';
                } else {
                    $_SESSION['flash'] = 'If that address is new, your account has been created. Sign in to continue.';
                }
                redirect($redirectTo);
            }
            // Audit fix (security): storage failures must not masquerade as
            // validation errors; show a safe retry message instead.
            // Audit pass 2: registration errors are surfaced generically too —
            // the "already exists" case never reaches here anymore.
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
        // Plain Post/Redirect/Get: the drawer-open signal rode along as a
        // short-lived cookie set by cart_add(); it is consumed (deleted) in
        // public/index.php on the very next request. No silent JS post-back,
        // no session flag — nothing that can get stuck and leave the drawer
        // covering the page forever.
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

    // Validate shipping fields (Checkout required them client-side)
    $required = ['fullName', 'street', 'city', 'state', 'zipCode', 'country'];
    foreach ($required as $f) {
        if (trim((string) ($_POST[$f] ?? '')) === '') {
            $_SESSION['flash_error'] = 'Please fill in all shipping fields.';
            redirect('/checkout');
        }
    }

    $subtotal = cart_total_price();
    // Audit pass 3: server-side price integrity — recompute the total from
    // the catalog prices, NOT from anything the client posted. The order's
    // item snapshot is rebuilt from find_product() so a tampered cart
    // session (or future client-supplied prices) can never alter money owed.
    $freshItems = [];
    foreach ($items as $item) {
        $pid = (string) ($item['product']['id'] ?? '');
        $product = find_product($pid);
        if (!$product) continue; // product delisted since add-to-cart
        $qty = min(99, max(1, (int) ($item['quantity'] ?? 1)));
        $freshItems[] = ['product' => $product, 'quantity' => $qty];
    }
    if (!$freshItems) {
        $_SESSION['flash_error'] = 'Your cart is empty.';
        redirect('/cart');
    }
    $subtotal = array_sum(array_map(
        fn($i) => $i['product']['price'] * $i['quantity'],
        $freshItems
    ));
    $total = $subtotal + cart_shipping($subtotal) + cart_tax($subtotal);

    $order = save_order(array_values($freshItems), $total, [
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
