<?php /* Login — port of src/pages/Login.tsx (tabbed sign-in / register) */
      $mode = ($_GET['mode'] ?? 'login') === 'register' ? 'register' : 'login';
      // Audit pass 1: validate the user-supplied ?redirect= BEFORE echoing it
      // into the form (and before login_user() ever sees it) — same open-redirect
      // policy the POST handler uses.
      $redirect = validate_redirect((string) ($_GET['redirect'] ?? '/'));
?>
<div class="max-w-md mx-auto px-4 py-16">
  <div class="glass-card rounded-2xl shadow-lg p-8">
    <h1 class="text-2xl font-bold text-center"><?= $mode === 'login' ? 'Welcome Back' : 'Create Account' ?></h1>
    <p class="text-sm text-glass-muted text-center mt-1">Sign in to ShopVerse to track orders and check out.</p>

    <!-- Tabs -->
    <div class="mt-6 grid grid-cols-2 rounded-lg glass-input p-1 text-sm font-semibold">
      <a href="/login?mode=login" class="rounded-md py-2 text-center <?= $mode === 'login' ? 'glass-card shadow text-indigo-600' : 'text-glass-muted' ?>">Sign In</a>
      <a href="/login?mode=register" class="rounded-md py-2 text-center <?= $mode === 'register' ? 'glass-card shadow text-indigo-600' : 'text-glass-muted' ?>">Register</a>
    </div>

    <?php if ($mode === 'login'): ?>
      <form method="post" action="/login" class="mt-6 space-y-4">
        <?= csrf_field() ?><input type="hidden" name="action" value="login">
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input type="email" name="email" required autofocus
                 class="w-full rounded-lg border border-[color:var(--glass-line)] glass-input px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <input type="password" name="password" required
                 class="w-full rounded-lg border border-[color:var(--glass-line)] glass-input px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <button class="w-full btn-primary rounded-xl font-semibold py-2.5">Sign In</button>
      </form>
    <?php else: ?>
      <form method="post" action="/login" class="mt-6 space-y-4">
        <?= csrf_field() ?><input type="hidden" name="action" value="register">
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
        <div>
          <label class="block text-sm font-medium mb-1">Full Name</label>
          <input type="text" name="name" required autofocus
                 class="w-full rounded-lg border border-[color:var(--glass-line)] glass-input px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input type="email" name="email" required
                 class="w-full rounded-lg border border-[color:var(--glass-line)] glass-input px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Password (min 6 chars)</label>
          <input type="password" name="password" required minlength="6"
                 class="w-full rounded-lg border border-[color:var(--glass-line)] glass-input px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <button class="w-full btn-primary rounded-xl font-semibold py-2.5">Create Account</button>
      </form>
    <?php endif; ?>

    <p class="text-xs text-glass-muted text-center mt-6">
      Audit fix: the React original accepted any credentials. This PHP version validates server-side with hashed passwords.
    </p>
  </div>
</div>
