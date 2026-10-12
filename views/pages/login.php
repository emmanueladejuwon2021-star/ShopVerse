<?php /* Login — port of src/pages/Login.tsx (tabbed sign-in / register) */
      $mode = ($_GET['mode'] ?? 'login') === 'register' ? 'register' : 'login';
      $redirect = $_GET['redirect'] ?? '/';
?>
<div class="max-w-md mx-auto px-4 py-16">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
    <h1 class="text-2xl font-bold text-center"><?= $mode === 'login' ? 'Welcome Back' : 'Create Account' ?></h1>
    <p class="text-sm text-gray-500 text-center mt-1">Sign in to ShopVerse to track orders and check out.</p>

    <!-- Tabs -->
    <div class="mt-6 grid grid-cols-2 rounded-lg bg-gray-100 dark:bg-gray-900 p-1 text-sm font-semibold">
      <a href="/login?mode=login" class="rounded-md py-2 text-center <?= $mode === 'login' ? 'bg-white dark:bg-gray-800 shadow text-indigo-600' : 'text-gray-500' ?>">Sign In</a>
      <a href="/login?mode=register" class="rounded-md py-2 text-center <?= $mode === 'register' ? 'bg-white dark:bg-gray-800 shadow text-indigo-600' : 'text-gray-500' ?>">Register</a>
    </div>

    <?php if ($mode === 'login'): ?>
      <form method="post" action="/login" class="mt-6 space-y-4">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input type="email" name="email" required autofocus
                 class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <input type="password" name="password" required
                 class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <button class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5">Sign In</button>
      </form>
    <?php else: ?>
      <form method="post" action="/login" class="mt-6 space-y-4">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
        <div>
          <label class="block text-sm font-medium mb-1">Full Name</label>
          <input type="text" name="name" required autofocus
                 class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input type="email" name="email" required
                 class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Password (min 6 chars)</label>
          <input type="password" name="password" required minlength="6"
                 class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <button class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5">Create Account</button>
      </form>
    <?php endif; ?>

    <p class="text-xs text-gray-400 text-center mt-6">
      Audit fix: the React original accepted any credentials. This PHP version validates server-side with hashed passwords.
    </p>
  </div>
</div>
