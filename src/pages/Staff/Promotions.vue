<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">🎉 Manajemen Promosi</h1>
      <button
        @click="openForm()"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ➕ Buat Promosi
      </button>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <div class="flex gap-4">
        <div class="flex-1">
          <label class="block text-sm font-semibold mb-2">Status</label>
          <select
            v-model="filterStatus"
            @change="fetchPromotions"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            <option value="">Semua</option>
            <option value="active">🔴 Aktif</option>
            <option value="inactive">⚪ Tidak Aktif</option>
          </select>
        </div>

        <div class="flex-1">
          <label class="block text-sm font-semibold mb-2">Cari</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nama promosi..."
            @keyup.enter="fetchPromotions"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
          />
        </div>

        <div class="flex items-end">
          <button
            @click="fetchPromotions"
            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
          >
            🔍 Cari
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat promosi...</p>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="promotion in promotions"
        :key="promotion.id"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-yellow-500"
      >
        <div class="flex justify-between items-start mb-4">
          <div>
            <p class="text-2xl font-bold text-yellow-600">{{ promotion.discount }}%</p>
            <p class="text-sm text-gray-600">Diskon</p>
          </div>
          <span :class="[
            'px-3 py-1 rounded-full text-white font-semibold text-sm',
            promotion.status === 'active' ? 'bg-green-500' : 'bg-gray-400'
          ]">
            {{ promotion.status === 'active' ? '🔴 Aktif' : '⚪ Tidak Aktif' }}
          </span>
        </div>

        <h3 class="text-lg font-bold mb-2">{{ promotion.name }}</h3>
        <p class="text-gray-600 text-sm mb-4">{{ promotion.description }}</p>

        <div class="space-y-2 text-sm mb-4 pb-4 border-b">
          <div class="flex justify-between">
            <span class="text-gray-600">Mulai:</span>
            <span class="font-semibold">{{ formatDate(promotion.start_date) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Berakhir:</span>
            <span class="font-semibold">{{ formatDate(promotion.end_date) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Min. Pembelian:</span>
            <span class="font-semibold">Rp{{ formatPrice(promotion.minimum_purchase) }}</span>
          </div>
        </div>

        <div class="flex gap-2">
          <button
            @click="editPromotion(promotion)"
            class="flex-1 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm font-semibold"
          >
            ✏️ Edit
          </button>
          <button
            @click="deletePromotion(promotion.id)"
            class="flex-1 px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition text-sm font-semibold"
          >
            🗑️ Hapus
          </button>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">{{ editingId ? '✏️ Edit Promosi' : '➕ Buat Promosi' }}</h2>
          <button
            @click="showForm = false"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <form @submit.prevent="savePromotion" class="space-y-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Nama Promosi *</label>
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

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Diskon (%) *</label>
              <input
                v-model.number="form.discount"
                type="number"
                min="0"
                max="100"
                step="0.1"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Min. Pembelian (Rp)</label>
              <input
                v-model.number="form.minimum_purchase"
                type="number"
                min="0"
                step="1000"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Tanggal Mulai *</label>
              <input
                v-model="form.start_date"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Tanggal Berakhir *</label>
              <input
                v-model="form.end_date"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Status</label>
            <select
              v-model="form.status"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            >
              <option value="active">🔴 Aktif</option>
              <option value="inactive">⚪ Tidak Aktif</option>
            </select>
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

const promotions = ref([])
const loading = ref(false)
const savingForm = ref(false)
const showForm = ref(false)
const editingId = ref(null)
const filterStatus = ref('')
const searchQuery = ref('')

const form = ref({
  name: '',
  description: '',
  discount: 0,
  minimum_purchase: 0,
  start_date: '',
  end_date: '',
  status: 'active',
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return dayjs(date).format('DD/MM/YYYY')
}

const fetchPromotions = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }
    if (filterStatus.value) params.status = filterStatus.value
    if (searchQuery.value) params.search = searchQuery.value

    const response = await api.get('promotions', { params })
    promotions.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat promosi')
  } finally {
    loading.value = false
  }
}

const openForm = (promo = null) => {
  if (promo) {
    form.value = { ...promo }
    editingId.value = promo.id
  } else {
    form.value = {
      name: '',
      description: '',
      discount: 0,
      minimum_purchase: 0,
      start_date: '',
      end_date: '',
      status: 'active',
    }
    editingId.value = null
  }
  showForm.value = true
}

const savePromotion = async () => {
  savingForm.value = true
  try {
    if (editingId.value) {
      await api.put(`promotions/${editingId.value}`, form.value)
      ElMessage.success('Promosi berhasil diperbarui')
    } else {
      await api.post('promotions', form.value)
      ElMessage.success('Promosi berhasil dibuat')
    }

    showForm.value = false
    fetchPromotions()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menyimpan promosi')
  } finally {
    savingForm.value = false
  }
}

const editPromotion = (promo) => {
  openForm(promo)
}

const deletePromotion = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin menghapus promosi ini?',
      'Warning',
      {
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.delete(`promotions/${id}`)
    ElMessage.success('Promosi berhasil dihapus')
    fetchPromotions()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal menghapus promosi')
    }
  }
}

onMounted(() => {
  fetchPromotions()
})
</script>