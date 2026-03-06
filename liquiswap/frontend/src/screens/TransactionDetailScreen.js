/**
 * Transaction Detail Screen
 * Transaction info, chat, and confirmation buttons
 */

import React, { useEffect, useState, useRef, useCallback } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  ScrollView,
  TextInput,
  Pressable,
  KeyboardAvoidingView,
  Platform,
  FlatList,
} from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withTiming,
  withSpring,
  withRepeat,
  interpolate,
  FadeIn,
  FadeInUp,
  FadeInDown,
  SlideInUp,
} from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import LottieView from 'lottie-react-native';
import * as Haptics from 'expo-haptics';
import { Ionicons } from '@expo/vector-icons';
import { Colors, Typography, Spacing, BorderRadius } from '../constants/theme';
import { formatCurrency, getCurrencyColor } from '../constants/theme';
import useAuthStore from '../store/authStore';
import useTransactionsStore from '../store/transactionsStore';
import useSocketStore from '../store/socketStore';

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

// Chat Message Component
const ChatMessage = ({ message, isMe, showAvatar }) => {
  return (
    <View style={[styles.messageContainer, isMe ? styles.messageRight : styles.messageLeft]}>
      {!isMe && showAvatar && (
        <View style={styles.messageAvatar}>
          <Text style={styles.messageAvatarText}>
            {message.sender?.name?.charAt(0) || 'U'}
          </Text>
        </View>
      )}
      <View style={[
        styles.messageBubble,
        isMe ? styles.messageBubbleRight : styles.messageBubbleLeft,
      ]}>
        <Text style={[
          styles.messageText,
          isMe ? styles.messageTextRight : styles.messageTextLeft,
        ]}>
          {message.content}
        </Text>
        <Text style={styles.messageTime}>
          {new Date(message.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
        </Text>
      </View>
    </View>
  );
};

// Match Celebration Overlay
const MatchCelebration = ({ visible, onComplete }) => {
  if (!visible) return null;

  return (
    <Animated.View 
      entering={FadeIn}
      style={styles.celebrationOverlay}
    >
      <LottieView
        source={require('../assets/lottie/confetti.json')}
        autoPlay
        loop={false}
        onAnimationFinish={onComplete}
        style={styles.confettiAnimation}
      />
      <Animated.View entering={FadeInUp.delay(300)} style={styles.celebrationTextContainer}>
        <Text style={styles.celebrationTitle}>It's a Match!</Text>
        <Text style={styles.celebrationSubtitle}>You found an exchange partner</Text>
      </Animated.View>
    </Animated.View>
  );
};

// Confirm Button with Pulse Animation
const ConfirmButton = ({ onPress, confirmed, disabled, title }) => {
  const pulseScale = useSharedValue(1);

  useEffect(() => {
    if (!confirmed && !disabled) {
      pulseScale.value = withRepeat(
        withSpring(1.05, { damping: 10 }),
        -1,
        true
      );
    } else {
      pulseScale.value = 1;
    }
  }, [confirmed, disabled]);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: pulseScale.value }],
  }));

  return (
    <AnimatedPressable
      onPress={onPress}
      disabled={confirmed || disabled}
      style={[
        styles.confirmButton,
        confirmed && styles.confirmButtonConfirmed,
        disabled && !confirmed && styles.confirmButtonDisabled,
        animatedStyle,
      ]}
    >
      <LinearGradient
        colors={confirmed ? [Colors.SuccessGreen, '#059669'] : [Colors.PulsePurple, '#7C3AED']}
        style={styles.confirmButtonGradient}
      >
        {confirmed ? (
          <Ionicons name="checkmark-circle" size={24} color={Colors.KribiWhite} />
        ) : (
          <Text style={styles.confirmButtonText}>{title}</Text>
        )}
      </LinearGradient>
    </AnimatedPressable>
  );
};

const TransactionDetailScreen = ({ navigation, route }) => {
  const { transactionId, isNewMatch = false } = route.params;
  const [message, setMessage] = useState('');
  const [showCelebration, setShowCelebration] = useState(isNewMatch);
  const [showPinModal, setShowPinModal] = useState(false);
  const [pin, setPin] = useState('');

  const { user } = useAuthStore();
  const { 
    currentTransaction, 
    messages, 
    fetchTransaction, 
    fetchMessages,
    sendMessage,
    confirmTransaction,
    addMessage,
    handleTransactionUpdate,
    handleTransactionCompleted,
  } = useTransactionsStore();
  const { socket, joinTransaction, leaveTransaction, subscribe } = useSocketStore();

  const flatListRef = useRef(null);

  useEffect(() => {
    loadData();
    joinTransaction(transactionId);

    // Subscribe to socket events
    const unsubMessage = subscribe('new_message', (msg) => {
      addMessage(msg);
      scrollToBottom();
    });

    const unsubUpdate = subscribe('transaction_update', (data) => {
      handleTransactionUpdate(data);
    });

    const unsubCompleted = subscribe('transaction_completed', (data) => {
      handleTransactionCompleted(data);
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
    });

    return () => {
      leaveTransaction(transactionId);
      unsubMessage();
      unsubUpdate();
      unsubCompleted();
    };
  }, [transactionId]);

  const loadData = async () => {
    await fetchTransaction(transactionId);
    await fetchMessages(transactionId);
    scrollToBottom();
  };

  const scrollToBottom = () => {
    setTimeout(() => {
      flatListRef.current?.scrollToEnd({ animated: true });
    }, 100);
  };

  const handleSendMessage = async () => {
    if (!message.trim()) return;

    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    await sendMessage(transactionId, message.trim());
    setMessage('');
    scrollToBottom();
  };

  const handleConfirm = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
    setShowPinModal(true);
  };

  const handlePinConfirm = async () => {
    if (pin.length < 4) return;

    const result = await confirmTransaction(transactionId, pin);
    
    if (result.success) {
      setShowPinModal(false);
      setPin('');
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
    } else {
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
      // Show error
    }
  };

  const handleCelebrationComplete = () => {
    setShowCelebration(false);
  };

  if (!currentTransaction) {
    return (
      <View style={styles.container}>
        <LinearGradient
          colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
          style={styles.background}
        />
        <View style={styles.loadingContainer}>
          <Text style={styles.loadingText}>Loading...</Text>
        </View>
      </View>
    );
  }

  const isSender = currentTransaction.senderId === user?.id;
  const otherParty = isSender ? currentTransaction.receiver : currentTransaction.sender;
  const myConfirmed = isSender ? currentTransaction.senderConfirmed : currentTransaction.receiverConfirmed;
  const otherConfirmed = isSender ? currentTransaction.receiverConfirmed : currentTransaction.senderConfirmed;

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={[Colors.DeepNavy, Colors.LiquiSwapNavy]}
        style={styles.background}
      />

      {/* Match Celebration */}
      <MatchCelebration 
        visible={showCelebration} 
        onComplete={handleCelebrationComplete}
      />

      {/* Header */}
      <View style={styles.header}>
        <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color={Colors.KribiWhite} />
        </Pressable>
        <View style={styles.headerCenter}>
          <Text style={styles.headerTitle}>{otherParty?.name || 'Exchange'}</Text>
          <View style={[styles.statusBadge, { backgroundColor: getCurrencyColor(currentTransaction.status) + '20' }]}>
            <Text style={[styles.statusText, { color: getCurrencyColor(currentTransaction.status) }]}>
              {currentTransaction.status}
            </Text>
          </View>
        </View>
        <View style={styles.placeholder} />
      </View>

      {/* Transaction Details Card */}
      <View style={styles.detailsCard}>
        <View style={styles.exchangeRow}>
          <View style={styles.exchangeItem}>
            <View style={[styles.currencyBadge, { backgroundColor: getCurrencyColor(currentTransaction.request?.haveType) }]}>
              <Text style={styles.currencyText}>{currentTransaction.request?.haveType}</Text>
            </View>
            <Text style={styles.exchangeAmount}>
              {formatCurrency(currentTransaction.senderAmount)}
            </Text>
          </View>
          <Ionicons name="arrow-forward" size={24} color={Colors.PulsePurple} />
          <View style={styles.exchangeItem}>
            <View style={[styles.currencyBadge, { backgroundColor: getCurrencyColor(currentTransaction.request?.wantType) }]}>
              <Text style={styles.currencyText}>{currentTransaction.request?.wantType}</Text>
            </View>
            <Text style={styles.exchangeAmount}>
              {formatCurrency(currentTransaction.receiverAmount)}
            </Text>
          </View>
        </View>

        {/* Confirmation Status */}
        {currentTransaction.status === 'PENDING' && (
          <View style={styles.confirmationStatus}>
            <View style={styles.confirmationItem}>
              <Ionicons 
                name={myConfirmed ? "checkmark-circle" : "ellipse-outline"} 
                size={20} 
                color={myConfirmed ? Colors.SuccessGreen : Colors.MediumGray} 
              />
              <Text style={styles.confirmationText}>
                {myConfirmed ? 'You confirmed' : 'Waiting for you'}
              </Text>
            </View>
            <View style={styles.confirmationItem}>
              <Ionicons 
                name={otherConfirmed ? "checkmark-circle" : "ellipse-outline"} 
                size={20} 
                color={otherConfirmed ? Colors.SuccessGreen : Colors.MediumGray} 
              />
              <Text style={styles.confirmationText}>
                {otherConfirmed ? 'They confirmed' : 'Waiting for them'}
              </Text>
            </View>
          </View>
        )}
      </View>

      {/* Chat Messages */}
      <FlatList
        ref={flatListRef}
        data={messages}
        keyExtractor={(item) => item.id}
        renderItem={({ item, index }) => (
          <ChatMessage
            message={item}
            isMe={item.senderId === user?.id}
            showAvatar={index === 0 || messages[index - 1]?.senderId !== item.senderId}
          />
        )}
        contentContainerStyle={styles.messagesList}
        onContentSizeChange={scrollToBottom}
      />

      {/* Confirm Buttons (if pending) */}
      {currentTransaction.status === 'PENDING' && !myConfirmed && (
        <Animated.View entering={SlideInUp} style={styles.confirmContainer}>
          <Text style={styles.confirmTitle}>Have you completed the transfer?</Text>
          <Text style={styles.confirmSubtitle}>
            Only confirm after you've sent the money
          </Text>
          <ConfirmButton
            title="I Confirm"
            onPress={handleConfirm}
            confirmed={myConfirmed}
            disabled={false}
          />
        </Animated.View>
      )}

      {/* Chat Input */}
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        keyboardVerticalOffset={100}
      >
        <View style={styles.inputContainer}>
          <TextInput
            style={styles.textInput}
            placeholder="Type a message..."
            placeholderTextColor={Colors.MediumGray}
            value={message}
            onChangeText={setMessage}
            multiline
          />
          <Pressable onPress={handleSendMessage} style={styles.sendButton}>
            <Ionicons name="send" size={24} color={Colors.PulsePurple} />
          </Pressable>
        </View>
      </KeyboardAvoidingView>

      {/* PIN Modal */}
      {showPinModal && (
        <View style={styles.pinModalOverlay}>
          <View style={styles.pinModal}>
            <Text style={styles.pinTitle}>Enter PIN</Text>
            <Text style={styles.pinSubtitle}>Confirm this transaction</Text>
            <TextInput
              style={styles.pinInput}
              placeholder="****"
              placeholderTextColor={Colors.MediumGray}
              keyboardType="number-pad"
              secureTextEntry
              maxLength={6}
              value={pin}
              onChangeText={setPin}
              autoFocus
            />
            <View style={styles.pinButtons}>
              <Pressable onPress={() => setShowPinModal(false)} style={styles.pinCancel}>
                <Text style={styles.pinCancelText}>Cancel</Text>
              </Pressable>
              <Pressable onPress={handlePinConfirm} style={styles.pinConfirm}>
                <LinearGradient
                  colors={[Colors.PulsePurple, '#7C3AED']}
                  style={styles.pinConfirmGradient}
                >
                  <Text style={styles.pinConfirmText}>Confirm</Text>
                </LinearGradient>
              </Pressable>
            </View>
          </View>
        </View>
      )}
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
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite,
  },
  celebrationOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: Colors.DeepNavy + 'EE',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 100,
  },
  confettiAnimation: {
    width: 300,
    height: 300,
  },
  celebrationTextContainer: {
    alignItems: 'center',
    marginTop: Spacing.lg,
  },
  celebrationTitle: {
    fontSize: 36,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.PulsePurple,
  },
  celebrationSubtitle: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '80',
    marginTop: Spacing.sm,
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
  headerCenter: {
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: Typography.sizes.h3,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  statusBadge: {
    paddingHorizontal: Spacing.sm,
    paddingVertical: 2,
    borderRadius: BorderRadius.sm,
    marginTop: 4,
  },
  statusText: {
    fontSize: Typography.sizes.tiny,
    fontFamily: Typography.fontFamily.medium,
    textTransform: 'uppercase',
  },
  placeholder: {
    width: 40,
  },
  detailsCard: {
    backgroundColor: Colors.KribiWhite + '08',
    borderRadius: BorderRadius.xl,
    marginHorizontal: Spacing.lg,
    padding: Spacing.lg,
    marginBottom: Spacing.md,
  },
  exchangeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  exchangeItem: {
    alignItems: 'center',
    flex: 1,
  },
  currencyBadge: {
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    borderRadius: BorderRadius.md,
    marginBottom: Spacing.sm,
  },
  currencyText: {
    fontSize: Typography.sizes.bodySmall,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  exchangeAmount: {
    fontSize: Typography.sizes.h3,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  confirmationStatus: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginTop: Spacing.lg,
    paddingTop: Spacing.lg,
    borderTopWidth: 1,
    borderTopColor: Colors.KribiWhite + '10',
  },
  confirmationItem: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  confirmationText: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    marginLeft: Spacing.xs,
  },
  messagesList: {
    paddingHorizontal: Spacing.lg,
    paddingVertical: Spacing.md,
  },
  messageContainer: {
    flexDirection: 'row',
    marginBottom: Spacing.md,
    maxWidth: '80%',
  },
  messageLeft: {
    alignSelf: 'flex-start',
  },
  messageRight: {
    alignSelf: 'flex-end',
    flexDirection: 'row-reverse',
  },
  messageAvatar: {
    width: 32,
    height: 32,
    borderRadius: BorderRadius.full,
    backgroundColor: Colors.PulsePurple + '30',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Spacing.sm,
  },
  messageAvatarText: {
    fontSize: 14,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.PulsePurple,
  },
  messageBubble: {
    padding: Spacing.md,
    borderRadius: BorderRadius.lg,
    maxWidth: '100%',
  },
  messageBubbleLeft: {
    backgroundColor: Colors.KribiWhite + '10',
    borderBottomLeftRadius: 4,
  },
  messageBubbleRight: {
    backgroundColor: Colors.PulsePurple,
    borderBottomRightRadius: 4,
  },
  messageText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
  },
  messageTextLeft: {
    color: Colors.KribiWhite,
  },
  messageTextRight: {
    color: Colors.KribiWhite,
  },
  messageTime: {
    fontSize: Typography.sizes.tiny,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    marginTop: 4,
    alignSelf: 'flex-end',
  },
  confirmContainer: {
    backgroundColor: Colors.KribiWhite + '08',
    padding: Spacing.lg,
    marginHorizontal: Spacing.lg,
    marginBottom: Spacing.md,
    borderRadius: BorderRadius.xl,
  },
  confirmTitle: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.semiBold,
    color: Colors.KribiWhite,
    textAlign: 'center',
  },
  confirmSubtitle: {
    fontSize: Typography.sizes.caption,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    textAlign: 'center',
    marginBottom: Spacing.md,
  },
  confirmButton: {
    height: 48,
    borderRadius: BorderRadius.xxl,
    overflow: 'hidden',
  },
  confirmButtonConfirmed: {
    backgroundColor: Colors.SuccessGreen,
  },
  confirmButtonDisabled: {
    opacity: 0.5,
  },
  confirmButtonGradient: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  confirmButtonText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: Spacing.lg,
    paddingVertical: Spacing.md,
    backgroundColor: Colors.KribiWhite + '05',
  },
  textInput: {
    flex: 1,
    backgroundColor: Colors.KribiWhite + '10',
    borderRadius: BorderRadius.xxl,
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite,
    maxHeight: 100,
  },
  sendButton: {
    marginLeft: Spacing.sm,
    padding: Spacing.sm,
  },
  pinModalOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.7)',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 200,
  },
  pinModal: {
    backgroundColor: Colors.DeepNavy,
    borderRadius: BorderRadius.xl,
    padding: Spacing.xl,
    width: '80%',
    alignItems: 'center',
  },
  pinTitle: {
    fontSize: Typography.sizes.h2,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    marginBottom: Spacing.sm,
  },
  pinSubtitle: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.regular,
    color: Colors.KribiWhite + '60',
    marginBottom: Spacing.lg,
  },
  pinInput: {
    width: '100%',
    height: 56,
    backgroundColor: Colors.KribiWhite + '10',
    borderRadius: BorderRadius.lg,
    fontSize: 24,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
    textAlign: 'center',
    letterSpacing: 8,
    marginBottom: Spacing.lg,
  },
  pinButtons: {
    flexDirection: 'row',
    gap: Spacing.md,
    width: '100%',
  },
  pinCancel: {
    flex: 1,
    paddingVertical: Spacing.md,
    alignItems: 'center',
  },
  pinCancelText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.medium,
    color: Colors.KribiWhite + '60',
  },
  pinConfirm: {
    flex: 2,
    borderRadius: BorderRadius.xxl,
    overflow: 'hidden',
  },
  pinConfirmGradient: {
    paddingVertical: Spacing.md,
    alignItems: 'center',
  },
  pinConfirmText: {
    fontSize: Typography.sizes.body,
    fontFamily: Typography.fontFamily.bold,
    color: Colors.KribiWhite,
  },
});

export default TransactionDetailScreen;
