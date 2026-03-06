/**
 * OTP Screen
 * Auto-advancing OTP input with success animation
 */

import React, { useState, useRef, useEffect, useCallback } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  TextInput,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Pressable,
} from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  withTiming,
  withSequence,
  interpolate,
  FadeIn,
  FadeInUp,
  FadeOut,
} from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import LottieView from 'lottie-react-native';
import * as Haptics from 'expo-haptics';
import { Colors, Typography, Spacing, BorderRadius, Animations } from '../constants/theme';
import useAuthStore from '../store/authStore';

const OTP_LENGTH = 6;

const OTPScreen = ({ navigation, route }) => {
  const { phone } = route.params;
  const [otp, setOtp] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const [showSuccess, setShowSuccess] = useState(false);
  const [countdown, setCountdown] = useState(60);
  const [canResend, setCanResend] = useState(false);

  const inputRefs = useRef([]);
  const { login } = useAuthStore();

  // Countdown timer
  useEffect(() => {
    if (countdown > 0) {
      const timer = setTimeout(() => setCountdown(countdown - 1), 1000);
      return () => clearTimeout(timer);
    } else {
      setCanResend(true);
    }
  }, [countdown]);

  // Auto-focus first input
  useEffect(() => {
    setTimeout(() => {
      inputRefs.current[0]?.focus();
    }, 500);
  }, []);

  const handleOtpChange = (text, index) => {
    // Only allow digits
    const digit = text.replace(/\D/g, '').slice(-1);
    
    if (digit) {
      const newOtp = otp.slice(0, index) + digit + otp.slice(index + 1);
      setOtp(newOtp);
      setError('');
      
      Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
      
      // Auto-advance to next input
      if (index < OTP_LENGTH - 1 && digit) {
        inputRefs.current[index + 1]?.focus();
      }
      
      // Check if OTP is complete
      if (newOtp.length === OTP_LENGTH && index === OTP_LENGTH - 1) {
        handleVerify(newOtp);
      }
    }
  };

  const handleKeyPress = (e, index) => {
    if (e.nativeEvent.key === 'Backspace') {
      if (otp[index]) {
        // Clear current digit
        const newOtp = otp.slice(0, index) + otp.slice(index + 1);
        setOtp(newOtp);
      } else if (index > 0) {
        // Go back to previous input
        inputRefs.current[index - 1]?.focus();
      }
    }
  };

  const handleVerify = async (code = otp) => {
    if (code.length !== OTP_LENGTH) {
      setError('Please enter the complete code');
      return;
    }

    setIsLoading(true);
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);

    const result = await login(phone, code);

    if (result.success) {
      // Show success animation
      setShowSuccess(true);
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      
      // Navigate after animation
      setTimeout(() => {
        if (result.isNewUser) {
          navigation.replace('SetupProfile');
        } else {
          navigation.replace('Main');
        }
      }, 1500);
    } else {
      setError(result.error || 'Invalid code');
      setIsLoading(false);
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
      
      // Shake animation
      shakeAnimation();
    }
  };

  const shakeValue = useSharedValue(0);

  const shakeAnimation = () => {
    shakeValue.value = withSequence(
      withTiming(-10, { duration: 50 }),
      withTiming(10, { duration: 50 }),
      withTiming(-10, { duration: 50 }),
      withTiming(10, { duration: 50 }),
      withTiming(0, { duration: 50 })
    );
  };

  const shakeStyle = useAnimatedStyle(() => ({
    transform: [{ translateX: shakeValue.value }],
  }));

  const handleResend = async () => {
    if (!canResend) return;
    
    setIsLoading(true);
    try {
      await api.post('/auth/send-otp', { phone });
      setCountdown(60);
      setCanResend(false);
      setError('');
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
    } catch (error) {
      setError('Failed to resend code');
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

      {/* Success Animation Overlay */}
      {showSuccess && (
        <Animated.View 
          entering={FadeIn}
          style={styles.successOverlay}
        >
          <LottieView
            source={require('../assets/lottie/success.json')}
            autoPlay
            loop={false}
            style={styles.successAnimation}
          />
          <Text style={styles.successText}>Verified!</Text>
        </Animated.View>
      )}

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled"
      >
        {/* Header */}
        <Animated.View 
          entering={FadeInUp.delay(100).duration(500)}
          style={styles.header}
        >
          <Text style={styles.title}>Enter Code</Text>
          <Text style={styles.subtitle}>
            We've sent a 6-digit code to{'\n'}+237 {phone}
          </Text>
        </Animated.View>

        {/* OTP Input */}
        <Animated.View 
          entering={FadeInUp.delay(200).duration(500)}
          style={[styles.otpContainer, shakeStyle]}
        >
          {Array.from({ length: OTP_LENGTH }).map((_, index) => (
            <View key={index} style={styles.otpInputWrapper}>
              <TextInput
                ref={(ref) => (inputRefs.current[index] = ref)}
                style={[
                  styles.otpInput,
                  otp[index] && styles.otpInputFilled,
                ]}
                keyboardType="number-pad"
                maxLength={1}
                value={otp[index] || ''}
                onChangeText={(text) => handleOtpChange(text, index)}
                onKeyPress={(e) => handleKeyPress(e, index)}
                editable={!isLoading && !showSuccess}
                selectTextOnFocus
              />
            </View>
          ))}
        </Animated.View>

        {error ? (
          <Animated.Text 
            entering={FadeIn}
            style={styles.errorText}
          >
            {error}
          </Animated.Text>
        ) : null}

        {/* Resend */}
        <Animated.View 
          entering={FadeInUp.delay(300).duration(500)}
          style={styles.resendContainer}
        >
          {canResend ? (
            <Pressable onPress={handleResend} disabled={isLoading}>
              <Text style={styles.resendText}>Resend Code</Text>
            </Pressable>
          ) : (
            <Text style={styles.countdownText}>
              Resend code in {countdown}s
            </Text>
          )}
        </Animated.View>

        {/* Verify Button */}
        <Animated.View 
          entering={FadeInUp.delay(400).duration(500)}
          style={styles.buttonContainer}
        >
          <Pressable
            onPress={() => handleVerify()}
            disabled={otp.length !== OTP_LENGTH || isLoading || showSuccess}
            style={({ pressed }) => [
              styles.verifyButton,
              (otp.length !== OTP_LENGTH || isLoading || showSuccess) && styles.verifyButtonDisabled,
              pressed && styles.verifyButtonPressed,
            ]}
          >
            <LinearGradient
              colors={[Colors.PulsePurple, '#7C3AED']}
              style={styles.verifyButtonGradient}
            >
              <Text style={styles.verifyButtonText}>
                {isLoading ? 'Verifying...' : 'Verify'}
              </Text>
            </LinearGradient>
          </Pressable>
        </Animated.View>

        {/* Change Number */}
        <Animated.View 
          entering={FadeInUp.delay(500).duration(500)}
          style={styles.changeNumberContainer}
        >
          <Pressable onPress={() => navigation.goBack()}>
            <Text style={styles.changeNumberText}>Change Phone Number</Text>
          </Pressable>
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
  successOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: Colors.DeepNavy,
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 100,
  },
  successAnimation: {
    width: 200,
    height: 200,
  },
  successText: {
    fontSize: Typography.sizes.h1,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.SuccessGreen,
    marginTop: Spacing.lg,
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: Spacing.xl,
    paddingTop: 100,
    paddingBottom: Spacing.xxl,
  },
  header: {
    marginBottom: Spacing.xl,
    alignItems: 'center',
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
    textAlign: 'center',
  },
  otpContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginBottom: Spacing.lg,
  },
  otpInputWrapper: {
    marginHorizontal: 4,
  },
  otpInput: {
    width: 48,
    height: 56,
    borderRadius: BorderRadius.lg,
    backgroundColor: Colors.KribiWhite + '10',
    borderWidth: 1,
    borderColor: Colors.KribiWhite + '20',
    fontSize: 24,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    textAlign: 'center',
  },
  otpInputFilled: {
    backgroundColor: Colors.PulsePurple + '30',
    borderColor: Colors.PulsePurple,
  },
  errorText: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.ErrorRed,
    textAlign: 'center',
    marginBottom: Spacing.md,
  },
  resendContainer: {
    alignItems: 'center',
    marginBottom: Spacing.xl,
  },
  resendText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.PulsePurple,
  },
  countdownText: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
  },
  buttonContainer: {
    marginBottom: Spacing.xl,
  },
  verifyButton: {
    height: 56,
    borderRadius: BorderRadius.xxl,
    overflow: 'hidden',
    ...Shadows.lg,
  },
  verifyButtonDisabled: {
    opacity: 0.5,
  },
  verifyButtonPressed: {
    transform: [{ scale: 0.98 }],
  },
  verifyButtonGradient: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  verifyButtonText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  changeNumberContainer: {
    alignItems: 'center',
  },
  changeNumberText: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '60',
  },
});

export default OTPScreen;
