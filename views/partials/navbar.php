<?php
/**
 * Production Navbar Component
 * Features promotional announcement strip, live cart and wishlist count badges, theme switcher, and mobile menu.
 */
$user = current_user();
$cartCount = cart_total_items();
$wishlistCount = count(wishlist_ids());
$pathNow = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$nav = [
    '/' => 'Home',
    '/products' => 'Shop',
    '/wishlist' => 'Wishlist',
    '/orders' => 'Orders',
];
?>
<!-- Top Promotional Banner -->
<div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-purple-700 text-white text-[11px] sm:text-xs font-medium py-1.5 px-4 text-center tracking-wide">
  <span class="inline-flex items-center gap-1.5">
    <span>✨</span>
    <span>Spring Sale: Use code <strong class="underline font-bold tracking-wider">SHOP10</strong> for 10% off entire order · Free Shipping over $50</span>
  </span>
</div>

<header class="sticky top-0 z-40 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-200/80 dark:border-gray-800 transition-colors">
  <nav class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
    <!-- Brand Logo -->
    <a href="/" class="flex items-center gap-2 group">
      <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-base shadow-md shadow-indigo-600/30 group-hover:scale-105 transition-transform">
        S
      </div>
      <span class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white">
        Shop<span class="text-indigo-600 dark:text-indigo-400">Verse</span>
      </span>
    </a>

    <!-- Desktop Navigation Links -->
    <div class="hidden md:flex items-center gap-7">
      <?php foreach ($nav as $href => $label): ?>
        <a href="<?= e($href) ?>"
           class="text-sm font-semibold transition-colors <?= $pathNow === $href ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400' ?>">
          <?= e($label) ?>
        </a>
      <?php endforeach; ?>
      <?php if ($user): ?>
        <a href="/account" class="text-sm font-semibold transition-colors <?= $pathNow === '/account' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400' ?>">
          Account
        </a>
      <?php endif; ?>
    </div>

    <!-- Quick Search Input -->
    <form action="/products" method="get" class="hidden sm:block flex-1 max-w-xs relative">
      <input type="search" name="search" placeholder="Search products, brands…" value="<?= e($_GET['search'] ?? '') ?>"
             class="w-full rounded-full border border-gray-200 dark:border-gray-700 bg-gray-100/80 dark:bg-gray-800/80 pl-9 pr-4 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
      <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </form>

    <!-- Right Controls -->
    <div class="flex items-center gap-2 sm:gap-3">
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
        <?php if ($wishlistCount > 0): ?>
          <span class="absolute -top-0.5 -right-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center shadow-sm">
            <?= (int) $wishlistCount ?>
          </span>
        <?php endif; ?>
      </a>

      <!-- Cart Trigger Button -->
      <button data-cart-open aria-label="Open cart" title="Your Cart"
              class="relative p-2 rounded-full text-gray-500 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
        <?php if ($cartCount > 0): ?>
          <span class="absolute -top-0.5 -right-0.5 bg-indigo-600 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center shadow-sm animate-scale">
            <?= (int) $cartCount ?>
          </span>
        <?php endif; ?>
      </button>

      <!-- User Account / Auth -->
      <?php if ($user): ?>
        <div class="hidden md:flex items-center gap-3 pl-2 border-l border-gray-200 dark:border-gray-800">
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
        <a href="/login" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3.5 py-2 rounded-xl shadow-sm transition-all">
          Sign In
        </a>
      <?php endif; ?>

      <!-- Mobile Hamburger Button -->
      <button id="mobile-menu-btn" aria-label="Menu" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
  </nav>

  <!-- Mobile Dropdown Menu -->
  <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 dark:border-gray-800 px-4 py-4 space-y-3 bg-white dark:bg-gray-900 shadow-xl">
    <form action="/products" method="get" class="relative pb-2">
      <input type="search" name="search" placeholder="Search products…" value="<?= e($_GET['search'] ?? '') ?>"
             class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </form>
    <?php foreach ($nav as $href => $label): ?>
      <a href="<?= e($href) ?>" class="block py-1.5 text-sm font-semibold text-gray-800 dark:text-gray-200"><?= e($label) ?></a>
    <?php endforeach; ?>
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
