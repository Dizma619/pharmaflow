<template>
  <nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4">
      <div
        class="flex justify-between items-center h-16"
      >

        <!-- Logo -->
        <router-link
          to="/"
          class="flex items-center space-x-2"
        >
          <span
            class="text-2xl font-bold text-green-600"
          >
            ⚕️
          </span>

          <span
            class="text-xl font-bold text-gray-800"
          >
            FarmaFlow
          </span>
        </router-link>

        <!-- Menu -->
        <div
          class="flex items-center space-x-6"
        >
          <router-link
            to="/"
            class="text-gray-700 hover:text-green-600"
          >
            Home
          </router-link>

          <router-link
            to="/products"
            class="text-gray-700 hover:text-green-600"
          >
            Products
          </router-link>
        </div>

        <!-- Auth -->
        <div
          class="flex items-center space-x-4"
        >

          <!-- Belum Login -->
          <template
            v-if="!authStore.isAuthenticated"
          >
            <router-link
              to="/login"
              class="px-4 py-2 text-gray-700 border border-gray-300 rounded hover:bg-gray-100"
            >
              Login
            </router-link>

            <router-link
              to="/register"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            >
              Register
            </router-link>
          </template>

          <!-- Sudah Login -->
          <template v-else>

            <!-- Cart -->
            <router-link
              v-if="
                authStore.user?.role
                === 'customer'
              "
              to="/cart"
              class="relative text-2xl hover:text-green-600"
            >
              🛒

              <span
                v-if="
                  cartStore.cartCount > 0
                "
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
              >
                {{
                  cartStore.cartCount
                }}
              </span>
            </router-link>

            <!-- Dropdown User -->
            <el-dropdown>

              <span
                class="cursor-pointer flex items-center gap-2"
              >
                {{
                  authStore.user?.name
                }}

                ⌄
              </span>

              <template #dropdown>

                <!-- Customer -->
                <el-dropdown-item
                  v-if="
                    authStore.user?.role
                    === 'customer'
                  "
                  @click="
                    router.push('/orders')
                  "
                >
                  My Orders
                </el-dropdown-item>

                <!-- Staff -->
                <el-dropdown-item
                  v-if="
                    authStore.user?.role
                    === 'staff'
                  "
                  @click="
                    router.push('/staff/pos')
                  "
                >
                  POS System
                </el-dropdown-item>

                <!-- Owner -->
                <el-dropdown-item
                  v-if="
                    authStore.user?.role
                    === 'owner'
                  "
                  @click="
                    router.push('/owner/dashboard')
                  "
                >
                  Dashboard
                </el-dropdown-item>

                <el-divider />

                <!-- Logout -->
                <el-dropdown-item
                  @click="logout"
                >
                  Logout
                </el-dropdown-item>

              </template>
            </el-dropdown>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import {
  useAuthStore
} from '@/stores/auth'

import {
  useCartStore
} from '@/stores/cart'

import api
from '@/services/api'

import {
  useRouter
} from 'vue-router'

import {
  ElMessage
} from 'element-plus'

const authStore =
  useAuthStore()

const cartStore =
  useCartStore()

const router =
  useRouter()

const logout =
async () => {

  try {

    // call backend logout
    await api.post(
      '/auth/logout'
    )

  } catch (error) {

    console.warn(
      'Logout API gagal:',
      error.response?.data
    )

  } finally {

    // paksa clear local
    await authStore.logout()

    ElMessage.success(
      'Logout berhasil'
    )

    router.push(
      '/login'
    )
  }
}
</script>