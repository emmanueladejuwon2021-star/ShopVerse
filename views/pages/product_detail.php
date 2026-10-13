<?php
/**
 * Production ProductDetail View
 * Includes multi-image gallery with thumbnail switcher & lightbox zoom,
 * variant selector (colors & specs) with dynamic pricing, stock urgency,
 * frequently bought together bundle upsells, customer review submission engine,
 * sticky add-to-cart bar, and recently viewed products.
 */
$product = find_product((string) ($params['id'] ?? ''));
if (!$product): ?>
  <div class="max-w-7xl mx-auto px-4 py-20 text-center">
    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-3xl">📦</div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Product Not Found</h1>
    <p class="text-gray-500 mt-2">The product you are looking for may have been moved or is no longer available.</p>
    <a href="/products" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 transition-colors">
      ← Return to Catalog
    </a>
  </div>
<?php else:
  $gallery = !empty($product['images']) ? $product['images'] : [$product['image']];
  $related = array_values(array_filter(all_products(), fn($p) => $p['category'] === $product['category'] && $p['id'] !== $product['id']));
  $isSaved = wishlist_has((string) $product['id']);
  $discountPercent = !empty($product['originalPrice']) ? (int) round((1 - $product['price'] / $product['originalPrice']) * 100) : 0;
  $stockCount = $product['stock_count'] ?? ($product['inStock'] ? 15 : 0);
  $reviewsList = get_product_reviews((string) $product['id']);
  $variants = $product['variants'] ?? [];
  $colors = $variants['colors'] ?? [];
  $options = $variants['options'] ?? [];

  // Bundle complementary items
  $bundleItems = [];
  if (!empty($product['bundle_ids'])) {
      foreach ($product['bundle_ids'] as $bId) {
          $bp = find_product((string) $bId);
          if ($bp) $bundleItems[] = $bp;
      }
  }
?>
<div class="max-w-7xl mx-auto px-4 py-8 lg:py-12" id="product-page-container" data-product-id="<?= e($product['id']) ?>">
  <!-- Breadcrumbs -->
  <nav class="flex items-center gap-2 text-xs md:text-sm text-gray-500 dark:text-gray-400 mb-8 overflow-x-auto whitespace-nowrap">
    <a href="/" class="hover:text-indigo-600 transition-colors">Home</a>
    <span>/</span>
    <a href="/products" class="hover:text-indigo-600 transition-colors">Shop</a>
    <span>/</span>
    <a href="/products?category=<?= urlencode($product['category']) ?>" class="hover:text-indigo-600 transition-colors"><?= e($product['category']) ?></a>
    <span>/</span>
    <span class="text-gray-900 dark:text-white font-medium truncate max-w-xs"><?= e($product['name']) ?></span>
  </nav>

  <div class="grid lg:grid-cols-2 gap-10 xl:gap-14 items-start">
    <!-- Image Gallery & Lightbox Trigger -->
    <div class="space-y-4">
      <!-- Main Display -->
      <div class="relative rounded-3xl overflow-hidden bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700 aspect-square shadow-sm group cursor-zoom-in"
           onclick="openLightbox()">
        <img id="main-product-img"
             src="<?= e($gallery[0]) ?>"
             alt="<?= e($product['name']) ?>"
             onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
             class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-105">

        <!-- Badges Overlay -->
        <div class="absolute top-4 left-4 flex flex-col gap-2 pointer-events-none z-10">
          <?php if (!empty($product['badge'])): ?>
            <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md tracking-wide">
              <?= e($product['badge']) ?>
            </span>
          <?php endif; ?>
          <?php if ($discountPercent > 0): ?>
            <span class="bg-rose-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
              Save <?= $discountPercent ?>%
            </span>
          <?php endif; ?>
        </div>

        <!-- Zoom Indicator Pill -->
        <div class="absolute bottom-4 right-4 bg-gray-900/70 text-white text-[11px] font-semibold px-3 py-1.5 rounded-full flex items-center gap-1.5 backdrop-blur-md opacity-80 group-hover:opacity-100 transition-opacity pointer-events-none">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
          Click to Zoom
        </div>
      </div>

      <!-- Thumbnails Strip -->
      <?php if (count($gallery) > 1): ?>
        <div class="grid grid-cols-4 gap-3 sm:gap-4">
          <?php foreach ($gallery as $idx => $imgUrl): ?>
            <button type="button"
                    onclick="switchProductImage('<?= e($imgUrl) ?>', this)"
                    class="thumb-btn rounded-2xl overflow-hidden bg-white dark:bg-gray-800 border-2 transition-all aspect-square <?= $idx === 0 ? 'border-indigo-600 ring-2 ring-indigo-500/20' : 'border-transparent opacity-75 hover:opacity-100' ?>">
              <img src="<?= e($imgUrl) ?>" alt="Thumb <?= $idx + 1 ?>"
                   onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
                   class="w-full h-full object-cover">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Product Details & Buy Box Section -->
    <div class="space-y-6">
      <div>
        <div class="flex items-center justify-between text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
          <span class="font-bold text-indigo-600 dark:text-indigo-400"><?= e($product['brand'] ?? $product['category']) ?></span>
          <span class="font-mono text-gray-400" id="live-sku-display">SKU: <?= e($product['sku'] ?? 'SV-' . $product['id']) ?></span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight">
          <?= e($product['name']) ?>
        </h1>

        <!-- Rating & Social Proof -->
        <div class="flex items-center gap-3 mt-3">
          <div class="flex items-center gap-1">
            <?= stars_html((float) $product['rating']) ?>
          </div>
          <span class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?= number_format((float) $product['rating'], 1) ?></span>
          <span class="text-gray-400">·</span>
          <a href="#reviews" onclick="switchTab('reviews', document.getElementById('reviews-tab-btn'))" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
            <?= count($reviewsList) ?> verified reviews
          </a>
        </div>
      </div>

      <!-- Pricing Block -->
      <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/60 flex flex-wrap items-baseline gap-3">
        <span class="text-3xl sm:text-4xl font-extrabold text-indigo-600 dark:text-indigo-400" id="live-price-display">
          <?= money($product['price']) ?>
        </span>
        <?php if (!empty($product['originalPrice'])): ?>
          <span class="text-lg text-gray-400 line-through" id="live-original-price">
            <?= money($product['originalPrice']) ?>
          </span>
          <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
            Special Pricing
          </span>
        <?php endif; ?>
      </div>

      <!-- Stock & Urgency Indicator -->
      <div class="space-y-2">
        <?php if (!$product['inStock']): ?>
          <div class="inline-flex items-center gap-2 text-sm font-semibold text-rose-600 dark:text-rose-400">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Currently Out of Stock
          </div>
        <?php elseif ($stockCount <= 5): ?>
          <div class="space-y-1.5">
            <div class="flex items-center gap-2 text-sm font-bold text-amber-600 dark:text-amber-400">
              <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
              ⚡ High Demand: Only <?= $stockCount ?> units left in stock!
            </div>
            <div class="w-full bg-amber-100 dark:bg-amber-950/50 rounded-full h-1.5 overflow-hidden">
              <div class="bg-amber-500 h-full rounded-full" style="width: <?= min(100, $stockCount * 20) ?>%"></div>
            </div>
          </div>
        <?php else: ?>
          <div class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> In Stock &amp; Ready for Same-Day Dispatch
          </div>
        <?php endif; ?>
      </div>

      <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-base">
        <?= e($product['description']) ?>
      </p>

      <!-- Main Add To Cart Form with Interactive Variants -->
      <form id="main-buy-form" method="post" action="/products" class="space-y-5 pt-2">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
        <input type="hidden" name="redirect" value="/product/<?= e($product['id']) ?>">
        <input type="hidden" id="form-variant-color" name="variant_color" value="<?= !empty($colors) ? e($colors[0]['name']) : '' ?>">
        <input type="hidden" id="form-variant-option" name="variant_option" value="<?= !empty($options) ? e($options[0]['name']) : '' ?>">
        <input type="hidden" id="form-price-diff" name="price_diff" value="0">
        <input type="hidden" id="form-custom-image" name="custom_image" value="">

        <!-- Color Swatch Selector -->
        <?php if (!empty($colors)): ?>
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300">
              <span>Color Finish: <span id="color-label" class="text-indigo-600 dark:text-indigo-400 font-semibold"><?= e($colors[0]['name']) ?></span></span>
            </div>
            <div class="flex items-center gap-3">
              <?php foreach ($colors as $idx => $c): ?>
                <button type="button"
                        onclick="selectColor('<?= e($c['name']) ?>', '<?= e($c['image'] ?? '') ?>', this)"
                        title="<?= e($c['name']) ?>"
                        class="color-swatch-btn w-8 h-8 rounded-full border-2 transition-transform hover:scale-110 flex items-center justify-center <?= $idx === 0 ? 'ring-2 ring-indigo-600 border-white' : 'border-gray-300 dark:border-gray-600' ?>"
                        style="background-color: <?= e($c['hex'] ?? '#333') ?>;">
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Option / Storage Selector -->
        <?php if (!empty($options)): ?>
          <div class="space-y-2">
            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">Specification / Capacity:</span>
            <div class="flex flex-wrap gap-2.5">
              <?php foreach ($options as $idx => $opt): ?>
                <button type="button"
                        onclick="selectOption('<?= e($opt['name']) ?>', <?= (float) ($opt['priceDiff'] ?? 0) ?>, this)"
                        class="option-pill-btn rounded-xl border px-4 py-2 text-xs font-semibold transition-all <?= $idx === 0 ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:border-gray-400' ?>">
                  <?= e($opt['name']) ?>
                  <?php if (!empty($opt['priceDiff']) && $opt['priceDiff'] > 0): ?>
                    <span class="opacity-80 text-[10px] ml-1">(+<?= money($opt['priceDiff']) ?>)</span>
                  <?php endif; ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Key Features Checkmarks -->
        <?php if (!empty($product['features'])): ?>
          <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Key Highlights</h3>
            <ul class="grid sm:grid-cols-2 gap-2 text-sm text-gray-700 dark:text-gray-200">
              <?php foreach ($product['features'] as $f): ?>
                <li class="flex items-center gap-2">
                  <span class="text-emerald-500 font-bold shrink-0">✓</span>
                  <span><?= e($f) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Quantity Stepper & Actions -->
        <div id="buy-box-controls" class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <!-- Quantity Stepper -->
          <div class="flex items-center rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 p-1 shrink-0 justify-between sm:justify-start">
            <button type="button" onclick="adjustQty(-1)" class="w-9 h-9 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300">−</button>
            <input id="product-qty-input" type="number" name="quantity" min="1" max="<?= max(1, $stockCount) ?>" value="1"
                   class="w-12 text-center bg-transparent font-semibold text-sm focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
            <button type="button" onclick="adjustQty(1)" class="w-9 h-9 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300">+</button>
          </div>

          <!-- Add To Cart Button -->
          <button type="submit" id="main-add-btn" <?= $product['inStock'] ? '' : 'disabled' ?>
                  class="flex-1 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:bg-gray-200 dark:disabled:bg-gray-800 disabled:text-gray-400 text-white font-bold py-3.5 px-6 shadow-lg shadow-indigo-600/25 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span><?= $product['inStock'] ? 'Add to Cart' : 'Out of Stock' ?></span>
          </button>

          <!-- Wishlist Toggle -->
          <button type="submit"
                  formaction="/products"
                  name="action"
                  value="toggle_wishlist"
                  title="<?= $isSaved ? 'In your wishlist' : 'Add to wishlist' ?>"
                  class="rounded-xl border border-gray-300 dark:border-gray-700 hover:border-rose-400 p-3.5 flex items-center justify-center text-gray-500 hover:text-rose-500 transition-colors bg-white dark:bg-gray-800 shrink-0">
            <?php if ($isSaved): ?>
              <svg class="w-5 h-5 text-rose-500 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            <?php else: ?>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <?php endif; ?>
          </button>
        </div>
      </form>

      <!-- Confidence Guarantees -->
      <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-2">
          <span class="text-indigo-600">🚚</span> Free Express Delivery Over $100
        </div>
        <div class="flex items-center gap-2">
          <span class="text-indigo-600">↩️</span> 30-Day Hassle-Free Returns
        </div>
        <div class="flex items-center gap-2">
          <span class="text-indigo-600">🛡️</span> Full Official Warranty
        </div>
        <div class="flex items-center gap-2">
          <span class="text-indigo-600">🔒</span> 256-Bit SSL Encrypted Checkout
        </div>
      </div>
    </div>
  </div>

  <!-- Frequently Bought Together Bundle Upsell -->
  <?php if (!empty($bundleItems)):
    $bundleTotal = $product['price'];
    foreach ($bundleItems as $bi) { $bundleTotal += $bi['price']; }
    $discountedBundle = round($bundleTotal * 0.85, 2);
  ?>
    <section class="mt-16 bg-gradient-to-br from-indigo-50/70 to-purple-50/50 dark:from-gray-800/80 dark:to-gray-800/40 rounded-3xl p-6 sm:p-8 border border-indigo-100 dark:border-gray-700/60 shadow-sm">
      <div class="max-w-2xl mb-6">
        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-900 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-800 inline-block mb-2">
          Bundle &amp; Save 15%
        </span>
        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
          Frequently Bought Together
        </h2>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">
          Combine this item with curated complementary gear for instant bundle savings.
        </p>
      </div>

      <form method="post" action="/products" class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_bundle">
        <input type="hidden" name="main_product_id" value="<?= e($product['id']) ?>">
        <input type="hidden" name="redirect" value="/cart">

        <!-- Bundle Items Visual Thumbnails -->
        <div class="flex flex-wrap items-center gap-3 sm:gap-4">
          <!-- Main Product -->
          <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs">
            <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="w-14 h-14 rounded-xl object-cover bg-gray-50">
            <div>
              <p class="font-bold text-xs text-gray-900 dark:text-white max-w-[130px] truncate"><?= e($product['name']) ?></p>
              <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400"><?= money($product['price']) ?></span>
            </div>
          </div>

          <span class="text-xl font-bold text-gray-400">+</span>

          <!-- Complementary Items -->
          <?php foreach ($bundleItems as $idx => $bi): ?>
            <?php if ($idx > 0): ?><span class="text-xl font-bold text-gray-400">+</span><?php endif; ?>
            <input type="hidden" name="bundle_product_ids[]" value="<?= e($bi['id']) ?>">
            <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs">
              <img src="<?= e($bi['image']) ?>" alt="<?= e($bi['name']) ?>" class="w-14 h-14 rounded-xl object-cover bg-gray-50">
              <div>
                <p class="font-bold text-xs text-gray-900 dark:text-white max-w-[130px] truncate"><?= e($bi['name']) ?></p>
                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400"><?= money($bi['price']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Bundle Pricing & Add Button -->
        <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 text-left lg:text-right shrink-0 w-full lg:w-auto space-y-2">
          <div class="flex lg:justify-end items-baseline gap-2">
            <span class="text-xs text-gray-400 line-through"><?= money($bundleTotal) ?></span>
            <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400"><?= money($discountedBundle) ?></span>
          </div>
          <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 block">
            Save <?= money($bundleTotal - $discountedBundle) ?> with Bundle
          </span>
          <button type="submit" class="w-full lg:w-auto rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-5 py-3 shadow-md transition-all">
            Add All to Cart →
          </button>
        </div>
      </form>
    </section>
  <?php endif; ?>

  <!-- Detailed Specifications, Verified Reviews, & Shipping Tabs -->
  <div class="mt-16 pt-10 border-t border-gray-200 dark:border-gray-800">
    <div class="flex items-center gap-6 border-b border-gray-200 dark:border-gray-800 pb-3 overflow-x-auto text-sm font-semibold">
      <button type="button" onclick="switchTab('specs', this)" class="detail-tab-btn pb-3 text-indigo-600 border-b-2 border-indigo-600">Technical Specifications</button>
      <button type="button" onclick="switchTab('reviews', this)" id="reviews-tab-btn" class="detail-tab-btn pb-3 text-gray-500 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent">Customer Reviews (<?= count($reviewsList) ?>)</button>
      <button type="button" onclick="switchTab('shipping', this)" class="detail-tab-btn pb-3 text-gray-500 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent">Shipping &amp; Logistics</button>
    </div>

    <!-- Tab 1: Specs -->
    <div id="tab-specs" class="detail-tab-panel py-8">
      <?php if (!empty($product['specs'])): ?>
        <div class="max-w-3xl bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/60 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/60 shadow-sm">
          <?php foreach ($product['specs'] as $k => $v): ?>
            <div class="grid grid-cols-3 px-6 py-4 text-xs sm:text-sm">
              <span class="font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider"><?= e($k) ?></span>
              <span class="col-span-2 text-gray-900 dark:text-white font-medium"><?= e($v) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-500 text-sm">Full specifications included in official packaging.</p>
      <?php endif; ?>
    </div>

    <!-- Tab 2: Reviews & Review Submission -->
    <div id="tab-reviews" class="detail-tab-panel hidden py-8">
      <div id="reviews" class="grid lg:grid-cols-3 gap-8 items-start">
        <!-- Review Score Summary & Write Button -->
        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-3xl border border-gray-200/80 dark:border-gray-700 text-center lg:text-left space-y-4">
          <div>
            <div class="text-5xl font-extrabold text-gray-900 dark:text-white"><?= number_format((float) $product['rating'], 1) ?></div>
            <div class="flex justify-center lg:justify-start gap-1 my-2">
              <?= stars_html((float) $product['rating']) ?>
            </div>
            <p class="text-xs text-gray-500">Based on <?= count($reviewsList) ?> verified customer reviews</p>
          </div>

          <div class="space-y-2 text-xs">
            <div class="flex items-center gap-2">
              <span class="w-8 font-semibold">5★</span>
              <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"><div class="bg-amber-400 h-full" style="width: 88%"></div></div>
              <span class="w-8 text-right text-gray-400">88%</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="w-8 font-semibold">4★</span>
              <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"><div class="bg-amber-400 h-full" style="width: 10%"></div></div>
              <span class="w-8 text-right text-gray-400">10%</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="w-8 font-semibold">3★</span>
              <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"><div class="bg-amber-400 h-full" style="width: 2%"></div></div>
              <span class="w-8 text-right text-gray-400">2%</span>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="toggleReviewModal(true)"
                    class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3 px-4 shadow-sm transition-all">
              Write a Review ✍️
            </button>
          </div>
        </div>

        <!-- Reviews List -->
        <div class="lg:col-span-2 space-y-4">
          <?php if (!empty($reviewsList)): ?>
            <?php foreach ($reviewsList as $rev): ?>
              <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/80 shadow-sm space-y-2.5">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-sm text-gray-900 dark:text-white"><?= e($rev['author']) ?></span>
                    <?php if (!empty($rev['verified'])): ?>
                      <span class="text-[10px] bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                        ✓ Verified Buyer
                      </span>
                    <?php endif; ?>
                  </div>
                  <span class="text-xs text-gray-400"><?= e($rev['date']) ?></span>
                </div>
                <div class="flex items-center gap-1">
                  <?= stars_html((float) $rev['rating']) ?>
                </div>
                <h4 class="font-bold text-sm text-gray-900 dark:text-white"><?= e($rev['title']) ?></h4>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed"><?= e($rev['comment']) ?></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-gray-500 text-sm">No written reviews yet. Be the first to share your thoughts!</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Tab 3: Shipping & Logistics -->
    <div id="tab-shipping" class="detail-tab-panel hidden py-8 max-w-3xl space-y-4 text-sm text-gray-600 dark:text-gray-300">
      <div class="p-6 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 space-y-3">
        <h3 class="font-bold text-gray-900 dark:text-white text-base">Global Shipping &amp; Real-Time Tracking</h3>
        <p>All items in this catalog are shipped directly from certified climate-controlled regional warehouses within 24 hours of purchase.</p>
        <ul class="list-disc pl-5 space-y-1.5 text-xs sm:text-sm">
          <li><strong>Standard Ground Delivery:</strong> 3-5 business days ($4.99, or FREE on orders over $100).</li>
          <li><strong>Express Air (2-Day Priority):</strong> 2 business days ($12.99).</li>
          <li><strong>Priority Overnight:</strong> Next business day delivery by 10:30 AM ($24.99).</li>
          <li>Every order is issued an authentic carrier tracking ID usable on our <a href="/track" class="text-indigo-600 dark:text-indigo-400 font-bold underline">Order Tracking Portal</a>.</li>
        </ul>
      </div>

      <div class="p-6 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 space-y-3">
        <h3 class="font-bold text-gray-900 dark:text-white text-base">30-Day Complete Satisfaction Guarantee</h3>
        <p>If you're not completely satisfied with your item, return it in original packaging within 30 days for an immediate 100% refund or exchange.</p>
      </div>
    </div>
  </div>

  <!-- Related Products Cross-Sell -->
  <?php if (!empty($related)): ?>
    <section class="mt-20 pt-10 border-t border-gray-200 dark:border-gray-800">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">You Might Also Like</h2>
          <p class="text-sm text-gray-500 mt-1">Complementary gear in <?= e($product['category']) ?></p>
        </div>
        <a href="/products?category=<?= urlencode($product['category']) ?>" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
          View all <?= e($product['category']) ?> →
        </a>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach (array_slice($related, 0, 4) as $p): ?>
          <?php include __DIR__ . '/../partials/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- Recently Viewed Products Strip -->
  <section id="recently-viewed-section" class="hidden mt-20 pt-10 border-t border-gray-200 dark:border-gray-800">
    <div class="mb-6">
      <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Recently Viewed</h2>
      <p class="text-sm text-gray-500 mt-1">Products you previously looked at</p>
    </div>
    <div id="recently-viewed-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <!-- Populated via client-side storage -->
    </div>
  </section>
</div>

<!-- Sticky Bottom Add-To-Cart Action Bar -->
<div id="sticky-buy-bar" class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-gray-900/95 border-t border-gray-200 dark:border-gray-800 p-4 shadow-2xl backdrop-blur-md z-40 transform translate-y-full transition-transform duration-300">
  <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
    <div class="flex items-center gap-3 min-w-0">
      <img src="<?= e($gallery[0]) ?>" alt="<?= e($product['name']) ?>"
           id="sticky-thumb"
           onerror="this.src='/assets/products/placeholder.svg'"
           class="w-12 h-12 rounded-xl object-cover bg-gray-100 shrink-0 border border-gray-200 dark:border-gray-700">
      <div class="min-w-0">
        <h4 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate"><?= e($product['name']) ?></h4>
        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400" id="sticky-price-display"><?= money($product['price']) ?></span>
      </div>
    </div>

    <button onclick="document.getElementById('main-add-btn').click()"
            class="rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm px-6 py-3 shadow-md transition-all shrink-0">
      Add to Cart →
    </button>
  </div>
</div>

<!-- Fullscreen Lightbox Modal -->
<div id="lightbox-modal" class="hidden fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
  <button onclick="closeLightbox()" class="absolute top-6 right-6 text-white text-3xl font-light hover:text-gray-300 z-50 p-2">✕</button>
  <button onclick="prevLightboxImage()" class="absolute left-6 text-white text-4xl hover:text-gray-300 z-50 p-2">‹</button>
  <button onclick="nextLightboxImage()" class="absolute right-6 text-white text-4xl hover:text-gray-300 z-50 p-2">›</button>

  <img id="lightbox-img" src="<?= e($gallery[0]) ?>" alt="Zoomed view"
       class="max-h-[85vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl transition-transform duration-300">
</div>

<!-- Write Review Modal -->
<div id="review-modal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full border border-gray-100 dark:border-gray-700 shadow-2xl relative">
    <button onclick="toggleReviewModal(false)" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>

    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Write a Review</h3>
    <p class="text-xs text-gray-500 mb-6">Share your authentic experience with <?= e($product['name']) ?>.</p>

    <form method="post" action="/products" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_review">
      <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
      <input type="hidden" id="review-rating-input" name="rating" value="5">

      <!-- Interactive 5-Star Selector -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Overall Rating *</label>
        <div class="flex items-center gap-1.5" id="star-picker">
          <?php for ($s = 1; $s <= 5; $s++): ?>
            <button type="button" onclick="setReviewRating(<?= $s ?>)"
                    class="star-pick-btn text-2xl text-yellow-400 focus:outline-none">★</button>
          <?php endfor; ?>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Your Name *</label>
        <input type="text" name="author" required placeholder="Jane Doe"
               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Review Headline *</label>
        <input type="text" name="title" required placeholder="e.g. Incredible performance and build quality"
               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Detailed Review *</label>
        <textarea name="comment" rows="4" required placeholder="What did you like or dislike about this product?"
                  class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
      </div>

      <button type="submit" class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 text-xs shadow-md transition-all">
        Submit Verified Review
      </button>
    </form>
  </div>
</div>

<script>
  var basePrice = <?= (float) $product['price'] ?>;
  var galleryImages = <?= json_encode($gallery) ?>;
  var currentLightboxIndex = 0;

  function switchProductImage(src, btn) {
    var main = document.getElementById('main-product-img');
    var stickyThumb = document.getElementById('sticky-thumb');
    if (main) main.src = src;
    if (stickyThumb) stickyThumb.src = src;

    var idx = galleryImages.indexOf(src);
    if (idx !== -1) currentLightboxIndex = idx;

    document.querySelectorAll('.thumb-btn').forEach(function(b) {
      b.classList.remove('border-indigo-600', 'ring-2', 'ring-indigo-500/20');
      b.classList.add('border-transparent', 'opacity-75');
    });
    if (btn) {
      btn.classList.add('border-indigo-600', 'ring-2', 'ring-indigo-500/20');
      btn.classList.remove('border-transparent', 'opacity-75');
    }
  }

  function selectColor(colorName, imageUrl, btn) {
    document.getElementById('color-label').innerText = colorName;
    document.getElementById('form-variant-color').value = colorName;

    if (imageUrl) {
      switchProductImage(imageUrl, null);
      document.getElementById('form-custom-image').value = imageUrl;
    }

    document.querySelectorAll('.color-swatch-btn').forEach(function(b) {
      b.classList.remove('ring-2', 'ring-indigo-600', 'border-white');
      b.classList.add('border-gray-300');
    });
    btn.classList.add('ring-2', 'ring-indigo-600', 'border-white');
    btn.classList.remove('border-gray-300');
  }

  function selectOption(optionName, priceDiff, btn) {
    document.getElementById('form-variant-option').value = optionName;
    document.getElementById('form-price-diff').value = priceDiff;

    var newPrice = basePrice + priceDiff;
    var formatted = '$' + newPrice.toFixed(2);
    document.getElementById('live-price-display').innerText = formatted;
    document.getElementById('sticky-price-display').innerText = formatted;

    document.querySelectorAll('.option-pill-btn').forEach(function(b) {
      b.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600', 'shadow-sm');
      b.classList.add('border-gray-300', 'bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-200');
    });
    btn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600', 'shadow-sm');
    btn.classList.remove('border-gray-300', 'bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-200');
  }

  function adjustQty(delta) {
    var input = document.getElementById('product-qty-input');
    if (!input) return;
    var current = parseInt(input.value, 10) || 1;
    var min = parseInt(input.min, 10) || 1;
    var max = parseInt(input.max, 10) || 99;
    input.value = Math.max(min, Math.min(max, current + delta));
  }

  function switchTab(tabName, btn) {
    document.querySelectorAll('.detail-tab-panel').forEach(function(p) { p.classList.add('hidden'); });
    document.querySelectorAll('.detail-tab-btn').forEach(function(b) {
      b.classList.remove('text-indigo-600', 'border-indigo-600');
      b.classList.add('text-gray-500', 'border-transparent');
    });
    var target = document.getElementById('tab-' + tabName);
    if (target) target.classList.remove('hidden');
    if (btn) {
      btn.classList.add('text-indigo-600', 'border-indigo-600');
      btn.classList.remove('text-gray-500', 'border-transparent');
    }
  }

  // Lightbox
  function openLightbox() {
    var modal = document.getElementById('lightbox-modal');
    var img = document.getElementById('lightbox-img');
    if (modal && img) {
      img.src = galleryImages[currentLightboxIndex] || galleryImages[0];
      modal.classList.remove('hidden');
    }
  }
  function closeLightbox() {
    var modal = document.getElementById('lightbox-modal');
    if (modal) modal.classList.add('hidden');
  }
  function nextLightboxImage() {
    currentLightboxIndex = (currentLightboxIndex + 1) % galleryImages.length;
    document.getElementById('lightbox-img').src = galleryImages[currentLightboxIndex];
  }
  function prevLightboxImage() {
    currentLightboxIndex = (currentLightboxIndex - 1 + galleryImages.length) % galleryImages.length;
    document.getElementById('lightbox-img').src = galleryImages[currentLightboxIndex];
  }

  // Review Modal & Rating Picker
  function toggleReviewModal(show) {
    var m = document.getElementById('review-modal');
    if (m) m.classList.toggle('hidden', !show);
  }
  function setReviewRating(r) {
    document.getElementById('review-rating-input').value = r;
    var btns = document.querySelectorAll('.star-pick-btn');
    btns.forEach(function(b, i) {
      b.classList.toggle('text-yellow-400', i < r);
      b.classList.toggle('text-gray-300', i >= r);
    });
  }

  // Sticky Buy Bar on Scroll
  window.addEventListener('scroll', function() {
    var controls = document.getElementById('buy-box-controls');
    var stickyBar = document.getElementById('sticky-buy-bar');
    if (!controls || !stickyBar) return;
    var rect = controls.getBoundingClientRect();
    if (rect.bottom < 0) {
      stickyBar.classList.remove('translate-y-full');
    } else {
      stickyBar.classList.add('translate-y-full');
    }
  });

  // Track Recently Viewed Products
  (function () {
    var currentId = <?= json_encode((string) $product['id']) ?>;
    var currentName = <?= json_encode($product['name']) ?>;
    var currentPrice = <?= json_encode(money($product['price'])) ?>;
    var currentImage = <?= json_encode($product['image']) ?>;

    var raw = localStorage.getItem('recently_viewed');
    var list = raw ? JSON.parse(raw) : [];

    // Filter out current
    list = list.filter(function(item) { return item.id !== currentId; });
    // Add current to top
    list.unshift({ id: currentId, name: currentName, price: currentPrice, image: currentImage });
    // Keep max 5
    list = list.slice(0, 5);
    localStorage.setItem('recently_viewed', JSON.stringify(list));

    // Render other recently viewed products
    var others = list.filter(function(i) { return i.id !== currentId; });
    var container = document.getElementById('recently-viewed-grid');
    var section = document.getElementById('recently-viewed-section');
    if (others.length > 0 && container && section) {
      var html = '';
      others.forEach(function(item) {
        html += '<a href="/product/' + item.id + '" class="p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs hover:border-indigo-500 transition-colors">' +
          '<img src="' + item.image + '" alt="' + item.name + '" onerror="this.src=\'/assets/products/placeholder.svg\'" class="w-full aspect-square object-cover rounded-xl bg-gray-50 mb-2">' +
          '<p class="text-xs font-bold text-gray-900 dark:text-white truncate">' + item.name + '</p>' +
          '<span class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400">' + item.price + '</span>' +
        '</a>';
      });
      container.innerHTML = html;
      section.classList.remove('hidden');
    }
  })();
</script>
<?php endif; ?>
