<?php /* Cart — glassmorphism rebuild. All surfaces use theme-flipping CSS vars,
           so light/dark colors always correlate (no hardcoded bg-white dark:bg-gray-800 mismatches). */
      $items = cart_items();
      $subtotal = cart_total_price();
      $shipping = cart_shipping($subtotal);
?>
<div class="max-w-7xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-2">Shopping Cart</h1>
  <p class="text-glass-muted mb-8"><?= cart_total_items() ?> item<?= cart_total_items() === 1 ? '' : 's' ?> in your cart</p>

  <?php if (!$items): ?>
    <div class="glass-card rounded-2xl text-center py-20 px-6">
      <p class="text-6xl mb-4">🛒</p>
      <h2 class="text-xl font-semibold">Your cart is empty</h2>
      <p class="text-glass-muted mt-2 mb-6">Looks like you haven't added anything yet.</p>
      <a href="/products" class="btn-primary inline-block rounded-xl font-semibold px-6 py-3">Start Shopping</a>
    </div>
  <?php else: ?>
    <div class="grid lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-4">
        <?php foreach ($items as $item): $p = $item['product']; ?>
          <div class="glass-card rounded-2xl p-4 flex gap-4 items-start">
            <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" class="w-24 h-24 object-cover rounded-xl glass-divider border">
            <div class="flex-1 min-w-0">
              <a href="/product/<?= e($p['id']) ?>" class="font-semibold hover:text-indigo-500 truncate block"><?= e($p['name']) ?></a>
              <p class="text-sm text-glass-muted"><?= e($p['category']) ?></p>
              <p class="text-indigo-600 dark:text-indigo-400 font-bold mt-1"><?= money($p['price']) ?></p>
              <div class="flex flex-wrap items-center gap-3 mt-3">
                <form method="post" class="flex items-center gap-2">
                  <?= csrf_field() ?><input type="hidden" name="action" value="update_quantity">
                  <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
                  <input type="hidden" name="redirect" value="/cart">
                  <input type="number" name="quantity" min="1" max="99" value="<?= (int) $item['quantity'] ?>"
                         class="glass-input w-16 rounded-lg px-2 py-1 text-sm text-center">
                  <button class="glass-chip text-xs rounded-lg px-3 py-1.5 font-medium transition">Update</button>
                </form>
                <form method="post">
                  <?= csrf_field() ?><input type="hidden" name="action" value="remove_from_cart">
                  <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
                  <input type="hidden" name="redirect" value="/cart">
                  <button class="text-xs text-red-500 dark:text-red-400 hover:underline">Remove</button>
                </form>
              </div>
            </div>
            <div class="text-right font-bold whitespace-nowrap"><?= money($p['price'] * $item['quantity']) ?></div>
          </div>
        <?php endforeach; ?>

        <form method="post" class="pt-2">
          <?= csrf_field() ?><input type="hidden" name="action" value="clear_cart">
          <input type="hidden" name="redirect" value="/cart">
          <button class="text-sm text-glass-muted hover:text-red-500 underline">Clear entire cart</button>
        </form>
      </div>

      <!-- Summary -->
      <aside class="glass-panel rounded-2xl p-6 h-fit lg:sticky top-20">
        <h2 class="text-lg font-bold mb-4">Order Summary</h2>
        <dl class="space-y-2 text-sm">
          <div class="flex justify-between"><dt class="text-glass-muted">Subtotal</dt><dd><?= money($subtotal) ?></dd></div>
          <div class="flex justify-between"><dt class="text-glass-muted">Shipping</dt><dd><?= $shipping === 0.0 ? '<span class="text-green-600 dark:text-green-400 font-medium">Free</span>' : money($shipping) ?></dd></div>
          <div class="flex justify-between"><dt class="text-glass-muted">Tax (8%)</dt><dd><?= money(cart_tax($subtotal)) ?></dd></div>
          <div class="flex justify-between text-base font-bold pt-2 border-t glass-divider">
            <dt>Total</dt><dd><?= money($subtotal + $shipping + cart_tax($subtotal)) ?></dd>
          </div>
        </dl>
        <a href="/checkout" class="btn-primary mt-5 block w-full text-center rounded-xl font-semibold py-3">Proceed to Checkout</a>
        <a href="/products" class="mt-3 block text-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Continue shopping</a>
      </aside>
    </div>
  <?php endif; ?>
</div>
