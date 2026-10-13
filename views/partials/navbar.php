<?php
/**
 * Production Navbar Component
 * Features currency switcher, live search autocomplete dropdown, tracking shortcut,
 * wishlist/cart count badges, theme switcher, and mobile menu.
 */
$user = current_user();
$cartCount = cart_total_items();
$wishlistCount = count(wishlist_ids());
$pathNow = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$activeCurrency = active_currency();
$currencies = supported_currencies();
$nav = [
    '/' => 'Home',
    '/products' => 'Shop',
    '/track' => 'Track Order',
    '/wishlist' => 'Wishlist',
    '/orders' => 'Orders',
];
?>
<!-- Top Utility & Promotional Announcement Bar -->
<div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 text-white text-[11px] sm:text-xs py-2 px-4 tracking-wide border-b border-indigo-700/50">
  <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
    <!-- Announcement Text -->
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-500 text-[10px] text-white font-bold">✓</span>
      <span>Spring Tech Event: Use code <strong class="underline font-bold tracking-wider text-amber-300">SHOP10</strong> for 10% off · Orders over $100 ship FREE</span>
    </div>

    <!-- Quick Utilities (Currency Selector & Order Tracker Link) -->
    <div class="flex items-center gap-4 text-indigo-200">
      <a href="/track" class="hover:text-white transition-colors flex items-center gap-1 font-medium">
        <span>📍</span> Track Shipment
      </a>

      <!-- Currency Switcher Form -->
      <form method="post" action="/products" class="flex items-center gap-1 text-[11px]">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="set_currency">
        <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
        <label for="currency-select" class="sr-only">Currency</label>
        <span class="text-indigo-300">Currency:</span>
        <select id="currency-select" name="currency" onchange="this.form.submit()"
                class="bg-indigo-950/80 text-white border border-indigo-700/60 rounded-lg px-2 py-0.5 text-[11px] font-bold focus:outline-none focus:ring-1 focus:ring-indigo-400 cursor-pointer">
          <?php foreach ($currencies as $code => $info): ?>
            <option value="<?= e($code) ?>" <?= $activeCurrency === $code ? 'selected' : '' ?>>
              <?= e($info['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>
</div>

<header class="sticky top-0 z-40 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-b border-gray-200/80 dark:border-gray-800 transition-colors shadow-xs">
  <nav class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
    <!-- Brand Logo -->
    <a href="/" class="flex items-center gap-2.5 group shrink-0">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center font-black text-lg shadow-md shadow-indigo-600/30 group-hover:scale-105 transition-transform">
        S
      </div>
      <span class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white">
        Shop<span class="text-indigo-600 dark:text-indigo-400">Verse</span>
      </span>
    </a>

    <!-- Desktop Navigation Links -->
    <div class="hidden lg:flex items-center gap-7">
      <?php foreach ($nav as $href => $label): ?>
        <a href="<?= e($href) ?>"
           class="text-sm font-semibold transition-colors <?= $pathNow === $href ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400' ?>">
          <?= e($label) ?>
        </a>
      <?php endforeach; ?>
      <?php if ($user): ?>
        <a href="/account" class="text-sm font-semibold transition-colors <?= $pathNow === '/account' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400' ?>">
          Account
        </a>
      <?php endif; ?>
    </div>

    <!-- Quick Search with Instant Autocomplete Dropdown -->
    <div class="hidden sm:block flex-1 max-w-sm relative" id="navbar-search-wrapper">
      <form action="/products" method="get" class="relative">
        <input id="nav-search-input" type="search" name="search" autocomplete="off"
               placeholder="Search products, Apple, Sony, specs…"
               value="<?= e($_GET['search'] ?? '') ?>"
               class="w-full rounded-full border border-gray-200 dark:border-gray-700 bg-gray-100/90 dark:bg-gray-800/90 pl-10 pr-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </form>

      <!-- Autocomplete Results Dropdown -->
      <div id="nav-search-results" class="hidden absolute left-0 right-0 top-full mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700/80 overflow-hidden z-50 divide-y divide-gray-100 dark:divide-gray-700/50">
        <!-- populated via JS -->
      </div>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-1.5 sm:gap-3">
      <!-- Theme Toggle -->
      <button data-theme-toggle aria-label="Toggle theme" title="Toggle theme"
              class="p-2 rounded-full text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4l1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4m11.4-11.4l1.4-1.4"/></svg>
      </button>

      <!-- Wishlist Button -->
      <a href="/wishlist" aria-label="Wishlist" title="Saved Items"
         class="relative p-2 rounded-full text-gray-500 hover:text-rose-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        <span id="nav-wishlist-count" class="<?= $wishlistCount > 0 ? '' : 'hidden' ?> absolute -top-0.5 -right-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center shadow-sm">
          <?= (int) $wishlistCount ?>
        </span>
      </a>

      <!-- Cart Trigger Button -->
      <button type="button" data-cart-open aria-label="Open cart" title="Your Cart"
              class="relative p-2 rounded-full text-gray-500 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
        <span id="nav-cart-count" class="<?= $cartCount > 0 ? '' : 'hidden' ?> absolute -top-0.5 -right-0.5 bg-indigo-600 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center shadow-sm animate-scale">
          <?= (int) $cartCount ?>
        </span>
      </button>

      <!-- User Account / Auth -->
      <?php if ($user): ?>
        <div class="hidden sm:flex items-center gap-3 pl-2 border-l border-gray-200 dark:border-gray-800">
          <a href="/account" class="text-xs font-semibold text-gray-800 dark:text-gray-200 hover:text-indigo-600 flex items-center gap-1.5">
            <span class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-[10px]">
              <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
            </span>
            <span class="max-w-[100px] truncate"><?= e($user['name']) ?></span>
          </a>
          <form method="post" action="/logout">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="logout">
            <button class="text-xs text-gray-400 hover:text-rose-500 transition-colors">Sign out</button>
          </form>
        </div>
      <?php else: ?>
        <a href="/login" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3.5 py-2 rounded-xl shadow-sm transition-all shrink-0">
          Sign In
        </a>
      <?php endif; ?>

      <!-- Mobile Hamburger Button -->
      <button id="mobile-menu-btn" aria-label="Menu" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
  </nav>

  <!-- Mobile Dropdown Menu -->
  <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-200 dark:border-gray-800 px-4 py-4 space-y-3 bg-white dark:bg-gray-900 shadow-xl">
    <form action="/products" method="get" class="relative pb-2">
      <input type="search" name="search" placeholder="Search products, brands…" value="<?= e($_GET['search'] ?? '') ?>"
             class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </form>
    <?php foreach ($nav as $href => $label): ?>
      <a href="<?= e($href) ?>" class="block py-1.5 text-sm font-semibold text-gray-800 dark:text-gray-200"><?= e($label) ?></a>
    <?php endforeach; ?>
    <a href="/cart" class="py-1.5 text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center justify-between">
      <span>Cart</span>
      <span class="bg-indigo-600 text-white text-[11px] px-2 py-0.5 rounded-full font-bold"><?= (int) $cartCount ?></span>
    </a>
    <?php if ($user): ?>
      <a href="/account" class="block py-1.5 text-sm font-semibold text-gray-800 dark:text-gray-200">Account (<?= e($user['name']) ?>)</a>
      <form method="post" action="/logout">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="logout">
        <button class="block py-1.5 text-sm font-semibold text-rose-500">Sign out</button>
      </form>
    <?php else: ?>
      <a href="/login" class="block py-1.5 text-sm font-bold text-indigo-600">Sign in / Create Account</a>
    <?php endif; ?>
  </div>
</header>

<script>
  // Live Instant Search Autocomplete
  (function () {
    var searchInput = document.getElementById('nav-search-input');
    var resultsBox = document.getElementById('nav-search-results');
    var debounceTimer = null;

    if (!searchInput || !resultsBox) return;

    searchInput.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      var query = searchInput.value.trim();

      if (query.length < 2) {
        resultsBox.innerHTML = '';
        resultsBox.classList.add('hidden');
        return;
      }

      debounceTimer = setTimeout(function () {
        fetch('/api/search?q=' + encodeURIComponent(query))
          .then(function (res) { return res.json(); })
          .then(function (data) {
            if (!data || data.length === 0) {
              resultsBox.innerHTML = '<div class="p-4 text-center text-xs text-gray-500">No matching products found for "' + query + '"</div>';
              resultsBox.classList.remove('hidden');
              return;
            }

            var html = '';
            data.forEach(function (p) {
              html += '<a href="' + p.url + '" class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-colors">' +
                '<img src="' + p.image + '" alt="' + p.name + '" onerror="this.src=\'/assets/products/placeholder.svg\'" class="w-10 h-10 rounded-lg object-cover bg-gray-100 dark:bg-gray-900 shrink-0 border border-gray-200 dark:border-gray-700">' +
                '<div class="flex-1 min-w-0">' +
                  '<div class="flex items-center gap-1.5">' +
                    '<span class="text-[10px] font-bold uppercase text-indigo-600 dark:text-indigo-400">' + p.brand + '</span>' +
                    (p.badge ? '<span class="text-[9px] bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.2 rounded font-semibold">' + p.badge + '</span>' : '') +
                  '</div>' +
                  '<p class="text-xs font-semibold text-gray-900 dark:text-white truncate">' + p.name + '</p>' +
                '</div>' +
                '<span class="text-xs font-bold text-gray-900 dark:text-white shrink-0">' + p.price + '</span>' +
              '</a>';
            });

            html += '<a href="/products?search=' + encodeURIComponent(query) + '" class="block p-2.5 text-center text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-gray-50/70 dark:bg-gray-900/40 hover:underline">View all results for "' + query + '" →</a>';

            resultsBox.innerHTML = html;
            resultsBox.classList.remove('hidden');
          })
          .catch(function () {
            resultsBox.classList.add('hidden');
          });
      }, 250);
    });

    // Close on click outside
    document.addEventListener('click', function (e) {
      if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
        resultsBox.classList.add('hidden');
      }
    });

    // Close on Escape
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') resultsBox.classList.add('hidden');
    });
  })();
</script>
