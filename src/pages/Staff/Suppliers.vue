<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">🏭 Manajemen Supplier</h1>
      <button
        @click="openForm()"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ➕ Tambah Supplier
      </button>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Cari Supplier</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Nama supplier..."
          @keyup.enter="fetchSuppliers"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Kota</label>
        <input
          v-model="filterCity"
          type="text"
          placeholder="Kota..."
          @keyup.enter="fetchSuppliers"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Status</label>
        <select
          v-model="filterStatus"
          @change="fetchSuppliers"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
        >
          <option value="">Semua</option>
          <option value="aktif">✅ Aktif</option>
          <option value="tidak_aktif">❌ Tidak Aktif</option>
        </select>
      </div>

      <div class="flex items-end">
        <button
          @click="fetchSuppliers"
          class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
        >
          🔍 Cari
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat supplier...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="suppliers.length === 0" class="text-center py-12 bg-white rounded-lg shadow-md">
      <p class="text-4xl mb-4">📭</p>
      <p class="text-xl text-gray-600">Belum ada supplier</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">Nama Supplier</th>
            <th class="px-6 py-3 text-left font-semibold">Kontak Person</th>
            <th class="px-6 py-3 text-left font-semibold">Email</th>
            <th class="px-6 py-3 text-left font-semibold">Telepon</th>
            <th class="px-6 py-3 text-left font-semibold">Lokasi</th>
            <th class="px-6 py-3 text-center font-semibold">Status</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="supplier in suppliers" :key="supplier.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold">
                  {{ supplier.name.charAt(0).toUpperCase() }}
                </div>
                <p class="font-semibold">{{ supplier.name }}</p>
              </div>
            </td>
            <td class="px-6 py-4">
              <p class="font-semibold text-sm">{{ supplier.contact_person }}</p>
              <p class="text-xs text-gray-600">👤 Contact</p>
            </td>
            <td class="px-6 py-4 text-sm">{{ supplier.email || '—' }}</td>
            <td class="px-6 py-4 text-sm font-semibold">{{ supplier.phone }}</td>
            <td class="px-6 py-4">
              <div class="text-sm">
                <p class="font-semibold">{{ supplier.city }}</p>
                <p class="text-xs text-gray-600">{{ supplier.province }}</p>
              </div>
            </td>
            <td class="px-6 py-4 text-center">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                supplier.status === 'aktif' ? 'bg-green-500' : 'bg-red-500'
              ]">
                {{ supplier.status === 'aktif' ? '✅ Aktif' : '❌ Tidak Aktif' }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <div class="flex gap-2 justify-center">
                <button
                  @click="viewDetail(supplier)"
                  class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm font-semibold"
                >
                  👁️ Detail
                </button>
                <button
                  @click="openForm(supplier)"
                  class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition text-sm font-semibold"
                >
                  ✏️ Edit
                </button>
                <button
                  @click="deleteSupplier(supplier.id)"
                  class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition text-sm font-semibold"
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
    <div v-if="selectedSupplier" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">📋 Detail Supplier</h2>
          <button
            @click="selectedSupplier = null"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <!-- Header -->
        <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b">
          <div>
            <p class="text-sm text-gray-600 font-semibold">Nama Supplier</p>
            <p class="text-lg font-bold">{{ selectedSupplier.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 font-semibold">Status</p>
            <p :class="selectedSupplier.status === 'aktif' ? 'text-green-600' : 'text-red-600'" class="text-lg font-bold">
              {{ selectedSupplier.status === 'aktif' ? '✅ Aktif' : '❌ Tidak Aktif' }}
            </p>
          </div>
        </div>

        <!-- Contact Info -->
        <div class="space-y-4 mb-6">
          <h3 class="font-bold text-lg">👤 Informasi Kontak</h3>

          <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
            <div>
              <p class="text-sm text-gray-600">Kontak Person</p>
              <p class="font-bold">{{ selectedSupplier.contact_person }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Email</p>
              <p class="font-bold">{{ selectedSupplier.email || '—' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Telepon</p>
              <p class="font-bold">{{ selectedSupplier.phone }}</p>
            </div>
          </div>
        </div>

        <!-- Address Info -->
        <div class="space-y-4 mb-6">
          <h3 class="font-bold text-lg">📍 Alamat</h3>

          <div class="p-4 bg-gray-50 rounded-lg space-y-2">
            <p class="font-bold">{{ selectedSupplier.address }}</p>
            <p class="text-sm">{{ selectedSupplier.city }}, {{ selectedSupplier.province }} {{ selectedSupplier.postal_code }}</p>
          </div>
        </div>

        <!-- Bank Info -->
        <div v-if="selectedSupplier.bank_name" class="space-y-4 mb-6">
          <h3 class="font-bold text-lg">🏦 Informasi Bank</h3>

          <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
            <div>
              <p class="text-sm text-gray-600">Bank</p>
              <p class="font-bold">{{ selectedSupplier.bank_name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">No. Rekening</p>
              <p class="font-bold">{{ selectedSupplier.bank_account }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-sm text-gray-600">Syarat Pembayaran</p>
              <p class="font-bold">{{ selectedSupplier.payment_terms }}</p>
            </div>
          </div>
        </div>

        <!-- Close Button -->
        <button
          @click="selectedSupplier = null"
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
          <h2 class="text-2xl font-bold">{{ editingId ? '✏️ Edit Supplier' : '➕ Tambah Supplier' }}</h2>
          <button
            @click="showForm = false"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <form @submit.prevent="saveSupplier" class="space-y-4">
          <!-- Name & Contact Person -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Nama Supplier *</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Kontak Person *</label>
              <input
                v-model="form.contact_person"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
          </div>

          <!-- Email & Phone -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Telepon *</label>
              <input
                v-model="form.phone"
                type="tel"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Alamat</label>
            <textarea
              v-model="form.address"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 h-20"
            ></textarea>
          </div>

          <!-- City, Province, Postal Code -->
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Kota</label>
              <input
                v-model="form.city"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Provinsi</label>
              <input
                v-model="form.province"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Kode Pos</label>
              <input
                v-model="form.postal_code"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>
          </div>

          <!-- Bank Info -->
          <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
            <h3 class="font-bold mb-3">🏦 Informasi Bank</h3>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Nama Bank</label>
                <input
                  v-model="form.bank_name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>

              <div>
                <label class="block text-gray-700 font-semibold mb-2">No. Rekening</label>
                <input
                  v-model="form.bank_account"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
            </div>

            <div class="mt-4">
              <label class="block text-gray-700 font-semibold mb-2">Syarat Pembayaran</label>
              <input
                v-model="form.payment_terms"
                type="text"
                placeholder="Contoh: 30 hari"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              />
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
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage, ElMessageBox } from 'element-plus'

const suppliers = ref([])
const loading = ref(false)
const savingForm = ref(false)
const showForm = ref(false)
const editingId = ref(null)
const selectedSupplier = ref(null)
const searchQuery = ref('')
const filterCity = ref('')
const filterStatus = ref('')

const form = ref({
  name: '',
  contact_person: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  province: '',
  postal_code: '',
  bank_name: '',
  bank_account: '',
  payment_terms: '',
  status: 'aktif',
})

const fetchSuppliers = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterCity.value) params.city = filterCity.value
    if (filterStatus.value) params.status = filterStatus.value

    const response = await api.get('suppliers', { params })
    suppliers.value = response.data.data.data || []
  } catch (error) {
    ElMessage.error('Gagal memuat supplier')
  } finally {
    loading.value = false
  }
}

const openForm = (supplier = null) => {
  if (supplier) {
    form.value = { ...supplier }
    editingId.value = supplier.id
  } else {
    form.value = {
      name: '',
      contact_person: '',
      email: '',
      phone: '',
      address: '',
      city: '',
      province: '',
      postal_code: '',
      bank_name: '',
      bank_account: '',
      payment_terms: '',
      status: 'aktif',
    }
    editingId.value = null
  }
  showForm.value = true
}

const saveSupplier = async () => {
  savingForm.value = true
  try {
    if (editingId.value) {
      await api.put(`suppliers/${editingId.value}`, form.value)
      ElMessage.success('Supplier berhasil diperbarui')
    } else {
      await api.post('suppliers', form.value)
      ElMessage.success('Supplier berhasil ditambahkan')
    }

    showForm.value = false
    fetchSuppliers()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menyimpan supplier')
  } finally {
    savingForm.value = false
  }
}

const viewDetail = (supplier) => {
  selectedSupplier.value = supplier
}

const deleteSupplier = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin menghapus supplier ini?',
      'Warning',
      {
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.delete(`suppliers/${id}`)
    ElMessage.success('Supplier berhasil dihapus')
    fetchSuppliers()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal menghapus supplier')
    }
  }
}

onMounted(() => {
  fetchSuppliers()
})
</script>