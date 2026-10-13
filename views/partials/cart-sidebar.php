<?php
/**
 * Production CartSidebar Component
 * Quick slide-over drawer with item management, free shipping indicator, and checkout actions.
 */
$items = cart_items();
$subtotal = cart_total_price();
$coupon = get_active_coupon();
$discount = cart_discount($subtotal);
$subAfterDiscount = max(0.0, $subtotal - $discount);
$open = !empty($_SESSION['cart_open']);
$freeShippingThreshold = 50.0;
?>
<div id="cart-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 transition-opacity <?= $open ? '' : 'hidden' ?>" data-cart-close></div>

<aside id="cart-sidebar"
       class="fixed top-0 right-0 h-full w-full max-w-md bg-white dark:bg-gray-900 shadow-2xl z-50 flex flex-col transition-transform duration-300 ease-in-out border-l border-gray-100 dark:border-gray-800 <?= $open ? '' : 'translate-x-full' ?>">
  <!-- Header -->
  <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800">
    <div class="flex items-center gap-2">
      <h2 class="text-lg font-bold text-gray-900 dark:text-white">Your Cart</h2>
      <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-0.5 rounded-full">
        <?= cart_total_items() ?>
      </span>
    </div>
    <button data-cart-close aria-label="Close cart" class="w-8 h-8 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors">
      ✕
    </button>
  </div>

  <!-- Free Shipping Progress -->
  <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 text-xs font-medium">
    <?php if ($subtotal >= $freeShippingThreshold || ($coupon && $coupon['type'] === 'free_shipping')): ?>
      <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1.5">
        <span>🎉</span> Free Express Shipping Unlocked!
      </span>
    <?php else: ?>
      <span class="text-gray-600 dark:text-gray-400">
        Add <strong><?= money($freeShippingThreshold - $subtotal) ?></strong> more to qualify for <strong>FREE Shipping</strong>.
      </span>
    <?php endif; ?>
  </div>

  <!-- Items List Scroll Area -->
  <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
    <?php if (!$items): ?>
      <div class="text-center py-16 space-y-3">
        <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-2xl">🛒</div>
        <p class="font-bold text-gray-900 dark:text-white">Your bag is empty</p>
        <p class="text-xs text-gray-400">Add some favorites to begin shopping.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($items as $item): $p = $item['product']; ?>
      <div class="flex gap-4 p-3 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
        <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"
             onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
             class="w-18 h-18 w-16 h-16 object-cover rounded-xl bg-gray-100 dark:bg-gray-800 shrink-0">
        
        <div class="flex-1 min-w-0">
          <span class="text-[10px] uppercase font-bold tracking-wider text-indigo-600 dark:text-indigo-400 block"><?= e($p['brand'] ?? $p['category']) ?></span>
          <a href="/product/<?= e($p['id']) ?>" class="text-xs font-bold truncate block text-gray-900 dark:text-white hover:text-indigo-600 transition-colors">
            <?= e($p['name']) ?>
          </a>
          <?php if (!empty($item['selectedColor']) || !empty($item['selectedOption'])): ?>
            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
              <?= !empty($item['selectedColor']) ? e($item['selectedColor']) : '' ?>
              <?= (!empty($item['selectedColor']) && !empty($item['selectedOption'])) ? ' · ' : '' ?>
              <?= !empty($item['selectedOption']) ? e($item['selectedOption']) : '' ?>
            </div>
          <?php endif; ?>
          <p class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400 mt-1"><?= money($p['price']) ?></p>

          <div class="flex items-center gap-3 mt-2">
            <!-- Stepper -->
            <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-0.5">
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_quantity">
                <input type="hidden" name="product_id" value="<?= e($item['key'] ?? $p['id']) ?>">
                <input type="hidden" name="quantity" value="<?= max(0, $item['quantity'] - 1) ?>">
                <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
                <button class="w-6 h-6 rounded hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center font-bold text-xs text-gray-500">−</button>
              </form>

              <span class="w-6 text-center text-xs font-bold"><?= (int) $item['quantity'] ?></span>

              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_quantity">
                <input type="hidden" name="product_id" value="<?= e($item['key'] ?? $p['id']) ?>">
                <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
                <button class="w-6 h-6 rounded hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center font-bold text-xs text-gray-500">+</button>
              </form>
            </div>

            <!-- Remove -->
            <form method="post" class="inline ml-auto">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="remove_from_cart">
              <input type="hidden" name="product_id" value="<?= e($item['key'] ?? $p['id']) ?>">
              <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
              <button class="text-[11px] font-medium text-gray-400 hover:text-rose-500 transition-colors">Remove</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Footer Actions -->
  <?php if ($items): ?>
    <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-5 space-y-4 bg-white dark:bg-gray-900">
      <div class="space-y-1.5 text-sm">
        <div class="flex justify-between text-gray-500">
          <span>Subtotal</span>
          <span class="font-bold text-gray-900 dark:text-white"><?= money($subtotal) ?></span>
        </div>
        <?php if ($discount > 0): ?>
          <div class="flex justify-between text-emerald-600 font-semibold text-xs">
            <span>Discount (<?= e($coupon['code'] ?? '') ?>)</span>
            <span>-<?= money($discount) ?></span>
          </div>
        <?php endif; ?>
      </div>

      <div class="grid grid-cols-2 gap-3 pt-2">
        <a href="/cart"
           class="text-center rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 font-semibold text-xs py-3 transition-colors">
          View Cart
        </a>
        <a href="/checkout"
           class="text-center rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3 shadow-md shadow-indigo-600/25 transition-all">
          Checkout →
        </a>
      </div>
    </div>
  <?php endif; ?>
</aside>
