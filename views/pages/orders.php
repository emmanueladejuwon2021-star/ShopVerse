<?php /* Orders — port of src/pages/Orders.tsx (now scoped per logged-in user) */
      $orders = array_reverse(user_orders()); // newest first
?>
<div class="max-w-4xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-8">My Orders</h1>

  <?php if (!$orders): ?>
    <div class="text-center py-20">
      <p class="text-6xl mb-4">📦</p>
      <h2 class="text-xl font-semibold">No orders yet</h2>
      <p class="text-gray-500 mt-1">Your placed orders will appear here.</p>
      <a href="/products" class="mt-4 inline-block rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3">Start Shopping</a>
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($orders as $o):
        $statusColors = [
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            'shipped' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
            'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        ];
      ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
              <span class="font-bold"><?= e($o['id']) ?></span>
              <span class="text-sm text-gray-500 ml-3"><?= date('M j, Y', strtotime($o['date'])) ?></span>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full <?= $statusColors[$o['status']] ?? '' ?>"><?= ucfirst(e($o['status'])) ?></span>
          </div>

          <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-700 text-sm">
            <?php foreach ($o['items'] as $item): $p = $item['product']; ?>
              <li class="flex items-center gap-3 py-2">
                <img src="<?= e($p['image']) ?>" alt="" class="w-10 h-10 rounded object-cover">
                <a href="/product/<?= e($p['id']) ?>" class="flex-1 truncate hover:text-indigo-600"><?= e($p['name']) ?></a>
                <span class="text-gray-500">× <?= (int) $item['quantity'] ?></span>
                <span class="font-medium w-20 text-right"><?= money($p['price'] * $item['quantity']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>

          <div class="mt-4 flex flex-wrap justify-between items-center gap-2 text-sm">
            <p class="text-gray-500">
              Ships to: <?= e($o['shippingAddress']['street']) ?>, <?= e($o['shippingAddress']['city']) ?> <?= e($o['shippingAddress']['zipCode']) ?>
            </p>
            <p class="font-bold text-base">Total: <?= money($o['total']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
