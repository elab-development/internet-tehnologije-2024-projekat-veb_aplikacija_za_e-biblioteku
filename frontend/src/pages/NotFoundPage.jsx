import { Link } from 'react-router-dom'

const NotFoundPage = () => {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gothic-gradient">
      <div className="text-center">
        <div className="mb-8">
          <h1 className="text-9xl font-serif font-bold text-gothic-50 mb-4">404</h1>
          <h2 className="text-3xl font-serif font-semibold text-gothic-300 mb-4">
            Stranica nije pronađena
          </h2>
          <p className="text-lg text-gothic-400 mb-8 max-w-md mx-auto">
            Izvinjavamo se, stranica koju tražite ne postoji ili je uklonjena.
          </p>
        </div>
        
        <div className="space-y-4">
          <Link to="/" className="btn-primary">
            Idi na početnu stranu
          </Link>
          <div className="text-center">
            <Link to="/books" className="text-accent-400 hover:text-accent-300 transition-colors duration-200">
              Pregledaj knjige
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}

export default NotFoundPage
