/**
 * Auth Store - Zustand
 * Manages authentication state and user data
 */

import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from '../utils/api';

const useAuthStore = create(
  persist(
    (set, get) => ({
      // State
      user: null,
      isAuthenticated: false,
      isLoading: false,
      error: null,
      tokens: null,
      onboardingCompleted: false,
      
      // Actions
      setOnboardingCompleted: (completed) => set({ onboardingCompleted: completed }),
      
      login: async (phone, otp) => {
        set({ isLoading: true, error: null });
        try {
          const response = await api.post('/auth/verify-otp', { phone, otp });
          const { user, tokens, isNewUser } = response.data.data;
          
          set({
            user,
            tokens,
            isAuthenticated: true,
            isLoading: false,
            isNewUser,
          });
          
          return { success: true, isNewUser };
        } catch (error) {
          set({
            error: error.response?.data?.message || 'Login failed',
            isLoading: false,
          });
          return { success: false, error: error.response?.data?.message };
        }
      },
      
      register: async (phone, otp, name) => {
        set({ isLoading: true, error: null });
        try {
          const response = await api.post('/auth/verify-otp', { phone, otp, name });
          const { user, tokens } = response.data.data;
          
          set({
            user,
            tokens,
            isAuthenticated: true,
            isLoading: false,
          });
          
          return { success: true };
        } catch (error) {
          set({
            error: error.response?.data?.message || 'Registration failed',
            isLoading: false,
          });
          return { success: false, error: error.response?.data?.message };
        }
      },
      
      logout: async () => {
        set({ isLoading: true });
        try {
          await api.post('/auth/logout');
        } catch (error) {
          console.log('Logout error:', error);
        } finally {
          set({
            user: null,
            tokens: null,
            isAuthenticated: false,
            isLoading: false,
            error: null,
          });
        }
      },
      
      refreshToken: async () => {
        const { tokens } = get();
        if (!tokens?.refreshToken) return false;
        
        try {
          const response = await api.post('/auth/refresh', {
            refreshToken: tokens.refreshToken,
          });
          
          set({ tokens: response.data.data });
          return true;
        } catch (error) {
          set({
            user: null,
            tokens: null,
            isAuthenticated: false,
          });
          return false;
        }
      },
      
      updateUser: (userData) => {
        set((state) => ({
          user: { ...state.user, ...userData },
        }));
      },
      
      updateBalances: (balances) => {
        set((state) => ({
          user: { ...state.user, balances },
        }));
      },
      
      setPin: async (pin) => {
        try {
          await api.post('/auth/set-pin', { pin });
          return { success: true };
        } catch (error) {
          return { 
            success: false, 
            error: error.response?.data?.message 
          };
        }
      },
      
      verifyPin: async (pin) => {
        try {
          await api.post('/auth/verify-pin', { pin });
          return { success: true };
        } catch (error) {
          return { 
            success: false, 
            error: error.response?.data?.message 
          };
        }
      },
      
      fetchProfile: async () => {
        try {
          const response = await api.get('/users/profile');
          set({ user: response.data.data.user });
          return response.data.data.user;
        } catch (error) {
          console.log('Fetch profile error:', error);
          return null;
        }
      },
      
      clearError: () => set({ error: null }),
    }),
    {
      name: 'auth-storage',
      storage: createJSONStorage(() => AsyncStorage),
      partialize: (state) => ({
        user: state.user,
        isAuthenticated: state.isAuthenticated,
        tokens: state.tokens,
        onboardingCompleted: state.onboardingCompleted,
      }),
    }
  )
);

export default useAuthStore;
