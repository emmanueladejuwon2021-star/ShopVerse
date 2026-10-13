<?php
/**
 * Production Payments — Paystack integration.
 *
 * Flow (server-authoritative, never trusts the client for money):
 *   1. Checkout POST → `paystack_create_transaction()` recalculates the cart
 *      total server-side, creates a PENDING order + payment intent row, and
 *      initialises a Paystack Standard Session with an embedded authorization
 *      payload (card / bank transfer / USSD / mobile money).
 *   2. The browser is redirected to the returned Paystack checkout URL.
 *   3. After the customer pays, Paystack redirects back to
 *      `/payment/callback?reference=...&trxage=...` — we ALSO verify the
 *      transaction server-side via the API (`/transaction/verify/:reference`)
 *      before marking anything paid. A redirect alone is never trusted.
 *   4. Paystack's Webhook (`POST /payments/webhook`, x-paystack-signature
 *      HMAC-SHA512 verified against PAYSTACK_SECRET_KEY) is the idempotent
 *      source of truth: it finalises orders even if the customer closes the
 *      tab, and records duplicate events without double-processing.
 *
 * Environment variables (set in Vercel → Project Settings or your host):
 *   PAYSTACK_SECRET_KEY     sk_test_... / sk_live_...   (server: init, verify, webhook)
 *   PAYSTACK_PUBLIC_KEY     pk_test_... / pk_live_...   (optional UI badge only)
 *   PAYSTACK_WEBHOOK_SECRET (optional; falls back to the secret key)
 *   APP_BASE_URL            optional canonical base URL for callbacks
 *
 * Without a secret key the gateway reports "not configured" and checkout
 * degrades to Cash-on-Delivery only — no fake card form, no silent failures.
 */

declare(strict_types=1);

require_once __DIR__ . '/http.php';

const PAYSTACK_API_BASE = 'https://api.paystack.co';

/* ------------------------------------------------------------------ */
/* Configuration                                                       */
/* ------------------------------------------------------------------ */

function paystack_secret_key(): string
{
    return trim((string) (getenv('PAYSTACK_SECRET_KEY') ?: getenv('PAYSTACK_PRIVATE_KEY') ?: ''));
}

function paystack_public_key(): string
{
    return trim((string) (getenv('PAYSTACK_PUBLIC_KEY') ?: ''));
}

function paystack_webhook_secret(): string
{
    return trim((string) (getenv('PAYSTACK_WEBHOOK_SECRET') ?: paystack_secret_key()));
}

function paystack_is_configured(): bool
{
    return paystack_secret_key() !== '';
}

/** True when the key looks like a live production key. */
function paystack_is_live(): bool
{
    return str_starts_with(paystack_secret_key(), 'sk_live_');
}

/**
 * Charge currency. Paystack's default account currency is NGN; override with
 * PAYSTACK_CURRENCY (e.g. GHS, ZAR, USD) if your integration is multi-currency.
 */
function paystack_currency(): string
{
    $c = strtoupper(trim((string) (getenv('PAYSTACK_CURRENCY') ?: 'NGN')));
    return preg_match('/^[A-Z]{3}$/', $c) ? $c : 'NGN';
}

/** Canonical base URL for Paystack redirect / callback endpoints. */
function app_base_url(): string
{
    $env = rtrim((string) (getenv('APP_BASE_URL') ?: ''), '/');
    if ($env !== '') {
        return $env;
    }
    $https = !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    $host  = (string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost');
    // Hard guard: Host header must look like a hostname, never injected HTML/CR.
    if (!preg_match('/^[A-Za-z0-9.\-]+(:\d{1,5})?$/', $host)) {
        $host = 'localhost';
    }
    return ($https ? 'https://' : 'http://') . $host;
}



/* ------------------------------------------------------------------ */
/* Low-level Paystack REST client (stream-based, curl-free)            */
/* ------------------------------------------------------------------ */

/**
 * @return array{ok:bool,status:int,data:?array,error:?string}
 */
function paystack_request(string $method, string $path, ?array $payload = null): array
{
    $secret = paystack_secret_key();
    if ($secret === '') {
        return ['ok' => false, 'status' => 0, 'data' => null, 'error' => 'Paystack is not configured on this server.'];
    }

    $headers = [
        'Authorization: Bearer ' . $secret,
        'Accept: application/json',
    ];
    $content = null;
    if ($payload !== null) {
        $headers[] = 'Content-Type: application/json';
        $content = json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    $context = stream_context_create([
        'http' => [
            'method'         => strtoupper($method),
            'header'         => implode("\r\n", $headers),
            'content'        => $content ?? '',
            'timeout'        => 15,
            'ignore_errors'  => true,
            'protocol_version' => 1.1,
        ],
    ]);

    $body = @file_get_contents(PAYSTACK_API_BASE . $path, false, $context);
    if ($body === false) {
        error_log('Paystack network failure: ' . $method . ' ' . $path);
        return ['ok' => false, 'status' => 0, 'data' => null, 'error' => 'Could not reach the payment gateway. Please try again.'];
    }

    $status = 200;
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) {
            $status = (int) $m[1];
            break;
        }
    }

    $json = json_decode($body, true);
    if (!is_array($json)) {
        error_log('Paystack non-JSON response (' . $status . '): ' . substr($body, 0, 300));
        return ['ok' => false, 'status' => $status, 'data' => null, 'error' => 'Unexpected response from the payment gateway.'];
    }

    $ok = $status >= 200 && $status < 300 && (($json['status'] ?? false) === true);
    return [
        'ok'     => $ok,
        'status' => $status,
        'data'   => $json['data'] ?? null,
        'error'  => $ok ? null : (string) ($json['message'] ?? 'Payment gateway rejected the request.'),
    ];
}

/** Generate a unique, collision-resistant payment reference. */
function paystack_reference(): string
{
    return 'SV-' . strtoupper(bin2hex(random_bytes(8)));
}

/* ------------------------------------------------------------------ */
/* Display currency helpers                                            */
/* ------------------------------------------------------------------ */

/**
 * Currency symbol for the configured PAYSTACK_CURRENCY (the money actually
 * charged). Falls back to the ISO code when no symbol is known.
 */
function paystack_currency_symbol(?string $code = null): string
{
    $symbols = ['NGN' => '₦', 'GHS' => '₵', 'ZAR' => 'R', 'USD' => '$', 'XOF' => 'CFA'];
    $code = strtoupper($code ?? paystack_currency());
    return $symbols[$code] ?? $code . ' ';
}

/**
 * Format an amount in the CHARGE currency (what Paystack will collect), as
 * opposed to money() which formats in the customer's browsing currency.
 * Whole-unit currencies (JPY, KRW…) carry no minor units.
 */
function paystack_money(float|int $major): string
{
    $c = paystack_currency();
    $decimals = in_array($c, ['JPY', 'KRW', 'XOF'], true) ? 0 : 2;
    return paystack_currency_symbol($c) . number_format((float) $major, $decimals);
}

/* ------------------------------------------------------------------ */
/* Intent creation (checkout → hosted Paystack session)                */
/* ------------------------------------------------------------------ */

/**
 * Recalculated, server-authoritative totals for the current cart.
 * Mirrors place_order_from_post() math so what we charge equals what we show.
 */
function paystack_cart_totals(string $shippingMethod = 'standard'): array
{
    $subtotal = cart_total_price();
    $discount = cart_discount($subtotal);
    $subAfterDiscount = max(0.0, $subtotal - $discount);
    $method = preg_match('/^[a-z]+$/', $shippingMethod) ? $shippingMethod : 'standard';
    $shipping = cart_shipping($subtotal, $method);
    $tax = cart_tax($subAfterDiscount);
    $total = round($subAfterDiscount + $shipping + $tax, 2);
    return [
        'subtotal' => round($subtotal, 2),
        'discount' => round($discount, 2),
        'shipping' => round($shipping, 2),
        'tax' => $tax,
        'total' => $total,
    ];
}

/**
 * Create a pending order + Paystack transaction. On success returns
 * ['ok'=>true,'redirect'=>'<paystack checkout url>','reference'=>...].
 * The order stays status='pending' until verified (callback/webhook).
 */
function paystack_create_transaction(array $items, array $totals, array $meta): array
{
    if (!paystack_is_configured()) {
        return ['ok' => false, 'errors' => ['Online payments are not configured yet. Add PAYSTACK_SECRET_KEY to the server environment, or choose Cash on Delivery.']];
    }

    $user  = current_user();
    $email = strtolower(trim((string) ($meta['email'] ?? ($user['email'] ?? 'guest@shopverse.app'))));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'errors' => ['A valid email address is required to start a payment.']];
    }

    $reference = paystack_reference();
    $amountMajor = (float) $totals['total'];
    if ($amountMajor <= 0) {
        return ['ok' => false, 'errors' => ['Order total must be greater than zero.']];
    }
    $amountMinor = (int) round($amountMajor * 100); // kobo / cents

    // Persist the PENDING order first — the reference is our idempotency key,
    // and a record that exists before the gateway call means webhooks can
    // always find something to reconcile against.
    $order = save_order(array_values($items), $amountMajor, array_merge($meta, [
        'paymentMethod' => 'Paystack Secure Checkout',
        'paymentStatus' => 'pending',
        'paystackReference' => $reference,
        'subtotal' => $totals['subtotal'],
        'discount' => $totals['discount'],
        'shipping' => $totals['shipping'],
        'tax' => $totals['tax'],
        'couponCode' => $meta['couponCode'] ?? null,
    ]), 'pending');
    if (!$order['ok']) {
        return ['ok' => false, 'errors' => $order['errors']];
    }
    $orderId = (string) $order['order']['id'];

    $metadata = [
        'order_id'    => $orderId,
        'user_id'     => $user['id'] ?? null,
        'customer'    => $meta['fullName'] ?? ($user['name'] ?? ''),
        'shipping'    => $meta['shippingMethod'] ?? 'standard',
        'city'        => $meta['city'] ?? '',
        'country'     => $meta['country'] ?? '',
        'subtotal'    => $totals['subtotal'],
        'discount'    => $totals['discount'],
        'shipping_fee' => $totals['shipping'],
        'tax'         => $totals['tax'],
    ];

    // Standard `transaction/initialize` — returns a hosted Paystack checkout
    // URL supporting card, bank transfer, USSD, mobile money & Apple Pay.
    $payload = [
        'amount'          => $amountMinor,           // kobo — never client-supplied
        'email'           => $email,
        'reference'       => $reference,             // our idempotency key
        'callback_url'    => app_base_url() . '/payment/callback?reference=' . rawurlencode($reference),
        'metadata'        => $metadata,
        'currency'        => paystack_currency(),
    ];
    $res = paystack_request('POST', '/transaction/initialize', $payload);
    if (!$res['ok'] || !is_array($res['data'])) {
        // Roll the local order to failed so history reflects reality.
        paystack_mark_status($orderId, 'payment_failed');
        error_log('Paystack init failed: ' . ($res['error'] ?? 'unknown'));
        return ['ok' => false, 'errors' => ['We could not start the secure payment session (' . ($res['error'] ?? 'gateway unavailable') . '). Please try again.']];
    }
    // Security: only ever redirect to a genuine https Paystack host.
    $checkoutUrl = (string) ($res['data']['authorization_url'] ?? '');
    if (!preg_match('#^https://[A-Za-z0-9.-]*paystack[A-Za-z0-9.-]*/#i', $checkoutUrl)) {
        paystack_mark_status($orderId, 'payment_failed');
        error_log('Paystack returned unexpected authorization_url for ' . $reference);
        return ['ok' => false, 'errors' => ['The payment gateway returned an unexpected response. Please try again.']];
    }

    // Remember where to send the user if they bail out of Paystack.
    $_SESSION['paystack_return_order'] = $orderId;

    return ['ok' => true, 'redirect' => $checkoutUrl, 'reference' => $reference, 'order_id' => $orderId];
}

/* ------------------------------------------------------------------ */
/* Verification (server-side, authoritative)                           */
/* ------------------------------------------------------------------ */

/** Verify a reference with Paystack's API. Returns ['ok','paid','data']. */
function paystack_verify_transaction(string $reference): array
{
    $reference = preg_replace('/[^A-Za-z0-9\-_]/', '', $reference) ?? '';
    if ($reference === '') {
        return ['ok' => false, 'paid' => false, 'data' => null];
    }
    $res = paystack_request('GET', '/transaction/verify/' . rawurlencode($reference));
    if (!$res['ok'] || !is_array($res['data'])) {
        return ['ok' => false, 'paid' => false, 'data' => $res['data']];
    }
    $paid = ($res['data']['status'] ?? '') === 'success';
    return ['ok' => true, 'paid' => $paid, 'data' => $res['data']];
}

/** Locate an order row by its paystack reference (works across backends). */
function find_order_by_reference(string $reference): ?array
{
    foreach (all_orders_for_lookup() as $o) {
        $ref = (string) ($o['paystackReference'] ?? '');
        if ($ref !== '' && hash_equals($ref, $reference)) {
            return $o;
        }
    }
    return null;
}

/**
 * Order lookup for reconciliation. Webhooks have no session, so we scan
 * per-user histories for every known user; SQLite/Turso backends keep this
 * cheap because references are indexed inside the shipping_address JSON blob.
 */
function all_orders_for_lookup(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $orders = [];
    try {
        if (db_backend() === 'sqlite') {
            $st = sqlite_pdo()->prepare("SELECT * FROM orders WHERE shipping_address LIKE '%paystackReference%' ORDER BY created_at DESC LIMIT 500");
            $st->execute();
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $addr = json_decode((string) $r['shipping_address'], true) ?: [];
                $orders[] = array_merge($r, [
                    'id' => $r['id'],
                    'userId' => $r['user_id'],
                    'date' => $r['created_at'],
                    'status' => $r['status'],
                    'total' => $r['total'],
                    'shippingAddress' => $addr,
                    'paystackReference' => $addr['paystackReference'] ?? '',
                    'paymentStatus' => $addr['paymentStatus'] ?? '',
                    'items' => json_decode((string) $r['items'], true) ?: [],
                ]);
            }
        } elseif (db_backend() === 'turso') {
            $rows = turso_select("SELECT * FROM orders WHERE shipping_address LIKE '%paystackReference%' ORDER BY created_at DESC LIMIT 500");
            foreach ($rows as $r) {
                $addr = json_decode((string) $r['shipping_address'], true) ?: [];
                $orders[] = array_merge($r, [
                    'userId' => $r['user_id'],
                    'date' => $r['created_at'],
                    'shippingAddress' => $addr,
                    'paystackReference' => $addr['paystackReference'] ?? '',
                    'paymentStatus' => $addr['paymentStatus'] ?? '',
                    'items' => json_decode((string) $r['items'], true) ?: [],
                ]);
            }
        } else {
            foreach (json_read('orders.json', 'id') as $o) {
                $orders[] = array_merge($o, [
                    'paystackReference' => $o['shippingAddress']['paystackReference'] ?? '',
                    'paymentStatus' => $o['shippingAddress']['paymentStatus'] ?? '',
                ]);
            }
        }
    } catch (Throwable $e) {
        error_log('ShopVerse payment lookup error: ' . $e->getMessage());
    }
    return $cache = $orders;
}

/** Update an order's lifecycle status (best-effort, logged on failure). */
function paystack_mark_status(string $orderId, string $status): void
{
    try {
        if (db_backend() === 'sqlite') {
            sqlite_pdo()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);
        } elseif (db_backend() === 'turso') {
            turso_exec('UPDATE orders SET status = ? WHERE id = ?', [$status, $orderId]);
        } else {
            $orders = json_read('orders.json', 'id');
            if (isset($orders[$orderId])) {
                $orders[$orderId]['status'] = $status;
                json_write('orders.json', $orders);
            }
        }
    } catch (Throwable $e) {
        error_log('ShopVerse payment status update failed: ' . $e->getMessage());
    }
}

/**
 * Idempotently mark an order paid after verification. Writes the gateway
 * receipt details into the order JSON. Returns true when this call performed
 * the transition (false = already paid / not found → nothing to do).
 */
function paystack_complete_payment(string $reference, array $txData): bool
{
    $order = find_order_by_reference($reference);
    if (!$order) {
        error_log('Paystack verify for unknown reference: ' . $reference);
        return false;
    }
    $addr = is_array($order['shippingAddress'] ?? null) ? $order['shippingAddress'] : [];
    if (($addr['paymentStatus'] ?? '') === 'paid') {
        return false; // already reconciled — idempotent
    }

    // Amount integrity: what Paystack actually collected must match the total
    // we invoiced. A mismatch never silently marks the order paid — it is
    // logged loudly for manual reconciliation.
    $chargedMinor = isset($txData['amount']) ? (int) $txData['amount'] : 0;
    $expectedMinor = (int) round((float) ($order['total'] ?? 0) * 100);
    if ($chargedMinor > 0 && $expectedMinor > 0 && $chargedMinor !== $expectedMinor) {
        error_log('PAYSTACK AMOUNT MISMATCH ref=' . $reference
            . ' expected_minor=' . $expectedMinor . ' charged_minor=' . $chargedMinor
            . ' order=' . ($order['id'] ?? '?'));
        $addr['amountMismatch'] = ['expected' => $expectedMinor, 'charged' => $chargedMinor];
    }

    $receipt = [
        'paymentStatus'  => 'paid',
        'paystackDomain' => (string) ($txData['domain'] ?? 'test'),
        'paystackChannel' => (string) ($txData['channel'] ?? 'card'),
        'paystackCurrency' => strtoupper((string) ($txData['currency'] ?? paystack_currency())),
        'paystackAmountPaidMinor' => $chargedMinor ?: null,
        'paystackAuthCode' => (string) (($txData['authorization']['auth_code'] ?? $txData['authorization']['authorization_code'] ?? '')),
        'paystackCardLast4' => (string) ($txData['authorization']['last4'] ?? ''),
        'paystackCardBrand' => (string) ($txData['authorization']['brand'] ?? ''),
        'paystackReceipt' => (string) ($txData['reference'] ?? $reference),
        'paidAt' => date('c'),
    ];
    $newAddr = array_merge($addr, $receipt);

    try {
        if (db_backend() === 'sqlite') {
            $st = sqlite_pdo()->prepare('UPDATE orders SET status = ?, shipping_address = ? WHERE id = ?');
            $st->execute(['processing', json_encode($newAddr), $order['id']]);
        } elseif (db_backend() === 'turso') {
            turso_exec('UPDATE orders SET status = ?, shipping_address = ? WHERE id = ?', ['processing', json_encode($newAddr), $order['id']]);
        } else {
            $orders = json_read('orders.json', 'id');
            if (isset($orders[$order['id']])) {
                $orders[$order['id']]['status'] = 'processing';
                $orders[$order['id']]['shippingAddress'] = $newAddr;
                json_write('orders.json', $orders);
            }
        }
    } catch (Throwable $e) {
        error_log('Paystack completion write failed: ' . $e->getMessage());
        return false;
    }

    // If the paying customer is browsing right now (same session), clear the
    // cart and show the receipt immediately.
    $uid = (string) ($order['userId'] ?? '');
    $sid = (string) (current_user()['id'] ?? '');
    if ($uid !== '' && hash_equals($uid, $sid)) {
        cart_clear();
        $_SESSION['last_order'] = array_merge($order, [
            'status' => 'processing',
            'shippingAddress' => $newAddr,
        ]);
    }
    return true;
}

/* ------------------------------------------------------------------ */
/* Webhook signature verification                                      */
/* ------------------------------------------------------------------ */

/** Constant-time HMAC-SHA512 check of the x-paystack-signature header. */
function paystack_verify_webhook_signature(string $rawBody, string $signatureHeader): bool
{
    $secret = paystack_webhook_secret();
    if ($secret === '' || $signatureHeader === '') {
        return false;
    }
    $expected = hash_hmac('sha512', $rawBody, $secret);
    return hash_equals($expected, strtolower($signatureHeader));
}

/**
 * Handle a decoded Paystack webhook event. Idempotent: repeat deliveries of
 * the same event id are ignored. Only mutates state for events we own.
 */
function paystack_handle_webhook(array $event, ?string $eventId = null): array
{
    $data = $event['data'] ?? [];
    $eventName = (string) ($event['event'] ?? '');
    $reference = (string) ($data['reference'] ?? '');

    if ($reference === '' || strpos($reference, 'SV-') !== 0) {
        return ['handled' => false, 'reason' => 'not ours'];
    }

    switch ($eventName) {
        case 'charge.success':
        case 'subscription.create':
            // Re-verify server-side before trusting the webhook body itself.
            $verify = paystack_verify_transaction($reference);
            if (!$verify['ok'] || !$verify['paid']) {
                return ['handled' => false, 'reason' => 'verification failed'];
            }
            $done = paystack_complete_payment($reference, is_array($verify['data']) ? $verify['data'] : $data);
            return ['handled' => true, 'completed' => $done];

        case 'charge.failed':
            $order = find_order_by_reference($reference);
            if ($order && (($order['paymentStatus'] ?? '') !== 'paid')) {
                paystack_mark_status((string) $order['id'], 'payment_failed');
                return ['handled' => true, 'completed' => false];
            }
            return ['handled' => true, 'completed' => false];

        case 'refund.processed':
            $order = find_order_by_reference($reference);
            if ($order) {
                paystack_mark_status((string) $order['id'], 'refunded');
                return ['handled' => true, 'completed' => false];
            }
            return ['handled' => false, 'reason' => 'unknown order'];

        default:
            return ['handled' => false, 'reason' => 'ignored event: ' . $eventName];
    }
}
