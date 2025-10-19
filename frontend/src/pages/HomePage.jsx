import { Link } from 'react-router-dom'
import { useAuthStore } from '../store/useAuthStore'

const HomePage = () => {
  const { user } = useAuthStore()

  return (
    <div className="min-h-screen bg-gothic-gradient">
      {/* Hero Section */}
      <div className="relative overflow-hidden">
        <div className="max-w-7xl mx-auto">
          <div className="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
            <main className="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
              <div className="sm:text-center lg:text-left">
                <h1 className="text-4xl tracking-tight font-serif font-extrabold text-gothic-50 sm:text-5xl md:text-6xl">
                  <span className="block xl:inline">Dobrodošli u</span>{' '}
                  <span className="block text-accent-400 xl:inline">E-Biblioteku</span>
                </h1>
                <p className="mt-3 text-base text-gothic-300 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                  Otkrijte svet knjiga u digitalnom formatu. Čitajte, pozajmljujte i uživajte 
                  u bogatoj kolekciji knjiga dostupnoj 24/7.
                </p>
                <div className="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                  <div className="rounded-md shadow">
                    <Link
                      to="/books"
                      className="btn-primary w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md md:py-4 md:text-lg md:px-10"
                    >
                      Pregledaj knjige
                    </Link>
                  </div>
                  <div className="mt-3 sm:mt-0 sm:ml-3">
                    {!user ? (
                      <Link
                        to="/register"
                        className="btn-secondary w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md md:py-4 md:text-lg md:px-10"
                      >
                        Registruj se
                      </Link>
                    ) : (
                      <Link
                        to="/loans"
                        className="btn-secondary w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md md:py-4 md:text-lg md:px-10"
                      >
                        Moje pozajmice
                      </Link>
                    )}
                  </div>
                </div>
              </div>
            </main>
          </div>
        </div>
        
        {/* Background decoration */}
        <div className="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
          <div className="h-56 w-full bg-gothic-800 sm:h-72 md:h-96 lg:w-full lg:h-full flex items-center justify-center">
            <div className="text-center">
              <div className="w-32 h-32 bg-accent-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                <span className="text-white font-serif text-4xl">📚</span>
              </div>
              <p className="text-gothic-300 text-lg">Milioni knjiga na dohvat ruke</p>
            </div>
          </div>
        </div>
      </div>

      {/* Features Section */}
      <div className="py-12 bg-gothic-900">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="lg:text-center">
            <h2 className="text-base text-accent-400 font-semibold tracking-wide uppercase">
              Funkcionalnosti
            </h2>
            <p className="mt-2 text-3xl leading-8 font-serif font-extrabold tracking-tight text-gothic-50 sm:text-4xl">
              Sve što vam treba za čitanje
            </p>
            <p className="mt-4 max-w-2xl text-xl text-gothic-300 lg:mx-auto">
              Naša platforma nudi sve potrebne alate za moderno čitanje i upravljanje bibliotekom.
            </p>
          </div>

          <div className="mt-10">
            <div className="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-10">
              <div className="card">
                <div className="flex">
                  <div className="flex-shrink-0">
                    <div className="flex items-center justify-center h-12 w-12 rounded-md bg-accent-500 text-white">
                      📖
                    </div>
                  </div>
                  <div className="ml-4">
                    <h3 className="text-lg leading-6 font-medium text-gothic-50">
                      Digitalno čitanje
                    </h3>
                    <p className="mt-2 text-base text-gothic-300">
                      Čitajte knjige direktno u browseru. Podržani su PDF formati sa optimizovanim prikazom.
                    </p>
                  </div>
                </div>
              </div>

              <div className="card">
                <div className="flex">
                  <div className="flex-shrink-0">
                    <div className="flex items-center justify-center h-12 w-12 rounded-md bg-accent-500 text-white">
                      🔍
                    </div>
                  </div>
                  <div className="ml-4">
                    <h3 className="text-lg leading-6 font-medium text-gothic-50">
                      Napredna pretraga
                    </h3>
                    <p className="mt-2 text-base text-gothic-300">
                      Pronađite knjige po naslovu, autoru, žanru ili bilo kom drugom kriterijumu.
                    </p>
                  </div>
                </div>
              </div>

              <div className="card">
                <div className="flex">
                  <div className="flex-shrink-0">
                    <div className="flex items-center justify-center h-12 w-12 rounded-md bg-accent-500 text-white">
                      ⏰
                    </div>
                  </div>
                  <div className="ml-4">
                    <h3 className="text-lg leading-6 font-medium text-gothic-50">
                      Pozajmice
                    </h3>
                    <p className="mt-2 text-base text-gothic-300">
                      Pozajmite knjige na određeno vreme i pratite svoje pozajmice.
                    </p>
                  </div>
                </div>
              </div>

              <div className="card">
                <div className="flex">
                  <div className="flex-shrink-0">
                    <div className="flex items-center justify-center h-12 w-12 rounded-md bg-accent-500 text-white">
                      👑
                    </div>
                  </div>
                  <div className="ml-4">
                    <h3 className="text-lg leading-6 font-medium text-gothic-50">
                      Premium pretplata
                    </h3>
                    <p className="mt-2 text-base text-gothic-300">
                      Pristupite svim knjigama bez ograničenja sa premium pretplatom.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default HomePage
