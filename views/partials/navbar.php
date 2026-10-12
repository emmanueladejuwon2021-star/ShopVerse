<?php /* Navbar — port of src/components/Navbar.tsx */ 
      $user = current_user();
      $count = cart_total_items();
      $pathNow = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
      $nav = [
          '/' => 'Home',
          '/products' => 'Shop',
          '/orders' => 'Orders',
      ];
?>
<header class="sticky top-0 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-800">
  <nav class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
    <a href="/" class="text-xl font-extrabold tracking-tight text-indigo-600 dark:text-indigo-400">ShopVerse</a>

    <div class="hidden md:flex items-center gap-6">
      <?php foreach ($nav as $href => $label): ?>
        <a href="<?= e($href) ?>"
           class="text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400 <?= $pathNow === $href ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300' ?>">
          <?= e($label) ?>
        </a>
      <?php endforeach; ?>
      <?php if ($user): ?>
        <a href="/account" class="text-sm font-medium <?= $pathNow === '/account' ? 'text-indigo-600' : 'text-gray-600 dark:text-gray-300' ?> hover:text-indigo-600">Account</a>
      <?php endif; ?>
    </div>

    <form action="/products" method="get" class="hidden sm:block flex-1 max-w-xs">
      <input type="search" name="search" placeholder="Search products…" value="<?= e($_GET['search'] ?? '') ?>"
             class="w-full rounded-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-4 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </form>

    <div class="flex items-center gap-3">
      <button data-theme-toggle aria-label="Toggle theme"
              class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
        <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4l1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4m11.4-11.4l1.4-1.4"/></svg>
      </button>

      <button data-cart-open-btn aria-label="Open cart" class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
        <?php if ($count > 0): ?>
          <span class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"><?= (int) $count ?></span>
        <?php endif; ?>
      </button>

      <?php if ($user): ?>
        <div class="hidden md:flex items-center gap-2">
          <span class="text-sm font-medium"><?= e($user['name']) ?></span>
          <form method="post" action="/logout">
            <?= csrf_field() ?><input type="hidden" name="action" value="logout">
            <button class="text-sm text-gray-500 hover:text-red-500">Sign out</button>
          </form>
        </div>
      <?php else: ?>
        <a href="/login" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Sign in</a>
      <?php endif; ?>

      <button id="mobile-menu-btn" aria-label="Menu" class="md:hidden p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
  </nav>

  <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 dark:border-gray-800 px-4 py-3 space-y-2 bg-white dark:bg-gray-900">
    <?php foreach ($nav as $href => $label): ?>
      <a href="<?= e($href) ?>" class="block py-1 text-sm font-medium"><?= e($label) ?></a>
    <?php endforeach; ?>
    <?php if ($user): ?>
      <a href="/account" class="block py-1 text-sm font-medium">Account</a>
      <form method="post" action="/logout"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button class="block py-1 text-sm text-red-500">Sign out</button></form>
    <?php else: ?>
      <a href="/login" class="block py-1 text-sm font-medium text-indigo-600">Sign in</a>
    <?php endif; ?>
  </div>
</header>
