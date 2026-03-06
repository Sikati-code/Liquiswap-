/**
 * Login Screen
 * Phone number input with country code
 */

import React, { useState, useCallback } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  TextInput,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Alert,
} from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  withTiming,
  interpolate,
  FadeIn,
  FadeInUp,
} from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';
import { Colors, Typography, Spacing, BorderRadius, Animations } from '../constants/theme';
import AnimatedButton from '../components/AnimatedButton';
import api from '../utils/api';

const LoginScreen = ({ navigation }) => {
  const [phone, setPhone] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const validatePhone = (number) => {
    // Cameroon phone number validation (starts with 6, 9 digits)
    const cameroonRegex = /^6[0-9]{8}$/;
    return cameroonRegex.test(number);
  };

  const handlePhoneChange = (text) => {
    // Remove non-digits
    const cleaned = text.replace(/\D/g, '');
    // Limit to 9 digits
    const trimmed = cleaned.slice(0, 9);
    setPhone(trimmed);
    setError('');
  };

  const handleContinue = async () => {
    if (!validatePhone(phone)) {
      setError('Please enter a valid Cameroon phone number');
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
      return;
    }

    setIsLoading(true);
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);

    try {
      // Request OTP
      await api.post('/auth/send-otp', { phone });
      
      // Navigate to OTP screen
      navigation.navigate('OTP', { phone });
    } catch (error) {
      console.log('Send OTP error:', error);
      setError(error.response?.data?.message || 'Failed to send OTP');
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
    } finally {
      setIsLoading(false);
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

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled"
      >
        {/* Header */}
        <Animated.View 
          entering={FadeInUp.delay(100).duration(500)}
          style={styles.header}
        >
          <Text style={styles.title}>Welcome to LiquiSwap</Text>
          <Text style={styles.subtitle}>
            Enter your phone number to get started
          </Text>
        </Animated.View>

        {/* Phone Input */}
        <Animated.View 
          entering={FadeInUp.delay(200).duration(500)}
          style={styles.inputContainer}
        >
          <View style={styles.phoneInputWrapper}>
            <View style={styles.countryCode}>
              <Text style={styles.countryCodeText}>+237</Text>
            </View>
            <TextInput
              style={styles.phoneInput}
              placeholder="6XX XXX XXX"
              placeholderTextColor={Colors.MediumGray}
              keyboardType="phone-pad"
              value={phone}
              onChangeText={handlePhoneChange}
              maxLength={9}
              autoFocus
            />
          </View>

          {error ? (
            <Animated.Text 
              entering={FadeIn}
              style={styles.errorText}
            >
              {error}
            </Animated.Text>
          ) : null}
        </Animated.View>

        {/* Info Text */}
        <Animated.View 
          entering={FadeInUp.delay(300).duration(500)}
          style={styles.infoContainer}
        >
          <Text style={styles.infoText}>
            We'll send you a verification code via SMS
          </Text>
        </Animated.View>

        {/* Continue Button */}
        <Animated.View 
          entering={FadeInUp.delay(400).duration(500)}
          style={styles.buttonContainer}
        >
          <AnimatedButton
            title="Continue"
            onPress={handleContinue}
            loading={isLoading}
            disabled={phone.length < 9}
            fullWidth
          />
        </Animated.View>

        {/* Terms */}
        <Animated.View 
          entering={FadeInUp.delay(500).duration(500)}
          style={styles.termsContainer}
        >
          <Text style={styles.termsText}>
            By continuing, you agree to our{' '}
            <Text style={styles.termsLink}>Terms of Service</Text>
            {' '}and{' '}
            <Text style={styles.termsLink}>Privacy Policy</Text>
          </Text>
        </Animated.View>
      </ScrollView>
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
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: Spacing.xl,
    paddingTop: 100,
    paddingBottom: Spacing.xxl,
  },
  header: {
    marginBottom: Spacing.xl,
  },
  title: {
    fontSize: Typography.sizes.h1,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    marginBottom: Spacing.sm,
  },
  subtitle: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '80',
  },
  inputContainer: {
    marginBottom: Spacing.lg,
  },
  phoneInputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.KribiWhite + '10',
    borderRadius: BorderRadius.lg,
    borderWidth: 1,
    borderColor: Colors.KribiWhite + '20',
    overflow: 'hidden',
  },
  countryCode: {
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.md,
    backgroundColor: Colors.KribiWhite + '10',
    borderRightWidth: 1,
    borderRightColor: Colors.KribiWhite + '20',
  },
  countryCodeText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite,
  },
  phoneInput: {
    flex: 1,
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.md,
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite,
  },
  errorText: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.ErrorRed,
    marginTop: Spacing.sm,
  },
  infoContainer: {
    marginBottom: Spacing.xl,
  },
  infoText: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    textAlign: 'center',
  },
  buttonContainer: {
    marginBottom: Spacing.xl,
  },
  termsContainer: {
    marginTop: 'auto',
  },
  termsText: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    textAlign: 'center',
  },
  termsLink: {
    color: Colors.PulsePurple,
    fontFamily: Typography.fontFamily.medium,
  },
});

export default LoginScreen;
