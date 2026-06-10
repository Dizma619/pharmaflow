<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">📦 Manajemen Rak</h1>
      <button
        @click="openForm()"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ➕ Tambah Rak
      </button>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 flex gap-4">
      <div class="flex-1">
        <label class="block text-sm font-semibold mb-2">Gudang</label>
        <select
          v-model="filterWarehouse"
          @change="fetchShelves"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
        >
          <option value="">Semua Gudang</option>
          <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
            {{ wh.name }}
          </option>
        </select>
      </div>

      <div class="flex-1">
        <label class="block text-sm font-semibold mb-2">Cari</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari rak..."
          @keyup.enter="fetchShelves"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
        />
      </div>

      <div class="flex items-end">
        <button
          @click="fetchShelves"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
        >
          🔍 Cari
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat rak...</p>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="shelf in shelves"
        :key="shelf.id"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-l-4 border-blue-500"
      >
        <div class="flex justify-between items-start mb-4">
          <div>
            <p class="text-sm text-gray-600">Kode</p>
            <p class="text-2xl font-bold text-blue-600">{{ shelf.code }}</p>
          </div>
          <div class="flex gap-2">
            <button
              @click="openForm(shelf)"
              class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-xs"
            >
              ✏️ Edit
            </button>
            <button
              @click="deleteShelf(shelf.id)"
              class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition text-xs"
            >
              🗑️
            </button>
          </div>
        </div>

        <h3 class="text-xl font-bold mb-2">{{ shelf.name }}</h3>
        <p class="text-gray-600 text-sm mb-4">{{ shelf.description }}</p>

        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">Gudang:</span>
            <span class="font-semibold">{{ shelf.warehouse?.name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Kapasitas:</span>
            <span class="font-semibold">{{ shelf.capacity || '-' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Status:</span>
            <span :class="shelf.status === 'aktif' ? 'text-green-600' : 'text-red-600'" class="font-semibold">
              {{ shelf.status === 'aktif' ? '✅ Aktif' : '❌ Tidak Aktif' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        <h2 class="text-2xl font-bold mb-6">{{ editingId ? '✏️ Edit Rak' : '➕ Tambah Rak' }}</h2>

        <form @submit.prevent="saveShelf" class="space-y-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Gudang *</label>
            <select
              v-model.number="form.warehouse_id"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              required
            >
              <option value="">Pilih Gudang</option>
              <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                {{ wh.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Kode Rak *</label>
            <input
              v-model="form.code"
              type="text"
              placeholder="A1, A2, B1, dll"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              required
            />
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Nama Rak *</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Rak Antibiotik, dll"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              required
            />
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Deskripsi</label>
            <textarea
              v-model="form.description"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 h-20"
            ></textarea>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Kapasitas</label>
            <input
              v-model.number="form.capacity"
              type="number"
              min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            />
          </div>

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
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage, ElMessageBox } from 'element-plus'

const shelves = ref([])
const warehouses = ref([])
const loading = ref(false)
const savingForm = ref(false)
const showForm = ref(false)
const editingId = ref(null)
const filterWarehouse = ref('')
const searchQuery = ref('')

const form = ref({
  warehouse_id: '',
  code: '',
  name: '',
  description: '',
  capacity: '',
})

const fetchShelves = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }
    if (filterWarehouse.value) params.warehouse_id = filterWarehouse.value
    if (searchQuery.value) params.search = searchQuery.value

    const response = await api.get('shelves', { params })
    shelves.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat rak')
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

const openForm = (shelf = null) => {
  if (shelf) {
    form.value = { ...shelf }
    editingId.value = shelf.id
  } else {
    form.value = { warehouse_id: '', code: '', name: '', description: '', capacity: '' }
    editingId.value = null
  }
  showForm.value = true
}

const saveShelf = async () => {
  savingForm.value = true
  try {
    if (editingId.value) {
      await api.put(`shelves/${editingId.value}`, form.value)
      ElMessage.success('Rak berhasil diperbarui')
    } else {
      await api.post('shelves', form.value)
      ElMessage.success('Rak berhasil ditambahkan')
    }

    showForm.value = false
    fetchShelves()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menyimpan rak')
  } finally {
    savingForm.value = false
  }
}

const deleteShelf = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin menghapus rak ini?',
      'Warning',
      {
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.delete(`shelves/${id}`)
    ElMessage.success('Rak berhasil dihapus')
    fetchShelves()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal menghapus rak')
    }
  }
}

onMounted(() => {
  fetchWarehouses()
  fetchShelves()
})
</script>