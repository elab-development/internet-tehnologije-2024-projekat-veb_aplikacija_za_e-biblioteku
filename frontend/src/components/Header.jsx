import { Link, useNavigate } from 'react-router-dom'
import { useAuthStore } from '../store/useAuthStore'

const Header = () => {
  const { user, logout, isAdmin, isAuthenticated } = useAuthStore()
  const navigate = useNavigate()

  const handleLogout = () => {
    logout()
    navigate('/login')
  }

  return (
    <header className="bg-gothic-900 border-b border-gothic-700 shadow-lg">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          <Link to="/" className="flex items-center space-x-2">
            <div className="w-8 h-8 bg-accent-gradient rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-lg">E</span>
            </div>
            <span className="text-xl font-serif font-semibold text-gothic-50">
              Biblioteka
            </span>
          </Link>

          <nav className="hidden md:flex items-center space-x-6">
            <Link 
              to="/books" 
              className="text-gothic-300 hover:text-gothic-100 transition-colors duration-200"
            >
              Knjige
            </Link>
            {isAuthenticated() && (
              <Link 
                to="/loans" 
                className="text-gothic-300 hover:text-gothic-100 transition-colors duration-200"
              >
                Pozajmice
              </Link>
            )}
            {isAdmin && (
              <Link 
                to="/admin/books" 
                className="text-gothic-300 hover:text-gothic-100 transition-colors duration-200"
              >
                Admin
              </Link>
            )}
          </nav>

          <div className="flex items-center space-x-4">
            {isAuthenticated() ? (
              <div className="flex items-center space-x-4">
                <Link
                  to="/profile"
                  className="text-sm text-gothic-300 hover:text-gothic-100 transition-colors duration-200"
                >
                  <span className="text-gothic-100 font-medium">{user.name}</span>
                  {user.subscription?.active && (
                    <span className="ml-2 px-2 py-1 bg-accent-500/20 text-accent-300 rounded-full text-xs">
                      Premium
                    </span>
                  )}
                </Link>
                <button
                  onClick={handleLogout}
                  className="btn-secondary text-sm px-4 py-2"
                >
                  Odjavi se
                </button>
              </div>
            ) : (
              <div className="flex items-center space-x-2">
                <Link 
                  to="/login" 
                  className="text-gothic-300 hover:text-gothic-100 transition-colors duration-200"
                >
                  Prijavi se
                </Link>
                <Link 
                  to="/register" 
                  className="btn-primary text-sm px-4 py-2"
                >
                  Registruj se
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </header>
  )
}

export default Header
