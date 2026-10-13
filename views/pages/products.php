<?php
/**
 * Production Products Catalog View
 * Features category pill carousel, multi-attribute filter toolbar, stock/sale toggles, and responsive grid.
 */
$opts = [
    'category'    => $_GET['category'] ?? 'All',
    'search'      => $_GET['search'] ?? '',
    'maxPrice'    => $_GET['maxPrice'] ?? '',
    'minRating'   => $_GET['minRating'] ?? '',
    'inStockOnly' => !empty($_GET['inStockOnly']),
    'onSaleOnly'  => !empty($_GET['onSaleOnly']),
    'sort'        => $_GET['sort'] ?? 'featured',
];
$items = filter_products($opts);
$categories = get_categories();
$hasActiveFilters = $opts['category'] !== 'All' || $opts['search'] !== '' || $opts['maxPrice'] !== '' || $opts['minRating'] !== '' || $opts['inStockOnly'] || $opts['onSaleOnly'];
?>
<div class="max-w-7xl mx-auto px-4 py-8 lg:py-12">
  <!-- Header & Title -->
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
      <span class="text-xs uppercase tracking-widest font-bold text-indigo-600 dark:text-indigo-400">Curated Catalog</span>
      <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-1">
        <?php if ($opts['category'] !== 'All'): ?>
          <?= e($opts['category']) ?>
        <?php elseif (!empty($opts['search'])): ?>
          Search Results for "<?= e($opts['search']) ?>"
        <?php else: ?>
          Explore All Products
        <?php endif; ?>
      </h1>
      <p class="text-sm text-gray-500 mt-1">
        Showing <?= count($items) ?> premium product<?= count($items) === 1 ? '' : 's' ?>
      </p>
    </div>

    <!-- Category Pills Quick Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 scrollbar-none">
      <?php foreach ($categories as $cat):
        $isActive = $opts['category'] === $cat;
        $catUrl = $cat === 'All' ? '/products' : '/products?category=' . urlencode($cat);
      ?>
        <a href="<?= e($catUrl) ?>"
           class="rounded-full px-4 py-2 text-xs font-semibold whitespace-nowrap transition-all <?= $isActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700/60' ?>">
          <?= e($cat) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Filter & Sort Toolbar -->
  <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-sm mb-8 space-y-4">
    <form method="get" action="/products" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
      <!-- Search Input -->
      <div class="relative">
        <input type="search" name="search" placeholder="Search keywords…" value="<?= e($opts['search']) ?>"
               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 pl-9 pr-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>

      <!-- Category Select -->
      <select name="category" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        <?php foreach ($categories as $c): ?>
          <option value="<?= e($c) ?>" <?= $opts['category'] === $c ? 'selected' : '' ?>><?= e($c) ?> Category</option>
        <?php endforeach; ?>
      </select>

      <!-- Max Price Select -->
      <select name="maxPrice" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        <option value="">Any Price</option>
        <?php foreach ([50, 100, 200, 300, 600] as $mp): ?>
          <option value="<?= $mp ?>" <?= (string) $opts['maxPrice'] === (string) $mp ? 'selected' : '' ?>>Under $<?= $mp ?></option>
        <?php endforeach; ?>
      </select>

      <!-- Min Rating Select -->
      <select name="minRating" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        <option value="">Any Rating</option>
        <?php foreach ([4.8, 4.5, 4.0] as $r): ?>
          <option value="<?= $r ?>" <?= (string) $opts['minRating'] === (string) $r ? 'selected' : '' ?>><?= $r ?>★ &amp; Above</option>
        <?php endforeach; ?>
      </select>

      <!-- Sorting Select -->
      <select name="sort" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        <?php foreach ([
          'featured' => 'Featured Sorting',
          'rating' => 'Highest Rated',
          'price-low' => 'Price: Low to High',
          'price-high' => 'Price: High to Low',
          'newest' => 'Newest Arrivals'
        ] as $val => $lbl): ?>
          <option value="<?= $val ?>" <?= $opts['sort'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
        <?php endforeach; ?>
      </select>

      <!-- Fast Toggle Checkboxes & Submit -->
      <div class="sm:col-span-2 lg:col-span-5 flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100 dark:border-gray-700/50">
        <div class="flex items-center gap-5">
          <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
            <input type="checkbox" name="inStockOnly" value="1" <?= $opts['inStockOnly'] ? 'checked' : '' ?>
                   class="rounded text-indigo-600 focus:ring-indigo-500">
            <span>In Stock Only</span>
          </label>
          <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
            <input type="checkbox" name="onSaleOnly" value="1" <?= $opts['onSaleOnly'] ? 'checked' : '' ?>
                   class="rounded text-indigo-600 focus:ring-indigo-500">
            <span>On Sale Deals</span>
          </label>
        </div>

        <div class="flex items-center gap-2">
          <?php if ($hasActiveFilters): ?>
            <a href="/products" class="rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
              Reset Filters
            </a>
          <?php endif; ?>
          <button type="submit" class="rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-5 py-2 shadow-sm transition-all">
            Apply Filters
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Product Grid -->
  <?php if (!$items): ?>
    <div class="max-w-md mx-auto text-center py-20 bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm">
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-3xl">🔍</div>
      <h3 class="text-lg font-bold text-gray-900 dark:text-white">No products found</h3>
      <p class="text-gray-500 text-sm mt-1">We couldn't find any products matching your specific criteria.</p>
      <a href="/products" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-5 py-2.5 transition-colors">
        Clear All Filters
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
