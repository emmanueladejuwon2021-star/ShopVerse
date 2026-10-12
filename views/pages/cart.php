<?php /* Cart — port of src/pages/Cart.tsx */
      $items = cart_items();
      $subtotal = cart_total_price();
      $shipping = cart_shipping($subtotal);
?>
<div class="max-w-7xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-8">Shopping Cart (<?= cart_total_items() ?> items)</h1>

  <?php if (!$items): ?>
    <div class="text-center py-20">
      <p class="text-6xl mb-4">🛒</p>
      <h2 class="text-xl font-semibold">Your cart is empty</h2>
      <a href="/products" class="mt-4 inline-block rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3">Start Shopping</a>
    </div>
  <?php else: ?>
    <div class="grid lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-4">
        <?php foreach ($items as $item): $p = $item['product']; ?>
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 flex gap-4">
            <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" class="w-24 h-24 object-cover rounded-lg">
            <div class="flex-1">
              <a href="/product/<?= e($p['id']) ?>" class="font-semibold hover:text-indigo-600"><?= e($p['name']) ?></a>
              <p class="text-sm text-gray-500"><?= e($p['category']) ?></p>
              <p class="text-indigo-600 dark:text-indigo-400 font-bold mt-1"><?= money($p['price']) ?></p>
              <div class="flex items-center gap-3 mt-3">
                <form method="post" class="flex items-center gap-2">
                  <?= csrf_field() ?><input type="hidden" name="action" value="update_quantity">
                  <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
                  <input type="hidden" name="redirect" value="/cart">
                  <input type="number" name="quantity" min="0" max="99" value="<?= (int) $item['quantity'] ?>"
                         class="w-16 rounded border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-2 py-1 text-sm text-center">
                  <button class="text-xs rounded bg-gray-100 dark:bg-gray-700 px-2 py-1 hover:bg-gray-200">Update</button>
                </form>
                <form method="post">
                  <?= csrf_field() ?><input type="hidden" name="action" value="remove_from_cart">
                  <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
                  <input type="hidden" name="redirect" value="/cart">
                  <button class="text-xs text-red-500 hover:underline">Remove</button>
                </form>
              </div>
            </div>
            <div class="text-right font-bold"><?= money($p['price'] * $item['quantity']) ?></div>
          </div>
        <?php endforeach; ?>

        <form method="post" class="pt-2">
          <?= csrf_field() ?><input type="hidden" name="action" value="clear_cart">
          <input type="hidden" name="redirect" value="/cart">
          <button class="text-sm text-gray-500 hover:text-red-500 underline">Clear entire cart</button>
        </form>
      </div>

      <!-- Summary -->
      <aside class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-fit sticky top-20">
        <h2 class="text-lg font-bold mb-4">Order Summary</h2>
        <dl class="space-y-2 text-sm">
          <div class="flex justify-between"><dt>Subtotal</dt><dd><?= money($subtotal) ?></dd></div>
          <div class="flex justify-between"><dt>Shipping</dt><dd><?= $shipping === 0.0 ? '<span class="text-green-600 font-medium">Free</span>' : money($shipping) ?></dd></div>
          <div class="flex justify-between"><dt>Tax (8%)</dt><dd><?= money(cart_tax($subtotal)) ?></dd></div>
          <div class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-2 text-base font-bold">
            <dt>Total</dt><dd><?= money($subtotal + $shipping + cart_tax($subtotal)) ?></dd>
          </div>
        </dl>
        <a href="/checkout" class="mt-5 block w-full text-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3">Proceed to Checkout</a>
        <a href="/products" class="mt-3 block text-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Continue shopping</a>
      </aside>
    </div>
  <?php endif; ?>
</div>
