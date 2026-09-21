import { useParams, Link } from 'react-router-dom';
import { useState } from 'react';
import { Star, ShoppingCart, Heart, Truck, Shield, RotateCcw, ChevronLeft, Minus, Plus, Check } from 'lucide-react';
import { products } from '../data/products';
import { useCart } from '../context/CartContext';
import ProductCard from '../components/ProductCard';
import { motion } from 'framer-motion';

export default function ProductDetail() {
  const { id } = useParams();
  const { addToCart } = useCart();
  const [quantity, setQuantity] = useState(1);
  const [added, setAdded] = useState(false);

  const product = products.find(p => p.id === id);

  if (!product) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-gray-900">Product Not Found</h2>
          <Link to="/products" className="mt-4 text-indigo-600 font-medium hover:text-indigo-700">
            ← Back to Products
          </Link>
        </div>
      </div>
    );
  }

  const relatedProducts = products.filter(p => p.category === product.category && p.id !== product.id).slice(0, 4);

  const handleAddToCart = () => {
    for (let i = 0; i < quantity; i++) {
      addToCart(product);
    }
    setAdded(true);
    setTimeout(() => setAdded(false), 2000);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Breadcrumb */}
        <nav className="flex items-center space-x-2 text-sm text-gray-500 mb-8">
          <Link to="/" className="hover:text-indigo-600">Home</Link>
          <span>/</span>
          <Link to="/products" className="hover:text-indigo-600">Products</Link>
          <span>/</span>
          <Link to={`/products?category=${product.category}`} className="hover:text-indigo-600">{product.category}</Link>
          <span>/</span>
          <span className="text-gray-900 font-medium truncate">{product.name}</span>
        </nav>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
          {/* Product Image */}
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            className="relative"
          >
            <div className="aspect-square rounded-3xl overflow-hidden bg-white shadow-sm border border-gray-100">
              <img
                src={product.image}
                alt={product.name}
                className="w-full h-full object-cover"
              />
            </div>
            {product.originalPrice && (
              <span className="absolute top-4 left-4 bg-red-500 text-white text-sm font-bold px-3 py-1.5 rounded-full">
                Sale
              </span>
            )}
          </motion.div>

          {/* Product Info */}
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            className="flex flex-col"
          >
            <div className="flex items-center space-x-2 mb-2">
              <span className="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
                {product.category}
              </span>
              {product.inStock ? (
                <span className="px-3 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full">In Stock</span>
              ) : (
                <span className="px-3 py-1 bg-red-50 text-red-700 text-xs font-medium rounded-full">Out of Stock</span>
              )}
            </div>

            <h1 className="text-3xl font-bold text-gray-900 mt-2">{product.name}</h1>

            {/* Rating */}
            <div className="flex items-center space-x-2 mt-3">
              <div className="flex items-center">
                {[...Array(5)].map((_, i) => (
                  <Star
                    key={i}
                    className={`h-5 w-5 ${i < Math.floor(product.rating) ? 'fill-amber-400 text-amber-400' : 'text-gray-300'}`}
                  />
                ))}
              </div>
              <span className="text-sm font-medium text-gray-700">{product.rating}</span>
              <span className="text-sm text-gray-500">({product.reviews.toLocaleString()} reviews)</span>
            </div>

            {/* Price */}
            <div className="flex items-center space-x-3 mt-6">
              <span className="text-3xl font-bold text-gray-900">${product.price.toFixed(2)}</span>
              {product.originalPrice && (
                <>
                  <span className="text-xl text-gray-400 line-through">${product.originalPrice.toFixed(2)}</span>
                  <span className="bg-red-100 text-red-700 text-sm font-bold px-2 py-0.5 rounded">
                    Save ${(product.originalPrice - product.price).toFixed(2)}
                  </span>
                </>
              )}
            </div>

            {/* Description */}
            <p className="text-gray-600 mt-6 leading-relaxed">{product.description}</p>

            {/* Features */}
            <div className="mt-6">
              <h3 className="font-semibold text-gray-900 mb-3">Key Features</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                {product.features.map((feature, i) => (
                  <div key={i} className="flex items-center space-x-2">
                    <Check className="h-4 w-4 text-green-500 shrink-0" />
                    <span className="text-sm text-gray-600">{feature}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Quantity & Add to Cart */}
            <div className="mt-8 space-y-4">
              <div className="flex items-center space-x-4">
                <span className="text-sm font-medium text-gray-700">Quantity:</span>
                <div className="flex items-center border border-gray-300 rounded-lg">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="p-2 hover:bg-gray-50 transition-colors"
                  >
                    <Minus className="h-4 w-4" />
                  </button>
                  <span className="px-4 font-medium">{quantity}</span>
                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="p-2 hover:bg-gray-50 transition-colors"
                  >
                    <Plus className="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div className="flex space-x-3">
                <button
                  onClick={handleAddToCart}
                  disabled={!product.inStock}
                  className={`flex-1 flex items-center justify-center space-x-2 py-3.5 rounded-full font-medium transition-all ${
                    added
                      ? 'bg-green-500 text-white'
                      : product.inStock
                      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                      : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  }`}
                >
                  {added ? (
                    <>
                      <Check className="h-5 w-5" />
                      <span>Added to Cart!</span>
                    </>
                  ) : (
                    <>
                      <ShoppingCart className="h-5 w-5" />
                      <span>Add to Cart</span>
                    </>
                  )}
                </button>
                <button className="p-3.5 border border-gray-300 rounded-full hover:bg-gray-50 transition-colors">
                  <Heart className="h-5 w-5 text-gray-500" />
                </button>
              </div>
            </div>

            {/* Benefits */}
            <div className="mt-8 grid grid-cols-3 gap-4 pt-6 border-t border-gray-200">
              <div className="text-center">
                <Truck className="h-6 w-6 text-indigo-600 mx-auto" />
                <p className="text-xs text-gray-600 mt-1">Free Shipping</p>
              </div>
              <div className="text-center">
                <Shield className="h-6 w-6 text-indigo-600 mx-auto" />
                <p className="text-xs text-gray-600 mt-1">2-Year Warranty</p>
              </div>
              <div className="text-center">
                <RotateCcw className="h-6 w-6 text-indigo-600 mx-auto" />
                <p className="text-xs text-gray-600 mt-1">30-Day Returns</p>
              </div>
            </div>
          </motion.div>
        </div>

        {/* Related Products */}
        {relatedProducts.length > 0 && (
          <section className="mt-16">
            <h2 className="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {relatedProducts.map((p, i) => (
                <ProductCard key={p.id} product={p} index={i} />
              ))}
            </div>
          </section>
        )}
      </div>
    </div>
  );
}
