import axiosInstance from './axiosInstance'

export const loanService = {
  // Get user's loans
  getLoans: async (params = {}) => {
    const queryParams = new URLSearchParams()
    
    if (params.page) queryParams.append('page', params.page)
    if (params.per_page) queryParams.append('per_page', params.per_page)
    if (params.only_active) queryParams.append('only_active', params.only_active)
    if (params.user_id) queryParams.append('user_id', params.user_id)

    const response = await axiosInstance.get(`/loans?${queryParams.toString()}`)
    return response.data
  },

  // Get single loan by ID
  getLoan: async (id) => {
    const response = await axiosInstance.get(`/loans/${id}`)
    return response.data
  },

  // Create new loan
  createLoan: async (bookId) => {
    const response = await axiosInstance.post('/loans', { book_id: bookId })
    return response.data
  },

  // Return a loan
  returnLoan: async (id) => {
    const response = await axiosInstance.put(`/loans/${id}/return`)
    return response.data
  },

  // Export loans as CSV (admin only)
  exportLoans: async (params = {}) => {
    const queryParams = new URLSearchParams()
    
    if (params.only_active) queryParams.append('only_active', params.only_active)
    if (params.user_id) queryParams.append('user_id', params.user_id)

    const response = await axiosInstance.get(`/loans/export.csv?${queryParams.toString()}`, {
      responseType: 'blob'
    })
    return response.data
  },
}
