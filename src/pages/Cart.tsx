import { Link } from 'react-router-dom';
import { Minus, Plus, Trash2, ShoppingBag, ArrowLeft, Tag, Shield } from 'lucide-react';
import { useCart } from '../context/CartContext';
import { motion, AnimatePresence } from 'framer-motion';

export default function Cart() {
  const { state, removeFromCart, updateQuantity, totalPrice, totalItems } = useCart();

  if (state.items.length === 0) {
    return (
      <div className="min-h-screen bg-mesh flex items-center justify-center pt-20 px-4">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-center"
        >
          <div className="h-20 w-20 sm:h-24 sm:w-24 bg-gray-100 rounded-2xl sm:rounded-3xl flex items-center justify-center mx-auto mb-6">
            <ShoppingBag className="h-10 w-10 sm:h-12 sm:w-12 text-gray-300" />
          </div>
          <h2 className="text-xl sm:text-2xl font-bold text-gray-900">Your cart is empty</h2>
          <p className="text-gray-500 mt-2 max-w-sm text-sm sm:text-base">Looks like you haven't added anything to your cart yet. Start exploring our collection!</p>
          <Link
            to="/products"
            className="mt-6 sm:mt-8 inline-flex items-center px-6 sm:px-8 py-3 sm:py-3.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl sm:rounded-2xl hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all text-sm sm:text-base"
          >
            Start Shopping
          </Link>
        </motion.div>
      </div>
    );
  }

  const shipping = totalPrice > 50 ? 0 : 9.99;
  const tax = totalPrice * 0.08;
  const grandTotal = totalPrice + shipping + tax;

  return (
    <div className="min-h-screen bg-mesh pt-20 sm:pt-24 w-full overflow-x-hidden">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <Link to="/products" className="inline-flex items-center text-gray-500 hover:text-indigo-600 mb-4 sm:mb-6 group text-sm sm:text-base">
          <ArrowLeft className="h-4 w-4 mr-1.5 group-hover:-translate-x-1 transition-transform shrink-0" />
          Continue Shopping
        </Link>

        <div className="flex items-center justify-between mb-6 sm:mb-8 gap-3">
          <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">Shopping Cart</h1>
          <span className="text-xs sm:text-sm text-gray-500 shrink-0">{totalItems} {totalItems === 1 ? 'item' : 'items'}</span>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
          {/* Cart Items */}
          <div className="lg:col-span-2 space-y-3 sm:space-y-4">
            <AnimatePresence>
              {state.items.map((item) => (
                <motion.div
                  key={item.product.id}
                  layout
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, x: -100 }}
                  className="bg-white rounded-2xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-5 md:p-6 group"
                >
                  <div className="flex gap-3 sm:gap-4 md:gap-5">
                    <Link to={`/product/${item.product.id}`} className="shrink-0">
                      <img
                        src={item.product.image}
                        alt={item.product.name}
                        className="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 object-cover rounded-xl sm:rounded-2xl"
                      />
                    </Link>
                    <div className="flex-1 min-w-0">
                      <div className="flex justify-between items-start gap-2">
                        <div className="min-w-0">
                          <Link to={`/product/${item.product.id}`}>
                            <h3 className="font-semibold text-gray-900 hover:text-indigo-600 transition-colors line-clamp-2 text-sm sm:text-base">
                              {item.product.name}
                            </h3>
                          </Link>
                          <p className="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">{item.product.category}</p>
                        </div>
                        <button
                          onClick={() => removeFromCart(item.product.id)}
                          className="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all shrink-0"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                      <div className="flex items-center justify-between mt-3 sm:mt-4 gap-2">
                        <div className="flex items-center bg-gray-100 rounded-xl overflow-hidden">
                          <button
                            onClick={() => updateQuantity(item.product.id, item.quantity - 1)}
                            className="p-2 sm:p-2.5 hover:bg-gray-200 transition-colors"
                          >
                            <Minus className="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                          </button>
                          <span className="px-3 sm:px-4 font-semibold text-xs sm:text-sm text-gray-900">{item.quantity}</span>
                          <button
                            onClick={() => updateQuantity(item.product.id, item.quantity + 1)}
                            className="p-2 sm:p-2.5 hover:bg-gray-200 transition-colors"
                          >
                            <Plus className="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                          </button>
                        </div>
                        <div className="text-right shrink-0">
                          <p className="font-bold text-base sm:text-lg text-gray-900">${(item.product.price * item.quantity).toFixed(2)}</p>
                          {item.quantity > 1 && (
                            <p className="text-[10px] sm:text-xs text-gray-500">${item.product.price.toFixed(2)} each</p>
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
            <div className="bg-white rounded-2xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-24">
              <h2 className="text-base sm:text-lg font-bold text-gray-900 mb-4 sm:mb-5">Order Summary</h2>
              
              {/* Coupon */}
              <div className="flex gap-2 mb-4 sm:mb-5">
                <div className="relative flex-1 min-w-0">
                  <Tag className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                  <input
                    type="text"
                    placeholder="Coupon code"
                    className="w-full pl-9 pr-3 py-2 sm:py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300"
                  />
                </div>
                <button className="px-3 sm:px-4 py-2 sm:py-2.5 bg-gray-900 text-white text-xs sm:text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors shrink-0">
                  Apply
                </button>
              </div>

              <div className="space-y-2.5 sm:space-y-3 text-xs sm:text-sm border-t border-gray-100 pt-3 sm:pt-4">
                <div className="flex justify-between gap-2">
                  <span className="text-gray-500">Subtotal ({totalItems} items)</span>
                  <span className="font-medium text-gray-900 shrink-0">${totalPrice.toFixed(2)}</span>
                </div>
                <div className="flex justify-between gap-2">
                  <span className="text-gray-500">Shipping</span>
                  <span className="font-medium shrink-0">
                    {shipping === 0 ? <span className="text-emerald-600">Free</span> : `$${shipping.toFixed(2)}`}
                  </span>
                </div>
                <div className="flex justify-between gap-2">
                  <span className="text-gray-500">Estimated Tax</span>
                  <span className="font-medium text-gray-900 shrink-0">${tax.toFixed(2)}</span>
                </div>
                
                {totalPrice < 50 && (
                  <div className="bg-amber-50 border border-amber-100 rounded-xl p-2.5 sm:p-3">
                    <p className="text-[10px] sm:text-xs text-amber-700">
                      Add <span className="font-bold">${(50 - totalPrice).toFixed(2)}</span> more for free shipping
                    </p>
                  </div>
                )}

                <div className="border-t border-gray-100 pt-2.5 sm:pt-3 flex justify-between items-center gap-2">
                  <span className="font-bold text-gray-900">Total</span>
                  <span className="text-xl sm:text-2xl font-bold text-gray-900 shrink-0">${grandTotal.toFixed(2)}</span>
                </div>
              </div>
              
              <Link
                to="/checkout"
                className="block w-full mt-4 sm:mt-6 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-center py-3 sm:py-4 rounded-xl sm:rounded-2xl font-semibold text-sm sm:text-base hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all"
              >
                Proceed to Checkout
              </Link>
              
              <div className="mt-3 sm:mt-4 flex items-center justify-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs text-gray-400">
                <Shield className="h-3 w-3 sm:h-3.5 sm:w-3.5 shrink-0" />
                <span>Secure checkout • SSL encrypted</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
