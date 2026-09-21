import { Link } from 'react-router-dom';
import { Package, Mail, Phone, MapPin, ArrowUpRight } from 'lucide-react';

export default function Footer() {
  return (
    <footer className="bg-gray-950 text-gray-300 relative overflow-hidden w-full">
      {/* Background decoration */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute -top-40 -right-40 w-64 sm:w-80 h-64 sm:h-80 bg-indigo-500/5 rounded-full blur-3xl" />
        <div className="absolute -bottom-40 -left-40 w-64 sm:w-80 h-64 sm:h-80 bg-purple-500/5 rounded-full blur-3xl" />
      </div>

      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Main Footer */}
        <div className="py-10 sm:py-12 md:py-16 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8 sm:gap-10">
          {/* Brand */}
          <div className="col-span-2 sm:col-span-2 md:col-span-3 lg:col-span-2 space-y-4 sm:space-y-5">
            <Link to="/" className="flex items-center gap-2.5">
              <div className="h-9 w-9 sm:h-10 sm:w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0">
                <Package className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
              </div>
              <div>
                <span className="text-lg sm:text-xl font-bold text-white">ShopVerse</span>
                <span className="block text-[10px] font-medium text-gray-500 -mt-0.5 tracking-wider uppercase">Premium Store</span>
              </div>
            </Link>
            <p className="text-xs sm:text-sm text-gray-400 max-w-sm leading-relaxed">
              Your destination for premium products. Curated collections, fast shipping, and exceptional customer service since 2024.
            </p>
            <div className="flex gap-2 sm:gap-3">
              {['T', 'I', 'G', 'L'].map((letter) => (
                <a
                  key={letter}
                  href="#"
                  className="h-8 w-8 sm:h-9 sm:w-9 bg-gray-800/50 border border-gray-700/50 rounded-lg sm:rounded-xl flex items-center justify-center hover:bg-indigo-500/20 hover:border-indigo-500/30 hover:text-indigo-400 transition-all text-xs sm:text-sm"
                >
                  <span className="font-bold">{letter}</span>
                </a>
              ))}
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-white font-semibold text-xs sm:text-sm mb-3 sm:mb-4">Shop</h3>
            <ul className="space-y-2 sm:space-y-3">
              {[
                { label: 'All Products', to: '/products' },
                { label: 'Electronics', to: '/products?category=Electronics' },
                { label: 'Clothing', to: '/products?category=Clothing' },
                { label: 'Accessories', to: '/products?category=Accessories' },
                { label: 'New Arrivals', to: '/products' },
              ].map((link) => (
                <li key={link.label}>
                  <Link to={link.to} className="text-xs sm:text-sm text-gray-400 hover:text-white transition-colors flex items-center group">
                    <span className="truncate">{link.label}</span>
                    <ArrowUpRight className="h-3 w-3 ml-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0" />
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Company */}
          <div>
            <h3 className="text-white font-semibold text-xs sm:text-sm mb-3 sm:mb-4">Company</h3>
            <ul className="space-y-2 sm:space-y-3">
              {['About Us', 'Careers', 'Blog', 'Press', 'Partners'].map((item) => (
                <li key={item}>
                  <a href="#" className="text-xs sm:text-sm text-gray-400 hover:text-white transition-colors flex items-center group">
                    <span className="truncate">{item}</span>
                    <ArrowUpRight className="h-3 w-3 ml-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0" />
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div className="col-span-2 sm:col-span-2 md:col-span-3 lg:col-span-1">
            <h3 className="text-white font-semibold text-xs sm:text-sm mb-3 sm:mb-4">Contact</h3>
            <ul className="space-y-2.5 sm:space-y-3">
              <li className="flex items-center gap-2 sm:gap-3 text-xs sm:text-sm">
                <Mail className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-indigo-400 shrink-0" />
                <span className="text-gray-400 truncate">support@shopverse.com</span>
              </li>
              <li className="flex items-center gap-2 sm:gap-3 text-xs sm:text-sm">
                <Phone className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-indigo-400 shrink-0" />
                <span className="text-gray-400">+1 (555) 123-4567</span>
              </li>
              <li className="flex items-start gap-2 sm:gap-3 text-xs sm:text-sm">
                <MapPin className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-indigo-400 shrink-0 mt-0.5" />
                <span className="text-gray-400">123 Market St<br />San Francisco, CA 94103</span>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="border-t border-gray-800/50 py-4 sm:py-6 flex flex-col sm:flex-row justify-between items-center gap-3 sm:gap-4">
          <p className="text-[10px] sm:text-xs text-gray-500 text-center sm:text-left">&copy; 2026 ShopVerse. All rights reserved.</p>
          <div className="flex items-center gap-4 sm:gap-6 flex-wrap justify-center">
            <a href="#" className="text-[10px] sm:text-xs text-gray-500 hover:text-gray-300 transition-colors">Privacy</a>
            <a href="#" className="text-[10px] sm:text-xs text-gray-500 hover:text-gray-300 transition-colors">Terms</a>
            <a href="#" className="text-[10px] sm:text-xs text-gray-500 hover:text-gray-300 transition-colors">Cookies</a>
            <div className="flex items-center gap-1.5 sm:gap-2">
              <div className="h-5 w-8 sm:h-6 sm:w-10 bg-gray-800 rounded flex items-center justify-center">
                <span className="text-[7px] sm:text-[8px] font-bold text-gray-400">VISA</span>
              </div>
              <div className="h-5 w-8 sm:h-6 sm:w-10 bg-gray-800 rounded flex items-center justify-center">
                <span className="text-[7px] sm:text-[8px] font-bold text-gray-400">MC</span>
              </div>
              <div className="h-5 w-8 sm:h-6 sm:w-10 bg-gray-800 rounded flex items-center justify-center">
                <span className="text-[7px] sm:text-[8px] font-bold text-gray-400">AMEX</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
