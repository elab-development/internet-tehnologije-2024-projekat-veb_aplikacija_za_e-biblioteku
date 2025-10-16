import { useState } from 'react'
import { Link } from 'react-router-dom'
import { loanService } from '../services/loanService'
import toast from 'react-hot-toast'

const LoanCard = ({ loan, onReturn }) => {
  const [returning, setReturning] = useState(false)

  const handleReturn = async () => {
    setReturning(true)
    try {
      const response = await loanService.returnLoan(loan.id)
      if (response.success) {
        toast.success('Knjiga je uspešno vraćena!')
        onReturn(loan.id)
      } else {
        toast.error(response.message || 'Greška pri vraćanju knjige')
      }
    } catch (error) {
      toast.error(error.response?.data?.message || 'Greška pri vraćanju knjige')
    }
    setReturning(false)
  }

  const isOverdue = new Date(loan.due_at) < new Date() && !loan.returned_at
  const isActive = !loan.returned_at

  return (
    <div className={`card ${isOverdue ? 'border-red-500/50 bg-red-500/5' : ''}`}>
      <div className="flex flex-col h-full">
        {/* Book Cover */}
        <div className="aspect-[3/4] bg-gothic-800 rounded-lg mb-4 overflow-hidden">
          {loan.book?.cover_url ? (
            <img
              src={loan.book.cover_url}
              alt={`Cover za ${loan.book.title}`}
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

        {/* Loan Info */}
        <div className="flex-1 flex flex-col">
          <h3 className="text-lg font-serif font-semibold text-gothic-50 mb-2 line-clamp-2">
            {loan.book?.title}
          </h3>
          
          <p className="text-gothic-300 text-sm mb-2">
            {loan.book?.author}
          </p>

          <div className="space-y-2 mb-4 text-sm">
            <div className="flex justify-between">
              <span className="text-gothic-400">Pozajmljeno:</span>
              <span className="text-gothic-100">
                {new Date(loan.created_at).toLocaleDateString('sr-RS')}
              </span>
            </div>

            <div className="flex justify-between">
              <span className="text-gothic-400">Rok:</span>
              <span className={`${isOverdue ? 'text-red-400' : 'text-gothic-100'}`}>
                {new Date(loan.due_at).toLocaleDateString('sr-RS')}
              </span>
            </div>

            {loan.returned_at && (
              <div className="flex justify-between">
                <span className="text-gothic-400">Vraćeno:</span>
                <span className="text-gothic-100">
                  {new Date(loan.returned_at).toLocaleDateString('sr-RS')}
                </span>
              </div>
            )}
          </div>

          {/* Status Badge */}
          <div className="mb-4">
            {isOverdue ? (
              <span className="px-3 py-1 bg-red-500/20 text-red-300 rounded-full text-xs font-medium">
                Zakašnjeno
              </span>
            ) : isActive ? (
              <span className="px-3 py-1 bg-green-500/20 text-green-300 rounded-full text-xs font-medium">
                Aktivno
              </span>
            ) : (
              <span className="px-3 py-1 bg-gray-500/20 text-gray-300 rounded-full text-xs font-medium">
                Vraćeno
              </span>
            )}
          </div>

          {/* Actions */}
          <div className="mt-auto space-y-2">
            <Link
              to={`/books/${loan.book?.id}`}
              className="btn-secondary w-full text-center block"
            >
              Pogledaj knjigu
            </Link>

            {isActive && (
              <button
                onClick={handleReturn}
                disabled={returning}
                className="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {returning ? 'Vraćanje...' : 'Vrati knjigu'}
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

export default LoanCard
