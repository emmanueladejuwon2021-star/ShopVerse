<?php
/**
 * Production Home View
 * Features high-conversion hero, visual category showcase, bestsellers grid,
 * confidence trust badges, customer social proof, and promo banner.
 */
$featured = featured_products(8);
$categories = array_slice(get_categories(), 1);
?>
<style>
  .hero-orb { position: absolute; border-radius: 9999px; filter: blur(70px); pointer-events: none; }
  .btn-shine { position: relative; overflow: hidden; }
  .btn-shine::after { content: ""; position: absolute; top: 0; left: -80%; width: 50%; height: 100%; background: linear-gradient(120deg, transparent, rgba(255,255,255,.4), transparent); transform: skewX(-20deg); }
  .btn-shine:hover::after { animation: shine .75s ease forwards; }
  @keyframes shine { to { left: 130%; } }
</style>

<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-950 text-white py-16 lg:py-24">
  <div class="hero-orb -top-24 -left-24 h-96 w-96 bg-indigo-500/20"></div>
  <div class="hero-orb -bottom-24 -right-24 h-96 w-96 bg-purple-500/20"></div>

  <div class="relative max-w-7xl mx-auto px-4 grid lg:grid-cols-12 gap-12 items-center">
    <!-- Left Hero Copy -->
    <div class="lg:col-span-7 text-center lg:text-left space-y-6">
      <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/20 px-3.5 py-1 text-xs font-semibold tracking-wide backdrop-blur-md">
        <span>⚡</span> <span>Spring Drop Live · Use code <strong class="underline font-bold text-amber-300">SHOP10</strong></span>
      </div>

      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.1]">
        Next-Level Gear.<br>
        <span class="bg-gradient-to-r from-amber-200 via-rose-200 to-indigo-200 bg-clip-text text-transparent">
          Crafted to Perfection.
        </span>
      </h1>

      <p class="text-base sm:text-lg text-indigo-100/90 max-w-xl mx-auto lg:mx-0 font-normal leading-relaxed">
        Discover precision electronics, timeless apparel, and premium everyday essentials designed for high performance and durability.
      </p>

      <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-2">
        <a href="/products" class="btn-shine rounded-2xl bg-white text-indigo-950 font-bold px-7 py-4 shadow-xl shadow-indigo-950/30 hover:bg-indigo-50 hover:-translate-y-0.5 transition-all text-sm">
          Shop All Products →
        </a>
        <a href="/products?onSaleOnly=1" class="rounded-2xl border border-white/30 hover:border-white/60 bg-white/5 backdrop-blur-sm font-semibold px-6 py-4 hover:bg-white/10 transition-all text-sm">
          Browse On-Sale Deals
        </a>
      </div>

      <!-- Micro Trust Metrics -->
      <div class="pt-4 flex items-center justify-center lg:justify-start gap-8 text-xs text-indigo-200/80">
        <div class="flex items-center gap-2">
          <span class="text-emerald-400 font-bold text-sm">✓</span> 40,000+ Happy Customers
        </div>
        <div class="flex items-center gap-2">
          <span class="text-emerald-400 font-bold text-sm">★</span> 4.9/5 Average Rating
        </div>
        <div class="flex items-center gap-2">
          <span class="text-emerald-400 font-bold text-sm">🚚</span> Free 2-Day Shipping
        </div>
      </div>
    </div>

    <!-- Right Bento Showcase -->
    <div class="lg:col-span-5 grid grid-cols-2 gap-3.5">
      <?php foreach (array_slice($featured, 0, 4) as $idx => $hp): ?>
        <a href="/product/<?= e($hp['id']) ?>"
           class="group relative rounded-2xl overflow-hidden shadow-lg border border-white/15 aspect-[4/5] bg-gray-900 <?= $idx % 2 === 1 ? 'translate-y-4' : '' ?>">
          <img src="<?= e($hp['image']) ?>" alt="<?= e($hp['name']) ?>"
               onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
               class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-500">
          <div class="absolute inset-0 bg-gradient-to-t from-gray-950/80 via-transparent to-transparent"></div>
          <div class="absolute bottom-3 inset-x-3 text-left">
            <span class="text-[10px] uppercase font-bold text-indigo-300 block"><?= e($hp['category']) ?></span>
            <p class="text-xs font-bold text-white truncate"><?= e($hp['name']) ?></p>
            <span class="text-xs font-extrabold text-amber-300"><?= money($hp['price']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Value Props Trust Bar -->
<section class="max-w-7xl mx-auto px-4 -mt-6 relative z-20">
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 p-6 rounded-3xl bg-white dark:bg-gray-800 shadow-xl border border-gray-100 dark:border-gray-700/60">
    <div class="flex items-center gap-3.5 p-2">
      <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">🚚</div>
      <div>
        <h4 class="font-bold text-sm text-gray-900 dark:text-white">Free Express Shipping</h4>
        <p class="text-xs text-gray-500">On all domestic orders over $50</p>
      </div>
    </div>
    <div class="flex items-center gap-3.5 p-2">
      <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">🛡️</div>
      <div>
        <h4 class="font-bold text-sm text-gray-900 dark:text-white">2-Year Official Warranty</h4>
        <p class="text-xs text-gray-500">Full replacement guarantee</p>
      </div>
    </div>
    <div class="flex items-center gap-3.5 p-2">
      <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">🔄</div>
      <div>
        <h4 class="font-bold text-sm text-gray-900 dark:text-white">30-Day Risk-Free Returns</h4>
        <p class="text-xs text-gray-500">Pre-paid labels included</p>
      </div>
    </div>
    <div class="flex items-center gap-3.5 p-2">
      <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">🔒</div>
      <div>
        <h4 class="font-bold text-sm text-gray-900 dark:text-white">256-Bit SSL Checkout</h4>
        <p class="text-xs text-gray-500">Bank-grade transaction security</p>
      </div>
    </div>
  </div>
</section>

<!-- Shop by Category Section -->
<section class="max-w-7xl mx-auto px-4 py-16">
  <div class="flex items-end justify-between mb-8">
    <div>
      <span class="text-xs uppercase font-bold tracking-widest text-indigo-600 dark:text-indigo-400">Collections</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-1">Shop by Category</h2>
    </div>
    <a href="/products" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
      Browse All →
    </a>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <?php
    $catIcons = [
        'Electronics' => '💻',
        'Accessories' => '⌨️',
        'Home' => '🛋️',
        'Sports' => '⌚',
    ];
    foreach ($categories as $cat): ?>
      <a href="/products?category=<?= urlencode($cat) ?>"
         class="group p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 shadow-sm hover:shadow-xl hover:border-indigo-500/40 transition-all text-center flex flex-col items-center gap-3 hover:-translate-y-1">
        <span class="text-4xl group-hover:scale-110 transition-transform"><?= $catIcons[$cat] ?? '📦' ?></span>
        <div>
          <h3 class="font-bold text-sm text-gray-900 dark:text-white"><?= e($cat) ?></h3>
          <span class="text-xs text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Explore Category →</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Official Brand Partners Showcase -->
<section class="max-w-7xl mx-auto px-4 py-4">
  <div class="p-8 rounded-3xl bg-white dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/60 shadow-xs">
    <div class="text-center mb-6">
      <span class="text-xs uppercase font-bold tracking-widest text-gray-400">Authorized Retailer &amp; Official Stockist</span>
      <h3 class="text-lg font-extrabold text-gray-900 dark:text-white mt-1">Shop World-Leading Flagship Brands</h3>
    </div>
    <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-8">
      <?php foreach (get_brands() as $brandName): ?>
        <a href="/products?brand=<?= urlencode($brandName) ?>"
           class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200/80 dark:border-gray-700/80 font-bold text-xs sm:text-sm text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-500/50 transition-all shadow-2xs hover:scale-105">
          <?= e($brandName) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Trending & Bestsellers Grid -->
<section class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-end justify-between mb-8">
    <div>
      <span class="text-xs uppercase font-bold tracking-widest text-indigo-600 dark:text-indigo-400">Most Popular</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-1">Trending Products</h2>
    </div>
    <a href="/products?sort=rating" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
      View Top Rated →
    </a>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach ($featured as $p): ?>
      <?php include __DIR__ . '/../partials/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>

<!-- Promotional Banner -->
<section class="max-w-7xl mx-auto px-4 py-12">
  <div class="rounded-3xl bg-gradient-to-r from-purple-900 via-indigo-900 to-indigo-800 text-white p-8 sm:p-12 relative overflow-hidden border border-white/10 shadow-2xl">
    <div class="relative z-10 max-w-xl space-y-4">
      <span class="text-xs uppercase font-bold tracking-widest text-amber-300">Limited-Time Offer</span>
      <h2 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight">
        Take 10% Off Your Entire First Purchase.
      </h2>
      <p class="text-indigo-100 text-sm leading-relaxed">
        Apply coupon code <code class="bg-white/20 px-2 py-0.5 rounded font-mono font-bold text-amber-200">SHOP10</code> at checkout for instant savings across our entire catalog.
      </p>
      <div class="pt-2">
        <a href="/products" class="inline-flex items-center gap-2 rounded-xl bg-white text-indigo-950 font-bold px-6 py-3 text-sm hover:bg-indigo-50 shadow-lg shadow-indigo-950/40 transition-all">
          Redeem Code Now →
        </a>
      </div>
    </div>
  </div>
</section>
