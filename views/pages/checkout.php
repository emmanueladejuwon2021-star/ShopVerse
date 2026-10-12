<?php /* Checkout — port of src/pages/Checkout.tsx (multi-step UI → single server-rendered form) */
      $items = cart_items();
      $subtotal = cart_total_price();
      $shipping = cart_shipping($subtotal);
      $tax = cart_tax($subtotal);
      $total = $subtotal + $shipping + $tax;
      $user = current_user();
?>
<div class="max-w-5xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-8">Checkout</h1>

  <?php if (!$items): ?>
    <p class="text-glass-muted">Your cart is empty. <a class="text-indigo-600 hover:underline" href="/products">Go shopping →</a></p>
  <?php else: ?>
    <form method="post" action="/checkout" class="grid lg:grid-cols-3 gap-8">
      <?= csrf_field() ?><input type="hidden" name="action" value="place_order">

      <div class="lg:col-span-2 space-y-8">
        <!-- Shipping -->
        <section class="glass-card rounded-xl p-6">
          <h2 class="text-lg font-bold mb-4">Shipping Address</h2>
          <div class="grid sm:grid-cols-2 gap-4">
            <?php
            $fields = [
                'fullName' => ['Full Name', $user['name'] ?? ''],
                'street'   => ['Street Address', ''],
                'city'     => ['City', ''],
                'state'    => ['State', ''],
                'zipCode'  => ['ZIP Code', ''],
                'country'  => ['Country', 'United States'],
            ];
            foreach ($fields as $name => [$label, $default]): ?>
              <div class="<?= in_array($name, ['street', 'country'], true) ? 'sm:col-span-2' : '' ?>">
                <label class="block text-sm font-medium mb-1"><?= $label ?></label>
                <input name="<?= e($name) ?>" required value="<?= e((string) $default) ?>"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- Payment -->
        <section class="glass-card rounded-xl p-6">
          <h2 class="text-lg font-bold mb-4">Payment</h2>
          <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium mb-1">Card Number</label>
              <!-- Audit pass 3: autocomplete=off + never name this field so it
                   cannot be captured by proxies/extensions; demo-only, not stored. -->
              <input name="cardNumber" inputmode="numeric" placeholder="1234 5678 9012 3456" maxlength="19" required
                     autocomplete="off" data-lpignore="true"
                     class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Expiry (MM/YY)</label>
              <input name="expiry" placeholder="08/27" pattern="\d{2}/\d{2}" required autocomplete="off" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">CVC</label>
              <input name="cvc" type="password" inputmode="numeric" placeholder="123" maxlength="4" required autocomplete="off" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
            </div>
          </div>
          <p class="text-xs text-gray-400 mt-3">Demo checkout — no real charge is made and card data is never stored.</p>
        </section>
      </div>

      <!-- Summary -->
      <aside class="glass-card rounded-xl p-6 h-fit">
        <h2 class="text-lg font-bold mb-4">Order Summary</h2>
        <ul class="space-y-2 text-sm max-h-48 overflow-y-auto mb-4">
          <?php foreach ($items as $item): $p = $item['product']; ?>
            <li class="flex justify-between gap-2">
              <span class="truncate"><?= e($p['name']) ?> × <?= (int) $item['quantity'] ?></span>
              <span class="shrink-0 font-medium"><?= money($p['price'] * $item['quantity']) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
        <dl class="space-y-2 text-sm border-t glass-divider pt-3">
          <div class="flex justify-between"><dt>Subtotal</dt><dd><?= money($subtotal) ?></dd></div>
          <div class="flex justify-between"><dt>Shipping</dt><dd><?= $shipping === 0.0 ? 'Free' : money($shipping) ?></dd></div>
          <div class="flex justify-between"><dt>Tax</dt><dd><?= money($tax) ?></dd></div>
          <div class="flex justify-between font-bold text-base pt-2 border-t glass-divider"><dt>Total</dt><dd><?= money($total) ?></dd></div>
        </dl>
        <button class="mt-5 w-full btn-primary rounded-xl font-semibold py-3">Place Order</button>
      </aside>
    </form>
  <?php endif; ?>
</div>
