import { useState, useEffect } from 'react'
import { useAuth } from '../hooks/useAuth'
import axiosInstance from '../services/axiosInstance'
import toast from 'react-hot-toast'

const ProfilePage = () => {
  const { user, updateUser, fetchProfile } = useAuth()
  const [isLoading, setIsLoading] = useState(false)
  const [subscriptionStatus, setSubscriptionStatus] = useState(null)

  useEffect(() => {
    fetchSubscriptionStatus()
  }, [])

  const fetchSubscriptionStatus = async () => {
    try {
      const response = await axiosInstance.get('/subscriptions/status')
      setSubscriptionStatus(response.data.data)
    } catch (error) {
      console.error('Failed to fetch subscription status:', error)
    }
  }

  const handleSubscriptionPurchase = async () => {
    setIsLoading(true)
    try {
      const response = await axiosInstance.post('/subscriptions', {
        plan: 'basic',
        duration: 30, // days
      })

      if (response.data.success) {
        toast.success('Pretplata je uspešno aktivirana!')
        await fetchProfile() // Refresh user data
        await fetchSubscriptionStatus()
      }
    } catch (error) {
      toast.error(error.response?.data?.message || 'Greška pri aktivaciji pretplate')
    }
    setIsLoading(false)
  }

  return (
    <div className="max-w-4xl mx-auto">
      <div className="mb-8">
        <h1 className="text-3xl font-serif font-bold text-gothic-50 mb-2">
          Moj profil
        </h1>
        <p className="text-gothic-400">
          Upravljajte svojim nalogom i pretplatom
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* User Info */}
        <div className="card">
          <h2 className="text-xl font-serif font-semibold text-gothic-50 mb-4">
            Informacije o nalogu
          </h2>
          
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gothic-300 mb-1">
                Ime i prezime
              </label>
              <p className="text-gothic-100">{user?.name}</p>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gothic-300 mb-1">
                Email adresa
              </label>
              <p className="text-gothic-100">{user?.email}</p>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gothic-300 mb-1">
                Uloga
              </label>
              <p className="text-gothic-100 capitalize">
                {user?.role === 'admin' ? 'Administrator' : 'Korisnik'}
              </p>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gothic-300 mb-1">
                Datum registracije
              </label>
              <p className="text-gothic-100">
                {user?.created_at ? new Date(user.created_at).toLocaleDateString('sr-RS') : 'N/A'}
              </p>
            </div>
          </div>
        </div>

        {/* Subscription Status */}
        <div className="card">
          <h2 className="text-xl font-serif font-semibold text-gothic-50 mb-4">
            Status pretplate
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
                    <span className="text-gothic-400">Istiće:</span>
                    <span className="text-gothic-100">
                      {new Date(subscriptionStatus.ends_at).toLocaleDateString('sr-RS')}
                    </span>
                  </div>
                </>
              )}
              
              {!subscriptionStatus.active && (
                <div className="mt-6">
                  <p className="text-gothic-400 text-sm mb-4">
                    Aktivirajte pretplatu da biste imali pristup svim knjigama bez ograničenja.
                  </p>
                  <button
                    onClick={handleSubscriptionPurchase}
                    disabled={isLoading}
                    className="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {isLoading ? 'Aktiviranje...' : 'Aktiviraj pretplatu (30 dana)'}
                  </button>
                </div>
              )}
            </div>
          ) : (
            <div className="text-center py-8">
              <p className="text-gothic-400">Učitavanje statusa pretplate...</p>
            </div>
          )}
        </div>
      </div>

      {/* Recent Activity */}
      <div className="card mt-8">
        <h2 className="text-xl font-serif font-semibold text-gothic-50 mb-4">
          Nedavna aktivnost
        </h2>
        
        <div className="text-center py-8">
          <p className="text-gothic-400">
            Aktivnost će biti dostupna uskoro...
          </p>
        </div>
      </div>
    </div>
  )
}

export default ProfilePage
