import { useState, useRef, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import {
  User, Mail, Lock, MapPin, CreditCard, Bell, Shield, Palette,
  Camera, Check, X, Eye, EyeOff, Plus, Trash2, Edit2, Phone,
  Calendar, Globe, DollarSign, LogOut, AlertTriangle, ChevronRight,
  Smartphone, Package, Heart, Settings as SettingsIcon
} from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import { Address, PaymentMethod, NotificationPreferences } from '../types';
import { motion, AnimatePresence } from 'framer-motion';

type Section = 'profile' | 'security' | 'addresses' | 'payment' | 'notifications' | 'preferences';

const sections: { id: Section; label: string; icon: any; description: string }[] = [
  { id: 'profile', label: 'Profile', icon: User, description: 'Personal information' },
  { id: 'security', label: 'Security', icon: Shield, description: 'Password & 2FA' },
  { id: 'addresses', label: 'Addresses', icon: MapPin, description: 'Shipping addresses' },
  { id: 'payment', label: 'Payment', icon: CreditCard, description: 'Cards & methods' },
  { id: 'notifications', label: 'Notifications', icon: Bell, description: 'Email & alerts' },
  { id: 'preferences', label: 'Preferences', icon: Palette, description: 'Language & theme' },
];

export default function AccountSettings() {
  const { user, isAuthenticated, logout, updateProfile } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [activeSection, setActiveSection] = useState<Section>('profile');
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  // Redirect if not authenticated
  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login');
    }
  }, [isAuthenticated, navigate]);

  // Parse section from URL hash
  useEffect(() => {
    const hash = location.hash.replace('#', '') as Section;
    if (hash && sections.some(s => s.id === hash)) {
      setActiveSection(hash);
    }
  }, [location]);

  const showToast = (message: string, type: 'success' | 'error' = 'success') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  if (!isAuthenticated || !user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-mesh pt-20 sm:pt-24 w-full overflow-x-hidden">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        {/* Header */}
        <div className="mb-6 sm:mb-8">
          <div className="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-gray-400 dark:text-slate-500 mb-2 sm:mb-3 flex-wrap">
            <Link to="/" className="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors shrink-0">Home</Link>
            <span className="shrink-0">/</span>
            <span className="text-gray-900 dark:text-white font-medium">Account Settings</span>
          </div>
          <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Account Settings</h1>
          <p className="text-gray-500 dark:text-slate-400 mt-1 sm:mt-2 text-sm sm:text-base">Manage your account preferences and personal information</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
          {/* Sidebar Navigation */}
          <aside className="lg:col-span-3">
            {/* Mobile: Horizontal Tabs */}
            <div className="lg:hidden overflow-x-auto scrollbar-hide -mx-4 px-4 pb-2">
              <div className="flex gap-2 min-w-max">
                {sections.map((section) => {
                  const Icon = section.icon;
                  return (
                    <button
                      key={section.id}
                      onClick={() => setActiveSection(section.id)}
                      className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium whitespace-nowrap transition-all ${
                        activeSection === section.id
                          ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-md'
                          : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-slate-700 hover:border-gray-300 dark:hover:border-slate-600'
                      }`}
                    >
                      <Icon className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                      {section.label}
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Desktop: Vertical Sidebar */}
            <nav className="hidden lg:block bg-white dark:bg-slate-800 rounded-2xl shadow-sm shadow-gray-200/50 dark:shadow-black/20 border border-gray-100 dark:border-slate-700 p-2 sticky top-24">
              {/* User Card */}
              <div className="p-3 mb-2 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-xl">
                <div className="flex items-center gap-3">
                  <div className="h-11 w-11 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                    <span className="text-white text-sm font-bold">{user.name?.charAt(0).toUpperCase()}</span>
                  </div>
                  <div className="min-w-0">
                    <p className="text-sm font-semibold text-gray-900 dark:text-white truncate">{user.name}</p>
                    <p className="text-xs text-gray-500 dark:text-slate-400 truncate">{user.email}</p>
                  </div>
                </div>
              </div>

              {sections.map((section) => {
                const Icon = section.icon;
                const isActive = activeSection === section.id;
                return (
                  <button
                    key={section.id}
                    onClick={() => setActiveSection(section.id)}
                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-all mb-0.5 ${
                      isActive
                        ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/20'
                        : 'text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50'
                    }`}
                  >
                    <Icon className={`h-4 w-4 shrink-0 ${isActive ? 'text-white' : 'text-gray-400 dark:text-slate-500'}`} />
                    <div className="flex-1 min-w-0">
                      <p className={`text-sm font-medium ${isActive ? 'text-white' : ''}`}>{section.label}</p>
                      <p className={`text-[10px] ${isActive ? 'text-white/70' : 'text-gray-400 dark:text-slate-500'}`}>{section.description}</p>
                    </div>
                    {isActive && <ChevronRight className="h-4 w-4 text-white/70 shrink-0" />}
                  </button>
                );
              })}

              <div className="border-t border-gray-100 dark:border-slate-700 my-2 pt-2">
                <button
                  onClick={() => { logout(); navigate('/'); }}
                  className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all"
                >
                  <LogOut className="h-4 w-4 shrink-0" />
                  <span className="text-sm font-medium">Sign Out</span>
                </button>
              </div>
            </nav>
          </aside>

          {/* Main Content */}
          <main className="lg:col-span-9 min-w-0">
            <AnimatePresence mode="wait">
              <motion.div
                key={activeSection}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
                transition={{ duration: 0.2 }}
              >
                {activeSection === 'profile' && <ProfileSection user={user} updateProfile={updateProfile} showToast={showToast} />}
                {activeSection === 'security' && <SecuritySection showToast={showToast} />}
                {activeSection === 'addresses' && <AddressesSection showToast={showToast} />}
                {activeSection === 'payment' && <PaymentSection showToast={showToast} />}
                {activeSection === 'notifications' && <NotificationsSection showToast={showToast} />}
                {activeSection === 'preferences' && <PreferencesSection showToast={showToast} />}
              </motion.div>
            </AnimatePresence>
          </main>
        </div>
      </div>

      {/* Toast Notification */}
      <AnimatePresence>
        {toast && (
          <motion.div
            initial={{ opacity: 0, y: 50, scale: 0.9 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 20, scale: 0.9 }}
            className="fixed bottom-6 left-1/2 -translate-x-1/2 z-[100]"
          >
            <div className={`flex items-center gap-2 px-4 sm:px-5 py-3 rounded-2xl shadow-xl ${
              toast.type === 'success'
                ? 'bg-emerald-500 text-white shadow-emerald-500/25'
                : 'bg-red-500 text-white shadow-red-500/25'
            }`}>
              {toast.type === 'success' ? <Check className="h-4 w-4 sm:h-5 sm:w-5" /> : <X className="h-4 w-4 sm:h-5 sm:w-5" />}
              <span className="text-xs sm:text-sm font-medium">{toast.message}</span>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

// ============== PROFILE SECTION ==============
function ProfileSection({ user, updateProfile, showToast }: any) {
  const [editing, setEditing] = useState(false);
  const [name, setName] = useState(user.name || '');
  const [email, setEmail] = useState(user.email || '');
  const [phone, setPhone] = useState(user.phone || '');
  const [bio, setBio] = useState(user.bio || '');
  const [dateOfBirth, setDateOfBirth] = useState(user.dateOfBirth || '');
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);

  const handleSave = () => {
    updateProfile({ name, email, phone, bio, dateOfBirth });
    setEditing(false);
    showToast('Profile updated successfully');
  };

  const handleAvatarChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => setAvatarPreview(reader.result as string);
      reader.readAsDataURL(file);
    }
  };

  return (
    <div className="space-y-5 sm:space-y-6">
      {/* Avatar Card */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div className="h-24 sm:h-32 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 relative">
          <div className="absolute inset-0" style={{ backgroundImage: 'radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0)', backgroundSize: '24px 24px' }} />
        </div>
        <div className="px-4 sm:px-6 pb-5 sm:pb-6 -mt-10 sm:-mt-12">
          <div className="flex flex-col sm:flex-row sm:items-end gap-3 sm:gap-4">
            <div className="relative inline-block">
              <div className="h-20 w-20 sm:h-24 sm:w-24 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center shadow-xl shadow-indigo-500/25 border-4 border-white">
                {avatarPreview ? (
                  <img src={avatarPreview} alt="" className="w-full h-full object-cover rounded-[14px] sm:rounded-[20px]" />
                ) : (
                  <span className="text-white text-2xl sm:text-3xl font-bold">{user.name?.charAt(0).toUpperCase()}</span>
                )}
              </div>
              <button
                onClick={() => fileInputRef.current?.click()}
                className="absolute -bottom-1 -right-1 sm:-bottom-2 sm:-right-2 h-7 w-7 sm:h-8 sm:w-8 bg-white rounded-xl shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors border border-gray-100"
              >
                <Camera className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-gray-600" />
              </button>
              <input
                ref={fileInputRef}
                type="file"
                accept="image/*"
                onChange={handleAvatarChange}
                className="hidden"
              />
            </div>
            <div className="flex-1 min-w-0 sm:pb-1">
              <h2 className="text-lg sm:text-xl font-bold text-gray-900 truncate">{user.name}</h2>
              <p className="text-xs sm:text-sm text-gray-500 truncate">{user.email}</p>
              <p className="text-xs text-gray-400 mt-0.5">Member since {new Date(user.joinedDate || Date.now()).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</p>
            </div>
            {!editing && (
              <button
                onClick={() => setEditing(true)}
                className="sm:self-end px-4 py-2 bg-gray-900 text-white text-xs sm:text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors shrink-0"
              >
                Edit Profile
              </button>
            )}
          </div>
        </div>
      </div>

      {/* Profile Form */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
        <h3 className="text-base sm:text-lg font-bold text-gray-900 mb-4 sm:mb-6">Personal Information</h3>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Full Name</label>
            <div className="relative">
              <User className="absolute left-3 sm:left-4 top-3 sm:top-3.5 h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
              <input
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                disabled={!editing}
                className="w-full pl-10 sm:pl-12 pr-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all disabled:opacity-70"
              />
            </div>
          </div>
          <div>
            <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Email Address</label>
            <div className="relative">
              <Mail className="absolute left-3 sm:left-4 top-3 sm:top-3.5 h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                disabled={!editing}
                className="w-full pl-10 sm:pl-12 pr-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all disabled:opacity-70"
              />
            </div>
          </div>
          <div>
            <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Phone Number</label>
            <div className="relative">
              <Phone className="absolute left-3 sm:left-4 top-3 sm:top-3.5 h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
              <input
                type="tel"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                disabled={!editing}
                placeholder="+1 (555) 000-0000"
                className="w-full pl-10 sm:pl-12 pr-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all disabled:opacity-70"
              />
            </div>
          </div>
          <div>
            <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Date of Birth</label>
            <div className="relative">
              <Calendar className="absolute left-3 sm:left-4 top-3 sm:top-3.5 h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
              <input
                type="date"
                value={dateOfBirth}
                onChange={(e) => setDateOfBirth(e.target.value)}
                disabled={!editing}
                className="w-full pl-10 sm:pl-12 pr-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all disabled:opacity-70"
              />
            </div>
          </div>
          <div className="sm:col-span-2">
            <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Bio</label>
            <textarea
              value={bio}
              onChange={(e) => setBio(e.target.value)}
              disabled={!editing}
              rows={3}
              placeholder="Tell us about yourself..."
              className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all disabled:opacity-70 resize-none"
            />
          </div>
        </div>

        {editing && (
          <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-5 sm:mt-6 pt-5 sm:pt-6 border-t border-gray-100">
            <button
              onClick={handleSave}
              className="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 rounded-xl font-semibold text-sm hover:shadow-lg hover:shadow-indigo-500/25 transition-all"
            >
              <Check className="h-4 w-4" />
              Save Changes
            </button>
            <button
              onClick={() => { setEditing(false); setName(user.name); setEmail(user.email); }}
              className="flex-1 sm:flex-none px-6 py-3 border border-gray-200 text-gray-700 rounded-xl font-medium text-sm hover:bg-gray-50 transition-all"
            >
              Cancel
            </button>
          </div>
        )}
      </div>

      {/* Quick Stats */}
      <div className="grid grid-cols-3 gap-3 sm:gap-4">
        {[
          { icon: Package, label: 'Orders', value: '12', color: 'from-blue-500 to-cyan-500' },
          { icon: Heart, label: 'Wishlist', value: '8', color: 'from-rose-500 to-pink-500' },
          { icon: MapPin, label: 'Addresses', value: '3', color: 'from-emerald-500 to-green-500' },
        ].map((stat, i) => (
          <div key={i} className="bg-white rounded-2xl shadow-sm shadow-gray-200/50 border border-gray-100 p-3 sm:p-4 text-center">
            <div className={`inline-flex h-9 w-9 sm:h-10 sm:w-10 bg-gradient-to-br ${stat.color} rounded-xl items-center justify-center shadow-lg mb-2`}>
              <stat.icon className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
            </div>
            <p className="text-lg sm:text-2xl font-bold text-gray-900">{stat.value}</p>
            <p className="text-[10px] sm:text-xs text-gray-500">{stat.label}</p>
          </div>
        ))}
      </div>
    </div>
  );
}

// ============== SECURITY SECTION ==============
function SecuritySection({ showToast }: { showToast: (m: string, t?: 'success' | 'error') => void }) {
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPasswords, setShowPasswords] = useState(false);
  const [twoFAEnabled, setTwoFAEnabled] = useState(false);

  const passwordStrength = (() => {
    let score = 0;
    if (newPassword.length >= 8) score++;
    if (/[A-Z]/.test(newPassword)) score++;
    if (/[0-9]/.test(newPassword)) score++;
    if (/[^A-Za-z0-9]/.test(newPassword)) score++;
    return score;
  })();

  const strengthColors = ['bg-gray-200', 'bg-red-400', 'bg-amber-400', 'bg-yellow-400', 'bg-emerald-400'];
  const strengthLabels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

  const handleChangePassword = () => {
    if (!currentPassword || !newPassword || !confirmPassword) {
      showToast('Please fill in all fields', 'error');
      return;
    }
    if (newPassword !== confirmPassword) {
      showToast('Passwords do not match', 'error');
      return;
    }
    if (newPassword.length < 8) {
      showToast('Password must be at least 8 characters', 'error');
      return;
    }
    setCurrentPassword('');
    setNewPassword('');
    setConfirmPassword('');
    showToast('Password changed successfully');
  };

  return (
    <div className="space-y-5 sm:space-y-6">
      {/* Change Password */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
        <div className="flex items-center gap-3 mb-4 sm:mb-6">
          <div className="h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0">
            <Lock className="h-5 w-5 text-white" />
          </div>
          <div>
            <h3 className="text-base sm:text-lg font-bold text-gray-900">Change Password</h3>
            <p className="text-xs sm:text-sm text-gray-500">Update your password to keep your account secure</p>
          </div>
        </div>

        <div className="space-y-3 sm:space-y-4">
          <div>
            <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Current Password</label>
            <div className="relative">
              <Lock className="absolute left-3 sm:left-4 top-3 sm:top-3.5 h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
              <input
                type={showPasswords ? 'text' : 'password'}
                value={currentPassword}
                onChange={(e) => setCurrentPassword(e.target.value)}
                placeholder="••••••••"
                className="w-full pl-10 sm:pl-12 pr-10 sm:pr-12 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
              />
              <button
                type="button"
                onClick={() => setShowPasswords(!showPasswords)}
                className="absolute right-3 sm:right-4 top-3 sm:top-3.5 text-gray-400 hover:text-gray-600"
              >
                {showPasswords ? <EyeOff className="h-4 w-4 sm:h-5 sm:w-5" /> : <Eye className="h-4 w-4 sm:h-5 sm:w-5" />}
              </button>
            </div>
          </div>
          <div>
            <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">New Password</label>
            <input
              type={showPasswords ? 'text' : 'password'}
              value={newPassword}
              onChange={(e) => setNewPassword(e.target.value)}
              placeholder="At least 8 characters"
              className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
            />
            {newPassword && (
              <div className="mt-2">
                <div className="flex gap-1 mb-1">
                  {[1, 2, 3, 4].map(i => (
                    <div key={i} className={`h-1 flex-1 rounded-full transition-all ${i <= passwordStrength ? strengthColors[passwordStrength] : 'bg-gray-200'}`} />
                  ))}
                </div>
                <p className="text-[10px] sm:text-xs text-gray-500">Password strength: <span className="font-medium">{strengthLabels[passwordStrength]}</span></p>
              </div>
            )}
          </div>
          <div>
            <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Confirm New Password</label>
            <input
              type={showPasswords ? 'text' : 'password'}
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              placeholder="Re-enter new password"
              className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
            />
          </div>
          <button
            onClick={handleChangePassword}
            className="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 rounded-xl font-semibold text-sm hover:shadow-lg hover:shadow-indigo-500/25 transition-all"
          >
            Update Password
          </button>
        </div>
      </div>

      {/* Two-Factor Authentication */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
        <div className="flex items-start gap-3 sm:gap-4">
          <div className="h-10 w-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 shrink-0">
            <Smartphone className="h-5 w-5 text-white" />
          </div>
          <div className="flex-1 min-w-0">
            <h3 className="text-base sm:text-lg font-bold text-gray-900">Two-Factor Authentication</h3>
            <p className="text-xs sm:text-sm text-gray-500 mt-0.5">Add an extra layer of security to your account</p>
            <div className="flex items-center justify-between mt-4 gap-3">
              <div className="min-w-0">
                <p className="text-xs sm:text-sm font-medium text-gray-900">
                  {twoFAEnabled ? 'Enabled' : 'Disabled'}
                </p>
                <p className="text-[10px] sm:text-xs text-gray-500">
                  {twoFAEnabled ? 'Your account is protected with 2FA' : 'Enable to protect your account'}
                </p>
              </div>
              <button
                onClick={() => { setTwoFAEnabled(!twoFAEnabled); showToast(twoFAEnabled ? '2FA disabled' : '2FA enabled'); }}
                className={`relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors ${
                  twoFAEnabled ? 'bg-emerald-500' : 'bg-gray-200'
                }`}
              >
                <span className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform ${
                  twoFAEnabled ? 'translate-x-6' : 'translate-x-1'
                }`} />
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Active Sessions */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
        <h3 className="text-base sm:text-lg font-bold text-gray-900 mb-4">Active Sessions</h3>
        <div className="space-y-3">
          {[
            { device: 'MacBook Pro', location: 'San Francisco, CA', current: true, time: 'Now' },
            { device: 'iPhone 15', location: 'San Francisco, CA', current: false, time: '2 hours ago' },
            { device: 'iPad Air', location: 'New York, NY', current: false, time: '3 days ago' },
          ].map((session, i) => (
            <div key={i} className="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
              <div className="h-9 w-9 bg-white rounded-lg flex items-center justify-center shadow-sm shrink-0">
                <Smartphone className="h-4 w-4 text-gray-600" />
              </div>
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 flex-wrap">
                  <p className="text-xs sm:text-sm font-medium text-gray-900">{session.device}</p>
                  {session.current && (
                    <span className="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[9px] sm:text-[10px] font-bold rounded">CURRENT</span>
                  )}
                </div>
                <p className="text-[10px] sm:text-xs text-gray-500 truncate">{session.location} • {session.time}</p>
              </div>
              {!session.current && (
                <button className="text-[10px] sm:text-xs text-red-500 hover:text-red-600 font-medium shrink-0">
                  Revoke
                </button>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ============== ADDRESSES SECTION ==============
function AddressesSection({ showToast }: { showToast: (m: string, t?: 'success' | 'error') => void }) {
  const [addresses, setAddresses] = useState<Address[]>([
    { id: '1', fullName: 'John Doe', street: '123 Market St', city: 'San Francisco', state: 'CA', zipCode: '94103', country: 'United States', phone: '+1 555-0100', isDefault: true, label: 'Home' },
    { id: '2', fullName: 'John Doe', street: '456 Office Blvd, Suite 200', city: 'Palo Alto', state: 'CA', zipCode: '94301', country: 'United States', phone: '+1 555-0101', isDefault: false, label: 'Work' },
  ]);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [formData, setFormData] = useState<Address>({
    fullName: '', street: '', city: '', state: '', zipCode: '', country: 'United States', phone: '', label: 'Home'
  });

  const handleSave = () => {
    if (!formData.fullName || !formData.street || !formData.city) {
      showToast('Please fill required fields', 'error');
      return;
    }
    if (editingId) {
      setAddresses(addresses.map(a => a.id === editingId ? { ...formData, id: editingId } : a));
      showToast('Address updated');
    } else {
      setAddresses([...addresses, { ...formData, id: Date.now().toString() }]);
      showToast('Address added');
    }
    resetForm();
  };

  const handleDelete = (id: string) => {
    setAddresses(addresses.filter(a => a.id !== id));
    showToast('Address removed');
  };

  const handleEdit = (addr: Address) => {
    setFormData(addr);
    setEditingId(addr.id || null);
    setShowForm(true);
  };

  const resetForm = () => {
    setFormData({ fullName: '', street: '', city: '', state: '', zipCode: '', country: 'United States', phone: '', label: 'Home' });
    setShowForm(false);
    setEditingId(null);
  };

  return (
    <div className="space-y-5 sm:space-y-6">
      <div className="flex items-center justify-between gap-3">
        <div>
          <h3 className="text-base sm:text-lg font-bold text-gray-900">Saved Addresses</h3>
          <p className="text-xs sm:text-sm text-gray-500">{addresses.length} address{addresses.length !== 1 ? 'es' : ''} saved</p>
        </div>
        {!showForm && (
          <button
            onClick={() => setShowForm(true)}
            className="flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gray-900 text-white text-xs sm:text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors shrink-0"
          >
            <Plus className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
            <span className="hidden sm:inline">Add Address</span>
            <span className="sm:hidden">Add</span>
          </button>
        )}
      </div>

      {/* Address Form */}
      <AnimatePresence>
        {showForm && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="overflow-hidden"
          >
            <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
              <h4 className="text-sm sm:text-base font-bold text-gray-900 mb-4">
                {editingId ? 'Edit Address' : 'New Address'}
              </h4>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                  <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Label</label>
                  <div className="flex gap-2">
                    {['Home', 'Work', 'Other'].map(label => (
                      <button
                        key={label}
                        onClick={() => setFormData({ ...formData, label })}
                        className={`flex-1 py-2 text-xs sm:text-sm font-medium rounded-lg transition-all ${
                          formData.label === label
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        }`}
                      >
                        {label}
                      </button>
                    ))}
                  </div>
                </div>
                <div>
                  <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Phone</label>
                  <input
                    type="tel"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    placeholder="+1 (555) 000-0000"
                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                  />
                </div>
                <div className="sm:col-span-2">
                  <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Full Name *</label>
                  <input
                    type="text"
                    value={formData.fullName}
                    onChange={(e) => setFormData({ ...formData, fullName: e.target.value })}
                    placeholder="John Doe"
                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                  />
                </div>
                <div className="sm:col-span-2">
                  <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Street Address *</label>
                  <input
                    type="text"
                    value={formData.street}
                    onChange={(e) => setFormData({ ...formData, street: e.target.value })}
                    placeholder="123 Main St"
                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                  />
                </div>
                <div>
                  <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">City *</label>
                  <input
                    type="text"
                    value={formData.city}
                    onChange={(e) => setFormData({ ...formData, city: e.target.value })}
                    placeholder="San Francisco"
                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                  />
                </div>
                <div>
                  <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">State</label>
                  <input
                    type="text"
                    value={formData.state}
                    onChange={(e) => setFormData({ ...formData, state: e.target.value })}
                    placeholder="CA"
                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                  />
                </div>
                <div>
                  <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">ZIP Code</label>
                  <input
                    type="text"
                    value={formData.zipCode}
                    onChange={(e) => setFormData({ ...formData, zipCode: e.target.value })}
                    placeholder="94103"
                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                  />
                </div>
                <div>
                  <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Country</label>
                  <input
                    type="text"
                    value={formData.country}
                    onChange={(e) => setFormData({ ...formData, country: e.target.value })}
                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 focus:bg-white transition-all"
                  />
                </div>
              </div>
              <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-5 sm:mt-6 pt-5 sm:pt-6 border-t border-gray-100">
                <button
                  onClick={handleSave}
                  className="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 rounded-xl font-semibold text-sm hover:shadow-lg hover:shadow-indigo-500/25 transition-all"
                >
                  <Check className="h-4 w-4" />
                  {editingId ? 'Update Address' : 'Save Address'}
                </button>
                <button
                  onClick={resetForm}
                  className="flex-1 sm:flex-none px-6 py-3 border border-gray-200 text-gray-700 rounded-xl font-medium text-sm hover:bg-gray-50 transition-all"
                >
                  Cancel
                </button>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Address List */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        {addresses.map((addr) => (
          <motion.div
            key={addr.id}
            layout
            className="bg-white rounded-2xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-5 relative group"
          >
            {addr.isDefault && (
              <span className="absolute top-3 right-3 sm:top-4 sm:right-4 px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] sm:text-xs font-semibold rounded-md">Default</span>
            )}
            <div className="flex items-start gap-3">
              <div className={`h-10 w-10 rounded-xl flex items-center justify-center shrink-0 ${
                addr.label === 'Home' ? 'bg-blue-50 text-blue-600' :
                addr.label === 'Work' ? 'bg-amber-50 text-amber-600' :
                'bg-purple-50 text-purple-600'
              }`}>
                <MapPin className="h-5 w-5" />
              </div>
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-1 flex-wrap">
                  <p className="text-sm font-bold text-gray-900">{addr.label}</p>
                  {addr.phone && <span className="text-[10px] sm:text-xs text-gray-500">• {addr.phone}</span>}
                </div>
                <p className="text-xs sm:text-sm text-gray-700 font-medium">{addr.fullName}</p>
                <p className="text-xs text-gray-500 mt-0.5 break-words">{addr.street}</p>
                <p className="text-xs text-gray-500">{addr.city}, {addr.state} {addr.zipCode}</p>
              </div>
            </div>
            <div className="flex gap-2 mt-4 pt-3 border-t border-gray-100">
              <button
                onClick={() => handleEdit(addr)}
                className="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
              >
                <Edit2 className="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                Edit
              </button>
              <button
                onClick={() => handleDelete(addr.id!)}
                className="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs sm:text-sm font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors"
              >
                <Trash2 className="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                Delete
              </button>
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  );
}

// ============== PAYMENT SECTION ==============
function PaymentSection({ showToast }: { showToast: (m: string, t?: 'success' | 'error') => void }) {
  const [cards, setCards] = useState<PaymentMethod[]>([
    { id: '1', type: 'visa', last4: '4242', expiry: '12/26', name: 'John Doe', isDefault: true },
    { id: '2', type: 'mastercard', last4: '8888', expiry: '08/25', name: 'John Doe', isDefault: false },
  ]);

  const gradients: Record<string, string> = {
    visa: 'from-blue-600 to-indigo-700',
    mastercard: 'from-orange-500 to-red-600',
    amex: 'from-slate-700 to-slate-900',
  };

  const handleDelete = (id: string) => {
    setCards(cards.filter(c => c.id !== id));
    showToast('Card removed');
  };

  const handleSetDefault = (id: string) => {
    setCards(cards.map(c => ({ ...c, isDefault: c.id === id })));
    showToast('Default card updated');
  };

  return (
    <div className="space-y-5 sm:space-y-6">
      <div className="flex items-center justify-between gap-3">
        <div>
          <h3 className="text-base sm:text-lg font-bold text-gray-900">Payment Methods</h3>
          <p className="text-xs sm:text-sm text-gray-500">{cards.length} card{cards.length !== 1 ? 's' : ''} saved</p>
        </div>
        <button
          onClick={() => showToast('Add card flow coming soon')}
          className="flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gray-900 text-white text-xs sm:text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors shrink-0"
        >
          <Plus className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
          <span className="hidden sm:inline">Add Card</span>
          <span className="sm:hidden">Add</span>
        </button>
      </div>

      {/* Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {cards.map((card) => (
          <motion.div
            key={card.id}
            layout
            className={`relative aspect-[1.6/1] rounded-2xl sm:rounded-3xl p-4 sm:p-5 bg-gradient-to-br ${gradients[card.type]} text-white shadow-xl overflow-hidden`}
          >
            <div className="absolute inset-0" style={{ backgroundImage: 'radial-gradient(circle at 1px 1px, rgba(255,255,255,0.1) 1px, transparent 0)', backgroundSize: '20px 20px' }} />
            <div className="relative h-full flex flex-col justify-between">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-[10px] sm:text-xs text-white/70 uppercase tracking-wider">{card.type}</p>
                  {card.isDefault && (
                    <span className="inline-block mt-1 px-1.5 py-0.5 bg-white/20 backdrop-blur-sm rounded text-[9px] sm:text-[10px] font-bold">DEFAULT</span>
                  )}
                </div>
                <div className="text-right">
                  <p className="text-lg sm:text-xl font-bold tracking-wider">•••• {card.last4}</p>
                </div>
              </div>
              <div className="flex items-end justify-between">
                <div>
                  <p className="text-[10px] sm:text-xs text-white/70">Card Holder</p>
                  <p className="text-xs sm:text-sm font-medium">{card.name}</p>
                </div>
                <div className="text-right">
                  <p className="text-[10px] sm:text-xs text-white/70">Expires</p>
                  <p className="text-xs sm:text-sm font-medium">{card.expiry}</p>
                </div>
              </div>
            </div>
          </motion.div>
        ))}
      </div>

      {/* Card Actions */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 divide-y divide-gray-100">
        {cards.map((card) => (
          <div key={card.id} className="flex items-center gap-3 sm:gap-4 p-3 sm:p-4">
            <div className={`h-10 w-14 sm:h-12 sm:w-16 rounded-lg bg-gradient-to-br ${gradients[card.type]} flex items-center justify-center shrink-0`}>
              <span className="text-white text-[9px] sm:text-[10px] font-bold uppercase">{card.type}</span>
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-xs sm:text-sm font-semibold text-gray-900">•••• •••• •••• {card.last4}</p>
              <p className="text-[10px] sm:text-xs text-gray-500">Expires {card.expiry}</p>
            </div>
            <div className="flex items-center gap-1 sm:gap-2 shrink-0">
              {!card.isDefault && (
                <button
                  onClick={() => handleSetDefault(card.id)}
                  className="px-2 sm:px-3 py-1.5 text-[10px] sm:text-xs font-medium text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                >
                  Set Default
                </button>
              )}
              <button
                onClick={() => handleDelete(card.id)}
                className="p-1.5 sm:p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
              >
                <Trash2 className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
              </button>
            </div>
          </div>
        ))}
      </div>

      <div className="flex items-start gap-3 p-3 sm:p-4 bg-amber-50 border border-amber-100 rounded-xl sm:rounded-2xl">
        <AlertTriangle className="h-4 w-4 sm:h-5 sm:w-5 text-amber-600 shrink-0 mt-0.5" />
        <div>
          <p className="text-xs sm:text-sm font-medium text-amber-900">Secure Payment</p>
          <p className="text-[10px] sm:text-xs text-amber-700 mt-0.5">Your card information is encrypted and stored securely. We never share your payment details with merchants.</p>
        </div>
      </div>
    </div>
  );
}

// ============== NOTIFICATIONS SECTION ==============
function NotificationsSection({ showToast }: { showToast: (m: string, t?: 'success' | 'error') => void }) {
  const [prefs, setPrefs] = useState<NotificationPreferences>({
    orderUpdates: true,
    promotions: true,
    newsletter: false,
    securityAlerts: true,
    productRecommendations: true,
    smsNotifications: false,
  });

  const togglePref = (key: keyof NotificationPreferences) => {
    setPrefs({ ...prefs, [key]: !prefs[key] });
    showToast('Preferences updated');
  };

  const items = [
    { key: 'orderUpdates' as const, title: 'Order Updates', desc: 'Get notified about your order status changes', icon: Package, color: 'from-blue-500 to-cyan-500' },
    { key: 'securityAlerts' as const, title: 'Security Alerts', desc: 'Important alerts about your account security', icon: Shield, color: 'from-red-500 to-rose-500' },
    { key: 'promotions' as const, title: 'Promotions & Deals', desc: 'Special offers and discount notifications', icon: Heart, color: 'from-pink-500 to-rose-500' },
    { key: 'productRecommendations' as const, title: 'Product Recommendations', desc: 'Personalized product suggestions for you', icon: SettingsIcon, color: 'from-purple-500 to-indigo-500' },
    { key: 'newsletter' as const, title: 'Weekly Newsletter', desc: 'Curated content and new arrivals every week', icon: Mail, color: 'from-amber-500 to-orange-500' },
    { key: 'smsNotifications' as const, title: 'SMS Notifications', desc: 'Receive important updates via text message', icon: Smartphone, color: 'from-emerald-500 to-green-500' },
  ];

  return (
    <div className="space-y-5 sm:space-y-6">
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
        <div className="flex items-center gap-3 mb-5 sm:mb-6">
          <div className="h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0">
            <Bell className="h-5 w-5 text-white" />
          </div>
          <div>
            <h3 className="text-base sm:text-lg font-bold text-gray-900">Notification Preferences</h3>
            <p className="text-xs sm:text-sm text-gray-500">Choose what notifications you want to receive</p>
          </div>
        </div>

        <div className="space-y-2 sm:space-y-3">
          {items.map((item) => {
            const Icon = item.icon;
            const isEnabled = prefs[item.key];
            return (
              <div
                key={item.key}
                className="flex items-center gap-3 sm:gap-4 p-3 sm:p-4 bg-gray-50 rounded-xl sm:rounded-2xl"
              >
                <div className={`h-9 w-9 sm:h-10 sm:w-10 bg-gradient-to-br ${item.color} rounded-xl flex items-center justify-center shadow-lg shrink-0`}>
                  <Icon className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-xs sm:text-sm font-semibold text-gray-900">{item.title}</p>
                  <p className="text-[10px] sm:text-xs text-gray-500 mt-0.5">{item.desc}</p>
                </div>
                <button
                  onClick={() => togglePref(item.key)}
                  className={`relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors ${
                    isEnabled ? 'bg-indigo-500' : 'bg-gray-200'
                  }`}
                >
                  <span className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform ${
                    isEnabled ? 'translate-x-6' : 'translate-x-1'
                  }`} />
                </button>
              </div>
            );
          })}
        </div>
      </div>

      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
        <h3 className="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Email Digest</h3>
        <p className="text-xs sm:text-sm text-gray-500 mb-4">How often would you like to receive email summaries?</p>
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
          {['Daily', 'Weekly', 'Monthly'].map((freq) => (
            <button
              key={freq}
              className="py-3 px-4 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-gray-700 hover:border-indigo-300 hover:bg-indigo-50 transition-all"
            >
              {freq}
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}

// ============== PREFERENCES SECTION ==============
function PreferencesSection({ showToast }: { showToast: (m: string, t?: 'success' | 'error') => void }) {
  const { theme, setTheme } = useTheme();
  const [language, setLanguage] = useState('en');
  const [currency, setCurrency] = useState('usd');

  const languages = [
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'es', name: 'Español', flag: '🇪🇸' },
    { code: 'fr', name: 'Français', flag: '🇫🇷' },
    { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
    { code: 'ja', name: '日本語', flag: '🇯🇵' },
    { code: 'zh', name: '中文', flag: '🇨🇳' },
  ];

  const currencies = [
    { code: 'usd', name: 'US Dollar', symbol: '$' },
    { code: 'eur', name: 'Euro', symbol: '€' },
    { code: 'gbp', name: 'British Pound', symbol: '£' },
    { code: 'jpy', name: 'Japanese Yen', symbol: '¥' },
  ];

  return (
    <div className="space-y-5 sm:space-y-6">
      {/* Language */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
        <div className="flex items-center gap-3 mb-4 sm:mb-5">
          <div className="h-10 w-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 shrink-0">
            <Globe className="h-5 w-5 text-white" />
          </div>
          <div>
            <h3 className="text-base sm:text-lg font-bold text-gray-900">Language</h3>
            <p className="text-xs sm:text-sm text-gray-500">Choose your preferred language</p>
          </div>
        </div>
        <div className="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
          {languages.map((lang) => (
            <button
              key={lang.code}
              onClick={() => { setLanguage(lang.code); showToast('Language updated'); }}
              className={`flex items-center gap-2 sm:gap-3 p-2.5 sm:p-3 rounded-xl border transition-all ${
                language === lang.code
                  ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500/20'
                  : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
              }`}
            >
              <span className="text-lg sm:text-xl">{lang.flag}</span>
              <span className="text-xs sm:text-sm font-medium text-gray-900 truncate">{lang.name}</span>
            </button>
          ))}
        </div>
      </div>

      {/* Currency */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
        <div className="flex items-center gap-3 mb-4 sm:mb-5">
          <div className="h-10 w-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 shrink-0">
            <DollarSign className="h-5 w-5 text-white" />
          </div>
          <div>
            <h3 className="text-base sm:text-lg font-bold text-gray-900">Currency</h3>
            <p className="text-xs sm:text-sm text-gray-500">Display prices in your preferred currency</p>
          </div>
        </div>
        <div className="grid grid-cols-2 gap-2 sm:gap-3">
          {currencies.map((curr) => (
            <button
              key={curr.code}
              onClick={() => { setCurrency(curr.code); showToast('Currency updated'); }}
              className={`flex items-center gap-2 sm:gap-3 p-2.5 sm:p-3 rounded-xl border transition-all ${
                currency === curr.code
                  ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500/20'
                  : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
              }`}
            >
              <span className="text-base sm:text-lg font-bold text-gray-900">{curr.symbol}</span>
              <div className="text-left min-w-0">
                <p className="text-xs sm:text-sm font-medium text-gray-900 truncate">{curr.name}</p>
                <p className="text-[10px] text-gray-500 uppercase">{curr.code}</p>
              </div>
            </button>
          ))}
        </div>
      </div>

      {/* Theme */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-gray-100 p-4 sm:p-6">
        <div className="flex items-center gap-3 mb-4 sm:mb-5">
          <div className="h-10 w-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/20 shrink-0">
            <Palette className="h-5 w-5 text-white" />
          </div>
          <div>
            <h3 className="text-base sm:text-lg font-bold text-gray-900">Appearance</h3>
            <p className="text-xs sm:text-sm text-gray-500">Customize how ShopVerse looks</p>
          </div>
        </div>
        <div className="grid grid-cols-3 gap-2 sm:gap-3">
          {([
            { id: 'light' as const, label: 'Light', preview: 'bg-white border-gray-200' },
            { id: 'dark' as const, label: 'Dark', preview: 'bg-gray-900 border-gray-700' },
            { id: 'system' as const, label: 'System', preview: 'bg-gradient-to-r from-white to-gray-900 border-gray-300' },
          ] as const).map((t) => (
            <button
              key={t.id}
              onClick={() => { setTheme(t.id); showToast('Theme updated'); }}
              className={`flex flex-col items-center gap-2 p-2.5 sm:p-3 rounded-xl border-2 transition-all ${
                theme === t.id
                  ? 'border-indigo-500 ring-2 ring-indigo-500/20'
                  : 'border-gray-200 hover:border-gray-300'
              }`}
            >
              <div className={`w-full aspect-video rounded-lg border ${t.preview}`} />
              <span className="text-[10px] sm:text-xs font-medium text-gray-900">{t.label}</span>
            </button>
          ))}
        </div>
      </div>

      {/* Danger Zone */}
      <div className="bg-white rounded-2xl sm:rounded-3xl shadow-sm shadow-gray-200/50 border border-red-100 p-4 sm:p-6">
        <div className="flex items-start gap-3 mb-4">
          <div className="h-10 w-10 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
            <AlertTriangle className="h-5 w-5 text-red-600" />
          </div>
          <div className="min-w-0">
            <h3 className="text-base sm:text-lg font-bold text-gray-900">Danger Zone</h3>
            <p className="text-xs sm:text-sm text-gray-500">Irreversible actions for your account</p>
          </div>
        </div>
        <div className="space-y-2 sm:space-y-3">
          <button
            onClick={() => showToast('This action is disabled in demo mode', 'error')}
            className="w-full flex items-center justify-between p-3 sm:p-4 border border-red-200 rounded-xl hover:bg-red-50 transition-colors group"
          >
            <div className="text-left min-w-0">
              <p className="text-xs sm:text-sm font-semibold text-red-600">Deactivate Account</p>
              <p className="text-[10px] sm:text-xs text-gray-500">Temporarily disable your account</p>
            </div>
            <ChevronRight className="h-4 w-4 text-red-400 group-hover:translate-x-1 transition-transform shrink-0" />
          </button>
          <button
            onClick={() => showToast('This action is disabled in demo mode', 'error')}
            className="w-full flex items-center justify-between p-3 sm:p-4 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition-colors group"
          >
            <div className="text-left min-w-0">
              <p className="text-xs sm:text-sm font-semibold text-red-600">Delete Account</p>
              <p className="text-[10px] sm:text-xs text-gray-500">Permanently delete your account and all data</p>
            </div>
            <ChevronRight className="h-4 w-4 text-red-400 group-hover:translate-x-1 transition-transform shrink-0" />
          </button>
        </div>
      </div>
    </div>
  );
}
