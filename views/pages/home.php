<?php /* Home — compact bento layout: hero + featured in one block, categories + trust merged */
      $featured = featured_products(4);
?>
<!-- Hero + Featured (single compact band) -->
<section class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 text-white">
  <div class="max-w-7xl mx-auto px-4 py-10 lg:py-12 grid lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] gap-8 items-center">
    <div class="text-center lg:text-left">
      <h1 class="text-3xl md:text-4xl font-extrabold leading-tight">Discover Products You'll Love</h1>
      <p class="mt-3 text-indigo-100">Curated premium goods at honest prices. Free shipping on orders over $50.</p>
      <div class="mt-5 flex flex-wrap justify-center lg:justify-start gap-3">
        <a href="/products" class="rounded-lg bg-white text-indigo-700 font-semibold px-5 py-2.5 hover:bg-indigo-50">Shop Now</a>
        <a href="/products?category=Electronics" class="rounded-lg border border-white/60 font-semibold px-5 py-2.5 hover:bg-white/10">Browse Electronics</a>
      </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <?php foreach ($featured as $hp): ?>
        <a href="/product/<?= e($hp['id']) ?>" title="<?= e($hp['name']) ?>"
           class="relative rounded-xl overflow-hidden shadow-lg hover:scale-[1.02] transition-transform aspect-square">
          <img src="<?= e($hp['image']) ?>" alt="<?= e($hp['name']) ?>" class="w-full h-full object-cover" loading="lazy">
          <span class="absolute inset-x-0 bottom-0 bg-black/50 text-white text-xs font-medium px-2 py-1 truncate"><?= e($hp['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Categories + Trust strip (merged compact band) -->
<section class="max-w-7xl mx-auto px-4 py-8 grid gap-6 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)]">
  <div>
    <h2 class="text-lg font-bold mb-3">Shop by Category</h2>
    <div class="flex flex-wrap gap-2">
      <?php foreach (array_slice(get_categories(), 1) as $cat): ?>
        <a href="/products?category=<?= urlencode($cat) ?>"
           class="rounded-full bg-white dark:bg-gray-800 shadow-sm hover:shadow-md hover:bg-indigo-50 dark:hover:bg-gray-700 px-4 py-2 text-sm font-semibold transition-shadow">
          <?= e($cat) ?>
        </a>
      <?php endforeach; ?>
    </div>
    <h2 class="text-lg font-bold mt-6 mb-3">Why ShopVerse</h2>
    <div class="grid grid-cols-3 gap-3">
      <div class="rounded-xl bg-white dark:bg-gray-800 p-3 shadow-sm text-center">
        <p class="text-2xl leading-none">🚚</p><h3 class="font-bold text-sm mt-1.5">Free Shipping</h3>
        <p class="text-xs text-gray-500 mt-0.5 hidden sm:block">On orders over $50</p>
      </div>
      <div class="rounded-xl bg-white dark:bg-gray-800 p-3 shadow-sm text-center">
        <p class="text-2xl leading-none">↩️</p><h3 class="font-bold text-sm mt-1.5">Easy Returns</h3>
        <p class="text-xs text-gray-500 mt-0.5 hidden sm:block">30-day policy</p>
      </div>
      <div class="rounded-xl bg-white dark:bg-gray-800 p-3 shadow-sm text-center">
        <p class="text-2xl leading-none">🔒</p><h3 class="font-bold text-sm mt-1.5">Secure Checkout</h3>
        <p class="text-xs text-gray-500 mt-0.5 hidden sm:block">Data protected</p>
      </div>
    </div>
  </div>
  <div>
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-bold">Featured Products</h2>
      <a href="/products" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline text-sm">View all →</a>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <?php foreach ($featured as $p): ?>
        <?php include __DIR__ . '/../partials/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
