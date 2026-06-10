import axios from 'axios'

const api = axios.create({
  // ✅ SUDAH FIX: Ditambahkan slash '/' di akhir v1 agar tidak v1suppliers lagi
  baseURL: 'http://127.0.0.1:8000/api/v1/',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// REQUEST INTERCEPTOR
api.interceptors.request.use(
  (config) => {
    console.log(
      'API REQUEST:',
      config.baseURL + config.url
    )

    const token = localStorage.getItem('token')

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error) => Promise.reject(error)
)

// RESPONSE INTERCEPTOR
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    // Kalau token expired / unauthorized
    if (status === 401) {
      console.warn('Session expired. Auto logout...')

      // Hapus data login
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      localStorage.removeItem('role')

      // Redirect ke login
      window.location.href = '/login'
    }

    return Promise.reject(error)
  }
)

export default api