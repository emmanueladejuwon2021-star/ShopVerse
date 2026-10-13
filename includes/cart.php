<?php
/**
 * Cart & Storefront State Management.
 * Replaces client-side React contexts (Cart + Wishlist + Coupons) with robust server-side state.
 */

declare(strict_types=1);

function sync_cart_cookie(array $cart): void
{
    $_SESSION['cart'] = $cart;
    $isHttps = !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    $json = json_encode($cart);
    if (!headers_sent()) {
        setcookie('shopverse_cart', $json ?: '[]', [
            'expires' => time() + 60 * 60 * 24 * 30, // 30 days
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
    $_COOKIE['shopverse_cart'] = $json ?: '[]';
}

function cart_items(): array
{
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        return $_SESSION['cart'];
    }

    if (!empty($_COOKIE['shopverse_cart'])) {
        $decoded = json_decode((string) $_COOKIE['shopverse_cart'], true);
        if (is_array($decoded) && !empty($decoded)) {
            $_SESSION['cart'] = $decoded;
            return $_SESSION['cart'];
        }
    }

    return $_SESSION['cart'] ?? [];
}

function cart_add(string $productId, int $qty = 1, ?string $color = null, ?string $option = null, float $priceDiff = 0.0, ?string $customImage = null): void
{
    $product = find_product($productId);
    if (!$product) return;
    if (isset($product['inStock']) && !$product['inStock']) return;

    $price = (float) $product['price'] + $priceDiff;
    $image = $customImage ?: ($product['image'] ?? ($product['images'][0] ?? '/assets/products/placeholder.svg'));

    // Compact item structure to keep storage lightweight and well within cookie bounds
    $compactProduct = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $price,
        'image' => $image,
        'brand' => $product['brand'] ?? '',
        'category' => $product['category'] ?? '',
    ];
    if ($color) $compactProduct['selectedColor'] = $color;
    if ($option) $compactProduct['selectedOption'] = $option;

    $cartKey = $productId;
    if ($color) $cartKey .= '-' . preg_replace('/[^a-zA-Z0-9]/', '', $color);
    if ($option) $cartKey .= '-' . preg_replace('/[^a-zA-Z0-9]/', '', $option);

    $cart = cart_items();
    if (isset($cart[$cartKey])) {
        $cart[$cartKey]['quantity'] += max(1, $qty);
    } else {
        $cart[$cartKey] = [
            'key' => $cartKey,
            'product' => $compactProduct,
            'quantity' => max(1, $qty),
            'selectedColor' => $color,
            'selectedOption' => $option,
        ];
    }

    sync_cart_cookie($cart);
    $_SESSION['cart_open'] = true;
}

function cart_remove(string $keyOrId): void
{
    $cart = cart_items();
    if (isset($cart[$keyOrId])) {
        unset($cart[$keyOrId]);
    } else {
        foreach ($cart as $k => $item) {
            if (($item['product']['id'] ?? '') === $keyOrId || ($item['key'] ?? '') === $keyOrId) {
                unset($cart[$k]);
                break;
            }
        }
    }
    sync_cart_cookie($cart);
}

function cart_update_qty(string $keyOrId, int $quantity): void
{
    if ($quantity <= 0) {
        cart_remove($keyOrId);
        return;
    }
    $cart = cart_items();
    if (isset($cart[$keyOrId])) {
        $cart[$keyOrId]['quantity'] = $quantity;
    } else {
        foreach ($cart as $k => $item) {
            if (($item['product']['id'] ?? '') === $keyOrId || ($item['key'] ?? '') === $keyOrId) {
                $cart[$k]['quantity'] = $quantity;
                break;
            }
        }
    }
    sync_cart_cookie($cart);
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
    unset($_SESSION['active_coupon']);
    $isHttps = !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    if (!headers_sent()) {
        setcookie('shopverse_cart', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
    unset($_COOKIE['shopverse_cart']);
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

function get_shipping_methods(): array
{
    return [
        'standard' => [
            'id' => 'standard',
            'name' => 'Standard Ground Delivery',
            'eta' => '3–5 Business Days',
            'cost' => 4.99,
            'freeThreshold' => 100.0,
        ],
        'express' => [
            'id' => 'express',
            'name' => 'Express Air (2-Day Priority)',
            'eta' => '2 Business Days',
            'cost' => 12.99,
            'freeThreshold' => null,
        ],
        'overnight' => [
            'id' => 'overnight',
            'name' => 'Priority Overnight Delivery',
            'eta' => 'Next Business Day by 10:30 AM',
            'cost' => 24.99,
            'freeThreshold' => null,
        ],
    ];
}

function cart_shipping(float $subtotal, string $method = 'standard'): float
{
    $coupon = get_active_coupon();
    if ($coupon && $coupon['type'] === 'free_shipping') {
        return 0.0;
    }
    $methods = get_shipping_methods();
    $chosen = $methods[$method] ?? $methods['standard'];
    if ($chosen['freeThreshold'] !== null && $subtotal >= $chosen['freeThreshold']) {
        return 0.0;
    }
    return $chosen['cost'];
}

function cart_tax(float $subtotalAfterDiscount): float
{
    return round(max(0.0, $subtotalAfterDiscount) * 0.08, 2);
}

// ----------------------------------------------------
// PRODUCT REVIEWS ENGINE
// ----------------------------------------------------

function get_product_reviews(string $productId): array
{
    $product = find_product($productId);
    $baseReviews = $product['reviews_list'] ?? [];
    $custom = $_SESSION['custom_reviews'][$productId] ?? [];
    return array_merge($custom, $baseReviews);
}

function add_product_review(string $productId, array $review): void
{
    if (!isset($_SESSION['custom_reviews'])) {
        $_SESSION['custom_reviews'] = [];
    }
    if (!isset($_SESSION['custom_reviews'][$productId])) {
        $_SESSION['custom_reviews'][$productId] = [];
    }
    array_unshift($_SESSION['custom_reviews'][$productId], $review);
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

function is_ajax_request(): bool
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
}

function render_cart_sidebar_html(): string
{
    ob_start();
    include __DIR__ . '/../views/partials/cart-sidebar.php';
    return (string) ob_get_clean();
}

function handle_post_actions(): void
{
    $action = $_POST['action'] ?? '';
    $redirect = validate_redirect((string) ($_POST['redirect'] ?? '/'));

    switch ($action) {
        case 'add_to_cart':
            $pid = (string) ($_POST['product_id'] ?? '');
            $qty = (int) ($_POST['quantity'] ?? 1);
            $color = !empty($_POST['variant_color']) ? trim((string) $_POST['variant_color']) : null;
            $option = !empty($_POST['variant_option']) ? trim((string) $_POST['variant_option']) : null;
            $priceDiff = (float) ($_POST['price_diff'] ?? 0.0);
            $customImage = !empty($_POST['custom_image']) ? trim((string) $_POST['custom_image']) : null;
            cart_add($pid, $qty, $color, $option, $priceDiff, $customImage);

            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'ok' => true,
                    'message' => 'Added to your cart 🎉',
                    'cartCount' => cart_total_items(),
                    'cartSubtotal' => money(cart_total_price()),
                    'wishlistCount' => count(wishlist_ids()),
                    'sidebarHtml' => render_cart_sidebar_html(),
                ]);
                exit;
            }
            $_SESSION['flash'] = 'Added to your cart 🎉';
            $_SESSION['cart_open'] = true;
            break;

        case 'add_bundle':
            $mainId = (string) ($_POST['main_product_id'] ?? '');
            $bundleIds = $_POST['bundle_product_ids'] ?? [];
            if ($mainId) cart_add($mainId, 1);
            if (is_array($bundleIds)) {
                foreach ($bundleIds as $bId) {
                    cart_add((string) $bId, 1);
                }
            }
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'ok' => true,
                    'message' => 'Bundle added to your cart 🎉',
                    'cartCount' => cart_total_items(),
                    'cartSubtotal' => money(cart_total_price()),
                    'wishlistCount' => count(wishlist_ids()),
                    'sidebarHtml' => render_cart_sidebar_html(),
                ]);
                exit;
            }
            $_SESSION['flash'] = 'Bundle successfully added to your cart with special savings! 🎉';
            $_SESSION['cart_open'] = true;
            break;

        case 'set_currency':
            $curr = strtoupper(trim((string) ($_POST['currency'] ?? 'USD')));
            if (array_key_exists($curr, supported_currencies())) {
                $_SESSION['currency'] = $curr;
            }
            redirect($redirect);
            return;

        case 'add_review':
            $pid = (string) ($_POST['product_id'] ?? '');
            $author = trim((string) ($_POST['author'] ?? ''));
            $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
            $title = trim((string) ($_POST['title'] ?? ''));
            $comment = trim((string) ($_POST['comment'] ?? ''));
            if ($pid && $title && $comment) {
                add_product_review($pid, [
                    'author' => $author ?: 'Verified Customer',
                    'date' => 'Just now',
                    'rating' => $rating,
                    'title' => $title,
                    'comment' => $comment,
                    'verified' => true,
                ]);
                $_SESSION['flash'] = 'Thank you! Your verified review has been published.';
            }
            redirect('/product/' . urlencode($pid) . '#reviews');
            return;

        case 'remove_from_cart':
            cart_remove((string) ($_POST['product_id'] ?? ''));
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'ok' => true,
                    'message' => 'Item removed from cart.',
                    'cartCount' => cart_total_items(),
                    'cartSubtotal' => money(cart_total_price()),
                    'wishlistCount' => count(wishlist_ids()),
                    'sidebarHtml' => render_cart_sidebar_html(),
                ]);
                exit;
            }
            $_SESSION['flash'] = 'Item removed from cart.';
            break;

        case 'update_quantity':
            cart_update_qty((string) ($_POST['product_id'] ?? ''), (int) ($_POST['quantity'] ?? 0));
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'ok' => true,
                    'message' => 'Cart updated.',
                    'cartCount' => cart_total_items(),
                    'cartSubtotal' => money(cart_total_price()),
                    'wishlistCount' => count(wishlist_ids()),
                    'sidebarHtml' => render_cart_sidebar_html(),
                ]);
                exit;
            }
            break;

        case 'clear_cart':
            cart_clear();
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'ok' => true,
                    'message' => 'Your cart has been cleared.',
                    'cartCount' => 0,
                    'cartSubtotal' => money(0.0),
                    'wishlistCount' => count(wishlist_ids()),
                    'sidebarHtml' => render_cart_sidebar_html(),
                ]);
                exit;
            }
            $_SESSION['flash'] = 'Your cart has been cleared.';
            break;

        case 'close_cart':
            $_SESSION['cart_open'] = false;
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => true]);
                exit;
            }
            break;

        case 'toggle_wishlist':
            $pid = (string) ($_POST['product_id'] ?? '');
            $added = wishlist_toggle($pid);
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'ok' => true,
                    'added' => $added,
                    'message' => $added ? 'Saved to your wishlist ❤️' : 'Removed from wishlist.',
                    'wishlistCount' => count(wishlist_ids()),
                    'cartCount' => cart_total_items(),
                ]);
                exit;
            }
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

    // Server-authoritative totals — the client never decides what it pays.
    $shippingMethod = trim((string) ($_POST['shippingMethod'] ?? 'standard'));
    $totals = paystack_cart_totals($shippingMethod);
    $subtotal = $totals['subtotal'];

    $coupon = get_active_coupon();
    $paymentMethod = trim((string) ($_POST['paymentMethod'] ?? 'paystack'));
    $orderNotes = trim((string) ($_POST['orderNotes'] ?? ''));
    $email = trim((string) ($_POST['billingEmail'] ?? (current_user()['email'] ?? '')));

    $meta = [
        'fullName' => trim((string) $_POST['fullName']),
        'street' => trim((string) $_POST['street']),
        'city' => trim((string) $_POST['city']),
        'state' => trim((string) $_POST['state']),
        'zipCode' => trim((string) $_POST['zipCode']),
        'country' => trim((string) $_POST['country']),
        'email' => $email,
        'shippingMethod' => $shippingMethod,
        'orderNotes' => $orderNotes,
        'couponCode' => $coupon['code'] ?? null,
    ];

    if ($paymentMethod === 'cod') {
        // Cash on Delivery: order is placed immediately, payment settles at
        // the door. No gateway involved, nothing to verify.
        $order = save_order(array_values($items), $totals['total'], array_merge($meta, [
            'paymentMethod' => 'Cash on Delivery',
            'paymentStatus' => 'pay_on_delivery',
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount'],
            'shipping' => $totals['shipping'],
            'tax' => $totals['tax'],
        ]), 'processing');
        if (!$order['ok']) {
            $_SESSION['flash_error'] = implode(' ', $order['errors']);
            redirect('/checkout');
        }
        cart_clear();
        $_SESSION['last_order'] = $order['order'];
        redirect('/order-success?id=' . urlencode((string) $order['order']['id']));
        return;
    }

    // Default + explicit 'paystack': hand off to Paystack's hosted checkout.
    // A PENDING order is created server-side; it only becomes paid after we
    // verify the transaction with Paystack's API (callback or webhook).
    $res = paystack_create_transaction(array_values($items), $totals, $meta);
    if (!$res['ok']) {
        $_SESSION['flash_error'] = implode(' ', $res['errors']);
        redirect('/checkout');
    }
    redirect($res['redirect']);
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}
