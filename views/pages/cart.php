<?php
/**
 * Production Cart View
 * Includes free shipping threshold progress bar, coupon engine, image fallbacks, and live totals.
 */
$items = cart_items();
$subtotal = cart_total_price();
$coupon = get_active_coupon();
$discount = cart_discount($subtotal);
$subAfterDiscount = max(0.0, $subtotal - $discount);
$shipping = cart_shipping($subtotal);
$tax = cart_tax($subAfterDiscount);
$total = round($subAfterDiscount + $shipping + $tax, 2);
$freeShippingThreshold = 50.0;
$distanceToFreeShipping = max(0.0, $freeShippingThreshold - $subtotal);
$progressPercent = min(100, (int) round(($subtotal / $freeShippingThreshold) * 100));
?>
<div class="max-w-7xl mx-auto px-4 py-8 lg:py-12">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Your Cart</h1>
      <p class="text-sm text-gray-500 mt-1"><?= cart_total_items() ?> item<?= cart_total_items() === 1 ? '' : 's' ?> in your bag</p>
    </div>
    <?php if ($items): ?>
      <a href="/products" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
        Continue Shopping →
      </a>
    <?php endif; ?>
  </div>

  <?php if (!$items): ?>
    <div class="max-w-md mx-auto text-center py-20 bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm">
      <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 flex items-center justify-center text-3xl">
        🛒
      </div>
      <h2 class="text-xl font-bold text-gray-900 dark:text-white">Your cart is currently empty</h2>
      <p class="text-gray-500 text-sm mt-2 leading-relaxed">
        Looks like you haven't added anything to your cart yet. Explore our top-tier curated catalog.
      </p>
      <a href="/products" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 shadow-lg shadow-indigo-600/20 transition-all">
        Start Shopping Now
      </a>
    </div>
  <?php else: ?>
    <!-- Free Shipping Progress Banner -->
    <div class="mb-8 p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 shadow-sm">
      <div class="flex items-center justify-between text-xs font-semibold mb-2">
        <?php if ($subtotal >= $freeShippingThreshold || ($coupon && $coupon['type'] === 'free_shipping')): ?>
          <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
            <span>🎉</span> <span>Congratulations! You've unlocked FREE Express Shipping.</span>
          </span>
        <?php else: ?>
          <span class="text-gray-700 dark:text-gray-300">
            Add <strong><?= money($distanceToFreeShipping) ?></strong> more to qualify for <strong>FREE Shipping</strong>!
          </span>
        <?php endif; ?>
        <span class="text-gray-400 font-mono"><?= $progressPercent ?>%</span>
      </div>
      <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-500 to-emerald-500 h-full rounded-full transition-all duration-500" style="width: <?= $progressPercent ?>%"></div>
      </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-8 lg:gap-10 items-start">
      <!-- Items List (8 cols) -->
      <div class="lg:col-span-8 space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/60">
          <?php foreach ($items as $item): $p = $item['product']; ?>
            <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-5 items-start sm:items-center">
              <a href="/product/<?= e($p['id']) ?>" class="shrink-0">
                <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"
                     onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
                     class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-2xl bg-gray-100 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-700">
              </a>

              <div class="flex-1 min-w-0">
                <span class="text-[11px] uppercase font-bold tracking-wider text-indigo-600 dark:text-indigo-400"><?= e($p['brand'] ?? $p['category']) ?></span>
                <a href="/product/<?= e($p['id']) ?>" class="font-bold text-base text-gray-900 dark:text-white truncate block hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                  <?= e($p['name']) ?>
                </a>
                <?php if (!empty($item['selectedColor']) || !empty($item['selectedOption'])): ?>
                  <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    <?= !empty($item['selectedColor']) ? 'Finish: ' . e($item['selectedColor']) : '' ?>
                    <?= (!empty($item['selectedColor']) && !empty($item['selectedOption'])) ? ' · ' : '' ?>
                    <?= !empty($item['selectedOption']) ? e($item['selectedOption']) : '' ?>
                  </div>
                <?php endif; ?>
                <p class="text-sm font-extrabold text-indigo-600 dark:text-indigo-400 mt-1"><?= money($p['price']) ?></p>

                <div class="flex flex-wrap items-center gap-4 mt-3">
                  <!-- Stepper Form -->
                  <div class="flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-0.5">
                    <form method="post" class="inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="update_quantity">
                      <input type="hidden" name="product_id" value="<?= e($item['key'] ?? $p['id']) ?>">
                      <input type="hidden" name="quantity" value="<?= max(0, $item['quantity'] - 1) ?>">
                      <input type="hidden" name="redirect" value="/cart">
                      <button class="w-7 h-7 rounded-lg hover:bg-white dark:hover:bg-gray-800 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300 text-xs">−</button>
                    </form>

                    <span class="w-8 text-center text-xs font-bold"><?= (int) $item['quantity'] ?></span>

                    <form method="post" class="inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="update_quantity">
                      <input type="hidden" name="product_id" value="<?= e($item['key'] ?? $p['id']) ?>">
                      <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                      <input type="hidden" name="redirect" value="/cart">
                      <button class="w-7 h-7 rounded-lg hover:bg-white dark:hover:bg-gray-800 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300 text-xs">+</button>
                    </form>
                  </div>

                  <!-- Remove Form -->
                  <form method="post" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="remove_from_cart">
                    <input type="hidden" name="product_id" value="<?= e($item['key'] ?? $p['id']) ?>">
                    <input type="hidden" name="redirect" value="/cart">
                    <button class="text-xs font-medium text-gray-400 hover:text-rose-500 transition-colors">
                      Remove
                    </button>
                  </form>
                </div>
              </div>

              <div class="sm:text-right shrink-0 pt-2 sm:pt-0">
                <span class="text-base font-extrabold text-gray-900 dark:text-white">
                  <?= money($p['price'] * $item['quantity']) ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="flex items-center justify-between pt-2">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="clear_cart">
            <input type="hidden" name="redirect" value="/cart">
            <button class="text-xs text-gray-400 hover:text-rose-500 font-semibold underline">
              Clear entire cart
            </button>
          </form>
        </div>
      </div>

      <!-- Order Summary (4 cols) -->
      <aside class="lg:col-span-4 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-7 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-5">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-700/60">
            Order Summary
          </h2>

          <!-- Promo Code Box -->
          <div>
            <?php if ($coupon): ?>
              <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-xs">
                <div>
                  <span class="font-bold text-emerald-700 dark:text-emerald-300"><?= e($coupon['code']) ?></span>
                  <p class="text-[11px] text-emerald-600 dark:text-emerald-400"><?= e($coupon['description']) ?></p>
                </div>
                <form method="post" action="/cart" class="inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="remove_coupon">
                  <input type="hidden" name="redirect" value="/cart">
                  <button class="text-rose-600 hover:underline font-semibold ml-2">Remove</button>
                </form>
              </div>
            <?php else: ?>
              <form method="post" action="/cart" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="apply_coupon">
                <input type="hidden" name="redirect" value="/cart">
                <input type="text" name="coupon_code" placeholder="Promo code (e.g. SHOP10)"
                       class="flex-1 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button class="rounded-xl bg-gray-900 dark:bg-gray-700 text-white text-xs font-semibold px-4 py-2 hover:bg-gray-800 transition-colors">
                  Apply
                </button>
              </form>
            <?php endif; ?>
          </div>

          <!-- Totals -->
          <dl class="space-y-2.5 text-sm pt-2 border-t border-gray-100 dark:border-gray-700/60 text-gray-600 dark:text-gray-300">
            <div class="flex justify-between">
              <dt>Subtotal</dt>
              <dd class="font-medium text-gray-900 dark:text-white"><?= money($subtotal) ?></dd>
            </div>
            <?php if ($discount > 0): ?>
              <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-medium">
                <dt>Promo Discount</dt>
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
              <dt>Total</dt>
              <dd class="text-indigo-600 dark:text-indigo-400"><?= money($total) ?></dd>
            </div>
          </dl>

          <a href="/checkout"
             class="block w-full text-center rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-4 shadow-lg shadow-indigo-600/25 transition-all text-sm">
            Proceed to Checkout →
          </a>

          <div class="text-center pt-2">
            <span class="inline-flex items-center gap-1.5 text-xs text-gray-400 font-medium">
              <span>🔒</span> 256-Bit SSL Encrypted Checkout
            </span>
          </div>
        </div>
      </aside>
    </div>
  <?php endif; ?>
</div>
