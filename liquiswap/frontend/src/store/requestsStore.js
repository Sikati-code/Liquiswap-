/**
 * Requests Store - Zustand
 * Manages exchange requests and marketplace data
 */

import { create } from 'zustand';
import api from '../utils/api';

const useRequestsStore = create((set, get) => ({
  // State
  requests: [],
  myRequests: [],
  currentRequest: null,
  marketplaceRequests: [],
  isLoading: false,
  error: null,
  hasMore: true,
  page: 1,
  filters: {
    haveType: null,
    wantType: null,
    minAmount: null,
    maxAmount: null,
  },
  stats: null,
  
  // Actions
  setFilters: (filters) => set({ filters, page: 1, marketplaceRequests: [] }),
  clearFilters: () => set({ 
    filters: { haveType: null, wantType: null, minAmount: null, maxAmount: null },
    page: 1,
  }),
  
  fetchMarketplace: async (reset = false) => {
    const { page, filters, marketplaceRequests } = get();
    const currentPage = reset ? 1 : page;
    
    set({ isLoading: true, error: null });
    
    try {
      const params = new URLSearchParams();
      params.append('page', currentPage);
      params.append('limit', 20);
      
      if (filters.haveType) params.append('haveType', filters.haveType);
      if (filters.wantType) params.append('wantType', filters.wantType);
      if (filters.minAmount) params.append('minAmount', filters.minAmount);
      if (filters.maxAmount) params.append('maxAmount', filters.maxAmount);
      
      const response = await api.get(`/requests?${params.toString()}`);
      const { requests, pagination } = response.data.data;
      
      set({
        marketplaceRequests: reset 
          ? requests 
          : [...marketplaceRequests, ...requests],
        hasMore: pagination.page < pagination.pages,
        page: currentPage + 1,
        isLoading: false,
      });
      
      return requests;
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to fetch marketplace',
        isLoading: false,
      });
      return [];
    }
  },
  
  fetchMyRequests: async (status = null) => {
    set({ isLoading: true, error: null });
    
    try {
      const params = new URLSearchParams();
      if (status) params.append('status', status);
      
      const response = await api.get(`/requests/my?${params.toString()}`);
      const { requests } = response.data.data;
      
      set({
        myRequests: requests,
        isLoading: false,
      });
      
      return requests;
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to fetch requests',
        isLoading: false,
      });
      return [];
    }
  },
  
  fetchRequest: async (id) => {
    set({ isLoading: true, error: null });
    
    try {
      const response = await api.get(`/requests/${id}`);
      const { request } = response.data.data;
      
      set({
        currentRequest: request,
        isLoading: false,
      });
      
      return request;
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to fetch request',
        isLoading: false,
      });
      return null;
    }
  },
  
  createRequest: async (requestData) => {
    set({ isLoading: true, error: null });
    
    try {
      const response = await api.post('/requests', requestData);
      const { request, matched, match, transaction } = response.data.data;
      
      set((state) => ({
        myRequests: [request, ...state.myRequests],
        isLoading: false,
      }));
      
      return { 
        success: true, 
        request, 
        matched, 
        match, 
        transaction,
        message: response.data.message 
      };
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to create request',
        isLoading: false,
      });
      return { 
        success: false, 
        error: error.response?.data?.message 
      };
    }
  },
  
  matchRequest: async (requestId) => {
    set({ isLoading: true, error: null });
    
    try {
      const response = await api.post(`/requests/${requestId}/match`);
      const { request, match, transaction } = response.data.data;
      
      set({ isLoading: false });
      
      return { 
        success: true, 
        request, 
        match, 
        transaction,
        message: response.data.message 
      };
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to match request',
        isLoading: false,
      });
      return { 
        success: false, 
        error: error.response?.data?.message 
      };
    }
  },
  
  cancelRequest: async (requestId) => {
    set({ isLoading: true, error: null });
    
    try {
      await api.delete(`/requests/${requestId}`);
      
      set((state) => ({
        myRequests: state.myRequests.map(r =>
          r.id === requestId ? { ...r, status: 'CANCELLED' } : r
        ),
        isLoading: false,
      }));
      
      return { success: true };
    } catch (error) {
      set({
        error: error.response?.data?.message || 'Failed to cancel request',
        isLoading: false,
      });
      return { 
        success: false, 
        error: error.response?.data?.message 
      };
    }
  },
  
  updateRequestStatus: (requestId, status) => {
    set((state) => ({
      myRequests: state.myRequests.map(r =>
        r.id === requestId ? { ...r, status } : r
      ),
      marketplaceRequests: state.marketplaceRequests.filter(r => r.id !== requestId),
    }));
  },
  
  addRequest: (request) => {
    set((state) => ({
      myRequests: [request, ...state.myRequests],
    }));
  },
  
  fetchStats: async () => {
    try {
      const response = await api.get('/requests/stats/overview');
      set({ stats: response.data.data });
      return response.data.data;
    } catch (error) {
      console.log('Fetch stats error:', error);
      return null;
    }
  },
  
  clearError: () => set({ error: null }),
  clearCurrentRequest: () => set({ currentRequest: null }),
}));

export default useRequestsStore;
