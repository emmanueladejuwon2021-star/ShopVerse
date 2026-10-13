<?php
/**
 * Production Checkout View
 * Features promo code engine, express payment method tabs, order notes, and itemized summary.
 */
$items = cart_items();
$subtotal = cart_total_price();
$coupon = get_active_coupon();
$discount = cart_discount($subtotal);
$subAfterDiscount = max(0.0, $subtotal - $discount);
$shipping = cart_shipping($subtotal);
$tax = cart_tax($subAfterDiscount);
$total = round($subAfterDiscount + $shipping + $tax, 2);
$user = current_user();
?>
<div class="max-w-6xl mx-auto px-4 py-8 lg:py-12">
  <div class="flex items-center gap-2 text-xs text-gray-400 uppercase tracking-widest font-semibold mb-2">
    <span>Cart</span> <span>/</span> <span class="text-indigo-600">Secure Checkout</span>
  </div>
  <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-8">Checkout</h1>

  <?php if (!$items): ?>
    <div class="max-w-md mx-auto text-center py-20 bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm">
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-2xl">🛒</div>
      <h2 class="text-xl font-bold text-gray-900 dark:text-white">Your cart is empty</h2>
      <p class="text-gray-500 text-sm mt-1">Add some products to your cart before proceeding to checkout.</p>
      <a class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 transition-colors" href="/products">
        Browse Products →
      </a>
    </div>
  <?php else: ?>
    <div class="grid lg:grid-cols-12 gap-8 lg:gap-10 items-start">
      <!-- Main Form (8 cols) -->
      <form id="checkout-form" method="post" action="/checkout" class="lg:col-span-7 space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="place_order">

        <!-- Shipping Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-5">
          <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">1</span>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Shipping Address</h2>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Full Name *</label>
              <input name="fullName" required value="<?= e($user['name'] ?? '') ?>" placeholder="John Doe"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Street Address *</label>
              <input name="street" required placeholder="123 Market St, Suite 400"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">City *</label>
              <input name="city" required placeholder="San Francisco"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">State / Province *</label>
              <input name="state" required placeholder="California"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">ZIP / Postal Code *</label>
              <input name="zipCode" required placeholder="94103"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Country *</label>
              <select name="country" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="United States" selected>United States</option>
                <option value="Canada">Canada</option>
                <option value="United Kingdom">United Kingdom</option>
                <option value="Australia">Australia</option>
                <option value="Nigeria">Nigeria</option>
                <option value="Germany">Germany</option>
              </select>
            </div>
          </div>

          <div class="pt-2">
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Delivery Notes (Optional)</label>
            <input name="orderNotes" placeholder="e.g. Leave package at front porch or with building concierge"
                   class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
          </div>
        </section>

        <!-- Payment Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-5">
          <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">2</span>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Payment Method</h2>
          </div>

          <!-- Payment Method Selector Tabs -->
          <div class="grid grid-cols-3 gap-2 p-1.5 bg-gray-100 dark:bg-gray-900 rounded-2xl">
            <label class="cursor-pointer">
              <input type="radio" name="paymentMethod" value="Credit / Debit Card" checked class="peer sr-only" onchange="togglePaymentTab('card')">
              <div class="peer-checked:bg-white dark:peer-checked:bg-gray-800 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400 peer-checked:shadow-sm rounded-xl py-2.5 text-xs font-semibold text-center transition-all text-gray-600 dark:text-gray-300">
                💳 Card
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="paymentMethod" value="Apple / Google Pay" class="peer sr-only" onchange="togglePaymentTab('express')">
              <div class="peer-checked:bg-white dark:peer-checked:bg-gray-800 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400 peer-checked:shadow-sm rounded-xl py-2.5 text-xs font-semibold text-center transition-all text-gray-600 dark:text-gray-300">
                ⚡ Express Pay
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="paymentMethod" value="Cash on Delivery" class="peer sr-only" onchange="togglePaymentTab('cod')">
              <div class="peer-checked:bg-white dark:peer-checked:bg-gray-800 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400 peer-checked:shadow-sm rounded-xl py-2.5 text-xs font-semibold text-center transition-all text-gray-600 dark:text-gray-300">
                📦 Pay on Delivery
              </div>
            </label>
          </div>

          <!-- Card Details Input -->
          <div id="payment-card-panel" class="space-y-4 pt-2">
            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Card Number *</label>
              <div class="relative">
                <input name="cardNumber" inputmode="numeric" placeholder="4242 •••• •••• 4242" maxlength="19" value="4242 4242 4242 4242"
                       class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 pl-4 pr-12 py-2.5 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <span class="absolute right-3.5 top-2.5 text-sm">🔒</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Expires (MM/YY) *</label>
                <input name="expiry" placeholder="12/28" pattern="\d{2}/\d{2}" value="12/28"
                       class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Security Code (CVC) *</label>
                <input name="cvc" inputmode="numeric" placeholder="123" maxlength="4" value="888"
                       class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
              </div>
            </div>
          </div>

          <div id="payment-express-panel" class="hidden p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-sm text-indigo-700 dark:text-indigo-300">
            ⚡ Ready for one-touch biometric authorization at order placement.
          </div>

          <div id="payment-cod-panel" class="hidden p-4 rounded-2xl bg-gray-50 dark:bg-gray-900 text-sm text-gray-600 dark:text-gray-300">
            📦 You can pay in cash or card when the courier arrives at your shipping address.
          </div>

          <p class="text-[11px] text-gray-400">
            🔒 Test demo mode active: No real monetary charge will be billed to your card.
          </p>
        </section>
      </form>

      <!-- Order Summary Aside (5 cols) -->
      <aside class="lg:col-span-5 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-7 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-5">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Order Summary</h2>
            <span class="text-xs text-gray-400"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?></span>
          </div>

          <!-- Items Scroll Area -->
          <ul class="space-y-3 max-h-60 overflow-y-auto pr-1">
            <?php foreach ($items as $item): $p = $item['product']; ?>
              <li class="flex items-center gap-3">
                <img src="<?= e($p['image']) ?>" alt=""
                     onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
                     class="w-12 h-12 rounded-xl object-cover bg-gray-100 dark:bg-gray-900 shrink-0">
                <div class="flex-1 min-w-0">
                  <h4 class="text-xs font-semibold text-gray-900 dark:text-white truncate"><?= e($p['name']) ?></h4>
                  <p class="text-[11px] text-gray-400">Qty: <?= (int) $item['quantity'] ?></p>
                </div>
                <span class="text-xs font-bold text-gray-900 dark:text-white shrink-0">
                  <?= money($p['price'] * $item['quantity']) ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>

          <!-- Promo Code Form -->
          <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60">
            <?php if ($coupon): ?>
              <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-xs">
                <div>
                  <span class="font-bold text-emerald-700 dark:text-emerald-300">Code: <?= e($coupon['code']) ?></span>
                  <p class="text-[11px] text-emerald-600 dark:text-emerald-400"><?= e($coupon['description']) ?></p>
                </div>
                <form method="post" action="/checkout" class="inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="remove_coupon">
                  <input type="hidden" name="redirect" value="/checkout">
                  <button class="text-rose-600 hover:underline font-semibold ml-2">Remove</button>
                </form>
              </div>
            <?php else: ?>
              <form method="post" action="/checkout" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="apply_coupon">
                <input type="hidden" name="redirect" value="/checkout">
                <input type="text" name="coupon_code" placeholder="Promo code (e.g. SHOP10)"
                       class="flex-1 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button class="rounded-xl bg-gray-900 dark:bg-gray-700 text-white text-xs font-semibold px-4 py-2 hover:bg-gray-800 transition-colors">
                  Apply
                </button>
              </form>
              <p class="text-[11px] text-gray-400 mt-1.5">Try code <strong class="text-indigo-600 font-mono">SHOP10</strong> for 10% off or <strong class="text-indigo-600 font-mono">FREESHIP</strong>.</p>
            <?php endif; ?>
          </div>

          <!-- Cost Breakdown -->
          <dl class="space-y-2.5 text-sm pt-4 border-t border-gray-100 dark:border-gray-700/60 text-gray-600 dark:text-gray-300">
            <div class="flex justify-between">
              <dt>Subtotal</dt>
              <dd class="font-medium text-gray-900 dark:text-white"><?= money($subtotal) ?></dd>
            </div>
            <?php if ($discount > 0): ?>
              <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-medium">
                <dt>Discount</dt>
                <dd>-<?= money($discount) ?></dd>
              </div>
            <?php endif; ?>
            <div class="flex justify-between">
              <dt>Shipping</dt>
              <dd class="font-medium text-gray-900 dark:text-white">
                <?= $shipping === 0.0 ? '<span class="text-emerald-600 font-bold">FREE</span>' : money($shipping) ?>
              </dd>
            </div>
            <div class="flex justify-between">
              <dt>Estimated Tax (8%)</dt>
              <dd class="font-medium text-gray-900 dark:text-white"><?= money($tax) ?></dd>
            </div>
            <div class="flex justify-between text-base font-extrabold text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-700">
              <dt>Order Total</dt>
              <dd class="text-indigo-600 dark:text-indigo-400"><?= money($total) ?></dd>
            </div>
          </dl>

          <!-- Place Order Button -->
          <button form="checkout-form" type="submit"
                  class="w-full rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-4 shadow-lg shadow-indigo-600/25 transition-all text-sm flex items-center justify-center gap-2">
            <span>Place Order</span>
            <span>·</span>
            <span><?= money($total) ?></span>
          </button>

          <div class="text-center">
            <span class="inline-flex items-center gap-1.5 text-xs text-gray-400 font-medium">
              <span>🛡️</span> 30-Day Money-Back Guarantee
            </span>
          </div>
        </div>
      </aside>
    </div>
  <?php endif; ?>
</div>

<script>
  function togglePaymentTab(type) {
    document.getElementById('payment-card-panel').classList.toggle('hidden', type !== 'card');
    document.getElementById('payment-express-panel').classList.toggle('hidden', type !== 'express');
    document.getElementById('payment-cod-panel').classList.toggle('hidden', type !== 'cod');
  }
</script>
