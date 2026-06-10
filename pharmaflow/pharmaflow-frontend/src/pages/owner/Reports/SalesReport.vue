<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">📈 Laporan Penjualan</h1>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Periode</label>
        <select
          v-model="reportPeriod"
          @change="fetchReport"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="daily">📅 Harian</option>
          <option value="weekly">📆 Mingguan</option>
          <option value="monthly">📊 Bulanan</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Dari Tanggal</label>
        <input
          v-model="startDate"
          type="date"
          @change="fetchReport"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Sampai Tanggal</label>
        <input
          v-model="endDate"
          type="date"
          @change="fetchReport"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div class="flex items-end gap-2">
        <button
          @click="fetchReport"
          class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Filter
        </button>
        <button
          @click="downloadPDF"
          class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
        >
          📥 PDF
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat laporan...</p>
    </div>

    <!-- Summary -->
    <div v-else class="grid grid-cols-4 gap-6">
      <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Penjualan</p>
        <p class="text-3xl font-bold text-green-600">Rp{{ formatPrice(report.total_sales) }}</p>
        <p class="text-xs text-gray-600 mt-1">{{ report.transaction_count }} transaksi</p>
      </div>

      <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Rata-rata Transaksi</p>
        <p class="text-3xl font-bold text-blue-600">Rp{{ formatPrice(report.average_transaction) }}</p>
      </div>

      <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Diskon</p>
        <p class="text-3xl font-bold text-red-600">Rp{{ formatPrice(report.total_discount) }}</p>
      </div>

      <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-l-4 border-purple-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Qty Terjual</p>
        <p class="text-3xl font-bold text-purple-600">{{ report.total_quantity }}</p>
        <p class="text-xs text-gray-600 mt-1">unit</p>
      </div>
    </div>

    <!-- Top Products -->
    <div v-if="report.top_products" class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-6">🔥 Produk Terlaris</h2>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-200">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">Produk</th>
              <th class="px-6 py-3 text-center font-semibold">Qty</th>
              <th class="px-6 py-3 text-right font-semibold">Total Penjualan</th>
              <th class="px-6 py-3 text-right font-semibold">Persentase</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="(product, idx) in report.top_products" :key="idx" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-semibold">{{ idx + 1 }}. {{ product.medicine.name }}</td>
              <td class="px-6 py-4 text-center">{{ product.total_quantity }}</td>
              <td class="px-6 py-4 text-right font-bold text-green-600">Rp{{ formatPrice(product.total_revenue) }}</td>
              <td class="px-6 py-4 text-right">{{ product.percentage }}%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- By Payment Status -->
    <div v-if="report.by_payment_status" class="grid grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">💳 Ringkasan Pembayaran</h2>

        <div class="space-y-4">
          <div v-for="status in report.by_payment_status" :key="status.payment_status" class="p-4 bg-gray-50 rounded-lg">
            <p class="text-gray-600 font-semibold mb-2">
              {{ status.payment_status === 'lunas' ? '✅ Tunai/Lunas' : '📋 Kredit' }}
            </p>
            <p class="text-3xl font-bold text-blue-600 mb-1">{{ status.count }}</p>
            <p class="text-gray-600">Rp{{ formatPrice(status.total) }}</p>
          </div>
        </div>
      </div>

      <!-- Daily Breakdown -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">📅 Breakdown Harian</h2>

        <div v-if="report.daily_breakdown && report.daily_breakdown.length > 0" class="space-y-2 max-h-64 overflow-y-auto">
          <div v-for="(day, idx) in report.daily_breakdown" :key="idx" class="flex justify-between items-center p-2 bg-gray-50 rounded">
            <span class="font-semibold text-sm">{{ day.date }}</span>
            <div class="text-right">
              <p class="font-bold text-green-600">Rp{{ formatPrice(day.total) }}</p>
              <p class="text-xs text-gray-600">{{ day.count }} trans</p>
            </div>
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

const reportPeriod = ref('daily')
const startDate = ref(dayjs().format('YYYY-MM-DD'))
const endDate = ref(dayjs().format('YYYY-MM-DD'))
const report = ref({
  total_sales: 0,
  transaction_count: 0,
  average_transaction: 0,
  total_discount: 0,
  total_quantity: 0,
  top_products: [],
  by_payment_status: [],
  daily_breakdown: [],
})
const loading = ref(false)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const fetchReport = async () => {
  loading.value = true
  try {
    const response = await api.get('reports/sales', {
      params: {
        period: reportPeriod.value,
        start_date: startDate.value,
        end_date: endDate.value,
      }
    })
    report.value = response.data.data
  } catch (error) {
    ElMessage.error('Gagal memuat laporan')
  } finally {
    loading.value = false
  }
}

const downloadPDF = () => {
  ElMessage.info('Fitur download PDF akan segera tersedia')
}

onMounted(() => {
  fetchReport()
})
</script>