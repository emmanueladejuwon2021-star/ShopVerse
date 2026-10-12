<?php /* Products — port of src/pages/Products.tsx (filters moved to GET query params) */
      $opts = [
          'category' => $_GET['category'] ?? 'All',
          'search'   => $_GET['search'] ?? '',
          'maxPrice' => $_GET['maxPrice'] ?? '',
          'minRating'=> $_GET['minRating'] ?? '',
          'sort'     => $_GET['sort'] ?? 'featured',
      ];
      $items = filter_products($opts);
?>
<div class="max-w-7xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-6">Shop</h1>

  <!-- Filter bar -->
  <form method="get" action="/products" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-8 grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <input type="search" name="search" placeholder="Search…" value="<?= e($opts['search']) ?>"
           class="rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
    <select name="category" class="rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
      <?php foreach (get_categories() as $c): ?>
        <option <?= $opts['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="maxPrice" class="rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
      <option value="">Max price</option>
      <?php foreach ([50, 100, 200, 500] as $mp): ?>
        <option value="<?= $mp ?>" <?= (string) $opts['maxPrice'] === (string) $mp ? 'selected' : '' ?>>Under $<?= $mp ?></option>
      <?php endforeach; ?>
    </select>
    <select name="minRating" class="rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
      <option value="">Rating</option>
      <?php foreach ([4.5, 4, 3] as $r): ?>
        <option value="<?= $r ?>" <?= (string) $opts['minRating'] === (string) $r ? 'selected' : '' ?>><?= $r ?>★ &amp; up</option>
      <?php endforeach; ?>
    </select>
    <div class="flex gap-2">
      <select name="sort" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
        <?php foreach (['featured' => 'Featured', 'price-low' => 'Price: Low to High', 'price-high' => 'Price: High to Low', 'rating' => 'Top Rated', 'newest' => 'Newest'] as $v => $l): ?>
          <option value="<?= $v ?>" <?= $opts['sort'] === $v ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-semibold">Apply</button>
    </div>
  </form>

  <p class="text-sm text-gray-500 mb-4"><?= count($items) ?> product<?= count($items) === 1 ? '' : 's' ?> found</p>

  <?php if (!$items): ?>
    <div class="text-center py-20 text-gray-500">
      <p class="text-5xl mb-4">🔍</p>
      <p class="font-semibold">No products match your filters.</p>
      <a href="/products" class="text-indigo-600 hover:underline text-sm mt-2 inline-block">Clear filters</a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <?php foreach ($items as $p): ?>
        <?php include __DIR__ . '/../partials/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
