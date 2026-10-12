<?php /* CartSidebar — port of src/components/CartSidebar.tsx */ 
      $items = cart_items();
      $subtotal = cart_total_price();
      // Server-rendered CLOSED, always. The drawer is opened client-side (JS)
      // only via the navbar button or the one-shot data-cart-open="1" body
      // flag — so a stale server flag can never cover the page on load.
?>
<!-- Inline styles (not Tailwind classes) for the closed/open geometry: this
     guarantees the drawer is off-screen even if app.css fails to load, is
     stale on Vercel's CDN, or is missing a utility class. JS only toggles
     [data-open]; CSS does the rest. -->
<style>
  #cart-sidebar{position:fixed;top:0;right:0;height:100%;width:100%;max-width:24rem;z-index:50;display:flex;flex-direction:column;box-shadow:var(--glass-shadow);transform:translateX(100%);visibility:hidden;transition:transform .3s ease,visibility .3s}
  #cart-sidebar[data-open]{transform:translateX(0);visibility:visible}
  #cart-overlay{position:fixed;inset:0;z-index:40;opacity:0;pointer-events:none;transition:opacity .3s ease}
  #cart-overlay[data-open]{opacity:1;pointer-events:auto}
</style>
<div id="cart-overlay" class="glass-overlay" data-cart-close></div>

<aside id="cart-sidebar"
       class="glass-drawer border-l glass-divider">
  <div class="flex items-center justify-between px-4 py-4 border-b glass-divider">
    <h2 class="text-lg font-bold">Your Cart (<?= count($items) ?>)</h2>
    <button data-cart-close aria-label="Close cart" class="p-2 rounded glass-chip transition">✕</button>
  </div>

  <div class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
    <?php if (!$items): ?>
      <p class="text-center text-glass-muted mt-10">Your cart is empty.</p>
    <?php endif; ?>
    <?php foreach ($items as $item): $p = $item['product']; ?>
      <div class="flex gap-3">
        <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" class="w-16 h-16 object-cover rounded-lg">
        <div class="flex-1 min-w-0">
          <a href="/product/<?= e($p['id']) ?>" class="text-sm font-medium truncate block hover:text-indigo-600"><?= e($p['name']) ?></a>
          <p class="text-sm text-indigo-600 dark:text-indigo-400 font-semibold"><?= money($p['price']) ?></p>
          <div class="flex items-center gap-2 mt-1">
            <form method="post" class="inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="update_quantity">
              <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
              <input type="hidden" name="quantity" value="<?= max(0, $item['quantity'] - 1) ?>">
              <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
              <button class="glass-chip w-6 h-6 rounded-lg text-sm transition">−</button>
            </form>
            <span class="text-sm w-6 text-center"><?= (int) $item['quantity'] ?></span>
            <form method="post" class="inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="update_quantity">
              <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
              <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
              <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
              <button class="glass-chip w-6 h-6 rounded-lg text-sm transition">+</button>
            </form>
            <form method="post" class="ml-auto">
              <?= csrf_field() ?><input type="hidden" name="action" value="remove_from_cart">
              <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
              <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
              <button class="text-xs text-red-500 hover:underline">Remove</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($items): ?>
    <div class="border-t border-[color:var(--glass-line)] dark:border-gray-800 px-4 py-4 space-y-3">
      <div class="flex justify-between font-semibold">
        <span>Subtotal</span><span><?= money($subtotal) ?></span>
      </div>
      <p class="text-xs text-glass-muted"><?= $subtotal >= 50 ? 'You qualify for free shipping 🎉' : money(50 - $subtotal) . ' away from free shipping' ?></p>
      <a href="/cart" class="block w-full text-center rounded-lg border border-indigo-600 text-indigo-600 dark:text-indigo-400 py-2 text-sm font-semibold hover:bg-indigo-50 dark:hover:bg-indigo-900/30">View Cart</a>
      <a href="/checkout" class="block w-full text-center rounded-lg bg-indigo-600 text-white py-2 text-sm font-semibold hover:bg-indigo-700">Checkout</a>
    </div>
  <?php endif; ?>
</aside>
