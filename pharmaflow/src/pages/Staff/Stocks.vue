<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">📦 Manajemen Stok</h1>
      <div class="flex gap-2">
        <button
          @click="openStockOpname"
          class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition font-semibold"
        >
          📋 Opname
        </button>
        <button
          @click="openAdjustment"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔄 Adjustment
        </button>
      </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Gudang</label>
        <select
          v-model="filterWarehouse"
          @change="fetchStocks"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Gudang</option>
          <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
            {{ wh.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Filter</label>
        <select
          v-model="filterType"
          @change="fetchStocks"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua</option>
          <option value="low_stock">Stok Menipis</option>
          <option value="expired">Expired</option>
          <option value="expiring_soon">Exp Soon (30d)</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Cari</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari obat..."
          @keyup.enter="fetchStocks"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div class="flex items-end">
        <button
          @click="fetchStocks"
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Cari
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat stok...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">Obat</th>
            <th class="px-6 py-3 text-left font-semibold">Gudang</th>
            <th class="px-6 py-3 text-left font-semibold">Rak</th>
            <th class="px-6 py-3 text-center font-semibold">Qty</th>
            <th class="px-6 py-3 text-left font-semibold">Exp Date</th>
            <th class="px-6 py-3 text-left font-semibold">Status</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="stock in stocks" :key="stock.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <p class="font-semibold">{{ stock.medicine?.name }}</p>
              <p class="text-xs text-gray-600">{{ stock.medicine?.code }}</p>
            </td>
            <td class="px-6 py-4 text-sm">{{ stock.warehouse?.name }}</td>
            <td class="px-6 py-4 text-sm">{{ stock.shelf?.code || '-' }}</td>
            <td class="px-6 py-4 text-center">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                stock.quantity <= stock.medicine?.stock_minimum ? 'bg-red-500' :
                stock.quantity <= stock.medicine?.stock_minimum * 1.5 ? 'bg-orange-500' :
                'bg-green-500'
              ]">
                {{ stock.quantity }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm">
              <span :class="[
                'px-2 py-1 rounded text-xs font-semibold',
                isExpired(stock.expired_date) ? 'bg-red-100 text-red-800' :
                isExpiringSoon(stock.expired_date) ? 'bg-orange-100 text-orange-800' :
                'bg-green-100 text-green-800'
              ]">
                {{ formatDate(stock.expired_date) }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm">
              <span v-if="isExpired(stock.expired_date)" class="text-red-600 font-semibold">❌ Expired</span>
              <span v-else-if="isExpiringSoon(stock.expired_date)" class="text-orange-600 font-semibold">⚠️ Soon</span>
              <span v-else-if="stock.quantity === 0" class="text-red-600 font-semibold">🔴 Habis</span>
              <span v-else-if="stock.quantity <= stock.medicine?.stock_minimum" class="text-orange-600 font-semibold">🟡 Menipis</span>
              <span v-else class="text-green-600 font-semibold">✅ Normal</span>
            </td>
            <td class="px-6 py-4 text-center">
              <button
                @click="openAdjustmentForStock(stock)"
                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm font-semibold"
              >
                🔄 Adjust
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Stock Adjustment Modal -->
    <div v-if="showAdjustmentForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        <h2 class="text-2xl font-bold mb-6">🔄 Stock Adjustment</h2>

        <form @submit.prevent="saveAdjustment" class="space-y-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Obat</label>
            <input
              :value="adjustmentForm.medicine_name"
              type="text"
              disabled
              class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Stok Sekarang</label>
              <input
                :value="adjustmentForm.quantity_before"
                type="number"
                disabled
                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 font-bold text-center"
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Stok Baru *</label>
              <input
                v-model.number="adjustmentForm.quantity_after"
                type="number"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold text-center"
                required
              />
            </div>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Tipe Adjustment *</label>
            <select
              v-model="adjustmentForm.type"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            >
              <option value="">Pilih Tipe</option>
              <option value="penambahan">➕ Penambahan</option>
              <option value="pengurangan">➖ Pengurangan</option>
              <option value="koreksi">🔄 Koreksi</option>
            </select>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Alasan *</label>
            <select
              v-model="adjustmentForm.reason"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            >
              <option value="">Pilih Alasan</option>
              <option value="opname">📋 Stock Opname</option>
              <option value="rusak">💔 Barang Rusak</option>
              <option value="hilang">🔍 Barang Hilang</option>
              <option value="retur">↩️ Retur dari Supplier</option>
              <option value="koreksi_data">📝 Koreksi Data</option>
            </select>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Catatan</label>
            <textarea
              v-model="adjustmentForm.notes"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 h-20"
              placeholder="Catatan tambahan..."
            ></textarea>
          </div>

          <div class="flex gap-2 pt-4">
            <button
              type="submit"
              :disabled="savingAdjustment"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition font-semibold"
            >
              {{ savingAdjustment ? '⏳ Saving...' : '✅ Simpan' }}
            </button>
            <button
              type="button"
              @click="showAdjustmentForm = false"
              class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
            >
              ❌ Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage, ElMessageBox } from 'element-plus'

const stocks = ref([])
const warehouses = ref([])
const loading = ref(false)
const savingAdjustment = ref(false)
const showAdjustmentForm = ref(false)
const filterWarehouse = ref('')
const filterType = ref('')
const searchQuery = ref('')

const adjustmentForm = ref({
  stock_id: '',
  medicine_name: '',
  quantity_before: 0,
  quantity_after: 0,
  type: '',
  reason: '',
  notes: '',
})

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

const fetchStocks = async () => {
  loading.value = true
  try {
    let endpoint = 'stocks'
    const params = { per_page: 100 }

    if (filterType.value === 'low_stock') {
      endpoint = 'stocks/low-stock'
    } else if (filterType.value === 'expired') {
      endpoint = 'stocks/expired'
    } else if (filterType.value === 'expiring_soon') {
      endpoint = 'stocks/expiring-soon'
    }

    if (filterWarehouse.value) params.warehouse_id = filterWarehouse.value
    if (searchQuery.value) params.search = searchQuery.value

    const response = await api.get(endpoint, { params })
    stocks.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat stok')
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

const openAdjustment = () => {
  adjustmentForm.value = {
    stock_id: '',
    medicine_name: '',
    quantity_before: 0,
    quantity_after: 0,
    type: '',
    reason: '',
    notes: '',
  }
  showAdjustmentForm.value = true
}

const openAdjustmentForStock = (stock) => {
  adjustmentForm.value = {
    stock_id: stock.id,
    medicine_name: stock.medicine?.name,
    quantity_before: stock.quantity,
    quantity_after: stock.quantity,
    type: '',
    reason: '',
    notes: '',
  }
  showAdjustmentForm.value = true
}

const openStockOpname = () => {
  ElMessage.info('Fitur Stock Opname akan segera tersedia')
}

const saveAdjustment = async () => {
  if (!adjustmentForm.value.type) {
    ElMessage.warning('Pilih tipe adjustment')
    return
  }

  if (!adjustmentForm.value.reason) {
    ElMessage.warning('Pilih alasan')
    return
  }

  savingAdjustment.value = true
  try {
    await api.post('stocks/adjustment', {
      stock_id: adjustmentForm.value.stock_id,
      quantity_after: adjustmentForm.value.quantity_after,
      type: adjustmentForm.value.type,
      reason: adjustmentForm.value.reason,
      notes: adjustmentForm.value.notes,
    })

    ElMessage.success('Stock adjustment berhasil')
    showAdjustmentForm.value = false
    fetchStocks()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menyimpan adjustment')
  } finally {
    savingAdjustment.value = false
  }
}

onMounted(() => {
  fetchWarehouses()
  fetchStocks()
})
</script>