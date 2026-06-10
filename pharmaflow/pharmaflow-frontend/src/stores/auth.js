import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore(
  'auth',
  () => {
    const user = ref(
      JSON.parse(
        localStorage.getItem('user')
      ) || null
    )

    const token = ref(
      localStorage.getItem('token') ||
        null
    )

    const isAuthenticated =
      computed(() => !!token.value)

    // =====================
    // LOGIN
    // =====================
   const login = async (email, password) => {
  try {
    console.log('--- A. MASUK KE DALAM STORE ---') // 👈 TAMBAHKAN INI
    const response = await api.post('auth/login', { email, password })
    console.log('--- B. AXIOS SELESAI MEMANGGIL API ---') // 👈 TAMBAHKAN INI
    
    // ... sisa kode Anda ...

        // FIX
        const data =
          response.data

        token.value =
          data.token

        user.value =
          data.user

        localStorage.setItem(
          'token',
          token.value
        )

        localStorage.setItem(
          'user',
          JSON.stringify(
            user.value
          )
        )

        return data
      } catch (error) {
        throw (
          error.response?.data ||
          error.message
        )
      }
    }

    // =====================
    // REGISTER
    // =====================
    const register = async (
      formData
    ) => {
      try {
        const response =
          await api.post(
            '/auth/register',
            formData
          )

        // FIX
        const data =
          response.data

        token.value =
          data.token

        user.value =
          data.user

        localStorage.setItem(
          'token',
          token.value
        )

        localStorage.setItem(
          'user',
          JSON.stringify(
            user.value
          )
        )

        return data
      } catch (error) {
        throw (
          error.response?.data ||
          error.message
        )
      }
    }

    const logout = async () => {
      try {
        if (token.value) {
          await api.post(
            '/auth/logout'
          )
        }
      } catch (error) {
        console.warn(
          'Logout gagal:',
          error
        )
      } finally {
        token.value = null
        user.value = null

        localStorage.removeItem(
          'token'
        )

        localStorage.removeItem(
          'user'
        )
      }
    }

    return {
      user,
      token,
      isAuthenticated,
      login,
      register,
      logout,
    }
  }
)