/**
 * Splash Screen
 * Animated logo with shimmer effect
 */

import React, { useEffect } from 'react';
import { View, Text, StyleSheet, Dimensions } from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withTiming,
  withSpring,
  withSequence,
  withRepeat,
  interpolate,
  Easing,
  runOnJS,
} from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import { Colors, Typography, Spacing, Animations } from '../constants/theme';

const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');

const SplashScreen = ({ onAnimationComplete }) => {
  const logoScale = useSharedValue(0.8);
  const logoOpacity = useSharedValue(0);
  const shimmerTranslate = useSharedValue(-200);
  const containerOpacity = useSharedValue(1);

  useEffect(() => {
    // Entrance animation
    logoOpacity.value = withTiming(1, { duration: 500 });
    logoScale.value = withSpring(1, { ...Animations.easing.spring, damping: 12 });

    // Shimmer animation
    shimmerTranslate.value = withRepeat(
      withTiming(200, { duration: 1500, easing: Easing.linear }),
      -1,
      false
    );

    // Exit animation after 2.5 seconds
    const timer = setTimeout(() => {
      containerOpacity.value = withTiming(
        0, 
        { duration: 400 },
        () => {
          runOnJS(onAnimationComplete)();
        }
      );
    }, 2500);

    return () => clearTimeout(timer);
  }, []);

  const logoAnimatedStyle = useAnimatedStyle(() => ({
    opacity: logoOpacity.value,
    transform: [{ scale: logoScale.value }],
  }));

  const shimmerAnimatedStyle = useAnimatedStyle(() => ({
    transform: [{ translateX: shimmerTranslate.value }],
  }));

  const containerAnimatedStyle = useAnimatedStyle(() => ({
    opacity: containerOpacity.value,
  }));

  return (
    <Animated.View style={[styles.container, containerAnimatedStyle]}>
      {/* Background Gradient */}
      <LinearGradient
        colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
        style={styles.background}
      />

      {/* Logo Container */}
      <Animated.View style={[styles.logoContainer, logoAnimatedStyle]}>
        {/* Logo Circle */}
        <View style={styles.logoCircle}>
          <Text style={styles.logoText}>L</Text>
          
          {/* Shimmer Overlay */}
          <Animated.View style={[styles.shimmer, shimmerAnimatedStyle]}>
            <LinearGradient
              colors={['transparent', 'rgba(255,255,255,0.3)', 'transparent']}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 0 }}
              style={styles.shimmerGradient}
            />
          </Animated.View>
        </View>

        {/* Brand Name */}
        <Text style={styles.brandName}>
          Liqui<Text style={styles.brandNameHighlight}>Swap</Text>
        </Text>

        {/* Tagline */}
        <Text style={styles.tagline}>Equatorial Trust</Text>
      </Animated.View>

      {/* Decorative Elements */}
      <View style={styles.decorativeContainer}>
        <Animated.View 
          style={[
            styles.decorativeCircle, 
            styles.circle1,
            { opacity: logoOpacity.value }
          ]} 
        />
        <Animated.View 
          style={[
            styles.decorativeCircle, 
            styles.circle2,
            { opacity: logoOpacity.value }
          ]} 
        />
        <Animated.View 
          style={[
            styles.decorativeCircle, 
            styles.circle3,
            { opacity: logoOpacity.value }
          ]} 
        />
      </View>
    </Animated.View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  background: {
    ...StyleSheet.absoluteFillObject,
  },
  logoContainer: {
    alignItems: 'center',
    zIndex: 10,
  },
  logoCircle: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: Colors.PulsePurple,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    ...Shadows.xl,
  },
  logoText: {
    fontSize: 60,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  shimmer: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: 60,
    transform: [{ skewX: '-20deg' }],
  },
  shimmerGradient: {
    flex: 1,
  },
  brandName: {
    fontSize: Typography.sizes.h1,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    marginTop: Spacing.lg,
    letterSpacing: 1,
  },
  brandNameHighlight: {
    color: Colors.PulsePurple,
  },
  tagline: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '80',
    marginTop: Spacing.sm,
    letterSpacing: 3,
  },
  decorativeContainer: {
    ...StyleSheet.absoluteFillObject,
    overflow: 'hidden',
  },
  decorativeCircle: {
    position: 'absolute',
    borderRadius: 100,
    borderWidth: 2,
    borderColor: Colors.PulsePurple + '30',
  },
  circle1: {
    width: 200,
    height: 200,
    top: SCREEN_HEIGHT * 0.1,
    left: -50,
  },
  circle2: {
    width: 300,
    height: 300,
    bottom: -100,
    right: -100,
  },
  circle3: {
    width: 150,
    height: 150,
    top: SCREEN_HEIGHT * 0.3,
    right: 30,
  },
});

export default SplashScreen;
