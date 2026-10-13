<?php
/**
 * Production Footer Component
 * Includes category sitemap, newsletter signup with incentive, payment security badges, and copyright.
 */
?>
<footer class="bg-gray-950 text-gray-400 mt-20 border-t border-gray-800/80">
  <div class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-2 md:grid-cols-5 gap-10 text-sm">
    <!-- Brand Bio (2 cols on md) -->
    <div class="col-span-2 space-y-4">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-base shadow-md">
          S
        </div>
        <span class="text-xl font-extrabold tracking-tight text-white">
          Shop<span class="text-indigo-500">Verse</span>
        </span>
      </div>
      <p class="text-xs text-gray-400 max-w-sm leading-relaxed">
        Curating premium electronics, apparel, and lifestyle accessories designed to last. Built with performance, durability, and customer satisfaction at our core.
      </p>
      <div class="flex items-center gap-3 text-xs text-gray-400 pt-2">
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> 99.9% Uptime</span>
        <span>·</span>
        <span>256-Bit Encrypted</span>
        <span>·</span>
        <span>Fast Global Dispatch</span>
      </div>
    </div>

    <!-- Shop Categories -->
    <div class="space-y-3">
      <h4 class="font-bold text-xs uppercase tracking-wider text-white">Categories</h4>
      <ul class="space-y-2 text-xs">
        <li><a class="hover:text-white transition-colors" href="/products">All Products</a></li>
        <li><a class="hover:text-white transition-colors" href="/products?category=Electronics">Electronics</a></li>
        <li><a class="hover:text-white transition-colors" href="/products?category=Accessories">Accessories</a></li>
        <li><a class="hover:text-white transition-colors" href="/products?category=Home">Home &amp; Office</a></li>
        <li><a class="hover:text-white transition-colors" href="/products?category=Sports">Sports &amp; Fitness</a></li>
      </ul>
    </div>

    <!-- Customer Account -->
    <div class="space-y-3">
      <h4 class="font-bold text-xs uppercase tracking-wider text-white">Customer Hub</h4>
      <ul class="space-y-2 text-xs">
        <li><a class="hover:text-white transition-colors" href="/track">Live Shipment Tracking</a></li>
        <li><a class="hover:text-white transition-colors" href="/orders">Order History &amp; Receipts</a></li>
        <li><a class="hover:text-white transition-colors" href="/wishlist">Saved Wishlist</a></li>
        <li><a class="hover:text-white transition-colors" href="/cart">Shopping Bag</a></li>
        <li><a class="hover:text-white transition-colors" href="/account">Account Settings</a></li>
      </ul>
    </div>

    <!-- Newsletter -->
    <div class="col-span-2 md:col-span-1 space-y-3">
      <h4 class="font-bold text-xs uppercase tracking-wider text-white">Stay In The Loop</h4>
      <p class="text-xs text-gray-400">Subscribe for early product drops, private sales, and 10% off your next purchase.</p>
      <form method="post" action="/" class="space-y-2">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="newsletter">
        <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
        <div class="flex gap-2">
          <input type="email" name="email" required placeholder="Enter your email"
                 class="w-full rounded-xl px-3 py-2 bg-gray-900 border border-gray-800 text-xs text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <button class="rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3.5 py-2 text-xs transition-colors shrink-0">
            Join
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bottom Bar with Payment Security -->
  <div class="border-t border-gray-900 bg-black/40 py-6 px-4">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
      <p>© <?= date('Y') ?> ShopVerse Inc. All rights reserved. Designed for speed, security, and elegance.</p>
      <div class="flex items-center gap-4 text-gray-400">
        <span class="inline-flex items-center gap-1 font-mono text-[11px] bg-gray-900 px-2 py-0.5 rounded border border-gray-800">VISA</span>
        <span class="inline-flex items-center gap-1 font-mono text-[11px] bg-gray-900 px-2 py-0.5 rounded border border-gray-800">MASTERCARD</span>
        <span class="inline-flex items-center gap-1 font-mono text-[11px] bg-gray-900 px-2 py-0.5 rounded border border-gray-800">APPLE PAY</span>
        <span class="inline-flex items-center gap-1 font-mono text-[11px] bg-gray-900 px-2 py-0.5 rounded border border-gray-800">PAYPAL</span>
      </div>
    </div>
  </div>
</footer>
