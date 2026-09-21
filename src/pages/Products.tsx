import { useState, useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import { SlidersHorizontal, Grid3X3, List, Search, X } from 'lucide-react';
import ProductCard from '../components/ProductCard';
import { products, categories } from '../data/products';
import { motion, AnimatePresence } from 'framer-motion';

export default function Products() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [sortBy, setSortBy] = useState('featured');
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

  const activeCategory = searchParams.get('category') || 'All';
  const searchQuery = searchParams.get('search') || '';

  const filteredProducts = useMemo(() => {
    let result = [...products];

    if (activeCategory !== 'All') {
      result = result.filter(p => p.category === activeCategory);
    }

    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      result = result.filter(p =>
        p.name.toLowerCase().includes(query) ||
        p.description.toLowerCase().includes(query) ||
        p.category.toLowerCase().includes(query)
      );
    }

    switch (sortBy) {
      case 'price-low':
        result.sort((a, b) => a.price - b.price);
        break;
      case 'price-high':
        result.sort((a, b) => b.price - a.price);
        break;
      case 'rating':
        result.sort((a, b) => b.rating - a.rating);
        break;
      case 'newest':
        result.sort((a, b) => parseInt(b.id) - parseInt(a.id));
        break;
    }

    return result;
  }, [activeCategory, searchQuery, sortBy]);

  const setCategory = (category: string) => {
    const params = new URLSearchParams(searchParams);
    if (category === 'All') {
      params.delete('category');
    } else {
      params.set('category', category);
    }
    setSearchParams(params);
  };

  return (
    <div className="min-h-screen bg-mesh pt-20 sm:pt-24 w-full overflow-x-hidden">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        {/* Header */}
        <motion.div
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-6 sm:mb-8"
        >
          <div className="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-gray-400 mb-2 sm:mb-3 flex-wrap">
            <span>Home</span>
            <span>/</span>
            <span className="text-gray-900 font-medium break-all">
              {searchQuery ? 'Search Results' : activeCategory === 'All' ? 'All Products' : activeCategory}
            </span>
          </div>
          <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 break-words">
            {searchQuery ? (
              <>Results for "<span className="text-gradient break-all">{searchQuery}</span>"</>
            ) : (
              activeCategory === 'All' ? 'All Products' : activeCategory
            )}
          </h1>
          <p className="text-gray-500 mt-1 sm:mt-2 text-sm sm:text-base">{filteredProducts.length} products found</p>
        </motion.div>

        {/* Filters Bar */}
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="bg-white rounded-2xl shadow-sm shadow-gray-200/50 border border-gray-100 p-3 sm:p-4 mb-6 sm:mb-8"
        >
          <div className="flex flex-col gap-3 sm:gap-4">
            {/* Categories */}
            <div className="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-hide -mx-1 px-1">
              <SlidersHorizontal className="h-4 w-4 text-gray-400 shrink-0 mr-0.5 sm:mr-1" />
              {categories.map((cat) => (
                <button
                  key={cat}
                  onClick={() => setCategory(cat)}
                  className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-xs sm:text-sm font-medium whitespace-nowrap transition-all shrink-0 ${
                    activeCategory === cat
                      ? 'bg-gray-900 text-white shadow-md'
                      : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                  }`}
                >
                  {cat}
                </button>
              ))}
            </div>

            {/* Sort & View */}
            <div className="flex items-center gap-2 sm:gap-3">
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                className="flex-1 sm:flex-none px-3 sm:px-4 py-2 sm:py-2.5 bg-gray-100 border-0 rounded-lg sm:rounded-xl text-xs sm:text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer min-w-0"
              >
                <option value="featured">Featured</option>
                <option value="price-low">Price: Low → High</option>
                <option value="price-high">Price: High → Low</option>
                <option value="rating">Top Rated</option>
                <option value="newest">Newest</option>
              </select>
              <div className="hidden sm:flex items-center bg-gray-100 rounded-xl p-1 shrink-0">
                <button
                  onClick={() => setViewMode('grid')}
                  className={`p-2 rounded-lg transition-all ${viewMode === 'grid' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-400 hover:text-gray-600'}`}
                >
                  <Grid3X3 className="h-4 w-4" />
                </button>
                <button
                  onClick={() => setViewMode('list')}
                  className={`p-2 rounded-lg transition-all ${viewMode === 'list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-400 hover:text-gray-600'}`}
                >
                  <List className="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </motion.div>

        {/* Active Filters */}
        {(searchQuery || activeCategory !== 'All') && (
          <div className="flex items-center flex-wrap gap-2 mb-4 sm:mb-6">
            <span className="text-xs sm:text-sm text-gray-500">Filters:</span>
            {searchQuery && (
              <button
                onClick={() => {
                  const params = new URLSearchParams(searchParams);
                  params.delete('search');
                  setSearchParams(params);
                }}
                className="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1 sm:py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs sm:text-sm font-medium hover:bg-indigo-100 transition-colors"
              >
                <Search className="h-3 w-3" />
                <span className="truncate max-w-[120px]">{searchQuery}</span>
                <X className="h-3 w-3 shrink-0" />
              </button>
            )}
            {activeCategory !== 'All' && (
              <button
                onClick={() => setCategory('All')}
                className="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1 sm:py-1.5 bg-purple-50 text-purple-700 rounded-lg text-xs sm:text-sm font-medium hover:bg-purple-100 transition-colors"
              >
                <span>{activeCategory}</span>
                <X className="h-3 w-3" />
              </button>
            )}
            <button
              onClick={() => setSearchParams({})}
              className="text-xs sm:text-sm text-gray-500 hover:text-gray-700 underline"
            >
              Clear all
            </button>
          </div>
        )}

        {/* Products Grid */}
        <AnimatePresence mode="wait">
          {filteredProducts.length > 0 ? (
            <motion.div
              key={`${activeCategory}-${sortBy}-${viewMode}`}
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6"
            >
              {filteredProducts.map((product, index) => (
                <ProductCard key={product.id} product={product} index={index} />
              ))}
            </motion.div>
          ) : (
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              className="text-center py-12 sm:py-20 px-4"
            >
              <div className="h-16 w-16 sm:h-20 sm:w-20 bg-gray-100 rounded-2xl sm:rounded-3xl flex items-center justify-center mx-auto mb-4">
                <Search className="h-8 w-8 sm:h-10 sm:w-10 text-gray-300" />
              </div>
              <p className="text-gray-900 text-lg sm:text-xl font-semibold">No products found</p>
              <p className="text-gray-500 mt-2 text-sm sm:text-base">Try adjusting your filters or search terms</p>
              <button
                onClick={() => setSearchParams({})}
                className="mt-6 px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors"
              >
                Clear Filters
              </button>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </div>
  );
}
