import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuthStore } from '../store/useAuthStore'
import toast from 'react-hot-toast'

const LoginPage = () => {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  })
  const [isLoading, setIsLoading] = useState(false)
  
  const { login } = useAuthStore()
  const navigate = useNavigate()

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    })
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setIsLoading(true)

    const result = await login(formData.email, formData.password)

    if (result.success) {
      toast.success('Uspešno ste se prijavili!')
      navigate('/books')
    } else {
      toast.error(result.error)
    }

    setIsLoading(false)
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gothic-gradient py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <div className="text-center">
          <div className="mx-auto h-12 w-12 bg-accent-gradient rounded-lg flex items-center justify-center">
            <span className="text-white font-bold text-xl">E</span>
          </div>
          <h2 className="mt-6 text-3xl font-serif font-bold text-gothic-50">
            Prijavi se u svoj nalog
          </h2>
          <p className="mt-2 text-sm text-gothic-400">
            Dobrodošli u E-Biblioteku
          </p>
        </div>

        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          <div className="space-y-4">
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gothic-300 mb-2">
                Email adresa
              </label>
              <input
                id="email"
                name="email"
                type="email"
                autoComplete="email"
                required
                className="input-field w-full"
                placeholder="vas@email.com"
                value={formData.email}
                onChange={handleChange}
              />
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gothic-300 mb-2">
                Lozinka
              </label>
              <input
                id="password"
                name="password"
                type="password"
                autoComplete="current-password"
                required
                className="input-field w-full"
                placeholder="Vaša lozinka"
                value={formData.password}
                onChange={handleChange}
              />
            </div>
          </div>

          <div>
            <button
              type="submit"
              disabled={isLoading}
              className="btn-primary w-full flex justify-center py-3 px-4 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isLoading ? 'Prijavljivanje...' : 'Prijavi se'}
            </button>
          </div>

          <div className="text-center space-y-3">
            <p className="text-sm text-gothic-400">
              Nemate nalog?{' '}
              <Link
                to="/register"
                className="font-medium text-accent-400 hover:text-accent-300 transition-colors duration-200"
              >
                Registrujte se ovde
              </Link>
            </p>
            
            <div className="border-t border-gothic-600 pt-3">
              <button
                type="button"
                onClick={() => navigate('/books')}
                className="text-sm text-gothic-300 hover:text-gothic-100 transition-colors duration-200"
              >
                Nastavi kao neulogovan korisnik
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  )
}

export default LoginPage
