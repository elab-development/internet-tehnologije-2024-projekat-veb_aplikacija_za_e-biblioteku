import { Link } from 'react-router-dom'
import { useState } from 'react'
import { useAuth } from '../hooks/useAuth'
import { bookService } from '../services/bookService'
import toast from 'react-hot-toast'

const BookCard = ({ book, onBookUpdate }) => {
  const { isAuthenticated } = useAuth()
  const [liking, setLiking] = useState(false)

  const handleLike = async (e) => {
    e.preventDefault()
    e.stopPropagation()
    
    if (!isAuthenticated) {
      toast.error('Morate se prijaviti da biste lajkovali knjigu')
      return
    }

    setLiking(true)
    try {
      const response = await bookService.toggleLike(book.id)
      if (response.data) {
        onBookUpdate?.(book.id, {
          is_liked_by_user: response.data.is_liked,
          likes_count: response.data.likes_count
        })
        toast.success(response.message)
      }
    } catch (error) {
      console.error('Error toggling like:', error)
      toast.error(error.response?.data?.message || 'Greška pri lajkovanju')
    }
    setLiking(false)
  }
  return (
    <div className="card hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
      <div className="flex flex-col h-full">
        {/* Cover Image */}
        <div className="aspect-[3/4] bg-gothic-800 rounded-lg mb-4 overflow-hidden">
          {book.cover_url ? (
            <img
              src={book.cover_url}
              alt={`Cover za ${book.title}`}
              className="w-full h-full object-cover"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-gothic-400">
              <div className="text-center">
                <div className="text-4xl mb-2">📚</div>
                <p className="text-sm">Nema slike</p>
              </div>
            </div>
          )}
        </div>

        {/* Book Info */}
        <div className="flex-1 flex flex-col">
          <h3 className="text-lg font-serif font-semibold text-gothic-50 mb-2 line-clamp-2">
            {book.title}
          </h3>
          
          <p className="text-gothic-300 text-sm mb-2">
            {book.author}
          </p>
          
          {book.genre && (
            <p className="text-gothic-400 text-sm mb-2">
              {book.genre}
            </p>
          )}
          
          {book.year && (
            <p className="text-gothic-400 text-sm mb-3">
              {book.year}
            </p>
          )}

          <div className="flex items-center justify-between mb-3">
            <div className="flex items-center gap-1">
              <span className="text-gothic-400 text-sm">❤️</span>
              <span className="text-gothic-400 text-sm">{book.likes_count || 0}</span>
            </div>
            <button
              onClick={handleLike}
              disabled={liking}
              className={`p-1 rounded transition-colors disabled:opacity-50 ${
                book?.is_liked_by_user 
                  ? 'text-red-500 hover:text-red-400' 
                  : 'text-gothic-400 hover:text-red-500'
              }`}
            >
              {liking ? '⏳' : (book?.is_liked_by_user ? '❤️' : '🤍')}
            </button>
          </div>

          {book.description && (
            <p className="text-gothic-400 text-sm mb-4 line-clamp-3 flex-1">
              {book.description}
            </p>
          )}

          {/* Actions */}
          <div className="mt-auto">
            <Link
              to={`/books/${book.id}`}
              className="btn-primary w-full text-center block"
            >
              Pogledaj detalje
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}

export default BookCard
