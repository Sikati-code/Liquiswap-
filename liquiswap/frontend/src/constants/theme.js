/**
 * LiquiSwap Design System
 * Equatorial Trust - Color Palette & Theme Configuration
 * 
 * Based on Chapter 6.1 of the project documentation
 */

// ============================================
// COLOR PALETTE (Exact Hex Codes)
// ============================================

export const Colors = {
  // Primary Colors
  LiquiSwapNavy: '#0B1E3B',      // Headers, Primary Buttons
  DoualaSlate: '#334155',         // Body Text
  
  // Accent Colors
  YelloGold: '#FFCC00',           // MTN Accents
  CitrusOrange: '#FF7900',        // Orange Accents
  PulsePurple: '#6D28D9',         // Swap Button, Branding
  CashGreen: '#059669',           // Cash Indicators
  
  // Neutral Colors
  KribiWhite: '#FFFFFF',          // Backgrounds
  DeepNavy: '#0F172A',            // Dark Mode Base
  
  // Extended Palette
  LightGray: '#F1F5F9',
  MediumGray: '#94A3B8',
  DarkGray: '#475569',
  ErrorRed: '#EF4444',
  SuccessGreen: '#10B981',
  WarningYellow: '#F59E0B',
  
  // Operator Colors
  MTN: '#FFCC00',
  ORANGE: '#FF7900',
  CASH: '#059669',
};

// ============================================
// DARK MODE COLORS
// ============================================

export const DarkColors = {
  background: '#0F172A',
  surface: '#1E293B',
  surfaceElevated: '#334155',
  textPrimary: '#F8FAFC',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  border: '#334155',
  ...Colors
};

// ============================================
// LIGHT MODE COLORS
// ============================================

export const LightColors = {
  background: '#FFFFFF',
  surface: '#F8FAFC',
  surfaceElevated: '#FFFFFF',
  textPrimary: '#0F172A',
  textSecondary: '#334155',
  textMuted: '#64748B',
  border: '#E2E8F0',
  ...Colors
};

// ============================================
// TYPOGRAPHY
// ============================================

export const Typography = {
  // Font Family
  fontFamily: {
    regular: 'Inter-Regular',
    medium: 'Inter-Medium',
    semiBold: 'Inter-SemiBold',
    bold: 'Inter-Bold',
  },
  
  // Scale (responsive to screen size)
  sizes: {
    h1: 28,      // Bold - Screen titles
    h2: 22,      // SemiBold - Section headers
    h3: 18,      // SemiBold - Card titles
    body: 16,    // Regular - Body text
    bodySmall: 14, // Regular - Secondary text
    caption: 12, // Medium - Labels, captions
    tiny: 10,    // Medium - Fine print
  },
  
  // Line Heights
  lineHeight: {
    tight: 1.2,
    normal: 1.5,
    relaxed: 1.75,
  },
  
  // Letter Spacing
  letterSpacing: {
    tight: -0.5,
    normal: 0,
    wide: 0.5,
  }
};

// ============================================
// SPACING SYSTEM
// ============================================

export const Spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
  xxxl: 64,
};

// ============================================
// BORDER RADIUS
// ============================================

export const BorderRadius = {
  none: 0,
  sm: 4,
  md: 8,
  lg: 12,
  xl: 16,
  xxl: 24,
  full: 9999,
};

// ============================================
// SHADOWS
// ============================================

export const Shadows = {
  none: {
    shadowColor: 'transparent',
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0,
    shadowRadius: 0,
    elevation: 0,
  },
  sm: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 2,
  },
  md: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
  },
  lg: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.15,
    shadowRadius: 16,
    elevation: 8,
  },
  xl: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 16 },
    shadowOpacity: 0.2,
    shadowRadius: 24,
    elevation: 12,
  },
};

// ============================================
// ANIMATION CONFIGURATIONS
// ============================================

export const Animations = {
  // Durations (in milliseconds)
  duration: {
    micro: 150,      // Button presses, small feedback
    fast: 200,       // Quick transitions
    normal: 300,     // Standard transitions
    slow: 400,       // Screen transitions
    slower: 500,     // Emphasis animations
  },
  
  // Easing Functions
  easing: {
    // Standard material motion easing
    standard: [0.4, 0.0, 0.2, 1],
    // Elements entering the screen
    enter: [0.0, 0.0, 0.2, 1],
    // Elements exiting the screen
    exit: [0.4, 0.0, 1, 1],
    // Emphasis (bounce)
    emphasis: [0.4, 0.0, 0.6, 1],
    // Spring for gestures
    spring: {
      damping: 15,
      stiffness: 150,
      mass: 1,
    }
  },
  
  // Stagger delays
  stagger: {
    fast: 50,
    normal: 100,
    slow: 150,
  }
};

// ============================================
// GESTURE CONFIGURATIONS
// ============================================

export const Gestures = {
  swipeThreshold: 50,
  longPressDuration: 500,
  doubleTapDelay: 300,
  scrollVelocity: 800,
};

// ============================================
// COMPONENT-SPECIFIC STYLES
// ============================================

export const ComponentStyles = {
  // Button Styles
  button: {
    primary: {
      backgroundColor: Colors.PulsePurple,
      paddingVertical: Spacing.md,
      paddingHorizontal: Spacing.lg,
      borderRadius: BorderRadius.xxl,
    },
    secondary: {
      backgroundColor: 'transparent',
      borderWidth: 2,
      borderColor: Colors.PulsePurple,
      paddingVertical: Spacing.md,
      paddingHorizontal: Spacing.lg,
      borderRadius: BorderRadius.xxl,
    },
    ghost: {
      backgroundColor: 'transparent',
      paddingVertical: Spacing.md,
      paddingHorizontal: Spacing.lg,
    },
  },
  
  // Card Styles
  card: {
    backgroundColor: Colors.KribiWhite,
    borderRadius: BorderRadius.xl,
    padding: Spacing.lg,
    ...Shadows.md,
  },
  
  // Input Styles
  input: {
    backgroundColor: Colors.LightGray,
    borderRadius: BorderRadius.lg,
    paddingVertical: Spacing.md,
    paddingHorizontal: Spacing.lg,
    fontSize: Typography.sizes.body,
    color: Colors.DoualaSlate,
  },
  
  // Balance Card
  balanceCard: {
    MTN: {
      backgroundColor: Colors.YelloGold,
      textColor: Colors.LiquiSwapNavy,
    },
    ORANGE: {
      backgroundColor: Colors.CitrusOrange,
      textColor: Colors.KribiWhite,
    },
    CASH: {
      backgroundColor: Colors.CashGreen,
      textColor: Colors.KribiWhite,
    },
  }
};

// ============================================
// LOTTIE ANIMATION REFERENCES
// ============================================

export const LottieAnimations = {
  // Onboarding
  onboarding1: require('../assets/lottie/onboarding-exchange.json'),
  onboarding2: require('../assets/lottie/onboarding-secure.json'),
  onboarding3: require('../assets/lottie/onboarding-instant.json'),
  
  // Status
  success: require('../assets/lottie/success.json'),
  error: require('../assets/lottie/placeholder.json'),
  loading: require('../assets/lottie/placeholder.json'),
  
  // Special
  confetti: require('../assets/lottie/confetti.json'),
  match: require('../assets/lottie/placeholder.json'),
  empty: require('../assets/lottie/placeholder.json'),
  
  // UI Feedback
  pullToRefresh: require('../assets/lottie/placeholder.json'),
  typing: require('../assets/lottie/placeholder.json'),
};

// ============================================
// UTILITY FUNCTIONS
// ============================================

export const getCurrencyColor = (type) => {
  switch (type?.toUpperCase()) {
    case 'MTN':
      return Colors.YelloGold;
    case 'ORANGE':
      return Colors.CitrusOrange;
    case 'CASH':
      return Colors.CashGreen;
    default:
      return Colors.MediumGray;
  }
};

export const getCurrencyTextColor = (type) => {
  switch (type?.toUpperCase()) {
    case 'MTN':
      return Colors.LiquiSwapNavy;
    case 'ORANGE':
    case 'CASH':
      return Colors.KribiWhite;
    default:
      return Colors.DoualaSlate;
  }
};

export const formatCurrency = (amount, currency = 'XAF') => {
  return new Intl.NumberFormat('fr-CM', {
    style: 'currency',
    currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

export const formatPhoneNumber = (phone) => {
  if (!phone) return '';
  const cleaned = phone.replace(/\D/g, '');
  if (cleaned.length === 9) {
    return `+237 ${cleaned.slice(0, 3)} ${cleaned.slice(3, 6)} ${cleaned.slice(6)}`;
  }
  return phone;
};

// ============================================
// THEME OBJECT (Complete)
// ============================================

export const Theme = {
  colors: Colors,
  darkColors: DarkColors,
  lightColors: LightColors,
  typography: Typography,
  spacing: Spacing,
  borderRadius: BorderRadius,
  shadows: Shadows,
  animations: Animations,
  gestures: Gestures,
  componentStyles: ComponentStyles,
  lottie: LottieAnimations,
};

export default Theme;
