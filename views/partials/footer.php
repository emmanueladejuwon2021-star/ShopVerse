<?php /* Footer — port of src/components/Footer.tsx */ ?>
<footer class="bg-gray-900 text-gray-300 mt-12">
  <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
    <div>
      <h3 class="font-bold text-white mb-3">ShopVerse</h3>
      <p class="text-gray-400">Premium products, fair prices, delivered fast.</p>
    </div>
    <div>
      <h4 class="font-semibold text-white mb-3">Shop</h4>
      <ul class="space-y-2">
        <li><a class="hover:text-white" href="/products">All Products</a></li>
        <li><a class="hover:text-white" href="/products?category=Electronics">Electronics</a></li>
        <li><a class="hover:text-white" href="/products?category=Clothing">Clothing</a></li>
      </ul>
    </div>
    <div>
      <h4 class="font-semibold text-white mb-3">Account</h4>
      <ul class="space-y-2">
        <li><a class="hover:text-white" href="/login">Sign in</a></li>
        <li><a class="hover:text-white" href="/orders">My Orders</a></li>
        <li><a class="hover:text-white" href="/cart">Cart</a></li>
      </ul>
    </div>
    <div>
      <h4 class="font-semibold text-white mb-3">Newsletter</h4>
      <form method="post" action="/" class="flex gap-2">
        <input type="hidden" name="action" value="newsletter">
        <input type="email" required placeholder="Email" class="flex-1 rounded px-3 py-1.5 bg-gray-800 border border-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button class="rounded bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-sm">Join</button>
      </form>
    </div>
  </div>
  <div class="border-t border-gray-800 py-4 text-center text-xs text-gray-500">
    © <?= date('Y') ?> ShopVerse. All rights reserved.
  </div>
</footer>
