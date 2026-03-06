/**
 * Home Screen (Dashboard)
 * Main dashboard with balances, quick actions, and recent transactions
 */

import React, { useEffect, useCallback, useState } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  ScrollView,
  RefreshControl,
  Pressable,
  FlatList,
} from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withTiming,
  withSpring,
  withDelay,
  withSequence,
  interpolate,
  FadeIn,
  FadeInDown,
  FadeInRight,
  FadeInUp,
  SlideInRight,
} from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';
import { Colors, Typography, Spacing, BorderRadius, Shadows } from '../constants/theme';
import { formatCurrency, getCurrencyColor } from '../constants/theme';
import useAuthStore from '../store/authStore';
import useRequestsStore from '../store/requestsStore';
import useTransactionsStore from '../store/transactionsStore';
import BalanceCard from '../components/BalanceCard';
import { DashboardSkeleton } from '../components/SkeletonLoader';
import { Ionicons } from '@expo/vector-icons';

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

// Quick Action Button
const QuickActionButton = ({ icon, label, onPress, color, delay }) => {
  const scale = useSharedValue(1);

  const handlePressIn = () => {
    scale.value = withSpring(0.9, { damping: 15 });
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
  };

  const handlePressOut = () => {
    scale.value = withSpring(1, { damping: 15 });
  };

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
  }));

  return (
    <Animated.View entering={FadeInUp.delay(delay).duration(400)}>
      <AnimatedPressable
        onPressIn={handlePressIn}
        onPressOut={handlePressOut}
        onPress={onPress}
        style={[styles.quickActionButton, animatedStyle]}
      >
        <View style={[styles.quickActionIcon, { backgroundColor: color + '20' }]}>
          <Ionicons name={icon} size={24} color={color} />
        </View>
        <Text style={styles.quickActionLabel}>{label}</Text>
      </AnimatedPressable>
    </Animated.View>
  );
};

// Transaction Item
const TransactionItem = ({ transaction, onPress, index }) => {
  const isSender = transaction.senderId === useAuthStore.getState().user?.id;
  const otherParty = isSender ? transaction.receiver : transaction.sender;
  
  const getStatusColor = (status) => {
    switch (status) {
      case 'COMPLETED':
        return Colors.SuccessGreen;
      case 'PENDING':
        return Colors.WarningYellow;
      case 'DISPUTED':
        return Colors.ErrorRed;
      default:
        return Colors.MediumGray;
    }
  };

  const getStatusText = (status) => {
    switch (status) {
      case 'COMPLETED':
        return 'Completed';
      case 'PENDING':
        return 'Pending';
      case 'IN_PROGRESS':
        return 'In Progress';
      case 'DISPUTED':
        return 'Disputed';
      default:
        return status;
    }
  };

  return (
    <Animated.View entering={FadeInUp.delay(400 + index * 50).duration(400)}>
      <Pressable onPress={onPress} style={styles.transactionItem}>
        <View style={styles.transactionAvatar}>
          <Text style={styles.transactionAvatarText}>
            {otherParty?.name?.charAt(0) || 'U'}
          </Text>
        </View>
        <View style={styles.transactionInfo}>
          <Text style={styles.transactionName} numberOfLines={1}>
            {otherParty?.name || 'Unknown'}
          </Text>
          <View style={[styles.statusBadge, { backgroundColor: getStatusColor(transaction.status) + '20' }]}>
            <Text style={[styles.statusText, { color: getStatusColor(transaction.status) }]}>
              {getStatusText(transaction.status)}
            </Text>
          </View>
        </View>
        <View style={styles.transactionAmount}>
          <Text style={styles.amountText}>
            {isSender ? '-' : '+'}{formatCurrency(transaction.senderAmount)}
          </Text>
          <Text style={styles.currencyType}>
            {transaction.request?.haveType}
          </Text>
        </View>
      </Pressable>
    </Animated.View>
  );
};

const HomeScreen = ({ navigation }) => {
  const [refreshing, setRefreshing] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  const { user, fetchProfile } = useAuthStore();
  const { fetchMyRequests } = useRequestsStore();
  const { transactions, fetchTransactions } = useTransactionsStore();

  // Animation values
  const headerOpacity = useSharedValue(0);
  const cardsTranslateX = useSharedValue(50);
  const actionsScale = useSharedValue(0);
  const listOpacity = useSharedValue(0);

  useEffect(() => {
    loadData();
    
    // Staggered animations
    headerOpacity.value = withTiming(1, { duration: 400 });
    cardsTranslateX.value = withDelay(100, withSpring(0, { damping: 15 }));
    actionsScale.value = withDelay(300, withSpring(1, { damping: 12 }));
    listOpacity.value = withDelay(400, withTiming(1, { duration: 400 }));
  }, []);

  const loadData = async () => {
    setIsLoading(true);
    await Promise.all([
      fetchProfile(),
      fetchTransactions(),
      fetchMyRequests(),
    ]);
    setIsLoading(false);
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadData();
    setRefreshing(false);
  };

  const handleCreateRequest = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
    navigation.navigate('CreateRequest');
  };

  const handleViewMarketplace = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    navigation.navigate('Marketplace');
  };

  const handleViewHistory = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    navigation.navigate('History');
  };

  const handleViewProfile = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    navigation.navigate('Profile');
  };

  const handleTransactionPress = (transaction) => {
    navigation.navigate('TransactionDetail', { transactionId: transaction.id });
  };

  const handleBalanceCardPress = (type) => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    // Could show balance details or quick actions
  };

  if (isLoading) {
    return (
      <View style={styles.container}>
        <LinearGradient
          colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
          style={styles.background}
        />
        <DashboardSkeleton />
      </View>
    );
  }

  const balances = user?.balances || [];
  const recentTransactions = transactions.slice(0, 5);

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
        style={styles.background}
      />

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={Colors.PulsePurple}
          />
        }
      >
        {/* Header */}
        <Animated.View 
          style={[
            styles.header,
            { opacity: headerOpacity }
          ]}
        >
          <View style={styles.headerLeft}>
            <View style={styles.greetingContainer}>
              <Text style={styles.greeting}>Good day,</Text>
              <Text style={styles.userName}>{user?.name || 'User'}</Text>
            </View>
          </View>
          <Pressable onPress={handleViewProfile} style={styles.avatarButton}>
            <View style={styles.avatar}>
              <Text style={styles.avatarText}>
                {user?.name?.charAt(0) || 'U'}
              </Text>
            </View>
          </Pressable>
        </Animated.View>

        {/* Balance Cards */}
        <Animated.View 
          style={[
            styles.cardsContainer,
            { transform: [{ translateX: cardsTranslateX }] }
          ]}
        >
          <FlatList
            data={balances}
            horizontal
            showsHorizontalScrollIndicator={false}
            keyExtractor={(item) => item.id}
            renderItem={({ item, index }) => (
              <BalanceCard
                type={item.type}
                amount={item.amount}
                phoneNumber={item.phoneNumber}
                onPress={handleBalanceCardPress}
                index={index}
              />
            )}
            contentContainerStyle={styles.cardsList}
          />
        </Animated.View>

        {/* Quick Actions */}
        <Animated.View 
          style={[
            styles.quickActionsContainer,
            { transform: [{ scale: actionsScale }] }
          ]}
        >
          <QuickActionButton
            icon="swap-horizontal"
            label="Swap"
            onPress={handleCreateRequest}
            color={Colors.PulsePurple}
            delay={300}
          />
          <QuickActionButton
            icon="storefront"
            label="Market"
            onPress={handleViewMarketplace}
            color={Colors.CashGreen}
            delay={350}
          />
          <QuickActionButton
            icon="time"
            label="History"
            onPress={handleViewHistory}
            color={Colors.YelloGold}
            delay={400}
          />
          <QuickActionButton
            icon="person"
            label="Profile"
            onPress={handleViewProfile}
            color={Colors.CitrusOrange}
            delay={450}
          />
        </Animated.View>

        {/* Recent Transactions */}
        <Animated.View 
          style={[
            styles.transactionsContainer,
            { opacity: listOpacity }
          ]}
        >
          <View style={styles.transactionsHeader}>
            <Text style={styles.transactionsTitle}>Recent Activity</Text>
            <Pressable onPress={handleViewHistory}>
              <Text style={styles.seeAllText}>See All</Text>
            </Pressable>
          </View>

          {recentTransactions.length > 0 ? (
            recentTransactions.map((transaction, index) => (
              <TransactionItem
                key={transaction.id}
                transaction={transaction}
                onPress={() => handleTransactionPress(transaction)}
                index={index}
              />
            ))
          ) : (
            <View style={styles.emptyState}>
              <Ionicons name="document-text-outline" size={48} color={Colors.MediumGray} />
              <Text style={styles.emptyStateText}>No transactions yet</Text>
              <Text style={styles.emptyStateSubtext}>
                Start by creating your first exchange request
              </Text>
            </View>
          )}
        </Animated.View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  background: {
    ...StyleSheet.absoluteFillObject,
  },
  scrollContent: {
    paddingTop: 60,
    paddingBottom: Spacing.xxl,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: Spacing.lg,
    marginBottom: Spacing.lg,
  },
  headerLeft: {
    flex: 1,
  },
  greetingContainer: {},
  greeting: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '80',
  },
  userName: {
    fontSize: Typography.sizes.h2,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    marginTop: 2,
  },
  avatarButton: {
    marginLeft: Spacing.md,
  },
  avatar: {
    width: 48,
    height: 48,
    borderRadius: BorderRadius.full,
    backgroundColor: Colors.PulsePurple,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    fontSize: 20,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  cardsContainer: {
    marginBottom: Spacing.lg,
  },
  cardsList: {
    paddingRight: Spacing.lg,
  },
  quickActionsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    paddingHorizontal: Spacing.lg,
    marginBottom: Spacing.xl,
  },
  quickActionButton: {
    alignItems: 'center',
  },
  quickActionIcon: {
    width: 64,
    height: 64,
    borderRadius: BorderRadius.full,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Spacing.sm,
  },
  quickActionLabel: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite,
  },
  transactionsContainer: {
    flex: 1,
    paddingHorizontal: Spacing.lg,
  },
  transactionsHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.md,
  },
  transactionsTitle: {
    fontSize: Typography.sizes.h3,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  seeAllText: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.PulsePurple,
  },
  transactionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.KribiWhite + '08',
    borderRadius: BorderRadius.lg,
    padding: Spacing.md,
    marginBottom: Spacing.sm,
  },
  transactionAvatar: {
    width: 48,
    height: 48,
    borderRadius: BorderRadius.full,
    backgroundColor: Colors.PulsePurple + '30',
    alignItems: 'center',
    justifyContent: 'center',
  },
  transactionAvatarText: {
    fontSize: 18,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.PulsePurple,
  },
  transactionInfo: {
    flex: 1,
    marginLeft: Spacing.md,
  },
  transactionName: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.semiBold,
    color: Colors.KribiWhite,
    marginBottom: 4,
  },
  statusBadge: {
    paddingHorizontal: Spacing.sm,
    paddingVertical: 2,
    borderRadius: BorderRadius.sm,
    alignSelf: 'flex-start',
  },
  statusText: {
    fontSize: Typography.sizes.tiny,
    fontFamily: Typography.fontFamily.medium,
  },
  transactionAmount: {
    alignItems: 'flex-end',
  },
  amountText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  currencyType: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    marginTop: 2,
  },
  emptyState: {
    alignItems: 'center',
    paddingVertical: Spacing.xl,
  },
  emptyStateText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '60',
    marginTop: Spacing.md,
  },
  emptyStateSubtext: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '40',
    marginTop: Spacing.xs,
    textAlign: 'center',
  },
});

export default HomeScreen;
