import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { bookService } from '../services/bookService'
import { useAuth } from '../hooks/useAuth'
import toast from 'react-hot-toast'

const BookDetailsPage = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const { user, isAuthenticated, hasActiveSubscription } = useAuth()
  
  const [book, setBook] = useState(null)
  const [loading, setLoading] = useState(true)
  const [borrowing, setBorrowing] = useState(false)
  const [previewContent, setPreviewContent] = useState(null)
  const [showPreview, setShowPreview] = useState(false)

  useEffect(() => {
    fetchBook()
  }, [id])

  const fetchBook = async () => {
    setLoading(true)
    try {
      const response = await bookService.getBook(id)
      if (response.success) {
        setBook(response.data)
      } else {
        toast.error('Knjiga nije pronađena')
        navigate('/books')
      }
    } catch (error) {
      toast.error('Greška pri učitavanju knjige')
      navigate('/books')
    }
    setLoading(false)
  }

  const handleBorrow = async () => {
    if (!isAuthenticated) {
      toast.error('Morate se prijaviti da biste pozajmili knjigu')
      navigate('/login')
      return
    }

    setBorrowing(true)
    try {
      const response = await bookService.borrowBook(id)
      if (response.success) {
        toast.success('Knjiga je uspešno pozajmljena!')
        navigate('/loans')
      } else {
        toast.error(response.message || 'Greška pri pozajmljivanju')
      }
    } catch (error) {
      toast.error(error.response?.data?.message || 'Greška pri pozajmljivanju')
    }
    setBorrowing(false)
  }

  const handlePreview = async () => {
    try {
      const response = await bookService.getBookPreview(id)
      if (response.success) {
        setPreviewContent(response.data)
        setShowPreview(true)
      } else {
        toast.error('Greška pri učitavanju pregleda')
      }
    } catch (error) {
      toast.error('Greška pri učitavanju pregleda')
    }
  }

  const handleReadFull = async () => {
    if (!isAuthenticated) {
      toast.error('Morate se prijaviti da biste čitali knjigu')
      navigate('/login')
      return
    }

    if (!hasActiveSubscription) {
      toast.error('Potrebna je aktivna pretplata za čitanje celih knjiga')
      return
    }

    try {
      const response = await bookService.readBook(id)
      if (response.success) {
        // For now, just show the content in a modal or new page
        // In a real app, you'd implement a proper PDF reader
        setPreviewContent(response.data)
        setShowPreview(true)
      } else {
        toast.error('Greška pri učitavanju knjige')
      }
    } catch (error) {
      toast.error('Greška pri učitavanju knjige')
    }
  }

  if (loading) {
    return (
      <div className="max-w-4xl mx-auto">
        <div className="animate-pulse">
          <div className="h-8 bg-gothic-800 rounded mb-4 w-1/3"></div>
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div className="aspect-[3/4] bg-gothic-800 rounded-lg"></div>
            <div className="space-y-4">
              <div className="h-6 bg-gothic-800 rounded w-3/4"></div>
              <div className="h-4 bg-gothic-800 rounded w-1/2"></div>
              <div className="h-4 bg-gothic-800 rounded w-1/3"></div>
              <div className="h-20 bg-gothic-800 rounded"></div>
            </div>
          </div>
        </div>
      </div>
    )
  }

  if (!book) {
    return (
      <div className="max-w-4xl mx-auto text-center py-12">
        <h1 className="text-2xl font-serif text-gothic-300 mb-4">
          Knjiga nije pronađena
        </h1>
        <button
          onClick={() => navigate('/books')}
          className="btn-primary"
        >
          Nazad na knjige
        </button>
      </div>
    )
  }

  return (
    <div className="max-w-6xl mx-auto">
      {/* Back Button */}
      <div className="mb-6">
        <button
          onClick={() => navigate('/books')}
          className="text-gothic-400 hover:text-gothic-200 transition-colors duration-200 flex items-center"
        >
          ← Nazad na knjige
        </button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Book Cover */}
        <div className="card">
          <div className="aspect-[3/4] bg-gothic-800 rounded-lg overflow-hidden">
            {book.cover_url ? (
              <img
                src={book.cover_url}
                alt={`Cover za ${book.title}`}
                className="w-full h-full object-cover"
              />
            ) : (
              <div className="w-full h-full flex items-center justify-center text-gothic-400">
                <div className="text-center">
                  <div className="text-6xl mb-4">📚</div>
                  <p className="text-lg">Nema slike</p>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Book Details */}
        <div className="card">
          <h1 className="text-3xl font-serif font-bold text-gothic-50 mb-4">
            {book.title}
          </h1>

          <div className="space-y-4 mb-6">
            <div>
              <span className="text-gothic-400 text-sm">Autor:</span>
              <p className="text-gothic-100 text-lg">{book.author}</p>
            </div>

            {book.year && (
              <div>
                <span className="text-gothic-400 text-sm">Godina:</span>
                <p className="text-gothic-100">{book.year}</p>
              </div>
            )}

            {book.isbn && (
              <div>
                <span className="text-gothic-400 text-sm">ISBN:</span>
                <p className="text-gothic-100 font-mono">{book.isbn}</p>
              </div>
            )}

            {book.description && (
              <div>
                <span className="text-gothic-400 text-sm">Opis:</span>
                <p className="text-gothic-100 leading-relaxed">
                  {book.description}
                </p>
              </div>
            )}
          </div>

          {/* Actions */}
          <div className="space-y-3">
            <button
              onClick={handlePreview}
              className="btn-secondary w-full"
            >
              Pregled (prve 3 stranice)
            </button>

            {isAuthenticated ? (
              <button
                onClick={handleBorrow}
                disabled={borrowing}
                className="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {borrowing ? 'Pozajmljivanje...' : 'Pozajmi knjigu'}
              </button>
            ) : (
              <button
                onClick={() => navigate('/login')}
                className="btn-primary w-full"
              >
                Prijavi se da pozajmiš
              </button>
            )}

            {isAuthenticated && hasActiveSubscription && (
              <button
                onClick={handleReadFull}
                className="btn-primary w-full bg-accent-600 hover:bg-accent-700"
              >
                Čitaj celu knjigu
              </button>
            )}

            {isAuthenticated && !hasActiveSubscription && (
              <div className="text-center">
                <p className="text-gothic-400 text-sm mb-2">
                  Za čitanje celih knjiga potrebna je pretplata
                </p>
                <button
                  onClick={() => navigate('/subscription')}
                  className="text-accent-400 hover:text-accent-300 text-sm"
                >
                  Aktiviraj pretplatu
                </button>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Preview Modal */}
      {showPreview && previewContent && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-gothic-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div className="flex items-center justify-between p-6 border-b border-gothic-700">
              <h2 className="text-xl font-serif text-gothic-50">
                {book.title} - Pregled
              </h2>
              <button
                onClick={() => setShowPreview(false)}
                className="text-gothic-400 hover:text-gothic-200 text-2xl"
              >
                ×
              </button>
            </div>
            <div className="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
              <div className="prose prose-invert max-w-none">
                <pre className="whitespace-pre-wrap text-gothic-100 leading-relaxed">
                  {previewContent.content}
                </pre>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default BookDetailsPage
