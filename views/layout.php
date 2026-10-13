<?php /* Layout — replaces App.tsx shell (ThemeProvider + Router + Navbar/CartSidebar/Footer/main). */ ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'ShopVerse — Premium Online Store') ?></title>
  
  <!-- SEO Meta Tags -->
  <meta name="description" content="ShopVerse is a modern e-commerce storefront featuring premium electronics, apparel, home essentials, and sports gear. Free express shipping on orders over $50.">
  <meta name="keywords" content="ecommerce, shop, online store, electronics, clothing, accessories, home decor, sports gear, shopverse">
  <meta name="theme-color" content="#4f46e5">

  <!-- Open Graph & Social Sharing -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ShopVerse">
  <meta property="og:title" content="<?= e($title ?? 'ShopVerse — Premium Online Store') ?>">
  <meta property="og:description" content="Curated premium lifestyle goods, electronics, and accessories. Fast delivery and 30-day money-back guarantee.">
  <meta property="og:image" content="/assets/products/placeholder.svg">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($title ?? 'ShopVerse — Premium Online Store') ?>">
  <meta name="twitter:description" content="Discover premium electronics, apparel, and lifestyle essentials.">

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='50' fill='%234f46e5'/><text y='65' x='50' font-size='50' font-weight='bold' fill='white' text-anchor='middle'>S</text></svg>">

  <script>
    // ThemeContext: persisted theme with system-preference fallback (zero flash)
    (function () {
      var saved = localStorage.getItem('theme');
      var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (dark) document.documentElement.classList.add('dark');
    })();
  </script>
  <link rel="stylesheet" href="/assets/app.css">

  <style>
    @media print {
      header, footer, #cart-sidebar, #cart-overlay, [data-theme-toggle], .print\:hidden {
        display: none !important;
      }
      body {
        background: white !important;
        color: black !important;
      }
    }
  </style>
</head>
<?php $_cartOpenFlag = !empty($_SESSION["cart_open"]); ?>
<body data-cart-open="<?= $_cartOpenFlag ? 1 : 0 ?>" class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col transition-colors duration-300">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <?php include __DIR__ . '/partials/cart-sidebar.php'; ?>

  <!-- Toast Notification Container (Floating) -->
  <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
    <?php if ($msg = flash()): ?>
      <div class="toast-item pointer-events-auto rounded-2xl p-4 text-xs font-semibold bg-gray-900/95 dark:bg-white/95 text-white dark:text-gray-900 shadow-2xl backdrop-blur-md flex items-center justify-between gap-3 border border-white/10 animate-slide-up transition-all duration-300">
        <div class="flex items-center gap-2.5">
          <span class="text-base">✨</span>
          <span><?= e($msg) ?></span>
        </div>
        <button onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100 text-sm">✕</button>
      </div>
    <?php endif; ?>

    <?php if ($emsg = flash_error()): ?>
      <div class="toast-item pointer-events-auto rounded-2xl p-4 text-xs font-semibold bg-rose-600/95 text-white shadow-2xl backdrop-blur-md flex items-center justify-between gap-3 border border-rose-400/30 animate-slide-up transition-all duration-300">
        <div class="flex items-center gap-2.5">
          <span class="text-base">⚠️</span>
          <span><?= e($emsg) ?></span>
        </div>
        <button onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100 text-sm">✕</button>
      </div>
    <?php endif; ?>
  </div>

  <main class="flex-1">
    <?php
    render('pages/' . $page, $params);
    ?>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <script>
    // Theme toggle button
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

    // Cart sidebar open/close
    function setCartOpen(open) {
      var el = document.getElementById('cart-sidebar');
      var overlay = document.getElementById('cart-overlay');
      if (!el) return;
      el.classList.toggle('translate-x-full', !open);
      if (overlay) overlay.classList.toggle('hidden', !open);
      document.body.style.overflow = open ? 'hidden' : '';
    }
    document.querySelectorAll('[data-cart-open]').forEach(function (b) {
      b.addEventListener('click', function (ev) { ev.preventDefault(); setCartOpen(true); });
    });
    document.querySelectorAll('[data-cart-close]').forEach(function (b) {
      b.addEventListener('click', function () { setCartOpen(false); });
    });

    // ESC key closes modals / sidebars
    window.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') setCartOpen(false);
    });

    // Server-instructed cart open flag
    if (document.body.getAttribute('data-cart-open') === '1') {
      setCartOpen(true);
    }

    // Auto-dismiss toast notifications after 4 seconds
    setTimeout(function() {
      document.querySelectorAll('.toast-item').forEach(function(item) {
        item.style.opacity = '0';
        item.style.transform = 'translateY(10px)';
        setTimeout(function() { item.remove(); }, 300);
      });
    }, 4000);

    // Toast Generator Helper
    function showToast(msg, isError) {
      var container = document.getElementById('toast-container');
      if (!container) return;
      var toast = document.createElement('div');
      toast.className = 'toast-item pointer-events-auto rounded-2xl p-4 text-xs font-semibold ' +
        (isError ? 'bg-rose-600/95 text-white border-rose-400/30' : 'bg-gray-900/95 dark:bg-white/95 text-white dark:text-gray-900 border-white/10') +
        ' shadow-2xl backdrop-blur-md flex items-center justify-between gap-3 border animate-slide-up transition-all duration-300';
      toast.innerHTML = '<div class="flex items-center gap-2.5"><span class="text-base">' + (isError ? '⚠️' : '✨') + '</span><span>' + msg + '</span></div><button onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100 text-sm">✕</button>';
      container.appendChild(toast);
      setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(function() { toast.remove(); }, 300);
      }, 4000);
    }

    // Progressive Enhancement: AJAX Add-to-Cart & Wishlist
    document.addEventListener('submit', function(e) {
      var form = e.target;
      var actionInput = form.querySelector('input[name="action"]');
      var actionVal = actionInput ? actionInput.value : '';

      if (actionVal === 'add_to_cart' || actionVal === 'toggle_wishlist') {
        e.preventDefault();
        var formData = new FormData(form);

        fetch(form.action || window.location.href, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
          if (data && data.ok) {
            showToast(data.message || 'Updated successfully!', false);

            if (typeof data.cartCount !== 'undefined') {
              var cBadge = document.getElementById('nav-cart-count');
              if (cBadge) {
                cBadge.innerText = data.cartCount;
                cBadge.classList.toggle('hidden', data.cartCount <= 0);
              }
            }
            if (typeof data.wishlistCount !== 'undefined') {
              var wBadge = document.getElementById('nav-wishlist-count');
              if (wBadge) {
                wBadge.innerText = data.wishlistCount;
                wBadge.classList.toggle('hidden', data.wishlistCount <= 0);
              }
            }

            if (actionVal === 'add_to_cart') {
              setCartOpen(true);
            }
          } else {
            form.submit();
          }
        })
        .catch(function() {
          form.submit();
        });
      }
    });
  </script>
</body>
</html>
