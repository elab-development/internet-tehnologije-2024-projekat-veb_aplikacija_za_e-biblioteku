import { Link } from 'react-router-dom'

const BookCard = ({ book }) => {
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
