<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">🏢 Manajemen Gudang</h1>
      <button
        @click="openForm()"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ➕ Tambah Gudang
      </button>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Cari gudang..."
        @keyup.enter="fetchWarehouses"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat gudang...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">Nama</th>
            <th class="px-6 py-3 text-left font-semibold">Lokasi</th>
            <th class="px-6 py-3 text-left font-semibold">Kapasitas</th>
            <th class="px-6 py-3 text-center font-semibold">Rak</th>
            <th class="px-6 py-3 text-center font-semibold">Status</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="warehouse in warehouses" :key="warehouse.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold">{{ warehouse.name }}</td>
            <td class="px-6 py-4">
              <p class="text-sm">{{ warehouse.address }}</p>
              <p class="text-xs text-gray-600">{{ warehouse.city }}, {{ warehouse.province }}</p>
            </td>
            <td class="px-6 py-4">{{ warehouse.capacity || '-' }}</td>
            <td class="px-6 py-4 text-center">
              <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                {{ warehouse.shelves_count || 0 }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                warehouse.status === 'aktif' ? 'bg-green-500' : 'bg-red-500'
              ]">
                {{ warehouse.status === 'aktif' ? '✅ Aktif' : '❌ Tidak Aktif' }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <div class="flex gap-2 justify-center">
                <button
                  @click="openForm(warehouse)"
                  class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm"
                >
                  ✏️ Edit
                </button>
                <button
                  @click="deleteWarehouse(warehouse.id)"
                  class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition text-sm"
                >
                  🗑️ Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">{{ editingId ? '✏️ Edit Gudang' : '➕ Tambah Gudang' }}</h2>
          <button
            @click="showForm = false"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <form @submit.prevent="saveWarehouse" class="space-y-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Nama Gudang *</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              required
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Kota *</label>
              <input
                v-model="form.city"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Provinsi *</label>
              <input
                v-model="form.province"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Alamat</label>
            <textarea
              v-model="form.address"
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

const warehouses = ref([])
const loading = ref(false)
const savingForm = ref(false)
const showForm = ref(false)
const editingId = ref(null)
const searchQuery = ref('')

const form = ref({
  name: '',
  city: '',
  province: '',
  address: '',
  capacity: '',
})

const fetchWarehouses = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }
    if (searchQuery.value) params.search = searchQuery.value

    const response = await api.get('warehouses', { params })
    warehouses.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat gudang')
  } finally {
    loading.value = false
  }
}

const openForm = (warehouse = null) => {
  if (warehouse) {
    form.value = { ...warehouse }
    editingId.value = warehouse.id
  } else {
    form.value = { name: '', city: '', province: '', address: '', capacity: '' }
    editingId.value = null
  }
  showForm.value = true
}

const saveWarehouse = async () => {
  savingForm.value = true
  try {
    if (editingId.value) {
      await api.put(`warehouses/${editingId.value}`, form.value)
      ElMessage.success('Gudang berhasil diperbarui')
    } else {
      await api.post('warehouses', form.value)
      ElMessage.success('Gudang berhasil ditambahkan')
    }

    showForm.value = false
    fetchWarehouses()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menyimpan gudang')
  } finally {
    savingForm.value = false
  }
}

const deleteWarehouse = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin menghapus gudang ini?',
      'Warning',
      {
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.delete(`warehouses/${id}`)
    ElMessage.success('Gudang berhasil dihapus')
    fetchWarehouses()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal menghapus gudang')
    }
  }
}

onMounted(() => {
  fetchWarehouses()
})
</script>