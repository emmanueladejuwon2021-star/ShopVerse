<?php
/**
 * Production Orders View
 * Displays customer purchase history with status badges, itemized line items, and receipt links.
 */
$orders = array_reverse(user_orders());
?>
<div class="max-w-5xl mx-auto px-4 py-10 lg:py-12">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">My Orders</h1>
      <p class="text-sm text-gray-500 mt-1">Review your recent order history and track shipments</p>
    </div>
    <a href="/products" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
      Browse Catalog →
    </a>
  </div>

  <?php if (!$orders): ?>
    <div class="max-w-md mx-auto text-center py-20 bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm">
      <div class="w-18 h-18 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700/60 flex items-center justify-center text-3xl">📦</div>
      <h2 class="text-xl font-bold text-gray-900 dark:text-white">No orders yet</h2>
      <p class="text-gray-500 text-sm mt-1 leading-relaxed">Once you place an order, your tracking details and receipts will appear right here.</p>
      <a href="/products" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-6 py-3 transition-colors shadow-md">
        Start Shopping Now
      </a>
    </div>
  <?php else: ?>
    <div class="space-y-6">
      <?php foreach ($orders as $o):
        $status = strtolower($o['status'] ?? 'processing');
        $statusColors = [
            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
            'shipped' => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300',
            'delivered' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
        ];
        $colorClass = $statusColors[$status] ?? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300';
      ?>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden transition-all hover:border-gray-200 dark:hover:border-gray-700">
          <!-- Card Header -->
          <div class="bg-gray-50/70 dark:bg-gray-900/40 p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="text-xs uppercase font-bold text-gray-400">Order</span>
                <span class="font-mono font-bold text-sm text-gray-900 dark:text-white"><?= e($o['id']) ?></span>
              </div>
              <p class="text-xs text-gray-500">Placed on <?= date('F j, Y', strtotime($o['date'] ?? 'now')) ?></p>
            </div>

            <div class="flex items-center gap-3">
              <span class="text-xs font-bold px-3 py-1 rounded-full <?= $colorClass ?>">
                <?= ucfirst(e($status)) ?>
              </span>
              <a href="/order-success?id=<?= urlencode((string) $o['id']) ?>"
                 class="rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-semibold px-3 py-1.5 transition-colors">
                View Receipt →
              </a>
            </div>
          </div>

          <!-- Items in Order -->
          <div class="p-5 sm:p-6 divide-y divide-gray-100 dark:divide-gray-700/60">
            <?php foreach ($o['items'] as $item): $p = $item['product']; ?>
              <div class="py-3.5 first:pt-0 last:pb-0 flex items-center gap-4">
                <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"
                     onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
                     class="w-14 h-14 rounded-xl object-cover bg-gray-100 dark:bg-gray-900 shrink-0 border border-gray-200/50 dark:border-gray-700">
                <div class="flex-1 min-w-0">
                  <a href="/product/<?= e($p['id']) ?>" class="font-semibold text-xs sm:text-sm text-gray-900 dark:text-white truncate block hover:text-indigo-600 transition-colors">
                    <?= e($p['name']) ?>
                  </a>
                  <p class="text-[11px] text-gray-400 mt-0.5"><?= e($p['category']) ?> · Qty: <?= (int) $item['quantity'] ?></p>
                </div>
                <span class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white shrink-0">
                  <?= money($p['price'] * $item['quantity']) ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Card Footer -->
          <div class="bg-gray-50/50 dark:bg-gray-900/30 p-5 sm:p-6 border-t border-gray-100 dark:border-gray-700/60 flex flex-wrap justify-between items-center gap-3 text-xs">
            <p class="text-gray-500">
              Shipped to: <strong class="text-gray-700 dark:text-gray-300"><?= e($o['shippingAddress']['street'] ?? '') ?>, <?= e($o['shippingAddress']['city'] ?? '') ?></strong>
            </p>
            <div class="flex items-center gap-2">
              <span class="text-gray-500">Total:</span>
              <span class="font-extrabold text-sm text-indigo-600 dark:text-indigo-400"><?= money($o['total']) ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
