<?php /* ProductDetail — port of src/pages/ProductDetail.tsx */
      $product = find_product((string) ($params['id'] ?? ''));
      if (!$product): ?>
        <div class="max-w-7xl mx-auto px-4 py-20 text-center">
          <p class="text-5xl mb-4">😕</p>
          <h1 class="text-2xl font-bold">Product not found</h1>
          <a href="/products" class="text-indigo-600 hover:underline mt-3 inline-block">← Back to shop</a>
        </div>
<?php else:
      $related = array_values(array_filter(all_products(), fn($p) => $p['category'] === $product['category'] && $p['id'] !== $product['id']));
      $qty = 1; // quantity selector handled by native number input in the form
?>
<div class="max-w-7xl mx-auto px-4 py-10">
  <nav class="text-sm text-gray-500 mb-6">
    <a href="/" class="hover:text-indigo-600">Home</a> /
    <a href="/products" class="hover:text-indigo-600">Shop</a> /
    <a href="/products?category=<?= urlencode($product['category']) ?>" class="hover:text-indigo-600"><?= e($product['category']) ?></a> /
    <span class="text-gray-800 dark:text-gray-200 font-medium"><?= e($product['name']) ?></span>
  </nav>

  <div class="grid md:grid-cols-2 gap-10">
    <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>"
         class="w-full rounded-2xl object-cover shadow-md aspect-square">

    <div>
      <p class="text-sm uppercase tracking-wide text-indigo-600 dark:text-indigo-400 font-semibold"><?= e($product['category']) ?></p>
      <h1 class="text-3xl font-bold mt-1"><?= e($product['name']) ?></h1>

      <div class="flex items-center gap-2 mt-3">
        <?= stars_html((float) $product['rating']) ?>
        <span class="text-sm text-gray-500"><?= e((string) $product['rating']) ?> · <?= number_format((int) $product['reviews']) ?> reviews</span>
      </div>

      <div class="flex items-baseline gap-3 mt-5">
        <span class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400"><?= money($product['price']) ?></span>
        <?php if (!empty($product['originalPrice'])): ?>
          <span class="text-xl text-gray-400 line-through"><?= money($product['originalPrice']) ?></span>
          <span class="text-sm font-semibold text-green-600">Save <?= money($product['originalPrice'] - $product['price']) ?></span>
        <?php endif; ?>
      </div>

      <p class="mt-2 text-sm font-medium <?= $product['inStock'] ? 'text-green-600' : 'text-red-500' ?>">
        <?= $product['inStock'] ? '● In stock' : '● Out of stock' ?>
      </p>

      <p class="mt-5 text-gray-600 dark:text-gray-300 leading-relaxed"><?= e($product['description']) ?></p>

      <ul class="mt-5 space-y-1 text-sm">
        <?php foreach ($product['features'] as $f): ?>
          <li class="flex items-center gap-2"><span class="text-indigo-500">✓</span> <?= e($f) ?></li>
        <?php endforeach; ?>
      </ul>

      <form method="post" class="mt-8 flex items-center gap-4">
        <input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
        <input type="hidden" name="redirect" value="/product/<?= e($product['id']) ?>">
        <input type="number" name="quantity" min="1" max="10" value="1"
               class="w-20 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-center">
        <button <?= $product['inStock'] ? '' : 'disabled' ?>
                class="flex-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-semibold py-3">
          Add to Cart
        </button>
      </form>
    </div>
  </div>

  <?php if ($related): ?>
    <section class="mt-16">
      <h2 class="text-2xl font-bold mb-6">Related Products</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach (array_slice($related, 0, 4) as $p): ?>
          <?php include __DIR__ . '/../partials/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>
<?php endif; ?>
