/**
 * API Utility
 * Axios instance with interceptors for authentication
 */

import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// API Base URL
export const API_URL = process.env.EXPO_PUBLIC_API_URL || 'http://localhost:3000/api';

// Create axios instance
const api = axios.create({
  baseURL: API_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor - add auth token
api.interceptors.request.use(
  async (config) => {
    try {
      const authData = await AsyncStorage.getItem('auth-storage');
      const parsedAuth = authData ? JSON.parse(authData) : null;
      const token = parsedAuth?.state?.tokens?.accessToken;
      
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (error) {
      console.log('Error getting token:', error);
    }
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor - handle token refresh
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;
    
    // If 401 and not already retrying
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      
      try {
        // Get refresh token
        const authData = await AsyncStorage.getItem('auth-storage');
        const parsedAuth = authData ? JSON.parse(authData) : null;
        const refreshToken = parsedAuth?.state?.tokens?.refreshToken;
        
        if (refreshToken) {
          // Try to refresh token
          const response = await axios.post(`${API_URL}/auth/refresh`, {
            refreshToken,
          });
          
          const { accessToken, refreshToken: newRefreshToken } = response.data.data;
          
          // Update stored tokens
          const newAuthData = {
            ...parsedAuth,
            state: {
              ...parsedAuth.state,
              tokens: {
                accessToken,
                refreshToken: newRefreshToken,
              },
            },
          };
          await AsyncStorage.setItem('auth-storage', JSON.stringify(newAuthData));
          
          // Retry original request
          originalRequest.headers.Authorization = `Bearer ${accessToken}`;
          return api(originalRequest);
        }
      } catch (refreshError) {
        // Refresh failed, clear auth and redirect to login
        await AsyncStorage.removeItem('auth-storage');
        // Navigation will be handled by auth state change
      }
    }
    
    return Promise.reject(error);
  }
);

export default api;
