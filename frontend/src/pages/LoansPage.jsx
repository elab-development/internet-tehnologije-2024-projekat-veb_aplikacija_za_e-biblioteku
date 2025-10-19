import { useState, useEffect } from 'react'
import { useSearchParams } from 'react-router-dom'
import { loanService } from '../services/loanService'
import { useAuth } from '../hooks/useAuth'
import LoanCard from '../components/LoanCard'
import toast from 'react-hot-toast'

const LoansPage = () => {
  const [searchParams, setSearchParams] = useSearchParams()
  const { user, isAdmin } = useAuth()
  
  const [loans, setLoans] = useState([])
  const [loading, setLoading] = useState(true)
  const [pagination, setPagination] = useState({})
  const [filters, setFilters] = useState({
    only_active: searchParams.get('only_active') || '',
  })

  useEffect(() => {
    fetchLoans()
  }, [searchParams])

  const fetchLoans = async () => {
    setLoading(true)
    try {
      const params = {
        page: searchParams.get('page') || 1,
        per_page: 12,
        only_active: searchParams.get('only_active') || '',
        user_id: isAdmin ? searchParams.get('user_id') || '' : undefined,
      }

          const response = await loanService.getLoans(params)
          
          if (response.data) {
            setLoans(response.data)
            setPagination(response.meta)
          } else {
            toast.error('Greška pri učitavanju pozajmica')
          }
    } catch (error) {
      toast.error('Greška pri učitavanju pozajmica')
      console.error('Error fetching loans:', error)
    }
    setLoading(false)
  }

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({ ...prev, [key]: value }))
    const newParams = new URLSearchParams(searchParams)
    if (value) {
      newParams.set(key, value)
    } else {
      newParams.delete(key)
    }
    newParams.set('page', '1') // Reset to first page
    setSearchParams(newParams)
  }

  const handlePageChange = (page) => {
    const newParams = new URLSearchParams(searchParams)
    newParams.set('page', page)
    setSearchParams(newParams)
  }

  const handleReturn = (loanId) => {
    setLoans(prev => prev.map(loan => 
      loan.id === loanId 
        ? { ...loan, returned_at: new Date().toISOString() }
        : loan
    ))
  }

  return (
    <div className="max-w-7xl mx-auto">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-serif font-bold text-gothic-50 mb-2">
          {isAdmin ? 'Sve pozajmice' : 'Moje pozajmice'}
        </h1>
        <p className="text-gothic-400">
          {isAdmin ? 'Upravljajte pozajmicama svih korisnika' : 'Pratite svoje pozajmice'}
        </p>
      </div>

      {/* Filters */}
      <div className="card mb-8">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-medium text-gothic-300 mb-2">
              Status
            </label>
            <select
              className="input-field w-full"
              value={filters.only_active}
              onChange={(e) => handleFilterChange('only_active', e.target.value)}
            >
              <option value="">Sve pozajmice</option>
              <option value="1">Samo aktivne</option>
              <option value="0">Samo vraćene</option>
            </select>
          </div>

          {isAdmin && (
            <div>
              <label className="block text-sm font-medium text-gothic-300 mb-2">
                Korisnik ID (opciono)
              </label>
              <input
                type="number"
                className="input-field w-full"
                placeholder="ID korisnika"
                value={searchParams.get('user_id') || ''}
                onChange={(e) => {
                  const newParams = new URLSearchParams(searchParams)
                  if (e.target.value) {
                    newParams.set('user_id', e.target.value)
                  } else {
                    newParams.delete('user_id')
                  }
                  newParams.set('page', '1')
                  setSearchParams(newParams)
                }}
              />
            </div>
          )}

          <div className="flex items-end">
          </div>
        </div>
      </div>

      {/* Loans Grid */}
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
      ) : loans.length > 0 ? (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            {loans.map((loan) => (
              <LoanCard 
                key={loan.id} 
                loan={loan} 
                onReturn={handleReturn}
              />
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
            Nema pozajmica
          </h3>
          <p className="text-gothic-400 mb-6">
            {isAdmin 
              ? 'Nema pozajmica koje odgovaraju vašim kriterijumima'
              : 'Nemate pozajmljenih knjiga. Posetite kolekciju knjiga da pozajmite neku!'
            }
          </p>
          {!isAdmin && (
            <a
              href="/books"
              className="btn-primary"
            >
              Pregledaj knjige
            </a>
          )}
        </div>
      )}
    </div>
  )
}

export default LoansPage
