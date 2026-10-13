<?php
/**
 * Cart & Storefront State Management.
 * Replaces client-side React contexts (Cart + Wishlist + Coupons) with robust server-side state.
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
    $_SESSION['cart_open'] = true;
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
    unset($_SESSION['active_coupon']);
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

// ----------------------------------------------------
// COUPON & DISCOUNT ENGINE
// ----------------------------------------------------

function get_active_coupon(): ?array
{
    return $_SESSION['active_coupon'] ?? null;
}

function apply_coupon(string $rawCode): array
{
    $code = strtoupper(trim($rawCode));
    $subtotal = cart_total_price();

    if ($code === 'SHOP10') {
        $_SESSION['active_coupon'] = [
            'code' => 'SHOP10',
            'type' => 'percent',
            'rate' => 0.10,
            'description' => '10% off entire order',
        ];
        return ['ok' => true, 'message' => 'Coupon SHOP10 applied! (10% off)'];
    }

    if ($code === 'SAVE20') {
        if ($subtotal < 100.0) {
            return ['ok' => false, 'message' => 'Coupon SAVE20 requires a minimum order of $100.00.'];
        }
        $_SESSION['active_coupon'] = [
            'code' => 'SAVE20',
            'type' => 'fixed',
            'amount' => 20.00,
            'description' => '$20 off orders over $100',
        ];
        return ['ok' => true, 'message' => 'Coupon SAVE20 applied! ($20.00 discount)'];
    }

    if ($code === 'FREESHIP') {
        $_SESSION['active_coupon'] = [
            'code' => 'FREESHIP',
            'type' => 'free_shipping',
            'description' => 'Free standard shipping on this order',
        ];
        return ['ok' => true, 'message' => 'Coupon FREESHIP applied! (Free shipping)'];
    }

    return ['ok' => false, 'message' => 'Invalid promo code. Try SHOP10 or SAVE20.'];
}

function remove_coupon(): void
{
    unset($_SESSION['active_coupon']);
}

function cart_discount(float $subtotal): float
{
    $coupon = get_active_coupon();
    if (!$coupon) return 0.0;

    if ($coupon['type'] === 'percent') {
        return round($subtotal * (float) $coupon['rate'], 2);
    }
    if ($coupon['type'] === 'fixed') {
        return min($subtotal, (float) $coupon['amount']);
    }
    return 0.0;
}

function cart_shipping(float $subtotal): float
{
    $coupon = get_active_coupon();
    if ($coupon && $coupon['type'] === 'free_shipping') {
        return 0.0;
    }
    return $subtotal >= 50.0 ? 0.0 : 4.99;
}

function cart_tax(float $subtotalAfterDiscount): float
{
    return round(max(0.0, $subtotalAfterDiscount) * 0.08, 2);
}

// ----------------------------------------------------
// WISHLIST ENGINE
// ----------------------------------------------------

function wishlist_ids(): array
{
    return $_SESSION['wishlist'] ?? [];
}

function wishlist_has(string $productId): bool
{
    return in_array($productId, wishlist_ids(), true);
}

function wishlist_toggle(string $productId): bool
{
    $list = wishlist_ids();
    $pos = array_search($productId, $list, true);
    if ($pos !== false) {
        unset($list[$pos]);
        $_SESSION['wishlist'] = array_values($list);
        return false; // removed
    } else {
        $list[] = $productId;
        $_SESSION['wishlist'] = array_values($list);
        return true; // added
    }
}

function wishlist_items(): array
{
    $ids = wishlist_ids();
    $items = [];
    foreach ($ids as $id) {
        $p = find_product($id);
        if ($p) $items[] = $p;
    }
    return $items;
}

// ----------------------------------------------------
// POST ACTION ROUTER
// ----------------------------------------------------

function handle_post_actions(): void
{
    $action = $_POST['action'] ?? '';
    $redirect = validate_redirect((string) ($_POST['redirect'] ?? '/'));

    switch ($action) {
        case 'add_to_cart':
            cart_add((string) ($_POST['product_id'] ?? ''), (int) ($_POST['quantity'] ?? 1));
            $_SESSION['flash'] = 'Added to your cart 🎉';
            break;

        case 'remove_from_cart':
            cart_remove((string) ($_POST['product_id'] ?? ''));
            $_SESSION['flash'] = 'Item removed from cart.';
            break;

        case 'update_quantity':
            cart_update_qty((string) ($_POST['product_id'] ?? ''), (int) ($_POST['quantity'] ?? 0));
            break;

        case 'clear_cart':
            cart_clear();
            $_SESSION['flash'] = 'Your cart has been cleared.';
            break;

        case 'close_cart':
            $_SESSION['cart_open'] = false;
            break;

        case 'toggle_wishlist':
            $pid = (string) ($_POST['product_id'] ?? '');
            $added = wishlist_toggle($pid);
            $_SESSION['flash'] = $added ? 'Saved to your wishlist ❤️' : 'Removed from wishlist.';
            break;

        case 'apply_coupon':
            $code = (string) ($_POST['coupon_code'] ?? '');
            $res = apply_coupon($code);
            if ($res['ok']) {
                $_SESSION['flash'] = $res['message'];
            } else {
                $_SESSION['flash_error'] = $res['message'];
            }
            break;

        case 'remove_coupon':
            remove_coupon();
            $_SESSION['flash'] = 'Promo code removed.';
            break;

        case 'place_order':
            place_order_from_post();
            return;

        case 'newsletter':
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            if ($email) {
                try {
                    db_subscribe((string) $email);
                    $_SESSION['flash'] = 'Thanks for subscribing! Use code WELCOME10 on your next order.';
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

    $_SESSION['cart_open'] = false;
    redirect($redirect);
}

function place_order_from_post(): void
{
    $items = cart_items();
    if (!$items) {
        $_SESSION['flash_error'] = 'Your cart is empty.';
        redirect('/cart');
    }

    $required = ['fullName', 'street', 'city', 'state', 'zipCode', 'country'];
    foreach ($required as $f) {
        if (trim((string) ($_POST[$f] ?? '')) === '') {
            $_SESSION['flash_error'] = 'Please fill in all shipping fields.';
            redirect('/checkout');
        }
    }

    $subtotal = cart_total_price();
    $discount = cart_discount($subtotal);
    $subAfterDiscount = max(0.0, $subtotal - $discount);
    $shipping = cart_shipping($subtotal);
    $tax = cart_tax($subAfterDiscount);
    $total = round($subAfterDiscount + $shipping + $tax, 2);

    $coupon = get_active_coupon();
    $paymentMethod = trim((string) ($_POST['paymentMethod'] ?? 'Credit / Debit Card'));
    $orderNotes = trim((string) ($_POST['orderNotes'] ?? ''));

    $order = save_order(array_values($items), $total, [
        'fullName' => trim((string) $_POST['fullName']),
        'street' => trim((string) $_POST['street']),
        'city' => trim((string) $_POST['city']),
        'state' => trim((string) $_POST['state']),
        'zipCode' => trim((string) $_POST['zipCode']),
        'country' => trim((string) $_POST['country']),
        'paymentMethod' => $paymentMethod,
        'orderNotes' => $orderNotes,
        'subtotal' => $subtotal,
        'discount' => $discount,
        'couponCode' => $coupon['code'] ?? null,
        'shipping' => $shipping,
        'tax' => $tax,
    ]);

    if (!$order['ok']) {
        $_SESSION['flash_error'] = implode(' ', $order['errors']);
        redirect('/checkout');
    }
    $orderData = $order['order'];

    cart_clear();
    $_SESSION['last_order'] = $orderData;
    redirect('/order-success?id=' . urlencode((string) $orderData['id']));
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}
