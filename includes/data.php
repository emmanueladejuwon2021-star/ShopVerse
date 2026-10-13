<?php
/**
 * Product catalog + categories.
 *
 * NOTE: src/data/products.ts in the original React app exported an EMPTY
 * array (flagged as CRITICAL in the audit), which made every storefront page
 * render empty states. The PHP port restores a real catalog so the app works.
 */

declare(strict_types=1);

function get_categories(): array
{
    return ['All', 'Electronics', 'Accessories', 'Home', 'Sports'];
}

function get_brands(): array
{
    $brands = [];
    foreach (all_products() as $p) {
        if (!empty($p['brand']) && !in_array($p['brand'], $brands, true)) {
            $brands[] = $p['brand'];
        }
    }
    sort($brands);
    return $brands;
}

/** @return array<int, array<string,mixed>> */
function all_products(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = json_decode(
        file_get_contents(__DIR__ . '/../data/products.json'),
        true
    );
    return $cache;
}

function find_product(string $id): ?array
{
    foreach (all_products() as $p) {
        if ((string) $p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function featured_products(int $limit = 4): array
{
    $items = all_products();
    usort($items, fn($a, $b) => $b['rating'] <=> $a['rating']);
    return array_slice($items, 0, $limit);
}

/**
 * Filter/sort pipeline — mirrors the useMemo logic of src/pages/Products.tsx.
 */
function filter_products(array $opts): array
{
    $items = all_products();

    if (!empty($opts['category']) && $opts['category'] !== 'All') {
        $items = array_values(array_filter($items, fn($p) => $p['category'] === $opts['category']));
    }

    if (!empty($opts['brand']) && $opts['brand'] !== 'All') {
        $items = array_values(array_filter($items, fn($p) => ($p['brand'] ?? '') === $opts['brand']));
    }

    if (!empty($opts['search'])) {
        $q = strtolower($opts['search']); // audit fix: mbstring not guaranteed on all hosts
        $items = array_values(array_filter($items, function ($p) use ($q) {
            return str_contains(strtolower($p['name']), $q)
                || str_contains(strtolower($p['description']), $q)
                || str_contains(strtolower($p['brand'] ?? ''), $q)
                || str_contains(strtolower($p['sku'] ?? ''), $q);
        }));
    }

    if (!empty($opts['maxPrice'])) {
        $max = (float) $opts['maxPrice'];
        $items = array_values(array_filter($items, fn($p) => $p['price'] <= $max));
    }

    if (!empty($opts['minRating'])) {
        $min = (float) $opts['minRating'];
        $items = array_values(array_filter($items, fn($p) => $p['rating'] >= $min));
    }

    if (!empty($opts['inStockOnly'])) {
        $items = array_values(array_filter($items, fn($p) => !empty($p['inStock'])));
    }

    if (!empty($opts['onSaleOnly'])) {
        $items = array_values(array_filter($items, fn($p) => !empty($p['originalPrice']) && $p['originalPrice'] > $p['price']));
    }

    switch ($opts['sort'] ?? 'featured') {
        case 'price-low':
            usort($items, fn($a, $b) => $a['price'] <=> $b['price']);
            break;
        case 'price-high':
            usort($items, fn($a, $b) => $b['price'] <=> $a['price']);
            break;
        case 'rating':
            usort($items, fn($a, $b) => $b['rating'] <=> $a['rating']);
            break;
        case 'newest':
            // Audit fix: original used parseInt(id) which breaks on non-numeric ids.
            usort($items, fn($a, $b) => $b['id'] <=> $a['id']);
            break;
    }

    return $items;
}
