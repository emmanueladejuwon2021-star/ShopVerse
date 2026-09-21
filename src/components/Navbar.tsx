import { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { ShoppingCart, Search, Menu, X, Package, Heart } from 'lucide-react';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { motion, AnimatePresence } from 'framer-motion';

export default function Navbar() {
  const { totalItems, toggleCart } = useCart();
  const { user, isAuthenticated, logout } = useAuth();
  const [searchQuery, setSearchQuery] = useState('');
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    setMobileMenuOpen(false);
    setUserMenuOpen(false);
  }, [location]);

  // Lock body scroll when mobile menu is open
  useEffect(() => {
    if (mobileMenuOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    return () => { document.body.style.overflow = ''; };
  }, [mobileMenuOpen]);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/products?search=${encodeURIComponent(searchQuery)}`);
      setSearchQuery('');
      setMobileMenuOpen(false);
    }
  };

  return (
    <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 safe-top ${
      scrolled 
        ? 'bg-white/80 backdrop-blur-xl shadow-lg shadow-black/[0.03] border-b border-gray-200/50' 
        : 'bg-white/60 backdrop-blur-md'
    }`}>
      <div className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between gap-2 sm:gap-4 h-16 sm:h-[72px]">
          {/* Logo */}
          <Link to="/" className="flex items-center gap-2 sm:gap-2.5 shrink-0 group">
            <div className="relative">
              <div className="absolute inset-0 bg-indigo-500/20 rounded-xl blur-lg group-hover:blur-xl transition-all" />
              <div className="relative h-9 w-9 sm:h-10 sm:w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/25 group-hover:shadow-indigo-500/40 transition-all">
                <Package className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
              </div>
            </div>
            <div className="hidden sm:block">
              <span className="text-lg sm:text-xl font-bold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                ShopVerse
              </span>
              <span className="block text-[10px] font-medium text-gray-400 -mt-0.5 tracking-wider uppercase">Premium Store</span>
            </div>
          </Link>

          {/* Search Bar - Desktop Only */}
          <form onSubmit={handleSearch} className="hidden md:flex flex-1 max-w-xl mx-4 lg:mx-8">
            <div className="relative w-full group">
              <div className="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 rounded-2xl opacity-0 group-focus-within:opacity-100 transition-opacity blur-xl" />
              <div className="relative flex items-center">
                <Search className="absolute left-4 h-4 w-4 text-gray-400 group-focus-within:text-indigo-500 transition-colors" />
                <input
                  type="text"
                  placeholder="Search for anything..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-11 pr-4 py-2.5 sm:py-3 bg-gray-100/80 border border-gray-200/50 rounded-2xl text-sm focus:outline-none focus:bg-white focus:border-indigo-300 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-400"
                />
              </div>
            </div>
          </form>

          {/* Desktop Nav */}
          <div className="hidden md:flex items-center gap-1 shrink-0">
            <Link to="/products" className="px-3 lg:px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-xl hover:bg-gray-100/80 transition-all whitespace-nowrap">
              Shop
            </Link>
            <Link to="/orders" className="px-3 lg:px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-xl hover:bg-gray-100/80 transition-all whitespace-nowrap">
              Orders
            </Link>

            <button className="p-2.5 text-gray-500 hover:text-rose-500 rounded-xl hover:bg-rose-50 transition-all">
              <Heart className="h-5 w-5" />
            </button>

            <button
              onClick={toggleCart}
              className="relative p-2.5 text-gray-600 hover:text-indigo-600 rounded-xl hover:bg-indigo-50 transition-all group"
            >
              <ShoppingCart className="h-5 w-5 transition-transform group-hover:scale-110" />
              {totalItems > 0 && (
                <motion.span
                  initial={{ scale: 0 }}
                  animate={{ scale: 1 }}
                  className="absolute -top-0.5 -right-0.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-lg shadow-indigo-500/30"
                >
                  {totalItems > 99 ? '99+' : totalItems}
                </motion.span>
              )}
            </button>

            <div className="h-6 w-px bg-gray-200 mx-1" />

            {isAuthenticated ? (
              <div className="relative">
                <button
                  onClick={() => setUserMenuOpen(!userMenuOpen)}
                  className="flex items-center gap-2 p-1.5 pr-3 rounded-2xl hover:bg-gray-100/80 transition-all"
                >
                  <div className="h-8 w-8 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center shadow-md shadow-indigo-500/20">
                    <span className="text-white text-xs font-bold">{user?.name?.charAt(0).toUpperCase()}</span>
                  </div>
                  <span className="text-sm font-medium text-gray-700 max-w-[100px] truncate hidden lg:block">{user?.name}</span>
                </button>
                <AnimatePresence>
                  {userMenuOpen && (
                    <>
                      <div className="fixed inset-0 z-40" onClick={() => setUserMenuOpen(false)} />
                      <motion.div
                        initial={{ opacity: 0, y: 8, scale: 0.95 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: 8, scale: 0.95 }}
                        transition={{ duration: 0.15 }}
                        className="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl shadow-black/10 border border-gray-100 py-2 overflow-hidden z-50"
                      >
                        <div className="px-4 py-3 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-100">
                          <p className="text-sm font-semibold text-gray-900 truncate">{user?.name}</p>
                          <p className="text-xs text-gray-500 mt-0.5 truncate">{user?.email}</p>
                        </div>
                        <Link
                          to="/orders"
                          className="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                          <Package className="h-4 w-4 mr-3 text-gray-400" />
                          My Orders
                        </Link>
                        <button
                          onClick={() => { logout(); setUserMenuOpen(false); }}
                          className="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                        >
                          <X className="h-4 w-4 mr-3" />
                          Sign Out
                        </button>
                      </motion.div>
                    </>
                  )}
                </AnimatePresence>
              </div>
            ) : (
              <Link
                to="/login"
                className="ml-1 px-4 lg:px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all whitespace-nowrap"
              >
                Sign In
              </Link>
            )}
          </div>

          {/* Mobile Buttons */}
          <div className="flex md:hidden items-center gap-1 shrink-0">
            <button onClick={toggleCart} className="relative p-2.5 text-gray-600">
              <ShoppingCart className="h-5 w-5" />
              {totalItems > 0 && (
                <span className="absolute top-1 right-1 bg-indigo-600 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center">
                  {totalItems > 9 ? '9+' : totalItems}
                </span>
              )}
            </button>
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="p-2.5 text-gray-600 hover:bg-gray-100 rounded-xl transition-colors"
            >
              {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>
          </div>
        </div>

        {/* Mobile Menu */}
        <AnimatePresence>
          {mobileMenuOpen && (
            <motion.div
              initial={{ opacity: 0, height: 0 }}
              animate={{ opacity: 1, height: 'auto' }}
              exit={{ opacity: 0, height: 0 }}
              transition={{ duration: 0.2 }}
              className="md:hidden overflow-hidden"
            >
              <div className="pb-6 pt-2 space-y-2">
                <form onSubmit={handleSearch} className="mb-4">
                  <div className="relative">
                    <Search className="absolute left-4 top-3.5 h-4 w-4 text-gray-400" />
                    <input
                      type="text"
                      placeholder="Search products..."
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                      className="w-full pl-11 pr-4 py-3 bg-gray-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    />
                  </div>
                </form>
                <Link to="/products" className="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-xl font-medium">
                  Shop All
                </Link>
                <Link to="/orders" className="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-xl font-medium">
                  My Orders
                </Link>
                <div className="pt-2 border-t border-gray-100">
                  {isAuthenticated ? (
                    <>
                      <div className="px-4 py-3">
                        <p className="text-sm font-medium text-gray-900 truncate">{user?.name}</p>
                        <p className="text-xs text-gray-500 truncate">{user?.email}</p>
                      </div>
                      <button onClick={() => logout()} className="flex items-center w-full px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl">
                        Sign Out
                      </button>
                    </>
                  ) : (
                    <Link to="/login" className="block mx-4 px-4 py-3 bg-indigo-600 text-white text-center rounded-xl font-medium">
                      Sign In
                    </Link>
                  )}
                </div>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </nav>
  );
}
