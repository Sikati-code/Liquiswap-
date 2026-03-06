/**
 * Balance Card Component
 * Animated card showing balance for MTN, Orange, or Cash
 */

import React, { useCallback } from 'react';
import { View, Text, StyleSheet, Pressable } from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  withTiming,
  interpolate,
  Extrapolate,
  runOnJS,
} from 'react-native-reanimated';
import * as Haptics from 'expo-haptics';
import { Colors, Typography, Spacing, BorderRadius, Shadows, Animations } from '../constants/theme';
import { formatCurrency, getCurrencyColor, getCurrencyTextColor } from '../constants/theme';

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

const BalanceCard = ({
  type,
  amount,
  phoneNumber,
  isActive = false,
  onPress,
  style = {},
  index = 0,
}) => {
  const scale = useSharedValue(1);
  const elevation = useSharedValue(isActive ? 8 : 4);
  const translateY = useSharedValue(0);

  const handlePressIn = useCallback(() => {
    scale.value = withSpring(0.98, Animations.easing.spring);
    translateY.value = withTiming(-4, { duration: 150 });
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
  }, []);

  const handlePressOut = useCallback(() => {
    scale.value = withSpring(1, Animations.easing.spring);
    translateY.value = withTiming(0, { duration: 150 });
  }, []);

  const handlePress = useCallback(() => {
    if (onPress) {
      onPress(type);
    }
  }, [onPress, type]);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [
      { scale: scale.value },
      { translateY: translateY.value },
    ],
    elevation: elevation.value,
  }));

  const backgroundColor = getCurrencyColor(type);
  const textColor = getCurrencyTextColor(type);

  const getIcon = () => {
    switch (type?.toUpperCase()) {
      case 'MTN':
        return 'M';
      case 'ORANGE':
        return 'O';
      case 'CASH':
        return 'C';
      default:
        return '?';
    }
  };

  const getLabel = () => {
    switch (type?.toUpperCase()) {
      case 'MTN':
        return 'MTN Mobile Money';
      case 'ORANGE':
        return 'Orange Money';
      case 'CASH':
        return 'Cash';
      default:
        return type;
    }
  };

  return (
    <AnimatedPressable
      onPressIn={handlePressIn}
      onPressOut={handlePressOut}
      onPress={handlePress}
      style={[
        styles.container,
        {
          backgroundColor,
          marginLeft: index === 0 ? Spacing.lg : Spacing.md,
        },
        animatedStyle,
        style,
      ]}
    >
      {/* Card Header */}
      <View style={styles.header}>
        <View style={[styles.iconContainer, { backgroundColor: textColor + '20' }]}>
          <Text style={[styles.icon, { color: textColor }]}>
            {getIcon()}
          </Text>
        </View>
        <Text style={[styles.label, { color: textColor + 'CC' }]}>
          {getLabel()}
        </Text>
      </View>

      {/* Balance Amount */}
      <View style={styles.balanceContainer}>
        <Text style={[styles.amount, { color: textColor }]}>
          {formatCurrency(amount)}
        </Text>
      </View>

      {/* Phone Number (if applicable) */}
      {phoneNumber && (
        <View style={styles.footer}>
          <Text style={[styles.phoneNumber, { color: textColor + 'AA' }]}>
            {phoneNumber}
          </Text>
        </View>
      )}

      {/* Active Indicator */}
      {isActive && (
        <View style={[styles.activeIndicator, { backgroundColor: textColor }]} />
      )}
    </AnimatedPressable>
  );
};

const styles = StyleSheet.create({
  container: {
    width: 280,
    height: 160,
    borderRadius: BorderRadius.xl,
    padding: Spacing.lg,
    ...Shadows.lg,
    position: 'relative',
    overflow: 'hidden',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  iconContainer: {
    width: 36,
    height: 36,
    borderRadius: BorderRadius.lg,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Spacing.sm,
  },
  icon: {
    fontSize: 18,
    fontFamily: Typography.fontFamily.bold,
  },
  label: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.medium,
  },
  balanceContainer: {
    flex: 1,
    justifyContent: 'center',
    marginTop: Spacing.md,
  },
  amount: {
    fontSize: 32,
    fontFamily: Typography.fontFamily.bold,
  },
  footer: {
    marginTop: Spacing.sm,
  },
  phoneNumber: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
  },
  activeIndicator: {
    position: 'absolute',
    bottom: Spacing.md,
    right: Spacing.md,
    width: 8,
    height: 8,
    borderRadius: BorderRadius.full,
  },
});

export default BalanceCard;
