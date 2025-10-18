import { useAuthStore } from '../store/useAuthStore'

export const useAuth = () => {
  const {
    user,
    token,
    isLoading,
    login,
    register,
    logout,
    isAdmin,
    hasActiveSubscription,
    isAuthenticated,
    updateUser,
    fetchProfile,
  } = useAuthStore()

  return {
    user,
    token,
    isLoading,
    login,
    register,
    logout,
    isAdmin: isAdmin(),
    hasActiveSubscription: hasActiveSubscription(),
    isAuthenticated: isAuthenticated(),
    updateUser,
    fetchProfile,
  }
}
