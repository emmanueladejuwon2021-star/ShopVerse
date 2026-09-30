import { Link } from 'react-router-dom';
import { Minus, Plus, Trash2, ShoppingBag, X, ArrowRight } from 'lucide-react';
import { useCart } from '../context/CartContext';
import { motion, AnimatePresence } from 'framer-motion';

export default function CartSidebar() {
  const { state, closeCart, removeFromCart, updateQuantity, totalPrice, totalItems } = useCart();

  return (
    <AnimatePresence>
      {state.isOpen && (
        <>
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/40 backdrop-blur-sm z-50"
            onClick={closeCart}
          />
          <motion.div
            initial={{ x: '100%' }}
            animate={{ x: 0 }}
            exit={{ x: '100%' }}
            transition={{ type: 'spring', damping: 30, stiffness: 300 }}
            className="fixed right-0 top-0 h-full w-full sm:max-w-md bg-white dark:bg-slate-900 shadow-2xl z-50 flex flex-col safe-bottom"
          >
            <div className="flex items-center justify-between px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-100 dark:border-slate-800 shrink-0">
              <div className="flex items-center gap-2 sm:gap-3 min-w-0">
                <div className="h-9 w-9 sm:h-10 sm:w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0">
                  <ShoppingBag className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                </div>
                <div className="min-w-0">
                  <h2 className="text-base sm:text-lg font-bold text-gray-900 dark:text-white">Your Cart</h2>
                  <p className="text-xs text-gray-500 dark:text-slate-400">{totalItems} {totalItems === 1 ? 'item' : 'items'}</p>
                </div>
              </div>
              <button onClick={closeCart} className="p-2 sm:p-2.5 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-xl transition-colors shrink-0">
                <X className="h-5 w-5 text-gray-500 dark:text-slate-400" />
              </button>
            </div>
            <div className="flex-1 overflow-y-auto px-4 sm:px-6 py-3 sm:py-4 min-h-0">
              {state.items.length === 0 ? (
                <div className="flex flex-col items-center justify-center h-full text-center px-4">
                  <div className="h-16 w-16 sm:h-20 sm:w-20 bg-gray-100 dark:bg-slate-800 rounded-2xl sm:rounded-3xl flex items-center justify-center mb-4">
                    <ShoppingBag className="h-8 w-8 sm:h-10 sm:w-10 text-gray-300 dark:text-slate-600" />
                  </div>
                  <p className="text-gray-900 dark:text-white text-base sm:text-lg font-semibold">Cart is empty</p>
                  <p className="text-gray-500 dark:text-slate-400 text-sm mt-1">Add some items to get started</p>
                  <button
                    onClick={closeCart}
                    className="mt-6 px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors"
                  >
                    Browse Products
                  </button>
                </div>
              ) : (
                <div className="space-y-2 sm:space-y-3">
                  {state.items.map((item) => (
                    <motion.div
                      key={item.product.id}
                      layout
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, x: -50, height: 0 }}
                      className="relative flex gap-2 sm:gap-3 bg-gray-50 dark:bg-slate-800 rounded-xl sm:rounded-2xl p-2.5 sm:p-3"
                    >
                      <img src={item.product.image} alt={item.product.name} className="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg sm:rounded-xl shrink-0" />
                      <div className="flex-1 min-w-0 flex flex-col justify-between">
                        <div className="min-w-0">
                          <h3 className="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white truncate pr-6">{item.product.name}</h3>
                          <p className="text-[10px] sm:text-xs text-gray-500 dark:text-slate-400 mt-0.5">{item.product.category}</p>
                        </div>
                        <div className="flex items-center justify-between gap-2 mt-1.5 sm:mt-2">
                          <div className="flex items-center bg-white dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg overflow-hidden">
                            <button onClick={() => updateQuantity(item.product.id, item.quantity - 1)} className="p-1 sm:p-1.5 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                              <Minus className="h-2.5 w-2.5 sm:h-3 sm:w-3 text-gray-700 dark:text-slate-200" />
                            </button>
                            <span className="text-[11px] sm:text-xs font-semibold w-5 sm:w-7 text-center text-gray-900 dark:text-white">{item.quantity}</span>
                            <button onClick={() => updateQuantity(item.product.id, item.quantity + 1)} className="p-1 sm:p-1.5 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                              <Plus className="h-2.5 w-2.5 sm:h-3 sm:w-3 text-gray-700 dark:text-slate-200" />
                            </button>
                          </div>
                          <span className="text-xs sm:text-sm font-bold text-gray-900 dark:text-white shrink-0">${(item.product.price * item.quantity).toFixed(2)}</span>
                        </div>
                      </div>
                      <button onClick={() => removeFromCart(item.product.id)} className="absolute top-2 right-2 p-1.5 text-gray-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-all">
                        <Trash2 className="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                      </button>
                    </motion.div>
                  ))}
                </div>
              )}
            </div>
            {state.items.length > 0 && (
              <div className="border-t border-gray-100 dark:border-slate-800 px-4 sm:px-6 py-4 sm:py-5 space-y-3 sm:space-y-4 bg-gray-50/50 dark:bg-slate-800/50 shrink-0">
                {totalPrice < 50 && (
                  <div className="space-y-1.5 sm:space-y-2">
                    <div className="flex justify-between text-[10px] sm:text-xs">
                      <span className="text-gray-500 dark:text-slate-400">Add ${(50 - totalPrice).toFixed(2)} more for free shipping</span>
                      <span className="font-medium text-indigo-600 dark:text-indigo-400">{Math.round((totalPrice / 50) * 100)}%</span>
                    </div>
                    <div className="h-1.5 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                      <motion.div initial={{ width: 0 }} animate={{ width: `${Math.min(100, (totalPrice / 50) * 100)}%` }} className="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full" />
                    </div>
                  </div>
                )}
                {totalPrice >= 50 && (
                  <div className="flex items-center gap-2 px-3 py-2 bg-green-50 dark:bg-green-500/10 rounded-xl">
                    <span className="text-green-600 dark:text-green-400 text-xs">✓</span>
                    <span className="text-[10px] sm:text-xs font-medium text-green-700 dark:text-green-400">You qualify for free shipping!</span>
                  </div>
                )}
                <div className="flex justify-between items-center pt-1 sm:pt-2">
                  <span className="text-gray-600 dark:text-slate-300 font-medium text-sm sm:text-base">Subtotal</span>
                  <span className="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">${totalPrice.toFixed(2)}</span>
                </div>
                <Link to="/checkout" onClick={closeCart} className="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 sm:py-3.5 rounded-xl sm:rounded-2xl font-semibold text-sm sm:text-base hover:shadow-lg hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all">
                  <span>Checkout</span>
                  <ArrowRight className="h-4 w-4" />
                </Link>
                <Link to="/cart" onClick={closeCart} className="block w-full border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-slate-200 text-center py-2.5 sm:py-3 rounded-xl sm:rounded-2xl font-medium hover:bg-white dark:hover:bg-slate-800 hover:border-gray-300 dark:hover:border-slate-600 transition-all text-xs sm:text-sm">
                  View Full Cart
                </Link>
              </div>
            )}
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}
