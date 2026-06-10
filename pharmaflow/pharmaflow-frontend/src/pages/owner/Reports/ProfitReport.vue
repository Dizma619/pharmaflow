<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">💹 Laporan Profit</h1>

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
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Pendapatan</p>
        <p class="text-3xl font-bold text-green-600">Rp{{ formatPrice(report.total_revenue) }}</p>
      </div>

      <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Biaya Beli</p>
        <p class="text-3xl font-bold text-red-600">Rp{{ formatPrice(report.total_cost) }}</p>
      </div>

      <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Profit</p>
        <p class="text-3xl font-bold text-blue-600">Rp{{ formatPrice(report.total_profit) }}</p>
      </div>

      <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-l-4 border-purple-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Profit Margin</p>
        <p class="text-3xl font-bold text-purple-600">{{ report.profit_margin }}%</p>
      </div>
    </div>

    <!-- Detail Table -->
    <div v-if="report.details" class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-6">📊 Detail Profit</h2>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-200">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
              <th class="px-6 py-3 text-right font-semibold">Revenue</th>
              <th class="px-6 py-3 text-right font-semibold">Biaya Beli</th>
              <th class="px-6 py-3 text-right font-semibold">Profit</th>
              <th class="px-6 py-3 text-right font-semibold">Margin</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="detail in report.details" :key="detail.date" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-semibold">{{ detail.date }}</td>
              <td class="px-6 py-4 text-right font-bold text-green-600">Rp{{ formatPrice(detail.revenue) }}</td>
              <td class="px-6 py-4 text-right font-bold text-red-600">Rp{{ formatPrice(detail.cost) }}</td>
              <td class="px-6 py-4 text-right font-bold text-blue-600">Rp{{ formatPrice(detail.profit) }}</td>
              <td class="px-6 py-4 text-right">{{ detail.margin }}%</td>
            </tr>
          </tbody>
        </table>
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
  total_revenue: 0,
  total_cost: 0,
  total_profit: 0,
  profit_margin: 0,
  details: [],
})
const loading = ref(false)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const fetchReport = async () => {
  loading.value = true
  try {
    const response = await api.get('reports/profit', {
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