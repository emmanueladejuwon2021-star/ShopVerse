<?php /* ProductCard — port of src/components/ProductCard.tsx */ ?>
<div class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-shadow overflow-hidden flex flex-col">
  <a href="/product/<?= e($p['id']) ?>" class="relative block overflow-hidden">
    <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"
         class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
    <?php if (!empty($p['originalPrice'])): ?>
      <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
        -<?= (int) round((1 - $p['price'] / $p['originalPrice']) * 100) ?>%
      </span>
    <?php endif; ?>
    <?php if (!$p['inStock']): ?>
      <span class="absolute top-2 right-2 bg-gray-700 text-white text-xs font-semibold px-2 py-1 rounded">Out of stock</span>
    <?php endif; ?>
  </a>

  <div class="p-4 flex flex-col flex-1">
    <p class="text-xs uppercase tracking-wide text-gray-400"><?= e($p['category']) ?></p>
    <a href="/product/<?= e($p['id']) ?>" class="font-semibold mt-1 line-clamp-2 hover:text-indigo-600"><?= e($p['name']) ?></a>

    <div class="flex items-center gap-1 mt-2">
      <?= stars_html((float) $p['rating']) ?>
      <span class="text-xs text-gray-500 ml-1"><?= number_format((int) $p['reviews']) ?> reviews</span>
    </div>

    <div class="mt-auto pt-3 flex items-center justify-between">
      <div>
        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400"><?= money($p['price']) ?></span>
        <?php if (!empty($p['originalPrice'])): ?>
          <span class="text-sm text-gray-400 line-through ml-1"><?= money($p['originalPrice']) ?></span>
        <?php endif; ?>
      </div>
      <form method="post" action="/products">
        <?= csrf_field() ?><input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/products') ?>">
        <button <?= $p['inStock'] ? '' : 'disabled' ?>
                class="rounded-lg bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white text-sm font-semibold px-3 py-2">
          Add
        </button>
      </form>
    </div>
  </div>
</div>
