<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-purple-900 to-purple-800 text-white shadow-lg fixed h-screen overflow-y-auto">
      <div class="p-6">
        <h1 class="text-2xl font-bold">FharmaFlow</h1>
        <p class="text-purple-200 text-sm">Owner Dashboard</p>
        <p class="text-purple-300 text-xs mt-2 font-semibold">FULL ACCESS</p>
      </div>

      <nav class="mt-6">
        <!-- Dashboard & Reports -->
        <div class="px-6 py-2">
          <p class="text-purple-200 text-xs font-bold uppercase mb-3">📊 Reports & Analytics</p>
          <router-link
            to="/owner/dashboard"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1"
            :class="isActive('/owner/dashboard') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">📊</span>
            <span>Dashboard</span>
          </router-link>
          <router-link
            to="/owner/reports/sales"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1"
            :class="isActive('/owner/reports/sales') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">📈</span>
            <span>Sales Report</span>
          </router-link>
          <router-link
            to="/owner/reports/profit"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1"
            :class="isActive('/owner/reports/profit') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">💹</span>
            <span>Profit Report</span>
          </router-link>
          <router-link
            to="/owner/reports/financial"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1"
            :class="isActive('/owner/reports/financial') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">💰</span>
            <span>Financial</span>
          </router-link>
          <router-link
            to="/owner/reports/cashflow"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1"
            :class="isActive('/owner/reports/cashflow') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">💵</span>
            <span>Cashflow</span>
          </router-link>
          <router-link
            to="/owner/analytics"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition"
            :class="isActive('/owner/analytics') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">📉</span>
            <span>Analytics</span>
          </router-link>
        </div>

        <!-- Management Access -->
        <div class="px-6 py-2 mt-4 border-t border-purple-700 pt-4">
          <p class="text-purple-200 text-xs font-bold uppercase mb-3">⚙️ Management</p>
          <router-link
            to="/owner/medicines"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/owner/medicines') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">💊</span>
            <span>Medicines</span>
          </router-link>
          <router-link
            to="/owner/categories"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/owner/categories') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">📂</span>
            <span>Categories</span>
          </router-link>
          <router-link
            to="/owner/suppliers"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/owner/suppliers') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">🏭</span>
            <span>Suppliers</span>
          </router-link>
          <router-link
            to="/owner/stocks"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/owner/stocks') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">📦</span>
            <span>Stocks</span>
          </router-link>
          <router-link
            to="/owner/purchases"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/owner/purchases') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">🛒</span>
            <span>Purchases</span>
          </router-link>
          <router-link
            to="/owner/orders"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/owner/orders') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">📦</span>
            <span>Orders</span>
          </router-link>
          <router-link
            to="/owner/employees"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/owner/employees') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">👥</span>
            <span>Employees</span>
          </router-link>
          <router-link
            to="/owner/promotions"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/owner/promotions') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">🎉</span>
            <span>Promotions</span>
          </router-link>
          <router-link
            to="/owner/vouchers"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition text-sm"
            :class="isActive('/owner/vouchers') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">🎟️</span>
            <span>Vouchers</span>
          </router-link>
        </div>

        <!-- Notifications -->
        <div class="px-6 py-2 mt-4 border-t border-purple-700 pt-4">
          <router-link
            to="/owner/notifications"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition"
            :class="isActive('/owner/notifications') ? 'bg-purple-600' : 'hover:bg-purple-700'"
          >
            <span class="text-xl">🔔</span>
            <span>Notifications</span>
            <span v-if="unreadNotifications > 0" class="ml-auto bg-red-500 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">
              {{ unreadNotifications }}
            </span>
          </router-link>
        </div>
      </nav>

      <!-- Logout -->
      <div class="border-t border-purple-700 p-6 absolute bottom-0 w-64">
        <button
          @click="logout"
          class="w-full flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-purple-700 transition text-red-300 font-semibold"
        >
          <span class="text-xl">🚪</span>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="ml-64 flex-1 flex flex-col">
      <!-- Top Bar -->
      <header class="bg-white shadow-md p-4 flex justify-between items-center sticky top-0 z-40">
        <h2 class="text-2xl font-bold text-gray-800">{{ currentPageTitle }}</h2>
        
        <div class="flex items-center space-x-6">
          <!-- Notifications -->
          <el-badge :value="unreadNotifications" class="cursor-pointer">
            <button
              @click="showNotifications = !showNotifications"
              class="text-2xl hover:text-purple-600 relative"
            >
              🔔
            </button>
          </el-badge>

          <!-- User Info -->
          <div class="flex items-center space-x-3">
            <span class="text-gray-700 font-semibold text-sm">{{ authStore.user?.name }}</span>
            <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center text-white font-bold">
              {{ authStore.user?.name.charAt(0).toUpperCase() }}
            </div>
          </div>
        </div>
      </header>

      <!-- Notifications Dropdown -->
      <div v-if="showNotifications" class="bg-white shadow-lg p-4 max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="text-gray-500 text-center py-4">
          No notifications
        </div>
        <div
          v-for="notif in notifications"
          :key="notif.id"
          class="p-3 bg-gray-50 rounded-lg mb-2 hover:bg-gray-100 cursor-pointer border-l-4 border-purple-500"
          @click="markNotificationAsRead(notif.id)"
        >
          <p class="font-semibold text-sm text-gray-800">{{ notif.title }}</p>
          <p class="text-xs text-gray-600">{{ notif.message }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ formatTime(notif.created_at) }}</p>
        </div>
      </div>

      <!-- Page Content -->
      <main class="flex-1 p-6 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import dayjs from 'dayjs'
import 'dayjs/locale/id'

dayjs.locale('id')

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()

const showNotifications = ref(false)
const notifications = ref([])
const unreadNotifications = ref(0)

const currentPageTitle = computed(() => {
  const parts = route.path.split('/')
  const lastPart = parts[parts.length - 1]
  
  const titles = {
    'dashboard': '📊 Dashboard',
    'sales': '📈 Sales Report',
    'profit': '💹 Profit Report',
    'financial': '💰 Financial Report',
    'cashflow': '💵 Cashflow Report',
    'analytics': '📉 Analytics',
    'medicines': '💊 Medicines',
    'categories': '📂 Categories',
    'suppliers': '🏭 Suppliers',
    'warehouses': '🏢 Warehouses',
    'stocks': '📦 Stocks',
    'purchases': '🛒 Purchases',
    'orders': '📦 Orders',
    'transactions': '💰 Transactions',
    'employees': '👥 Employees',
    'promotions': '🎉 Promotions',
    'vouchers': '🎟️ Vouchers',
    'notifications': '🔔 Notifications',
  }
  
  return titles[lastPart] || 'Owner Dashboard'
})

const isActive = (path) => {
  return route.path === path
}

const formatTime = (time) => {
  return dayjs(time).fromNow()
}

const fetchNotifications = async () => {
  try {
    const response = await api.get('notifications?per_page=10&unread_only=true')
    notifications.value = response.data.data.data || []
    unreadNotifications.value = response.data.unread_count || 0
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  }
}

const markNotificationAsRead = async (notifId) => {
  try {
    await api.post(`notifications/${notifId}/read`)
    fetchNotifications()
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}

const logout = async () => {
  try {
    await api.post('auth/logout')
    authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

onMounted(() => {
  fetchNotifications()
})

// Auto refresh notifications every 30 seconds
const interval = setInterval(fetchNotifications, 30000)

watch(
  () => route.path,
  () => {
    showNotifications.value = false
  }
)
</script>