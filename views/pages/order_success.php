<?php
/**
 * Production Order Confirmation & Success View
 * Provides itemized invoice receipt, interactive fulfillment timeline, and printable summary.
 */
$orderId = $_GET['id'] ?? ($_SESSION['last_order']['id'] ?? '');
$order = null;

if (!empty($_SESSION['last_order']) && (string) $_SESSION['last_order']['id'] === (string) $orderId) {
    $order = $_SESSION['last_order'];
} else {
    foreach (user_orders() as $o) {
        if ((string) $o['id'] === (string) $orderId) {
            $order = $o;
            break;
        }
    }
}

if (!$order): ?>
  <div class="max-w-xl mx-auto px-4 py-20 text-center">
    <h1 class="text-2xl font-bold">Order Not Found</h1>
    <p class="text-gray-500 mt-2">Could not locate receipt for reference #<?= e($orderId) ?>.</p>
    <a href="/orders" class="mt-4 inline-block text-indigo-600 hover:underline">View All Orders →</a>
  </div>
<?php else:
  $shippingAddr = $order['shippingAddress'] ?? [];
  $subtotal = $order['subtotal'] ?? ($order['total'] * 0.85);
  $discount = $order['discount'] ?? 0.0;
  $shippingCost = $order['shipping'] ?? 0.0;
  $taxCost = $order['tax'] ?? ($order['total'] * 0.08);
  $total = $order['total'];
  $estDelivery = date('M j, Y', strtotime('+4 business days'));
?>
<div class="max-w-4xl mx-auto px-4 py-10 lg:py-14 print:p-0 print:max-w-none">
  <!-- Success Header -->
  <div class="text-center space-y-3 mb-10 print:mb-6">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mb-2">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>
    <span class="inline-block text-xs uppercase tracking-widest font-bold text-indigo-600 dark:text-indigo-400">Order Confirmed</span>
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">Thank you for your order!</h1>
    <p class="text-gray-600 dark:text-gray-300 text-sm max-w-lg mx-auto">
      We've received your order and our fulfillment team is preparing your package. A confirmation has been logged to your account.
    </p>
    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-xs font-mono font-medium text-gray-700 dark:text-gray-300">
      <span>Order ID:</span>
      <span class="font-bold text-gray-900 dark:text-white"><?= e($order['id']) ?></span>
    </div>
  </div>

  <!-- Interactive Tracking Timeline -->
  <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm mb-8 print:hidden">
    <div class="flex items-center justify-between mb-6">
      <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 dark:text-gray-400">Fulfillment Status</h3>
      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-2.5 py-1 rounded-full">
        Estimated Delivery: <?= e($estDelivery) ?>
      </span>
    </div>

    <div class="grid grid-cols-4 gap-2 relative">
      <!-- Step 1 -->
      <div class="text-center relative">
        <div class="w-8 h-8 mx-auto rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shadow-md">✓</div>
        <p class="mt-2 text-xs font-bold text-gray-900 dark:text-white">Placed</p>
        <span class="text-[10px] text-gray-400 block"><?= date('M j, g:i a') ?></span>
      </div>
      <!-- Step 2 -->
      <div class="text-center relative">
        <div class="w-8 h-8 mx-auto rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shadow-md animate-pulse">2</div>
        <p class="mt-2 text-xs font-bold text-indigo-600 dark:text-indigo-400">Processing</p>
        <span class="text-[10px] text-gray-400 block">Warehouse</span>
      </div>
      <!-- Step 3 -->
      <div class="text-center relative opacity-50">
        <div class="w-8 h-8 mx-auto rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex items-center justify-center text-xs font-bold">3</div>
        <p class="mt-2 text-xs font-semibold text-gray-700 dark:text-gray-300">Shipped</p>
        <span class="text-[10px] text-gray-400 block">Carrier</span>
      </div>
      <!-- Step 4 -->
      <div class="text-center relative opacity-50">
        <div class="w-8 h-8 mx-auto rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex items-center justify-center text-xs font-bold">4</div>
        <p class="mt-2 text-xs font-semibold text-gray-700 dark:text-gray-300">Delivered</p>
        <span class="text-[10px] text-gray-400 block">Destination</span>
      </div>
    </div>
  </div>

  <!-- Receipt Card -->
  <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden mb-8">
    <div class="p-6 sm:p-8 border-b border-gray-100 dark:border-gray-700/60 flex flex-wrap justify-between items-center gap-4">
      <div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Invoice Summary</h2>
        <p class="text-xs text-gray-500">Date: <?= date('F j, Y', strtotime($order['date'] ?? 'now')) ?></p>
      </div>
      <button type="button" onclick="window.print()"
              class="print:hidden inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Print Invoice
      </button>
    </div>

    <!-- Items List -->
    <div class="p-6 sm:p-8 divide-y divide-gray-100 dark:divide-gray-700/60">
      <?php foreach ($order['items'] as $item): $p = $item['product']; ?>
        <div class="py-4 first:pt-0 last:pb-0 flex items-center gap-4">
          <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"
               onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
               class="w-16 h-16 rounded-xl object-cover bg-gray-100 dark:bg-gray-900 shrink-0">
          <div class="flex-1 min-w-0">
            <h4 class="font-semibold text-sm text-gray-900 dark:text-white truncate"><?= e($p['name']) ?></h4>
            <p class="text-xs text-gray-400 mt-0.5"><?= e($p['category']) ?> · Qty: <?= (int) $item['quantity'] ?></p>
          </div>
          <span class="font-bold text-sm text-gray-900 dark:text-white shrink-0">
            <?= money($p['price'] * $item['quantity']) ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Totals Table -->
    <div class="bg-gray-50 dark:bg-gray-800/80 p-6 sm:p-8 border-t border-gray-100 dark:border-gray-700/60 space-y-2.5 text-sm">
      <div class="flex justify-between text-gray-600 dark:text-gray-300">
        <span>Subtotal</span>
        <span><?= money($subtotal) ?></span>
      </div>
      <?php if ($discount > 0): ?>
        <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-medium">
          <span>Promo Discount (<?= e($order['couponCode'] ?? 'Promo') ?>)</span>
          <span>-<?= money($discount) ?></span>
        </div>
      <?php endif; ?>
      <div class="flex justify-between text-gray-600 dark:text-gray-300">
        <span>Standard Shipping</span>
        <span><?= $shippingCost === 0.0 ? 'FREE' : money($shippingCost) ?></span>
      </div>
      <div class="flex justify-between text-gray-600 dark:text-gray-300">
        <span>Estimated Sales Tax (8%)</span>
        <span><?= money($taxCost) ?></span>
      </div>
      <div class="flex justify-between font-extrabold text-lg text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-700">
        <span>Total Paid</span>
        <span class="text-indigo-600 dark:text-indigo-400"><?= money($total) ?></span>
      </div>
    </div>
  </div>

  <!-- Delivery & Payment Info Grid -->
  <div class="grid sm:grid-cols-2 gap-6 mb-10">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-sm">
      <h3 class="font-bold text-sm uppercase tracking-wider text-gray-400 mb-3">Delivery Address</h3>
      <p class="font-semibold text-gray-900 dark:text-white text-sm"><?= e($shippingAddr['fullName'] ?? '') ?></p>
      <p class="text-sm text-gray-600 dark:text-gray-300 mt-1"><?= e($shippingAddr['street'] ?? '') ?></p>
      <p class="text-sm text-gray-600 dark:text-gray-300">
        <?= e($shippingAddr['city'] ?? '') ?>, <?= e($shippingAddr['state'] ?? '') ?> <?= e($shippingAddr['zipCode'] ?? '') ?>
      </p>
      <p class="text-sm text-gray-600 dark:text-gray-300"><?= e($shippingAddr['country'] ?? '') ?></p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-sm">
      <?php
        $payMethod = (string) ($shippingAddr['paymentMethod'] ?? $order['paymentMethod'] ?? '');
        $payStatus = (string) ($shippingAddr['paymentStatus'] ?? '');
        $isPaid    = $payStatus === 'paid';
        $isCod     = $payStatus === 'pay_on_delivery' || stripos($payMethod, 'Cash') !== false;
      ?>
      <h3 class="font-bold text-sm uppercase tracking-wider text-gray-400 mb-3">Payment &amp; Security</h3>
      <p class="font-semibold text-gray-900 dark:text-white text-sm">
        <?= e($payMethod ?: 'Prepaid') ?>
      </p>
      <?php if ($isPaid): ?>
        <p class="text-xs text-gray-500 mt-1">
          Paid via Paystack<?= !empty($shippingAddr['paystackCardBrand']) ? ' · ' . e((string) $shippingAddr['paystackCardBrand']) : '' ?><?= !empty($shippingAddr['paystackCardLast4']) ? ' •••• ' . e((string) $shippingAddr['paystackCardLast4']) : '' ?><?= !empty($shippingAddr['paystackChannel']) ? ' · ' . e(ucfirst((string) $shippingAddr['paystackChannel'])) : '' ?><?= !empty($shippingAddr['paystackCurrency']) ? ' · ' . e((string) $shippingAddr['paystackCurrency']) : '' ?>
        </p>
        <?php if (!empty($shippingAddr['paystackReceipt'])): ?>
          <p class="text-xs text-gray-500 mt-0.5 font-mono">Receipt: <?= e((string) $shippingAddr['paystackReceipt']) ?></p>
        <?php endif; ?>
      <?php elseif ($isCod): ?>
        <p class="text-xs text-gray-500 mt-1">Settled at the door on delivery — no online charge made yet.</p>
      <?php else: ?>
        <p class="text-xs text-gray-500 mt-1">Transaction encrypted via 256-bit TLS protocol.</p>
      <?php endif; ?>
      <div class="mt-3 flex flex-wrap gap-2">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl
          <?= $isPaid ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60'
                      : ($isCod ? 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60'
                                : 'text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/60') ?>">
          <span><?= $isPaid ? '✓' : ($isCod ? '📦' : '⏳') ?></span>
          <?= $isPaid ? 'Payment Verified' : ($isCod ? 'Pay on Delivery' : 'Payment Pending') ?>
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-semibold bg-indigo-50 dark:bg-indigo-950/60 px-3 py-1.5 rounded-xl">
          <span>🛡️</span> Purchase Protected by ShopVerse Guarantee
        </span>
      </div>
    </div>
  </div>

  <!-- Action CTAs -->
  <div class="flex flex-col sm:flex-row items-center justify-center gap-4 print:hidden">
    <a href="/orders" class="w-full sm:w-auto text-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-white font-semibold px-8 py-3.5 transition-colors">
      View All Orders
    </a>
    <a href="/products" class="w-full sm:w-auto text-center rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3.5 shadow-lg shadow-indigo-600/25 transition-all">
      Continue Shopping →
    </a>
  </div>
</div>
<?php endif; ?>
