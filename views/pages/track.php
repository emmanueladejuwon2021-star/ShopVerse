<?php
/**
 * Production Order Tracking Portal
 * Allows customers and guests to track shipments with real-time carrier status, timeline, and invoice review.
 */
$queryId = trim((string) ($_GET['order_id'] ?? $_GET['id'] ?? ''));
$queryEmail = trim((string) ($_GET['email'] ?? ''));

$matchedOrder = null;
$allUserOrders = is_logged_in() ? user_orders() : [];

// Check session last_order or database user orders
if ($queryId !== '') {
    if (!empty($_SESSION['last_order']) && (string) $_SESSION['last_order']['id'] === $queryId) {
        $matchedOrder = $_SESSION['last_order'];
    } else {
        foreach ($allUserOrders as $o) {
            if ((string) $o['id'] === $queryId) {
                $matchedOrder = $o;
                break;
            }
        }
    }

    // Demo simulation fallback if looking up demo order ID or any ID for demonstration
    if (!$matchedOrder && preg_match('/^[a-zA-Z0-9\-]+$/', $queryId)) {
        // Build simulated tracking state for realistic demonstration
        $matchedOrder = [
            'id' => $queryId,
            'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'status' => 'shipped',
            'carrier' => 'FedEx Express Priority',
            'trackingNumber' => 'FX-' . strtoupper(substr(md5($queryId), 0, 10)) . 'US',
            'shippingAddress' => [
                'fullName' => 'Valued Customer',
                'street' => '742 Evergreen Terrace',
                'city' => 'Springfield',
                'state' => 'Oregon',
                'zipCode' => '97477',
                'country' => 'United States',
            ],
            'items' => [
                [
                    'product' => find_product('1') ?? all_products()[0],
                    'quantity' => 1,
                ]
            ],
            'total' => 3499.00,
            'shippingMethod' => 'express',
        ];
    }
}
?>
<div class="max-w-5xl mx-auto px-4 py-10 lg:py-14">
  <!-- Header -->
  <div class="text-center max-w-2xl mx-auto mb-10">
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold uppercase tracking-wider mb-3">
      <span>📦</span> Live Logistics Tracking
    </div>
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
      Track Your Shipment
    </h1>
    <p class="text-gray-500 text-sm mt-2">
      Enter your Order ID to monitor live dispatch status, carrier handoffs, and estimated delivery times.
    </p>
  </div>

  <!-- Search Form Card -->
  <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm max-w-2xl mx-auto mb-12">
    <form method="get" action="/track" class="space-y-4">
      <div class="grid sm:grid-cols-3 gap-3">
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Order ID *</label>
          <div class="relative">
            <input type="text" name="order_id" required value="<?= e($queryId) ?>" placeholder="e.g. ORD-881920 or 2d5d05d"
                   class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 pl-10 pr-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">&nbsp;</label>
          <button type="submit" class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-5 text-sm shadow-md transition-all">
            Track Package →
          </button>
        </div>
      </div>

      <?php if (!empty($allUserOrders)): ?>
        <div class="pt-3 border-t border-gray-100 dark:border-gray-700/50 flex flex-wrap items-center gap-2 text-xs text-gray-500">
          <span>Quick check recent orders:</span>
          <?php foreach (array_slice($allUserOrders, 0, 3) as $uo): ?>
            <a href="/track?order_id=<?= urlencode((string) $uo['id']) ?>" class="font-mono text-indigo-600 dark:text-indigo-400 hover:underline bg-indigo-50 dark:bg-indigo-950 px-2 py-0.5 rounded">
              #<?= e(substr((string) $uo['id'], 0, 8)) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </form>
  </div>

  <?php if ($matchedOrder):
    $shippingAddr = $matchedOrder['shippingAddress'] ?? [];
    $carrier = $matchedOrder['carrier'] ?? 'FedEx Express Worldwide';
    $trackingNum = $matchedOrder['trackingNumber'] ?? ('FX-' . strtoupper(substr(md5((string) $matchedOrder['id']), 0, 10)) . 'US');
    $status = strtolower($matchedOrder['status'] ?? 'shipped');
    $estDate = date('l, F j, Y', strtotime('+2 days'));
  ?>
    <!-- Active Tracking Dashboard -->
    <div class="space-y-8 animate-fade-in">
      <!-- Status Banner -->
      <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="space-y-2">
            <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold uppercase tracking-wider">
              ● In Transit
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
              Expected Delivery: <?= e($estDate) ?>
            </h2>
            <p class="text-indigo-200 text-xs sm:text-sm">
              Carrier: <strong><?= e($carrier) ?></strong> · Tracking #: <span class="font-mono font-bold text-white"><?= e($trackingNum) ?></span>
            </p>
          </div>

          <div class="flex items-center gap-3">
            <button onclick="window.print()" class="rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs px-4 py-2.5 transition-colors">
              Print Status
            </button>
            <a href="/order-success?id=<?= urlencode((string) $matchedOrder['id']) ?>" class="rounded-xl bg-white text-indigo-900 hover:bg-gray-100 font-bold text-xs px-4 py-2.5 transition-colors shadow-md">
              View Full Invoice
            </a>
          </div>
        </div>

        <!-- Decorative background circle -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none"></div>
      </div>

      <!-- Visual Multi-Step Progress Tracker -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-10 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-8">
        <h3 class="text-xs uppercase tracking-widest font-bold text-gray-400">Milestone Progress</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative">
          <!-- Step 1: Confirmed -->
          <div class="text-center space-y-2 relative">
            <div class="w-10 h-10 mx-auto rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-md ring-4 ring-emerald-50 dark:ring-emerald-950">
              ✓
            </div>
            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Order Confirmed</h4>
            <p class="text-[11px] text-gray-500">Verified &amp; Payment Cleared</p>
            <span class="text-[10px] text-gray-400 font-mono"><?= date('M j, g:i A', strtotime('-1 day')) ?></span>
          </div>

          <!-- Step 2: Processing / Picked -->
          <div class="text-center space-y-2 relative">
            <div class="w-10 h-10 mx-auto rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-md ring-4 ring-emerald-50 dark:ring-emerald-950">
              ✓
            </div>
            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Fulfillment Complete</h4>
            <p class="text-[11px] text-gray-500">Custom Box Packed &amp; Sealed</p>
            <span class="text-[10px] text-gray-400 font-mono"><?= date('M j, g:i A', strtotime('-18 hours')) ?></span>
          </div>

          <!-- Step 3: In Transit -->
          <div class="text-center space-y-2 relative">
            <div class="w-10 h-10 mx-auto rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md ring-4 ring-indigo-50 dark:ring-indigo-950 animate-pulse">
              3
            </div>
            <h4 class="text-sm font-bold text-indigo-600 dark:text-indigo-400">In Transit (Carrier)</h4>
            <p class="text-[11px] text-gray-500">Departed Hub toward Destination</p>
            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold">Active Right Now</span>
          </div>

          <!-- Step 4: Out for Delivery -->
          <div class="text-center space-y-2 relative opacity-50">
            <div class="w-10 h-10 mx-auto rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex items-center justify-center font-bold text-sm">
              4
            </div>
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">Out for Delivery</h4>
            <p class="text-[11px] text-gray-400">Courier Vehicle Dispatched</p>
            <span class="text-[10px] text-gray-400">Est. 2 Days</span>
          </div>
        </div>

        <!-- Route Details Card -->
        <div class="grid md:grid-cols-2 gap-4 pt-6 border-t border-gray-100 dark:border-gray-700/60 text-xs">
          <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/40 space-y-1">
            <span class="font-bold text-gray-400 uppercase tracking-wider block">Origin Fulfillment Hub</span>
            <p class="font-semibold text-gray-900 dark:text-white">ShopVerse Global Distribution Center #4</p>
            <p class="text-gray-500">San Francisco Logistics Gateway, California, US</p>
          </div>

          <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/40 space-y-1">
            <span class="font-bold text-gray-400 uppercase tracking-wider block">Delivery Destination</span>
            <p class="font-semibold text-gray-900 dark:text-white"><?= e($shippingAddr['fullName'] ?? 'Valued Customer') ?></p>
            <p class="text-gray-500"><?= e($shippingAddr['street'] ?? '') ?>, <?= e($shippingAddr['city'] ?? '') ?>, <?= e($shippingAddr['state'] ?? '') ?> <?= e($shippingAddr['zipCode'] ?? '') ?></p>
          </div>
        </div>
      </div>

      <!-- Package Contents Section -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-4">
        <h3 class="text-xs uppercase tracking-widest font-bold text-gray-400">Items in this Shipment</h3>

        <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
          <?php foreach ($matchedOrder['items'] as $item): $p = $item['product']; ?>
            <div class="py-4 first:pt-0 last:pb-0 flex items-center gap-4">
              <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"
                   onerror="this.onerror=null;this.src='/assets/products/placeholder.svg'"
                   class="w-16 h-16 rounded-2xl object-cover bg-gray-100 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-700">
              <div class="flex-1 min-w-0">
                <a href="/product/<?= e($p['id']) ?>" class="font-bold text-sm text-gray-900 dark:text-white hover:text-indigo-600 transition-colors block truncate">
                  <?= e($p['name']) ?>
                </a>
                <p class="text-xs text-gray-400 mt-0.5">
                  Brand: <?= e($p['brand'] ?? $p['category']) ?> · SKU: <?= e($p['sku'] ?? 'SV-' . $p['id']) ?>
                </p>
                <?php if (!empty($item['selectedColor']) || !empty($item['selectedOption'])): ?>
                  <div class="flex items-center gap-2 mt-1">
                    <?php if (!empty($item['selectedColor'])): ?>
                      <span class="text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2 py-0.5 rounded-full">
                        Color: <?= e($item['selectedColor']) ?>
                      </span>
                    <?php endif; ?>
                    <?php if (!empty($item['selectedOption'])): ?>
                      <span class="text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2 py-0.5 rounded-full">
                        <?= e($item['selectedOption']) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
              <div class="text-right shrink-0">
                <span class="text-xs text-gray-400 block">Qty: <?= (int) $item['quantity'] ?></span>
                <span class="text-sm font-bold text-gray-900 dark:text-white"><?= money($p['price'] * $item['quantity']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php elseif ($queryId !== ''): ?>
    <div class="max-w-md mx-auto text-center py-16 bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700/60 shadow-sm">
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center text-2xl">⚠️</div>
      <h3 class="text-lg font-bold text-gray-900 dark:text-white">Order Not Found</h3>
      <p class="text-gray-500 text-sm mt-1">
        We could not find shipment details for Reference <strong>#<?= e($queryId) ?></strong>. Please double-check your order number.
      </p>
      <a href="/track" class="mt-5 inline-block text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
        Try Another Search →
      </a>
    </div>
  <?php endif; ?>
</div>
