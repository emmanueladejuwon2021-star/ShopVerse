import { Link } from 'react-router-dom';
import { ArrowRight, Truck, Shield, RotateCcw, Headphones } from 'lucide-react';
import ProductCard from '../components/ProductCard';
import { products } from '../data/products';

export default function Home() {
  const featuredProducts = products.filter(p => p.inStock).slice(0, 4);

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div className="absolute inset-0" style={{ backgroundImage: 'radial-gradient(circle at 25% 50%, white 1px, transparent 1px)', backgroundSize: '40px 40px' }} />
        </div>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32 relative">
          <div className="max-w-2xl">
            <h1 className="text-4xl md:text-6xl font-bold text-white leading-tight">
              Discover Your Next
              <span className="block text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-pink-200">
                Favorite Product
              </span>
            </h1>
            <p className="mt-6 text-lg text-indigo-100 max-w-lg">
              Shop thousands of premium products with free shipping, easy returns, and the best prices guaranteed.
            </p>
            <div className="mt-8 flex flex-col sm:flex-row gap-4">
              <Link
                to="/products"
                className="inline-flex items-center justify-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-full hover:bg-gray-100 transition-colors"
              >
                Shop Now
                <ArrowRight className="ml-2 h-5 w-5" />
              </Link>
              <Link
                to="/products?category=Electronics"
                className="inline-flex items-center justify-center px-8 py-3 border-2 border-white/30 text-white font-semibold rounded-full hover:bg-white/10 transition-colors"
              >
                Browse Electronics
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="py-12 bg-white border-b border-gray-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div className="flex items-center space-x-3">
              <div className="p-3 bg-indigo-50 rounded-xl">
                <Truck className="h-6 w-6 text-indigo-600" />
              </div>
              <div>
                <p className="font-semibold text-gray-900 text-sm">Free Shipping</p>
                <p className="text-xs text-gray-500">On orders over $50</p>
              </div>
            </div>
            <div className="flex items-center space-x-3">
              <div className="p-3 bg-green-50 rounded-xl">
                <Shield className="h-6 w-6 text-green-600" />
              </div>
              <div>
                <p className="font-semibold text-gray-900 text-sm">Secure Payment</p>
                <p className="text-xs text-gray-500">100% protected</p>
              </div>
            </div>
            <div className="flex items-center space-x-3">
              <div className="p-3 bg-amber-50 rounded-xl">
                <RotateCcw className="h-6 w-6 text-amber-600" />
              </div>
              <div>
                <p className="font-semibold text-gray-900 text-sm">Easy Returns</p>
                <p className="text-xs text-gray-500">30-day policy</p>
              </div>
            </div>
            <div className="flex items-center space-x-3">
              <div className="p-3 bg-purple-50 rounded-xl">
                <Headphones className="h-6 w-6 text-purple-600" />
              </div>
              <div>
                <p className="font-semibold text-gray-900 text-sm">24/7 Support</p>
                <p className="text-xs text-gray-500">Always here for you</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Featured Products */}
      <section className="py-16 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between mb-8">
            <div>
              <h2 className="text-2xl md:text-3xl font-bold text-gray-900">Featured Products</h2>
              <p className="text-gray-500 mt-1">Handpicked items just for you</p>
            </div>
            <Link
              to="/products"
              className="hidden sm:inline-flex items-center text-indigo-600 font-medium hover:text-indigo-700 transition-colors"
            >
              View All
              <ArrowRight className="ml-1 h-4 w-4" />
            </Link>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {featuredProducts.map((product, index) => (
              <ProductCard key={product.id} product={product} index={index} />
            ))}
          </div>
          <div className="mt-8 text-center sm:hidden">
            <Link
              to="/products"
              className="inline-flex items-center text-indigo-600 font-medium"
            >
              View All Products
              <ArrowRight className="ml-1 h-4 w-4" />
            </Link>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl p-8 md:p-12 text-center">
            <h2 className="text-2xl md:text-4xl font-bold text-white">Get 20% Off Your First Order</h2>
            <p className="mt-4 text-indigo-100 max-w-lg mx-auto">
              Sign up for our newsletter and receive an exclusive discount code for your first purchase.
            </p>
            <div className="mt-8 flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
              <input
                type="email"
                placeholder="Enter your email"
                className="flex-1 px-5 py-3 rounded-full focus:outline-none focus:ring-2 focus:ring-white/50"
              />
              <button className="px-6 py-3 bg-white text-indigo-600 font-semibold rounded-full hover:bg-gray-100 transition-colors">
                Subscribe
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
