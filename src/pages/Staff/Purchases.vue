<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">🛒 Manajemen Pembelian</h1>
      <button
        @click="openForm()"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ➕ Buat Pembelian
      </button>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Supplier</label>
        <select
          v-model="filterSupplier"
          @change="fetchPurchases"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Supplier</option>
          <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
            {{ supplier.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Status</label>
        <select
          v-model="filterStatus"
          @change="fetchPurchases"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Status</option>
          <option value="pending">⏳ Pending</option>
          <option value="received">✅ Received</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Periode</label>
        <input
          v-model="filterDate"
          type="month"
          @change="fetchPurchases"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div class="flex items-end">
        <button
          @click="fetchPurchases"
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Filter
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat pembelian...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">No. Pembelian</th>
            <th class="px-6 py-3 text-left font-semibold">Supplier</th>
            <th class="px-6 py-3 text-center font-semibold">Items</th>
            <th class="px-6 py-3 text-right font-semibold">Total</th>
            <th class="px-6 py-3 text-left font-semibold">Status</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold">{{ purchase.purchase_number }}</td>
            <td class="px-6 py-4">{{ purchase.supplier?.name }}</td>
            <td class="px-6 py-4 text-center">
              <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                {{ purchase.items_total }} item
              </span>
            </td>
            <td class="px-6 py-4 text-right font-bold text-green-600">
              Rp{{ formatPrice(purchase.total_amount) }}
            </td>
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                purchase.status === 'pending' ? 'bg-yellow-500' : 'bg-green-500'
              ]">
                {{ purchase.status === 'pending' ? '⏳ Pending' : '✅ Received' }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <div class="flex gap-2 justify-center">
                <button
                  @click="viewDetail(purchase)"
                  class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm"
                >
                  👁️ View
                </button>
                <button
                  v-if="purchase.status === 'pending'"
                  @click="editPurchase(purchase)"
                  class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition text-sm"
                >
                  ✏️ Edit
                </button>
                <button
                  v-if="purchase.status === 'pending'"
                  @click="openReceiveModal(purchase)"
                  class="px-3 py-1 bg-orange-600 text-white rounded hover:bg-orange-700 transition text-sm"
                >
                  📦 Terima
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedPurchase" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Detail Pembelian</h2>
          <button
            @click="selectedPurchase = null"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <!-- Header Info -->
        <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
          <div>
            <p class="text-sm text-gray-600">No. Pembelian</p>
            <p class="font-bold">{{ selectedPurchase.purchase_number }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Supplier</p>
            <p class="font-bold">{{ selectedPurchase.supplier?.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Tanggal</p>
            <p class="font-bold">{{ formatDate(selectedPurchase.created_at) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Status</p>
            <p :class="selectedPurchase.status === 'pending' ? 'text-yellow-600' : 'text-green-600'" class="font-bold">
              {{ selectedPurchase.status === 'pending' ? '⏳ Pending' : '✅ Received' }}
            </p>
          </div>
        </div>

        <!-- Items -->
        <div class="mb-6">
          <h3 class="font-bold mb-3">Daftar Item:</h3>
          <div class="space-y-2">
            <div v-for="item in selectedPurchase.items" :key="item.id" class="flex justify-between p-2 bg-gray-50 rounded">
              <div>
                <p class="font-semibold text-sm">{{ item.medicine.name }}</p>
                <p class="text-xs text-gray-600">Exp: {{ formatDate(item.expired_date) }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm">{{ item.quantity }} x Rp{{ formatPrice(item.unit_price) }}</p>
                <p class="font-semibold text-green-600">Rp{{ formatPrice(item.subtotal) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Total -->
        <div class="border-t pt-4">
          <div class="flex justify-between text-lg font-bold">
            <span>Total</span>
            <span class="text-green-600">Rp{{ formatPrice(selectedPurchase.total_amount) }}</span>
          </div>
        </div>

        <!-- Close Button -->
        <button
          @click="selectedPurchase = null"
          class="w-full mt-6 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
        >
          Tutup
        </button>
      </div>
    </div>

    <!-- Receive Modal -->
    <div v-if="showReceiveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <h2 class="text-2xl font-bold mb-6">📦 Terima Pembelian</h2>

        <form @submit.prevent="receivePurchase" class="space-y-4">
          <!-- Warehouse -->
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Gudang Tujuan *</label>
            <select
              v-model.number="receiveForm.warehouse_id"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            >
              <option value="">Pilih Gudang</option>
              <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                {{ wh.name }}
              </option>
            </select>
          </div>

          <!-- Shelf -->
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Rak (Opsional)</label>
            <select
              v-model.number="receiveForm.shelf_id"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Pilih Rak</option>
              <option v-for="shelf in shelves" :key="shelf.id" :value="shelf.id">
                {{ shelf.code }} - {{ shelf.name }}
              </option>
            </select>
          </div>

          <!-- Received Items -->
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Qty Diterima</label>
            <div class="space-y-2 max-h-40 overflow-y-auto">
              <div v-for="item in receivingPurchase?.items" :key="item.id" class="flex items-center gap-4 p-3 bg-gray-50 rounded">
                <div class="flex-1">
                  <p class="font-semibold text-sm">{{ item.medicine.name }}</p>
                  <p class="text-xs text-gray-600">Qty Order: {{ item.quantity }}</p>
                </div>
                <input
                  v-model.number="item.quantity_received"
                  type="number"
                  min="0"
                  :max="item.quantity"
                  class="w-20 px-2 py-1 border border-gray-300 rounded text-center focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex gap-2 pt-4">
            <button
              type="submit"
              :disabled="receivingPurchase === false"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition font-semibold"
            >
              ✅ Terima
            </button>
            <button
              type="button"
              @click="showReceiveModal = false"
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

const purchases = ref([])
const suppliers = ref([])
const warehouses = ref([])
const shelves = ref([])
const loading = ref(false)
const selectedPurchase = ref(null)
const showReceiveModal = ref(false)
const receivingPurchase = ref(null)
const filterSupplier = ref('')
const filterStatus = ref('')
const filterDate = ref(dayjs().format('YYYY-MM'))

const receiveForm = ref({
  warehouse_id: '',
  shelf_id: '',
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return dayjs(date).format('DD/MM/YYYY HH:mm')
}

const fetchPurchases = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }
    if (filterSupplier.value) params.supplier_id = filterSupplier.value
    if (filterStatus.value) params.status = filterStatus.value
    if (filterDate.value) {
      const [year, month] = filterDate.value.split('-')
      params.start_date = `${year}-${month}-01`
      params.end_date = dayjs(`${year}-${month}-01`).endOf('month').format('YYYY-MM-DD')
    }

    const response = await api.get('purchases', { params })
    purchases.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat pembelian')
  } finally {
    loading.value = false
  }
}

const fetchSuppliers = async () => {
  try {
    const response = await api.get('suppliers?per_page=100')
    suppliers.value = response.data.data.data || []
  } catch (error) {
    console.error('Failed to fetch suppliers:', error)
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

const fetchShelves = async () => {
  try {
    const response = await api.get('shelves?per_page=100')
    shelves.value = response.data.data.data || []
  } catch (error) {
    console.error('Failed to fetch shelves:', error)
  }
}

const viewDetail = (purchase) => {
  selectedPurchase.value = purchase
}

const editPurchase = (purchase) => {
  ElMessage.info('Fitur edit pembelian akan segera tersedia')
}

const openReceiveModal = (purchase) => {
  receivingPurchase.value = purchase
  showReceiveModal.value = true
  receiveForm.value = {
    warehouse_id: '',
    shelf_id: '',
  }
}

const openForm = () => {
  ElMessage.info('Fitur buat pembelian akan segera tersedia')
}

const receivePurchase = async () => {
  if (!receiveForm.value.warehouse_id) {
    ElMessage.warning('Pilih gudang tujuan')
    return
  }

  try {
    const receivedItems = receivingPurchase.value.items
      .filter(item => item.quantity_received > 0)
      .map(item => ({
        purchase_item_id: item.id,
        quantity_received: item.quantity_received
      }))

    if (receivedItems.length === 0) {
      ElMessage.warning('Masukkan minimal 1 item yang diterima')
      return
    }

    await api.post(`purchases/${receivingPurchase.value.id}/receive`, {
      warehouse_id: receiveForm.value.warehouse_id,
      shelf_id: receiveForm.value.shelf_id || null,
      received_items: receivedItems
    })

    ElMessage.success('Pembelian berhasil diterima')
    showReceiveModal.value = false
    fetchPurchases()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menerima pembelian')
  }
}

onMounted(() => {
  fetchSuppliers()
  fetchWarehouses()
  fetchShelves()
  fetchPurchases()
})
</script>