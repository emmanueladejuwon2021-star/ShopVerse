import { Link } from 'react-router-dom';
import { ArrowRight, Truck, Shield, RotateCcw, Headphones, Sparkles, TrendingUp, Zap } from 'lucide-react';
import ProductCard from '../components/ProductCard';
import { products } from '../data/products';
import { motion } from 'framer-motion';

export default function Home() {
  const featuredProducts = products.filter(p => p.inStock).slice(0, 8);
  const trendingProducts = products.filter(p => p.rating >= 4.7).slice(0, 4);

  return (
    <div className="min-h-screen bg-mesh">
      {/* Hero Section */}
      <section className="relative overflow-hidden pt-24 pb-16 md:pt-32 md:pb-24">
        {/* Background Elements */}
        <div className="absolute inset-0 overflow-hidden">
          <div className="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-indigo-400/20 to-purple-400/20 rounded-full blur-3xl animate-pulse-soft" />
          <div className="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-tr from-pink-400/15 to-rose-400/15 rounded-full blur-3xl animate-pulse-soft" style={{ animationDelay: '1.5s' }} />
          <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-blue-400/5 to-indigo-400/5 rounded-full blur-3xl" />
        </div>

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {/* Left Content */}
            <motion.div
              initial={{ opacity: 0, x: -30 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.6 }}
            >
              <div className="inline-flex items-center space-x-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full mb-6">
                <Sparkles className="h-4 w-4 text-indigo-500" />
                <span className="text-sm font-medium text-indigo-700">New Season Collection 2026</span>
              </div>
              
              <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-[1.1] tracking-tight">
                Discover Products
                <span className="block mt-2">
                  You'll <span className="text-gradient">Absolutely</span>
                </span>
                <span className="block">Love.</span>
              </h1>
              
              <p className="mt-6 text-lg text-gray-500 max-w-lg leading-relaxed">
                Curated collection of premium products designed for modern living. 
                Free shipping on orders over $50, hassle-free returns.
              </p>

              <div className="mt-8 flex flex-col sm:flex-row gap-4">
                <Link
                  to="/products"
                  className="group inline-flex items-center justify-center px-8 py-4 bg-gray-900 text-white font-semibold rounded-2xl hover:bg-gray-800 transition-all hover:shadow-xl hover:shadow-gray-900/20 hover:-translate-y-0.5"
                >
                  Explore Collection
                  <ArrowRight className="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" />
                </Link>
                <Link
                  to="/products?category=Electronics"
                  className="inline-flex items-center justify-center px-8 py-4 bg-white text-gray-700 font-semibold rounded-2xl border border-gray-200 hover:border-gray-300 hover:shadow-lg hover:-translate-y-0.5 transition-all"
                >
                  <Zap className="mr-2 h-5 w-5 text-amber-500" />
                  Trending Now
                </Link>
              </div>

              {/* Stats */}
              <div className="mt-12 flex items-center space-x-8">
                <div>
                  <p className="text-2xl font-bold text-gray-900">50K+</p>
                  <p className="text-sm text-gray-500">Happy Customers</p>
                </div>
                <div className="h-10 w-px bg-gray-200" />
                <div>
                  <p className="text-2xl font-bold text-gray-900">4.9★</p>
                  <p className="text-sm text-gray-500">Average Rating</p>
                </div>
                <div className="h-10 w-px bg-gray-200" />
                <div>
                  <p className="text-2xl font-bold text-gray-900">24/7</p>
                  <p className="text-sm text-gray-500">Support</p>
                </div>
              </div>
            </motion.div>

            {/* Right - Hero Image Grid */}
            <motion.div
              initial={{ opacity: 0, x: 30 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.6, delay: 0.2 }}
              className="relative hidden lg:block"
            >
              <div className="relative grid grid-cols-2 gap-4">
                <div className="space-y-4">
                  <div className="relative rounded-3xl overflow-hidden shadow-2xl shadow-indigo-500/10 card-hover">
                    <img
                      src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=500&fit=crop"
                      alt="Headphones"
                      className="w-full h-64 object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
                  </div>
                  <div className="relative rounded-3xl overflow-hidden shadow-xl card-hover">
                    <img
                      src="https://images.unsplash.com/photo-1523275335684-37898b6b3327?w=400&h=300&fit=crop"
                      alt="Watch"
                      className="w-full h-40 object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
                  </div>
                </div>
                <div className="space-y-4 pt-8">
                  <div className="relative rounded-3xl overflow-hidden shadow-xl card-hover">
                    <img
                      src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=300&fit=crop"
                      alt="Shoes"
                      className="w-full h-40 object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
                  </div>
                  <div className="relative rounded-3xl overflow-hidden shadow-2xl shadow-purple-500/10 card-hover">
                    <img
                      src="https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&h=500&fit=crop"
                      alt="Laptop"
                      className="w-full h-64 object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
                  </div>
                </div>
              </div>
              {/* Floating Badge */}
              <div className="absolute -bottom-4 left-1/2 -translate-x-1/2 glass rounded-2xl px-6 py-3 shadow-xl animate-float">
                <div className="flex items-center space-x-3">
                  <div className="h-10 w-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center">
                    <TrendingUp className="h-5 w-5 text-white" />
                  </div>
                  <div>
                    <p className="text-sm font-bold text-gray-900">Trending</p>
                    <p className="text-xs text-gray-500">+2.4K sold today</p>
                  </div>
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Trust Bar */}
      <section className="py-8 border-y border-gray-200/60 bg-white/50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
            {[
              { icon: Truck, title: 'Free Shipping', desc: 'On orders $50+', color: 'from-blue-500 to-cyan-500' },
              { icon: Shield, title: 'Secure Payment', desc: '256-bit encryption', color: 'from-emerald-500 to-green-500' },
              { icon: RotateCcw, title: 'Easy Returns', desc: '30-day guarantee', color: 'from-amber-500 to-orange-500' },
              { icon: Headphones, title: '24/7 Support', desc: 'Always available', color: 'from-purple-500 to-pink-500' },
            ].map((item, i) => (
              <motion.div
                key={i}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1 }}
                className="flex items-center space-x-3"
              >
                <div className={`p-2.5 bg-gradient-to-br ${item.color} rounded-xl shadow-lg`}>
                  <item.icon className="h-5 w-5 text-white" />
                </div>
                <div>
                  <p className="font-semibold text-gray-900 text-sm">{item.title}</p>
                  <p className="text-xs text-gray-500">{item.desc}</p>
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Categories */}
      <section className="py-16 md:py-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900">Shop by Category</h2>
            <p className="text-gray-500 mt-3 max-w-lg mx-auto">Find exactly what you're looking for in our curated collections</p>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            {[
              { name: 'Electronics', image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop', count: '2.4K items', gradient: 'from-indigo-500/80 to-blue-600/80' },
              { name: 'Clothing', image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop', count: '1.8K items', gradient: 'from-rose-500/80 to-pink-600/80' },
              { name: 'Accessories', image: 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=400&h=400&fit=crop', count: '960 items', gradient: 'from-amber-500/80 to-orange-600/80' },
              { name: 'Home', image: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400&h=400&fit=crop', count: '1.2K items', gradient: 'from-emerald-500/80 to-teal-600/80' },
            ].map((cat, i) => (
              <motion.div
                key={cat.name}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1 }}
              >
                <Link
                  to={`/products?category=${cat.name}`}
                  className="group relative block rounded-3xl overflow-hidden aspect-square card-hover"
                >
                  <img
                    src={cat.image}
                    alt={cat.name}
                    className="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                  <div className={`absolute inset-0 bg-gradient-to-t ${cat.gradient}`} />
                  <div className="absolute inset-0 flex flex-col items-center justify-center text-white">
                    <h3 className="text-xl md:text-2xl font-bold">{cat.name}</h3>
                    <p className="text-sm text-white/80 mt-1">{cat.count}</p>
                    <div className="mt-3 px-4 py-1.5 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                      Browse →
                    </div>
                  </div>
                </Link>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Featured Products */}
      <section className="py-16 md:py-20 bg-white/50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-end justify-between mb-10">
            <div>
              <div className="inline-flex items-center space-x-2 px-3 py-1 bg-indigo-50 rounded-full mb-3">
                <Sparkles className="h-3.5 w-3.5 text-indigo-500" />
                <span className="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Featured</span>
              </div>
              <h2 className="text-3xl md:text-4xl font-bold text-gray-900">Handpicked for You</h2>
              <p className="text-gray-500 mt-2">Our editors' top picks this week</p>
            </div>
            <Link
              to="/products"
              className="hidden md:inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 group"
            >
              View All
              <ArrowRight className="ml-1.5 h-4 w-4 group-hover:translate-x-1 transition-transform" />
            </Link>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {featuredProducts.map((product, index) => (
              <ProductCard key={product.id} product={product} index={index} />
            ))}
          </div>
        </div>
      </section>

      {/* Trending Products */}
      <section className="py-16 md:py-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-end justify-between mb-10">
            <div>
              <div className="inline-flex items-center space-x-2 px-3 py-1 bg-rose-50 rounded-full mb-3">
                <TrendingUp className="h-3.5 w-3.5 text-rose-500" />
                <span className="text-xs font-semibold text-rose-600 uppercase tracking-wider">Trending</span>
              </div>
              <h2 className="text-3xl md:text-4xl font-bold text-gray-900">Most Popular</h2>
              <p className="text-gray-500 mt-2">Highest rated products this month</p>
            </div>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {trendingProducts.map((product, index) => (
              <ProductCard key={product.id} product={product} index={index} />
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-16 md:py-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-gray-900 via-indigo-950 to-purple-950 p-8 md:p-16">
            {/* Background decorations */}
            <div className="absolute inset-0 overflow-hidden">
              <div className="absolute top-0 right-0 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl" />
              <div className="absolute bottom-0 left-0 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl" />
              <div className="absolute inset-0" style={{ backgroundImage: 'radial-gradient(circle at 1px 1px, rgba(255,255,255,0.05) 1px, transparent 0)', backgroundSize: '40px 40px' }} />
            </div>
            
            <div className="relative text-center max-w-2xl mx-auto">
              <div className="inline-flex items-center space-x-2 px-4 py-2 bg-white/10 border border-white/10 rounded-full mb-6">
                <span className="text-sm text-indigo-200">🎉 Limited Offer</span>
              </div>
              <h2 className="text-3xl md:text-5xl font-bold text-white leading-tight">
                Get 20% Off Your
                <span className="block text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 to-purple-300">
                  First Order
                </span>
              </h2>
              <p className="mt-5 text-indigo-200/80 text-lg">
                Join 50,000+ happy customers. Subscribe for exclusive deals and early access to new arrivals.
              </p>
              <div className="mt-8 flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                <input
                  type="email"
                  placeholder="Enter your email"
                  className="flex-1 px-5 py-3.5 bg-white/10 border border-white/20 rounded-2xl text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-indigo-400/50 focus:bg-white/15 transition-all"
                />
                <button className="px-7 py-3.5 bg-white text-gray-900 font-semibold rounded-2xl hover:bg-gray-100 transition-all hover:shadow-xl hover:-translate-y-0.5 whitespace-nowrap">
                  Get Code
                </button>
              </div>
              <p className="mt-4 text-xs text-white/40">No spam, unsubscribe anytime. By subscribing you agree to our Privacy Policy.</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
