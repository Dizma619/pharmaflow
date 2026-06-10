<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">📂 Manajemen Kategori</h1>
      <button
        @click="openForm()"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ➕ Tambah Kategori
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat kategori...</p>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="category in categories"
        :key="category.id"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-blue-500"
      >
        <div class="flex justify-between items-start mb-4">
          <div class="text-4xl">📂</div>
          <div class="flex gap-2">
            <button
              @click="openForm(category)"
              class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm"
            >
              ✏️ Edit
            </button>
            <button
              @click="deleteCategory(category.id)"
              class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition text-sm"
            >
              🗑️ Hapus
            </button>
          </div>
        </div>

        <h3 class="text-xl font-bold mb-2">{{ category.name }}</h3>
        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ category.description }}</p>

        <div class="pt-4 border-t space-y-2">
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Produk:</span>
            <span class="font-bold text-blue-600">{{ category.medicines_count || 0 }}</span>
          </div>
          <div class="text-xs text-gray-500">
            Dibuat: {{ formatDate(category.created_at) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        <h2 class="text-2xl font-bold mb-6">{{ editingId ? '✏️ Edit Kategori' : '➕ Tambah Kategori' }}</h2>

        <form @submit.prevent="saveCategory" class="space-y-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Nama Kategori *</label>
            <input
              v-model="form.name"
              type="text"
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
import dayjs from 'dayjs'
import { ElMessage, ElMessageBox } from 'element-plus'

const categories = ref([])
const loading = ref(false)
const savingForm = ref(false)
const showForm = ref(false)
const editingId = ref(null)

const form = ref({
  name: '',
  description: '',
})

const formatDate = (date) => {
  return dayjs(date).format('DD/MM/YYYY')
}

const fetchCategories = async () => {
  loading.value = true
  try {
    const response = await api.get('categories?per_page=100')
    categories.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat kategori')
  } finally {
    loading.value = false
  }
}

const openForm = (category = null) => {
  if (category) {
    form.value = { ...category }
    editingId.value = category.id
  } else {
    form.value = { name: '', description: '' }
    editingId.value = null
  }
  showForm.value = true
}

const saveCategory = async () => {
  savingForm.value = true
  try {
    if (editingId.value) {
      await api.put(`categories/${editingId.value}`, form.value)
      ElMessage.success('Kategori berhasil diperbarui')
    } else {
      await api.post('categories', form.value)
      ElMessage.success('Kategori berhasil ditambahkan')
    }

    showForm.value = false
    fetchCategories()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menyimpan kategori')
  } finally {
    savingForm.value = false
  }
}

const deleteCategory = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin menghapus kategori ini?',
      'Warning',
      {
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.delete(`categories/${id}`)
    ElMessage.success('Kategori berhasil dihapus')
    fetchCategories()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal menghapus kategori')
    }
  }
}

onMounted(() => {
  fetchCategories()
})
</script>