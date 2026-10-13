<?php
/**
 * Production Checkout View
 * Promo code engine, shipping speed selector, Paystack-secured payment methods,
 * saved address auto-fill, and itemized summary. Card data is NEVER collected
 * here — online payments are completed on Paystack's PCI-DSS Level 1 hosted
 * checkout; this page only chooses the method and hands off.
 */
$items = cart_items();
$subtotal = cart_total_price();
$coupon = get_active_coupon();
$discount = cart_discount($subtotal);
$subAfterDiscount = max(0.0, $subtotal - $discount);
$shipping = cart_shipping($subtotal, 'standard');
$tax = cart_tax($subAfterDiscount);
$total = round($subAfterDiscount + $shipping + $tax, 2);
$user = current_user();

$savedAddresses = [];
if ($user) {
    $acct = guard_read(fn() => db_get_account((string) $user['id']))['result'] ?? null;
    $savedAddresses = $acct['addresses'] ?? [];
}
$shippingMethods = get_shipping_methods();
$paystackReady = paystack_is_configured();
$paystackLive  = $paystackReady && paystack_is_live();
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
      <!-- Main Form (7 cols) -->
      <form id="checkout-form" method="post" action="/checkout" class="lg:col-span-7 space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="place_order">

        <!-- Shipping Address Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-5">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">1</span>
              <h2 class="text-lg font-bold text-gray-900 dark:text-white">Shipping Address</h2>
            </div>
            <span class="text-xs text-gray-400">Step 1 of 3</span>
          </div>

          <?php if (!empty($savedAddresses)): ?>
            <div class="p-3 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-2xl flex items-center gap-2 overflow-x-auto text-xs border border-indigo-100 dark:border-indigo-900/50">
              <span class="font-bold text-indigo-700 dark:text-indigo-300 shrink-0">⚡ Autofill:</span>
              <?php foreach ($savedAddresses as $sa): ?>
                <button type="button" onclick="fillAddress(<?= htmlspecialchars(json_encode($sa), ENT_QUOTES) ?>)"
                        class="rounded-xl bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-800 px-3 py-1 font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white transition-colors shrink-0 shadow-xs">
                  <?= e($sa['label'] ?? 'Saved Address') ?> (<?= e($sa['city'] ?? '') ?>)
                </button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Full Name *</label>
              <input id="addr-name" name="fullName" required value="<?= e($user['name'] ?? '') ?>" placeholder="John Doe"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Street Address *</label>
              <input id="addr-street" name="street" required placeholder="123 Market St, Suite 400"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">City *</label>
              <input id="addr-city" name="city" required placeholder="San Francisco"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">State / Province *</label>
              <input id="addr-state" name="state" required placeholder="California"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">ZIP / Postal Code *</label>
              <input id="addr-zip" name="zipCode" required placeholder="94103"
                     class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Country *</label>
              <select id="addr-country" name="country" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="United States" selected>United States</option>
                <option value="Canada">Canada</option>
                <option value="United Kingdom">United Kingdom</option>
                <option value="Australia">Australia</option>
                <option value="Nigeria">Nigeria</option>
                <option value="Germany">Germany</option>
                <option value="France">France</option>
              </select>
            </div>
          </div>

          <div class="pt-2">
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Delivery Instructions (Optional)</label>
            <input name="orderNotes" placeholder="e.g. Leave package with reception or at side gate"
                   class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
          </div>
        </section>

        <!-- Shipping Delivery Method Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-5">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">2</span>
              <h2 class="text-lg font-bold text-gray-900 dark:text-white">Shipping Speed &amp; Carrier</h2>
            </div>
            <span class="text-xs text-gray-400">Step 2 of 3</span>
          </div>

          <div class="space-y-3">
            <?php foreach ($shippingMethods as $smId => $sm):
              $cost = cart_shipping($subtotal, $smId);
              $isFree = $cost === 0.0;
            ?>
              <label class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                <div class="flex items-center gap-3.5">
                  <input type="radio" name="shippingMethod" value="<?= e($smId) ?>" <?= $smId === 'standard' ? 'checked' : '' ?>
                         onchange="updateShippingRate(<?= (float) $cost ?>)"
                         class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                  <div>
                    <span class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white block"><?= e($sm['name']) ?></span>
                    <span class="text-xs text-gray-400">Estimated transit: <?= e($sm['eta']) ?></span>
                  </div>
                </div>
                <span class="text-xs font-bold text-gray-900 dark:text-white shrink-0">
                  <?= $isFree ? '<span class="text-emerald-600 font-bold">FREE</span>' : money($cost) ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- Payment Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-5">
          <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-3">
              <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">3</span>
              <h2 class="text-lg font-bold text-gray-900 dark:text-white">Payment Method</h2>
            </div>
            <div class="flex items-center gap-2">
              <?php if ($paystackReady): ?>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full border
                  <?= $paystackLive
                      ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 border-emerald-200 dark:border-emerald-800'
                      : 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800' ?>">
                  <span class="w-1.5 h-1.5 rounded-full <?= $paystackLive ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' ?>"></span>
                  Paystack · <?= $paystackLive ? 'Live' : 'Test Mode' ?>
                </span>
              <?php endif; ?>
              <span class="text-xs text-gray-400">Step 3 of 3</span>
            </div>
          </div>

          <?php if (!$paystackReady): ?>
            <!-- Honest operator notice — no fake gateway, no fake card form. -->
            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
              <strong>⚙️ Online payments are not configured yet.</strong><br>
              Add <code class="font-mono bg-white/60 dark:bg-gray-900/60 px-1.5 py-0.5 rounded">PAYSTACK_SECRET_KEY</code> to the server environment
              (Vercel → Project Settings → Environment Variables) to accept cards, bank transfer, USSD and mobile money via Paystack.
              Until then you can continue with Cash on Delivery.
            </div>
          <?php endif; ?>

          <!-- Billing email (used for the Paystack receipt) -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1.5">Billing Email *</label>
            <input type="email" name="billingEmail" required value="<?= e($user['email'] ?? '') ?>" placeholder="you@example.com"
                   class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <p class="text-[11px] text-gray-400 mt-1.5">Paystack sends the official payment receipt to this address.</p>
          </div>

          <!-- Payment method options -->
          <div class="space-y-3">
            <label class="block <?= $paystackReady ? '' : 'opacity-60' ?>">
              <div class="flex items-start gap-3.5 p-4 rounded-2xl border-2 transition-all cursor-pointer border-gray-200 dark:border-gray-700 hover:border-indigo-400 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60 dark:has-[:checked]:bg-indigo-950/40">
                <input type="radio" name="paymentMethod" value="paystack" <?= $paystackReady ? 'checked' : 'disabled' ?> class="mt-1 w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-bold text-sm text-gray-900 dark:text-white">💳 Pay with Paystack</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-950/70 px-2 py-0.5 rounded-full">Recommended</span>
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Card · Bank Transfer · USSD · Mobile Money · Apple&nbsp;Pay — completed on Paystack's PCI-DSS&nbsp;Level&nbsp;1 certified checkout. Your card details never touch our servers.
                  </p>
                  <div class="flex items-center gap-3 mt-2 text-[10px] font-semibold text-gray-400">
                    <span class="rounded border border-gray-200 dark:border-gray-700 px-1.5 py-0.5">VISA</span>
                    <span class="rounded border border-gray-200 dark:border-gray-700 px-1.5 py-0.5">Mastercard</span>
                    <span class="rounded border border-gray-200 dark:border-gray-700 px-1.5 py-0.5">Verve</span>
                    <span class="rounded border border-gray-200 dark:border-gray-700 px-1.5 py-0.5">Bank Transfer</span>
                  </div>
                </div>
              </div>
            </label>

            <label class="block">
              <div class="flex items-start gap-3.5 p-4 rounded-2xl border-2 transition-all cursor-pointer border-gray-200 dark:border-gray-700 hover:border-indigo-400 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60 dark:has-[:checked]:bg-indigo-950/40">
                <input type="radio" name="paymentMethod" value="cod" <?= $paystackReady ? '' : 'checked' ?> class="mt-1 w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                <div class="flex-1 min-w-0">
                  <span class="font-bold text-sm text-gray-900 dark:text-white">📦 Cash on Delivery</span>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pay in cash or by card terminal when the courier delivers your package. Order is placed immediately.
                  </p>
                </div>
              </div>
            </label>
          </div>

          <p class="text-[11px] text-gray-400 flex items-center gap-1.5">
            🔒 256-bit SSL encrypted · Powered by Paystack · Verified server-side before your order is confirmed
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
                  <div class="flex items-center gap-2 text-[11px] text-gray-400 mt-0.5">
                    <span>Qty: <?= (int) $item['quantity'] ?></span>
                    <?php if (!empty($item['selectedColor'])): ?>
                      <span>· <?= e($item['selectedColor']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($item['selectedOption'])): ?>
                      <span>· <?= e($item['selectedOption']) ?></span>
                    <?php endif; ?>
                  </div>
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
              <dd id="summary-shipping" class="font-medium text-gray-900 dark:text-white">
                <?= $shipping === 0.0 ? '<span class="text-emerald-600 font-bold">FREE</span>' : money($shipping) ?>
              </dd>
            </div>
            <div class="flex justify-between">
              <dt>Estimated Tax (8%)</dt>
              <dd class="font-medium text-gray-900 dark:text-white"><?= money($tax) ?></dd>
            </div>
            <div class="flex justify-between text-base font-extrabold text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-700">
              <dt>Order Total</dt>
              <dd id="summary-total" class="text-indigo-600 dark:text-indigo-400"><?= money($total) ?></dd>
            </div>
          </dl>

          <!-- Place Order / Pay Button -->
          <button id="checkout-submit" form="checkout-form" type="submit" data-paystack="<?= $paystackReady ? '1' : '0' ?>"
                  class="w-full rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-60 disabled:cursor-wait text-white font-bold py-4 shadow-lg shadow-indigo-600/25 transition-all text-sm flex items-center justify-center gap-2">
            <span id="checkout-submit-label"><?= $paystackReady ? '🔒 Pay ' . e(money($total)) . ' Securely with Paystack' : 'Place Order Now' ?></span>
            <svg id="checkout-submit-spinner" class="hidden animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
          </button>

          <div class="text-center space-y-1.5">
            <span class="block text-[11px] text-gray-400 font-medium">You will be redirected to Paystack's secure checkout to complete payment.</span>
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
  var subtotalAfterDiscount = <?= (float) $subAfterDiscount ?>;
  var taxCost = <?= (float) $tax ?>;

  function togglePaymentTab(type) {
    // Legacy no-op kept for safety; payment UI is now radio-card based.
  }

  function updateShippingRate(cost) {
    var shipDisplay = document.getElementById('summary-shipping');
    var totalDisplay = document.getElementById('summary-total');
    if (shipDisplay) {
      shipDisplay.innerHTML = cost === 0 ? '<span class="text-emerald-600 font-bold">FREE</span>' : '$' + cost.toFixed(2);
    }
    if (totalDisplay) {
      var newTotal = subtotalAfterDiscount + cost + taxCost;
      totalDisplay.innerText = '$' + newTotal.toFixed(2);
    }
    var label = document.getElementById('checkout-submit-label');
    if (label && label.dataset.paystackOn === '1') {
      label.textContent = '\uD83D\uDD12 Pay $' + newTotal.toFixed(2) + ' Securely with Paystack';
    }
  }

  function fillAddress(addr) {
    if (!addr) return;
    if (addr.fullName) document.getElementById('addr-name').value = addr.fullName;
    if (addr.street) document.getElementById('addr-street').value = addr.street;
    if (addr.city) document.getElementById('addr-city').value = addr.city;
    if (addr.state) document.getElementById('addr-state').value = addr.state;
    if (addr.zipCode) document.getElementById('addr-zip').value = addr.zipCode;
    if (addr.country) document.getElementById('addr-country').value = addr.country;
  }

  (function () {
    var form = document.getElementById('checkout-form');
    var btn = document.getElementById('checkout-submit');
    var spinner = document.getElementById('checkout-submit-spinner');
    var label = document.getElementById('checkout-submit-label');
    if (!form || !btn) return;

    var paystackOn = btn.getAttribute('data-paystack') === '1';
    if (label) label.dataset.paystackOn = paystackOn ? '1' : '0';

    form.addEventListener('submit', function () {
      // Double-submit guard: the server creates a fresh pending order + Paystack
      // session per POST, so one click must equal exactly one request.
      if (form.dataset.submitting === '1') return false;
      form.dataset.submitting = '1';
      btn.disabled = true;
      if (spinner) spinner.classList.remove('hidden');
      if (label) {
        label.textContent = paystackOn
          ? 'Redirecting to Paystack…'
          : 'Placing your order…';
      }
      return true;
    });
  })();
</script>
