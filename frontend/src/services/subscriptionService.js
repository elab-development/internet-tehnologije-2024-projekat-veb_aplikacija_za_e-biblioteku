import axiosInstance from './axiosInstance'

export const subscriptionService = {
  // Get subscription status
  getSubscriptionStatus: async () => {
    const response = await axiosInstance.get('/subscriptions/status')
    return response.data
  },

  // Create new subscription
  createSubscription: async (subscriptionData) => {
    const response = await axiosInstance.post('/subscriptions', subscriptionData)
    return response.data
  },

  // Get subscription history
  getSubscriptionHistory: async (params = {}) => {
    const queryParams = new URLSearchParams()
    
    if (params.page) queryParams.append('page', params.page)
    if (params.per_page) queryParams.append('per_page', params.per_page)

    const response = await axiosInstance.get(`/subscriptions/history?${queryParams.toString()}`)
    return response.data
  },

  // Cancel subscription (if supported)
  cancelSubscription: async (id) => {
    const response = await axiosInstance.delete(`/subscriptions/${id}`)
    return response.data
  },
}
