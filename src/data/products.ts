import { Product } from '../types';

export const products: Product[] = [
  {
    id: '1',
    name: 'Wireless Noise-Canceling Headphones',
    description: 'Premium over-ear headphones with active noise cancellation, 30-hour battery life, and crystal-clear audio. Features adaptive sound control and speak-to-chat technology.',
    price: 299.99,
    originalPrice: 349.99,
    image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&h=500&fit=crop',
    category: 'Electronics',
    rating: 4.8,
    reviews: 2456,
    inStock: true,
    features: ['Active Noise Cancellation', '30-hour battery', 'Bluetooth 5.2', 'Touch controls', 'Foldable design']
  },
  {
    id: '2',
    name: 'Ultra-Slim Laptop Stand',
    description: 'Ergonomic aluminum laptop stand with adjustable height settings. Compatible with all laptops 10-17 inches. Improves posture and reduces neck strain.',
    price: 49.99,
    originalPrice: 69.99,
    image: 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=500&h=500&fit=crop',
    category: 'Accessories',
    rating: 4.6,
    reviews: 892,
    inStock: true,
    features: ['Adjustable height', 'Aluminum build', 'Non-slip pads', 'Foldable', 'Heat dissipation']
  },
  {
    id: '3',
    name: 'Smart Fitness Watch Pro',
    description: 'Advanced fitness tracker with GPS, heart rate monitoring, sleep tracking, and 50+ workout modes. Water-resistant up to 50m with 7-day battery life.',
    price: 199.99,
    originalPrice: 249.99,
    image: 'https://images.unsplash.com/photo-1523275335684-37898b6b3327?w=500&h=500&fit=crop',
    category: 'Electronics',
    rating: 4.7,
    reviews: 3201,
    inStock: true,
    features: ['GPS tracking', 'Heart rate monitor', 'Sleep tracking', '50+ workouts', 'Water resistant 50m']
  },
  {
    id: '4',
    name: 'Organic Cotton T-Shirt',
    description: 'Premium organic cotton t-shirt with a relaxed fit. Sustainably sourced and ethically manufactured. Available in multiple colors.',
    price: 34.99,
    image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500&h=500&fit=crop',
    category: 'Clothing',
    rating: 4.5,
    reviews: 567,
    inStock: true,
    features: ['100% organic cotton', 'Relaxed fit', 'Pre-shrunk', 'Eco-friendly dyes', 'Fair trade certified']
  },
  {
    id: '5',
    name: 'Mechanical Gaming Keyboard',
    description: 'RGB mechanical keyboard with Cherry MX switches, per-key lighting, and programmable macros. Aircraft-grade aluminum frame for durability.',
    price: 149.99,
    originalPrice: 179.99,
    image: 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=500&h=500&fit=crop',
    category: 'Electronics',
    rating: 4.9,
    reviews: 1823,
    inStock: true,
    features: ['Cherry MX switches', 'Per-key RGB', 'Macro programmable', 'USB-C', 'Aircraft aluminum']
  },
  {
    id: '6',
    name: 'Minimalist Leather Wallet',
    description: 'Slim RFID-blocking leather wallet with capacity for 8 cards and cash. Handcrafted from genuine full-grain leather.',
    price: 59.99,
    image: 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=500&h=500&fit=crop',
    category: 'Accessories',
    rating: 4.4,
    reviews: 445,
    inStock: true,
    features: ['RFID blocking', 'Full-grain leather', '8 card slots', 'Slim profile', 'Gift box included']
  },
  {
    id: '7',
    name: 'Portable Bluetooth Speaker',
    description: 'Waterproof portable speaker with 360° sound, 20-hour battery, and built-in microphone. Perfect for outdoor adventures.',
    price: 79.99,
    originalPrice: 99.99,
    image: 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&h=500&fit=crop',
    category: 'Electronics',
    rating: 4.6,
    reviews: 1567,
    inStock: true,
    features: ['360° sound', 'IPX7 waterproof', '20-hour battery', 'Built-in mic', 'Pair two speakers']
  },
  {
    id: '8',
    name: 'Running Shoes Ultra Boost',
    description: 'Lightweight running shoes with responsive cushioning and breathable mesh upper. Designed for long-distance comfort and performance.',
    price: 129.99,
    originalPrice: 159.99,
    image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&h=500&fit=crop',
    category: 'Clothing',
    rating: 4.7,
    reviews: 2890,
    inStock: true,
    features: ['Responsive cushioning', 'Breathable mesh', 'Lightweight', 'Rubber outsole', 'Reflective details']
  },
  {
    id: '9',
    name: 'Ceramic Pour-Over Coffee Set',
    description: 'Handcrafted ceramic coffee dripper with matching mug and bamboo stand. Brew the perfect cup every time with precision flow control.',
    price: 44.99,
    image: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=500&h=500&fit=crop',
    category: 'Home',
    rating: 4.8,
    reviews: 734,
    inStock: true,
    features: ['Handcrafted ceramic', 'Precision flow', 'Bamboo stand', 'Includes mug', 'Dishwasher safe']
  },
  {
    id: '10',
    name: 'Wireless Charging Pad',
    description: 'Fast wireless charging pad compatible with all Qi-enabled devices. Sleek design with LED indicator and anti-slip surface.',
    price: 29.99,
    originalPrice: 39.99,
    image: 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=500&h=500&fit=crop',
    category: 'Electronics',
    rating: 4.3,
    reviews: 1234,
    inStock: true,
    features: ['15W fast charging', 'Qi compatible', 'LED indicator', 'Anti-slip surface', 'Ultra-thin']
  },
  {
    id: '11',
    name: 'Yoga Mat Premium',
    description: 'Extra-thick non-slip yoga mat made from natural rubber. Perfect grip, cushioned support, and eco-friendly materials.',
    price: 68.99,
    image: 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=500&h=500&fit=crop',
    category: 'Sports',
    rating: 4.6,
    reviews: 987,
    inStock: true,
    features: ['Natural rubber', 'Non-slip surface', '6mm thick', 'Carrying strap', 'Eco-friendly']
  },
  {
    id: '12',
    name: 'Smart Home Hub',
    description: 'Central smart home controller with voice assistant, touchscreen display, and compatibility with 1000+ smart devices.',
    price: 179.99,
    originalPrice: 219.99,
    image: 'https://images.unsplash.com/photo-1558089687-f282ffcbc126?w=500&h=500&fit=crop',
    category: 'Electronics',
    rating: 4.5,
    reviews: 1678,
    inStock: false,
    features: ['Voice assistant', 'Touchscreen', '1000+ devices', 'Video calls', 'Smart routines']
  }
];

export const categories = ['All', 'Electronics', 'Clothing', 'Accessories', 'Home', 'Sports'];
