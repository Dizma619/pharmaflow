<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">📦 Laporan Inventory</h1>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Filter</label>
        <select
          v-model="filterType"
          @change="fetchReport"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Produk</option>
          <option value="low_stock">🟡 Stok Menipis</option>
          <option value="expired">❌ Expired</option>
          <option value="expiring_soon">⚠️ Exp Soon (30d)</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Gudang</label>
        <select
          v-model="filterWarehouse"
          @change="fetchReport"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Gudang</option>
          <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
            {{ wh.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Kategori</label>
        <select
          v-model="filterCategory"
          @change="fetchReport"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
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
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Produk</p>
        <p class="text-3xl font-bold text-blue-600">{{ report.total_medicines }}</p>
      </div>

      <div class="bg-gradient-to-br from-orange-50 to-orange-100 border-l-4 border-orange-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Stok Menipis</p>
        <p class="text-3xl font-bold text-orange-600">{{ report.low_stock_count }}</p>
      </div>

      <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Expired</p>
        <p class="text-3xl font-bold text-red-600">{{ report.expired_count }}</p>
      </div>

      <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-l-4 border-yellow-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Exp Soon (30d)</p>
        <p class="text-3xl font-bold text-yellow-600">{{ report.expiring_soon_count }}</p>
      </div>
    </div>

    <!-- Inventory Detail Table -->
    <div v-if="report.items" class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold mb-6">📊 Detail Inventory</h2>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-200">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">Produk</th>
              <th class="px-6 py-3 text-left font-semibold">Kategori</th>
              <th class="px-6 py-3 text-left font-semibold">Gudang</th>
              <th class="px-6 py-3 text-center font-semibold">Qty</th>
              <th class="px-6 py-3 text-left font-semibold">Exp Date</th>
              <th class="px-6 py-3 text-left font-semibold">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="item in report.items" :key="item.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-semibold">{{ item.medicine.name }}</td>
              <td class="px-6 py-4">{{ item.medicine.category?.name }}</td>
              <td class="px-6 py-4 text-sm">{{ item.warehouse?.name }}</td>
              <td class="px-6 py-4 text-center font-bold">{{ item.quantity }}</td>
              <td class="px-6 py-4 text-sm">{{ formatDate(item.expired_date) }}</td>
              <td class="px-6 py-4">
                <span v-if="isExpired(item.expired_date)" class="px-3 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">
                  ❌ Expired
                </span>
                <span v-else-if="isExpiringSoon(item.expired_date)" class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">
                  ⚠️ Soon
                </span>
                <span v-else-if="item.quantity <= item.medicine.stock_minimum" class="px-3 py-1 bg-orange-100 text-orange-800 rounded text-xs font-semibold">
                  🟡 Menipis
                </span>
                <span v-else class="px-3 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">
                  ✅ Normal
                </span>
              </td>
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

const filterType = ref('')
const filterWarehouse = ref('')
const filterCategory = ref('')
const report = ref({
  total_medicines: 0,
  low_stock_count: 0,
  expired_count: 0,
  expiring_soon_count: 0,
  items: [],
})
const warehouses = ref([])
const categories = ref([])
const loading = ref(false)

const formatDate = (date) => {
  if (!date) return '-'
  return dayjs(date).format('DD/MM/YYYY')
}

const isExpired = (date) => {
  if (!date) return false
  return dayjs(date).isBefore(dayjs())
}

const isExpiringSoon = (date) => {
  if (!date) return false
  return dayjs(date).isAfter(dayjs()) && dayjs(date).isBefore(dayjs().add(30, 'days'))
}

const fetchReport = async () => {
  loading.value = true
  try {
    const params = {}
    if (filterType.value) params.filter = filterType.value
    if (filterWarehouse.value) params.warehouse_id = filterWarehouse.value
    if (filterCategory.value) params.category_id = filterCategory.value

    const response = await api.get('reports/inventory', { params })
    report.value = response.data.data
  } catch (error) {
    ElMessage.error('Gagal memuat laporan')
  } finally {
    loading.value = false
  }
}

const fetchWarehouses = async () => {
  try {
    const response = await api.get('warehouses?per_page=100')
    warehouses.value = response.data.data.data || []
  } catch (error) {
    console.error('Failed to fetch warehouses:', error)
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get('categories?per_page=100')
    categories.value = response.data.data.data || []
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const downloadPDF = () => {
  ElMessage.info('Fitur download PDF akan segera tersedia')
}

onMounted(() => {
  fetchWarehouses()
  fetchCategories()
  fetchReport()
})
</script>