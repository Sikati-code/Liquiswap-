/**
 * Setup Profile Screen
 * Allows new users to set their name and transaction PIN
 */

import React, { useState } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  TextInput, 
  Pressable, 
  KeyboardAvoidingView, 
  Platform,
  ScrollView,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';
import { Colors, Typography, Spacing, BorderRadius, Shadows } from '../constants/theme';
import useAuthStore from '../store/authStore';

const SetupProfileScreen = ({ navigation }) => {
  const [name, setName] = useState('');
  const [pin, setPin] = useState('');
  const [confirmPin, setConfirmPin] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const { user, updateUser, setPin: apiSetPin } = useAuthStore();

  const handleComplete = async () => {
    if (!name.trim()) {
      setError('Please enter your name');
      return;
    }
    if (pin.length < 4) {
      setError('PIN must be at least 4 digits');
      return;
    }
    if (pin !== confirmPin) {
      setError('PINs do not match');
      return;
    }

    setIsLoading(true);
    setError('');
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);

    try {
      // In a real app, you might want to update the profile name first
      // await api.put('/users/profile', { name });
      
      const pinResult = await apiSetPin(pin);
      
      if (pinResult.success) {
        updateUser({ name });
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
        navigation.replace('Main');
      } else {
        setError(pinResult.error || 'Failed to set PIN');
      }
    } catch (err) {
      setError('An error occurred during setup');
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

      <ScrollView contentContainerStyle={styles.scrollContent}>
        <View style={styles.header}>
          <Text style={styles.title}>Welcome!</Text>
          <Text style={styles.subtitle}>Let's set up your profile</Text>
        </View>

        <View style={styles.form}>
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Full Name</Text>
            <TextInput
              style={styles.input}
              placeholder="Enter your name"
              placeholderTextColor={Colors.MediumGray}
              value={name}
              onChangeText={setName}
              autoCapitalize="words"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Transaction PIN</Text>
            <TextInput
              style={styles.input}
              placeholder="4-6 digits"
              placeholderTextColor={Colors.MediumGray}
              value={pin}
              onChangeText={setPin}
              keyboardType="number-pad"
              secureTextEntry
              maxLength={6}
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Confirm PIN</Text>
            <TextInput
              style={styles.input}
              placeholder="Repeat your PIN"
              placeholderTextColor={Colors.MediumGray}
              value={confirmPin}
              onChangeText={setConfirmPin}
              keyboardType="number-pad"
              secureTextEntry
              maxLength={6}
            />
          </View>

          {error ? <Text style={styles.errorText}>{error}</Text> : null}

          <Pressable
            onPress={handleComplete}
            disabled={isLoading}
            style={({ pressed }) => [
              styles.button,
              isLoading && styles.buttonDisabled,
              pressed && styles.buttonPressed,
            ]}
          >
            <LinearGradient
              colors={[Colors.PulsePurple, '#7C3AED']}
              style={styles.buttonGradient}
            >
              <Text style={styles.buttonText}>
                {isLoading ? 'Setting up...' : 'Get Started'}
              </Text>
            </LinearGradient>
          </Pressable>
        </View>
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
    paddingHorizontal: Spacing.xl,
    paddingTop: 80,
    paddingBottom: Spacing.xxl,
  },
  header: {
    marginBottom: Spacing.xxl,
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
  form: {
    gap: Spacing.lg,
  },
  inputGroup: {
    gap: Spacing.xs,
  },
  label: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '60',
    marginLeft: 4,
  },
  input: {
    height: 56,
    backgroundColor: Colors.KribiWhite + '10',
    borderRadius: BorderRadius.lg,
    paddingHorizontal: Spacing.md,
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite,
    borderWidth: 1,
    borderColor: Colors.KribiWhite + '20',
  },
  errorText: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.ErrorRed,
    textAlign: 'center',
  },
  button: {
    height: 56,
    borderRadius: BorderRadius.xxl,
    overflow: 'hidden',
    marginTop: Spacing.md,
    ...Shadows.lg,
  },
  buttonDisabled: {
    opacity: 0.5,
  },
  buttonPressed: {
    transform: [{ scale: 0.98 }],
  },
  buttonGradient: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  buttonText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
});

export default SetupProfileScreen;
