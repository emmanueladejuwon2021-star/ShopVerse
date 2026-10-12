<?php /* Layout — replaces App.tsx shell (ThemeProvider + Router + Navbar/CartSidebar/Footer/main). */
      // Audit pass 2: the interactive script below is served with a per-request
      // CSP hash instead of blanket 'unsafe-inline'. The hash must be computed
      // over the EXACT bytes emitted between the <script> tags.
      $inlineScript = <<<'JS'
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var root = document.documentElement;
        var dark = root.classList.toggle('dark');
        localStorage.setItem('theme', dark ? 'dark' : 'light');
      });
    });
    var menuBtn = document.getElementById('mobile-menu-btn');
    var mobileMenu = document.getElementById('mobile-menu');
    if (menuBtn && mobileMenu) {
      menuBtn.addEventListener('click', function () { mobileMenu.classList.toggle('hidden'); });
    }
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
    if (document.body.getAttribute('data-cart-open') === '1') {
      document.body.removeAttribute('data-cart-open');
      setCartOpen(true);
    }
JS;
      $cspScriptHash = 'sha256-' . base64_encode(hash('sha256', $inlineScript, true));
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'ShopVerse') ?></title>
  <script src="/assets/theme-bootstrap.js"></script>
  <link rel="stylesheet" href="/assets/app.css">
  <?php // Audit pass 2: extend the server's script-src with this page's inline hash. ?>
  <meta http-equiv="Content-Security-Policy"
        content="script-src 'self' <?= e(SHOPVERSE_CSP_SCRIPT_SRC) ?> '<?= e($cspScriptHash) ?>'; style-src 'self' 'unsafe-inline'">
</head>

<?php
// Belt-and-braces: even if some upstream bug ever passes a truthy flag, the
// drawer may NEVER render open on the home page. Closed is the default.
$_cartOpenFlag = !empty($cart_drawer_open) && $page !== 'home';
?>
<body data-cart-open="<?= $_cartOpenFlag ? 1 : 0 ?>" class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <?php include __DIR__ . '/partials/cart-sidebar.php'; ?>

  <main class="flex-1">
    <?php if ($msg = flash()): ?>
      <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="rounded-lg px-4 py-3 text-sm font-medium glass-card border !border-indigo-300/50 dark:!border-indigo-500/30 text-indigo-700 dark:text-indigo-200">
          <?= e($msg) ?>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($emsg = flash_error()): ?>
      <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="rounded-lg px-4 py-3 text-sm font-medium glass-card border !border-red-300/50 dark:!border-red-500/30 text-red-700 dark:text-red-200" role="alert">
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

  <script><?= $inlineScript ?></script>
</body>
</html>
