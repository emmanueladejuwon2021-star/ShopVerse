<?php
/**
 * Wishlist View
 * Displays all saved products with quick-add to cart and removal controls.
 */
$items = wishlist_items();
?>
<div class="max-w-7xl mx-auto px-4 py-10 lg:py-12">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Saved Items</h1>
      <p class="text-sm text-gray-500 mt-1"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?> in your personal wishlist</p>
    </div>
    <?php if ($items): ?>
      <a href="/products" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
        Continue Shopping →
      </a>
    <?php endif; ?>
  </div>

  <?php if (!$items): ?>
    <div class="max-w-md mx-auto text-center py-20 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/60 p-8 shadow-sm">
      <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center text-3xl">
        ❤️
      </div>
      <h2 class="text-xl font-bold text-gray-900 dark:text-white">Your wishlist is empty</h2>
      <p class="text-gray-500 text-sm mt-2 leading-relaxed">
        Explore our curated collection and click the heart icon on any product to save your favorite items for later.
      </p>
      <a href="/products" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 shadow-lg shadow-indigo-600/20 transition-all">
        Browse Catalog
      </a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <?php foreach ($items as $p): ?>
        <?php include __DIR__ . '/../partials/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
