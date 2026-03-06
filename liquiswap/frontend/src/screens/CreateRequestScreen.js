/**
 * Create Request Screen
 * Multi-step modal for creating exchange requests
 */

import React, { useState, useCallback } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  TextInput,
  ScrollView,
  Pressable,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withTiming,
  withSpring,
  interpolate,
  FadeIn,
  FadeInRight,
  FadeOutLeft,
  SlideInRight,
  SlideOutLeft,
} from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';
import { Ionicons } from '@expo/vector-icons';
import { Colors, Typography, Spacing, BorderRadius, Shadows } from '../constants/theme';
import { formatCurrency, getCurrencyColor, getCurrencyTextColor } from '../constants/theme';
import useRequestsStore from '../store/requestsStore';
import SwapButton from '../components/SwapButton';

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

const CURRENCY_TYPES = [
  { type: 'MTN', label: 'MTN Mobile Money', color: Colors.YelloGold, textColor: Colors.LiquiSwapNavy },
  { type: 'ORANGE', label: 'Orange Money', color: Colors.CitrusOrange, textColor: Colors.KribiWhite },
  { type: 'CASH', label: 'Cash', color: Colors.CashGreen, textColor: Colors.KribiWhite },
];

// Step 1: Select "Have" Currency
const HaveStep = ({ selected, onSelect }) => {
  return (
    <Animated.View entering={FadeInRight.duration(400)} style={styles.stepContainer}>
      <Text style={styles.stepTitle}>What do you have?</Text>
      <Text style={styles.stepSubtitle}>Select the currency you want to exchange</Text>
      
      <View style={styles.currencyGrid}>
        {CURRENCY_TYPES.map((currency, index) => (
          <AnimatedPressable
            key={currency.type}
            onPress={() => onSelect(currency.type)}
            style={[
              styles.currencyCard,
              { 
                backgroundColor: currency.color,
                borderWidth: selected === currency.type ? 3 : 0,
                borderColor: Colors.KribiWhite,
              },
            ]}
          >
            <Text style={[styles.currencyCardIcon, { color: currency.textColor }]}>
              {currency.type[0]}
            </Text>
            <Text style={[styles.currencyCardLabel, { color: currency.textColor }]}>
              {currency.label}
            </Text>
            {selected === currency.type && (
              <View style={styles.checkmark}>
                <Ionicons name="checkmark-circle" size={24} color={currency.textColor} />
              </View>
            )}
          </AnimatedPressable>
        ))}
      </View>
    </Animated.View>
  );
};

// Step 2: Enter Amounts
const AmountStep = ({ haveType, haveAmount, wantAmount, onHaveAmountChange, onWantAmountChange }) => {
  const haveCurrency = CURRENCY_TYPES.find(c => c.type === haveType);

  return (
    <Animated.View entering={FadeInRight.duration(400)} style={styles.stepContainer}>
      <Text style={styles.stepTitle}>How much?</Text>
      <Text style={styles.stepSubtitle}>Enter the amounts for your exchange</Text>
      
      {/* Have Amount */}
      <View style={styles.amountInputContainer}>
        <Text style={styles.amountLabel}>You have</Text>
        <View style={[styles.amountInputWrapper, { backgroundColor: haveCurrency?.color + '20' }]}>
          <View style={[styles.currencyBadge, { backgroundColor: haveCurrency?.color }]}>
            <Text style={[styles.currencyBadgeText, { color: haveCurrency?.textColor }]}>
              {haveType}
            </Text>
          </View>
          <TextInput
            style={styles.amountInput}
            placeholder="0"
            placeholderTextColor={Colors.MediumGray}
            keyboardType="number-pad"
            value={haveAmount}
            onChangeText={onHaveAmountChange}
            autoFocus
          />
        </View>
      </View>

      {/* Exchange Arrow */}
      <View style={styles.exchangeArrow}>
        <View style={styles.arrowCircle}>
          <Ionicons name="arrow-down" size={24} color={Colors.PulsePurple} />
        </View>
      </View>

      {/* Want Amount */}
      <View style={styles.amountInputContainer}>
        <Text style={styles.amountLabel}>You want</Text>
        <View style={[styles.amountInputWrapper, { backgroundColor: Colors.PulsePurple + '10' }]}>
          <View style={[styles.currencyBadge, { backgroundColor: Colors.PulsePurple }]}>
            <Text style={[styles.currencyBadgeText, { color: Colors.KribiWhite }]}>
              ?
            </Text>
          </View>
          <TextInput
            style={styles.amountInput}
            placeholder="0"
            placeholderTextColor={Colors.MediumGray}
            keyboardType="number-pad"
            value={wantAmount}
            onChangeText={onWantAmountChange}
          />
        </View>
      </View>

      {/* Exchange Rate Preview */}
      {haveAmount && wantAmount && (
        <Animated.View entering={FadeIn} style={styles.ratePreview}>
          <Text style={styles.rateText}>
            Rate: 1 {haveType} = {(parseFloat(wantAmount) / parseFloat(haveAmount)).toFixed(2)} ?
          </Text>
        </Animated.View>
      )}
    </Animated.View>
  );
};

// Step 3: Select "Want" Currency
const WantStep = ({ haveType, selected, onSelect }) => {
  const availableCurrencies = CURRENCY_TYPES.filter(c => c.type !== haveType);

  return (
    <Animated.View entering={FadeInRight.duration(400)} style={styles.stepContainer}>
      <Text style={styles.stepTitle}>What do you want?</Text>
      <Text style={styles.stepSubtitle}>Select the currency you want to receive</Text>
      
      <View style={styles.currencyGrid}>
        {availableCurrencies.map((currency) => (
          <AnimatedPressable
            key={currency.type}
            onPress={() => onSelect(currency.type)}
            style={[
              styles.currencyCard,
              { 
                backgroundColor: currency.color,
                borderWidth: selected === currency.type ? 3 : 0,
                borderColor: Colors.KribiWhite,
              },
            ]}
          >
            <Text style={[styles.currencyCardIcon, { color: currency.textColor }]}>
              {currency.type[0]}
            </Text>
            <Text style={[styles.currencyCardLabel, { color: currency.textColor }]}>
              {currency.label}
            </Text>
            {selected === currency.type && (
              <View style={styles.checkmark}>
                <Ionicons name="checkmark-circle" size={24} color={currency.textColor} />
              </View>
            )}
          </AnimatedPressable>
        ))}
      </View>
    </Animated.View>
  );
};

// Step 4: Review
const ReviewStep = ({ haveType, wantType, haveAmount, wantAmount }) => {
  const haveCurrency = CURRENCY_TYPES.find(c => c.type === haveType);
  const wantCurrency = CURRENCY_TYPES.find(c => c.type === wantType);

  return (
    <Animated.View entering={FadeInRight.duration(400)} style={styles.stepContainer}>
      <Text style={styles.stepTitle}>Review</Text>
      <Text style={styles.stepSubtitle}>Confirm your exchange details</Text>
      
      <View style={styles.reviewCard}>
        {/* Have Section */}
        <View style={styles.reviewSection}>
          <Text style={styles.reviewLabel}>You give</Text>
          <View style={styles.reviewAmountRow}>
            <View style={[styles.reviewCurrencyBadge, { backgroundColor: haveCurrency?.color }]}>
              <Text style={[styles.reviewCurrencyText, { color: haveCurrency?.textColor }]}>
                {haveType}
              </Text>
            </View>
            <Text style={styles.reviewAmount}>{formatCurrency(haveAmount)}</Text>
          </View>
        </View>

        {/* Arrow */}
        <View style={styles.reviewArrow}>
          <Ionicons name="arrow-down" size={32} color={Colors.PulsePurple} />
        </View>

        {/* Want Section */}
        <View style={styles.reviewSection}>
          <Text style={styles.reviewLabel}>You receive</Text>
          <View style={styles.reviewAmountRow}>
            <View style={[styles.reviewCurrencyBadge, { backgroundColor: wantCurrency?.color }]}>
              <Text style={[styles.reviewCurrencyText, { color: wantCurrency?.textColor }]}>
                {wantType}
              </Text>
            </View>
            <Text style={styles.reviewAmount}>{formatCurrency(wantAmount)}</Text>
          </View>
        </View>

        {/* Rate */}
        <View style={styles.rateContainer}>
          <Text style={styles.rateLabel}>Exchange Rate</Text>
          <Text style={styles.rateValue}>
            1 {haveType} = {(parseFloat(wantAmount) / parseFloat(haveAmount)).toFixed(4)} {wantType}
          </Text>
        </View>
      </View>
    </Animated.View>
  );
};

const CreateRequestScreen = ({ navigation }) => {
  const [step, setStep] = useState(1);
  const [haveType, setHaveType] = useState('');
  const [wantType, setWantType] = useState('');
  const [haveAmount, setHaveAmount] = useState('');
  const [wantAmount, setWantAmount] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const { createRequest } = useRequestsStore();

  const handleNext = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    if (step < 4) {
      setStep(step + 1);
    } else {
      handleSubmit();
    }
  };

  const handleBack = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    if (step > 1) {
      setStep(step - 1);
    } else {
      navigation.goBack();
    }
  };

  const handleSubmit = async () => {
    setIsLoading(true);
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);

    const result = await createRequest({
      haveType,
      wantType,
      haveAmount: parseFloat(haveAmount),
      wantAmount: parseFloat(wantAmount),
      expiresInMinutes: 60,
    });

    setIsLoading(false);

    if (result.success) {
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      if (result.matched) {
        navigation.replace('TransactionDetail', { 
          transactionId: result.transaction.id,
          isNewMatch: true 
        });
      } else {
        navigation.goBack();
      }
    } else {
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
      // Show error
    }
  };

  const canProceed = () => {
    switch (step) {
      case 1:
        return haveType !== '';
      case 2:
        return haveAmount !== '' && wantAmount !== '' && parseFloat(haveAmount) > 0 && parseFloat(wantAmount) > 0;
      case 3:
        return wantType !== '';
      case 4:
        return true;
      default:
        return false;
    }
  };

  const renderStep = () => {
    switch (step) {
      case 1:
        return <HaveStep selected={haveType} onSelect={setHaveType} />;
      case 2:
        return (
          <AmountStep
            haveType={haveType}
            haveAmount={haveAmount}
            wantAmount={wantAmount}
            onHaveAmountChange={setHaveAmount}
            onWantAmountChange={setWantAmount}
          />
        );
      case 3:
        return <WantStep haveType={haveType} selected={wantType} onSelect={setWantType} />;
      case 4:
        return (
          <ReviewStep
            haveType={haveType}
            wantType={wantType}
            haveAmount={haveAmount}
            wantAmount={wantAmount}
          />
        );
      default:
        return null;
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      style={styles.container}
    >
      <LinearGradient
        colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
        style={styles.background}
      />

      {/* Header */}
      <View style={styles.header}>
        <Pressable onPress={handleBack} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color={Colors.KribiWhite} />
        </Pressable>
        <Text style={styles.headerTitle}>Create Request</Text>
        <View style={styles.placeholder} />
      </View>

      {/* Progress Bar */}
      <View style={styles.progressContainer}>
        {[1, 2, 3, 4].map((s) => (
          <View
            key={s}
            style={[
              styles.progressDot,
              s <= step && styles.progressDotActive,
            ]}
          />
        ))}
      </View>

      {/* Content */}
      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled"
      >
        {renderStep()}
      </ScrollView>

      {/* Footer */}
      <View style={styles.footer}>
        <SwapButton
          title={step === 4 ? 'CONFIRM SWAP' : 'Continue'}
          onPress={handleNext}
          disabled={!canProceed()}
          loading={isLoading}
        />
      </View>
    </KeyboardAvoidingView>
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
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.lg,
    paddingTop: 60,
    paddingBottom: Spacing.md,
  },
  backButton: {
    padding: Spacing.sm,
  },
  headerTitle: {
    fontSize: Typography.sizes.h3,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  placeholder: {
    width: 40,
  },
  progressContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: Spacing.md,
  },
  progressDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: Colors.KribiWhite + '30',
    marginHorizontal: 4,
  },
  progressDotActive: {
    width: 24,
    backgroundColor: Colors.PulsePurple,
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: Spacing.lg,
  },
  stepContainer: {
    flex: 1,
    paddingTop: Spacing.lg,
  },
  stepTitle: {
    fontSize: Typography.sizes.h1,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    marginBottom: Spacing.sm,
  },
  stepSubtitle: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    marginBottom: Spacing.xl,
  },
  currencyGrid: {
    gap: Spacing.md,
  },
  currencyCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Spacing.lg,
    borderRadius: BorderRadius.xl,
    marginBottom: Spacing.md,
    position: 'relative',
  },
  currencyCardIcon: {
    fontSize: 24,
    fontFamily: Typography.fontFamily.bold,
    width: 40,
    height: 40,
    textAlign: 'center',
    lineHeight: 40,
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: BorderRadius.lg,
    marginRight: Spacing.md,
  },
  currencyCardLabel: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.semiBold,
    flex: 1,
  },
  checkmark: {
    position: 'absolute',
    right: Spacing.lg,
  },
  amountInputContainer: {
    marginBottom: Spacing.lg,
  },
  amountLabel: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '60',
    marginBottom: Spacing.sm,
  },
  amountInputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: BorderRadius.lg,
    padding: Spacing.md,
  },
  currencyBadge: {
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    borderRadius: BorderRadius.md,
    marginRight: Spacing.md,
  },
  currencyBadgeText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
  },
  amountInput: {
    flex: 1,
    fontSize: 28,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  exchangeArrow: {
    alignItems: 'center',
    marginVertical: Spacing.md,
  },
  arrowCircle: {
    width: 48,
    height: 48,
    borderRadius: BorderRadius.full,
    backgroundColor: Colors.PulsePurple + '20',
    alignItems: 'center',
    justifyContent: 'center',
  },
  ratePreview: {
    backgroundColor: Colors.PulsePurple + '10',
    padding: Spacing.md,
    borderRadius: BorderRadius.lg,
    alignItems: 'center',
    marginTop: Spacing.lg,
  },
  rateText: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.PulsePurple,
  },
  reviewCard: {
    backgroundColor: Colors.KribiWhite + '08',
    borderRadius: BorderRadius.xl,
    padding: Spacing.xl,
  },
  reviewSection: {
    marginBottom: Spacing.md,
  },
  reviewLabel: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '60',
    marginBottom: Spacing.sm,
  },
  reviewAmountRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  reviewCurrencyBadge: {
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    borderRadius: BorderRadius.md,
    marginRight: Spacing.md,
  },
  reviewCurrencyText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
  },
  reviewAmount: {
    fontSize: 28,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  reviewArrow: {
    alignItems: 'center',
    marginVertical: Spacing.lg,
  },
  rateContainer: {
    borderTopWidth: 1,
    borderTopColor: Colors.KribiWhite + '10',
    paddingTop: Spacing.lg,
    marginTop: Spacing.md,
  },
  rateLabel: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    marginBottom: Spacing.xs,
  },
  rateValue: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.semiBold,
    color: Colors.KribiWhite,
  },
  footer: {
    padding: Spacing.lg,
    paddingBottom: Spacing.xxl,
  },
});

export default CreateRequestScreen;
