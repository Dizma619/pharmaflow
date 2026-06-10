<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">📦 Manajemen Pesanan</h1>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-5 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Status</label>
        <select
          v-model="filterStatus"
          @change="fetchOrders"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Status</option>
          <option value="pending">⏳ Pending</option>
          <option value="diproses">📦 Diproses</option>
          <option value="dikirim">🚚 Dikirim</option>
          <option value="selesai">✅ Selesai</option>
          <option value="dibatalkan">❌ Dibatalkan</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Metode Pembayaran</label>
        <select
          v-model="filterPayment"
          @change="fetchOrders"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Metode</option>
          <option value="cod">💵 COD</option>
          <option value="online_payment">💳 Online Payment</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Periode</label>
        <input
          v-model="filterDate"
          type="month"
          @change="fetchOrders"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Cari</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="No. Pesanan / Customer"
          @keyup.enter="fetchOrders"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div class="flex items-end">
        <button
          @click="fetchOrders"
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Filter
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat pesanan...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">No. Pesanan</th>
            <th class="px-6 py-3 text-left font-semibold">Customer</th>
            <th class="px-6 py-3 text-center font-semibold">Items</th>
            <th class="px-6 py-3 text-right font-semibold">Total</th>
            <th class="px-6 py-3 text-left font-semibold">Status</th>
            <th class="px-6 py-3 text-left font-semibold">Pembayaran</th>
            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold">{{ order.order_number }}</td>
            <td class="px-6 py-4">
              <p class="font-semibold text-sm">{{ order.user?.name }}</p>
              <p class="text-xs text-gray-600">{{ order.user?.email }}</p>
            </td>
            <td class="px-6 py-4 text-center">
              <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                {{ order.items.length }} item
              </span>
            </td>
            <td class="px-6 py-4 text-right font-bold text-green-600">
              Rp{{ formatPrice(order.total_amount) }}
            </td>
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                getStatusColor(order.status)
              ]">
                {{ getStatusLabel(order.status) }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded text-xs font-semibold',
                order.payment_method === 'cod' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
              ]">
                {{ order.payment_method === 'cod' ? '💵 COD' : '💳 Online' }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm">{{ formatDate(order.created_at) }}</td>
            <td class="px-6 py-4 text-center">
              <div class="flex gap-2 justify-center">
                <button
                  @click="viewDetail(order)"
                  class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm"
                >
                  👁️ View
                </button>
                <button
                  v-if="order.status === 'pending'"
                  @click="updateOrderStatus(order, 'diproses')"
                  class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition text-sm"
                >
                  📦 Proses
                </button>
                <button
                  v-if="order.status === 'diproses'"
                  @click="updateOrderStatus(order, 'dikirim')"
                  class="px-3 py-1 bg-purple-600 text-white rounded hover:bg-purple-700 transition text-sm"
                >
                  🚚 Kirim
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedOrder" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Detail Pesanan</h2>
          <button
            @click="selectedOrder = null"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <!-- Order Header -->
        <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
          <div>
            <p class="text-sm text-gray-600">No. Pesanan</p>
            <p class="font-bold">{{ selectedOrder.order_number }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Tanggal</p>
            <p class="font-bold">{{ formatDate(selectedOrder.created_at) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Customer</p>
            <p class="font-bold">{{ selectedOrder.user?.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Status</p>
            <p :class="'font-bold ' + (selectedOrder.status === 'selesai' ? 'text-green-600' : 'text-yellow-600')">
              {{ getStatusLabel(selectedOrder.status) }}
            </p>
          </div>
        </div>

        <!-- Shipping Info -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
          <h3 class="font-bold mb-2">📍 Alamat Pengiriman</h3>
          <p class="text-sm">{{ selectedOrder.delivery_address }}</p>
          <p class="text-sm">{{ selectedOrder.delivery_city }}</p>
        </div>

        <!-- Items -->
        <div class="mb-6">
          <h3 class="font-bold mb-3">📦 Items:</h3>
          <div class="space-y-2">
            <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between p-2 bg-gray-50 rounded">
              <div>
                <p class="font-semibold text-sm">{{ item.medicine.name }}</p>
                <p class="text-xs text-gray-600">{{ item.quantity }} x Rp{{ formatPrice(item.unit_price) }}</p>
              </div>
              <p class="font-semibold text-green-600">Rp{{ formatPrice(item.subtotal) }}</p>
            </div>
          </div>
        </div>

        <!-- Total -->
        <div class="border-t pt-4 mb-6">
          <div class="flex justify-between text-lg font-bold">
            <span>Total</span>
            <span class="text-green-600">Rp{{ formatPrice(selectedOrder.total_amount) }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
          <button
            v-if="selectedOrder.status === 'pending'"
            @click="confirmUpdateStatus('diproses')"
            class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition font-semibold"
          >
            📦 Proses Pesanan
          </button>
          <button
            @click="selectedOrder = null"
            class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage, ElMessageBox } from 'element-plus'

const orders = ref([])
const loading = ref(false)
const selectedOrder = ref(null)
const filterStatus = ref('')
const filterPayment = ref('')
const filterDate = ref(dayjs().format('YYYY-MM'))
const searchQuery = ref('')

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return dayjs(date).format('DD/MM/YYYY HH:mm')
}

const getStatusColor = (status) => {
  const colors = {
    'pending': 'bg-yellow-500',
    'diproses': 'bg-blue-500',
    'dikirim': 'bg-purple-500',
    'selesai': 'bg-green-500',
    'dibatalkan': 'bg-red-500',
  }
  return colors[status] || 'bg-gray-500'
}

const getStatusLabel = (status) => {
  const labels = {
    'pending': '⏳ Pending',
    'diproses': '📦 Diproses',
    'dikirim': '🚚 Dikirim',
    'selesai': '✅ Selesai',
    'dibatalkan': '❌ Dibatalkan',
  }
  return labels[status] || status
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }
    if (filterStatus.value) params.status = filterStatus.value
    if (filterPayment.value) params.payment_method = filterPayment.value
    if (filterDate.value) {
      const [year, month] = filterDate.value.split('-')
      params.start_date = `${year}-${month}-01`
      params.end_date = dayjs(`${year}-${month}-01`).endOf('month').format('YYYY-MM-DD')
    }
    if (searchQuery.value) params.search = searchQuery.value

    const response = await api.get('orders', { params })
    orders.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat pesanan')
  } finally {
    loading.value = false
  }
}

const viewDetail = (order) => {
  selectedOrder.value = order
}

const updateOrderStatus = async (order, newStatus) => {
  try {
    await ElMessageBox.confirm(
      `Ubah status pesanan menjadi ${getStatusLabel(newStatus)}?`,
      'Konfirmasi',
      {
        confirmButtonText: 'Ya, Ubah',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.put(`orders/${order.id}`, { status: newStatus })
    ElMessage.success('Status pesanan berhasil diubah')
    fetchOrders()
    selectedOrder.value = null
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal mengubah status')
    }
  }
}

const confirmUpdateStatus = (status) => {
  updateOrderStatus(selectedOrder.value, status)
}

onMounted(() => {
  fetchOrders()
})
</script>