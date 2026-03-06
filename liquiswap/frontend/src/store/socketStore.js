/**
 * Socket Store - Zustand
 * Manages WebSocket connection and real-time events
 */

import { create } from 'zustand';
import io from 'socket.io-client';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_URL } from '../utils/api';

const useSocketStore = create((set, get) => ({
  // State
  socket: null,
  isConnected: false,
  isConnecting: false,
  connectionError: null,
  currentTransactionId: null,
  
  // Actions
  connect: async () => {
    const { socket, isConnecting } = get();
    
    if (socket?.connected || isConnecting) return;
    
    set({ isConnecting: true, connectionError: null });
    
    try {
      // Get auth token
      const authData = await AsyncStorage.getItem('auth-storage');
      const parsedAuth = authData ? JSON.parse(authData) : null;
      const token = parsedAuth?.state?.tokens?.accessToken;
      
      if (!token) {
        set({ 
          isConnecting: false, 
          connectionError: 'No authentication token' 
        });
        return;
      }
      
      // Create socket connection
      const newSocket = io(API_URL, {
        auth: { token },
        transports: ['websocket'],
        reconnection: true,
        reconnectionAttempts: 5,
        reconnectionDelay: 1000,
      });
      
      // Connection events
      newSocket.on('connect', () => {
        console.log('Socket connected:', newSocket.id);
        set({ 
          socket: newSocket, 
          isConnected: true, 
          isConnecting: false,
          connectionError: null 
        });
        
        // Rejoin transaction room if needed
        const { currentTransactionId } = get();
        if (currentTransactionId) {
          newSocket.emit('join_transaction', currentTransactionId);
        }
      });
      
      newSocket.on('disconnect', (reason) => {
        console.log('Socket disconnected:', reason);
        set({ isConnected: false });
      });
      
      newSocket.on('connect_error', (error) => {
        console.log('Socket connection error:', error);
        set({ 
          isConnecting: false, 
          connectionError: error.message 
        });
      });
      
      // Set up event listeners
      get().setupEventListeners(newSocket);
      
      set({ socket: newSocket });
      
    } catch (error) {
      console.log('Socket connect error:', error);
      set({ 
        isConnecting: false, 
        connectionError: error.message 
      });
    }
  },
  
  disconnect: () => {
    const { socket } = get();
    if (socket) {
      socket.disconnect();
      set({ 
        socket: null, 
        isConnected: false,
        currentTransactionId: null 
      });
    }
  },
  
  setupEventListeners: (socket) => {
    // Match events
    socket.on('match_found', (data) => {
      console.log('Match found:', data);
      // This will be handled by the component
    });
    
    // Transaction events
    socket.on('transaction_completed', (data) => {
      console.log('Transaction completed:', data);
      // Update transactions store
    });
    
    socket.on('confirmation_received', (data) => {
      console.log('Confirmation received:', data);
      // Update UI to show confirmation status
    });
    
    // Chat events
    socket.on('new_message', (message) => {
      console.log('New message:', message);
      // Add message to transactions store
    });
    
    socket.on('user_typing', (data) => {
      console.log('User typing:', data);
      // Update typing indicator
    });
    
    // Request events
    socket.on('request_expired', (data) => {
      console.log('Request expired:', data);
      // Update requests store
    });
    
    // Notification events
    socket.on('notification', (notification) => {
      console.log('Notification:', notification);
      // Show in-app notification
    });
    
    // Error events
    socket.on('error', (error) => {
      console.log('Socket error:', error);
    });
  },
  
  // Emit events
  createRequest: (requestData) => {
    const { socket } = get();
    if (socket) {
      socket.emit('create_request', requestData);
    }
  },
  
  cancelRequest: (requestId) => {
    const { socket } = get();
    if (socket) {
      socket.emit('cancel_request', requestId);
    }
  },
  
  joinTransaction: (transactionId) => {
    const { socket } = get();
    if (socket) {
      socket.emit('join_transaction', transactionId);
      set({ currentTransactionId: transactionId });
    }
  },
  
  leaveTransaction: (transactionId) => {
    const { socket } = get();
    if (socket) {
      socket.emit('leave_transaction', transactionId);
      set({ currentTransactionId: null });
    }
  },
  
  sendMessage: (transactionId, content, messageType = 'TEXT') => {
    const { socket } = get();
    if (socket) {
      socket.emit('send_message', { transactionId, content, messageType });
    }
  },
  
  sendTyping: (transactionId, isTyping) => {
    const { socket } = get();
    if (socket) {
      socket.emit('typing', { transactionId, isTyping });
    }
  },
  
  confirmTransaction: (transactionId) => {
    const { socket } = get();
    if (socket) {
      socket.emit('confirm_transaction', { transactionId });
    }
  },
  
  // Subscribe to events (for components)
  subscribe: (event, callback) => {
    const { socket } = get();
    if (socket) {
      socket.on(event, callback);
      return () => socket.off(event, callback);
    }
    return () => {};
  },
  
  clearError: () => set({ connectionError: null }),
}));

export default useSocketStore;
