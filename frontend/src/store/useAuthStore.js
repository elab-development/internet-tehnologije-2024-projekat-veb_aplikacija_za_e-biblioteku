import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import axiosInstance from '../services/axiosInstance'

const useAuthStore = create(
  persist(
    (set, get) => ({
      user: null,
      token: localStorage.getItem('auth_token'),
      isLoading: false,

        // Login function
        login: async (email, password) => {
          set({ isLoading: true })
          try {
            const response = await axiosInstance.post('/login', {
              email,
              password,
            })

            const { user, token } = response.data
            console.log('Login response:', response.data) // Debug log

            set({
              user,
              token,
              isLoading: false,
            })

            // Čuvaj token u localStorage za axiosInstance
            localStorage.setItem('auth_token', token)
            console.log('Token saved to localStorage:', token) // Debug log

            return { success: true, user, token }
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
          const response = await axiosInstance.post('/register', {
            name,
            email,
            password,
            password_confirmation,
          })

          const { user, token } = response.data

          set({
            user,
            token,
            isLoading: false,
          })

            // Čuvaj token u localStorage za axiosInstance
            localStorage.setItem('auth_token', token)

            const profileResult = await get().fetchProfile()
            if (profileResult.success) {
              set({ user: profileResult.user })
            }

            return { success: true, user, token }
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

      // Check if user is authenticated
      isAuthenticated: () => {
        const { user, token } = get()
        return !!(user && token)
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
          const responseData = response.data

          const userData = responseData.data.user
          const subscription = responseData.data.subscription

          set((state) => ({
            user: { 
              ...state.user, 
              ...userData,
              subscription: subscription
            },
          }))

          return { success: true, user: { ...userData, subscription } }
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
