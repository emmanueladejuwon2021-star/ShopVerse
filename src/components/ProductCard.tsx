import { Link } from 'react-router-dom';
import { ShoppingCart, Star, Heart } from 'lucide-react';
import { Product } from '../types';
import { useCart } from '../context/CartContext';
import { motion } from 'framer-motion';
import { useState } from 'react';

interface ProductCardProps {
  product: Product;
  index?: number;
}

export default function ProductCard({ product, index = 0 }: ProductCardProps) {
  const { addToCart } = useCart();
  const [isLiked, setIsLiked] = useState(false);
  const [imageLoaded, setImageLoaded] = useState(false);

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: index * 0.06, duration: 0.4 }}
      className="group relative bg-white rounded-3xl border border-gray-100 overflow-hidden card-hover"
    >
      {/* Image Section */}
      <Link to={`/product/${product.id}`} className="relative block">
        <div className="relative aspect-square overflow-hidden bg-gray-100">
          {!imageLoaded && (
            <div className="absolute inset-0 bg-gray-100 shimmer" />
          )}
          <img
            src={product.image}
            alt={product.name}
            onLoad={() => setImageLoaded(true)}
            className={`w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ${imageLoaded ? 'opacity-100' : 'opacity-0'}`}
          />
          
          {/* Overlay on hover */}
          <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
          
          {/* Badges */}
          <div className="absolute top-3 left-3 flex flex-col gap-2">
            {product.originalPrice && (
              <span className="px-2.5 py-1 bg-rose-500 text-white text-[11px] font-bold rounded-lg shadow-lg shadow-rose-500/30">
                -{Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)}%
              </span>
            )}
            {product.rating >= 4.8 && (
              <span className="px-2.5 py-1 bg-amber-400 text-amber-900 text-[11px] font-bold rounded-lg shadow-lg shadow-amber-400/30">
                ★ Top Rated
              </span>
            )}
          </div>

          {!product.inStock && (
            <div className="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center">
              <span className="px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-xl">Out of Stock</span>
            </div>
          )}

          {/* Quick actions */}
          <div className="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all duration-300">
            <button
              onClick={(e) => { e.preventDefault(); setIsLiked(!isLiked); }}
              className={`p-2.5 rounded-xl shadow-lg transition-all ${
                isLiked ? 'bg-rose-500 text-white' : 'bg-white text-gray-600 hover:text-rose-500'
              }`}
            >
              <Heart className={`h-4 w-4 ${isLiked ? 'fill-current' : ''}`} />
            </button>
          </div>

          {/* Quick Add Button */}
          <div className="absolute bottom-3 left-3 right-3 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300">
            <button
              onClick={(e) => { e.preventDefault(); if (product.inStock) addToCart(product); }}
              disabled={!product.inStock}
              className="w-full flex items-center justify-center space-x-2 py-2.5 bg-white/95 backdrop-blur-sm text-gray-900 font-semibold text-sm rounded-xl hover:bg-white shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ShoppingCart className="h-4 w-4" />
              <span>Quick Add</span>
            </button>
          </div>
        </div>
      </Link>

      {/* Info Section */}
      <div className="p-4 pt-3.5">
        {/* Category & Rating */}
        <div className="flex items-center justify-between mb-2">
          <span className="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">{product.category}</span>
          <div className="flex items-center space-x-1">
            <Star className="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
            <span className="text-xs font-semibold text-gray-700">{product.rating}</span>
            <span className="text-xs text-gray-400">({product.reviews > 999 ? `${(product.reviews / 1000).toFixed(1)}k` : product.reviews})</span>
          </div>
        </div>

        {/* Name */}
        <Link to={`/product/${product.id}`}>
          <h3 className="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors text-sm leading-snug line-clamp-2">
            {product.name}
          </h3>
        </Link>

        {/* Price */}
        <div className="flex items-center space-x-2 mt-3">
          <span className="text-lg font-bold text-gray-900">${product.price.toFixed(2)}</span>
          {product.originalPrice && (
            <span className="text-sm text-gray-400 line-through">${product.originalPrice.toFixed(2)}</span>
          )}
        </div>
      </div>
    </motion.div>
  );
}
