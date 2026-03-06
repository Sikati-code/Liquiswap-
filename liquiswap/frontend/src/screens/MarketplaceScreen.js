/**
 * Marketplace Screen
 * Browse and match exchange requests
 */

import React, { useEffect, useState, useCallback } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  FlatList,
  RefreshControl,
  Pressable,
  Modal,
} from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withTiming,
  withSpring,
  FadeIn,
  FadeInUp,
  SlideInDown,
  SlideOutUp,
} from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';
import { Ionicons } from '@expo/vector-icons';
import { Colors, Typography, Spacing, BorderRadius } from '../constants/theme';
import useRequestsStore from '../store/requestsStore';
import RequestCard from '../components/RequestCard';
import { ListSkeleton } from '../components/SkeletonLoader';

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

// Filter Modal
const FilterModal = ({ visible, onClose, filters, onApply }) => {
  const [localFilters, setLocalFilters] = useState(filters);

  const handleApply = () => {
    onApply(localFilters);
    onClose();
  };

  const handleClear = () => {
    setLocalFilters({ haveType: null, wantType: null });
  };

  return (
    <Modal
      visible={visible}
      transparent
      animationType="none"
      onRequestClose={onClose}
    >
      <View style={styles.modalOverlay}>
        <Animated.View 
          entering={SlideInDown}
          exiting={SlideOutUp}
          style={styles.modalContent}
        >
          <LinearGradient
            colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
            style={styles.modalGradient}
          >
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Filter Requests</Text>
              <Pressable onPress={onClose}>
                <Ionicons name="close" size={24} color={Colors.KribiWhite} />
              </Pressable>
            </View>

            <View style={styles.filterSection}>
              <Text style={styles.filterLabel}>They Have</Text>
              <View style={styles.filterOptions}>
                {['MTN', 'ORANGE', 'CASH'].map((type) => (
                  <Pressable
                    key={type}
                    onPress={() => setLocalFilters(f => ({ ...f, haveType: type }))}
                    style={[
                      styles.filterOption,
                      localFilters.haveType === type && styles.filterOptionActive,
                    ]}
                  >
                    <Text style={[
                      styles.filterOptionText,
                      localFilters.haveType === type && styles.filterOptionTextActive,
                    ]}>
                      {type}
                    </Text>
                  </Pressable>
                ))}
              </View>
            </View>

            <View style={styles.filterSection}>
              <Text style={styles.filterLabel}>They Want</Text>
              <View style={styles.filterOptions}>
                {['MTN', 'ORANGE', 'CASH'].map((type) => (
                  <Pressable
                    key={type}
                    onPress={() => setLocalFilters(f => ({ ...f, wantType: type }))}
                    style={[
                      styles.filterOption,
                      localFilters.wantType === type && styles.filterOptionActive,
                    ]}
                  >
                    <Text style={[
                      styles.filterOptionText,
                      localFilters.wantType === type && styles.filterOptionTextActive,
                    ]}>
                      {type}
                    </Text>
                  </Pressable>
                ))}
              </View>
            </View>

            <View style={styles.modalFooter}>
              <Pressable onPress={handleClear} style={styles.clearButton}>
                <Text style={styles.clearButtonText}>Clear</Text>
              </Pressable>
              <Pressable onPress={handleApply} style={styles.applyButton}>
                <LinearGradient
                  colors={[Colors.PulsePurple, '#7C3AED']}
                  style={styles.applyButtonGradient}
                >
                  <Text style={styles.applyButtonText}>Apply Filters</Text>
                </LinearGradient>
              </Pressable>
            </View>
          </LinearGradient>
        </Animated.View>
      </View>
    </Modal>
  );
};

const MarketplaceScreen = ({ navigation }) => {
  const [refreshing, setRefreshing] = useState(false);
  const [filterModalVisible, setFilterModalVisible] = useState(false);
  
  const { 
    marketplaceRequests, 
    isLoading, 
    hasMore, 
    filters,
    fetchMarketplace, 
    setFilters,
    matchRequest,
  } = useRequestsStore();

  useEffect(() => {
    fetchMarketplace(true);
  }, [filters]);

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchMarketplace(true);
    setRefreshing(false);
  };

  const onLoadMore = () => {
    if (hasMore && !isLoading) {
      fetchMarketplace();
    }
  };

  const handleRequestPress = (request) => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    // Navigate to request detail
  };

  const handleMatch = async (request) => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
    const result = await matchRequest(request.id);
    
    if (result.success) {
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      navigation.navigate('TransactionDetail', { 
        transactionId: result.transaction.id,
        isNewMatch: true 
      });
    } else {
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
    }
  };

  const handleApplyFilters = (newFilters) => {
    setFilters(newFilters);
  };

  const renderItem = ({ item, index }) => (
    <RequestCard
      request={item}
      onPress={handleRequestPress}
      onMatch={() => handleMatch(item)}
      index={index}
    />
  );

  const renderEmpty = () => (
    <View style={styles.emptyContainer}>
      <Ionicons name="search-outline" size={64} color={Colors.MediumGray} />
      <Text style={styles.emptyTitle}>No requests found</Text>
      <Text style={styles.emptySubtitle}>
        Try adjusting your filters or check back later
      </Text>
    </View>
  );

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
        style={styles.background}
      />

      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Marketplace</Text>
        <Pressable 
          onPress={() => setFilterModalVisible(true)}
          style={styles.filterButton}
        >
          <Ionicons name="options-outline" size={24} color={Colors.KribiWhite} />
          {(filters.haveType || filters.wantType) && (
            <View style={styles.filterBadge} />
          )}
        </Pressable>
      </View>

      {/* Stats Bar */}
      <View style={styles.statsBar}>
        <Text style={styles.statsText}>
          {marketplaceRequests.length} active requests
        </Text>
      </View>

      {/* List */}
      {isLoading && marketplaceRequests.length === 0 ? (
        <ListSkeleton count={5} />
      ) : (
        <FlatList
          data={marketplaceRequests}
          renderItem={renderItem}
          keyExtractor={(item) => item.id}
          contentContainerStyle={styles.listContent}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={onRefresh}
              tintColor={Colors.PulsePurple}
            />
          }
          onEndReached={onLoadMore}
          onEndReachedThreshold={0.5}
          ListEmptyComponent={renderEmpty}
        />
      )}

      {/* Filter Modal */}
      <FilterModal
        visible={filterModalVisible}
        onClose={() => setFilterModalVisible(false)}
        filters={filters}
        onApply={handleApplyFilters}
      />
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
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: Spacing.lg,
    paddingTop: 60,
    paddingBottom: Spacing.md,
  },
  headerTitle: {
    fontSize: Typography.sizes.h1,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  filterButton: {
    padding: Spacing.sm,
    position: 'relative',
  },
  filterBadge: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: Colors.PulsePurple,
  },
  statsBar: {
    paddingHorizontal: Spacing.lg,
    paddingBottom: Spacing.md,
  },
  statsText: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
  },
  listContent: {
    paddingTop: Spacing.md,
    paddingBottom: Spacing.xxl,
  },
  emptyContainer: {
    alignItems: 'center',
    paddingTop: Spacing.xxxl,
    paddingHorizontal: Spacing.xl,
  },
  emptyTitle: {
    fontSize: Typography.sizes.h3,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    marginTop: Spacing.lg,
  },
  emptySubtitle: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    textAlign: 'center',
    marginTop: Spacing.sm,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    borderTopLeftRadius: BorderRadius.xxl,
    borderTopRightRadius: BorderRadius.xxl,
    overflow: 'hidden',
    maxHeight: '80%',
  },
  modalGradient: {
    padding: Spacing.xl,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.xl,
  },
  modalTitle: {
    fontSize: Typography.sizes.h2,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  filterSection: {
    marginBottom: Spacing.xl,
  },
  filterLabel: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '80',
    marginBottom: Spacing.md,
  },
  filterOptions: {
    flexDirection: 'row',
    gap: Spacing.sm,
  },
  filterOption: {
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    borderRadius: BorderRadius.lg,
    backgroundColor: Colors.KribiWhite + '10',
    borderWidth: 1,
    borderColor: Colors.KribiWhite + '20',
  },
  filterOptionActive: {
    backgroundColor: Colors.PulsePurple + '30',
    borderColor: Colors.PulsePurple,
  },
  filterOptionText: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '60',
  },
  filterOptionTextActive: {
    color: Colors.PulsePurple,
  },
  modalFooter: {
    flexDirection: 'row',
    gap: Spacing.md,
    marginTop: Spacing.lg,
  },
  clearButton: {
    flex: 1,
    paddingVertical: Spacing.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
  clearButtonText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '60',
  },
  applyButton: {
    flex: 2,
    borderRadius: BorderRadius.xxl,
    overflow: 'hidden',
  },
  applyButtonGradient: {
    paddingVertical: Spacing.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
  applyButtonText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
});

export default MarketplaceScreen;
