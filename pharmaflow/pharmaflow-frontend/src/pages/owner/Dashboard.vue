<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">📊 Dashboard</h1>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <div class="flex gap-4 items-end">
        <div class="flex-1">
          <label class="block text-sm font-semibold mb-2">Dari Tanggal</label>
          <input
            v-model="startDate"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
          />
        </div>
        <div class="flex-1">
          <label class="block text-sm font-semibold mb-2">Sampai Tanggal</label>
          <input
            v-model="endDate"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
          />
        </div>
        <button
          @click="fetchDashboard"
          class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
        >
          🔍 Filter
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <p class="text-lg text-gray-600">⏳ Memuat dashboard...</p>
    </div>

    <!-- Summary Cards -->
    <div v-else class="grid grid-cols-4 gap-6">
      <!-- Total Revenue -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Total Pendapatan</p>
            <p class="text-3xl font-bold text-green-600">Rp{{ formatPrice(summary.sales.total_revenue) }}</p>
          </div>
          <span class="text-4xl">💰</span>
        </div>
      </div>

      <!-- Total Orders -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Total Pesanan</p>
            <p class="text-3xl font-bold text-blue-600">{{ summary.sales.total_orders }}</p>
          </div>
          <span class="text-4xl">📦</span>
        </div>
      </div>

      <!-- Total Expenses -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Total Pengeluaran</p>
            <p class="text-3xl font-bold text-red-600">Rp{{ formatPrice(summary.financial.total_expenses) }}</p>
          </div>
          <span class="text-4xl">💸</span>
        </div>
      </div>

      <!-- Profit -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Profit</p>
            <p class="text-3xl font-bold text-purple-600">Rp{{ formatPrice(summary.financial.profit) }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ summary.financial.profit_margin }}</p>
          </div>
          <span class="text-4xl">📈</span>
        </div>
      </div>
    </div>

    <!-- Inventory Overview -->
    <div class="grid grid-cols-3 gap-6">
      <!-- Total Medicines -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Produk</p>
        <p class="text-4xl font-bold text-blue-600">{{ summary.inventory.total_medicines }}</p>
      </div>

      <!-- Low Stock -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Stok Menipis</p>
        <p class="text-4xl font-bold text-orange-600">{{ summary.inventory.low_stock_count }}</p>
      </div>

      <!-- Expired -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Barang Expired</p>
        <p class="text-4xl font-bold text-red-600">{{ summary.inventory.expired_count }}</p>
      </div>
    </div>

    <!-- Orders Status -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-6">📋 Status Pesanan</h2>

      <div class="grid grid-cols-4 gap-4">
        <div class="p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
          <p class="text-gray-600 font-semibold">Pending</p>
          <p class="text-3xl font-bold text-yellow-600">{{ summary.orders.pending }}</p>
        </div>

        <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
          <p class="text-gray-600 font-semibold">Diproses</p>
          <p class="text-3xl font-bold text-blue-600">{{ summary.orders.in_process }}</p>
        </div>

        <div class="p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
          <p class="text-gray-600 font-semibold">Dikirim</p>
          <p class="text-3xl font-bold text-purple-600">{{ summary.orders.in_transit }}</p>
        </div>

        <div class="p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
          <p class="text-gray-600 font-semibold">Selesai</p>
          <p class="text-3xl font-bold text-green-600">{{ summary.orders.completed || 0 }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-4">⚡ Aksi Cepat</h2>

      <div class="grid grid-cols-4 gap-4">
        <router-link
          to="/admin/medicines"
          class="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition text-center"
        >
          <p class="text-3xl mb-2">💊</p>
          <p class="font-semibold text-green-600">Kelola Obat</p>
        </router-link>

        <router-link
          to="/admin/stocks"
          class="p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition text-center"
        >
          <p class="text-3xl mb-2">📦</p>
          <p class="font-semibold text-blue-600">Kelola Stok</p>
        </router-link>

        <router-link
          to="/admin/purchases"
          class="p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition text-center"
        >
          <p class="text-3xl mb-2">🛒</p>
          <p class="font-semibold text-purple-600">Pembelian</p>
        </router-link>

        <router-link
          to="/admin/reports"
          class="p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition text-center"
        >
          <p class="text-3xl mb-2">📊</p>
          <p class="font-semibold text-orange-600">Laporan</p>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'

const startDate = ref(
  dayjs()
    .startOf('month')
    .format('YYYY-MM-DD')
)

const endDate = ref(
  dayjs().format('YYYY-MM-DD')
)
const summary = ref({
  sales: { total_revenue: 0, total_orders: 0, total_sales: 0 },
  financial: { total_expenses: 0, profit: 0, profit_margin: '0%' },
  inventory: { total_medicines: 0, low_stock_count: 0, expired_count: 0 },
  orders: { pending: 0, in_process: 0, in_transit: 0, completed: 0 }
})
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

onMounted(() => {
  fetchDashboard()
})
</script>