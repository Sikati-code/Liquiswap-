/**
 * LiquiSwap App
 * Main entry point with navigation configuration
 */

import React, { useEffect, useState } from 'react';
import { StatusBar } from 'expo-status-bar';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { View, Text, StyleSheet } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';

// Screens
import SplashScreen from './src/screens/SplashScreen';
import OnboardingScreen from './src/screens/OnboardingScreen';
import LoginScreen from './src/screens/LoginScreen';
import OTPScreen from './src/screens/OTPScreen';
import HomeScreen from './src/screens/HomeScreen';
import CreateRequestScreen from './src/screens/CreateRequestScreen';
import MarketplaceScreen from './src/screens/MarketplaceScreen';
import TransactionDetailScreen from './src/screens/TransactionDetailScreen';
import SetupProfileScreen from './src/screens/SetupProfileScreen';

// Store
import useAuthStore from './src/store/authStore';
import useSocketStore from './src/store/socketStore';

// Theme
import { Colors, Typography, Spacing } from './src/constants/theme';

const Stack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

// Placeholder screens for tab navigation
const HistoryScreen = () => (
  <View style={styles.placeholderContainer}>
    <LinearGradient
      colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
      style={StyleSheet.absoluteFillObject}
    />
    <Text style={styles.placeholderText}>History Screen</Text>
  </View>
);

const ProfileScreen = () => (
  <View style={styles.placeholderContainer}>
    <LinearGradient
      colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
      style={StyleSheet.absoluteFillObject}
    />
    <Text style={styles.placeholderText}>Profile Screen</Text>
  </View>
);

// Main Tab Navigator
const MainTabs = () => {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarStyle: {
          backgroundColor: Colors.DeepNavy,
          borderTopWidth: 0,
          elevation: 0,
          height: 80,
          paddingBottom: 20,
        },
        tabBarActiveTintColor: Colors.PulsePurple,
        tabBarInactiveTintColor: Colors.KribiWhite + '60',
        tabBarLabelStyle: {
          fontSize: 12,
          fontFamily: Typography.fontFamily.medium,
        },
        tabBarIcon: ({ focused, color, size }) => {
          let iconName;
          
          switch (route.name) {
            case 'Home':
              iconName = focused ? 'home' : 'home-outline';
              break;
            case 'Marketplace':
              iconName = focused ? 'storefront' : 'storefront-outline';
              break;
            case 'History':
              iconName = focused ? 'time' : 'time-outline';
              break;
            case 'Profile':
              iconName = focused ? 'person' : 'person-outline';
              break;
            default:
              iconName = 'circle';
          }
          
          return <Ionicons name={iconName} size={size} color={color} />;
        },
      })}
    >
      <Tab.Screen name="Home" component={HomeScreen} />
      <Tab.Screen name="Marketplace" component={MarketplaceScreen} />
      <Tab.Screen name="History" component={HistoryScreen} />
      <Tab.Screen name="Profile" component={ProfileScreen} />
    </Tab.Navigator>
  );
};

// Main App Component
export default function App() {
  const [isLoading, setIsLoading] = useState(true);
  const [showSplash, setShowSplash] = useState(true);
  
  const { isAuthenticated, onboardingCompleted, setOnboardingCompleted } = useAuthStore();
  const { connect, disconnect } = useSocketStore();

  // Handle splash animation completion
  const handleSplashComplete = () => {
    setShowSplash(false);
  };

  // Connect socket when authenticated
  useEffect(() => {
    if (isAuthenticated) {
      connect();
    } else {
      disconnect();
    }
    
    return () => {
      disconnect();
    };
  }, [isAuthenticated]);

  // Show splash screen
  if (showSplash) {
    return (
      <>
        <StatusBar style="light" />
        <SplashScreen onAnimationComplete={handleSplashComplete} />
      </>
    );
  }

  return (
    <>
      <StatusBar style="light" />
      <NavigationContainer>
        <Stack.Navigator
          screenOptions={{
            headerShown: false,
            contentStyle: { backgroundColor: Colors.DeepNavy },
            animation: 'slide_from_right',
          }}
        >
          {!onboardingCompleted ? (
            <Stack.Screen name="Onboarding">
              {(props) => (
                <OnboardingScreen
                  {...props}
                  onComplete={() => setOnboardingCompleted(true)}
                  onSkip={() => setOnboardingCompleted(true)}
                />
              )}
            </Stack.Screen>
          ) : !isAuthenticated ? (
            <>
              <Stack.Screen name="Login" component={LoginScreen} />
              <Stack.Screen name="OTP" component={OTPScreen} />
            </>
          ) : (
            <>
              <Stack.Screen name="SetupProfile" component={SetupProfileScreen} />
              <Stack.Screen name="Main" component={MainTabs} />
              <Stack.Screen 
                name="CreateRequest" 
                component={CreateRequestScreen}
                options={{
                  presentation: 'modal',
                  animation: 'slide_from_bottom',
                }}
              />
              <Stack.Screen 
                name="TransactionDetail" 
                component={TransactionDetailScreen}
              />
            </>
          )}
        </Stack.Navigator>
      </NavigationContainer>
    </>
  );
}

const styles = StyleSheet.create({
  placeholderContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  placeholderText: {
    fontSize: Typography.sizes.h2,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
});
