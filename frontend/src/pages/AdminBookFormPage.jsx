import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { bookService } from '../services/bookService'
import { useAuth } from '../hooks/useAuth'
import toast from 'react-hot-toast'

const AdminBookFormPage = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const { isAdmin } = useAuth()
  
  const [formData, setFormData] = useState({
    title: '',
    author: '',
    year: '',
    description: '',
    isbn: '',
    cover_image: null,
    pdf_file: null,
  })
  const [loading, setLoading] = useState(false)
  const [isEditMode, setIsEditMode] = useState(false)
  const [isbnLoading, setIsbnLoading] = useState(false)

  useEffect(() => {
    if (!isAdmin) {
      navigate('/books')
      return
    }

    if (id) {
      setIsEditMode(true)
      fetchBook()
    }
  }, [id, isAdmin, navigate])

  const fetchBook = async () => {
    setLoading(true)
    try {
      const response = await bookService.getBook(id)
      if (response.success) {
        const book = response.data
        setFormData({
          title: book.title || '',
          author: book.author || '',
          year: book.year || '',
          description: book.description || '',
          isbn: book.isbn || '',
          cover_image: null, // Don't pre-fill file inputs
          pdf_file: null,    // Don't pre-fill file inputs
        })
      } else {
        toast.error('Knjiga nije pronađena')
        navigate('/admin/books')
      }
    } catch (error) {
      toast.error('Greška pri učitavanju knjige')
      navigate('/admin/books')
    }
    setLoading(false)
  }

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
  }

  const handleFileChange = (e) => {
    const { name, files } = e.target
    setFormData(prev => ({ ...prev, [name]: files[0] }))
  }

  const handleFetchByIsbn = async () => {
    if (!formData.isbn) {
      toast.error('Unesite ISBN za pretragu')
      return
    }

    setIsbnLoading(true)
    try {
      const response = await bookService.fetchByIsbn(formData.isbn)
      if (response.success) {
        const fetchedData = response.data
        setFormData(prev => ({
          ...prev,
          title: fetchedData.title || prev.title,
          author: fetchedData.author || prev.author,
          year: fetchedData.year || prev.year,
          description: fetchedData.description || prev.description,
        }))
        toast.success('Podaci o knjizi uspešno preuzeti!')
      } else {
        toast.error(response.message || 'Greška pri preuzimanju podataka')
      }
    } catch (error) {
      toast.error(error.response?.data?.message || 'Greška pri preuzimanju podataka')
    }
    setIsbnLoading(false)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)

    try {
      const formDataToSend = new FormData()
      
      // Add text fields
      formDataToSend.append('title', formData.title)
      formDataToSend.append('author', formData.author)
      formDataToSend.append('year', formData.year)
      formDataToSend.append('description', formData.description)
      formDataToSend.append('isbn', formData.isbn)
      
      // Add files if selected
      if (formData.cover_image) {
        formDataToSend.append('cover_image', formData.cover_image)
      }
      if (formData.pdf_file) {
        formDataToSend.append('pdf_file', formData.pdf_file)
      }

      let response
      if (isEditMode) {
        response = await bookService.updateBook(id, formDataToSend)
      } else {
        response = await bookService.createBook(formDataToSend)
      }

      if (response.success) {
        toast.success(`Knjiga je uspešno ${isEditMode ? 'ažurirana' : 'dodana'}!`)
        navigate('/admin/books')
      } else {
        toast.error(response.message || `Greška pri ${isEditMode ? 'ažuriranju' : 'dodavanju'} knjige`)
      }
    } catch (error) {
      toast.error(error.response?.data?.message || `Greška pri ${isEditMode ? 'ažuriranju' : 'dodavanju'} knjige`)
    }
    setLoading(false)
  }

  if (!isAdmin) {
    return (
      <div className="max-w-4xl mx-auto text-center py-12">
        <h1 className="text-2xl font-serif text-gothic-300 mb-4">
          Nemate dozvolu za pristup admin panelu
        </h1>
        <p className="text-gothic-400 mb-6">
          Ova stranica je dostupna samo administratorima.
        </p>
        <button onClick={() => navigate('/books')} className="btn-primary">
          Nazad na knjige
        </button>
      </div>
    )
  }

  if (loading && isEditMode) {
    return (
      <div className="max-w-4xl mx-auto">
        <div className="animate-pulse">
          <div className="h-8 bg-gothic-800 rounded mb-4 w-1/3"></div>
          <div className="space-y-4">
            <div className="h-12 bg-gothic-800 rounded"></div>
            <div className="h-12 bg-gothic-800 rounded"></div>
            <div className="h-12 bg-gothic-800 rounded"></div>
            <div className="h-32 bg-gothic-800 rounded"></div>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="max-w-4xl mx-auto">
      {/* Header */}
      <div className="mb-8">
        <button
          onClick={() => navigate('/admin/books')}
          className="text-gothic-400 hover:text-gothic-200 transition-colors duration-200 flex items-center mb-4"
        >
          ← Nazad na admin panel
        </button>
        
        <h1 className="text-3xl font-serif font-bold text-gothic-50 mb-2">
          {isEditMode ? 'Izmeni knjigu' : 'Dodaj novu knjigu'}
        </h1>
        <p className="text-gothic-400">
          {isEditMode ? 'Ažurirajte informacije o knjizi' : 'Unesite informacije o novoj knjizi'}
        </p>
      </div>

      {/* Form */}
      <div className="card">
        <form onSubmit={handleSubmit} className="space-y-6">
          {/* ISBN Section */}
          <div>
            <label htmlFor="isbn" className="block text-sm font-medium text-gothic-300 mb-2">
              ISBN
            </label>
            <div className="flex gap-2">
              <input
                type="text"
                id="isbn"
                name="isbn"
                value={formData.isbn}
                onChange={handleChange}
                className="input-field flex-1"
                placeholder="Unesite ISBN"
              />
              <button
                type="button"
                onClick={handleFetchByIsbn}
                disabled={isbnLoading}
                className="btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isbnLoading ? 'Preuzimam...' : 'Preuzmi sa Open Library'}
              </button>
            </div>
          </div>

          {/* Basic Info */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label htmlFor="title" className="block text-sm font-medium text-gothic-300 mb-2">
                Naslov *
              </label>
              <input
                type="text"
                id="title"
                name="title"
                value={formData.title}
                onChange={handleChange}
                className="input-field w-full"
                required
                placeholder="Unesite naslov knjige"
              />
            </div>

            <div>
              <label htmlFor="author" className="block text-sm font-medium text-gothic-300 mb-2">
                Autor *
              </label>
              <input
                type="text"
                id="author"
                name="author"
                value={formData.author}
                onChange={handleChange}
                className="input-field w-full"
                required
                placeholder="Unesite ime autora"
              />
            </div>
          </div>

          <div>
            <label htmlFor="year" className="block text-sm font-medium text-gothic-300 mb-2">
              Godina izdanja
            </label>
            <input
              type="number"
              id="year"
              name="year"
              value={formData.year}
              onChange={handleChange}
              className="input-field w-full"
              placeholder="Unesite godinu izdanja"
              min="1000"
              max="2030"
            />
          </div>

          <div>
            <label htmlFor="description" className="block text-sm font-medium text-gothic-300 mb-2">
              Opis
            </label>
            <textarea
              id="description"
              name="description"
              value={formData.description}
              onChange={handleChange}
              rows="5"
              className="input-field w-full"
              placeholder="Unesite opis knjige"
            />
          </div>

          {/* File Uploads */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label htmlFor="cover_image" className="block text-sm font-medium text-gothic-300 mb-2">
                Naslovna slika
              </label>
              <input
                type="file"
                id="cover_image"
                name="cover_image"
                accept="image/jpeg,image/png,image/jpg"
                onChange={handleFileChange}
                className="input-field w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-accent-500 file:text-white hover:file:bg-accent-600"
              />
              <p className="text-gothic-400 text-sm mt-1">
                Podržani formati: JPG, PNG (max 5MB)
              </p>
            </div>

            <div>
              <label htmlFor="pdf_file" className="block text-sm font-medium text-gothic-300 mb-2">
                PDF fajl
              </label>
              <input
                type="file"
                id="pdf_file"
                name="pdf_file"
                accept="application/pdf"
                onChange={handleFileChange}
                className="input-field w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-accent-500 file:text-white hover:file:bg-accent-600"
              />
              <p className="text-gothic-400 text-sm mt-1">
                PDF format (max 50MB)
              </p>
            </div>
          </div>

          {/* Submit Buttons */}
          <div className="flex justify-end gap-4 pt-6 border-t border-gothic-700">
            <button
              type="button"
              onClick={() => navigate('/admin/books')}
              className="btn-secondary"
            >
              Odustani
            </button>
            <button
              type="submit"
              disabled={loading}
              className="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Čuvam...' : (isEditMode ? 'Ažuriraj knjigu' : 'Dodaj knjigu')}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

export default AdminBookFormPage
