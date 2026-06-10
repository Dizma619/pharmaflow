<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">💰 Riwayat Transaksi</h1>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Status</label>
        <select
          v-model="filterStatus"
          @change="fetchTransactions"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Status</option>
          <option value="lunas">✅ Lunas</option>
          <option value="kredit">📋 Kredit</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Tanggal</label>
        <input
          v-model="filterDate"
          type="date"
          @change="fetchTransactions"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Cari</label>
        <input
          v-model="searchQuery"
          @keyup.enter="fetchTransactions"
          type="text"
          placeholder="Nomor referensi..."
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div class="flex items-end">
        <button
          @click="fetchTransactions"
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Cari
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div v-if="!loading" class="grid grid-cols-4 gap-6">
      <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Transaksi</p>
        <p class="text-3xl font-bold text-green-600">{{ summary.transaction_count }}</p>
        <p class="text-xs text-gray-600 mt-1">transaksi</p>
      </div>

      <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Penjualan</p>
        <p class="text-3xl font-bold text-blue-600">Rp{{ formatPrice(summary.total_sales) }}</p>
      </div>

      <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Diskon</p>
        <p class="text-3xl font-bold text-red-600">Rp{{ formatPrice(summary.total_discount) }}</p>
      </div>

      <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-l-4 border-purple-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Rata-rata</p>
        <p class="text-3xl font-bold text-purple-600">Rp{{ formatPrice(summary.average_transaction) }}</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat transaksi...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">No. Referensi</th>
            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
            <th class="px-6 py-3 text-left font-semibold">Kasir</th>
            <th class="px-6 py-3 text-right font-semibold">Total</th>
            <th class="px-6 py-3 text-right font-semibold">Diskon</th>
            <th class="px-6 py-3 text-left font-semibold">Status</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold">{{ transaction.reference_number }}</td>
            <td class="px-6 py-4">{{ formatDate(transaction.created_at) }}</td>
            <td class="px-6 py-4 text-sm">{{ transaction.kasir?.name }}</td>
            <td class="px-6 py-4 text-right font-bold text-green-600">Rp{{ formatPrice(transaction.final_amount) }}</td>
            <td class="px-6 py-4 text-right font-bold text-red-600">Rp{{ formatPrice(transaction.discount_amount) }}</td>
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded-full text-white font-semibold text-sm',
                transaction.payment_status === 'lunas' ? 'bg-green-500' : 'bg-yellow-500'
              ]">
                {{ transaction.payment_status === 'lunas' ? '✅ Lunas' : '📋 Kredit' }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <button
                @click="viewDetail(transaction)"
                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm font-semibold"
              >
                👁️ View
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedTransaction" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Detail Transaksi</h2>
          <button
            @click="selectedTransaction = null"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <!-- Header Info -->
        <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
          <div>
            <p class="text-sm text-gray-600">No. Referensi</p>
            <p class="font-bold">{{ selectedTransaction.reference_number }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Tanggal</p>
            <p class="font-bold">{{ formatDate(selectedTransaction.created_at) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Kasir</p>
            <p class="font-bold">{{ selectedTransaction.kasir?.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Status</p>
            <p :class="selectedTransaction.payment_status === 'lunas' ? 'text-green-600' : 'text-yellow-600'" class="font-bold">
              {{ selectedTransaction.payment_status === 'lunas' ? '✅ Lunas' : '📋 Kredit' }}
            </p>
          </div>
        </div>

        <!-- Items -->
        <div class="mb-6">
          <h3 class="font-bold mb-3">Produk:</h3>
          <div class="space-y-2">
            <div v-for="item in selectedTransaction.items" :key="item.id" class="flex justify-between">
              <span>{{ item.medicine.name }} x{{ item.quantity }}</span>
              <span>Rp{{ formatPrice(item.subtotal) }}</span>
            </div>
          </div>
        </div>

        <!-- Totals -->
        <div class="border-t pt-4 space-y-2">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span>Rp{{ formatPrice(selectedTransaction.total_amount) }}</span>
          </div>
          <div class="flex justify-between text-red-600">
            <span>Diskon</span>
            <span>-Rp{{ formatPrice(selectedTransaction.discount_amount) }}</span>
          </div>
          <div class="flex justify-between text-2xl font-bold bg-green-50 p-3 rounded">
            <span>Total</span>
            <span class="text-green-600">Rp{{ formatPrice(selectedTransaction.final_amount) }}</span>
          </div>
        </div>

        <!-- Close Button -->
        <button
          @click="selectedTransaction = null"
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

const transactions = ref([])
const loading = ref(false)
const selectedTransaction = ref(null)
const filterStatus = ref('')
const filterDate = ref('')
const searchQuery = ref('')
const summary = ref({
  transaction_count: 0,
  total_sales: 0,
  total_discount: 0,
  average_transaction: 0,
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return dayjs(date).format('DD/MM/YYYY HH:mm')
}

const fetchTransactions = async () => {
  loading.value = true
  try {
    const params = {
      per_page: 100,
      today: filterDate.value ? false : true,
    }

    if (filterStatus.value) params.payment_status = filterStatus.value
    if (filterDate.value) params.date = filterDate.value
    if (searchQuery.value) params.search = searchQuery.value

    const response = await api.get('transactions', { params })
    transactions.value = response.data.data.data || []
    summary.value = response.data.summary || {}
  } catch (error) {
    ElMessage.error('Gagal memuat transaksi')
  } finally {
    loading.value = false
  }
}

const viewDetail = (transaction) => {
  selectedTransaction.value = transaction
}

onMounted(() => {
  fetchTransactions()
})
</script>