<template>
  <div class="min-h-screen bg-gradient-to-br from-green-400 via-blue-500 to-purple-600 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-green-600 mb-2">⚕️ FharmaFlow</h1>
        <p class="text-gray-600">Platform E-Commerce Apotek Terpadu</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Name -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap *</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="John Doe"
              required
            />
            <span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name[0] }}</span>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-gray-700 font-semibold mb-2">No. Telepon *</label>
            <input
              v-model="form.phone"
              type="tel"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="081234567890"
              required
            />
            <span v-if="errors.phone" class="text-red-500 text-sm">{{ errors.phone[0] }}</span>
          </div>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-gray-700 font-semibold mb-2">Email *</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="john@example.com"
            required
          />
          <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</span>
        </div>

        <!-- Address -->
        <div>
          <label class="block text-gray-700 font-semibold mb-2">Alamat</label>
          <input
            v-model="form.address"
            type="text"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="Jl. Jalan No. 1"
          />
        </div>

        <!-- City & Province -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Kota</label>
            <input
              v-model="form.city"
              type="text"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Jakarta"
            />
          </div>
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Provinsi</label>
            <input
              v-model="form.province"
              type="text"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="DKI Jakarta"
            />
          </div>
        </div>

        <!-- Password -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Password *</label>
            <input
              v-model="form.password"
              type="password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="••••••••"
              required
            />
            <span v-if="errors.password" class="text-red-500 text-sm">{{ errors.password[0] }}</span>
          </div>
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Konfirmasi Password *</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="••••••••"
              required
            />
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition text-lg mt-6"
        >
          {{ loading ? '⏳ Loading...' : '✅ Daftar' }}
        </button>
      </form>

      <!-- Login Link -->
      <p class="text-center mt-6 text-gray-600">
        Sudah punya akun?
        <router-link to="/login" class="text-green-600 font-semibold hover:underline">
          Login di sini
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'

const authStore = useAuthStore()
const router = useRouter()
const loading = ref(false)
const errors = ref({})

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  address: '',
  city: '',
  province: '',
  postal_code: '',
})

const handleRegister = async () => {
  loading.value = true
  errors.value = {}

  try {
    await authStore.register(form.value)
    ElMessage.success('Registrasi berhasil! Silakan login.')
    router.push('/login')
  } catch (error) {
    if (error.errors) {
      errors.value = error.errors
    } else {
      ElMessage.error(error.message || 'Registrasi gagal')
    }
  } finally {
    loading.value = false
  }
}
</script>