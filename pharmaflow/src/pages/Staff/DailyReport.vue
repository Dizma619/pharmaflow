<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">📋 Laporan Harian</h1>
      <div class="flex gap-2">
        <button
          @click="setDateToToday"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          📅 Hari Ini
        </button>
        <button
          @click="downloadPDF"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
        >
          📥 Download PDF
        </button>
      </div>
    </div>

    <!-- Date Selector -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <div class="flex gap-4 items-end">
        <div class="flex-1">
          <label class="block text-sm font-semibold mb-2">📅 Pilih Tanggal</label>
          <input
            v-model="selectedDate"
            type="date"
            @change="fetchDailyReport"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold"
          />
        </div>
        <button
          @click="fetchDailyReport"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Load
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <p class="text-lg text-gray-600">⏳ Memuat laporan...</p>
    </div>

    <!-- Report Summary -->
    <div v-else class="grid grid-cols-4 gap-6">
      <!-- Total Transactions -->
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Total Transaksi</p>
            <p class="text-4xl font-bold text-blue-600">{{ report.transaction_count || 0 }}</p>
          </div>
          <span class="text-4xl">🧾</span>
        </div>
      </div>

      <!-- Total Sales -->
      <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-600 rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Total Penjualan</p>
            <p class="text-3xl font-bold text-green-600">Rp{{ formatPrice(report.total_sales || 0) }}</p>
          </div>
          <span class="text-4xl">💰</span>
        </div>
      </div>

      <!-- Total Discount -->
      <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-600 rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Total Diskon</p>
            <p class="text-3xl font-bold text-red-600">Rp{{ formatPrice(report.total_discount || 0) }}</p>
          </div>
          <span class="text-4xl">🔴</span>
        </div>
      </div>

      <!-- Avg Transaction -->
      <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-l-4 border-purple-600 rounded-lg shadow-md p-6 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-gray-600 text-sm font-semibold mb-2">Rata-rata Transaksi</p>
            <p class="text-3xl font-bold text-purple-600">Rp{{ formatPrice(report.average_transaction || 0) }}</p>
          </div>
          <span class="text-4xl">📊</span>
        </div>
      </div>
    </div>

    <!-- Payment Status Breakdown -->
    <div v-if="report.by_payment_status && report.by_payment_status.length > 0" class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-6">💳 Ringkasan Pembayaran</h2>

      <div class="grid grid-cols-2 gap-6">
        <div v-for="status in report.by_payment_status" :key="status.payment_status" class="p-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border-2 border-gray-200 hover:shadow-lg transition">
          <p class="text-gray-600 font-semibold text-lg mb-3">
            {{ status.payment_status === 'lunas' ? '✅ Tunai/Lunas' : '📋 Kredit' }}
          </p>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-gray-600">Jumlah Transaksi</span>
              <span class="font-bold text-blue-600 text-lg">{{ status.count }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Total Penjualan</span>
              <span class="font-bold text-green-600 text-lg">Rp{{ formatPrice(status.total) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Selling Products -->
    <div v-if="report.top_products && report.top_products.length > 0" class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-6">🔥 Produk Terlaris</h2>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-200">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">Peringkat</th>
              <th class="px-6 py-3 text-left font-semibold">Produk</th>
              <th class="px-6 py-3 text-left font-semibold">Kategori</th>
              <th class="px-6 py-3 text-center font-semibold">Qty Terjual</th>
              <th class="px-6 py-3 text-right font-semibold">Total Penjualan</th>
              <th class="px-6 py-3 text-right font-semibold">Persentase</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="(product, idx) in report.top_products.slice(0, 10)" :key="idx" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-bold text-lg">
                <span v-if="idx === 0" class="text-2xl">🥇</span>
                <span v-else-if="idx === 1" class="text-2xl">🥈</span>
                <span v-else-if="idx === 2" class="text-2xl">🥉</span>
                <span v-else class="text-gray-600">#{{ idx + 1 }}</span>
              </td>
              <td class="px-6 py-4 font-semibold">{{ product.medicine?.name }}</td>
              <td class="px-6 py-4">{{ product.medicine?.category?.name }}</td>
              <td class="px-6 py-4 text-center font-bold">{{ product.total_quantity }}</td>
              <td class="px-6 py-4 text-right font-bold text-green-600">Rp{{ formatPrice(product.total_revenue) }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center gap-2 justify-end">
                  <div class="w-24 bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-green-500 h-2 rounded-full"
                      :style="{ width: (product.percentage || 0) + '%' }"
                    ></div>
                  </div>
                  <span class="font-bold text-sm">{{ product.percentage }}%</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && report.transaction_count === 0" class="text-center py-12 bg-white rounded-lg shadow-md">
      <p class="text-4xl mb-4">📭</p>
      <p class="text-xl text-gray-600">Belum ada transaksi untuk tanggal ini</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage } from 'element-plus'

const selectedDate = ref(dayjs().format('YYYY-MM-DD'))
const report = ref({
  transaction_count: 0,
  total_sales: 0,
  total_discount: 0,
  average_transaction: 0,
  by_payment_status: [],
  top_products: [],
})
const loading = ref(false)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const fetchDailyReport = async () => {
  loading.value = true
  try {
    const response = await api.get('reports/daily', {
      params: { date: selectedDate.value }
    })
    report.value = response.data.data
  } catch (error) {
    ElMessage.error('Gagal memuat laporan')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const setDateToToday = () => {
  selectedDate.value = dayjs().format('YYYY-MM-DD')
  fetchDailyReport()
}

const downloadPDF = () => {
  ElMessage.info('Fitur download PDF akan segera tersedia')
}

onMounted(() => {
  fetchDailyReport()
})
</script>