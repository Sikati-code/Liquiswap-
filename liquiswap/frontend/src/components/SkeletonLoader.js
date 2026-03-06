/**
 * Skeleton Loader Component
 * Shimmering placeholder for loading states
 */

import React from 'react';
import { View, StyleSheet, Dimensions } from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withRepeat,
  withTiming,
  interpolate,
} from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import { Colors, Spacing, BorderRadius } from '../constants/theme';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

const SkeletonItem = ({ width, height, borderRadius = BorderRadius.md, style = {} }) => {
  const shimmerTranslate = useSharedValue(-SCREEN_WIDTH);

  React.useEffect(() => {
    shimmerTranslate.value = withRepeat(
      withTiming(SCREEN_WIDTH, { duration: 1500 }),
      -1,
      false
    );
  }, []);

  const shimmerStyle = useAnimatedStyle(() => ({
    transform: [{ translateX: shimmerTranslate.value }],
  }));

  return (
    <View
      style={[
        styles.skeletonItem,
        {
          width,
          height,
          borderRadius,
        },
        style,
      ]}
    >
      <Animated.View style={[styles.shimmer, shimmerStyle]}>
        <LinearGradient
          colors={['transparent', 'rgba(255,255,255,0.5)', 'transparent']}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 0 }}
          style={styles.shimmerGradient}
        />
      </Animated.View>
    </View>
  );
};

// Balance Card Skeleton
export const BalanceCardSkeleton = () => (
  <View style={styles.balanceCardContainer}>
    <SkeletonItem width={280} height={160} borderRadius={BorderRadius.xl} />
  </View>
);

// Request Card Skeleton
export const RequestCardSkeleton = () => (
  <View style={styles.requestCardContainer}>
    <View style={styles.requestCardHeader}>
      <SkeletonItem width={40} height={40} borderRadius={BorderRadius.full} />
      <View style={styles.requestCardHeaderText}>
        <SkeletonItem width={120} height={16} />
        <SkeletonItem width={60} height={12} style={{ marginTop: Spacing.xs }} />
      </View>
    </View>
    <View style={styles.requestCardBody}>
      <SkeletonItem width="100%" height={80} borderRadius={BorderRadius.lg} />
    </View>
  </View>
);

// Transaction Item Skeleton
export const TransactionItemSkeleton = () => (
  <View style={styles.transactionItemContainer}>
    <SkeletonItem width={48} height={48} borderRadius={BorderRadius.full} />
    <View style={styles.transactionItemText}>
      <SkeletonItem width={150} height={16} />
      <SkeletonItem width={80} height={12} style={{ marginTop: Spacing.xs }} />
    </View>
    <SkeletonItem width={80} height={20} borderRadius={BorderRadius.sm} />
  </View>
);

// Dashboard Skeleton
export const DashboardSkeleton = () => (
  <View style={styles.dashboardContainer}>
    {/* Header Skeleton */}
    <View style={styles.dashboardHeader}>
      <SkeletonItem width={50} height={50} borderRadius={BorderRadius.full} />
      <View style={styles.dashboardHeaderText}>
        <SkeletonItem width={100} height={14} />
        <SkeletonItem width={150} height={20} style={{ marginTop: Spacing.xs }} />
      </View>
    </View>

    {/* Balance Cards Skeleton */}
    <View style={styles.balanceCardsRow}>
      <BalanceCardSkeleton />
      <BalanceCardSkeleton />
    </View>

    {/* Quick Actions Skeleton */}
    <View style={styles.quickActionsContainer}>
      <SkeletonItem width={64} height={64} borderRadius={BorderRadius.full} />
      <SkeletonItem width={64} height={64} borderRadius={BorderRadius.full} />
      <SkeletonItem width={64} height={64} borderRadius={BorderRadius.full} />
      <SkeletonItem width={64} height={64} borderRadius={BorderRadius.full} />
    </View>

    {/* Recent Transactions Skeleton */}
    <View style={styles.transactionsContainer}>
      <SkeletonItem width={150} height={20} style={{ marginBottom: Spacing.md }} />
      <TransactionItemSkeleton />
      <TransactionItemSkeleton />
      <TransactionItemSkeleton />
    </View>
  </View>
);

// List Skeleton
export const ListSkeleton = ({ count = 5 }) => (
  <View style={styles.listContainer}>
    {Array.from({ length: count }).map((_, index) => (
      <RequestCardSkeleton key={index} />
    ))}
  </View>
);

const styles = StyleSheet.create({
  skeletonItem: {
    backgroundColor: Colors.LightGray,
    overflow: 'hidden',
  },
  shimmer: {
    width: SCREEN_WIDTH,
    height: '100%',
  },
  shimmerGradient: {
    width: 100,
    height: '100%',
  },
  // Balance Card
  balanceCardContainer: {
    marginRight: Spacing.md,
  },
  // Request Card
  requestCardContainer: {
    backgroundColor: Colors.KribiWhite,
    borderRadius: BorderRadius.xl,
    padding: Spacing.lg,
    marginHorizontal: Spacing.lg,
    marginBottom: Spacing.md,
    ...Shadows.sm,
  },
  requestCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: Spacing.md,
  },
  requestCardHeaderText: {
    marginLeft: Spacing.sm,
    flex: 1,
  },
  requestCardBody: {
    marginTop: Spacing.sm,
  },
  // Transaction Item
  transactionItemContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: Spacing.md,
    paddingHorizontal: Spacing.lg,
  },
  transactionItemText: {
    flex: 1,
    marginLeft: Spacing.md,
  },
  // Dashboard
  dashboardContainer: {
    flex: 1,
    paddingTop: Spacing.lg,
  },
  dashboardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: Spacing.lg,
    marginBottom: Spacing.lg,
  },
  dashboardHeaderText: {
    marginLeft: Spacing.md,
  },
  balanceCardsRow: {
    flexDirection: 'row',
    paddingLeft: Spacing.lg,
    marginBottom: Spacing.lg,
  },
  quickActionsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    paddingHorizontal: Spacing.lg,
    marginBottom: Spacing.lg,
  },
  transactionsContainer: {
    flex: 1,
    paddingHorizontal: Spacing.lg,
  },
  // List
  listContainer: {
    paddingTop: Spacing.md,
  },
});

export default SkeletonItem;
