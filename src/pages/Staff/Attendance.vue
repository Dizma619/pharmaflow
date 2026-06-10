<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">✅ Manajemen Kehadiran</h1>
      <button
        @click="markAttendanceToday"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ✏️ Absen Hari Ini
      </button>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-5 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Dari Tanggal</label>
        <input
          v-model="startDate"
          type="date"
          @change="fetchAttendance"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Sampai Tanggal</label>
        <input
          v-model="endDate"
          type="date"
          @change="fetchAttendance"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Karyawan</label>
        <select
          v-model="filterEmployee"
          @change="fetchAttendance"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua</option>
          <option v-for="emp in employees" :key="emp.id" :value="emp.id">
            {{ emp.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Status</label>
        <select
          v-model="filterStatus"
          @change="fetchAttendance"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Semua</option>
          <option value="hadir">✅ Hadir</option>
          <option value="alfa">❌ Alfa</option>
          <option value="sakit">🏥 Sakit</option>
          <option value="izin">📋 Izin</option>
        </select>
      </div>

      <div class="flex items-end">
        <button
          @click="fetchAttendance"
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
        >
          🔍 Filter
        </button>
      </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-4 gap-6">
      <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Hadir</p>
        <p class="text-3xl font-bold text-green-600">{{ summary.hadir }}</p>
      </div>

      <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Alfa</p>
        <p class="text-3xl font-bold text-red-600">{{ summary.alfa }}</p>
      </div>

      <div class="bg-gradient-to-br from-orange-50 to-orange-100 border-l-4 border-orange-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Sakit</p>
        <p class="text-3xl font-bold text-orange-600">{{ summary.sakit }}</p>
      </div>

      <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm font-semibold mb-2">Izin</p>
        <p class="text-3xl font-bold text-blue-600">{{ summary.izin }}</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat kehadiran...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
            <th class="px-6 py-3 text-left font-semibold">Karyawan</th>
            <th class="px-6 py-3 text-left font-semibold">Status</th>
            <th class="px-6 py-3 text-left font-semibold">Check In</th>
            <th class="px-6 py-3 text-left font-semibold">Check Out</th>
            <th class="px-6 py-3 text-left font-semibold">Keterangan</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="att in attendances" :key="att.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">{{ formatDate(att.attendance_date) }}</td>
            <td class="px-6 py-4 font-semibold">{{ att.employee?.name }}</td>
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded-full font-semibold text-white text-sm',
                getStatusColor(att.status)
              ]">
                {{ getStatusLabel(att.status) }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm">{{ att.check_in_time ? formatTime(att.check_in_time) : '-' }}</td>
            <td class="px-6 py-4 text-sm">{{ att.check_out_time ? formatTime(att.check_out_time) : '-' }}</td>
            <td class="px-6 py-4 text-sm">{{ att.notes || '-' }}</td>
            <td class="px-6 py-4 text-center">
              <button
                @click="editAttendance(att)"
                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm"
              >
                ✏️ Edit
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        <h2 class="text-2xl font-bold mb-6">✏️ Edit Kehadiran</h2>

        <form @submit.prevent="saveAttendance" class="space-y-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Karyawan</label>
            <input
              :value="editingAttendance.employee?.name"
              type="text"
              disabled
              class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100"
            />
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Tanggal</label>
            <input
              :value="formatDate(editingAttendance.attendance_date)"
              type="text"
              disabled
              class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100"
            />
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Status *</label>
            <select
              v-model="editingAttendance.status"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            >
              <option value="hadir">✅ Hadir</option>
              <option value="alfa">❌ Alfa</option>
              <option value="sakit">🏥 Sakit</option>
              <option value="izin">📋 Izin</option>
            </select>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Keterangan</label>
            <textarea
              v-model="editingAttendance.notes"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 h-20"
            ></textarea>
          </div>

          <div class="flex gap-2 pt-4">
            <button
              type="submit"
              :disabled="savingForm"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition font-semibold"
            >
              {{ savingForm ? '⏳ Saving...' : '✅ Simpan' }}
            </button>
            <button
              type="button"
              @click="showEditForm = false"
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
import { ElMessage } from 'element-plus'

const attendances = ref([])
const employees = ref([])
const loading = ref(false)
const savingForm = ref(false)
const showEditForm = ref(false)
const startDate = ref(dayjs().startOfMonth().format('YYYY-MM-DD'))
const endDate = ref(dayjs().format('YYYY-MM-DD'))
const filterEmployee = ref('')
const filterStatus = ref('')
const editingAttendance = ref({})
const summary = ref({
  hadir: 0,
  alfa: 0,
  sakit: 0,
  izin: 0,
})

const formatDate = (date) => {
  return dayjs(date).format('DD/MM/YYYY')
}

const formatTime = (time) => {
  return dayjs(time).format('HH:mm')
}

const getStatusColor = (status) => {
  const colors = {
    'hadir': 'bg-green-500',
    'alfa': 'bg-red-500',
    'sakit': 'bg-orange-500',
    'izin': 'bg-blue-500',
  }
  return colors[status] || 'bg-gray-500'
}

const getStatusLabel = (status) => {
  const labels = {
    'hadir': '✅ Hadir',
    'alfa': '❌ Alfa',
    'sakit': '🏥 Sakit',
    'izin': '📋 Izin',
  }
  return labels[status] || status
}

const fetchAttendance = async () => {
  loading.value = true
  try {
    const params = {
      per_page: 100,
      start_date: startDate.value,
      end_date: endDate.value,
    }
    if (filterEmployee.value) params.employee_id = filterEmployee.value
    if (filterStatus.value) params.status = filterStatus.value

    const response = await api.get('attendance', { params })
    attendances.value = response.data.data.data || []
    summary.value = response.data.summary || {}
  } catch (error) {
    ElMessage.error('Gagal memuat kehadiran')
  } finally {
    loading.value = false
  }
}

const fetchEmployees = async () => {
  try {
    const response = await api.get('employees?per_page=100')
    employees.value = response.data.data.data || []
  } catch (error) {
    console.error('Failed to fetch employees:', error)
  }
}

const editAttendance = (attendance) => {
  editingAttendance.value = { ...attendance }
  showEditForm.value = true
}

const saveAttendance = async () => {
  savingForm.value = true
  try {
    await api.post(`attendance/mark`, {
      employee_id: editingAttendance.value.employee_id,
      attendance_date: editingAttendance.value.attendance_date,
      status: editingAttendance.value.status,
      notes: editingAttendance.value.notes,
    })

    ElMessage.success('Kehadiran berhasil diperbarui')
    showEditForm.value = false
    fetchAttendance()
  } catch (error) {
    ElMessage.error('Gagal menyimpan kehadiran')
  } finally {
    savingForm.value = false
  }
}

const markAttendanceToday = () => {
  ElMessage.info('Fitur absen otomatis akan segera tersedia')
}

onMounted(() => {
  fetchEmployees()
  fetchAttendance()
})
</script>