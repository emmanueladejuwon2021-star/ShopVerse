import { Link } from 'react-router-dom';
import { Package, Clock, Truck, CheckCircle, ShoppingBag, ArrowRight } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { motion } from 'framer-motion';

const statusConfig = {
  pending: { icon: Clock, color: 'text-amber-600', bg: 'bg-amber-50', border: 'border-amber-100', label: 'Pending' },
  processing: { icon: Package, color: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-100', label: 'Processing' },
  shipped: { icon: Truck, color: 'text-purple-600', bg: 'bg-purple-50', border: 'border-purple-100', label: 'Shipped' },
  delivered: { icon: CheckCircle, color: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100', label: 'Delivered' },
};

export default function Orders() {
  const { orders, isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return (
      <div className="min-h-screen bg-mesh flex items-center justify-center pt-20">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-center"
        >
          <div className="h-20 w-20 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
            <Package className="h-10 w-10 text-gray-300" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900">Sign in to view orders</h2>
          <p className="text-gray-500 mt-2">You need to be logged in to see your order history.</p>
          <Link
            to="/login"
            className="mt-6 inline-flex items-center px-8 py-3.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-2xl hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all"
          >
            Sign In
            <ArrowRight className="ml-2 h-4 w-4" />
          </Link>
        </motion.div>
      </div>
    );
  }

  if (orders.length === 0) {
    return (
      <div className="min-h-screen bg-mesh flex items-center justify-center pt-20">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-center"
        >
          <div className="h-20 w-20 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
            <ShoppingBag className="h-10 w-10 text-gray-300" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900">No orders yet</h2>
          <p className="text-gray-500 mt-2 max-w-sm">When you place an order, it will appear here. Start shopping to see your orders!</p>
          <Link
            to="/products"
            className="mt-6 inline-flex items-center px-8 py-3.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-2xl hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all"
          >
            Start Shopping
            <ArrowRight className="ml-2 h-4 w-4" />
          </Link>
        </motion.div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-mesh pt-24">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl md:text-4xl font-bold text-gray-900">My Orders</h1>
            <p className="text-gray-500 mt-1">{orders.length} {orders.length === 1 ? 'order' : 'orders'} placed</p>
          </div>
          <Link
            to="/products"
            className="hidden sm:inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-all"
          >
            Shop More
          </Link>
        </div>

        <div className="space-y-5">
          {orders.map((order, index) => {
            const status = statusConfig[order.status];
            const StatusIcon = status.icon;

            return (
              <motion.div
                key={order.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: index * 0.1 }}
                className="bg-white rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 overflow-hidden card-hover"
              >
                {/* Order Header */}
                <div className="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                  <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                      <p className="text-sm font-semibold text-gray-900">Order #{order.id}</p>
                      <p className="text-xs text-gray-500 mt-0.5">
                        Placed on {new Date(order.date).toLocaleDateString('en-US', {
                          year: 'numeric',
                          month: 'long',
                          day: 'numeric',
                        })}
                      </p>
                    </div>
                    <div className="flex items-center space-x-3">
                      <div className={`flex items-center space-x-1.5 px-3 py-1.5 rounded-lg border ${status.bg} ${status.border}`}>
                        <StatusIcon className={`h-3.5 w-3.5 ${status.color}`} />
                        <span className={`text-xs font-semibold ${status.color}`}>{status.label}</span>
                      </div>
                      <span className="text-lg font-bold text-gray-900">${order.total.toFixed(2)}</span>
                    </div>
                  </div>
                </div>

                {/* Order Items */}
                <div className="p-6">
                  <div className="space-y-3">
                    {order.items.map((item) => (
                      <div key={item.product.id} className="flex items-center space-x-4">
                        <img
                          src={item.product.image}
                          alt={item.product.name}
                          className="w-14 h-14 rounded-xl object-cover"
                        />
                        <div className="flex-1 min-w-0">
                          <p className="text-sm font-medium text-gray-900 truncate">{item.product.name}</p>
                          <p className="text-xs text-gray-500">Qty: {item.quantity} • {item.product.category}</p>
                        </div>
                        <p className="text-sm font-semibold text-gray-900">
                          ${(item.product.price * item.quantity).toFixed(2)}
                        </p>
                      </div>
                    ))}
                  </div>

                  {/* Shipping Address */}
                  <div className="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <p className="text-xs text-gray-500">
                      <span className="font-medium text-gray-700">Ship to:</span> {order.shippingAddress.fullName}, {order.shippingAddress.city}, {order.shippingAddress.state}
                    </p>
                    <Link to={`/product/${order.items[0]?.product.id}`} className="text-xs text-indigo-600 font-medium hover:text-indigo-700">
                      Buy Again →
                    </Link>
                  </div>
                </div>
              </motion.div>
            );
          })}
        </div>
      </div>
    </div>
  );
}
