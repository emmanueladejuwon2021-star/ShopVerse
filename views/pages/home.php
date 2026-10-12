<?php /* Home — mobile-first layout: stacked hero, category rail, featured grid, sticky cart bar */
      $featured = featured_products(6);
      $cats     = array_slice(get_categories(), 1); // drop "All"
?>
<style>
  .hero-orb{position:absolute;border-radius:9999px;filter:blur(60px);pointer-events:none}
  .btn-shine{position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s}
  .btn-shine::after{content:"";position:absolute;top:0;left:-80%;width:50%;height:100%;background:linear-gradient(120deg,transparent,rgba(255,255,255,.55),transparent);transform:skewX(-20deg)}
  .btn-shine:hover::after{animation:shine .8s ease forwards}
  @keyframes shine{to{left:130%}}
  .tile-zoom img{transition:transform .5s cubic-bezier(.4,0,.2,1)}
  .tile-zoom:hover img{transform:scale(1.07)}
  .cat-chip{transition:transform .2s,box-shadow .2s}
  .cat-chip:active,.cat-chip:hover{transform:translateY(-2px)}
  .rail{-webkit-overflow-scrolling:touch;scroll-snap-type:x proximity}
  .rail>*{scroll-snap-align:start}
  .trust-icon{display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;font-size:1.15rem;background:linear-gradient(135deg,#eef2ff,#faf5ff)}
  .dark .trust-icon{background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(168,85,247,.18))}
  @media (prefers-reduced-motion:reduce){.btn-shine::after,.tile-zoom img{animation:none;transition:none}}
</style>

<!-- 1 · Hero — single column on phones, split from md up -->
<section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 text-white">
  <div class="hero-orb -top-20 -left-20 h-64 w-64 bg-white/10"></div>
  <div class="hero-orb -right-24 -bottom-24 h-72 w-72 bg-purple-400/20"></div>
  <div class="relative max-w-7xl mx-auto px-4 pt-10 pb-12 lg:py-14 grid gap-8 md:grid-cols-2 md:items-center">
    <div class="text-center md:text-left">
      <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 border border-white/20 px-3 py-1 text-xs font-semibold tracking-wide backdrop-blur-sm">
        ✨ New season drops just landed
      </span>
      <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight tracking-tight">
        Discover Products<br class="hidden sm:block">
        <span class="bg-gradient-to-r from-amber-200 via-pink-200 to-indigo-200 bg-clip-text text-transparent">You'll Love</span>
      </h1>
      <p class="mt-3 text-indigo-100 max-w-md mx-auto md:mx-0">Curated premium goods at honest prices. Free shipping on orders over $50.</p>
      <div class="mt-6 flex flex-col sm:flex-row justify-center md:justify-start gap-3">
        <a href="/products" class="btn-shine rounded-xl bg-white text-indigo-700 font-semibold px-6 py-3 text-center shadow-lg shadow-indigo-900/20 hover:bg-indigo-50">Shop Now</a>
        <a href="/products?category=Electronics" class="rounded-xl border border-white/60 font-semibold px-6 py-3 text-center hover:bg-white/10 transition-colors">Browse Electronics</a>
      </div>
    </div>
    <!-- Featured tiles: 2-up on phones, 4-up with staggered offset from sm -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <?php foreach ($featured as $i => $hp): ?>
        <a href="/product/<?= e($hp['id']) ?>" title="<?= e($hp['name']) ?>"
           class="tile-zoom relative rounded-xl overflow-hidden shadow-lg ring-1 ring-white/20 active:scale-[0.98] transition-transform aspect-square <?= $i % 2 ? 'sm:translate-y-3' : '' ?>">
          <img src="<?= e($hp['image']) ?>" alt="<?= e($hp['name']) ?>" class="w-full h-full object-cover" loading="lazy">
          <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent text-white text-xs font-medium px-2 pt-4 pb-1.5 truncate"><?= e($hp['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 2 · Categories — swipeable horizontal rail on mobile, wraps on desktop -->
<section class="max-w-7xl mx-auto px-4 pt-8">
  <h2 class="text-lg font-bold mb-3 tracking-tight">Shop by Category</h2>
  <div class="rail flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 md:flex-wrap md:overflow-visible md:mx-0 md:px-0">
    <a href="/products"
       class="cat-chip shrink-0 rounded-full bg-indigo-600 text-white px-5 py-2.5 text-sm font-semibold shadow-sm">All</a>
    <?php foreach ($cats as $cat): ?>
      <a href="/products?category=<?= urlencode($cat) ?>"
         class="cat-chip shrink-0 rounded-full glass-card shadow-sm hover:shadow-md active:bg-indigo-50 dark:active:bg-gray-700 px-5 py-2.5 text-sm font-semibold">
        <?= e($cat) ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- 3 · Trust row — always 3-up, compact icons + short copy for small screens -->
<section class="max-w-7xl mx-auto px-4 py-6">
  <div class="grid grid-cols-3 gap-3">
    <div class="rounded-xl glass-card p-3 shadow-sm text-center">
      <span class="trust-icon mx-auto">🚚</span><h3 class="font-bold text-xs sm:text-sm mt-2">Free Shipping</h3>
      <p class="text-[11px] sm:text-xs text-glass-muted dark:text-gray-400 mt-0.5">Over $50</p>
    </div>
    <div class="rounded-xl glass-card p-3 shadow-sm text-center">
      <span class="trust-icon mx-auto">↩️</span><h3 class="font-bold text-xs sm:text-sm mt-2">Easy Returns</h3>
      <p class="text-[11px] sm:text-xs text-glass-muted dark:text-gray-400 mt-0.5">30-day policy</p>
    </div>
    <div class="rounded-xl glass-card p-3 shadow-sm text-center">
      <span class="trust-icon mx-auto">🔒</span><h3 class="font-bold text-xs sm:text-sm mt-2">Secure Checkout</h3>
      <p class="text-[11px] sm:text-xs text-glass-muted dark:text-gray-400 mt-0.5">Data protected</p>
    </div>
  </div>
</section>

<!-- 4 · Featured products — 2 cols phone → 3 tablet → 6 desktop -->
<section class="max-w-7xl mx-auto px-4 pb-14">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-bold tracking-tight">Featured Products</h2>
    <a href="/products" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline text-sm">View all →</a>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
    <?php foreach ($featured as $p): ?>
      <?php include __DIR__ . '/../partials/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>

<?php if (cart_total_items() > 0): $subtotal = cart_total_price(); ?>
<!-- 5 · Sticky cart bar — mobile only; tap target above the thumb zone -->
<div class="sticky bottom-0 z-30 md:hidden -mt-2">
  <div class="m-3 rounded-2xl bg-indigo-600 text-white shadow-xl shadow-indigo-900/30 px-4 py-3 flex items-center gap-3">
    <span class="shrink-0 grid place-items-center w-8 h-8 rounded-full bg-white/20 text-sm font-bold"><?= cart_total_items() ?></span>
    <span class="flex-1 min-w-0 text-sm font-semibold truncate"><?= money($subtotal) ?> in your cart</span>
    <a href="/cart" class="shrink-0 rounded-lg bg-white/15 border border-white/30 px-3 py-1.5 text-sm font-semibold">View cart</a>
  </div>
</div>
<?php endif; ?>
