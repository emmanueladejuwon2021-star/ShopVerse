import { useParams, Link } from 'react-router-dom';
import { useState } from 'react';
import { Star, ShoppingCart, Heart, Truck, Shield, RotateCcw, Minus, Plus, Check, Share2 } from 'lucide-react';
import { products } from '../data/products';
import { useCart } from '../context/CartContext';
import ProductCard from '../components/ProductCard';
import { motion } from 'framer-motion';

export default function ProductDetail() {
  const { id } = useParams();
  const { addToCart } = useCart();
  const [quantity, setQuantity] = useState(1);
  const [added, setAdded] = useState(false);
  const [isLiked, setIsLiked] = useState(false);
  const [selectedImage, setSelectedImage] = useState(0);

  const product = products.find(p => p.id === id);

  if (!product) {
    return (
      <div className="min-h-screen bg-mesh flex items-center justify-center pt-20">
        <div className="text-center">
          <div className="h-20 w-20 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
            <ShoppingCart className="h-10 w-10 text-gray-300" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900">Product Not Found</h2>
          <Link to="/products" className="mt-4 inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors">
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

  const images = [product.image, product.image, product.image];

  return (
    <div className="min-h-screen bg-mesh pt-24">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Breadcrumb */}
        <nav className="flex items-center space-x-2 text-sm text-gray-400 mb-8">
          <Link to="/" className="hover:text-indigo-600 transition-colors">Home</Link>
          <span>/</span>
          <Link to="/products" className="hover:text-indigo-600 transition-colors">Products</Link>
          <span>/</span>
          <Link to={`/products?category=${product.category}`} className="hover:text-indigo-600 transition-colors">{product.category}</Link>
          <span>/</span>
          <span className="text-gray-700 font-medium truncate max-w-[200px]">{product.name}</span>
        </nav>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
          {/* Product Images */}
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            className="space-y-4"
          >
            {/* Main Image */}
            <div className="relative aspect-square rounded-3xl overflow-hidden bg-white border border-gray-100 shadow-xl shadow-gray-200/30">
              <img
                src={images[selectedImage]}
                alt={product.name}
                className="w-full h-full object-cover"
              />
              {product.originalPrice && (
                <span className="absolute top-4 left-4 bg-gradient-to-r from-rose-500 to-pink-500 text-white text-sm font-bold px-4 py-2 rounded-xl shadow-lg shadow-rose-500/30">
                  Save ${(product.originalPrice - product.price).toFixed(2)}
                </span>
              )}
              <button className="absolute top-4 right-4 p-3 bg-white/90 backdrop-blur-sm rounded-xl shadow-lg hover:bg-white transition-all">
                <Share2 className="h-5 w-5 text-gray-600" />
              </button>
            </div>
            
            {/* Thumbnail Strip */}
            <div className="flex space-x-3">
              {images.map((img, i) => (
                <button
                  key={i}
                  onClick={() => setSelectedImage(i)}
                  className={`relative w-20 h-20 rounded-xl overflow-hidden border-2 transition-all ${
                    selectedImage === i ? 'border-indigo-500 shadow-lg shadow-indigo-500/20' : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <img src={img} alt="" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          </motion.div>

          {/* Product Info */}
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 0.1 }}
            className="flex flex-col"
          >
            {/* Tags */}
            <div className="flex items-center space-x-2 mb-3">
              <span className="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-lg">
                {product.category}
              </span>
              {product.inStock ? (
                <span className="px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg flex items-center space-x-1">
                  <span className="w-1.5 h-1.5 bg-emerald-500 rounded-full" />
                  <span>In Stock</span>
                </span>
              ) : (
                <span className="px-3 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-lg">Out of Stock</span>
              )}
            </div>

            <h1 className="text-3xl md:text-4xl font-bold text-gray-900 leading-tight">{product.name}</h1>

            {/* Rating */}
            <div className="flex items-center space-x-3 mt-4">
              <div className="flex items-center space-x-0.5">
                {[...Array(5)].map((_, i) => (
                  <Star
                    key={i}
                    className={`h-5 w-5 ${i < Math.floor(product.rating) ? 'fill-amber-400 text-amber-400' : 'text-gray-200'}`}
                  />
                ))}
              </div>
              <span className="text-sm font-semibold text-gray-900">{product.rating}</span>
              <span className="text-sm text-gray-400">•</span>
              <span className="text-sm text-gray-500">{product.reviews.toLocaleString()} reviews</span>
            </div>

            {/* Price */}
            <div className="flex items-end space-x-3 mt-6">
              <span className="text-4xl font-bold text-gray-900">${product.price.toFixed(2)}</span>
              {product.originalPrice && (
                <span className="text-xl text-gray-400 line-through mb-1">${product.originalPrice.toFixed(2)}</span>
              )}
            </div>

            {/* Description */}
            <p className="text-gray-500 mt-6 leading-relaxed text-base">{product.description}</p>

            {/* Features */}
            <div className="mt-8">
              <h3 className="font-semibold text-gray-900 mb-4">Key Features</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {product.features.map((feature, i) => (
                  <div key={i} className="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl">
                    <div className="h-6 w-6 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                      <Check className="h-3.5 w-3.5 text-emerald-600" />
                    </div>
                    <span className="text-sm text-gray-700 font-medium">{feature}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Quantity & Add to Cart */}
            <div className="mt-8 space-y-4 pt-6 border-t border-gray-100">
              <div className="flex items-center space-x-4">
                <span className="text-sm font-medium text-gray-700">Quantity</span>
                <div className="flex items-center bg-gray-100 rounded-xl overflow-hidden">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="p-3 hover:bg-gray-200 transition-colors"
                  >
                    <Minus className="h-4 w-4" />
                  </button>
                  <span className="px-5 font-semibold text-gray-900">{quantity}</span>
                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="p-3 hover:bg-gray-200 transition-colors"
                  >
                    <Plus className="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div className="flex space-x-3">
                <button
                  onClick={handleAddToCart}
                  disabled={!product.inStock}
                  className={`flex-1 flex items-center justify-center space-x-2.5 py-4 rounded-2xl font-semibold text-base transition-all ${
                    added
                      ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/25'
                      : product.inStock
                      ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-0.5'
                      : 'bg-gray-200 text-gray-500 cursor-not-allowed'
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
                      <span>Add to Cart — ${(product.price * quantity).toFixed(2)}</span>
                    </>
                  )}
                </button>
                <button
                  onClick={() => setIsLiked(!isLiked)}
                  className={`p-4 rounded-2xl border-2 transition-all ${
                    isLiked ? 'border-rose-200 bg-rose-50 text-rose-500' : 'border-gray-200 text-gray-400 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50'
                  }`}
                >
                  <Heart className={`h-5 w-5 ${isLiked ? 'fill-current' : ''}`} />
                </button>
              </div>
            </div>

            {/* Benefits */}
            <div className="mt-8 grid grid-cols-3 gap-4">
              {[
                { icon: Truck, label: 'Free Shipping', sub: 'Orders $50+' },
                { icon: Shield, label: 'Warranty', sub: '2 years' },
                { icon: RotateCcw, label: 'Returns', sub: '30 days' },
              ].map((item, i) => (
                <div key={i} className="text-center p-4 bg-gray-50 rounded-2xl">
                  <item.icon className="h-6 w-6 text-indigo-500 mx-auto" />
                  <p className="text-xs font-semibold text-gray-900 mt-2">{item.label}</p>
                  <p className="text-[11px] text-gray-500">{item.sub}</p>
                </div>
              ))}
            </div>
          </motion.div>
        </div>

        {/* Related Products */}
        {relatedProducts.length > 0 && (
          <section className="mt-20">
            <div className="flex items-center justify-between mb-8">
              <div>
                <h2 className="text-2xl font-bold text-gray-900">You May Also Like</h2>
                <p className="text-gray-500 mt-1">Similar products in {product.category}</p>
              </div>
            </div>
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
