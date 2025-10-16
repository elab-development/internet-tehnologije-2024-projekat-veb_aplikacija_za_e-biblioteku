import { Navigate } from 'react-router-dom'
import { useAuthStore } from '../store/useAuthStore'

const AdminRoute = ({ children }) => {
  const { user, isAdmin } = useAuthStore()

  if (!user) {
    return <Navigate to="/login" replace />
  }

  if (!isAdmin()) {
    return <Navigate to="/books" replace />
  }

  return children
}

export default AdminRoute
