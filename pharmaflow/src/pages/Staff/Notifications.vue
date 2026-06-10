<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">🔔 Semua Notifikasi</h1>
      <button
        @click="markAllAsRead"
        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
      >
        ✓ Tandai Semua Dibaca
      </button>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Status</label>
        <select
          v-model="filterStatus"
          @change="fetchNotifications"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua</option>
          <option value="unread">📨 Belum Dibaca</option>
          <option value="read">✓ Sudah Dibaca</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Tipe</label>
        <select
          v-model="filterType"
          @change="fetchNotifications"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Tipe</option>
          <option value="stock_warning">⚠️ Peringatan Stok</option>
          <option value="purchase">🛒 Pembelian</option>
          <option value="order">📦 Pesanan</option>
          <option value="payment">💳 Pembayaran</option>
          <option value="system">ℹ️ Sistem</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Periode</label>
        <select
          v-model="filterPeriod"
          @change="fetchNotifications"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua</option>
          <option value="today">Hari Ini</option>
          <option value="week">Minggu Ini</option>
          <option value="month">Bulan Ini</option>
        </select>
      </div>

      <div class="flex items-end">
        <button
          @click="fetchNotifications"
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Filter
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <p class="text-lg text-gray-600">⏳ Memuat notifikasi...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="notifications.length === 0" class="text-center py-12 bg-white rounded-lg shadow-md">
      <p class="text-4xl mb-4">📭</p>
      <p class="text-xl text-gray-600">Tidak ada notifikasi</p>
    </div>

    <!-- Notifications List -->
    <div v-else class="space-y-3">
      <div
        v-for="notif in notifications"
        :key="notif.id"
        :class="[
          'p-5 rounded-lg shadow-md hover:shadow-lg transition cursor-pointer border-l-4',
          notif.is_read ? 'bg-gray-50 border-gray-300' : 'bg-blue-50 border-blue-500'
        ]"
        @click="markAsRead(notif.id)"
      >
        <div class="flex items-start justify-between">
          <!-- Icon & Content -->
          <div class="flex items-start gap-4 flex-1">
            <span class="text-4xl">{{ getNotificationIcon(notif.type) }}</span>

            <div class="flex-1">
              <!-- Title & Status -->
              <div class="flex items-center gap-2 mb-1">
                <p class="text-lg font-bold text-gray-800">{{ notif.title }}</p>
                <span v-if="!notif.is_read" class="px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded">
                  BARU
                </span>
              </div>

              <!-- Message -->
              <p class="text-gray-600 mb-2">{{ notif.message }}</p>

              <!-- Meta Info -->
              <div class="flex items-center gap-4 text-xs text-gray-500">
                <span>{{ formatDatetime(notif.created_at) }}</span>
                <span class="px-2 py-1 bg-gray-100 rounded text-gray-700 font-semibold">
                  {{ getTypeLabel(notif.type) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Action Button -->
          <div class="ml-4">
            <button
              v-if="!notif.is_read"
              @click.stop="markAsRead(notif.id)"
              class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition font-semibold"
            >
              ✓ Baca
            </button>
            <span v-else class="text-green-600 font-bold">✓ Dibaca</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage } from 'element-plus'

const notifications = ref([])
const loading = ref(false)
const filterStatus = ref('')
const filterType = ref('')
const filterPeriod = ref('')

const formatDatetime = (date) => {
  return dayjs(date).format('DD/MM/YYYY HH:mm')
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

const getTypeLabel = (type) => {
  const labels = {
    'stock_warning': 'Peringatan Stok',
    'low_stock': 'Stok Menipis',
    'expired': 'Expired',
    'purchase': 'Pembelian',
    'order': 'Pesanan',
    'payment': 'Pembayaran',
    'system': 'Sistem',
    'info': 'Informasi',
  }
  return labels[type] || type
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }

    if (filterStatus.value === 'unread') {
      params.unread_only = true
    } else if (filterStatus.value === 'read') {
      params.read_only = true
    }

    if (filterType.value) params.type = filterType.value

    if (filterPeriod.value === 'today') {
      params.start_date = dayjs().format('YYYY-MM-DD')
      params.end_date = dayjs().format('YYYY-MM-DD')
    } else if (filterPeriod.value === 'week') {
      params.start_date = dayjs().startOf('week').format('YYYY-MM-DD')
      params.end_date = dayjs().endOf('week').format('YYYY-MM-DD')
    } else if (filterPeriod.value === 'month') {
      params.start_date = dayjs().startOf('month').format('YYYY-MM-DD')
      params.end_date = dayjs().endOf('month').format('YYYY-MM-DD')
    }

    const response = await api.get('notifications', { params })
    notifications.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat notifikasi')
  } finally {
    loading.value = false
  }
}

const markAsRead = async (notifId) => {
  try {
    await api.post(`notifications/${notifId}/read`)
    fetchNotifications()
  } catch (error) {
    console.error('Failed to mark as read:', error)
  }
}

const markAllAsRead = async () => {
  try {
    await api.post('notifications/mark-all-read')
    ElMessage.success('Semua notifikasi ditandai sebagai dibaca')
    fetchNotifications()
  } catch (error) {
    ElMessage.error('Gagal menandai semua')
  }
}

onMounted(() => {
  fetchNotifications()
})
</script>