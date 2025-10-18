import axiosInstance from './axiosInstance'

export const bookService = {
  // Get paginated list of books
  getBooks: async (params = {}) => {
    const queryParams = new URLSearchParams()
    
    if (params.page) queryParams.append('page', params.page)
    if (params.per_page) queryParams.append('per_page', params.per_page)
    if (params.search) queryParams.append('search', params.search)
    if (params.genre) queryParams.append('genre', params.genre)
    if (params.sort_by) queryParams.append('sort_by', params.sort_by)
    if (params.sort_order) queryParams.append('sort_order', params.sort_order)

    const response = await axiosInstance.get(`/books?${queryParams.toString()}`)
    return response.data
  },

  // Get single book by ID
  getBook: async (id) => {
    const response = await axiosInstance.get(`/books/${id}`)
    return response.data
  },

  // Search books
  searchBooks: async (query, params = {}) => {
    const queryParams = new URLSearchParams()
    queryParams.append('search', query)
    
    if (params.page) queryParams.append('page', params.page)
    if (params.per_page) queryParams.append('per_page', params.per_page)
    if (params.genre) queryParams.append('genre', params.genre)
    if (params.sort_by) queryParams.append('sort_by', params.sort_by)
    if (params.sort_order) queryParams.append('sort_order', params.sort_order)

    const response = await axiosInstance.get(`/books/search?${queryParams.toString()}`)
    return response.data
  },

  // Get book preview (first 3 pages)
  getBookPreview: async (id) => {
    const response = await axiosInstance.get(`/books/${id}/preview`)
    return response.data
  },

  // Get specific page of book
  getBookPage: async (id, page) => {
    const response = await axiosInstance.get(`/books/${id}/page?page=${page}`)
    return response.data
  },

  // Read full book (for subscribers)
  readBook: async (id) => {
    const response = await axiosInstance.get(`/books/${id}/read`)
    return response.data
  },

  // Borrow a book
  borrowBook: async (id) => {
    const response = await axiosInstance.post(`/loans`, { book_id: id })
    return response.data
  },

  // Fetch book by ISBN from Open Library
  fetchByIsbn: async (isbn) => {
    const response = await axiosInstance.get(`/books/fetch-by-isbn?isbn=${isbn}`)
    return response.data
  },

  // Admin functions
  createBook: async (bookData) => {
    const response = await axiosInstance.post('/books', bookData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return response.data
  },

  updateBook: async (id, bookData) => {
    const response = await axiosInstance.post(`/books/${id}`, bookData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return response.data
  },

  deleteBook: async (id) => {
    const response = await axiosInstance.delete(`/books/${id}`)
    return response.data
  },

  exportBooks: async (params = {}) => {
    const queryParams = new URLSearchParams()
    
    if (params.search) queryParams.append('search', params.search)
    if (params.genre) queryParams.append('genre', params.genre)

    const response = await axiosInstance.get(`/books/export.csv?${queryParams.toString()}`)
    return response.data
  },
}
