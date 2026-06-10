<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-8 px-6">
      <div class="max-w-2xl mx-auto">
        <h1 class="text-4xl font-bold">📍 Lacak Pesanan</h1>
        <p class="text-blue-100">Pantau status pesanan Anda secara real-time</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto px-6 py-8">
      <!-- Search Form -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">🔍 Cari Pesanan Anda</h2>

        <form @submit.prevent="searchOrder" class="space-y-4">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Pesanan</label>
            <input
              v-model="searchOrderNumber"
              type="text"
              placeholder="Contoh: ORD-20240115-XXXXX"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">No. WhatsApp</label>
            <input
              v-model="searchPhone"
              type="tel"
              placeholder="Contoh: 081234567890"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <button
            :disabled="searching"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition font-bold"
          >
            {{ searching ? '⏳ Mencari...' : '🔍 Cari Pesanan' }}
          </button>
        </form>
      </div>

      <!-- Order Found -->
      <div v-if="orderFound && order" class="bg-white rounded-lg shadow-md p-8 mb-8">
        <!-- Order Header -->
        <div class="mb-8 pb-8 border-b">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h2 class="text-3xl font-bold text-green-600">{{ order.order_number }}</h2>
              <p class="text-gray-600">{{ formatDate(order.created_at) }}</p>
            </div>
            <span :class="[
              'px-4 py-2 rounded-full font-bold text-white',
              getStatusColor(order.status)
            ]">
              {{ getStatusLabel(order.status) }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600">Penerima</p>
              <p class="font-bold">{{ order.customer_name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Alamat</p>
              <p class="font-bold">{{ order.delivery_city }}</p>
            </div>
          </div>
        </div>

        <!-- Status Timeline -->
        <div class="mb-8">
          <h3 class="font-bold text-lg mb-6">📅 Status Pengiriman</h3>

          <div class="space-y-4">
            <!-- Pending -->
            <div class="flex gap-4">
              <div class="flex flex-col items-center">
                <div :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center font-bold text-white',
                  ['pending', 'diproses', 'dikirim', 'selesai'].indexOf(order.status) >= 0 ? 'bg-green-500' : 'bg-gray-300'
                ]">
                  ✓
                </div>
                <div v-if="order.status !== 'selesai'" class="w-1 h-16 bg-gray-300 my-2"></div>
              </div>
              <div>
                <p class="font-bold">Pesanan Diterima</p>
                <p class="text-sm text-gray-600">{{ formatDate(order.created_at) }}</p>
              </div>
            </div>

            <!-- Processing -->
            <div class="flex gap-4">
              <div class="flex flex-col items-center">
                <div :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center font-bold text-white',
                  ['diproses', 'dikirim', 'selesai'].indexOf(order.status) >= 0 ? 'bg-green-500' : 'bg-gray-300'
                ]">
                  {{ ['diproses', 'dikirim', 'selesai'].indexOf(order.status) >= 0 ? '✓' : '2' }}
                </div>
                <div v-if="!['dikirim', 'selesai'].includes(order.status)" class="w-1 h-16 bg-gray-300 my-2"></div>
              </div>
              <div>
                <p class="font-bold">Diproses</p>
                <p class="text-sm text-gray-600">Tim kami sedang menyiapkan pesanan</p>
              </div>
            </div>

            <!-- Shipped -->
            <div class="flex gap-4">
              <div class="flex flex-col items-center">
                <div :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center font-bold text-white',
                  ['dikirim', 'selesai'].indexOf(order.status) >= 0 ? 'bg-green-500' : 'bg-gray-300'
                ]">
                  {{ ['dikirim', 'selesai'].indexOf(order.status) >= 0 ? '✓' : '3' }}
                </div>
                <div v-if="order.status !== 'selesai'" class="w-1 h-16 bg-gray-300 my-2"></div>
              </div>
              <div>
                <p class="font-bold">Dikirim</p>
                <p class="text-sm text-gray-600">
                  {{ order.shipping_method === 'standard' ? 'Estimasi 1-2 hari kerja' : order.shipping_method === 'express' ? 'Estimasi besok pagi' : 'Hari yang sama' }}
                </p>
                <p v-if="order.tracking_number" class="text-sm text-blue-600 font-bold mt-1">
                  Nomor Resi: {{ order.tracking_number }}
                </p>
              </div>
            </div>

            <!-- Completed -->
            <div class="flex gap-4">
              <div class="flex flex-col items-center">
                <div :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center font-bold text-white',
                  order.status === 'selesai' ? 'bg-green-500' : 'bg-gray-300'
                ]">
                  {{ order.status === 'selesai' ? '✓' : '4' }}
                </div>
              </div>
              <div>
                <p class="font-bold">Selesai</p>
                <p v-if="order.delivered_at" class="text-sm text-gray-600">
                  Diterima pada {{ formatDate(order.delivered_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Details -->
        <div class="mb-8 pb-8 border-t pt-8">
          <h3 class="font-bold text-lg mb-4">📦 Detail Pesanan</h3>

          <div class="space-y-2 text-sm mb-4">
            <div v-for="item in order.items" :key="item.id" class="flex justify-between py-2 border-b">
              <span>{{ item.medicine?.name }} x{{ item.quantity }}</span>
              <span class="font-bold">Rp{{ formatPrice(item.subtotal) }}</span>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-gray-600">Subtotal</span>
              <span class="font-bold">Rp{{ formatPrice(order.subtotal) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Ongkir</span>
              <span class="font-bold">Rp{{ formatPrice(order.shipping_cost) }}</span>
            </div>
            <div v-if="order.discount_amount > 0" class="flex justify-between text-red-600">
              <span>Diskon</span>
              <span class="font-bold">-Rp{{ formatPrice(order.discount_amount) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold bg-green-50 p-2 rounded">
              <span>Total</span>
              <span class="text-green-600">Rp{{ formatPrice(order.total_amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Shipping Address -->
        <div class="mb-8 pb-8 border-t pt-8">
          <h3 class="font-bold text-lg mb-4">📍 Alamat Pengiriman</h3>

          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="font-bold mb-1">{{ order.customer_name }}</p>
            <p class="font-bold mb-2">{{ order.customer_phone }}</p>
            <p class="text-gray-700">{{ order.shipping_address || order.delivery_address }}</p>
            <p class="text-gray-700">{{ order.delivery_city }}, {{ order.shipping_province }} {{ order.shipping_postal_code }}</p>
            <p v-if="order.notes" class="text-gray-600 text-sm mt-2">
              <strong>Catatan:</strong> {{ order.notes }}
            </p>
          </div>
        </div>

        <!-- Contact -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6">
          <h3 class="font-bold text-lg mb-3">💬 Ada Pertanyaan?</h3>
          <p class="text-gray-700 mb-3">Hubungi kami melalui WhatsApp atau telepon</p>

          <div class="flex gap-2">
            <a
              href="https://wa.me/6281234567890"
              target="_blank"
              class="flex-1 text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-bold"
            >
              💬 WhatsApp
            </a>
            <a
              href="tel:+6281234567890"
              class="flex-1 text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold"
            >
              📞 Telepon
            </a>
          </div>
        </div>
      </div>

      <!-- Not Found -->
      <div v-else-if="searched && !orderFound" class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6">
        <h3 class="font-bold text-lg text-red-800 mb-2">❌ Pesanan Tidak Ditemukan</h3>
        <p class="text-red-700 mb-3">
          Pastikan nomor pesanan dan nomor WhatsApp sudah benar
        </p>
        <button
          @click="reset"
          class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-bold"
        >
          🔄 Coba Lagi
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage } from 'element-plus'

const searchOrderNumber = ref('')
const searchPhone = ref('')
const searching = ref(false)
const searched = ref(false)
const orderFound = ref(false)
const order = ref(null)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return dayjs(date).format('DD MMMM YYYY HH:mm')
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

const searchOrder = async () => {
  if (!searchOrderNumber.value && !searchPhone.value) {
    ElMessage.warning('Masukkan nomor pesanan atau nomor WhatsApp')
    return
  }

  searching.value = true
  searched.value = true

  try {
    const response = await api.post('orders/track', {
      order_number: searchOrderNumber.value,
      phone: searchPhone.value,
    })

    order.value = response.data.data
    orderFound.value = true
  } catch (error) {
    orderFound.value = false
    ElMessage.error('Pesanan tidak ditemukan')
  } finally {
    searching.value = false
  }
}

const reset = () => {
  searchOrderNumber.value = ''
  searchPhone.value = ''
  searched.value = false
  orderFound.value = false
  order.value = null
}
</script>