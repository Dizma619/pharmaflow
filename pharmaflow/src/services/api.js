import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// ================================
// REQUEST INTERCEPTOR
// Tambahkan token otomatis
// ================================
api.interceptors.request.use(
  (config) => {
    try {
      const authStore = useAuthStore()

      // Ambil token dari pinia atau localStorage
      const token =
        authStore.token ||
        localStorage.getItem('token')

      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }
    } catch (error) {
      console.error('Auth Store Error:', error)
    }

    return config
  },
  (error) => Promise.reject(error)
)

// ================================
// RESPONSE INTERCEPTOR
// Handle token expired / unauthorized
// ================================
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      try {
        const authStore = useAuthStore()

        authStore.logout()
      } catch (e) {
        console.error('Logout error:', e)
      }

      // redirect ke login
      window.location.href = '/login'
    }

    return Promise.reject(error)
  }
)

export default api