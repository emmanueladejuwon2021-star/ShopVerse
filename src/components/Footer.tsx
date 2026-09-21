import { Link } from 'react-router-dom';
import { Package, Mail, Phone, MapPin } from 'lucide-react';

export default function Footer() {
  return (
    <footer className="bg-gray-900 text-gray-300">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Brand */}
          <div className="space-y-4">
            <div className="flex items-center space-x-2">
              <Package className="h-7 w-7 text-indigo-400" />
              <span className="text-xl font-bold text-white">ShopVerse</span>
            </div>
            <p className="text-sm text-gray-400">
              Your one-stop destination for quality products. Shop with confidence and enjoy fast delivery.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-white font-semibold mb-4">Quick Links</h3>
            <ul className="space-y-2">
              <li><Link to="/" className="text-sm hover:text-indigo-400 transition-colors">Home</Link></li>
              <li><Link to="/products" className="text-sm hover:text-indigo-400 transition-colors">Products</Link></li>
              <li><Link to="/orders" className="text-sm hover:text-indigo-400 transition-colors">My Orders</Link></li>
              <li><Link to="/cart" className="text-sm hover:text-indigo-400 transition-colors">Cart</Link></li>
            </ul>
          </div>

          {/* Categories */}
          <div>
            <h3 className="text-white font-semibold mb-4">Categories</h3>
            <ul className="space-y-2">
              <li><Link to="/products?category=Electronics" className="text-sm hover:text-indigo-400 transition-colors">Electronics</Link></li>
              <li><Link to="/products?category=Clothing" className="text-sm hover:text-indigo-400 transition-colors">Clothing</Link></li>
              <li><Link to="/products?category=Accessories" className="text-sm hover:text-indigo-400 transition-colors">Accessories</Link></li>
              <li><Link to="/products?category=Home" className="text-sm hover:text-indigo-400 transition-colors">Home & Living</Link></li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="text-white font-semibold mb-4">Contact Us</h3>
            <ul className="space-y-3">
              <li className="flex items-center space-x-2 text-sm">
                <Mail className="h-4 w-4 text-indigo-400" />
                <span>support@shopverse.com</span>
              </li>
              <li className="flex items-center space-x-2 text-sm">
                <Phone className="h-4 w-4 text-indigo-400" />
                <span>+1 (555) 123-4567</span>
              </li>
              <li className="flex items-center space-x-2 text-sm">
                <MapPin className="h-4 w-4 text-indigo-400" />
                <span>San Francisco, CA</span>
              </li>
            </ul>
          </div>
        </div>

        <div className="border-t border-gray-800 mt-10 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p className="text-sm text-gray-500">&copy; 2026 ShopVerse. All rights reserved.</p>
          <div className="flex space-x-6 mt-4 md:mt-0">
            <a href="#" className="text-sm text-gray-500 hover:text-indigo-400 transition-colors">Privacy Policy</a>
            <a href="#" className="text-sm text-gray-500 hover:text-indigo-400 transition-colors">Terms of Service</a>
            <a href="#" className="text-sm text-gray-500 hover:text-indigo-400 transition-colors">Cookie Policy</a>
          </div>
        </div>
      </div>
    </footer>
  );
}
