<?php /* AccountSettings — port of src/pages/AccountSettings.tsx (Profile / Addresses / Payments / Notifications tabs).
        Audit fix: the React version discarded address/card edits on tab switch; here they persist to storage. */
      $user = current_user();
      $tab = in_array($_GET['tab'] ?? '', ['profile', 'addresses', 'payments', 'notifications'], true) ? $_GET['tab'] : 'profile';

      $file = STORAGE_DIR . '/account_' . $user['id'] . '.json';
      $acct = storage_read($file);
      $acct += ['phone' => '', 'bio' => '', 'addresses' => [], 'cards' => [], 'notifications' => array_fill_keys(
          ['orderUpdates', 'promotions', 'newsletter', 'securityAlerts', 'productRecommendations', 'smsNotifications'], true)];

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($_POST['action'] ?? '') {
          case 'save_profile':
            $acct['phone'] = trim((string) ($_POST['phone'] ?? ''));
            $acct['bio'] = trim((string) ($_POST['bio'] ?? ''));
            storage_write($file, $acct);
            $_SESSION['flash'] = 'Profile saved.';
            redirect('/account?tab=' . e($_POST['tab'] ?? 'profile'));
          case 'add_address':
            $acct['addresses'][] = [
                'id' => bin2hex(random_bytes(4)),
                'label' => trim((string) ($_POST['label'] ?? 'Home')),
                'fullName' => trim((string) ($_POST['fullName'] ?? $user['name'])),
                'street' => trim((string) ($_POST['street'] ?? '')),
                'city' => trim((string) ($_POST['city'] ?? '')),
                'state' => trim((string) ($_POST['state'] ?? '')),
                'zipCode' => trim((string) ($_POST['zipCode'] ?? '')),
                'country' => trim((string) ($_POST['country'] ?? '')),
            ];
            storage_write($file, $acct);
            $_SESSION['flash'] = 'Address saved.';
            redirect('/account?tab=addresses');
          case 'delete_address':
            $acct['addresses'] = array_values(array_filter($acct['addresses'], fn($a) => $a['id'] !== ($_POST['address_id'] ?? '')));
            storage_write($file, $acct);
            $_SESSION['flash'] = 'Address deleted.';
            redirect('/account?tab=addresses');
          case 'add_card':
            $num = preg_replace('/\D/', '', (string) ($_POST['cardNumber'] ?? ''));
            $acct['cards'][] = [
                'id' => bin2hex(random_bytes(4)),
                'last4' => substr($num, -4),
                'expiry' => trim((string) ($_POST['expiry'] ?? '')),
                'name' => trim((string) ($_POST['cardName'] ?? '')),
            ];
            storage_write($file, $acct);
            $_SESSION['flash'] = 'Card added.';
            redirect('/account?tab=payments');
          case 'delete_card':
            $acct['cards'] = array_values(array_filter($acct['cards'], fn($c) => $c['id'] !== ($_POST['card_id'] ?? '')));
            storage_write($file, $acct);
            $_SESSION['flash'] = 'Card removed.';
            redirect('/account?tab=payments');
          case 'save_notifications':
            foreach ($acct['notifications'] as $k => $v) {
                $acct['notifications'][$k] = isset($_POST[$k]);
            }
            storage_write($file, $acct);
            $_SESSION['flash'] = 'Notification preferences saved.';
            redirect('/account?tab=notifications');
        }
      }

      $tabs = ['profile' => 'Profile', 'addresses' => 'Addresses', 'payments' => 'Payment Methods', 'notifications' => 'Notifications'];
?>
<div class="max-w-4xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-2">Account Settings</h1>
  <p class="text-sm text-gray-500 mb-6"><?= e($user['email']) ?> · Member since <?= date('M Y', strtotime($user['joinedDate'] ?? 'now')) ?></p>

  <div class="flex flex-wrap gap-2 mb-8 border-b border-gray-200 dark:border-gray-800 pb-2">
    <?php foreach ($tabs as $key => $label): ?>
      <a href="/account?tab=<?= $key ?>"
         class="px-4 py-2 rounded-lg text-sm font-semibold <?= $tab === $key ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?>">
        <?= e($label) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ($tab === 'profile'): ?>
    <form method="post" action="/account?tab=profile" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-4 max-w-lg">
      <input type="hidden" name="action" value="save_profile"><input type="hidden" name="tab" value="profile">
      <div><label class="block text-sm font-medium mb-1">Name</label>
        <input value="<?= e($user['name']) ?>" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm"></div>
      <div><label class="block text-sm font-medium mb-1">Email</label>
        <input value="<?= e($user['email']) ?>" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm"></div>
      <div><label class="block text-sm font-medium mb-1">Phone</label>
        <input name="phone" value="<?= e($acct['phone']) ?>" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm"></div>
      <div><label class="block text-sm font-medium mb-1">Bio</label>
        <textarea name="bio" rows="3" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm"><?= e($acct['bio']) ?></textarea></div>
      <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 text-sm">Save Changes</button>
    </form>

  <?php elseif ($tab === 'addresses'): ?>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-3">
        <?php if (!$acct['addresses']): ?><p class="text-gray-500 text-sm">No saved addresses.</p><?php endif; ?>
        <?php foreach ($acct['addresses'] as $a): ?>
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-sm">
            <div class="flex justify-between items-start">
              <p class="font-semibold"><?= e($a['label']) ?></p>
              <form method="post"><input type="hidden" name="action" value="delete_address"><input type="hidden" name="address_id" value="<?= e($a['id']) ?>">
                <button class="text-xs text-red-500 hover:underline">Delete</button></form>
            </div>
            <p class="mt-1 text-gray-600 dark:text-gray-300"><?= e($a['fullName']) ?><br><?= e($a['street']) ?><br><?= e($a['city']) ?>, <?= e($a['state']) ?> <?= e($a['zipCode']) ?><br><?= e($a['country']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
      <form method="post" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-3 h-fit">
        <input type="hidden" name="action" value="add_address">
        <h2 class="font-bold">Add Address</h2>
        <input name="label" placeholder="Label (Home/Work)" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
        <input name="street" placeholder="Street" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
        <div class="grid grid-cols-2 gap-3">
          <input name="city" placeholder="City" required class="rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
          <input name="state" placeholder="State" required class="rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <input name="zipCode" placeholder="ZIP" required class="rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
          <input name="country" placeholder="Country" required class="rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
        </div>
        <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 text-sm w-full">Save Address</button>
      </form>
    </div>

  <?php elseif ($tab === 'payments'): ?>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-3">
        <?php if (!$acct['cards']): ?><p class="text-gray-500 text-sm">No saved cards.</p><?php endif; ?>
        <?php foreach ($acct['cards'] as $c): ?>
          <div class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white rounded-xl p-4 flex items-center justify-between">
            <div><p class="text-xs opacity-80">•••• •••• •••• <?= e($c['last4']) ?></p>
              <p class="text-sm mt-1"><?= e($c['name']) ?> · <?= e($c['expiry']) ?></p></div>
            <form method="post"><input type="hidden" name="action" value="delete_card"><input type="hidden" name="card_id" value="<?= e($c['id']) ?>">
              <button class="text-xs bg-white/20 hover:bg-white/30 rounded px-2 py-1">Remove</button></form>
          </div>
        <?php endforeach; ?>
      </div>
      <form method="post" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-3 h-fit">
        <input type="hidden" name="action" value="add_card">
        <h2 class="font-bold">Add Card</h2>
        <input name="cardNumber" placeholder="Card number" inputmode="numeric" maxlength="19" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
        <input name="cardName" placeholder="Name on card" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
        <input name="expiry" placeholder="MM/YY" pattern="\d{2}/\d{2}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm">
        <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 text-sm w-full">Add Card</button>
        <p class="text-xs text-gray-400">Only the last 4 digits are stored.</p>
      </form>
    </div>

  <?php else: ?>
    <form method="post" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-3 max-w-lg">
      <input type="hidden" name="action" value="save_notifications">
      <?php $labels = [
          'orderUpdates' => 'Order updates', 'promotions' => 'Promotions & offers', 'newsletter' => 'Newsletter',
          'securityAlerts' => 'Security alerts', 'productRecommendations' => 'Product recommendations', 'smsNotifications' => 'SMS notifications'];
      foreach ($labels as $key => $label): ?>
        <label class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 text-sm cursor-pointer">
          <span><?= e($label) ?></span>
          <input type="checkbox" name="<?= e($key) ?>" <?= !empty($acct['notifications'][$key]) ? 'checked' : '' ?> class="w-4 h-4 accent-indigo-600">
        </label>
      <?php endforeach; ?>
      <button class="mt-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 text-sm">Save Preferences</button>
    </form>
  <?php endif; ?>
</div>
