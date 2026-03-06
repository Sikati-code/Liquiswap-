/**
 * Transactions Store - Zustand
 * Manages transactions, chat, and confirmations
 */

import { create } from 'zustand';
import api from '../utils/api';

const useTransactionsStore = create((set, get) => ({
  // State
  transactions: [],
  currentTransaction: null,
  messages: [],
  isLoading: false,
  error: null,
  unreadCount: 0,
  isTyping: false,
  
  // Actions
  fetchTransactions: async (status = null) => {
    set({ isLoading: true, error: null });
    
    try {
      const params = new URLSearchParams();
      if (status) params.append('status', status);
      
      const response = await api.get(`/transactions?${params.toString()}`);
      const { transactions } = response.data.data;
      
      set({
        transactions,
        isLoading: false,
      });
      
      return transactions;
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to fetch transactions',
        isLoading: false,
      });
      return [];
    }
  },
  
  fetchTransaction: async (id) => {
    set({ isLoading: true, error: null });
    
    try {
      const response = await api.get(`/transactions/${id}`);
      const { transaction } = response.data.data;
      
      set({
        currentTransaction: transaction,
        messages: transaction.messages || [],
        isLoading: false,
      });
      
      return transaction;
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to fetch transaction',
        isLoading: false,
      });
      return null;
    }
  },
  
  confirmTransaction: async (transactionId, pin) => {
    set({ isLoading: true, error: null });
    
    try {
      const response = await api.post(`/transactions/${transactionId}/confirm`, { pin });
      const { transaction } = response.data.data;
      
      set((state) => ({
        currentTransaction: transaction,
        transactions: state.transactions.map(t =>
          t.id === transactionId ? transaction : t
        ),
        isLoading: false,
      }));
      
      return { success: true, transaction, message: response.data.message };
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to confirm transaction',
        isLoading: false,
      });
      return { 
        success: false, 
        error: error.response?.data?.message 
      };
    }
  },
  
  raiseDispute: async (transactionId, reason) => {
    set({ isLoading: true, error: null });
    
    try {
      const response = await api.post(`/transactions/${transactionId}/dispute`, { reason });
      const { transaction } = response.data.data;
      
      set((state) => ({
        currentTransaction: transaction,
        transactions: state.transactions.map(t =>
          t.id === transactionId ? transaction : t
        ),
        isLoading: false,
      }));
      
      return { success: true, transaction };
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to raise dispute',
        isLoading: false,
      });
      return { 
        success: false, 
        error: error.response?.data?.message 
      };
    }
  },
  
  rateTransaction: async (transactionId, rating, review) => {
    try {
      await api.post(`/transactions/${transactionId}/rate`, { rating, review });
      return { success: true };
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.message 
      };
    }
  },
  
  // Chat Actions
  fetchMessages: async (transactionId, page = 1) => {
    try {
      const response = await api.get(
        `/chat/${transactionId}/messages?page=${page}&limit=50`
      );
      const { messages } = response.data.data;
      
      set((state) => ({
        messages: page === 1 ? messages : [...messages, ...state.messages],
      }));
      
      return messages;
    } catch (error) {
      console.log('Fetch messages error:', error);
      return [];
    }
  },
  
  sendMessage: async (transactionId, content, messageType = 'TEXT') => {
    try {
      const response = await api.post(
        `/chat/${transactionId}/messages`,
        { content, messageType }
      );
      const { message } = response.data.data;
      
      set((state) => ({
        messages: [...state.messages, message],
      }));
      
      return message;
    } catch (error) {
      console.log('Send message error:', error);
      return null;
    }
  },
  
  addMessage: (message) => {
    set((state) => ({
      messages: [...state.messages, message],
    }));
  },
  
  sendTypingIndicator: async (transactionId, isTyping) => {
    try {
      await api.post(`/chat/${transactionId}/typing`, { isTyping });
    } catch (error) {
      console.log('Typing indicator error:', error);
    }
  },
  
  setTyping: (isTyping) => set({ isTyping }),
  
  fetchUnreadCount: async () => {
    try {
      const response = await api.get('/chat/unread/count');
      set({ unreadCount: response.data.data.unreadCount });
      return response.data.data.unreadCount;
    } catch (error) {
      console.log('Fetch unread count error:', error);
      return 0;
    }
  },
  
  // Socket Event Handlers
  handleTransactionUpdate: (transaction) => {
    set((state) => ({
      currentTransaction: 
        state.currentTransaction?.id === transaction.id 
          ? transaction 
          : state.currentTransaction,
      transactions: state.transactions.map(t =>
        t.id === transaction.id ? transaction : t
      ),
    }));
  },
  
  handleTransactionCompleted: (data) => {
    const { transactionId } = data;
    set((state) => ({
      transactions: state.transactions.map(t =>
        t.id === transactionId ? { ...t, status: 'COMPLETED' } : t
      ),
      currentTransaction: 
        state.currentTransaction?.id === transactionId
          ? { ...state.currentTransaction, status: 'COMPLETED' }
          : state.currentTransaction,
    }));
  },
  
  clearError: () => set({ error: null }),
  clearCurrentTransaction: () => set({ 
    currentTransaction: null, 
    messages: [] 
  }),
}));

export default useTransactionsStore;
