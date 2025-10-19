import { useState, useEffect } from 'react'
import { useSearchParams } from 'react-router-dom'
import { bookService } from '../services/bookService'
import BookCard from '../components/BookCard'
import toast from 'react-hot-toast'

const BooksPage = () => {
  const [searchParams, setSearchParams] = useSearchParams()
  const [books, setBooks] = useState([])
  const [loading, setLoading] = useState(true)
  const [pagination, setPagination] = useState({})
  const [filters, setFilters] = useState({
    search: searchParams.get('search') || '',
    genre: searchParams.get('genre') || '',
    sort_by: searchParams.get('sort_by') || 'title',
    sort_order: searchParams.get('sort_order') || 'asc',
  })

  useEffect(() => {
    fetchBooks()
  }, [searchParams])

  useEffect(() => {
    fetchBooks()
  }, [filters])

  const fetchBooks = async () => {
    setLoading(true)
    try {
      const params = {
        page: searchParams.get('page') || 1,
        per_page: 12,
        search: searchParams.get('search') || '',
        genre: searchParams.get('genre') || '',
        sort_by: searchParams.get('sort_by') || 'title',
        sort_order: searchParams.get('sort_order') || 'asc',
      }

      const response = await bookService.getBooks(params)
      
      if (response.data) {
        setBooks(response.data)
        setPagination(response.meta)
      } else {
        toast.error('Greška pri učitavanju knjiga')
      }
    } catch (error) {
      toast.error('Greška pri učitavanju knjiga')
      console.error('Error fetching books:', error)
    }
    setLoading(false)
  }

  const handleSearch = (e) => {
    e.preventDefault()
    const newParams = new URLSearchParams(searchParams)
    newParams.set('search', filters.search)
    newParams.set('page', '1') // Reset to first page
    setSearchParams(newParams)
  }

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({ ...prev, [key]: value }))
    const newParams = new URLSearchParams(searchParams)
    newParams.set(key, value)
    newParams.set('page', '1') // Reset to first page
    setSearchParams(newParams)
  }

  const handlePageChange = (page) => {
    const newParams = new URLSearchParams(searchParams)
    newParams.set('page', page)
    setSearchParams(newParams)
  }

  return (
    <div className="max-w-7xl mx-auto">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-serif font-bold text-gothic-50 mb-2">
          Kolekcija knjiga
        </h1>
        <p className="text-gothic-400">
          Otkrijte našu bogatu kolekciju knjiga
        </p>
      </div>

      {/* Search and Filters */}
      <div className="card mb-8">
        <form onSubmit={handleSearch} className="mb-6">
          <div className="flex gap-4">
            <div className="flex-1">
              <input
                type="text"
                placeholder="Pretražite po naslovu ili autoru..."
                className="input-field w-full"
                value={filters.search}
                onChange={(e) => setFilters(prev => ({ ...prev, search: e.target.value }))}
              />
            </div>
            <button type="submit" className="btn-primary px-6">
              Pretraži
            </button>
          </div>
        </form>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-medium text-gothic-300 mb-2">
              Žanr
            </label>
            <select
              className="input-field w-full"
              value={filters.genre}
              onChange={(e) => handleFilterChange('genre', e.target.value)}
            >
              <option value="">Svi žanrovi</option>
              <option value="Autobiography">Autobiografija</option>
              <option value="Crime">Krimi</option>
              <option value="Dystopian Fiction">Distopijska fikcija</option>
              <option value="Fantasy">Fantazija</option>
              <option value="Fiction">Fikcija</option>
              <option value="Historical Fiction">Istorijska fikcija</option>
              <option value="Magical Realism">Magični realizam</option>
              <option value="Mystery">Misterija</option>
              <option value="Non-fiction">Ne-fikcija</option>
              <option value="Philosophy">Filozofija</option>
              <option value="Romance">Romansa</option>
              <option value="Satire">Satira</option>
              <option value="Science Fiction">Naučna fantastika</option>
              <option value="Thriller">Triler</option>
              <option value="Young Adult">Mladi odrasli</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gothic-300 mb-2">
              Sortiraj po
            </label>
            <select
              className="input-field w-full"
              value={filters.sort_by}
              onChange={(e) => handleFilterChange('sort_by', e.target.value)}
            >
              <option value="title">Naslov</option>
              <option value="author">Autor</option>
              <option value="year">Godina</option>
              <option value="created_at">Datum dodavanja</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gothic-300 mb-2">
              Redosled
            </label>
            <select
              className="input-field w-full"
              value={filters.sort_order}
              onChange={(e) => handleFilterChange('sort_order', e.target.value)}
            >
              <option value="asc">Rastući</option>
              <option value="desc">Opadajući</option>
            </select>
          </div>
        </div>
      </div>

      {/* Books Grid */}
      {loading ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {[...Array(8)].map((_, index) => (
            <div key={index} className="card animate-pulse">
              <div className="aspect-[3/4] bg-gothic-800 rounded-lg mb-4"></div>
              <div className="h-4 bg-gothic-800 rounded mb-2"></div>
              <div className="h-3 bg-gothic-800 rounded mb-2 w-3/4"></div>
              <div className="h-3 bg-gothic-800 rounded mb-4 w-1/2"></div>
              <div className="h-10 bg-gothic-800 rounded"></div>
            </div>
          ))}
        </div>
      ) : books.length > 0 ? (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            {books.map((book) => (
              <BookCard key={book.id} book={book} />
            ))}
          </div>

          {/* Pagination */}
          {pagination.last_page > 1 && (
            <div className="flex justify-center items-center space-x-2">
              <button
                onClick={() => handlePageChange(pagination.current_page - 1)}
                disabled={pagination.current_page === 1}
                className="btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Prethodna
              </button>
              
              <span className="text-gothic-300 px-4">
                Strana {pagination.current_page} od {pagination.last_page}
              </span>
              
              <button
                onClick={() => handlePageChange(pagination.current_page + 1)}
                disabled={pagination.current_page === pagination.last_page}
                className="btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Sledeća
              </button>
            </div>
          )}
        </>
      ) : (
        <div className="text-center py-12">
          <div className="text-6xl mb-4">📚</div>
          <h3 className="text-xl font-serif text-gothic-300 mb-2">
            Nema rezultata
          </h3>
          <p className="text-gothic-400">
            Pokušajte da promenite kriterijume pretrage
          </p>
        </div>
      )}
    </div>
  )
}

export default BooksPage
