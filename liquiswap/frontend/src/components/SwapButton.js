/**
 * Swap Button Component
 * Large, animated CTA button for the swap action
 */

import React, { useCallback } from 'react';
import { View, Text, StyleSheet, Pressable } from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  withTiming,
  withSequence,
  withRepeat,
  interpolate,
  Extrapolate,
} from 'react-native-reanimated';
import * as Haptics from 'expo-haptics';
import { LinearGradient } from 'expo-linear-gradient';
import { Colors, Typography, Spacing, BorderRadius, Animations } from '../constants/theme';

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);
const AnimatedLinearGradient = Animated.createAnimatedComponent(LinearGradient);

const SwapButton = ({
  title = 'SWAP',
  onPress,
  disabled = false,
  loading = false,
  style = {},
}) => {
  const scale = useSharedValue(1);
  const glowOpacity = useSharedValue(0);
  const shimmerTranslate = useSharedValue(-200);

  // Shimmer animation for loading state
  React.useEffect(() => {
    if (loading) {
      shimmerTranslate.value = withRepeat(
        withTiming(400, { duration: 1000 }),
        -1,
        false
      );
    } else {
      shimmerTranslate.value = -200;
    }
  }, [loading]);

  const handlePressIn = useCallback(() => {
    if (!disabled && !loading) {
      scale.value = withSpring(0.95, Animations.easing.spring);
      glowOpacity.value = withTiming(0.5, { duration: 150 });
      Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
    }
  }, [disabled, loading]);

  const handlePressOut = useCallback(() => {
    if (!disabled && !loading) {
      scale.value = withSpring(1, Animations.easing.spring);
      glowOpacity.value = withTiming(0, { duration: 200 });
    }
  }, [disabled, loading]);

  const handlePress = useCallback(() => {
    if (!disabled && !loading && onPress) {
      // Pulse animation on press
      scale.value = withSequence(
        withTiming(0.9, { duration: 100 }),
        withSpring(1, { ...Animations.easing.spring, damping: 10 })
      );
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      onPress();
    }
  }, [disabled, loading, onPress]);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
  }));

  const glowStyle = useAnimatedStyle(() => ({
    opacity: glowOpacity.value,
  }));

  const shimmerStyle = useAnimatedStyle(() => ({
    transform: [{ translateX: shimmerTranslate.value }],
  }));

  return (
    <View style={[styles.container, style]}>
      {/* Glow Effect */}
      <AnimatedLinearGradient
        colors={[Colors.PulsePurple, Colors.PulsePurple + '00']}
        style={[styles.glow, glowStyle]}
        pointerEvents="none"
      />

      {/* Main Button */}
      <AnimatedPressable
        onPressIn={handlePressIn}
        onPressOut={handlePressOut}
        onPress={handlePress}
        style={[
          styles.button,
          (disabled || loading) && styles.buttonDisabled,
          animatedStyle,
        ]}
        disabled={disabled || loading}
      >
        <LinearGradient
          colors={[Colors.PulsePurple, '#7C3AED']}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.gradient}
        >
          {/* Shimmer Effect for Loading */}
          {loading && (
            <Animated.View style={[styles.shimmer, shimmerStyle]}>
              <View style={styles.shimmerLine} />
            </Animated.View>
          )}

          {/* Button Content */}
          <View style={styles.content}>
            <Text style={styles.text}>
              {loading ? 'Processing...' : title}
            </Text>
          </View>
        </LinearGradient>
      </AnimatedPressable>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  glow: {
    position: 'absolute',
    width: 200,
    height: 200,
    borderRadius: 100,
    top: -50,
    opacity: 0,
  },
  button: {
    width: '100%',
    height: 56,
    borderRadius: BorderRadius.xxl,
    overflow: 'hidden',
    ...Shadows.lg,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  gradient: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
    overflow: 'hidden',
  },
  shimmer: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: 100,
    backgroundColor: 'rgba(255,255,255,0.3)',
    transform: [{ skewX: '-20deg' }],
  },
  shimmerLine: {
    width: 20,
    height: '100%',
    backgroundColor: 'rgba(255,255,255,0.5)',
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 1,
  },
  text: {
    fontSize: Typography.sizes.h3,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    letterSpacing: 2,
  },
});

export default SwapButton;
