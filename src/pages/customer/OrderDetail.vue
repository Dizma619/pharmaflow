<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-8 px-6">
      <div class="max-w-4xl mx-auto flex justify-between items-center">
        <div>
          <h1 class="text-4xl font-bold">📦 Detail Pesanan</h1>
          <p class="text-blue-100">{{ orderNumber }}</p>
        </div>
        <span :class="[
          'px-6 py-3 rounded-lg font-bold text-white text-lg',
          getStatusColor(order.status)
        ]">
          {{ getStatusLabel(order.status) }}
        </span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <p class="text-lg text-gray-600">⏳ Memuat detail pesanan...</p>
    </div>

    <!-- Main Content -->
    <div v-else class="max-w-4xl mx-auto px-6 py-8 space-y-6">
      <!-- Status Timeline -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">📅 Status Pengiriman</h2>

        <div class="space-y-4">
          <div class="flex gap-4">
            <div class="flex flex-col items-center">
              <div :class="[
                'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white',
                ['pending', 'diproses', 'dikirim', 'selesai'].indexOf(order.status) >= 0 ? 'bg-green-500' : 'bg-gray-300'
              ]">
                ✓
              </div>
              <div v-if="order.status !== 'selesai'" class="w-1 h-20 bg-gray-300 my-2"></div>
            </div>
            <div>
              <p class="font-bold text-lg">Pesanan Diterima</p>
              <p class="text-gray-600">{{ formatDate(order.created_at) }}</p>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="flex flex-col items-center">
              <div :class="[
                'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white',
                ['diproses', 'dikirim', 'selesai'].indexOf(order.status) >= 0 ? 'bg-green-500' : 'bg-gray-300'
              ]">
                {{ ['diproses', 'dikirim', 'selesai'].indexOf(order.status) >= 0 ? '✓' : '2' }}
              </div>
              <div v-if="!['dikirim', 'selesai'].includes(order.status)" class="w-1 h-20 bg-gray-300 my-2"></div>
            </div>
            <div>
              <p class="font-bold text-lg">Diproses</p>
              <p class="text-gray-600">Kami sedang menyiapkan pesanan Anda</p>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="flex flex-col items-center">
              <div :class="[
                'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white',
                ['dikirim', 'selesai'].indexOf(order.status) >= 0 ? 'bg-green-500' : 'bg-gray-300'
              ]">
                {{ ['dikirim', 'selesai'].indexOf(order.status) >= 0 ? '✓' : '3' }}
              </div>
              <div v-if="order.status !== 'selesai'" class="w-1 h-20 bg-gray-300 my-2"></div>
            </div>
            <div>
              <p class="font-bold text-lg">Dikirim</p>
              <p class="text-gray-600">
                {{ order.shipping_method === 'standard' ? '📦 1-2 hari kerja' : order.shipping_method === 'express' ? '🚀 Besok pagi' : '⚡ Hari yang sama' }}
              </p>
              <p v-if="order.tracking_number" class="text-blue-600 font-bold mt-1">
                📍 {{ order.tracking_number }}
              </p>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="flex flex-col items-center">
              <div :class="[
                'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white',
                order.status === 'selesai' ? 'bg-green-500' : 'bg-gray-300'
              ]">
                {{ order.status === 'selesai' ? '✓' : '4' }}
              </div>
            </div>
            <div>
              <p class="font-bold text-lg">Selesai</p>
              <p v-if="order.delivered_at" class="text-gray-600">
                {{ formatDate(order.delivered_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">📦 Item Pesanan</h2>

        <div class="space-y-3">
          <div
            v-for="item in order.items"
            :key="item.id"
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500"
          >
            <div>
              <p class="font-bold">{{ item.medicine?.name }}</p>
              <p class="text-sm text-gray-600">Kuantitas: {{ item.quantity }}</p>
            </div>
            <div class="text-right">
              <p class="font-bold">Rp{{ formatPrice(item.unit_price) }}/item</p>
              <p class="text-lg font-bold text-green-600">Rp{{ formatPrice(item.subtotal) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">💰 Ringkasan Biaya</h2>

        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">Subtotal</span>
            <span class="font-bold">Rp{{ formatPrice(order.subtotal) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Ongkir ({{ order.shipping_method }})</span>
            <span class="font-bold">Rp{{ formatPrice(order.shipping_cost) }}</span>
          </div>
          <div v-if="order.discount_amount > 0" class="flex justify-between text-red-600">
            <span>Diskon</span>
            <span class="font-bold">-Rp{{ formatPrice(order.discount_amount) }}</span>
          </div>
          <div class="flex justify-between text-xl font-bold bg-green-50 p-4 rounded border-2 border-green-300">
            <span>Total Pembayaran</span>
            <span class="text-green-600">Rp{{ formatPrice(order.total_amount) }}</span>
          </div>
        </div>
      </div>

      <!-- Shipping Address -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">📍 Alamat Pengiriman</h2>

        <div class="p-4 bg-gray-50 rounded-lg">
          <p class="font-bold text-lg mb-1">{{ order.customer_name }}</p>
          <p class="font-bold mb-2">{{ order.customer_phone }}</p>
          <p class="text-gray-700 mb-1">{{ order.shipping_address || order.delivery_address }}</p>
          <p class="text-gray-700">{{ order.delivery_city }}, {{ order.shipping_province }} {{ order.shipping_postal_code }}</p>
          <p v-if="order.notes" class="text-gray-600 text-sm mt-2">
            <strong>Catatan:</strong> {{ order.notes }}
          </p>
        </div>
      </div>

      <!-- Payment Info -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">💳 Informasi Pembayaran</h2>

        <div class="grid grid-cols-2 gap-4">
          <div class="p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600 font-bold">Metode Pembayaran</p>
            <p class="font-bold text-lg">
              {{ order.payment_method === 'cod' ? '💵 Bayar di Tempat' : '💳 Transfer Bank' }}
            </p>
          </div>
          <div class="p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600 font-bold">Status Pembayaran</p>
            <p :class="[
              'font-bold text-lg',
              order.payment_status === 'completed' ? 'text-green-600' : 'text-orange-600'
            ]">
              {{ order.payment_status === 'completed' ? '✅ Lunas' : '⏳ Pending' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-3">
        <router-link
          to="/orders"
          class="flex-1 text-center px-6 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-bold"
        >
          ← Kembali ke Pesanan
        </router-link>

        <button
          @click="contactSupport"
          class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-bold"
        >
          💬 Hubungi Support
        </button>

        <button
          v-if="order.status === 'pending' && order.payment_status !== 'completed'"
          @click="cancelOrder"
          class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-bold"
        >
          ❌ Batalkan Pesanan
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage, ElMessageBox } from 'element-plus'

const route = useRoute()
const router = useRouter()

const order = ref({})
const loading = ref(false)

const orderNumber = ref('')

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return dayjs(date).format('DD MMM YYYY HH:mm')
}

const getStatusColor = (status) => {
  const colors = {
    'pending': 'bg-yellow-500',
    'diproses': 'bg-blue-500',
    'dikirim': 'bg-purple-500',
    'selesai': 'bg-green-500',
    'dibatalkan': 'bg-red-500',
  }
  return colors[status] || 'bg-gray-500'
}

const getStatusLabel = (status) => {
  const labels = {
    'pending': '⏳ Menunggu',
    'diproses': '⚙️ Diproses',
    'dikirim': '🚚 Dikirim',
    'selesai': '✅ Selesai',
    'dibatalkan': '❌ Dibatalkan',
  }
  return labels[status] || status
}

const fetchOrder = async () => {
  loading.value = true
  try {
    const response = await api.get(`orders/${route.params.id}`)
    order.value = response.data.data
    orderNumber.value = order.value.order_number
  } catch (error) {
    ElMessage.error('Gagal memuat detail pesanan')
    router.push('/orders')
  } finally {
    loading.value = false
  }
}

const cancelOrder = async () => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin membatalkan pesanan ini?',
      'Konfirmasi',
      {
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.post(`orders/${order.value.id}/cancel`)
    ElMessage.success('Pesanan berhasil dibatalkan')
    fetchOrder()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal membatalkan pesanan')
    }
  }
}

const contactSupport = () => {
  const message = `Halo, saya ingin menanyakan tentang pesanan saya ${order.value.order_number}`
  const whatsappUrl = `https://wa.me/6281234567890?text=${encodeURIComponent(message)}`
  window.open(whatsappUrl, '_blank')
}

onMounted(() => {
  fetchOrder()
})
</script>