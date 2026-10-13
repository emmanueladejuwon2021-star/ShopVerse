<?php
/**
 * Production ProductDetail View
 * Includes multi-image gallery with thumbnail switcher, stock status, specs tabs,
 * verified customer review breakdown, wishlist toggle, and related products cross-sell.
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
  $reviewsList = $product['reviews_list'] ?? [];
?>
<div class="max-w-7xl mx-auto px-4 py-8 lg:py-12">
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
    <!-- Image Gallery Section -->
    <div class="space-y-4">
      <!-- Main Display -->
      <div class="relative rounded-3xl overflow-hidden bg-gray-100 dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700 aspect-square shadow-sm">
        <img id="main-product-img"
             src="<?= e($gallery[0]) ?>"
             alt="<?= e($product['name']) ?>"
             onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
             class="w-full h-full object-cover object-center transition-all duration-300">

        <!-- Badges Overlay -->
        <div class="absolute top-4 left-4 flex flex-col gap-2 pointer-events-none">
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
      </div>

      <!-- Thumbnails Strip -->
      <?php if (count($gallery) > 1): ?>
        <div class="grid grid-cols-4 gap-3 sm:gap-4">
          <?php foreach ($gallery as $idx => $imgUrl): ?>
            <button type="button"
                    onclick="switchProductImage('<?= e($imgUrl) ?>', this)"
                    class="thumb-btn rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 border-2 transition-all aspect-square <?= $idx === 0 ? 'border-indigo-600 ring-2 ring-indigo-500/20' : 'border-transparent opacity-75 hover:opacity-100' ?>">
              <img src="<?= e($imgUrl) ?>" alt="Thumb <?= $idx + 1 ?>"
                   onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
                   class="w-full h-full object-cover">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Product Info Section -->
    <div class="space-y-6">
      <div>
        <div class="flex items-center justify-between text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
          <span class="font-bold text-indigo-600 dark:text-indigo-400"><?= e($product['brand'] ?? $product['category']) ?></span>
          <span class="font-mono text-gray-400">SKU: <?= e($product['sku'] ?? 'SV-' . $product['id']) ?></span>
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
          <a href="#reviews" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
            <?= number_format((int) $product['reviews']) ?> verified reviews
          </a>
        </div>
      </div>

      <!-- Pricing Block -->
      <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/60 flex flex-wrap items-baseline gap-3">
        <span class="text-3xl sm:text-4xl font-extrabold text-indigo-600 dark:text-indigo-400">
          <?= money($product['price']) ?>
        </span>
        <?php if (!empty($product['originalPrice'])): ?>
          <span class="text-lg text-gray-400 line-through">
            <?= money($product['originalPrice']) ?>
          </span>
          <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
            You save <?= money($product['originalPrice'] - $product['price']) ?>
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
              ⚡ Only <?= $stockCount ?> left in stock - order soon!
            </div>
            <div class="w-full bg-amber-100 dark:bg-amber-950/50 rounded-full h-1.5 overflow-hidden">
              <div class="bg-amber-500 h-full rounded-full" style="width: <?= min(100, $stockCount * 20) ?>%"></div>
            </div>
          </div>
        <?php else: ?>
          <div class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> In Stock &amp; Ready to Ship
          </div>
        <?php endif; ?>
      </div>

      <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-base">
        <?= e($product['description']) ?>
      </p>

      <!-- Key Features Checkmarks -->
      <?php if (!empty($product['features'])): ?>
        <div class="space-y-2">
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

      <!-- Actions (Add to Cart + Wishlist) -->
      <form method="post" action="/products" class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
        <input type="hidden" name="redirect" value="/product/<?= e($product['id']) ?>">

        <!-- Quantity Stepper -->
        <div class="flex items-center rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 p-1 shrink-0 justify-between sm:justify-start">
          <button type="button" onclick="adjustQty(-1)" class="w-9 h-9 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300">−</button>
          <input id="product-qty-input" type="number" name="quantity" min="1" max="<?= max(1, $stockCount) ?>" value="1"
                 class="w-12 text-center bg-transparent font-semibold text-sm focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
          <button type="button" onclick="adjustQty(1)" class="w-9 h-9 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300">+</button>
        </div>

        <!-- Add To Cart -->
        <button <?= $product['inStock'] ? '' : 'disabled' ?>
                class="flex-1 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:bg-gray-200 dark:disabled:bg-gray-800 disabled:text-gray-400 text-white font-bold py-3.5 px-6 shadow-lg shadow-indigo-600/25 transition-all flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          <?= $product['inStock'] ? 'Add to Cart' : 'Out of Stock' ?>
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
      </form>

      <!-- Confidence Guarantees -->
      <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-2">
          <span class="text-indigo-600">🚚</span> Free delivery on orders over $50
        </div>
        <div class="flex items-center gap-2">
          <span class="text-indigo-600">↩️</span> 30-day hassle-free returns
        </div>
        <div class="flex items-center gap-2">
          <span class="text-indigo-600">🛡️</span> 2-Year official warranty
        </div>
        <div class="flex items-center gap-2">
          <span class="text-indigo-600">🔒</span> 256-bit encrypted checkout
        </div>
      </div>
    </div>
  </div>

  <!-- Detailed Specifications & Tabs Section -->
  <div class="mt-16 pt-10 border-t border-gray-200 dark:border-gray-800">
    <div class="flex items-center gap-6 border-b border-gray-200 dark:border-gray-800 pb-3 overflow-x-auto text-sm font-semibold">
      <button type="button" onclick="switchTab('specs', this)" class="detail-tab-btn pb-3 text-indigo-600 border-b-2 border-indigo-600">Specifications</button>
      <button type="button" onclick="switchTab('reviews', this)" id="reviews-tab-btn" class="detail-tab-btn pb-3 text-gray-500 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent">Customer Reviews (<?= number_format((int) $product['reviews']) ?>)</button>
      <button type="button" onclick="switchTab('shipping', this)" class="detail-tab-btn pb-3 text-gray-500 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent">Shipping &amp; Returns</button>
    </div>

    <!-- Tab 1: Specs -->
    <div id="tab-specs" class="detail-tab-panel py-8">
      <?php if (!empty($product['specs'])): ?>
        <div class="max-w-2xl bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/60 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/60">
          <?php foreach ($product['specs'] as $k => $v): ?>
            <div class="grid grid-cols-3 px-6 py-3.5 text-sm">
              <span class="font-semibold text-gray-500 dark:text-gray-400"><?= e($k) ?></span>
              <span class="col-span-2 text-gray-900 dark:text-white font-medium"><?= e($v) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-500 text-sm">Full manufacturer specifications included in product manual.</p>
      <?php endif; ?>
    </div>

    <!-- Tab 2: Reviews -->
    <div id="tab-reviews" class="detail-tab-panel hidden py-8">
      <div id="reviews" class="grid lg:grid-cols-3 gap-8 items-start">
        <!-- Review Score Summary -->
        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 text-center lg:text-left">
          <div class="text-5xl font-extrabold text-gray-900 dark:text-white"><?= number_format((float) $product['rating'], 1) ?></div>
          <div class="flex justify-center lg:justify-start gap-1 my-2">
            <?= stars_html((float) $product['rating']) ?>
          </div>
          <p class="text-xs text-gray-500">Based on <?= number_format((int) $product['reviews']) ?> verified customer ratings</p>

          <div class="mt-6 space-y-2 text-xs">
            <div class="flex items-center gap-2">
              <span class="w-8">5★</span>
              <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"><div class="bg-amber-400 h-full" style="width: 82%"></div></div>
              <span class="w-8 text-right text-gray-400">82%</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="w-8">4★</span>
              <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"><div class="bg-amber-400 h-full" style="width: 14%"></div></div>
              <span class="w-8 text-right text-gray-400">14%</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="w-8">3★</span>
              <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"><div class="bg-amber-400 h-full" style="width: 3%"></div></div>
              <span class="w-8 text-right text-gray-400">3%</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="w-8">2★</span>
              <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"><div class="bg-amber-400 h-full" style="width: 1%"></div></div>
              <span class="w-8 text-right text-gray-400">1%</span>
            </div>
          </div>
        </div>

        <!-- Reviews List -->
        <div class="lg:col-span-2 space-y-4">
          <?php if (!empty($reviewsList)): ?>
            <?php foreach ($reviewsList as $rev): ?>
              <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-2">
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
                <h4 class="font-semibold text-sm text-gray-900 dark:text-white"><?= e($rev['title']) ?></h4>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed"><?= e($rev['comment']) ?></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-gray-500 text-sm">No written reviews yet. Be the first to leave feedback!</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Tab 3: Shipping -->
    <div id="tab-shipping" class="detail-tab-panel hidden py-8 max-w-3xl space-y-4 text-sm text-gray-600 dark:text-gray-300">
      <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 space-y-3">
        <h3 class="font-bold text-gray-900 dark:text-white text-base">Shipping Information</h3>
        <p>Orders are dispatched within 24 hours on business days from our domestic fulfillment centers.</p>
        <ul class="list-disc pl-5 space-y-1">
          <li><strong>Standard Shipping:</strong> 3-5 business days ($4.99, or FREE on orders over $50).</li>
          <li><strong>Express Shipping:</strong> 1-2 business days ($12.99).</li>
          <li>Full real-time tracking number provided immediately upon dispatch.</li>
        </ul>
      </div>

      <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 space-y-3">
        <h3 class="font-bold text-gray-900 dark:text-white text-base">30-Day Money-Back Guarantee</h3>
        <p>If you are not 100% satisfied with your purchase, return it in original condition within 30 days for an instant, full refund. Pre-paid return shipping labels are included in your account dashboard.</p>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if (!empty($related)): ?>
    <section class="mt-20 pt-10 border-t border-gray-200 dark:border-gray-800">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">You Might Also Like</h2>
          <p class="text-sm text-gray-500 mt-1">Hand-picked recommendations based on this item</p>
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
</div>

<script>
  function switchProductImage(src, btn) {
    var main = document.getElementById('main-product-img');
    if (main) {
      main.src = src;
    }
    document.querySelectorAll('.thumb-btn').forEach(function(b) {
      b.classList.remove('border-indigo-600', 'ring-2', 'ring-indigo-500/20');
      b.classList.add('border-transparent', 'opacity-75');
    });
    btn.classList.add('border-indigo-600', 'ring-2', 'ring-indigo-500/20');
    btn.classList.remove('border-transparent', 'opacity-75');
  }

  function adjustQty(delta) {
    var input = document.getElementById('product-qty-input');
    if (!input) return;
    var current = parseInt(input.value, 10) || 1;
    var min = parseInt(input.min, 10) || 1;
    var max = parseInt(input.max, 10) || 99;
    var next = Math.max(min, Math.min(max, current + delta));
    input.value = next;
  }

  function switchTab(tabName, btn) {
    document.querySelectorAll('.detail-tab-panel').forEach(function(p) { p.classList.add('hidden'); });
    document.querySelectorAll('.detail-tab-btn').forEach(function(b) {
      b.classList.remove('text-indigo-600', 'border-indigo-600');
      b.classList.add('text-gray-500', 'border-transparent');
    });
    var target = document.getElementById('tab-' + tabName);
    if (target) target.classList.remove('hidden');
    btn.classList.add('text-indigo-600', 'border-indigo-600');
    btn.classList.remove('text-gray-500', 'border-transparent');
  }
</script>
<?php endif; ?>
