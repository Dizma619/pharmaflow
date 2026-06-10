<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">🛒 Manajemen Pembelian</h1>
      <button
        @click="openForm"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold shadow-md"
      >
        ➕ Buat Pembelian
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Supplier</label>
        <select
          v-model="filterSupplier"
          @change="fetchPurchases"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua Supplier</option>
          <option v-for="supplier in suppliers" :key="supplier?.id" :value="supplier?.id">
            {{ supplier?.name }}
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
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold shadow"
        >
          🔍 Filter
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat data pembelian...</p>
    </div>

    <div v-else-if="!purchases || purchases.length === 0" class="bg-white rounded-lg shadow-md p-8 text-center text-gray-500">
      📦 Belum ada data transaksi pembelian.
    </div>

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
          <tr v-for="purchase in purchases" :key="purchase?.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold text-blue-600">{{ purchase?.po_number }}</td>
            <td class="px-6 py-4">{{ purchase?.supplier?.name || 'Tidak Diketahui' }}</td>
            <td class="px-6 py-4 text-center">
              <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                {{ purchase?.items_total || 0 }} item
              </span>
            </td>
            <td class="px-6 py-4 text-right font-bold text-green-600">
              Rp{{ formatPrice(purchase?.total_amount) }}
            </td>
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                purchase?.status === 'pending' ? 'bg-yellow-500' : 'bg-green-500'
              ]">
                {{ purchase?.status === 'pending' ? '⏳ Pending' : '✅ Received' }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <div class="flex gap-2 justify-center">
                <button
                  @click="viewDetail(purchase)"
                  class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm"
                >
                  👁️ Detail
                </button>
                <button
                  v-if="purchase?.status === 'pending'"
                  @click="editPurchase(purchase)"
                  class="px-3 py-1 bg-amber-500 text-white rounded hover:bg-amber-600 transition text-sm"
                >
                  ✏️ Edit
                </button>
                <button
                  v-if="purchase?.status === 'pending'"
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

    <div v-if="showFormModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 max-w-3xl w-full max-h-[85vh] flex flex-col">
        <div class="flex justify-between items-center mb-4 pb-2 border-b">
          <h2 class="text-2xl font-bold">{{ isEditMode ? '✏️ Edit Pembelian' : '➕ Buat Pembelian Baru' }}</h2>
          <button @click="showFormModal = false" class="text-gray-600 hover:text-gray-900 text-2xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4 overflow-y-auto pr-2 flex-1">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Supplier *</label>
            <select
              v-model="purchaseForm.supplier_id"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
              required
            >
              <option value="">-- Pilih Supplier --</option>
              <option v-for="supplier in suppliers" :key="supplier?.id" :value="supplier?.id">
                {{ supplier?.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
            <textarea
              v-model="purchaseForm.notes"
              rows="2"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
              placeholder="Catatan tambahan (opsional)..."
            ></textarea>
          </div>

          <div class="border-t pt-2">
            <div class="flex justify-between items-center mb-2">
              <h3 class="font-bold text-gray-800">Daftar Obat / Item</h3>
              <button
                type="button"
                @click="addFormItem"
                class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 font-semibold"
              >
                ＋ Tambah Obat
              </button>
            </div>

            <div class="space-y-3 max-h-52 overflow-y-auto p-1 bg-gray-50 rounded border">
              <div v-if="!purchaseForm.items || purchaseForm.items.length === 0" class="text-center py-4 text-sm text-gray-400">
                Belum ada item obat ditambahkan. Klik tombol di atas.
              </div>
              <div
                v-for="(item, index) in purchaseForm.items"
                :key="index"
                class="grid grid-cols-12 gap-2 bg-white p-2 border rounded shadow-sm items-end"
              >
                <div class="col-span-4">
                  <label class="block text-[11px] font-bold text-gray-600">Nama Obat *</label>
                  <select v-model="item.medicine_id" class="w-full p-1 text-sm border rounded focus:outline-none focus:ring-1" required>
                    <option value="">-- Pilih --</option>
                    <option v-for="med in medicines" :key="med?.id" :value="med?.id">
                      {{ med?.name }}
                    </option>
                  </select>
                </div>
                <div class="col-span-2">
                  <label class="block text-[11px] font-bold text-gray-600">Jumlah *</label>
                  <input type="number" v-model.number="item.quantity" min="1" class="w-full p-1 text-sm border rounded text-center focus:outline-none font-medium" required />
                </div>
                <div class="col-span-3">
                  <label class="block text-[11px] font-bold text-gray-600">Harga (Rp) *</label>
                  <input type="number" v-model.number="item.unit_price" min="0" class="w-full p-1 text-sm border rounded focus:outline-none text-right font-medium" required />
                </div>
                <div class="col-span-2">
                  <label class="block text-[11px] font-bold text-gray-600">Exp. Date</label>
                  <input type="date" v-model="item.expired_date" class="w-full p-1 text-xs border rounded focus:outline-none" />
                </div>
                <div class="col-span-1 text-center">
                  <button
                    type="button"
                    @click="removeFormItem(index)"
                    class="text-red-500 hover:text-red-700 font-bold text-lg mb-1"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-between items-center bg-gray-100 p-3 rounded-lg font-bold text-lg">
            <span>Estimasi Total:</span>
            <span class="text-green-600">Rp{{ formatPrice(calculatedFormTotal) }}</span>
          </div>

          <div class="flex gap-2 pt-2 border-t">
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
            >
              💾 Simpan Transaksi
            </button>
            <button
              type="button"
              @click="showFormModal = false"
              class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
            >
              ❌ Batal
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="selectedPurchase" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-2xl w-full max-h-[85vh] flex flex-col">
        <div class="flex justify-between items-center mb-4 pb-2 border-b">
          <h2 class="text-2xl font-bold">Detail Pembelian</h2>
          <button @click="selectedPurchase = null" class="text-gray-600 hover:text-gray-800 font-bold text-2xl">&times;</button>
        </div>

        <div class="overflow-y-auto flex-1 pr-2 space-y-4">
          <div class="grid grid-cols-2 gap-4 pb-4 border-b">
            <div>
              <p class="text-xs text-gray-500 uppercase font-bold">No. Pembelian</p>
              <p class="font-bold text-gray-800">{{ selectedPurchase?.po_number }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase font-bold">Supplier</p>
              <p class="font-bold text-gray-800">{{ selectedPurchase?.supplier?.name || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase font-bold">Tanggal</p>
              <p class="font-bold text-gray-800">{{ formatDate(selectedPurchase?.created_at) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase font-bold">Status</p>
              <p :class="selectedPurchase?.status === 'pending' ? 'text-yellow-600' : 'text-green-600'" class="font-bold">
                {{ selectedPurchase?.status === 'pending' ? '⏳ Pending' : '✅ Received' }}
              </p>
            </div>
            <div class="col-span-2" v-if="selectedPurchase?.notes">
              <p class="text-xs text-gray-500 uppercase font-bold">Catatan</p>
              <p class="text-sm text-gray-700 bg-gray-50 p-2 rounded border">{{ selectedPurchase?.notes }}</p>
            </div>
          </div>

          <div>
            <h3 class="font-bold mb-3 text-gray-800">Daftar Item Obat:</h3>
            <div class="space-y-2">
              <div v-for="item in selectedPurchase?.items" :key="item?.id" class="flex justify-between p-3 bg-gray-50 rounded border">
                <div>
                  <p class="font-semibold text-sm text-gray-900">{{ item?.medicine?.name || 'Obat Terhapus' }}</p>
                  <p class="text-xs text-gray-500">Exp: {{ item?.expired_date ? dayjs(item?.expired_date).format('DD/MM/YYYY') : '-' }}</p>
                  <p class="text-xs text-blue-600 font-medium" v-if="selectedPurchase?.status === 'received'">Diterima: {{ item?.quantity_received }} item</p>
                </div>
                <div class="text-right">
                  <p class="text-xs text-gray-600">{{ item?.quantity }} x Rp{{ formatPrice(item?.unit_price) }}</p>
                  <p class="font-bold text-green-600">Rp{{ formatPrice(item?.subtotal) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="border-t pt-4 mt-4">
          <div class="flex justify-between text-xl font-bold">
            <span>Total Transaksi</span>
            <span class="text-green-600">Rp{{ formatPrice(selectedPurchase?.total_amount) }}</span>
          </div>
          <button
            @click="selectedPurchase = null"
            class="w-full mt-4 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
          >
            Tutup Detail
          </button>
        </div>
      </div>
    </div>

    <div v-if="showReceiveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-2xl w-full max-h-[85vh] flex flex-col">
        <h2 class="text-2xl font-bold mb-4 border-b pb-2">📦 Terima & Input Stok Pembelian</h2>

        <form @submit.prevent="receivePurchase" class="space-y-4 overflow-y-auto pr-2 flex-1">
          <div>
            <label class="block text-gray-700 font-semibold mb-1 text-sm">Gudang Tujuan *</label>
            <select
              v-model.number="receiveForm.warehouse_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              required
            >
              <option value="">-- Pilih Gudang Tujuan --</option>
              <option v-for="wh in warehouses" :key="wh?.id" :value="wh?.id">
                {{ wh?.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-1 text-sm">Rak Penempatan (Opsional)</label>
            <select
              v-model.number="receiveForm.shelf_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="">-- Pilih Rak (Opsional) --</option>
              <option v-for="shelf in shelves" :key="shelf?.id" :value="shelf?.id">
                {{ shelf?.code }} - {{ shelf?.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2 text-sm">Verifikasi Jumlah Barang Diterima</label>
            <div class="space-y-2 max-h-48 overflow-y-auto p-1 bg-gray-50 border rounded">
              <div v-for="item in receivingPurchase?.items" :key="item?.id" class="flex items-center justify-between gap-4 p-2 bg-white rounded border shadow-sm">
                <div class="flex-1">
                  <p class="font-semibold text-xs text-gray-800">{{ item?.medicine?.name }}</p>
                  <p class="text-[11px] text-gray-500">Jumlah Dipesan: <span class="font-bold text-gray-700">{{ item?.quantity }}</span></p>
                </div>
                <div class="flex items-center gap-2">
                  <label class="text-[11px] font-bold text-gray-600">Diterima:</label>
                  <input
                    v-model.number="item.quantity_received"
                    type="number"
                    min="0"
                    :max="item?.quantity"
                    class="w-20 px-2 py-1 border border-gray-300 rounded text-center focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="flex gap-2 pt-2 border-t">
            <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
              ✅ Konfirmasi & Masuk Stok
            </button>
            <button type="button" @click="showReceiveModal = false" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
              ❌ Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage } from 'element-plus'

// State List Data
const purchases = ref([])
const suppliers = ref([])
const warehouses = ref([])
const shelves = ref([])
const medicines = ref([])

// State UI / Modals
const loading = ref(false)
const selectedPurchase = ref(null)
const showReceiveModal = ref(false)
const receivingPurchase = ref(null)
const showFormModal = ref(false)
const isEditMode = ref(false)
const currentEditId = ref(null)

// State Filters
const filterSupplier = ref('')
const filterStatus = ref('')
const filterDate = ref(dayjs().format('YYYY-MM'))

// State Forms Data
const purchaseForm = ref({
  supplier_id: '',
  notes: '',
  items: []
})

const receiveForm = ref({
  warehouse_id: '',
  shelf_id: '',
})

// Hitung estimasi harga
const calculatedFormTotal = computed(() => {
  if (!purchaseForm.value || !purchaseForm.value.items) return 0
  return purchaseForm.value.items.reduce((sum, item) => {
    const qty = Number(item?.quantity) || 0
    const price = Number(item?.unit_price) || 0
    return sum + (qty * price)
  }, 0)
})

// Format Helpers
const formatPrice = (price) => {
  return price ? new Intl.NumberFormat('id-ID').format(price) : '0'
}

const formatDate = (date) => {
  return date ? dayjs(date).format('DD/MM/YYYY HH:mm') : '-'
}

// Extractor Data Array otomatis menembus bungkus JSON Laravel
const extractDataArray = (response) => {
  if (!response) return []
  let target = response.data !== undefined ? response.data : response

  if (target && target.data !== undefined && Array.isArray(target.data)) {
    return target.data
  }
  if (Array.isArray(target)) {
    return target
  }
  if (target && typeof target === 'object') {
    const keys = Object.keys(target)
    for (const key of keys) {
      if (Array.isArray(target[key])) {
        return target[key]
      }
    }
  }
  return []
}

// FETCHERS (Sudah diperbaiki dengan tanda / di awal)
const fetchPurchases = async () => {
  loading.value = true
  try {
    const params = {}
    if (filterSupplier.value) params.supplier_id = filterSupplier.value
    if (filterStatus.value) params.status = filterStatus.value
    if (filterDate.value && filterDate.value.includes('-')) {
      const [year, month] = filterDate.value.split('-')
      params.start_date = `${year}-${month}-01 00:00:00`
      params.end_date = dayjs(`${year}-${month}-01`).endOf('month').format('YYYY-MM-DD 23:59:59')
    }

    const response = await api.get('/purchases', { params })
    purchases.value = extractDataArray(response)
  } catch (error) {
    ElMessage.error('Gagal memuat data pembelian')
    console.error('Error Purchases:', error)
  } finally {
    loading.value = false
  }
}

const fetchSuppliers = async () => {
  try {

    const response = await api.get('/suppliers?per_page=100')

    console.log('SUPPLIER RESPONSE', response.data)

    suppliers.value =
      response.data.data?.data ||
      response.data.data ||
      []

    console.log('SUPPLIERS', suppliers.value)

  } catch (error) {

    console.error(error)

  }
}

const fetchWarehouses = async () => {
  try {
    const response = await api.get('/warehouses?per_page=100')
    warehouses.value = extractDataArray(response)
  } catch (error) {
    console.error('Error Warehouses:', error)
  }
}

const fetchShelves = async () => {
  try {
    const response = await api.get('/shelves?per_page=100')
    shelves.value = extractDataArray(response)
  } catch (error) {
    console.error('Error Shelves:', error)
  }
}

const fetchMedicines = async () => {
  try {

    const response = await api.get('/medicines?per_page=100')

    console.log(response.data)

    medicines.value =
      response.data.data?.data ||
      response.data.data ||
      []

    console.log(medicines.value)

  } catch (error) {

    console.error(error)

  }
}

// Actions Form
const openForm = () => {
  isEditMode.value = false
  currentEditId.value = null
  purchaseForm.value = {
    supplier_id: '',
    notes: '',
    items: [
      { medicine_id: '', quantity: 1, unit_price: 0, expired_date: '' }
    ]
  }
  showFormModal.value = true
}

const editPurchase = (purchase) => {
  if (!purchase) return
  isEditMode.value = true
  currentEditId.value = purchase?.id
  
  const mappedItems = purchase?.items ? purchase.items.map(item => ({
    medicine_id: item?.medicine_id || '',
    quantity: item?.quantity || 1,
    unit_price: item?.unit_price || 0,
    expired_date: item?.expired_date ? dayjs(item.expired_date).format('YYYY-MM-DD') : ''
  })) : []

  purchaseForm.value = {
    supplier_id: purchase?.supplier_id || purchase?.supplier?.id || '',
    notes: purchase?.notes || '',
    items: mappedItems
  }
  showFormModal.value = true
}

const addFormItem = () => {
  purchaseForm.value.items.push({
    medicine_id: '',
    quantity: 1,
    unit_price: 0,
    expired_date: ''
  })
}

const removeFormItem = (index) => {
  purchaseForm.value.items.splice(index, 1)
}

const submitForm = async () => {
  if (!purchaseForm.value.items || purchaseForm.value.items.length === 0) {
    ElMessage.warning('Tambahkan minimal 1 item obat!')
    return
  }

  try {
    if (isEditMode.value) {
      await api.put(`/purchases/${currentEditId.value}`, purchaseForm.value)
      ElMessage.success('Pembelian berhasil diperbarui')
    } else {
      await api.post('/purchases', purchaseForm.value)
      ElMessage.success('Pembelian baru berhasil dibuat')
    }
    showFormModal.value = false
    fetchPurchases()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menyimpan transaksi pembelian')
  }
}

const viewDetail = (purchase) => {
  selectedPurchase.value = purchase
}

const openReceiveModal = (purchase) => {
  if (!purchase || !purchase.items) return
  purchase.items.forEach(item => {
    if (!item.quantity_received) {
      item.quantity_received = item.quantity
    }
  })
  receivingPurchase.value = purchase
  showReceiveModal.value = true
  receiveForm.value = { warehouse_id: '', shelf_id: '' }
}

const receivePurchase = async () => {
  if (!receiveForm.value.warehouse_id) {
    ElMessage.warning('Silakan pilih gudang tujuan penempatan!')
    return
  }

  try {
    const receivedItems = receivingPurchase.value.items.map(item => ({
      purchase_item_id: item.id,
      quantity_received: item.quantity_received ?? 0
    }))

    await api.post(`/purchases/${receivingPurchase.value.id}/receive`, {
      warehouse_id: receiveForm.value.warehouse_id,
      shelf_id: receiveForm.value.shelf_id || null,
      received_items: receivedItems
    })

    ElMessage.success('Pembelian berhasil diterima & stok gudang telah diperbarui!')
    showReceiveModal.value = false
    fetchPurchases()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal memproses penerimaan barang')
  }
}

onMounted(() => {
  fetchSuppliers()
  fetchWarehouses()
  fetchShelves()
  fetchMedicines()
  fetchPurchases()
})
</script>