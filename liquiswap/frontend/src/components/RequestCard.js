/**
 * Request Card Component
 * Card showing exchange request in marketplace
 */

import React, { useCallback } from 'react';
import { View, Text, StyleSheet, Pressable } from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  withTiming,
  interpolate,
} from 'react-native-reanimated';
import * as Haptics from 'expo-haptics';
import { Colors, Typography, Spacing, BorderRadius, Shadows, Animations } from '../constants/theme';
import { formatCurrency, getCurrencyColor } from '../constants/theme';

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

const RequestCard = ({
  request,
  onPress,
  onMatch,
  onSave,
  style = {},
  index = 0,
}) => {
  const scale = useSharedValue(1);
  const opacity = useSharedValue(1);

  const handlePressIn = useCallback(() => {
    scale.value = withSpring(0.97, Animations.easing.spring);
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
  }, []);

  const handlePressOut = useCallback(() => {
    scale.value = withSpring(1, Animations.easing.spring);
  }, []);

  const handlePress = useCallback(() => {
    if (onPress) {
      onPress(request);
    }
  }, [onPress, request]);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
    opacity: opacity.value,
  }));

  const { owner, haveType, wantType, haveAmount, wantAmount, location, createdAt } = request;

  const getTimeAgo = (date) => {
    const now = new Date();
    const diff = now - new Date(date);
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    return `${days}d ago`;
  };

  return (
    <AnimatedPressable
      onPressIn={handlePressIn}
      onPressOut={handlePressOut}
      onPress={handlePress}
      style={[
        styles.container,
        animatedStyle,
        { marginTop: index === 0 ? 0 : Spacing.md },
        style,
      ]}
    >
      {/* User Info Header */}
      <View style={styles.header}>
        <View style={styles.avatarContainer}>
          <Text style={styles.avatarText}>
            {owner?.name?.charAt(0) || 'U'}
          </Text>
        </View>
        <View style={styles.userInfo}>
          <Text style={styles.userName} numberOfLines={1}>
            {owner?.name || 'Anonymous'}
          </Text>
          <View style={styles.ratingContainer}>
            <Text style={styles.ratingText}>★ {owner?.rating?.toFixed(1) || '5.0'}</Text>
          </View>
        </View>
        <Text style={styles.timeAgo}>{getTimeAgo(createdAt)}</Text>
      </View>

      {/* Exchange Details */}
      <View style={styles.exchangeContainer}>
        {/* Have Section */}
        <View style={styles.exchangeSection}>
          <View style={[styles.currencyBadge, { backgroundColor: getCurrencyColor(haveType) + '20' }]}>
            <Text style={[styles.currencyText, { color: getCurrencyColor(haveType) }]}>
              {haveType}
            </Text>
          </View>
          <Text style={styles.amountText}>{formatCurrency(haveAmount)}</Text>
        </View>

        {/* Arrow */}
        <View style={styles.arrowContainer}>
          <Text style={styles.arrow}>→</Text>
        </View>

        {/* Want Section */}
        <View style={styles.exchangeSection}>
          <View style={[styles.currencyBadge, { backgroundColor: getCurrencyColor(wantType) + '20' }]}>
            <Text style={[styles.currencyText, { color: getCurrencyColor(wantType) }]}>
              {wantType}
            </Text>
          </View>
          <Text style={styles.amountText}>{formatCurrency(wantAmount)}</Text>
        </View>
      </View>

      {/* Location & Rate */}
      <View style={styles.footer}>
        {location && (
          <View style={styles.locationContainer}>
            <Text style={styles.locationText}>📍 {location}</Text>
          </View>
        )}
        <View style={styles.rateContainer}>
          <Text style={styles.rateText}>
            Rate: 1 {haveType} = {(wantAmount / haveAmount).toFixed(2)} {wantType}
          </Text>
        </View>
      </View>
    </AnimatedPressable>
  );
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: Colors.KribiWhite,
    borderRadius: BorderRadius.xl,
    padding: Spacing.lg,
    ...Shadows.md,
    marginHorizontal: Spacing.lg,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: Spacing.md,
  },
  avatarContainer: {
    width: 40,
    height: 40,
    borderRadius: BorderRadius.full,
    backgroundColor: Colors.PulsePurple + '20',
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.PulsePurple,
  },
  userInfo: {
    flex: 1,
    marginLeft: Spacing.sm,
  },
  userName: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.semiBold,
    color: Colors.DoualaSlate,
  },
  ratingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 2,
  },
  ratingText: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.YelloGold,
  },
  timeAgo: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.MediumGray,
  },
  exchangeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: Colors.LightGray,
    borderRadius: BorderRadius.lg,
    padding: Spacing.md,
  },
  exchangeSection: {
    flex: 1,
    alignItems: 'center',
  },
  currencyBadge: {
    paddingHorizontal: Spacing.sm,
    paddingVertical: 4,
    borderRadius: BorderRadius.md,
    marginBottom: Spacing.xs,
  },
  currencyText: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.bold,
  },
  amountText: {
    fontSize: Typography.sizes.h3,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.DoualaSlate,
  },
  arrowContainer: {
    paddingHorizontal: Spacing.md,
  },
  arrow: {
    fontSize: 24,
    color: Colors.PulsePurple,
  },
  footer: {
    marginTop: Spacing.md,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  locationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  locationText: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.MediumGray,
  },
  rateContainer: {
    backgroundColor: Colors.PulsePurple + '10',
    paddingHorizontal: Spacing.sm,
    paddingVertical: 4,
    borderRadius: BorderRadius.sm,
  },
  rateText: {
    fontSize: Typography.sizes.tiny,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.PulsePurple,
  },
});

export default RequestCard;
