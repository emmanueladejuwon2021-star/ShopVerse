import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { CreditCard, Lock, ArrowLeft, Check } from 'lucide-react';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { Address } from '../types';
import { motion } from 'framer-motion';

export default function Checkout() {
  const { state, totalPrice, totalItems, clearCart } = useCart();
  const { isAuthenticated, addOrder, user } = useAuth();
  const navigate = useNavigate();
  const [step, setStep] = useState(1);
  const [orderPlaced, setOrderPlaced] = useState(false);

  const [address, setAddress] = useState<Address>({
    fullName: user?.name || '',
    street: '',
    city: '',
    state: '',
    zipCode: '',
    country: 'United States',
  });

  const [payment, setPayment] = useState({
    cardNumber: '',
    expiry: '',
    cvv: '',
    nameOnCard: '',
  });

  const shipping = totalPrice > 50 ? 0 : 9.99;
  const tax = totalPrice * 0.08;
  const grandTotal = totalPrice + shipping + tax;

  if (state.items.length === 0 && !orderPlaced) {
    return (
      <div className="min-h-screen bg-mesh flex items-center justify-center pt-20 px-4">
        <div className="text-center">
          <h2 className="text-xl sm:text-2xl font-bold text-gray-900">Your cart is empty</h2>
          <Link to="/products" className="mt-4 text-indigo-600 font-medium text-sm sm:text-base">Continue Shopping</Link>
        </div>
      </div>
    );
  }

  if (orderPlaced) {
    return (
      <div className="min-h-screen bg-mesh flex items-center justify-center pt-20 px-4">
        <motion.div
          initial={{ scale: 0.8, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          className="text-center max-w-md mx-auto"
        >
          <motion.div
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ type: 'spring', delay: 0.2 }}
            className="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-emerald-400 to-green-500 rounded-2xl sm:rounded-3xl flex items-center justify-center mx-auto mb-6 sm:mb-8 shadow-xl shadow-emerald-500/25"
          >
            <Check className="h-10 w-10 sm:h-12 sm:w-12 text-white" />
          </motion.div>
          <h2 className="text-2xl sm:text-3xl font-bold text-gray-900">Order Confirmed!</h2>
          <p className="text-gray-500 mt-3 text-base sm:text-lg">Thank you for your purchase. We'll send you a confirmation email shortly.</p>
          <div className="mt-6 sm:mt-8 space-y-3">
            <Link
              to="/orders"
              className="block w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 sm:py-3.5 rounded-xl sm:rounded-2xl font-semibold text-sm sm:text-base hover:shadow-xl hover:shadow-indigo-500/25 transition-all"
            >
              View My Orders
            </Link>
            <Link
              to="/products"
              className="block w-full border border-gray-200 text-gray-700 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl font-medium text-sm sm:text-base hover:bg-gray-50 transition-all"
            >
              Continue Shopping
            </Link>
          </div>
        </motion.div>
      </div>
    );
  }

  const handlePlaceOrder = () => {
    if (!isAuthenticated) {
      navigate('/login?redirect=checkout');
      return;
    }

    const order = {
      id: `ORD-${Date.now()}`,
      items: [...state.items],
      total: grandTotal,
      status: 'processing' as const,
      date: new Date().toISOString(),
      shippingAddress: address,
    };

    addOrder(order);
    clearCart();
    setOrderPlaced(true);
  };

  const steps = [
    { num: 1, label: 'Shipping' },
    { num: 2, label: 'Payment' },
    { num: 3, label: 'Review' },
  ];

  return (
    <div className="min-h-screen bg-mesh pt-20 sm:pt-24 w-full overflow-x-hidden">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <Link to="/cart" className="inline-flex items-center text-gray-500 hover:text-indigo-600 mb-4 sm:mb-6 group text-sm sm:text-base">
          <ArrowLeft className="h-4 w-4 mr-1.5 group-hover:-translate-x-1 transition-transform shrink-0" />
          Back to Cart
        </Link>

        <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-6 sm:mb-8">Checkout</h1>

        {/* Progress Steps */}
        <div className="flex items-center justify-center mb-8 sm:mb-10">
          {steps.map((s, i) => (
            <div key={s.num} className="flex items-center">
              <div className="flex flex-col items-center">
                <div className={`w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center font-bold text-xs sm:text-sm transition-all ${
                  step >= s.num
                    ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/25'
                    : 'bg-gray-100 text-gray-400'
                }`}>
                  {step > s.num ? <Check className="h-4 w-4 sm:h-5 sm:w-5" /> : s.num}
                </div>
                <span className={`text-[10px] sm:text-xs font-medium mt-1.5 sm:mt-2 ${step >= s.num ? 'text-gray-900' : 'text-gray-400'}`}>
                  {s.label}
                </span>
              </div>
              {i < steps.length - 1 && (
                <div className={`w-8 sm:w-16 md:w-24 h-0.5 mx-2 sm:mx-3 mb-4 sm:mb-5 rounded-full transition-all ${
                  step > s.num ? 'bg-gradient-to-r from-indigo-500 to-purple-600' : 'bg-gray-200'
                }`} />
              )}
            </div>
          ))}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
          {/* Form */}
          <div className="lg:col-span-2">
            {step === 1 && (
              <motion.div
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-5 sm:p-6 md:p-8"
              >
                <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Shipping Address</h2>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                  <div className="sm:col-span-2">
                    <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Full Name</label>
                    <input
                      type="text"
                      value={address.fullName}
                      onChange={(e) => setAddress({ ...address, fullName: e.target.value })}
                      className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                      placeholder="John Doe"
                    />
                  </div>
                  <div className="sm:col-span-2">
                    <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Street Address</label>
                    <input
                      type="text"
                      value={address.street}
                      onChange={(e) => setAddress({ ...address, street: e.target.value })}
                      className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                      placeholder="123 Main St, Apt 4"
                    />
                  </div>
                  <div>
                    <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">City</label>
                    <input
                      type="text"
                      value={address.city}
                      onChange={(e) => setAddress({ ...address, city: e.target.value })}
                      className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                      placeholder="San Francisco"
                    />
                  </div>
                  <div>
                    <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">State</label>
                    <input
                      type="text"
                      value={address.state}
                      onChange={(e) => setAddress({ ...address, state: e.target.value })}
                      className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                      placeholder="CA"
                    />
                  </div>
                  <div>
                    <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">ZIP Code</label>
                    <input
                      type="text"
                      value={address.zipCode}
                      onChange={(e) => setAddress({ ...address, zipCode: e.target.value })}
                      className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                      placeholder="94102"
                    />
                  </div>
                  <div>
                    <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Country</label>
                    <input
                      type="text"
                      value={address.country}
                      onChange={(e) => setAddress({ ...address, country: e.target.value })}
                      className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                    />
                  </div>
                </div>
                <button
                  onClick={() => setStep(2)}
                  className="mt-5 sm:mt-6 w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 sm:py-3.5 rounded-xl sm:rounded-2xl font-semibold text-sm sm:text-base hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all"
                >
                  Continue to Payment
                </button>
              </motion.div>
            )}

            {step === 2 && (
              <motion.div
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-5 sm:p-6 md:p-8"
              >
                <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6 flex items-center">
                  <CreditCard className="h-5 w-5 sm:h-6 sm:w-6 mr-2 text-indigo-500 shrink-0" />
                  Payment Details
                </h2>
                <div className="space-y-3 sm:space-y-4">
                  <div>
                    <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Name on Card</label>
                    <input
                      type="text"
                      value={payment.nameOnCard}
                      onChange={(e) => setPayment({ ...payment, nameOnCard: e.target.value })}
                      className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                      placeholder="John Doe"
                    />
                  </div>
                  <div>
                    <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Card Number</label>
                    <input
                      type="text"
                      value={payment.cardNumber}
                      onChange={(e) => setPayment({ ...payment, cardNumber: e.target.value })}
                      className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                      placeholder="4242 4242 4242 4242"
                    />
                  </div>
                  <div className="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                      <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Expiry</label>
                      <input
                        type="text"
                        value={payment.expiry}
                        onChange={(e) => setPayment({ ...payment, expiry: e.target.value })}
                        className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                        placeholder="MM/YY"
                      />
                    </div>
                    <div>
                      <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">CVV</label>
                      <input
                        type="text"
                        value={payment.cvv}
                        onChange={(e) => setPayment({ ...payment, cvv: e.target.value })}
                        className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                        placeholder="123"
                      />
                    </div>
                  </div>
                </div>
                <div className="flex items-center gap-2 mt-4 sm:mt-5 px-3 sm:px-4 py-2.5 sm:py-3 bg-emerald-50 border border-emerald-100 rounded-lg sm:rounded-xl">
                  <Lock className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-emerald-600 shrink-0" />
                  <span className="text-xs sm:text-sm text-emerald-700 font-medium">Your payment info is encrypted and secure</span>
                </div>
                <div className="flex gap-2 sm:gap-3 mt-5 sm:mt-6">
                  <button
                    onClick={() => setStep(1)}
                    className="flex-1 border border-gray-200 text-gray-700 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl font-medium text-sm sm:text-base hover:bg-gray-50 transition-all"
                  >
                    Back
                  </button>
                  <button
                    onClick={() => setStep(3)}
                    className="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 sm:py-3.5 rounded-xl sm:rounded-2xl font-semibold text-sm sm:text-base hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all"
                  >
                    Review Order
                  </button>
                </div>
              </motion.div>
            )}

            {step === 3 && (
              <motion.div
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-5 sm:p-6 md:p-8"
              >
                <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Review Your Order</h2>
                
                <div className="space-y-3 sm:space-y-4">
                  <div className="bg-gray-50 rounded-xl sm:rounded-2xl p-4 sm:p-5">
                    <div className="flex items-center justify-between mb-1.5 sm:mb-2">
                      <h3 className="font-semibold text-gray-900 text-xs sm:text-sm">Shipping Address</h3>
                      <button onClick={() => setStep(1)} className="text-[10px] sm:text-xs text-indigo-600 font-medium hover:text-indigo-700">Edit</button>
                    </div>
                    <p className="text-xs sm:text-sm text-gray-600 leading-relaxed break-all">
                      {address.fullName}<br />
                      {address.street}<br />
                      {address.city}, {address.state} {address.zipCode}<br />
                      {address.country}
                    </p>
                  </div>

                  <div className="bg-gray-50 rounded-xl sm:rounded-2xl p-4 sm:p-5">
                    <div className="flex items-center justify-between mb-1.5 sm:mb-2">
                      <h3 className="font-semibold text-gray-900 text-xs sm:text-sm">Payment Method</h3>
                      <button onClick={() => setStep(2)} className="text-[10px] sm:text-xs text-indigo-600 font-medium hover:text-indigo-700">Edit</button>
                    </div>
                    <div className="flex items-center gap-2 sm:gap-3">
                      <div className="h-7 w-10 sm:h-8 sm:w-12 bg-gray-200 rounded flex items-center justify-center shrink-0">
                        <span className="text-[7px] sm:text-[8px] font-bold text-gray-500">VISA</span>
                      </div>
                      <p className="text-xs sm:text-sm text-gray-600 truncate">
                        •••• •••• •••• {payment.cardNumber.slice(-4) || '4242'}
                      </p>
                    </div>
                  </div>

                  <div className="bg-gray-50 rounded-xl sm:rounded-2xl p-4 sm:p-5">
                    <h3 className="font-semibold text-gray-900 text-xs sm:text-sm mb-2 sm:mb-3">Items ({totalItems})</h3>
                    <div className="space-y-2 sm:space-y-3">
                      {state.items.map(item => (
                        <div key={item.product.id} className="flex items-center gap-2 sm:gap-3">
                          <img src={item.product.image} alt={item.product.name} className="w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl object-cover shrink-0" />
                          <div className="flex-1 min-w-0">
                            <p className="text-xs sm:text-sm font-medium text-gray-900 truncate">{item.product.name}</p>
                            <p className="text-[10px] sm:text-xs text-gray-500">Qty: {item.quantity}</p>
                          </div>
                          <p className="text-xs sm:text-sm font-semibold text-gray-900 shrink-0">${(item.product.price * item.quantity).toFixed(2)}</p>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>

                <div className="flex gap-2 sm:gap-3 mt-5 sm:mt-6">
                  <button
                    onClick={() => setStep(2)}
                    className="flex-1 border border-gray-200 text-gray-700 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl font-medium text-sm sm:text-base hover:bg-gray-50 transition-all"
                  >
                    Back
                  </button>
                  <button
                    onClick={handlePlaceOrder}
                    className="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 sm:py-3.5 rounded-xl sm:rounded-2xl font-semibold text-sm sm:text-base hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all"
                  >
                    Place Order — ${grandTotal.toFixed(2)}
                  </button>
                </div>
              </motion.div>
            )}
          </div>

          {/* Order Summary Sidebar */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-24">
              <h2 className="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Order Summary</h2>
              <div className="space-y-2 sm:space-y-3 max-h-44 sm:max-h-52 overflow-y-auto mb-3 sm:mb-4">
                {state.items.map(item => (
                  <div key={item.product.id} className="flex items-center gap-2 sm:gap-3">
                    <img src={item.product.image} alt={item.product.name} className="w-9 h-9 sm:w-10 sm:h-10 rounded-lg object-cover shrink-0" />
                    <div className="flex-1 min-w-0">
                      <p className="text-[11px] sm:text-xs font-medium text-gray-900 truncate">{item.product.name}</p>
                      <p className="text-[10px] sm:text-[11px] text-gray-500">×{item.quantity}</p>
                    </div>
                    <span className="text-[11px] sm:text-xs font-semibold shrink-0">${(item.product.price * item.quantity).toFixed(2)}</span>
                  </div>
                ))}
              </div>
              <div className="border-t border-gray-100 pt-3 sm:pt-4 space-y-2 sm:space-y-2.5 text-xs sm:text-sm">
                <div className="flex justify-between gap-2">
                  <span className="text-gray-500">Subtotal</span>
                  <span className="font-medium shrink-0">${totalPrice.toFixed(2)}</span>
                </div>
                <div className="flex justify-between gap-2">
                  <span className="text-gray-500">Shipping</span>
                  <span className="font-medium shrink-0">{shipping === 0 ? <span className="text-emerald-600">Free</span> : `$${shipping.toFixed(2)}`}</span>
                </div>
                <div className="flex justify-between gap-2">
                  <span className="text-gray-500">Tax</span>
                  <span className="font-medium shrink-0">${tax.toFixed(2)}</span>
                </div>
                <div className="border-t border-gray-100 pt-2 sm:pt-3 flex justify-between items-center gap-2">
                  <span className="font-bold text-gray-900">Total</span>
                  <span className="text-lg sm:text-xl font-bold text-gray-900 shrink-0">${grandTotal.toFixed(2)}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
