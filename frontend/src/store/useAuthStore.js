import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import axiosInstance from '../services/axiosInstance'

const useAuthStore = create(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      isLoading: false,

      // Login function
      login: async (email, password) => {
        set({ isLoading: true })
        try {
          const response = await axiosInstance.post('/auth/login', {
            email,
            password,
          })

          const { user, token } = response.data.data

          set({
            user,
            token,
            isLoading: false,
          })

          return { success: true, user }
        } catch (error) {
          set({ isLoading: false })
          return {
            success: false,
            error: error.response?.data?.message || 'Login failed',
          }
        }
      },

      // Register function
      register: async (name, email, password, password_confirmation) => {
        set({ isLoading: true })
        try {
          const response = await axiosInstance.post('/auth/register', {
            name,
            email,
            password,
            password_confirmation,
          })

          const { user, token } = response.data.data

          set({
            user,
            token,
            isLoading: false,
          })

          return { success: true, user }
        } catch (error) {
          set({ isLoading: false })
          return {
            success: false,
            error: error.response?.data?.message || 'Registration failed',
          }
        }
      },

      // Logout function
      logout: () => {
        set({
          user: null,
          token: null,
        })
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
      },

      // Check if user is admin
      isAdmin: () => {
        const { user } = get()
        return user?.role === 'admin'
      },

      // Check if user has active subscription
      hasActiveSubscription: () => {
        const { user } = get()
        return user?.subscription?.active === true
      },

      // Update user data (after profile changes)
      updateUser: (userData) => {
        set((state) => ({
          user: { ...state.user, ...userData },
        }))
      },

      // Fetch user profile from /me endpoint
      fetchProfile: async () => {
        try {
          const response = await axiosInstance.get('/me')
          const userData = response.data.data

          set((state) => ({
            user: { ...state.user, ...userData },
          }))

          return { success: true, user: userData }
        } catch (error) {
          return {
            success: false,
            error: error.response?.data?.message || 'Failed to fetch profile',
          }
        }
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({
        user: state.user,
        token: state.token,
      }),
    }
  )
)

export { useAuthStore }
