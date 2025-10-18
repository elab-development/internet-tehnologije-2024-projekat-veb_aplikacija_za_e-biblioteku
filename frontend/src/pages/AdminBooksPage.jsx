import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { bookService } from '../services/bookService'
import { useAuth } from '../hooks/useAuth'
import toast from 'react-hot-toast'

const AdminBooksPage = () => {
  const { isAdmin } = useAuth()
  const [books, setBooks] = useState([])
  const [loading, setLoading] = useState(true)
  const [pagination, setPagination] = useState({})
  const [filters, setFilters] = useState({
    search: '',
    genre: '',
    sort_by: 'title',
    sort_order: 'asc',
  })

  useEffect(() => {
    if (isAdmin) {
      fetchBooks()
    }
  }, [isAdmin])

  const fetchBooks = async () => {
    setLoading(true)
    try {
      const params = {
        page: 1,
        per_page: 50, // Show more books in admin view
        search: filters.search,
        genre: filters.genre,
        sort_by: filters.sort_by,
        sort_order: filters.sort_order,
      }

      const response = await bookService.getBooks(params)
      
      if (response.success) {
        setBooks(response.data.data)
        setPagination(response.data)
      } else {
        toast.error('Greška pri učitavanju knjiga')
      }
    } catch (error) {
      toast.error('Greška pri učitavanju knjiga')
      console.error('Error fetching books:', error)
    }
    setLoading(false)
  }

  const handleDelete = async (id) => {
    if (window.confirm('Da li ste sigurni da želite da obrišete ovu knjigu?')) {
      try {
        const response = await bookService.deleteBook(id)
        if (response.success) {
          toast.success('Knjiga je uspešno obrisana!')
          fetchBooks() // Refresh the list
        } else {
          toast.error(response.message || 'Greška pri brisanju knjige')
        }
      } catch (error) {
        toast.error(error.response?.data?.message || 'Greška pri brisanju knjige')
      }
    }
  }

  const handleExport = async () => {
    try {
      const response = await bookService.exportBooks()
      
      // Create download link
      const blob = new Blob([response], { type: 'text/csv' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `knjige_${new Date().toISOString().split('T')[0]}.csv`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
      
      toast.success('CSV fajl je preuzet')
    } catch (error) {
      toast.error('Greška pri preuzimanju CSV fajla')
    }
  }

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({ ...prev, [key]: value }))
  }

  const applyFilters = () => {
    fetchBooks()
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
        <Link to="/books" className="btn-primary">
          Nazad na knjige
        </Link>
      </div>
    )
  }

  return (
    <div className="max-w-7xl mx-auto">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-serif font-bold text-gothic-50 mb-2">
          Administracija knjiga
        </h1>
        <p className="text-gothic-400">
          Upravljajte kolekcijom knjiga
        </p>
      </div>

      {/* Actions */}
      <div className="card mb-8">
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div className="flex flex-col md:flex-row gap-4 flex-1">
            <input
              type="text"
              placeholder="Pretražite knjige..."
              className="input-field flex-1"
              value={filters.search}
              onChange={(e) => handleFilterChange('search', e.target.value)}
            />
            
            <select
              className="input-field"
              value={filters.genre}
              onChange={(e) => handleFilterChange('genre', e.target.value)}
            >
              <option value="">Svi žanrovi</option>
              <option value="fiction">Fikcija</option>
              <option value="non-fiction">Non-fikcija</option>
              <option value="science">Nauka</option>
              <option value="history">Istorija</option>
              <option value="biography">Biografija</option>
              <option value="poetry">Poezija</option>
            </select>
            
            <select
              className="input-field"
              value={filters.sort_by}
              onChange={(e) => handleFilterChange('sort_by', e.target.value)}
            >
              <option value="title">Naslov</option>
              <option value="author">Autor</option>
              <option value="year">Godina</option>
              <option value="created_at">Datum dodavanja</option>
            </select>
            
            <select
              className="input-field"
              value={filters.sort_order}
              onChange={(e) => handleFilterChange('sort_order', e.target.value)}
            >
              <option value="asc">Rastući</option>
              <option value="desc">Opadajući</option>
            </select>
            
            <button onClick={applyFilters} className="btn-secondary">
              Primeni filtere
            </button>
          </div>
          
          <div className="flex gap-2">
            <button onClick={handleExport} className="btn-secondary">
              Preuzmi CSV
            </button>
            <Link to="/admin/books/create" className="btn-primary">
              Dodaj knjigu
            </Link>
          </div>
        </div>
      </div>

      {/* Books Table */}
      {loading ? (
        <div className="card">
          <div className="animate-pulse">
            <div className="h-12 bg-gothic-800 rounded mb-4"></div>
            {[...Array(10)].map((_, index) => (
              <div key={index} className="h-16 bg-gothic-800 rounded mb-2"></div>
            ))}
          </div>
        </div>
      ) : books.length > 0 ? (
        <div className="card overflow-hidden">
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gothic-800">
                <tr>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Slika</th>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Naslov</th>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Autor</th>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Godina</th>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">ISBN</th>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Datum dodavanja</th>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Akcije</th>
                </tr>
              </thead>
              <tbody>
                {books.map((book) => (
                  <tr key={book.id} className="border-b border-gothic-700 last:border-b-0 hover:bg-gothic-800/50">
                    <td className="py-3 px-4">
                      <div className="w-12 h-16 bg-gothic-800 rounded overflow-hidden">
                        {book.cover_url ? (
                          <img
                            src={book.cover_url}
                            alt={book.title}
                            className="w-full h-full object-cover"
                          />
                        ) : (
                          <div className="w-full h-full flex items-center justify-center text-gothic-400 text-xs">
                            📚
                          </div>
                        )}
                      </div>
                    </td>
                    <td className="py-3 px-4 text-gothic-100 font-medium">{book.title}</td>
                    <td className="py-3 px-4 text-gothic-200">{book.author}</td>
                    <td className="py-3 px-4 text-gothic-200">{book.year || '-'}</td>
                    <td className="py-3 px-4 text-gothic-200 font-mono text-sm">{book.isbn || '-'}</td>
                    <td className="py-3 px-4 text-gothic-200 text-sm">
                      {new Date(book.created_at).toLocaleDateString('sr-RS')}
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex gap-2">
                        <Link
                          to={`/admin/books/${book.id}/edit`}
                          className="text-accent-400 hover:text-accent-300 text-sm"
                        >
                          Izmeni
                        </Link>
                        <button
                          onClick={() => handleDelete(book.id)}
                          className="text-red-400 hover:text-red-300 text-sm"
                        >
                          Obriši
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      ) : (
        <div className="text-center py-12">
          <div className="text-6xl mb-4">📚</div>
          <h3 className="text-xl font-serif text-gothic-300 mb-2">
            Nema knjiga
          </h3>
          <p className="text-gothic-400 mb-6">
            Nema knjiga koje odgovaraju vašim kriterijumima
          </p>
          <Link to="/admin/books/create" className="btn-primary">
            Dodaj prvu knjigu
          </Link>
        </div>
      )}
    </div>
  )
}

export default AdminBooksPage
