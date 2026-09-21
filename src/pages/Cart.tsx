import { Link } from 'react-router-dom';
import { Minus, Plus, Trash2, ShoppingBag, ArrowLeft } from 'lucide-react';
import { useCart } from '../context/CartContext';
import { motion, AnimatePresence } from 'framer-motion';

export default function Cart() {
  const { state, removeFromCart, updateQuantity, totalPrice, totalItems } = useCart();

  if (state.items.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <ShoppingBag className="h-20 w-20 text-gray-300 mx-auto" />
          <h2 className="text-2xl font-bold text-gray-900 mt-4">Your cart is empty</h2>
          <p className="text-gray-500 mt-2">Looks like you haven't added anything yet.</p>
          <Link
            to="/products"
            className="mt-6 inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-full hover:bg-indigo-700 transition-colors"
          >
            Start Shopping
          </Link>
        </div>
      </div>
    );
  }

  const shipping = totalPrice > 50 ? 0 : 9.99;
  const tax = totalPrice * 0.08;
  const grandTotal = totalPrice + shipping + tax;

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Link to="/products" className="inline-flex items-center text-gray-500 hover:text-indigo-600 mb-6">
          <ArrowLeft className="h-4 w-4 mr-1" />
          Continue Shopping
        </Link>

        <h1 className="text-3xl font-bold text-gray-900 mb-8">Shopping Cart ({totalItems} items)</h1>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Cart Items */}
          <div className="lg:col-span-2 space-y-4">
            <AnimatePresence>
              {state.items.map((item) => (
                <motion.div
                  key={item.product.id}
                  layout
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, x: -100 }}
                  className="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6"
                >
                  <div className="flex space-x-4">
                    <Link to={`/product/${item.product.id}`}>
                      <img
                        src={item.product.image}
                        alt={item.product.name}
                        className="w-24 h-24 sm:w-32 sm:h-32 object-cover rounded-xl"
                      />
                    </Link>
                    <div className="flex-1 min-w-0">
                      <div className="flex justify-between">
                        <Link to={`/product/${item.product.id}`}>
                          <h3 className="font-semibold text-gray-900 hover:text-indigo-600 transition-colors">
                            {item.product.name}
                          </h3>
                        </Link>
                        <button
                          onClick={() => removeFromCart(item.product.id)}
                          className="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                        >
                          <Trash2 className="h-5 w-5" />
                        </button>
                      </div>
                      <p className="text-sm text-gray-500 mt-1">{item.product.category}</p>
                      <div className="flex items-center justify-between mt-4">
                        <div className="flex items-center border border-gray-300 rounded-lg">
                          <button
                            onClick={() => updateQuantity(item.product.id, item.quantity - 1)}
                            className="p-2 hover:bg-gray-50 transition-colors"
                          >
                            <Minus className="h-4 w-4" />
                          </button>
                          <span className="px-4 font-medium text-sm">{item.quantity}</span>
                          <button
                            onClick={() => updateQuantity(item.product.id, item.quantity + 1)}
                            className="p-2 hover:bg-gray-50 transition-colors"
                          >
                            <Plus className="h-4 w-4" />
                          </button>
                        </div>
                        <div className="text-right">
                          <p className="font-bold text-gray-900">${(item.product.price * item.quantity).toFixed(2)}</p>
                          {item.quantity > 1 && (
                            <p className="text-xs text-gray-500">${item.product.price.toFixed(2)} each</p>
                          )}
                        </div>
                      </div>
                    </div>
                  </div>
                </motion.div>
              ))}
            </AnimatePresence>
          </div>

          {/* Order Summary */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
              <h2 className="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>
              <div className="space-y-3 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-600">Subtotal ({totalItems} items)</span>
                  <span className="font-medium">${totalPrice.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Shipping</span>
                  <span className="font-medium">{shipping === 0 ? <span className="text-green-600">Free</span> : `$${shipping.toFixed(2)}`}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Tax</span>
                  <span className="font-medium">${tax.toFixed(2)}</span>
                </div>
                {shipping === 0 && (
                  <p className="text-xs text-green-600 bg-green-50 px-3 py-1.5 rounded-full">
                    ✓ You qualify for free shipping!
                  </p>
                )}
                <div className="border-t border-gray-200 pt-3 flex justify-between">
                  <span className="font-bold text-gray-900">Total</span>
                  <span className="font-bold text-xl text-gray-900">${grandTotal.toFixed(2)}</span>
                </div>
              </div>
              <Link
                to="/checkout"
                className="block w-full mt-6 bg-indigo-600 text-white text-center py-3 rounded-full font-medium hover:bg-indigo-700 transition-colors"
              >
                Proceed to Checkout
              </Link>
              <div className="mt-4 text-center">
                <p className="text-xs text-gray-500">Secure checkout powered by Stripe</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
