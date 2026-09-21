import { Link } from 'react-router-dom';
import { Package, Mail, Phone, MapPin, ArrowUpRight } from 'lucide-react';

export default function Footer() {
  return (
    <footer className="bg-gray-950 text-gray-300 relative overflow-hidden">
      {/* Background decoration */}
      <div className="absolute inset-0 overflow-hidden">
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-indigo-500/5 rounded-full blur-3xl" />
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500/5 rounded-full blur-3xl" />
      </div>

      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Main Footer */}
        <div className="py-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
          {/* Brand */}
          <div className="lg:col-span-2 space-y-5">
            <Link to="/" className="flex items-center space-x-2.5">
              <div className="h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <Package className="h-5 w-5 text-white" />
              </div>
              <div>
                <span className="text-xl font-bold text-white">ShopVerse</span>
                <span className="block text-[10px] font-medium text-gray-500 -mt-0.5 tracking-wider uppercase">Premium Store</span>
              </div>
            </Link>
            <p className="text-sm text-gray-400 max-w-sm leading-relaxed">
              Your destination for premium products. Curated collections, fast shipping, and exceptional customer service since 2024.
            </p>
            <div className="flex space-x-3">
              {['twitter', 'instagram', 'github', 'linkedin'].map((social) => (
                <a
                  key={social}
                  href="#"
                  className="h-9 w-9 bg-gray-800/50 border border-gray-700/50 rounded-xl flex items-center justify-center hover:bg-indigo-500/20 hover:border-indigo-500/30 hover:text-indigo-400 transition-all"
                >
                  <span className="text-xs font-bold uppercase">{social[0]}</span>
                </a>
              ))}
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-white font-semibold text-sm mb-4">Shop</h3>
            <ul className="space-y-3">
              {[
                { label: 'All Products', to: '/products' },
                { label: 'Electronics', to: '/products?category=Electronics' },
                { label: 'Clothing', to: '/products?category=Clothing' },
                { label: 'Accessories', to: '/products?category=Accessories' },
                { label: 'New Arrivals', to: '/products' },
              ].map((link) => (
                <li key={link.label}>
                  <Link to={link.to} className="text-sm text-gray-400 hover:text-white transition-colors flex items-center group">
                    {link.label}
                    <ArrowUpRight className="h-3 w-3 ml-1 opacity-0 group-hover:opacity-100 transition-opacity" />
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Company */}
          <div>
            <h3 className="text-white font-semibold text-sm mb-4">Company</h3>
            <ul className="space-y-3">
              {['About Us', 'Careers', 'Blog', 'Press', 'Partners'].map((item) => (
                <li key={item}>
                  <a href="#" className="text-sm text-gray-400 hover:text-white transition-colors flex items-center group">
                    {item}
                    <ArrowUpRight className="h-3 w-3 ml-1 opacity-0 group-hover:opacity-100 transition-opacity" />
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="text-white font-semibold text-sm mb-4">Contact</h3>
            <ul className="space-y-3">
              <li className="flex items-center space-x-3 text-sm">
                <Mail className="h-4 w-4 text-indigo-400 shrink-0" />
                <span className="text-gray-400">support@shopverse.com</span>
              </li>
              <li className="flex items-center space-x-3 text-sm">
                <Phone className="h-4 w-4 text-indigo-400 shrink-0" />
                <span className="text-gray-400">+1 (555) 123-4567</span>
              </li>
              <li className="flex items-start space-x-3 text-sm">
                <MapPin className="h-4 w-4 text-indigo-400 shrink-0 mt-0.5" />
                <span className="text-gray-400">123 Market St<br />San Francisco, CA 94103</span>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="border-t border-gray-800/50 py-6 flex flex-col md:flex-row justify-between items-center gap-4">
          <p className="text-xs text-gray-500">&copy; 2026 ShopVerse. All rights reserved.</p>
          <div className="flex items-center space-x-6">
            <a href="#" className="text-xs text-gray-500 hover:text-gray-300 transition-colors">Privacy</a>
            <a href="#" className="text-xs text-gray-500 hover:text-gray-300 transition-colors">Terms</a>
            <a href="#" className="text-xs text-gray-500 hover:text-gray-300 transition-colors">Cookies</a>
            <div className="flex items-center space-x-2 ml-4">
              <div className="h-6 w-10 bg-gray-800 rounded flex items-center justify-center">
                <span className="text-[8px] font-bold text-gray-400">VISA</span>
              </div>
              <div className="h-6 w-10 bg-gray-800 rounded flex items-center justify-center">
                <span className="text-[8px] font-bold text-gray-400">MC</span>
              </div>
              <div className="h-6 w-10 bg-gray-800 rounded flex items-center justify-center">
                <span className="text-[8px] font-bold text-gray-400">AMEX</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
