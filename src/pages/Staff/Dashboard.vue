<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">📊 Staff Dashboard</h1>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <div class="flex gap-4 items-end">
        <div class="flex-1">
          <label class="block text-sm font-semibold mb-2">Dari Tanggal</label>
          <input
            v-model="startDate"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div class="flex-1">
          <label class="block text-sm font-semibold mb-2">Sampai Tanggal</label>
          <input
            v-model="endDate"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <button
          @click="fetchDashboard"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Filter
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <p class="text-lg text-gray-600">⏳ Memuat data...</p>
    </div>

    <!-- Summary Cards -->
    <div v-else class="grid grid-cols-4 gap-6">
      <!-- Today's Sales -->
      <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-600 rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Penjualan Hari Ini</p>
            <p class="text-3xl font-bold text-green-600">Rp{{ formatPrice(summary.sales.today_sales) }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ summary.sales.today_transactions }} transaksi</p>
          </div>
          <span class="text-4xl">💰</span>
        </div>
      </div>

      <!-- Total Sales Period -->
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Total Penjualan</p>
            <p class="text-3xl font-bold text-blue-600">Rp{{ formatPrice(summary.sales.total_revenue) }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ summary.sales.total_orders }} pesanan</p>
          </div>
          <span class="text-4xl">📈</span>
        </div>
      </div>

      <!-- Low Stock Items -->
      <div class="bg-gradient-to-br from-orange-50 to-orange-100 border-l-4 border-orange-600 rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Stok Menipis</p>
            <p class="text-3xl font-bold text-orange-600">{{ summary.inventory.low_stock_count }}</p>
            <router-link to="/staff/stocks" class="text-xs text-orange-600 hover:underline mt-1">
              Lihat detail →
            </router-link>
          </div>
          <span class="text-4xl">⚠️</span>
        </div>
      </div>

      <!-- Total Employees -->
      <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-l-4 border-purple-600 rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Karyawan Aktif</p>
            <p class="text-3xl font-bold text-purple-600">{{ summary.employees.active }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ attendance.present }} hadir hari ini</p>
          </div>
          <span class="text-4xl">👥</span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-6">⚡ Aksi Cepat</h2>

      <div class="grid grid-cols-6 gap-4">
        <router-link
          to="/staff/pos"
          class="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition text-center border-2 border-green-200 hover:shadow-lg"
        >
          <p class="text-4xl mb-2">🖥️</p>
          <p class="font-semibold text-green-700 text-sm">Buka POS</p>
        </router-link>

        <router-link
          to="/staff/medicines"
          class="p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition text-center border-2 border-blue-200 hover:shadow-lg"
        >
          <p class="text-4xl mb-2">💊</p>
          <p class="font-semibold text-blue-700 text-sm">Kelola Obat</p>
        </router-link>

        <router-link
          to="/staff/purchases"
          class="p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition text-center border-2 border-purple-200 hover:shadow-lg"
        >
          <p class="text-4xl mb-2">🛒</p>
          <p class="font-semibold text-purple-700 text-sm">Pembelian</p>
        </router-link>

        <router-link
          to="/staff/stocks"
          class="p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition text-center border-2 border-orange-200 hover:shadow-lg"
        >
          <p class="text-4xl mb-2">📦</p>
          <p class="font-semibold text-orange-700 text-sm">Kelola Stok</p>
        </router-link>

        <router-link
          to="/staff/employees"
          class="p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition text-center border-2 border-indigo-200 hover:shadow-lg"
        >
          <p class="text-4xl mb-2">👥</p>
          <p class="font-semibold text-indigo-700 text-sm">Karyawan</p>
        </router-link>

        <router-link
          to="/staff/reports/sales"
          class="p-4 bg-red-50 rounded-lg hover:bg-red-100 transition text-center border-2 border-red-200 hover:shadow-lg"
        >
          <p class="text-4xl mb-2">📊</p>
          <p class="font-semibold text-red-700 text-sm">Laporan</p>
        </router-link>
      </div>
    </div>

    <!-- Top 5 Selling Products -->
    <div class="grid grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">🔥 Produk Terlaris</h2>

        <div v-if="topProducts.length === 0" class="text-center py-8 text-gray-600">
          Belum ada penjualan
        </div>

        <div v-else class="space-y-2">
          <div v-for="(product, idx) in topProducts.slice(0, 5)" :key="idx" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
            <div class="flex-1">
              <p class="font-semibold text-sm">{{ idx + 1 }}. {{ product.medicine.name }}</p>
              <p class="text-xs text-gray-600">{{ product.total_quantity }} unit</p>
            </div>
            <p class="font-bold text-green-600 text-sm">Rp{{ formatPrice(product.total_revenue) }}</p>
          </div>
        </div>
      </div>

      <!-- Attendance Today -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">✅ Kehadiran Hari Ini</h2>

        <div class="grid grid-cols-2 gap-3">
          <div class="text-center p-4 bg-green-50 rounded-lg border-2 border-green-200">
            <p class="text-3xl font-bold text-green-600">{{ attendance.present }}</p>
            <p class="text-xs text-gray-600 mt-1">Hadir</p>
          </div>

          <div class="text-center p-4 bg-red-50 rounded-lg border-2 border-red-200">
            <p class="text-3xl font-bold text-red-600">{{ attendance.absent }}</p>
            <p class="text-xs text-gray-600 mt-1">Alfa</p>
          </div>

          <div class="text-center p-4 bg-orange-50 rounded-lg border-2 border-orange-200">
            <p class="text-3xl font-bold text-orange-600">{{ attendance.sick }}</p>
            <p class="text-xs text-gray-600 mt-1">Sakit</p>
          </div>

          <div class="text-center p-4 bg-yellow-50 rounded-lg border-2 border-yellow-200">
            <p class="text-3xl font-bold text-yellow-600">{{ attendance.leave }}</p>
            <p class="text-xs text-gray-600 mt-1">Izin</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Inventory Status -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-4">📦 Status Inventory</h2>

      <div class="grid grid-cols-4 gap-4">
        <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
          <p class="text-gray-600 text-sm font-semibold">Total Produk</p>
          <p class="text-3xl font-bold text-blue-600 mt-2">{{ summary.inventory.total_medicines }}</p>
        </div>

        <div class="p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg">
          <p class="text-gray-600 text-sm font-semibold">Habis</p>
          <p class="text-3xl font-bold text-red-600 mt-2">{{ summary.inventory.out_of_stock }}</p>
        </div>

        <div class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg">
          <p class="text-gray-600 text-sm font-semibold">Expired</p>
          <p class="text-3xl font-bold text-orange-600 mt-2">{{ summary.inventory.expired_count }}</p>
        </div>

        <div class="p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg">
          <p class="text-gray-600 text-sm font-semibold">Exp Soon (30d)</p>
          <p class="text-3xl font-bold text-yellow-600 mt-2">{{ summary.inventory.expiring_soon }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'

const startDate = ref(dayjs().startOfMonth().format('YYYY-MM-DD'))
const endDate = ref(dayjs().format('YYYY-MM-DD'))
const summary = ref({
  sales: { today_sales: 0, today_transactions: 0, total_revenue: 0, total_orders: 0 },
  inventory: { total_medicines: 0, low_stock_count: 0, expired_count: 0, out_of_stock: 0, expiring_soon: 0 },
  employees: { active: 0 }
})
const topProducts = ref([])
const attendance = ref({ present: 0, absent: 0, sick: 0, leave: 0 })
const loading = ref(false)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const fetchDashboard = async () => {
  loading.value = true
  try {
    const response = await api.get('dashboard/summary', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value
      }
    })
    summary.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch dashboard:', error)
  } finally {
    loading.value = false
  }
}

const fetchTopProducts = async () => {
  try {
    const response = await api.get('reports/sales', {
      params: { period: 'daily' }
    })
    topProducts.value = response.data.data.top_products || []
  } catch (error) {
    console.error('Failed to fetch top products:', error)
  }
}

const fetchAttendance = async () => {
  try {
    const response = await api.get('attendance/today')
    const data = response.data.data.summary || {}
    attendance.value = {
      present: data.present || 0,
      absent: data.absent || 0,
      sick: data.sick || 0,
      leave: data.leave || 0
    }
  } catch (error) {
    console.error('Failed to fetch attendance:', error)
  }
}

onMounted(() => {
  fetchDashboard()
  fetchTopProducts()
  fetchAttendance()
})
</script>