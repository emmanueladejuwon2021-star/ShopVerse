<?php /* Home — port of src/pages/Home.tsx (hero, categories, featured, features strip) */ 
      $featured = featured_products(4);
?>
<!-- Hero -->
<section class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 text-white">
  <div class="max-w-7xl mx-auto px-4 py-20 md:py-28 grid md:grid-cols-2 gap-10 items-center">
    <div>
      <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">Discover Products You'll Love</h1>
      <p class="mt-4 text-indigo-100 text-lg">Curated premium goods at honest prices. Free shipping on orders over $50.</p>
      <div class="mt-8 flex gap-4">
        <a href="/products" class="rounded-lg bg-white text-indigo-700 font-semibold px-6 py-3 hover:bg-indigo-50">Shop Now</a>
        <a href="/products?category=Electronics" class="rounded-lg border border-white/60 font-semibold px-6 py-3 hover:bg-white/10">Browse Electronics</a>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <?php foreach (array_slice(all_products(), 0, 4) as $hp): ?>
        <a href="/product/<?= e($hp['id']) ?>" class="rounded-xl overflow-hidden shadow-lg hover:scale-[1.02] transition-transform">
          <img src="<?= e($hp['image']) ?>" alt="<?= e($hp['name']) ?>" class="w-full h-40 object-cover" loading="lazy">
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="max-w-7xl mx-auto px-4 py-12">
  <h2 class="text-2xl font-bold mb-6">Shop by Category</h2>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
    <?php foreach (array_slice(get_categories(), 1) as $cat): ?>
      <a href="/products?category=<?= urlencode($cat) ?>"
         class="rounded-xl bg-white dark:bg-gray-800 shadow-sm hover:shadow-md p-6 text-center font-semibold transition-shadow">
        <?= e($cat) ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Featured -->
<section class="max-w-7xl mx-auto px-4 py-12">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Featured Products</h2>
    <a href="/products" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline text-sm">View all →</a>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach ($featured as $p): ?>
      <?php include __DIR__ . '/../partials/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>

<!-- Trust strip -->
<section class="max-w-7xl mx-auto px-4 pb-12 grid sm:grid-cols-3 gap-6 text-center">
  <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm">
    <p class="text-3xl">🚚</p><h3 class="font-bold mt-2">Free Shipping</h3>
    <p class="text-sm text-gray-500 mt-1">On all orders over $50</p>
  </div>
  <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm">
    <p class="text-3xl">↩️</p><h3 class="font-bold mt-2">Easy Returns</h3>
    <p class="text-sm text-gray-500 mt-1">30-day return policy</p>
  </div>
  <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm">
    <p class="text-3xl">🔒</p><h3 class="font-bold mt-2">Secure Checkout</h3>
    <p class="text-sm text-gray-500 mt-1">Your data is protected</p>
  </div>
</section>
