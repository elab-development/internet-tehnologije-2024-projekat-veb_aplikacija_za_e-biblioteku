import { useState, useEffect } from 'react'
import { useAuth } from '../hooks/useAuth'
import { subscriptionService } from '../services/subscriptionService'
import toast from 'react-hot-toast'

const SubscriptionPage = () => {
  const { user, fetchProfile } = useAuth()
  const [subscriptionStatus, setSubscriptionStatus] = useState(null)
  const [subscriptionHistory, setSubscriptionHistory] = useState([])
  const [loading, setLoading] = useState(true)
  const [purchasing, setPurchasing] = useState(false)

  useEffect(() => {
    fetchSubscriptionData()
  }, [])

  const fetchSubscriptionData = async () => {
    setLoading(true)
    try {
      const [statusResponse, historyResponse] = await Promise.all([
        subscriptionService.getSubscriptionStatus(),
        subscriptionService.getSubscriptionHistory()
      ])

      if (statusResponse.success) {
        setSubscriptionStatus(statusResponse.data)
      }

      if (historyResponse.success) {
        setSubscriptionHistory(historyResponse.data.data)
      }
    } catch (error) {
      toast.error('Greška pri učitavanju podataka o pretplati')
      console.error('Error fetching subscription data:', error)
    }
    setLoading(false)
  }

  const handlePurchase = async (plan, duration) => {
    setPurchasing(true)
    try {
      const response = await subscriptionService.createSubscription({
        plan,
        duration,
      })

      if (response.success) {
        toast.success('Pretplata je uspešno aktivirana!')
        await fetchProfile() // Refresh user data
        await fetchSubscriptionData() // Refresh subscription data
      } else {
        toast.error(response.message || 'Greška pri aktivaciji pretplate')
      }
    } catch (error) {
      toast.error(error.response?.data?.message || 'Greška pri aktivaciji pretplate')
    }
    setPurchasing(false)
  }

  if (loading) {
    return (
      <div className="max-w-4xl mx-auto">
        <div className="animate-pulse">
          <div className="h-8 bg-gothic-800 rounded mb-4 w-1/3"></div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="h-64 bg-gothic-800 rounded-lg"></div>
            <div className="h-64 bg-gothic-800 rounded-lg"></div>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="max-w-6xl mx-auto">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-serif font-bold text-gothic-50 mb-2">
          Pretplata
        </h1>
        <p className="text-gothic-400">
          Upravljajte svojom pretplatom i uživajte u svim prednostima
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Current Status */}
        <div className="card">
          <h2 className="text-xl font-serif font-semibold text-gothic-50 mb-4">
            Trenutni status
          </h2>
          
          {subscriptionStatus ? (
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-gothic-400">Status:</span>
                <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                  subscriptionStatus.active 
                    ? 'bg-green-500/20 text-green-300' 
                    : 'bg-red-500/20 text-red-300'
                }`}>
                  {subscriptionStatus.active ? 'Aktivna' : 'Neaktivna'}
                </span>
              </div>
              
              {subscriptionStatus.active && (
                <>
                  <div className="flex items-center justify-between">
                    <span className="text-gothic-400">Plan:</span>
                    <span className="text-gothic-100 capitalize">{subscriptionStatus.plan}</span>
                  </div>
                  
                  <div className="flex items-center justify-between">
                    <span className="text-gothic-400">Počela:</span>
                    <span className="text-gothic-100">
                      {new Date(subscriptionStatus.starts_at).toLocaleDateString('sr-RS')}
                    </span>
                  </div>
                  
                  <div className="flex items-center justify-between">
                    <span className="text-gothic-400">Istiće:</span>
                    <span className="text-gothic-100">
                      {new Date(subscriptionStatus.ends_at).toLocaleDateString('sr-RS')}
                    </span>
                  </div>
                </>
              )}
            </div>
          ) : (
            <div className="text-center py-8">
              <p className="text-gothic-400">Učitavanje statusa pretplate...</p>
            </div>
          )}
        </div>

        {/* Available Plans */}
        <div className="card">
          <h2 className="text-xl font-serif font-semibold text-gothic-50 mb-4">
            Dostupni planovi
          </h2>
          
          <div className="space-y-4">
            <div className="border border-gothic-700 rounded-lg p-4">
              <div className="flex items-center justify-between mb-2">
                <h3 className="text-lg font-semibold text-gothic-100">Basic Plan</h3>
                <span className="text-accent-400 font-bold">Besplatno</span>
              </div>
              <p className="text-gothic-400 text-sm mb-4">
                Pristup pregledu knjiga (prve 3 stranice)
              </p>
              <ul className="text-gothic-300 text-sm space-y-1 mb-4">
                <li>• Pregled prvih 3 stranice</li>
                <li>• Pretraga knjiga</li>
                <li>• Pozajmljivanje knjiga</li>
              </ul>
            </div>

            <div className="border border-accent-500/50 rounded-lg p-4 bg-accent-500/5">
              <div className="flex items-center justify-between mb-2">
                <h3 className="text-lg font-semibold text-gothic-100">Premium Plan</h3>
                <span className="text-accent-400 font-bold">30 dana</span>
              </div>
              <p className="text-gothic-400 text-sm mb-4">
                Pristup svim knjigama bez ograničenja
              </p>
              <ul className="text-gothic-300 text-sm space-y-1 mb-4">
                <li>• Sve iz Basic plana</li>
                <li>• Čitanje celih knjiga</li>
                <li>• Preuzimanje PDF fajlova</li>
                <li>• Prioritetna podrška</li>
              </ul>
              
              {!subscriptionStatus?.active && (
                <button
                  onClick={() => handlePurchase('basic', 30)}
                  disabled={purchasing}
                  className="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {purchasing ? 'Aktiviranje...' : 'Aktiviraj Premium (30 dana)'}
                </button>
              )}
              
              {subscriptionStatus?.active && (
                <div className="text-center">
                  <p className="text-green-400 text-sm font-medium">
                    Vaša pretplata je aktivna!
                  </p>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Subscription History */}
      {subscriptionHistory.length > 0 && (
        <div className="card mt-8">
          <h2 className="text-xl font-serif font-semibold text-gothic-50 mb-4">
            Istorija pretplata
          </h2>
          
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead>
                <tr className="border-b border-gothic-700">
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Plan</th>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Počela</th>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Istekla</th>
                  <th className="text-left py-3 px-4 text-gothic-300 font-medium">Status</th>
                </tr>
              </thead>
              <tbody>
                {subscriptionHistory.map((subscription) => (
                  <tr key={subscription.id} className="border-b border-gothic-700 last:border-b-0">
                    <td className="py-3 px-4 text-gothic-100 capitalize">{subscription.plan}</td>
                    <td className="py-3 px-4 text-gothic-100">
                      {new Date(subscription.starts_at).toLocaleDateString('sr-RS')}
                    </td>
                    <td className="py-3 px-4 text-gothic-100">
                      {new Date(subscription.ends_at).toLocaleDateString('sr-RS')}
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                        subscription.active 
                          ? 'bg-green-500/20 text-green-300' 
                          : 'bg-gray-500/20 text-gray-300'
                      }`}>
                        {subscription.active ? 'Aktivna' : 'Istekla'}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Benefits Section */}
      <div className="card mt-8">
        <h2 className="text-xl font-serif font-semibold text-gothic-50 mb-4">
          Prednosti pretplate
        </h2>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div className="text-center">
            <div className="w-12 h-12 bg-accent-500/20 rounded-lg flex items-center justify-center mx-auto mb-3">
              <span className="text-accent-400 text-xl">📖</span>
            </div>
            <h3 className="font-semibold text-gothic-100 mb-2">Neograničeno čitanje</h3>
            <p className="text-gothic-400 text-sm">
              Čitajte sve knjige u kolekciji bez ograničenja
            </p>
          </div>
          
          <div className="text-center">
            <div className="w-12 h-12 bg-accent-500/20 rounded-lg flex items-center justify-center mx-auto mb-3">
              <span className="text-accent-400 text-xl">💾</span>
            </div>
            <h3 className="font-semibold text-gothic-100 mb-2">Preuzimanje</h3>
            <p className="text-gothic-400 text-sm">
              Preuzmite PDF fajlove za offline čitanje
            </p>
          </div>
          
          <div className="text-center">
            <div className="w-12 h-12 bg-accent-500/20 rounded-lg flex items-center justify-center mx-auto mb-3">
              <span className="text-accent-400 text-xl">⚡</span>
            </div>
            <h3 className="font-semibold text-gothic-100 mb-2">Brži pristup</h3>
            <p className="text-gothic-400 text-sm">
              Prioritetni pristup novim knjigama
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}

export default SubscriptionPage
