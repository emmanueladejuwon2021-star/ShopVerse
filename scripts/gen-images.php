<?php
/**
 * One-time generator: replaces the external "mock" product photos (Unsplash
 * hot-links) with locally served, branded SVG placeholders.
 *
 * Writes public/assets/products/{id}.svg and rewrites the "image" field of
 * data/products.json to point at them. Fully offline / deterministic.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$jsonPath = $root . '/data/products.json';
$outDir = $root . '/public/assets/products';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

$products = json_decode((string) file_get_contents($jsonPath), true);
if (!is_array($products)) { fwrite(STDERR, "bad products.json\n"); exit(1); }

$palettes = [
    ['#eef2ff', '#e0e7ff', '#4f46e5'], // indigo
    ['#ecfeff', '#cffafe', '#0891b2'], // cyan
    ['#f0fdf4', '#dcfce7', '#16a34a'], // green
    ['#fffbeb', '#fef3c7', '#d97706'], // amber
    ['#fdf2f8', '#fce7f3', '#db2777'], // pink
    ['#f5f3ff', '#ede9fe', '#7c3aed'], // violet
];

// category -> simple line-art glyph (stroke paths on a 100x100 canvas)
$glyphs = [
    'Electronics' => '<rect x="18" y="26" width="64" height="42" rx="4"/><path d="M38 76h24M50 68v8"/>',
    'Clothing'    => '<path d="M35 24l-16 10 8 14 8-5v32h30V43l8 5 8-14-16-10a15 15 0 0 1-30 0z"/>',
    'Accessories' => '<circle cx="50" cy="42" r="20"/><path d="M50 32v12l8 5"/><path d="M30 20l-8-8M70 20l8-8"/>',
    'Home'        => '<path d="M20 50L50 22l30 28"/><path d="M28 46v32h44V46"/><rect x="42" y="58" width="16" height="20"/>',
    'Sports'      => '<circle cx="50" cy="50" r="26"/><path d="M50 24v52M24 50h52M32 32c12 12 24 24 36 36M68 32C56 44 44 56 32 68"/>',
];
$defaultGlyph = '<rect x="26" y="34" width="48" height="36" rx="4"/><path d="M26 44h48M40 34v-8h20v8"/>';

$esc = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');

foreach ($products as $i => &$p) {
    [$bg1, $bg2, $ink] = $palettes[$i % count($palettes)];
    $glyph = $glyphs[$p['category']] ?? $defaultGlyph;
    $name = $esc($p['name']);
    $cat  = $esc(strtoupper($p['category']));

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400" role="img" aria-label="{$name}">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$bg1}"/>
      <stop offset="100%" stop-color="{$bg2}"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="url(#g)"/>
  <g transform="translate(120 90) scale(1.6)" fill="none" stroke="{$ink}" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" opacity="0.9">
    {$glyph}
  </g>
  <text x="200" y="316" text-anchor="middle" font-family="system-ui, -apple-system, sans-serif" font-size="22" font-weight="700" fill="{$ink}">{$name}</text>
  <text x="200" y="344" text-anchor="middle" font-family="system-ui, -apple-system, sans-serif" font-size="14" letter-spacing="3" fill="{$ink}" opacity="0.65">{$cat}</text>
</svg>
SVG;

    file_put_contents("{$outDir}/{$p['id']}.svg", $svg);
    $p['image'] = "/assets/products/{$p['id']}.svg";
}
unset($p);

file_put_contents($jsonPath, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "Regenerated " . count($products) . " product images and updated data/products.json\n";
