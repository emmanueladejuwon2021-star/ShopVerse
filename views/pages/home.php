<?php /* Home — compact bento layout: hero + featured in one block, categories + trust merged */
      $featured = featured_products(4);
?>
<style>
  .hero-orb{position:absolute;border-radius:9999px;filter:blur(60px);pointer-events:none}
  .hero-badge{animation:pulse-soft 3s ease-in-out infinite}
  .btn-shine{position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s}
  .btn-shine::after{content:"";position:absolute;top:0;left:-80%;width:50%;height:100%;background:linear-gradient(120deg,transparent,rgba(255,255,255,.55),transparent);transform:skewX(-20deg)}
  .btn-shine:hover::after{animation:shine .8s ease forwards}
  @keyframes shine{to{left:130%}}
  .tile-zoom img{transition:transform .5s cubic-bezier(.4,0,.2,1)}
  .tile-zoom:hover img{transform:scale(1.07)}
  .cat-chip{transition:transform .2s,box-shadow .2s,background-color .2s}
  .cat-chip:hover{transform:translateY(-2px)}
  .trust-icon{display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;font-size:1.15rem;background:linear-gradient(135deg,#eef2ff,#faf5ff)}
  .dark .trust-icon{background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(168,85,247,.18))}
  @media (prefers-reduced-motion:reduce){.hero-badge,.btn-shine::after,.tile-zoom img{animation:none;transition:none}}
</style>

<!-- Hero + Featured (single compact band) -->
<section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 text-white">
  <div class="hero-orb animate-float -top-20 -left-20 h-64 w-64 bg-white/10"></div>
  <div class="hero-orb -right-24 -bottom-24 h-72 w-72 bg-purple-400/20"></div>
  <div class="relative max-w-7xl mx-auto px-4 py-10 lg:py-12 grid lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] gap-8 items-center">
    <div class="text-center lg:text-left">
      <span class="hero-badge inline-flex items-center gap-1.5 rounded-full bg-white/15 border border-white/20 px-3 py-1 text-xs font-semibold tracking-wide backdrop-blur-sm">
        ✨ New season drops just landed
      </span>
      <h1 class="mt-3 text-3xl md:text-4xl font-extrabold leading-tight tracking-tight">
        Discover Products<br class="hidden md:block">
        <span class="bg-gradient-to-r from-amber-200 via-pink-200 to-indigo-200 bg-clip-text text-transparent">You'll Love</span>
      </h1>
      <p class="mt-3 text-indigo-100">Curated premium goods at honest prices. Free shipping on orders over $50.</p>
      <div class="mt-5 flex flex-wrap justify-center lg:justify-start gap-3">
        <a href="/products" class="btn-shine rounded-lg bg-white text-indigo-700 font-semibold px-5 py-2.5 shadow-lg shadow-indigo-900/20 hover:bg-indigo-50 hover:-translate-y-0.5">Shop Now</a>
        <a href="/products?category=Electronics" class="rounded-lg border border-white/60 font-semibold px-5 py-2.5 hover:bg-white/10 transition-colors">Browse Electronics</a>
      </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <?php foreach ($featured as $i => $hp): ?>
        <a href="/product/<?= e($hp['id']) ?>" title="<?= e($hp['name']) ?>"
           class="tile-zoom relative rounded-xl overflow-hidden shadow-lg ring-1 ring-white/20 hover:scale-[1.03] hover:shadow-2xl transition-transform aspect-square <?= $i % 2 ? 'sm:translate-y-3' : '' ?>">
          <img src="<?= e($hp['image']) ?>" alt="<?= e($hp['name']) ?>" class="w-full h-full object-cover" loading="lazy">
          <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent text-white text-xs font-medium px-2 pt-4 pb-1.5 truncate"><?= e($hp['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Categories + Trust strip (merged compact band) -->
<section class="max-w-7xl mx-auto px-4 py-8 grid gap-6 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)]">
  <div>
    <h2 class="text-lg font-bold mb-3 tracking-tight">Shop by Category</h2>
    <div class="flex flex-wrap gap-2">
      <?php foreach (array_slice(get_categories(), 1) as $cat): ?>
        <a href="/products?category=<?= urlencode($cat) ?>"
           class="cat-chip rounded-full bg-white dark:bg-gray-800 shadow-sm hover:shadow-md hover:bg-indigo-50 dark:hover:bg-gray-700 px-4 py-2 text-sm font-semibold">
          <?= e($cat) ?>
        </a>
      <?php endforeach; ?>
    </div>
    <h2 class="text-lg font-bold mt-6 mb-3 tracking-tight">Why ShopVerse</h2>
    <div class="grid grid-cols-3 gap-3">
      <div class="card-hover rounded-xl bg-white dark:bg-gray-800 p-3 shadow-sm text-center">
        <span class="trust-icon mx-auto">🚚</span><h3 class="font-bold text-sm mt-2">Free Shipping</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 hidden sm:block">On orders over $50</p>
      </div>
      <div class="card-hover rounded-xl bg-white dark:bg-gray-800 p-3 shadow-sm text-center">
        <span class="trust-icon mx-auto">↩️</span><h3 class="font-bold text-sm mt-2">Easy Returns</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 hidden sm:block">30-day policy</p>
      </div>
      <div class="card-hover rounded-xl bg-white dark:bg-gray-800 p-3 shadow-sm text-center">
        <span class="trust-icon mx-auto">🔒</span><h3 class="font-bold text-sm mt-2">Secure Checkout</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 hidden sm:block">Data protected</p>
      </div>
    </div>
  </div>
  <div>
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-bold tracking-tight">Featured Products</h2>
      <a href="/products" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline text-sm">View all →</a>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <?php foreach ($featured as $p): ?>
        <?php include __DIR__ . '/../partials/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
