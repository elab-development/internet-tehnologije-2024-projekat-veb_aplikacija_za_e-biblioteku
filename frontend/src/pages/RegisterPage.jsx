import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuthStore } from '../store/useAuthStore'
import toast from 'react-hot-toast'

const RegisterPage = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  })
  const [isLoading, setIsLoading] = useState(false)
  
  const { register } = useAuthStore()
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

    if (formData.password !== formData.password_confirmation) {
      toast.error('Lozinke se ne poklapaju')
      setIsLoading(false)
      return
    }

    const result = await register(
      formData.name,
      formData.email,
      formData.password,
      formData.password_confirmation
    )

    if (result.success) {
      toast.success('Uspešno ste se registrovali!')
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
            Kreiraj novi nalog
          </h2>
          <p className="mt-2 text-sm text-gothic-400">
            Pridružite se E-Biblioteci
          </p>
        </div>

        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          <div className="space-y-4">
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-gothic-300 mb-2">
                Ime i prezime
              </label>
              <input
                id="name"
                name="name"
                type="text"
                autoComplete="name"
                required
                className="input-field w-full"
                placeholder="Vaše ime i prezime"
                value={formData.name}
                onChange={handleChange}
              />
            </div>

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
                autoComplete="new-password"
                required
                className="input-field w-full"
                placeholder="Najmanje 8 karaktera"
                value={formData.password}
                onChange={handleChange}
              />
            </div>

            <div>
              <label htmlFor="password_confirmation" className="block text-sm font-medium text-gothic-300 mb-2">
                Potvrdi lozinku
              </label>
              <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autoComplete="new-password"
                required
                className="input-field w-full"
                placeholder="Ponovite lozinku"
                value={formData.password_confirmation}
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
              {isLoading ? 'Registracija...' : 'Registruj se'}
            </button>
          </div>

          <div className="text-center space-y-3">
            <p className="text-sm text-gothic-400">
              Već imate nalog?{' '}
              <Link
                to="/login"
                className="font-medium text-accent-400 hover:text-accent-300 transition-colors duration-200"
              >
                Prijavite se ovde
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

export default RegisterPage
