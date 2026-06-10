<template>
  <div
    class="min-h-screen bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center"
  >
    <div
      class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md"
    >
      <!-- TITLE -->
      <h2
        class="text-3xl font-bold text-center mb-6 text-gray-800"
      >
        PharmaFlow
      </h2>

      <!-- LOGIN FORM -->
      <form @submit.prevent="handleLogin">
        <!-- EMAIL -->
        <div>
          <label
            class="block text-gray-700 font-semibold mb-2"
          >
            Email
          </label>

          <input
            v-model="form.email"
            type="email"
            placeholder="Enter your email"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500"
          />
        </div>

        <!-- PASSWORD -->
        <div>
          <label
            class="block text-gray-700 font-semibold mb-2"
          >
            Password
          </label>

          <input
            v-model="form.password"
            type="password"
            placeholder="Enter your password"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500"
          />
        </div>

        <!-- BUTTON -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-green-600 text-white font-semibold py-2 rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition"
        >
          {{
            loading
              ? 'Loading...'
              : 'Login'
          }}
        </button>
      </form>

      <!-- REGISTER LINK -->
      <p
        class="text-center mt-4 text-gray-600"
      >
        Don't have account?

        <router-link
          to="/register"
          class="text-green-600 font-semibold hover:underline"
        >
          Register here
        </router-link>
      </p>

      <!-- TEST ACCOUNT -->
      <div
        class="mt-6 p-4 bg-blue-50 rounded border border-blue-200 text-sm"
      >
        <p
          class="font-semibold text-blue-900 mb-2"
        >
          Test Accounts:
        </p>

        <p class="text-blue-800">
          <strong>Owner:</strong>
          owner@pharmaflow.local /
          password123
        </p>

        <p class="text-blue-800">
          <strong>Staff:</strong>
          staff1@pharmaflow.local /
          password123
        </p>

        <p class="text-blue-800">
          <strong>Customer:</strong>
          customer1@pharmaflow.local /
          password123
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'

const authStore =
  useAuthStore()

const router =
  useRouter()

const loading =
  ref(false)

const form = ref({
  email: '',
  password: '',
})

const handleLogin = async () => {
  console.log('--- 1. TOMBOL LOGIN DIKLIK ---') // 👈 TAMBAHKAN INI
  loading.value = true

  try {
    console.log('--- 2. MEMANGGIL STORE PINIA ---') // 👈 TAMBAHKAN INI
    const response = await authStore.login(form.value.email, form.value.password)
    console.log('--- 3. STORE BERHASIL MENJAWAB! ---') // 👈 TAMBAHKAN INI
    

      ElMessage.success(
        'Login berhasil!'
      )

      const role =
        response.user.role

      // CUSTOMER
      if (
        role ===
        'customer'
      ) {

        router.push('/')

      }

      // STAFF
      else if (
        role ===
        'staff'
      ) {

        router.push(
          '/staff/dashboard'
        )

      }

      // OWNER
      else if (
        role ===
        'owner'
      ) {

        router.push(
          '/owner/dashboard'
        )

      }

      // FALLBACK
      else {

        router.push('/')

      }

    } catch (error) {

      console.error(
        'LOGIN ERROR:',
        error
      )

      ElMessage.error(
        error?.message ||
        error?.errors?.email?.[0] ||
        error?.errors?.password?.[0] ||
        'Login gagal'
      )

    } finally {

      loading.value =
        false

    }
  }
</script>