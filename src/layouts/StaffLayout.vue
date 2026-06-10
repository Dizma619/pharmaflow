<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-lg fixed h-screen overflow-y-auto">
      <div class="p-6">
        <h1 class="text-2xl font-bold">FharmaFlow</h1>
        <p class="text-blue-200 text-sm">Staff Dashboard</p>
        <p class="text-blue-300 text-xs mt-2 font-semibold">{{ authStore.user?.department ? '📦 ' + getDepartmentLabel(authStore.user.department) : 'Staff' }}</p>
      </div>

      <nav class="mt-6 space-y-1">
        <!-- Dashboard -->
        <div class="px-6 py-2">
          <p class="text-blue-200 text-xs font-bold uppercase mb-3">Dashboard</p>
          <router-link
            to="/staff/dashboard"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1"
            :class="isActive('/staff/dashboard') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">📊</span>
            <span>Dashboard</span>
          </router-link>
        </div>

        <!-- POS & Transaksi -->
        <div class="px-6 py-2 border-t border-blue-700 pt-4">
          <p class="text-blue-200 text-xs font-bold uppercase mb-3">🖥️ POS & Transaksi</p>
          <router-link
            to="/staff/pos"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1"
            :class="isActive('/staff/pos') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">🖥️</span>
            <span>POS Kasir</span>
          </router-link>
          <router-link
            to="/staff/transactions"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1"
            :class="isActive('/staff/transactions') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">💰</span>
            <span>Transaksi</span>
          </router-link>
          <router-link
            to="/staff/daily-report"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition"
            :class="isActive('/staff/daily-report') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">📋</span>
            <span>Laporan Harian</span>
          </router-link>
        </div>

        <!-- Inventory & Stock -->
        <div class="px-6 py-2 border-t border-blue-700 pt-4">
          <p class="text-blue-200 text-xs font-bold uppercase mb-3">📦 Inventory</p>
          <router-link
            to="/staff/medicines"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/medicines') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">💊</span>
            <span>Obat</span>
          </router-link>
          <router-link
            to="/staff/categories"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/categories') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">📂</span>
            <span>Kategori</span>
          </router-link>
          <router-link
            to="/staff/stocks"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/stocks') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">📦</span>
            <span>Stok</span>
          </router-link>
          <router-link
            to="/staff/warehouses"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/warehouses') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">🏢</span>
            <span>Gudang</span>
          </router-link>
          <router-link
            to="/staff/shelves"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition text-sm"
            :class="isActive('/staff/shelves') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">📫</span>
            <span>Rak</span>
          </router-link>
        </div>

        <!-- Purchasing -->
        <div class="px-6 py-2 border-t border-blue-700 pt-4">
          <p class="text-blue-200 text-xs font-bold uppercase mb-3">🛒 Pembelian</p>
          <router-link
            to="/staff/purchases"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1"
            :class="isActive('/staff/purchases') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">🛒</span>
            <span>Pembelian</span>
          </router-link>
          <router-link
            to="/staff/suppliers"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition"
            :class="isActive('/staff/suppliers') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">🏭</span>
            <span>Supplier</span>
          </router-link>
        </div>

        <!-- Management -->
        <div class="px-6 py-2 border-t border-blue-700 pt-4">
          <p class="text-blue-200 text-xs font-bold uppercase mb-3">⚙️ Manajemen</p>
          <router-link
            to="/staff/orders"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/orders') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">📦</span>
            <span>Pesanan</span>
          </router-link>
          <router-link
            to="/staff/employees"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/employees') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">👥</span>
            <span>Karyawan</span>
          </router-link>
          <router-link
            to="/staff/attendance"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/attendance') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">✅</span>
            <span>Kehadiran</span>
          </router-link>
          <router-link
            to="/staff/promotions"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/promotions') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">🎉</span>
            <span>Promosi</span>
          </router-link>
          <router-link
            to="/staff/vouchers"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition text-sm"
            :class="isActive('/staff/vouchers') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">🎟️</span>
            <span>Voucher</span>
          </router-link>
        </div>

        <!-- Reports -->
        <div class="px-6 py-2 border-t border-blue-700 pt-4">
          <p class="text-blue-200 text-xs font-bold uppercase mb-3">📊 Laporan</p>
          <router-link
            to="/staff/reports/sales"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/reports/sales') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">📈</span>
            <span>Penjualan</span>
          </router-link>
          <router-link
            to="/staff/reports/profit"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition mb-1 text-sm"
            :class="isActive('/staff/reports/profit') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">💹</span>
            <span>Profit</span>
          </router-link>
          <router-link
            to="/staff/reports/inventory"
            class="flex items-center space-x-3 py-3 px-4 rounded-lg transition text-sm"
            :class="isActive('/staff/reports/inventory') ? 'bg-blue-600' : 'hover:bg-blue-700'"
          >
            <span class="text-xl">📦</span>
            <span>Inventory</span>
          </router-link>
        </div>
      </nav>

      <!-- Logout -->
      <div class="border-t border-blue-700 p-6 absolute bottom-0 w-64">
        <button
          @click="logout"
          class="w-full flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-blue-700 transition text-red-300 font-semibold"
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
          <!-- Notifications Bell -->
          <div class="relative">
            <button
              @click="toggleNotifications"
              class="relative text-2xl hover:text-blue-600 transition"
            >
              🔔
              <span
                v-if="unreadNotifications > 0"
                class="absolute top-0 right-0 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
              >
                {{ unreadNotifications > 99 ? '99+' : unreadNotifications }}
              </span>
            </button>

            <!-- Notifications Dropdown -->
            <div
              v-if="showNotificationsDropdown"
              class="absolute top-12 right-0 w-96 bg-white rounded-lg shadow-xl border border-gray-200 max-h-96 overflow-y-auto z-50"
            >
              <!-- Header -->
              <div class="p-4 border-b bg-gradient-to-r from-blue-50 to-blue-100 sticky top-0 flex justify-between items-center">
                <h3 class="font-bold text-lg">🔔 Notifikasi</h3>
                <button
                  @click="markAllNotificationsAsRead"
                  class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition font-semibold"
                >
                  ✓ Tandai Semua
                </button>
              </div>

              <!-- Notifications List -->
              <div v-if="notifications.length === 0" class="p-8 text-center text-gray-500">
                <p class="text-lg">📭</p>
                <p>Tidak ada notifikasi</p>
              </div>

              <div v-else class="divide-y">
                <div
                  v-for="notif in notifications"
                  :key="notif.id"
                  @click="handleNotificationClick(notif)"
                  :class="[
                    'p-4 cursor-pointer hover:bg-gray-50 transition border-l-4',
                    notif.is_read ? 'border-gray-300 bg-gray-50' : 'border-blue-500 bg-blue-50'
                  ]"
                >
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <!-- Icon & Title -->
                      <div class="flex items-center gap-2 mb-1">
                        <span class="text-lg">{{ getNotificationIcon(notif.type) }}</span>
                        <p class="font-bold text-gray-800">{{ notif.title }}</p>
                        <span v-if="!notif.is_read" class="ml-auto w-2 h-2 bg-blue-600 rounded-full"></span>
                      </div>

                      <!-- Message -->
                      <p class="text-sm text-gray-600 ml-7">{{ notif.message }}</p>

                      <!-- Time -->
                      <p class="text-xs text-gray-500 ml-7 mt-1">{{ formatTime(notif.created_at) }}</p>
                    </div>

                    <!-- Mark as read button -->
                    <button
                      v-if="!notif.is_read"
                      @click.stop="markNotificationAsRead(notif.id)"
                      class="ml-2 px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition"
                    >
                      ✓
                    </button>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="p-3 border-t text-center bg-gray-50">
                <router-link
                  to="/staff/notifications"
                  class="text-sm text-blue-600 hover:text-blue-800 font-semibold"
                >
                  Lihat Semua Notifikasi →
                </router-link>
              </div>
            </div>
          </div>

          <!-- User Info -->
          <div class="flex items-center space-x-3 pl-6 border-l border-gray-300">
            <div class="text-right">
              <p class="text-gray-700 font-semibold text-sm">{{ authStore.user?.name }}</p>
              <p class="text-gray-500 text-xs">{{ getDepartmentLabel(authStore.user?.department) }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
              {{ authStore.user?.name.charAt(0).toUpperCase() }}
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6 overflow-y-auto">
        <router-view />
      </main>
    </div>

    <!-- Overlay when dropdown is open -->
    <div
      v-if="showNotificationsDropdown"
      class="fixed inset-0 z-40"
      @click="showNotificationsDropdown = false"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import dayjs from 'dayjs'
import 'dayjs/locale/id'
import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(relativeTime)
dayjs.locale('id')

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()

const notifications = ref([])
const unreadNotifications = ref(0)
const showNotificationsDropdown = ref(false)
let notificationInterval = null

const currentPageTitle = computed(() => {
  const titles = {
    'dashboard': '📊 Dashboard',
    'pos': '🖥️ POS Kasir',
    'transactions': '💰 Riwayat Transaksi',
    'daily-report': '📋 Laporan Harian',
    'medicines': '💊 Manajemen Obat',
    'categories': '📂 Manajemen Kategori',
    'stocks': '📦 Manajemen Stok',
    'warehouses': '🏢 Manajemen Gudang',
    'shelves': '📫 Manajemen Rak',
    'purchases': '🛒 Manajemen Pembelian',
    'suppliers': '🏭 Manajemen Supplier',
    'orders': '📦 Manajemen Pesanan',
    'employees': '👥 Manajemen Karyawan',
    'attendance': '✅ Manajemen Kehadiran',
    'promotions': '🎉 Manajemen Promosi',
    'vouchers': '🎟️ Manajemen Voucher',
    'sales': '📈 Laporan Penjualan',
    'profit': '💹 Laporan Profit',
    'inventory': '📦 Laporan Inventory',
    'notifications': '🔔 Semua Notifikasi',
  }

  const parts = route.path.split('/')
  const lastPart = parts[parts.length - 1]
  return titles[lastPart] || 'Staff Dashboard'
})

const isActive = (path) => {
  return route.path.startsWith(path)
}

const getDepartmentLabel = (dept) => {
  const labels = {
    'kasir': '💵 Kasir',
    'inventory': '📦 Inventory',
    'admin': '⚙️ Admin',
  }
  return labels[dept] || 'Staff'
}

const formatTime = (time) => {
  return dayjs(time).fromNow()
}

const getNotificationIcon = (type) => {
  const icons = {
    'stock_warning': '⚠️',
    'low_stock': '🟡',
    'expired': '❌',
    'purchase': '🛒',
    'order': '📦',
    'payment': '💳',
    'system': 'ℹ️',
    'info': 'ℹ️',
  }
  return icons[type] || '🔔'
}

const fetchNotifications = async () => {
  try {
    const response = await api.get('notifications', {
      params: {
        per_page: 10,
        order_by: 'desc',
      }
    })
    notifications.value = response.data.data.data || []
    unreadNotifications.value = response.data.unread_count || 0
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  }
}

const toggleNotifications = () => {
  showNotificationsDropdown.value = !showNotificationsDropdown.value
  if (showNotificationsDropdown.value) {
    fetchNotifications()
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

const markAllNotificationsAsRead = async () => {
  try {
    await api.post('notifications/mark-all-read')
    fetchNotifications()
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const handleNotificationClick = (notif) => {
  if (!notif.is_read) {
    markNotificationAsRead(notif.id)
  }

  // Handle navigation berdasarkan type
  if (notif.type === 'low_stock' || notif.type === 'stock_warning') {
    router.push('/staff/stocks')
  } else if (notif.type === 'purchase') {
    router.push('/staff/purchases')
  } else if (notif.type === 'order') {
    router.push('/staff/orders')
  }

  showNotificationsDropdown.value = false
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

  // Refresh notifications setiap 30 detik
  notificationInterval = setInterval(() => {
    fetchNotifications()
  }, 30000)
})

onUnmounted(() => {
  if (notificationInterval) {
    clearInterval(notificationInterval)
  }
})

watch(
  () => route.path,
  () => {
    showNotificationsDropdown.value = false
  }
)
</script>

<style scoped>
/* Smooth transitions */
.transition {
  transition: all 0.3s ease;
}

/* Notification animation */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>