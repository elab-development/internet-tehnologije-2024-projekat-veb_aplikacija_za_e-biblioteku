import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import MainLayout from './components/MainLayout'
import ProtectedRoute from './components/ProtectedRoute'
import HomePage from './pages/HomePage'
import LoginPage from './pages/LoginPage'
import RegisterPage from './pages/RegisterPage'
import ProfilePage from './pages/ProfilePage'
import BooksPage from './pages/BooksPage'
import BookDetailsPage from './pages/BookDetailsPage'
import LoansPage from './pages/LoansPage'
import SubscriptionPage from './pages/SubscriptionPage'
import AdminBooksPage from './pages/AdminBooksPage'
import AdminRoute from './components/AdminRoute'

function App() {
  return (
    <Router>
      <div className="App">
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />
          <Route path="/" element={<MainLayout />}>
            <Route index element={<HomePage />} />
            <Route path="profile" element={
              <ProtectedRoute>
                <ProfilePage />
              </ProtectedRoute>
            } />
            <Route path="books" element={<BooksPage />} />
            <Route path="books/:id" element={<BookDetailsPage />} />
            <Route path="loans" element={
              <ProtectedRoute>
                <LoansPage />
              </ProtectedRoute>
            } />
            <Route path="subscription" element={
              <ProtectedRoute>
                <SubscriptionPage />
              </ProtectedRoute>
            } />
            <Route path="admin/books" element={
              <ProtectedRoute>
                <AdminRoute>
                  <AdminBooksPage />
                </AdminRoute>
              </ProtectedRoute>
            } />
            {/* Additional routes will be added here */}
          </Route>
        </Routes>
        <Toaster
          position="top-right"
          toastOptions={{
            duration: 4000,
            style: {
              background: '#1a1a1a',
              color: '#e5e5e5',
              border: '1px solid #404040',
            },
          }}
        />
      </div>
    </Router>
  )
}

export default App
