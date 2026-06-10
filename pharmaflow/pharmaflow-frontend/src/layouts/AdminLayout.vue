<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white shadow-lg fixed h-screen overflow-y-auto">
      <div class="p-6">
        <h1 class="text-2xl font-bold">FharmaFlow</h1>
        <p class="text-gray-400 text-sm">Admin Dashboard</p>
      </div>

      <nav class="mt-6">
        <div v-for="item in menuItems" :key="item.path" class="px-6 py-2">
          <router-link
            :to="item.path"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition"
            :class="isActive(item.path) ? 'bg-green-600' : 'hover:bg-gray-800'"
          >
            <span class="text-xl">{{ item.icon }}</span>
            <span>{{ item.label }}</span>
          </router-link>
        </div>
      </nav>

      <div class="border-t border-gray-700 p-6 absolute bottom-0 w-64">
        <button
          @click="logout"
          class="w-full flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-gray-800 transition text-red-400"
        >
          <span class="text-xl">🚪</span>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="ml-64 flex-1 flex flex-col">
      <!-- Top Bar -->
      <header class="bg-white shadow-md p-4 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">{{ currentPageTitle }}</h2>
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <el-badge :value="unreadNotifications" class="cursor-pointer">
            <button
              @click="showNotifications = !showNotifications"
              class="text-2xl hover:text-green-600"
            >
              🔔
            </button>
          </el-badge>

          <!-- User Info -->
          <div class="flex items-center space-x-3">
            <span class="text-gray-700 font-semibold">{{ authStore.user?.name }}</span>
            <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
              {{ authStore.user?.name.charAt(0).toUpperCase() }}
            </div>
          </div>
        </div>
      </header>

      <!-- Notifications Dropdown -->
      <div v-if="showNotifications" class="bg-white shadow-lg p-4 max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="text-gray-500 text-center py-4">
          Tidak ada notifikasi baru
        </div>
        <div
          v-for="notif in notifications"
          :key="notif.id"
          class="p-3 bg-gray-50 rounded-lg mb-2 hover:bg-gray-100 cursor-pointer border-l-4 border-green-500"
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

const menuItems = [
  { path: '/admin/dashboard', label: 'Dashboard', icon: '📊' },
  { path: '/admin/medicines', label: 'Obat', icon: '💊' },
  { path: '/admin/categories', label: 'Kategori', icon: '📂' },
  { path: '/admin/suppliers', label: 'Supplier', icon: '🏭' },
  { path: '/admin/warehouses', label: 'Gudang', icon: '🏢' },
  { path: '/admin/stocks', label: 'Stok', icon: '📦' },
  { path: '/admin/purchases', label: 'Pembelian', icon: '🛒' },
  { path: '/admin/orders', label: 'Pesanan', icon: '📋' },
  { path: '/admin/employees', label: 'Karyawan', icon: '👥' },
  { path: '/admin/attendance', label: 'Kehadiran', icon: '✅' },
  { path: '/admin/promotions', label: 'Promo', icon: '🎉' },
  { path: '/admin/vouchers', label: 'Voucher', icon: '🎟️' },
  { path: '/admin/reports', label: 'Laporan', icon: '📈' },
]

const currentPageTitle = computed(() => {
  const item = menuItems.find(m => m.path === route.path)
  return item?.label || 'Dashboard'
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

// Refresh notifications every 30 seconds
const interval = setInterval(fetchNotifications, 30000)

watch(
  () => route.path,
  () => {
    showNotifications.value = false
  }
)
</script>