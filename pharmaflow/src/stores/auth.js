import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(
    localStorage.getItem('user')
      ? JSON.parse(localStorage.getItem('user'))
      : null
  )

  const token = ref(
    localStorage.getItem('token') || null
  )

  const isAuthenticated = computed(
    () => !!token.value
  )

  // LOGIN
  const login = async (
    email,
    password
  ) => {
    try {
      const response = await api.post(
        'auth/login',
        {
          email,
          password,
        }
      )

      // sesuaikan response backend Laravel
      token.value =
        response.data.data?.token ||
        response.data.token

      user.value =
        response.data.data?.user ||
        response.data.user

      localStorage.setItem(
        'token',
        token.value
      )

      localStorage.setItem(
        'user',
        JSON.stringify(user.value)
      )

      return response.data
    } catch (error) {
      console.error(
        'LOGIN ERROR:',
        error.response?.data
      )

      throw (
        error.response?.data || {
          message:
            'Login gagal'
        }
      )
    }
  }

  // REGISTER
  const register = async (
    data
  ) => {
    try {
      const response =
        await api.post(
          'auth/register',
          data
        )

      token.value =
        response.data.data?.token ||
        response.data.token

      user.value =
        response.data.data?.user ||
        response.data.user

      localStorage.setItem(
        'token',
        token.value
      )

      localStorage.setItem(
        'user',
        JSON.stringify(user.value)
      )

      return response.data
    } catch (error) {
      throw (
        error.response?.data || {
          message:
            'Register gagal'
        }
      )
    }
  }

  // LOGOUT
  const logout = () => {
    token.value = null
    user.value = null

    localStorage.removeItem(
      'token'
    )
    localStorage.removeItem(
      'user'
    )
  }

  return {
    user,
    token,
    isAuthenticated,
    login,
    register,
    logout,
  }
})