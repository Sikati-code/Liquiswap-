/**
 * Animated Button Component
 * Pressable button with scale and ripple animations
 */

import React, { useCallback } from 'react';
import { View, Text, Pressable, StyleSheet } from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  withTiming,
  interpolate,
  Extrapolate,
} from 'react-native-reanimated';
import * as Haptics from 'expo-haptics';
import { Colors, Typography, Spacing, BorderRadius, Animations } from '../constants/theme';

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

const AnimatedButton = ({
  title,
  onPress,
  variant = 'primary', // primary, secondary, ghost
  size = 'medium', // small, medium, large
  disabled = false,
  loading = false,
  icon = null,
  style = {},
  textStyle = {},
  haptic = true,
  fullWidth = false,
}) => {
  const scale = useSharedValue(1);
  const rippleScale = useSharedValue(0);
  const rippleOpacity = useSharedValue(0);

  const handlePressIn = useCallback(() => {
    scale.value = withSpring(0.95, Animations.easing.spring);
    rippleScale.value = withTiming(1, { duration: 300 });
    rippleOpacity.value = withTiming(0.3, { duration: 150 });
    
    if (haptic && !disabled) {
      Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    }
  }, [disabled, haptic]);

  const handlePressOut = useCallback(() => {
    scale.value = withSpring(1, Animations.easing.spring);
    rippleScale.value = withTiming(0, { duration: 200 });
    rippleOpacity.value = withTiming(0, { duration: 200 });
  }, []);

  const handlePress = useCallback(() => {
    if (!disabled && !loading && onPress) {
      onPress();
    }
  }, [disabled, loading, onPress]);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
  }));

  const rippleStyle = useAnimatedStyle(() => ({
    transform: [{ scale: rippleScale.value }],
    opacity: rippleOpacity.value,
  }));

  const getBackgroundColor = () => {
    if (disabled) return Colors.MediumGray;
    
    switch (variant) {
      case 'primary':
        return Colors.PulsePurple;
      case 'secondary':
        return 'transparent';
      case 'ghost':
        return 'transparent';
      default:
        return Colors.PulsePurple;
    }
  };

  const getTextColor = () => {
    if (disabled) return Colors.KribiWhite;
    
    switch (variant) {
      case 'primary':
        return Colors.KribiWhite;
      case 'secondary':
        return Colors.PulsePurple;
      case 'ghost':
        return Colors.PulsePurple;
      default:
        return Colors.KribiWhite;
    }
  };

  const getSizeStyles = () => {
    switch (size) {
      case 'small':
        return {
          paddingVertical: Spacing.sm,
          paddingHorizontal: Spacing.md,
        };
      case 'large':
        return {
          paddingVertical: Spacing.lg,
          paddingHorizontal: Spacing.xl,
        };
      default:
        return {
          paddingVertical: Spacing.md,
          paddingHorizontal: Spacing.lg,
        };
    }
  };

  return (
    <AnimatedPressable
      onPressIn={handlePressIn}
      onPressOut={handlePressOut}
      onPress={handlePress}
      style={[
        styles.button,
        {
          backgroundColor: getBackgroundColor(),
          borderWidth: variant === 'secondary' ? 2 : 0,
          borderColor: variant === 'secondary' ? Colors.PulsePurple : 'transparent',
          width: fullWidth ? '100%' : 'auto',
          ...getSizeStyles(),
        },
        animatedStyle,
        style,
      ]}
      disabled={disabled || loading}
    >
      {/* Ripple Effect */}
      <Animated.View
        style={[
          styles.ripple,
          { backgroundColor: getTextColor() },
          rippleStyle,
        ]}
        pointerEvents="none"
      />
      
      {/* Content */}
      <View style={styles.content}>
        {icon && <View style={styles.iconContainer}>{icon}</View>}
        <Text
          style={[
            styles.text,
            {
              color: getTextColor(),
              fontSize: size === 'small' ? Typography.sizes.bodySmall : Typography.sizes.body,
            },
            textStyle,
          ]}
        >
          {loading ? 'Loading...' : title}
        </Text>
      </View>
    </AnimatedPressable>
  );
};

const styles = StyleSheet.create({
  button: {
    borderRadius: BorderRadius.xxl,
    overflow: 'hidden',
    position: 'relative',
    alignItems: 'center',
    justifyContent: 'center',
  },
  ripple: {
    position: 'absolute',
    width: 200,
    height: 200,
    borderRadius: 100,
    top: -100,
    left: -100,
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 1,
  },
  iconContainer: {
    marginRight: Spacing.sm,
  },
  text: {
    fontFamily: Typography.fontFamily.semiBold,
    textAlign: 'center',
  },
});

export default AnimatedButton;
