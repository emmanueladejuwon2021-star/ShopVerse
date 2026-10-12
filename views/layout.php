<?php /* Layout — replaces App.tsx shell (ThemeProvider + Router + Navbar/CartSidebar/Footer/main). */ ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'ShopVerse') ?></title>
  <script>
    // ThemeContext port: persisted theme w/ system-preference fallback (no-flash inline)
    (function () {
      var saved = localStorage.getItem('theme');
      var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (dark) document.documentElement.classList.add('dark');
    })();
  </script>
  <link rel="stylesheet" href="/assets/app.css">
</head>

<?php $_cartOpenFlag = !empty($cart_open_once); ?>
<body data-cart-open="<?= $_cartOpenFlag ? 1 : 0 ?>" class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col transition-colors duration-300">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <?php include __DIR__ . '/partials/cart-sidebar.php'; ?>

  <main class="flex-1">
    <?php if ($msg = flash()): ?>
      <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="rounded-lg px-4 py-3 text-sm font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-700">
          <?= e($msg) ?>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($emsg = flash_error()): ?>
      <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="rounded-lg px-4 py-3 text-sm font-medium bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-200 border border-red-200 dark:border-red-700" role="alert">
          <?= e($emsg) ?>
        </div>
      </div>
    <?php endif; ?>

    <?php
    // server-side "route element" switch — mirrors <Routes> in App.tsx
    render('pages/' . $page, $params);
    ?>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <script>
    // Theme toggle button (replaces ThemeContext useTheme().toggleTheme)
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var root = document.documentElement;
        var dark = root.classList.toggle('dark');
        localStorage.setItem('theme', dark ? 'dark' : 'light');
      });
    });

    // Mobile menu (Navbar hamburger)
    var menuBtn = document.getElementById('mobile-menu-btn');
    var mobileMenu = document.getElementById('mobile-menu');
    if (menuBtn && mobileMenu) {
      menuBtn.addEventListener('click', function () { mobileMenu.classList.toggle('hidden'); });
    }

    // Cart sidebar open/close (replaces isOpen state of CartContext).
    // Driven purely by the [data-open] attribute + inline CSS in the sidebar
    // partial — no Tailwind class juggling, so a stale/missing app.css can
    // never leave the drawer stuck over the page. Default state: CLOSED.
    function setCartOpen(open) {
      var el = document.getElementById('cart-sidebar');
      var overlay = document.getElementById('cart-overlay');
      if (!el) return;
      if (open) {
        el.setAttribute('data-open', '');
        if (overlay) overlay.setAttribute('data-open', '');
      } else {
        el.removeAttribute('data-open');
        if (overlay) overlay.removeAttribute('data-open');
      }
    }
    document.querySelectorAll('[data-cart-open-btn]').forEach(function (b) {
      b.addEventListener('click', function (ev) { ev.preventDefault(); setCartOpen(true); });
    });
    document.querySelectorAll('[data-cart-close]').forEach(function (b) {
      b.addEventListener('click', function () { setCartOpen(false); });
    });
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape') setCartOpen(false);
    });
    // Server told us to open the sidebar after an add-to-cart (one-shot):
    // strict === '1' check, and the attribute is stripped immediately so a
    // reload / back-forward restore can never re-open it.
    if (document.body.getAttribute('data-cart-open') === '1') {
      document.body.removeAttribute('data-cart-open');
      setCartOpen(true);
    }
  </script>
</body>
</html>
