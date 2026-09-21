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
      className="group relative bg-white dark:bg-slate-800 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-slate-700 overflow-hidden card-hover w-full min-w-0"
    >
      <Link to={`/product/${product.id}`} className="relative block">
        <div className="relative aspect-square overflow-hidden bg-gray-100 dark:bg-slate-700">
          {!imageLoaded && (
            <div className="absolute inset-0 bg-gray-100 dark:bg-slate-700 shimmer" />
          )}
          <img
            src={product.image}
            alt={product.name}
            onLoad={() => setImageLoaded(true)}
            className={`w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ${imageLoaded ? 'opacity-100' : 'opacity-0'}`}
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
          <div className="absolute top-2 left-2 sm:top-3 sm:left-3 flex flex-col gap-1.5 sm:gap-2">
            {product.originalPrice && (
              <span className="px-2 sm:px-2.5 py-0.5 sm:py-1 bg-rose-500 text-white text-[10px] sm:text-[11px] font-bold rounded-md sm:rounded-lg shadow-lg shadow-rose-500/30">
                -{Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)}%
              </span>
            )}
            {product.rating >= 4.8 && (
              <span className="px-2 sm:px-2.5 py-0.5 sm:py-1 bg-amber-400 text-amber-900 text-[10px] sm:text-[11px] font-bold rounded-md sm:rounded-lg shadow-lg shadow-amber-400/30">
                ★ Top
              </span>
            )}
          </div>
          {!product.inStock && (
            <div className="absolute inset-0 bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm flex items-center justify-center">
              <span className="px-3 sm:px-4 py-1.5 sm:py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs sm:text-sm font-bold rounded-lg sm:rounded-xl">Out of Stock</span>
            </div>
          )}
          <div className="absolute top-2 right-2 sm:top-3 sm:right-3 flex flex-col gap-1.5 sm:gap-2 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all duration-300 hidden sm:flex">
            <button
              onClick={(e) => { e.preventDefault(); setIsLiked(!isLiked); }}
              className={`p-2 sm:p-2.5 rounded-lg sm:rounded-xl shadow-lg transition-all ${
                isLiked ? 'bg-rose-500 text-white' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 hover:text-rose-500'
              }`}
            >
              <Heart className={`h-3.5 w-3.5 sm:h-4 sm:w-4 ${isLiked ? 'fill-current' : ''}`} />
            </button>
          </div>
          <div className="absolute bottom-2 left-2 right-2 sm:bottom-3 sm:left-3 sm:right-3 sm:opacity-0 sm:group-hover:opacity-100 sm:translate-y-2 sm:group-hover:translate-y-0 transition-all duration-300">
            <button
              onClick={(e) => { e.preventDefault(); if (product.inStock) addToCart(product); }}
              disabled={!product.inStock}
              className="w-full flex items-center justify-center gap-1.5 sm:gap-2 py-2 sm:py-2.5 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm text-gray-900 dark:text-white font-semibold text-xs sm:text-sm rounded-lg sm:rounded-xl hover:bg-white dark:hover:bg-slate-700 shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ShoppingCart className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
              <span>Quick Add</span>
            </button>
          </div>
        </div>
      </Link>
      <div className="p-3 sm:p-4 pt-2.5 sm:pt-3.5">
        <div className="flex items-center justify-between mb-1.5 sm:mb-2 gap-2">
          <span className="text-[10px] sm:text-[11px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider truncate">{product.category}</span>
          <div className="flex items-center gap-0.5 sm:gap-1 shrink-0">
            <Star className="h-3 w-3 sm:h-3.5 sm:w-3.5 fill-amber-400 text-amber-400 shrink-0" />
            <span className="text-[11px] sm:text-xs font-semibold text-gray-700 dark:text-slate-200">{product.rating}</span>
            <span className="text-[10px] sm:text-xs text-gray-400 dark:text-slate-500 hidden sm:inline">({product.reviews > 999 ? `${(product.reviews / 1000).toFixed(1)}k` : product.reviews})</span>
          </div>
        </div>
        <Link to={`/product/${product.id}`}>
          <h3 className="font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors text-xs sm:text-sm leading-snug line-clamp-2">
            {product.name}
          </h3>
        </Link>
        <div className="flex items-center gap-1.5 sm:gap-2 mt-2 sm:mt-3 flex-wrap">
          <span className="text-base sm:text-lg font-bold text-gray-900 dark:text-white">${product.price.toFixed(2)}</span>
          {product.originalPrice && (
            <span className="text-xs sm:text-sm text-gray-400 dark:text-slate-500 line-through">${product.originalPrice.toFixed(2)}</span>
          )}
        </div>
      </div>
    </motion.div>
  );
}
