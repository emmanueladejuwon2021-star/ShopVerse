<?php
/**
 * Production ProductCard Component
 * Features dual-image hover transition, wishlist toggle, stock alerts, merchandising badges, and responsive UI.
 */
$isSaved = wishlist_has((string) $p['id']);
$secondaryImage = !empty($p['images'][1]) ? $p['images'][1] : null;
$discountPercent = !empty($p['originalPrice']) ? (int) round((1 - $p['price'] / $p['originalPrice']) * 100) : 0;
?>
<div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700/60 overflow-hidden flex flex-col relative">
  <!-- Image Container -->
  <div class="relative block overflow-hidden bg-gray-100 dark:bg-gray-900 aspect-[4/3]">
    <a href="/product/<?= e($p['id']) ?>" class="block w-full h-full">
      <!-- Primary Image -->
      <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"
           onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
           class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" loading="lazy">
      
      <!-- Secondary Hover Image (if available) -->
      <?php if ($secondaryImage): ?>
        <img src="<?= e($secondaryImage) ?>" alt="<?= e($p['name']) ?>"
             onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
             class="absolute inset-0 w-full h-full object-cover object-center opacity-0 group-hover:opacity-100 transition-opacity duration-500" loading="lazy">
      <?php endif; ?>
    </a>

    <!-- Top Badges (Left) -->
    <div class="absolute top-2.5 left-2.5 flex flex-col gap-1.5 pointer-events-none z-10">
      <?php if (!empty($p['badge'])): ?>
        <span class="bg-indigo-600 text-white text-[11px] font-bold px-2.5 py-0.5 rounded-full shadow-sm tracking-wide">
          <?= e($p['badge']) ?>
        </span>
      <?php endif; ?>
      <?php if ($discountPercent > 0): ?>
        <span class="bg-rose-500 text-white text-[11px] font-bold px-2 py-0.5 rounded-full shadow-sm">
          -<?= $discountPercent ?>%
        </span>
      <?php endif; ?>
      <?php if (!$p['inStock']): ?>
        <span class="bg-gray-900/90 text-white text-[11px] font-semibold px-2 py-0.5 rounded-full backdrop-blur-sm">
          Out of Stock
        </span>
      <?php endif; ?>
    </div>

    <!-- Wishlist Button (Top Right) -->
    <form method="post" class="absolute top-2.5 right-2.5 z-20">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="toggle_wishlist">
      <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
      <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/products') ?>">
      <button type="submit" aria-label="<?= $isSaved ? 'Remove from wishlist' : 'Save to wishlist' ?>"
              title="<?= $isSaved ? 'In your wishlist' : 'Save to wishlist' ?>"
              class="w-8 h-8 rounded-full bg-white/90 dark:bg-gray-800/90 hover:bg-white dark:hover:bg-gray-800 shadow-md backdrop-blur-sm flex items-center justify-center transition-transform hover:scale-110 active:scale-95 text-gray-500 hover:text-rose-500">
        <?php if ($isSaved): ?>
          <svg class="w-4 h-4 text-rose-500 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        <?php else: ?>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        <?php endif; ?>
      </button>
    </form>
  </div>

  <!-- Content -->
  <div class="p-4 flex flex-col flex-1">
    <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-400 uppercase tracking-wider mb-1">
      <span><?= e($p['brand'] ?? $p['category']) ?></span>
      <span class="text-[11px] lowercase tracking-normal text-gray-400 font-mono"><?= e($p['sku'] ?? '') ?></span>
    </div>

    <a href="/product/<?= e($p['id']) ?>" class="font-semibold text-gray-900 dark:text-white line-clamp-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
      <?= e($p['name']) ?>
    </a>

    <!-- Rating & Reviews -->
    <div class="flex items-center gap-1.5 mt-1.5">
      <?= stars_html((float) $p['rating']) ?>
      <span class="text-xs font-medium text-gray-600 dark:text-gray-400"><?= number_format((float) $p['rating'], 1) ?></span>
      <span class="text-xs text-gray-400">(<?= number_format((int) $p['reviews']) ?>)</span>
    </div>

    <!-- Low Stock Alert -->
    <?php if ($p['inStock'] && !empty($p['stock_count']) && $p['stock_count'] <= 6): ?>
      <div class="mt-2 text-[11px] font-semibold text-amber-600 dark:text-amber-400 flex items-center gap-1">
        <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
        Only <?= (int) $p['stock_count'] ?> left in stock
      </div>
    <?php endif; ?>

    <!-- Pricing & Action -->
    <div class="mt-auto pt-4 flex items-center justify-between gap-2 border-t border-gray-100 dark:border-gray-700/50">
      <div>
        <div class="flex items-baseline gap-1.5">
          <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400"><?= money($p['price']) ?></span>
          <?php if (!empty($p['originalPrice'])): ?>
            <span class="text-xs text-gray-400 line-through"><?= money($p['originalPrice']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <form method="post" action="/products" class="inline">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/products') ?>">
        <button <?= $p['inStock'] ? '' : 'disabled' ?>
                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:bg-gray-200 dark:disabled:bg-gray-700 disabled:text-gray-400 text-white text-xs font-semibold px-3 py-2 shadow-sm transition-all hover:shadow-indigo-500/20">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          Add
        </button>
      </form>
    </div>
  </div>
</div>
