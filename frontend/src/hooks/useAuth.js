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
    updateUser,
    fetchProfile,
    isAuthenticated: !!user,
  }
}
