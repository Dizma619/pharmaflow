<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">💊 Manajemen Obat</h1>
      <button
        @click="openForm()"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ➕ Tambah Obat
      </button>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <div class="grid grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-2">Cari Obat</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nama / Kode..."
            @keyup.enter="fetchMedicines"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Kategori</label>
          <select
            v-model="filterCategory"
            @change="fetchMedicines"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Supplier</label>
          <select
            v-model="filterSupplier"
            @change="fetchMedicines"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Supplier</option>
            <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">
              {{ sup.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Status Stok</label>
          <select
            v-model="filterStockStatus"
            @change="fetchMedicines"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua</option>
            <option value="tersedia">✅ Tersedia</option>
            <option value="menipis">🟡 Menipis</option>
            <option value="habis">🔴 Habis</option>
          </select>
        </div>

        <div class="flex items-end">
          <button
            @click="fetchMedicines"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
          >
            🔍 Cari
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-4 gap-6">
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Obat</p>
        <p class="text-3xl font-bold text-blue-600">{{ stats.total_medicines }}</p>
      </div>

      <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Stok Menipis</p>
        <p class="text-3xl font-bold text-red-600">{{ stats.low_stock }}</p>
      </div>

      <div class="bg-gradient-to-br from-orange-50 to-orange-100 border-l-4 border-orange-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Expired</p>
        <p class="text-3xl font-bold text-orange-600">{{ stats.expired }}</p>
      </div>

      <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Nilai</p>
        <p class="text-lg font-bold text-green-600">Rp{{ formatPrice(stats.total_value) }}</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat obat...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="medicines.length === 0" class="text-center py-12 bg-white rounded-lg shadow-md">
      <p class="text-4xl mb-4">📭</p>
      <p class="text-xl text-gray-600">Belum ada obat</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">Kode/Nama Obat</th>
            <th class="px-6 py-3 text-left font-semibold">Kategori</th>
            <th class="px-6 py-3 text-left font-semibold">Supplier</th>
            <th class="px-6 py-3 text-right font-semibold">Harga Pokok</th>
            <th class="px-6 py-3 text-right font-semibold">Harga Jual</th>
            <th class="px-6 py-3 text-center font-semibold">Stok</th>
            <th class="px-6 py-3 text-center font-semibold">Markup</th>
            <th class="px-6 py-3 text-left font-semibold">Status</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="medicine in medicines" :key="medicine.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4">
              <div>
                <p class="font-bold">{{ medicine.name }}</p>
                <p class="text-xs text-gray-600">{{ medicine.code }}</p>
              </div>
            </td>
            <td class="px-6 py-4 text-sm">
              <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                {{ medicine.category?.name }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm">{{ medicine.supplier?.name }}</td>
            <td class="px-6 py-4 text-right font-bold">Rp{{ formatPrice(medicine.base_price) }}</td>
            <td class="px-6 py-4 text-right font-bold text-green-600">Rp{{ formatPrice(medicine.selling_price) }}</td>
            <td class="px-6 py-4 text-center">
              <span :class="[
                'px-3 py-1 rounded-full font-bold text-white text-sm',
                medicine.total_stock === 0 ? 'bg-red-500' :
                medicine.total_stock <= medicine.stock_minimum ? 'bg-orange-500' :
                'bg-green-500'
              ]">
                {{ medicine.total_stock || 0 }}
              </span>
            </td>
            <td class="px-6 py-4 text-center font-bold">{{ medicine.markup_percentage || 0 }}%</td>
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                medicine.status === 'aktif' ? 'bg-green-500' : 'bg-gray-400'
              ]">
                {{ medicine.status === 'aktif' ? '✅ Aktif' : '❌ Tidak Aktif' }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <div class="flex gap-2 justify-center">
                <button
                  @click="viewDetail(medicine)"
                  class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-xs font-semibold"
                >
                  👁️ Detail
                </button>
                <button
                  @click="openForm(medicine)"
                  class="px-2 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition text-xs font-semibold"
                >
                  ✏️ Edit
                </button>
                <button
                  @click="deleteMedicine(medicine.id)"
                  class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition text-xs font-semibold"
                >
                  🗑️ Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedMedicine" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">💊 Detail Obat</h2>
          <button
            @click="selectedMedicine = null"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b">
          <div>
            <p class="text-sm text-gray-600 font-semibold">Kode Obat</p>
            <p class="text-lg font-bold">{{ selectedMedicine.code }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 font-semibold">Nama</p>
            <p class="text-lg font-bold">{{ selectedMedicine.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 font-semibold">Kategori</p>
            <p class="font-bold">{{ selectedMedicine.category?.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 font-semibold">Supplier</p>
            <p class="font-bold">{{ selectedMedicine.supplier?.name }}</p>
          </div>
        </div>

        <!-- Pricing -->
        <div class="mb-6">
          <h3 class="font-bold text-lg mb-3">💰 Harga</h3>
          <div class="grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
            <div>
              <p class="text-sm text-gray-600">Harga Pokok</p>
              <p class="font-bold text-lg">Rp{{ formatPrice(selectedMedicine.base_price) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Markup</p>
              <p class="font-bold text-lg">{{ selectedMedicine.markup_percentage }}%</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Harga Jual</p>
              <p class="font-bold text-lg text-green-600">Rp{{ formatPrice(selectedMedicine.selling_price) }}</p>
            </div>
          </div>
        </div>

        <!-- Stock Info -->
        <div class="mb-6">
          <h3 class="font-bold text-lg mb-3">📦 Stok</h3>
          <div class="grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
            <div>
              <p class="text-sm text-gray-600">Stok Min</p>
              <p class="font-bold text-lg">{{ selectedMedicine.stock_minimum }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Stok Saat Ini</p>
              <p :class="selectedMedicine.total_stock === 0 ? 'text-red-600' : selectedMedicine.total_stock <= selectedMedicine.stock_minimum ? 'text-orange-600' : 'text-green-600'" class="font-bold text-lg">
                {{ selectedMedicine.total_stock || 0 }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Stok Max</p>
              <p class="font-bold text-lg">{{ selectedMedicine.stock_maximum }}</p>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div v-if="selectedMedicine.description" class="mb-6">
          <h3 class="font-bold text-lg mb-2">📝 Deskripsi</h3>
          <p class="text-sm text-gray-700 p-4 bg-gray-50 rounded-lg">{{ selectedMedicine.description }}</p>
        </div>

        <!-- Close -->
        <button
          @click="selectedMedicine = null"
          class="w-full px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
        >
          Tutup
        </button>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">{{ editingId ? '✏️ Edit Obat' : '➕ Tambah Obat' }}</h2>
          <button
            @click="showForm = false"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <form @submit.prevent="saveMedicine" class="space-y-4">
          <!-- Basic Info -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Kode Obat *</label>
              <input
                v-model="form.code"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                placeholder="OBT001"
                required
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Nama Obat *</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Deskripsi</label>
            <textarea
              v-model="form.description"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 h-20"
            ></textarea>
          </div>

          <!-- Category & Supplier -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Kategori *</label>
              <select
                v-model.number="form.category_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              >
                <option value="">Pilih Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Supplier *</label>
              <select
                v-model.number="form.supplier_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              >
                <option value="">Pilih Supplier</option>
                <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">
                  {{ sup.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Pricing Section -->
          <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
            <h3 class="font-bold mb-3">💰 Harga</h3>

            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Harga Pokok (Rp) *</label>
                <input
                  v-model.number="form.base_price"
                  type="number"
                  min="0"
                  step="100"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                  required
                />
              </div>

              <div>
                <label class="block text-gray-700 font-semibold mb-2">Markup (%)</label>
                <input
                  v-model.number="form.markup_percentage"
                  type="number"
                  min="0"
                  max="100"
                  step="0.1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>

              <div>
                <label class="block text-gray-700 font-semibold mb-2">Harga Jual (Rp)</label>
                <input
                  :value="calculateSellingPrice"
                  type="number"
                  disabled
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 font-bold"
                />
              </div>
            </div>
          </div>

          <!-- Stock Section -->
          <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
            <h3 class="font-bold mb-3">📦 Stok</h3>

            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Stok Min</label>
                <input
                  v-model.number="form.stock_minimum"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>

              <div>
                <label class="block text-gray-700 font-semibold mb-2">Stok Max</label>
                <input
                  v-model.number="form.stock_maximum"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>

              <div>
                <label class="block text-gray-700 font-semibold mb-2">Exp Date</label>
                <input
                  v-model="form.expired_date"
                  type="date"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
            </div>
          </div>

          <!-- Status -->
          <div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.status"
                type="checkbox"
                :true-value="'aktif'"
                :false-value="'tidak_aktif'"
                class="w-5 h-5"
              />
              <span class="font-semibold">Status Aktif</span>
            </label>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2 pt-4">
            <button
              type="submit"
              :disabled="savingForm"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition font-semibold"
            >
              {{ savingForm ? '⏳ Saving...' : '✅ Simpan' }}
            </button>
            <button
              type="button"
              @click="showForm = false"
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
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage, ElMessageBox } from 'element-plus'

const medicines = ref([])
const categories = ref([])
const suppliers = ref([])
const loading = ref(false)
const savingForm = ref(false)
const showForm = ref(false)
const editingId = ref(null)
const selectedMedicine = ref(null)
const searchQuery = ref('')
const filterCategory = ref('')
const filterSupplier = ref('')
const filterStockStatus = ref('')
const stats = ref({
  total_medicines: 0,
  low_stock: 0,
  expired: 0,
  total_value: 0,
})

const form = ref({
  code: '',
  name: '',
  description: '',
  category_id: '',
  supplier_id: '',
  base_price: 0,
  markup_percentage: 25,
  unit: 'pcs',
  stock_minimum: 10,
  stock_maximum: 100,
  expired_date: '',
  status: 'aktif',
})

const calculateSellingPrice = computed(() => {
  return form.value.base_price + (form.value.base_price * form.value.markup_percentage / 100)
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const fetchMedicines = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterCategory.value) params.category_id = filterCategory.value
    if (filterSupplier.value) params.supplier_id = filterSupplier.value
    if (filterStockStatus.value) {
      if (filterStockStatus.value === 'tersedia') {
        params.stock_status = 'normal'
      } else if (filterStockStatus.value === 'menipis') {
        params.stock_status = 'low'
      } else if (filterStockStatus.value === 'habis') {
        params.stock_status = 'out'
      }
    }

    const response = await api.get('medicines', { params })
    medicines.value = response.data.data.data || []
    stats.value = response.data.stats || {}

    // Calculate total stock
    medicines.value.forEach(med => {
      med.total_stock = med.stocks?.reduce((sum, stock) => sum + stock.quantity, 0) || 0
    })
  } catch (error) {
    ElMessage.error('Gagal memuat obat')
  } finally {
    loading.value = false
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

const fetchSuppliers = async () => {
  try {
    const response = await api.get('suppliers?per_page=100')
    suppliers.value = response.data.data.data || []
  } catch (error) {
    console.error('Failed to fetch suppliers:', error)
  }
}

const openForm = (medicine = null) => {
  if (medicine) {
    form.value = { ...medicine }
    editingId.value = medicine.id
  } else {
    form.value = {
      code: '',
      name: '',
      description: '',
      category_id: '',
      supplier_id: '',
      base_price: 0,
      markup_percentage: 25,
      unit: 'pcs',
      stock_minimum: 10,
      stock_maximum: 100,
      expired_date: '',
      status: 'aktif',
    }
    editingId.value = null
  }
  showForm.value = true
}

const saveMedicine = async () => {
  savingForm.value = true
  try {
    const data = {
      ...form.value,
      selling_price: calculateSellingPrice.value,
    }

    if (editingId.value) {
      await api.put(`medicines/${editingId.value}`, data)
      ElMessage.success('Obat berhasil diperbarui')
    } else {
      await api.post('medicines', data)
      ElMessage.success('Obat berhasil ditambahkan')
    }

    showForm.value = false
    fetchMedicines()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menyimpan obat')
  } finally {
    savingForm.value = false
  }
}

const viewDetail = (medicine) => {
  selectedMedicine.value = { ...medicine }
}

const deleteMedicine = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin menghapus obat ini?',
      'Warning',
      {
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.delete(`medicines/${id}`)
    ElMessage.success('Obat berhasil dihapus')
    fetchMedicines()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal menghapus obat')
    }
  }
}

onMounted(() => {
  fetchCategories()
  fetchSuppliers()
  fetchMedicines()
})
</script>