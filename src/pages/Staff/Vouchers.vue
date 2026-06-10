<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">🎟️ Manajemen Voucher</h1>
      <button
        @click="openForm()"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ➕ Buat Voucher
      </button>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <div class="grid grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-2">Cari Kode</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Kode voucher..."
            @keyup.enter="fetchVouchers"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Status</label>
          <select
            v-model="filterStatus"
            @change="fetchVouchers"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Status</option>
            <option value="active">🟢 Aktif</option>
            <option value="inactive">⚫ Tidak Aktif</option>
            <option value="expired">⛔ Expired</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Tipe Diskon</label>
          <select
            v-model="filterType"
            @change="fetchVouchers"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua</option>
            <option value="percentage">📊 Persen (%)</option>
            <option value="nominal">💰 Nominal (Rp)</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Periode</label>
          <input
            v-model="filterPeriod"
            type="month"
            @change="fetchVouchers"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="flex items-end">
          <button
            @click="fetchVouchers"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
          >
            🔍 Cari
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-4 gap-6">
      <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Voucher</p>
        <p class="text-3xl font-bold text-green-600">{{ stats.total_vouchers }}</p>
      </div>

      <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Aktif</p>
        <p class="text-3xl font-bold text-blue-600">{{ stats.active_vouchers }}</p>
      </div>

      <div class="bg-gradient-to-br from-orange-50 to-orange-100 border-l-4 border-orange-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Digunakan</p>
        <p class="text-3xl font-bold text-orange-600">{{ stats.total_used }}</p>
      </div>

      <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-l-4 border-purple-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Total Diskon</p>
        <p class="text-lg font-bold text-purple-600">Rp{{ formatPrice(stats.total_discount) }}</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat voucher...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="vouchers.length === 0" class="text-center py-12 bg-white rounded-lg shadow-md">
      <p class="text-4xl mb-4">🎟️</p>
      <p class="text-xl text-gray-600">Belum ada voucher</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">Kode Voucher</th>
            <th class="px-6 py-3 text-left font-semibold">Diskon</th>
            <th class="px-6 py-3 text-left font-semibold">Min. Pembelian</th>
            <th class="px-6 py-3 text-center font-semibold">Kuota</th>
            <th class="px-6 py-3 text-center font-semibold">Digunakan</th>
            <th class="px-6 py-3 text-left font-semibold">Periode</th>
            <th class="px-6 py-3 text-left font-semibold">Status</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="voucher in vouchers" :key="voucher.id" class="hover:bg-gray-50 transition">
            <!-- Kode Voucher -->
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center font-bold text-yellow-700">
                  {{ voucher.code.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="font-bold text-lg">{{ voucher.code }}</p>
                  <p class="text-xs text-gray-600">ID: {{ voucher.id }}</p>
                </div>
              </div>
            </td>

            <!-- Diskon -->
            <td class="px-6 py-4">
              <div class="font-bold text-lg">
                <span v-if="voucher.discount_type === 'percentage'" class="text-blue-600">
                  {{ voucher.discount_value }}%
                </span>
                <span v-else class="text-green-600">
                  Rp{{ formatPrice(voucher.discount_value) }}
                </span>
              </div>
              <p class="text-xs text-gray-600">
                {{ voucher.discount_type === 'percentage' ? 'Persentase' : 'Nominal' }}
              </p>
            </td>

            <!-- Min Pembelian -->
            <td class="px-6 py-4 text-sm">
              <p class="font-bold">Rp{{ formatPrice(voucher.minimum_purchase) }}</p>
            </td>

            <!-- Kuota -->
            <td class="px-6 py-4 text-center">
              <div class="font-bold text-lg">{{ voucher.quota }}</div>
              <p class="text-xs text-gray-600">kuota</p>
            </td>

            <!-- Digunakan -->
            <td class="px-6 py-4 text-center">
              <div class="font-bold" :class="voucher.usage_count > voucher.quota * 0.8 ? 'text-orange-600' : 'text-blue-600'">
                {{ voucher.usage_count }}
              </div>
              <div class="w-16 bg-gray-200 rounded-full h-2 mt-1 mx-auto">
                <div
                  class="h-2 rounded-full transition-all"
                  :class="voucher.usage_count > voucher.quota * 0.8 ? 'bg-orange-500' : 'bg-blue-500'"
                  :style="{ width: Math.min((voucher.usage_count / voucher.quota * 100), 100) + '%' }"
                ></div>
              </div>
            </td>

            <!-- Periode -->
            <td class="px-6 py-4 text-sm">
              <p class="font-semibold">{{ formatDate(voucher.start_date) }}</p>
              <p class="text-xs text-gray-600">s/d</p>
              <p class="font-semibold">{{ formatDate(voucher.end_date) }}</p>
            </td>

            <!-- Status -->
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                getStatusColor(voucherStatus(voucher))
              ]">
                {{ getStatusLabel(voucherStatus(voucher)) }}
              </span>
            </td>

            <!-- Aksi -->
            <td class="px-6 py-4 text-center">
              <div class="flex gap-2 justify-center">
                <button
                  @click="viewDetail(voucher)"
                  class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm font-semibold"
                >
                  👁️ Detail
                </button>
                <button
                  @click="openForm(voucher)"
                  class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition text-sm font-semibold"
                >
                  ✏️ Edit
                </button>
                <button
                  @click="deleteVoucher(voucher.id)"
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
    <div v-if="selectedVoucher" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">🎟️ Detail Voucher</h2>
          <button
            @click="selectedVoucher = null"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <!-- Header -->
        <div class="mb-6 pb-6 border-b">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-16 h-16 rounded-lg bg-yellow-100 flex items-center justify-center font-bold text-3xl text-yellow-700">
              {{ selectedVoucher.code.charAt(0).toUpperCase() }}
            </div>
            <div>
              <p class="text-3xl font-bold">{{ selectedVoucher.code }}</p>
              <p :class="getStatusTextColor(voucherStatus(selectedVoucher))" class="font-bold text-sm">
                {{ getStatusLabel(voucherStatus(selectedVoucher)) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Discount Info -->
        <div class="mb-6 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
          <h3 class="font-bold text-lg mb-3">💰 Informasi Diskon</h3>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <p class="text-sm text-gray-600">Tipe</p>
              <p class="font-bold text-lg">
                {{ selectedVoucher.discount_type === 'percentage' ? '📊 Persen' : '💵 Nominal' }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Nilai</p>
              <p class="font-bold text-lg" :class="selectedVoucher.discount_type === 'percentage' ? 'text-blue-600' : 'text-green-600'">
                <span v-if="selectedVoucher.discount_type === 'percentage'">{{ selectedVoucher.discount_value }}%</span>
                <span v-else>Rp{{ formatPrice(selectedVoucher.discount_value) }}</span>
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Min. Pembelian</p>
              <p class="font-bold text-lg">Rp{{ formatPrice(selectedVoucher.minimum_purchase) }}</p>
            </div>
          </div>
        </div>

        <!-- Kuota Info -->
        <div class="mb-6 p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
          <h3 class="font-bold text-lg mb-3">📦 Kuota</h3>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <p class="text-sm text-gray-600">Total Kuota</p>
              <p class="font-bold text-lg text-purple-600">{{ selectedVoucher.quota }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Sudah Digunakan</p>
              <p class="font-bold text-lg" :class="selectedVoucher.usage_count > selectedVoucher.quota * 0.8 ? 'text-orange-600' : 'text-blue-600'">
                {{ selectedVoucher.usage_count }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Sisa Kuota</p>
              <p class="font-bold text-lg text-green-600">{{ selectedVoucher.quota - selectedVoucher.usage_count }}</p>
            </div>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-3 mt-3">
            <div
              class="h-3 rounded-full transition-all"
              :class="selectedVoucher.usage_count > selectedVoucher.quota * 0.8 ? 'bg-orange-500' : 'bg-blue-500'"
              :style="{ width: Math.min((selectedVoucher.usage_count / selectedVoucher.quota * 100), 100) + '%' }"
            ></div>
          </div>
          <p class="text-xs text-gray-600 mt-1">
            {{ Math.round((selectedVoucher.usage_count / selectedVoucher.quota * 100)) }}% digunakan
          </p>
        </div>

        <!-- Periode Info -->
        <div class="mb-6 p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
          <h3 class="font-bold text-lg mb-3">📅 Periode</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600">Mulai</p>
              <p class="font-bold">{{ formatDatetime(selectedVoucher.start_date) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Berakhir</p>
              <p class="font-bold">{{ formatDatetime(selectedVoucher.end_date) }}</p>
            </div>
          </div>
          <p class="text-xs text-gray-600 mt-2">
            {{ getDaysRemaining(selectedVoucher.end_date) }} hari lagi
          </p>
        </div>

        <!-- Close Button -->
        <button
          @click="selectedVoucher = null"
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
          <h2 class="text-2xl font-bold">{{ editingId ? '✏️ Edit Voucher' : '🎟️ Buat Voucher Baru' }}</h2>
          <button
            @click="showForm = false"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <form @submit.prevent="saveVoucher" class="space-y-4">
          <!-- Kode Voucher -->
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Kode Voucher *</label>
            <input
              v-model="form.code"
              type="text"
              placeholder="PROMO2024"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 uppercase"
              @input="form.code = form.code.toUpperCase()"
              required
            />
            <p class="text-xs text-gray-600 mt-1">Gunakan huruf besar tanpa spasi</p>
          </div>

          <!-- Tipe Diskon & Nilai -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Tipe Diskon *</label>
              <select
                v-model="form.discount_type"
                @change="form.discount_value = 0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              >
                <option value="percentage">📊 Persentase (%)</option>
                <option value="nominal">💵 Nominal (Rp)</option>
              </select>
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">
                {{ form.discount_type === 'percentage' ? 'Persentase (%)' : 'Nominal (Rp)' }} *
              </label>
              <input
                v-model.number="form.discount_value"
                type="number"
                min="0"
                :max="form.discount_type === 'percentage' ? 100 : undefined"
                step="0.1"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
          </div>

          <!-- Min Pembelian -->
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Minimum Pembelian (Rp)</label>
            <input
              v-model.number="form.minimum_purchase"
              type="number"
              min="0"
              step="1000"
              placeholder="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            />
          </div>

          <!-- Kuota -->
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Kuota (Berapa kali bisa digunakan) *</label>
            <input
              v-model.number="form.quota"
              type="number"
              min="1"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              required
            />
          </div>

          <!-- Periode -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Tanggal Mulai *</label>
              <input
                v-model="form.start_date"
                type="datetime-local"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Tanggal Berakhir *</label>
              <input
                v-model="form.end_date"
                type="datetime-local"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
          </div>

          <!-- Status -->
          <div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="w-5 h-5"
              />
              <span class="font-semibold">Aktifkan Voucher</span>
            </label>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2 pt-4 border-t">
            <button
              type="submit"
              :disabled="savingForm"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition font-semibold"
            >
              {{ savingForm ? '⏳ Saving...' : editingId ? '✏️ Update' : '➕ Buat' }}
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

const vouchers = ref([])
const loading = ref(false)
const savingForm = ref(false)
const showForm = ref(false)
const editingId = ref(null)
const selectedVoucher = ref(null)
const searchQuery = ref('')
const filterStatus = ref('')
const filterType = ref('')
const filterPeriod = ref('')
const stats = ref({
  total_vouchers: 0,
  active_vouchers: 0,
  total_used: 0,
  total_discount: 0,
})

const form = ref({
  code: '',
  discount_type: 'percentage',
  discount_value: 0,
  minimum_purchase: 0,
  quota: 1,
  start_date: '',
  end_date: '',
  is_active: true,
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return dayjs(date).format('DD/MM/YYYY')
}

const formatDatetime = (date) => {
  return dayjs(date).format('DD/MM/YYYY HH:mm')
}

const getDaysRemaining = (endDate) => {
  const days = dayjs(endDate).diff(dayjs(), 'day')
  return Math.max(0, days)
}

const voucherStatus = (voucher) => {
  const today = dayjs()
  const endDate = dayjs(voucher.end_date)

  if (today.isAfter(endDate)) return 'expired'
  if (!voucher.is_active) return 'inactive'
  if (voucher.usage_count >= voucher.quota) return 'used_up'
  return 'active'
}

const getStatusColor = (status) => {
  const colors = {
    'active': 'bg-green-500',
    'inactive': 'bg-gray-400',
    'expired': 'bg-red-500',
    'used_up': 'bg-orange-500',
  }
  return colors[status] || 'bg-gray-500'
}

const getStatusTextColor = (status) => {
  const colors = {
    'active': 'text-green-600',
    'inactive': 'text-gray-600',
    'expired': 'text-red-600',
    'used_up': 'text-orange-600',
  }
  return colors[status] || 'text-gray-600'
}

const getStatusLabel = (status) => {
  const labels = {
    'active': '🟢 Aktif',
    'inactive': '⚫ Tidak Aktif',
    'expired': '⛔ Expired',
    'used_up': '🟠 Kuota Penuh',
  }
  return labels[status] || status
}

const fetchVouchers = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterStatus.value) params.status = filterStatus.value
    if (filterType.value) params.discount_type = filterType.value
    if (filterPeriod.value) {
      const [year, month] = filterPeriod.value.split('-')
      params.start_date = `${year}-${month}-01`
      params.end_date = dayjs(`${year}-${month}-01`).endOf('month').format('YYYY-MM-DD')
    }

    const response = await api.get('vouchers', { params })
    vouchers.value = response.data.data.data || []
    stats.value = response.data.stats || {}
  } catch (error) {
    ElMessage.error('Gagal memuat voucher')
  } finally {
    loading.value = false
  }
}

const openForm = (voucher = null) => {
  if (voucher) {
    form.value = {
      code: voucher.code,
      discount_type: voucher.discount_type,
      discount_value: voucher.discount_value,
      minimum_purchase: voucher.minimum_purchase,
      quota: voucher.quota,
      start_date: dayjs(voucher.start_date).format('YYYY-MM-DDTHH:mm'),
      end_date: dayjs(voucher.end_date).format('YYYY-MM-DDTHH:mm'),
      is_active: voucher.is_active,
    }
    editingId.value = voucher.id
  } else {
    form.value = {
      code: '',
      discount_type: 'percentage',
      discount_value: 0,
      minimum_purchase: 0,
      quota: 1,
      start_date: dayjs().format('YYYY-MM-DDTHH:mm'),
      end_date: dayjs().add(1, 'month').format('YYYY-MM-DDTHH:mm'),
      is_active: true,
    }
    editingId.value = null
  }
  showForm.value = true
}

const saveVoucher = async () => {
  if (!form.value.code.trim()) {
    ElMessage.warning('Kode voucher tidak boleh kosong')
    return
  }

  if (form.value.discount_value <= 0) {
    ElMessage.warning('Nilai diskon harus lebih dari 0')
    return
  }

  savingForm.value = true
  try {
    const data = {
      ...form.value,
      start_date: dayjs(form.value.start_date).toISOString(),
      end_date: dayjs(form.value.end_date).toISOString(),
    }

    if (editingId.value) {
      await api.put(`vouchers/${editingId.value}`, data)
      ElMessage.success('Voucher berhasil diperbarui')
    } else {
      await api.post('vouchers', data)
      ElMessage.success('Voucher berhasil dibuat')
    }

    showForm.value = false
    fetchVouchers()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Gagal menyimpan voucher')
  } finally {
    savingForm.value = false
  }
}

const viewDetail = (voucher) => {
  selectedVoucher.value = voucher
}

const deleteVoucher = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin menghapus voucher ini?',
      'Warning',
      {
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.delete(`vouchers/${id}`)
    ElMessage.success('Voucher berhasil dihapus')
    fetchVouchers()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal menghapus voucher')
    }
  }
}

onMounted(() => {
  fetchVouchers()
})
</script>