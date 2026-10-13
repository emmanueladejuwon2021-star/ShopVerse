<?php
/**
 * Paystack Return / Payment Status page (GET /payment/callback?reference=...)
 *
 * Rendered by the front controller ONLY after the reference has been
 * re-verified server-side against the Paystack API — a raw redirect from
 * checkout.paystack.co is never trusted. States:
 *   pending    → payment seen, final confirmation in flight (webhook/verify)
 *   failed     → Paystack reports the charge failed/abandoned
 *   unknown    → reference not found in our system
 * Successful payments are redirected to /order-success before this view runs.
 */
$state = $state ?? 'pending';
$reference = $reference ?? '';
$order = $order ?? null;

$styles = [
    'pending' => [
        'icon'  => '⏳',
        'ring'  => 'bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400',
        'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
        'title' => 'Confirming your payment…',
        'body'  => 'We have received the redirect from Paystack and are verifying the transaction with our payment provider. This usually completes within a few seconds — you can safely keep this page open.',
        'spin'  => true,
    ],
    'failed' => [
        'icon'  => '✕',
        'ring'  => 'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400',
        'badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800',
        'title' => 'Payment was not completed',
        'body'  => 'Paystack reported this transaction as failed or abandoned. <strong>No charge was made</strong> — if your account was debited anyway, the amount is released automatically by Paystack within a few minutes. Your cart is untouched, so you can retry below.',
        'spin'  => false,
    ],
    'unknown' => [
        'icon'  => '?',
        'ring'  => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300',
        'badge' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700',
        'title' => 'We could not find this payment',
        'body'  => 'This transaction reference does not match any order on this account. If you just paid, the confirmation may still be propagating — check your <a href="/orders" class="text-indigo-600 font-semibold hover:underline">order history</a> in a minute, or contact support with the reference below.',
        'spin'  => false,
    ],
][$state] ?? [
    'icon'  => '⏳',
    'ring'  => 'bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400',
    'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
    'title' => 'Payment status',
    'body'  => 'Please check your order history for the latest status of this payment.',
    'spin'  => true,
];
?>
<div class="max-w-xl mx-auto px-4 py-16 lg:py-24">
  <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm p-8 sm:p-10 text-center space-y-6">

    <div class="mx-auto w-16 h-16 rounded-full <?= $styles['ring'] ?> flex items-center justify-center text-2xl font-bold relative">
      <?php if ($styles['spin']): ?>
        <span class="absolute inset-0 rounded-full border-4 border-transparent border-t-current animate-spin"></span>
      <?php endif; ?>
      <span><?= $styles['icon'] ?></span>
    </div>

    <span class="inline-block text-[11px] uppercase tracking-widest font-bold px-3 py-1 rounded-full border <?= $styles['badge'] ?>">
      <?= e(ucfirst($state)) ?>
    </span>

    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight"><?= e($styles['title']) ?></h1>

    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed max-w-md mx-auto">
      <?= $styles['body'] /* safe: static copy authored by us, no user input */ ?>
    </p>

    <?php if ($reference !== ''): ?>
      <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gray-100 dark:bg-gray-900 text-xs font-mono text-gray-700 dark:text-gray-300">
        <span class="text-gray-400">Paystack Reference:</span>
        <span class="font-bold select-all"><?= e($reference) ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($order)): ?>
      <dl class="text-xs text-left divide-y divide-gray-100 dark:divide-gray-700/60 border border-gray-100 dark:border-gray-700/60 rounded-2xl overflow-hidden">
        <div class="flex justify-between px-4 py-2.5">
          <dt class="text-gray-400">Order ID</dt>
          <dd class="font-semibold text-gray-900 dark:text-white font-mono"><?= e((string) ($order['id'] ?? '')) ?></dd>
        </div>
        <div class="flex justify-between px-4 py-2.5">
          <dt class="text-gray-400">Amount</dt>
          <dd class="font-semibold text-gray-900 dark:text-white"><?= money((float) ($order['total'] ?? 0)) ?></dd>
        </div>
        <?php if (!empty($order['shippingAddress']['paystackCurrency'])): ?>
          <!-- The currency actually collected at the gateway (from Paystack's verify response) -->
          <div class="flex justify-between px-4 py-2.5">
            <dt class="text-gray-400">Charged in</dt>
            <dd class="font-semibold text-gray-900 dark:text-white font-mono"><?= e((string) $order['shippingAddress']['paystackCurrency']) ?></dd>
          </div>
        <?php endif; ?>
      </dl>
    <?php endif; ?>

    <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
      <?php if ($state === 'failed'): ?>
        <a href="/checkout"
           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3 text-sm transition-colors shadow-lg shadow-indigo-600/25">
          ↻ Try Payment Again
        </a>
      <?php endif; ?>
      <?php if ($state === 'unknown'): ?>
        <!-- No matching order on this server — recover via the cart instead of a dead end -->
        <a href="/cart"
           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3 text-sm transition-colors shadow-lg shadow-indigo-600/25">
          Back to My Cart
        </a>
      <?php endif; ?>
      <a href="/orders"
         class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold px-6 py-3 text-sm transition-colors">
          View My Orders
        </a>
      <a href="/products"
         class="inline-flex items-center justify-center gap-2 rounded-2xl text-gray-400 hover:text-indigo-600 font-semibold px-4 py-3 text-sm transition-colors">
          Continue Shopping
        </a>
    </div>

    <p class="text-[11px] text-gray-400 pt-2">
      🔒 Payments secured &amp; verified server-side by Paystack. Amounts are never taken from browser data.
    </p>
  </div>
</div>

<?php if ($state === 'pending'): ?>
<script>
  // Auto-refresh while the async verification converges (webhook vs. redirect race).
  setTimeout(function () { window.location.reload(); }, 6000);
</script>
<?php endif; ?>
