/**
 * Onboarding Screen
 * Parallax carousel with animated transitions
 */

import React, { useRef, useState, useCallback } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  Dimensions, 
  FlatList,
  Pressable,
} from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  useAnimatedScrollHandler,
  interpolate,
  Extrapolate,
  withTiming,
  withSpring,
  runOnJS,
} from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import LottieView from 'lottie-react-native';
import { Colors, Typography, Spacing, BorderRadius, Animations } from '../constants/theme';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

// Onboarding data
const ONBOARDING_DATA = [
  {
    id: '1',
    title: 'Exchange Instantly',
    description: 'Swap between MTN Mobile Money, Orange Money, and Cash in seconds. No more waiting in long lines.',
    animation: require('../assets/lottie/onboarding-exchange.json'),
    backgroundColor: Colors.PulsePurple,
  },
  {
    id: '2',
    title: 'Secure & Trusted',
    description: 'Your transactions are protected with bank-grade security. Verify every exchange with your PIN.',
    animation: require('../assets/lottie/onboarding-secure.json'),
    backgroundColor: Colors.CashGreen,
  },
  {
    id: '3',
    title: 'Find Matches',
    description: 'Our smart matching algorithm finds the best exchange partners near you instantly.',
    animation: require('../assets/lottie/onboarding-instant.json'),
    backgroundColor: Colors.YelloGold,
  },
];

const OnboardingItem = ({ item, index, scrollX }) => {
  const inputRange = [
    (index - 1) * SCREEN_WIDTH,
    index * SCREEN_WIDTH,
    (index + 1) * SCREEN_WIDTH,
  ];

  // Parallax effect for background
  const backgroundAnimatedStyle = useAnimatedStyle(() => ({
    transform: [
      {
        translateX: interpolate(
          scrollX.value,
          inputRange,
          [-SCREEN_WIDTH * 0.3, 0, SCREEN_WIDTH * 0.3],
          Extrapolate.CLAMP
        ),
      },
    ],
  }));

  // Fade and slide for content
  const contentAnimatedStyle = useAnimatedStyle(() => ({
    opacity: interpolate(
      scrollX.value,
      inputRange,
      [0, 1, 0],
      Extrapolate.CLAMP
    ),
    transform: [
      {
        translateY: interpolate(
          scrollX.value,
          inputRange,
          [50, 0, 50],
          Extrapolate.CLAMP
        ),
      },
    ],
  }));

  // Scale for image
  const imageAnimatedStyle = useAnimatedStyle(() => ({
    transform: [
      {
        scale: interpolate(
          scrollX.value,
          inputRange,
          [0.8, 1, 0.8],
          Extrapolate.CLAMP
        ),
      },
    ],
  }));

  return (
    <View style={styles.itemContainer}>
      {/* Parallax Background */}
      <Animated.View 
        style={[
          styles.parallaxBackground, 
          { backgroundColor: item.backgroundColor + '15' },
          backgroundAnimatedStyle
        ]} 
      />

      {/* Content */}
      <Animated.View style={[styles.contentContainer, contentAnimatedStyle]}>
        {/* Animation/Image */}
        <Animated.View style={[styles.animationContainer, imageAnimatedStyle]}>
          <LottieView
            source={item.animation}
            autoPlay
            loop
            style={styles.animation}
          />
        </Animated.View>

        {/* Text Content */}
        <View style={styles.textContainer}>
          <Text style={styles.title}>{item.title}</Text>
          <Text style={styles.description}>{item.description}</Text>
        </View>
      </Animated.View>
    </View>
  );
};

const OnboardingScreen = ({ onComplete, onSkip }) => {
  const [currentIndex, setCurrentIndex] = useState(0);
  const flatListRef = useRef(null);
  const scrollX = useSharedValue(0);

  const scrollHandler = useAnimatedScrollHandler({
    onScroll: (event) => {
      scrollX.value = event.contentOffset.x;
    },
  });

  const onViewableItemsChanged = useCallback(({ viewableItems }) => {
    if (viewableItems.length > 0) {
      setCurrentIndex(viewableItems[0].index);
    }
  }, []);

  const viewabilityConfig = {
    itemVisiblePercentThreshold: 50,
  };

  const handleNext = () => {
    if (currentIndex < ONBOARDING_DATA.length - 1) {
      flatListRef.current?.scrollToIndex({
        index: currentIndex + 1,
        animated: true,
      });
    } else {
      onComplete();
    }
  };

  const handleSkip = () => {
    // Zoom out animation before skipping
    onSkip();
  };

  // Button animation
  const buttonScale = useSharedValue(1);

  const buttonAnimatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: buttonScale.value }],
  }));

  const handlePressIn = () => {
    buttonScale.value = withSpring(0.95, Animations.easing.spring);
  };

  const handlePressOut = () => {
    buttonScale.value = withSpring(1, Animations.easing.spring);
  };

  const renderItem = ({ item, index }) => (
    <OnboardingItem item={item} index={index} scrollX={scrollX} />
  );

  return (
    <View style={styles.container}>
      {/* Background */}
      <LinearGradient
        colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
        style={styles.background}
      />

      {/* Skip Button */}
      <Pressable onPress={handleSkip} style={styles.skipButton}>
        <Text style={styles.skipText}>Skip</Text>
      </Pressable>

      {/* Carousel */}
      <Animated.FlatList
        ref={flatListRef}
        data={ONBOARDING_DATA}
        renderItem={renderItem}
        keyExtractor={(item) => item.id}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        onScroll={scrollHandler}
        scrollEventThrottle={16}
        onViewableItemsChanged={onViewableItemsChanged}
        viewabilityConfig={viewabilityConfig}
      />

      {/* Pagination Dots */}
      <View style={styles.paginationContainer}>
        {ONBOARDING_DATA.map((_, index) => (
          <View
            key={index}
            style={[
              styles.paginationDot,
              index === currentIndex && styles.paginationDotActive,
            ]}
          />
        ))}
      </View>

      {/* Next/Get Started Button */}
      <AnimatedPressable
        onPressIn={handlePressIn}
        onPressOut={handlePressOut}
        onPress={handleNext}
        style={[styles.nextButton, buttonAnimatedStyle]}
      >
        <LinearGradient
          colors={[Colors.PulsePurple, '#7C3AED']}
          style={styles.nextButtonGradient}
        >
          <Text style={styles.nextButtonText}>
            {currentIndex === ONBOARDING_DATA.length - 1 ? 'Get Started' : 'Next'}
          </Text>
        </LinearGradient>
      </AnimatedPressable>
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
  skipButton: {
    position: 'absolute',
    top: 50,
    right: Spacing.lg,
    zIndex: 10,
    padding: Spacing.sm,
  },
  skipText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '80',
  },
  itemContainer: {
    width: SCREEN_WIDTH,
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  parallaxBackground: {
    position: 'absolute',
    width: SCREEN_WIDTH * 1.5,
    height: SCREEN_WIDTH * 1.5,
    borderRadius: SCREEN_WIDTH,
    top: -SCREEN_WIDTH * 0.3,
  },
  contentContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: Spacing.xl,
  },
  animationContainer: {
    width: SCREEN_WIDTH * 0.7,
    height: SCREEN_WIDTH * 0.7,
    alignItems: 'center',
    justifyContent: 'center',
  },
  animation: {
    width: '100%',
    height: '100%',
  },
  textContainer: {
    alignItems: 'center',
    marginTop: Spacing.xl,
  },
  title: {
    fontSize: Typography.sizes.h1,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    textAlign: 'center',
    marginBottom: Spacing.md,
  },
  description: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '80',
    textAlign: 'center',
    lineHeight: 24,
    paddingHorizontal: Spacing.lg,
  },
  paginationContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: Spacing.xl,
  },
  paginationDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: Colors.KribiWhite + '40',
    marginHorizontal: 4,
  },
  paginationDotActive: {
    width: 24,
    backgroundColor: Colors.PulsePurple,
  },
  nextButton: {
    marginHorizontal: Spacing.xl,
    marginBottom: Spacing.xxl,
    height: 56,
    borderRadius: BorderRadius.xxl,
    overflow: 'hidden',
    ...Shadows.lg,
  },
  nextButtonGradient: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  nextButtonText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
});

export default OnboardingScreen;
