<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">🔄 History Adjustment Stok</h1>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-5 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Tipe</label>
        <select
          v-model="filterType"
          @change="fetchAdjustments"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua</option>
          <option value="penambahan">➕ Penambahan</option>
          <option value="pengurangan">➖ Pengurangan</option>
          <option value="koreksi">🔄 Koreksi</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Alasan</label>
        <select
          v-model="filterReason"
          @change="fetchAdjustments"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua</option>
          <option value="opname">📋 Stock Opname</option>
          <option value="rusak">💔 Barang Rusak</option>
          <option value="hilang">🔍 Barang Hilang</option>
          <option value="retur">↩️ Retur</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Dari Tanggal</label>
        <input
          v-model="startDate"
          type="date"
          @change="fetchAdjustments"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Sampai Tanggal</label>
        <input
          v-model="endDate"
          type="date"
          @change="fetchAdjustments"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div class="flex items-end">
        <button
          @click="fetchAdjustments"
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Filter
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat adjustment...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
            <th class="px-6 py-3 text-left font-semibold">Produk</th>
            <th class="px-6 py-3 text-left font-semibold">Tipe</th>
            <th class="px-6 py-3 text-center font-semibold">Stok Sebelum</th>
            <th class="px-6 py-3 text-center font-semibold">Qty Adj</th>
            <th class="px-6 py-3 text-center font-semibold">Stok Sesudah</th>
            <th class="px-6 py-3 text-left font-semibold">Alasan</th>
            <th class="px-6 py-3 text-left font-semibold">Oleh</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="adj in adjustments" :key="adj.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm">{{ formatDate(adj.created_at) }}</td>
            <td class="px-6 py-4 font-semibold">{{ adj.stock?.medicine?.name }}</td>
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                adj.type === 'penambahan' ? 'bg-green-500' :
                adj.type === 'pengurangan' ? 'bg-red-500' :
                'bg-blue-500'
              ]">
                {{ getTypeLabel(adj.type) }}
              </span>
            </td>
            <td class="px-6 py-4 text-center font-bold">{{ adj.quantity_before }}</td>
            <td class="px-6 py-4 text-center font-bold" :class="[
              adj.adjustment_qty >= 0 ? 'text-green-600' : 'text-red-600'
            ]">
              {{ adj.adjustment_qty >= 0 ? '+' : '' }}{{ adj.adjustment_qty }}
            </td>
            <td class="px-6 py-4 text-center font-bold">{{ adj.quantity_after }}</td>
            <td class="px-6 py-4 text-sm">{{ adj.reason }}</td>
            <td class="px-6 py-4 text-sm">{{ adj.adjusted_by?.name }}</td>
            <td class="px-6 py-4 text-center">
              <button
                @click="viewDetail(adj)"
                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm"
              >
                👁️ View
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedAdjustment" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Detail Adjustment</h2>
          <button
            @click="selectedAdjustment = null"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <div class="space-y-4">
          <div class="p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">Produk</p>
            <p class="font-bold text-lg">{{ selectedAdjustment.stock?.medicine?.name }}</p>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg text-center">
              <p class="text-xs text-gray-600 mb-1">Sebelum</p>
              <p class="font-bold text-2xl">{{ selectedAdjustment.quantity_before }}</p>
            </div>

            <div class="p-4 bg-blue-50 rounded-lg text-center flex flex-col items-center justify-center">
              <p :class="selectedAdjustment.adjustment_qty >= 0 ? 'text-green-600' : 'text-red-600'" class="font-bold text-2xl">
                {{ selectedAdjustment.adjustment_qty >= 0 ? '+' : '' }}{{ selectedAdjustment.adjustment_qty }}
              </p>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg text-center">
              <p class="text-xs text-gray-600 mb-1">Sesudah</p>
              <p class="font-bold text-2xl">{{ selectedAdjustment.quantity_after }}</p>
            </div>
          </div>

          <div class="pt-4 border-t space-y-3">
            <div>
              <p class="text-sm text-gray-600">Tipe</p>
              <p class="font-bold">{{ getTypeLabel(selectedAdjustment.type) }}</p>
            </div>

            <div>
              <p class="text-sm text-gray-600">Alasan</p>
              <p class="font-bold">{{ selectedAdjustment.reason }}</p>
            </div>

            <div>
              <p class="text-sm text-gray-600">Catatan</p>
              <p class="font-bold">{{ selectedAdjustment.notes || '-' }}</p>
            </div>

            <div>
              <p class="text-sm text-gray-600">Diinput oleh</p>
              <p class="font-bold">{{ selectedAdjustment.adjusted_by?.name }}</p>
            </div>

            <div>
              <p class="text-sm text-gray-600">Tanggal</p>
              <p class="font-bold">{{ formatDatetime(selectedAdjustment.created_at) }}</p>
            </div>
          </div>
        </div>

        <button
          @click="selectedAdjustment = null"
          class="w-full mt-6 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage } from 'element-plus'

const adjustments = ref([])
const loading = ref(false)
const selectedAdjustment = ref(null)
const filterType = ref('')
const filterReason = ref('')
const startDate = ref(dayjs().startOfMonth().format('YYYY-MM-DD'))
const endDate = ref(dayjs().format('YYYY-MM-DD'))

const formatDate = (date) => {
  return dayjs(date).format('DD/MM/YYYY')
}

const formatDatetime = (date) => {
  return dayjs(date).format('DD/MM/YYYY HH:mm')
}

const getTypeLabel = (type) => {
  const labels = {
    'penambahan': '➕ Penambahan',
    'pengurangan': '➖ Pengurangan',
    'koreksi': '🔄 Koreksi',
  }
  return labels[type] || type
}

const fetchAdjustments = async () => {
  loading.value = true
  try {
    const params = {
      per_page: 100,
      start_date: startDate.value,
      end_date: endDate.value,
    }
    if (filterType.value) params.type = filterType.value
    if (filterReason.value) params.reason = filterReason.value

    const response = await api.get('stocks/adjustments', { params })
    adjustments.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat adjustment')
  } finally {
    loading.value = false
  }
}

const viewDetail = (adjustment) => {
  selectedAdjustment.value = adjustment
}

onMounted(() => {
  fetchAdjustments()
})
</script>