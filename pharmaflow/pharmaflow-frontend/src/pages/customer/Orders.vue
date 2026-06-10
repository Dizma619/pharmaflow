<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-8 px-6">
      <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold">📦 Pesanan Saya</h1>
        <p class="text-blue-100">Kelola dan pantau pesanan Anda di sini</p>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">
      <!-- Filter Tabs -->
      <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex gap-2 flex-wrap">
        <button
          @click="filterStatus = null"
          :class="[
            'px-4 py-2 rounded-lg font-bold transition',
            filterStatus === null
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
          ]"
        >
          📋 Semua ({{ totalOrders }})
        </button>

        <button
          @click="filterStatus = 'pending'"
          :class="[
            'px-4 py-2 rounded-lg font-bold transition',
            filterStatus === 'pending'
              ? 'bg-yellow-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
          ]"
        >
          ⏳ Menunggu ({{ statusCounts.pending }})
        </button>

        <button
          @click="filterStatus = 'diproses'"
          :class="[
            'px-4 py-2 rounded-lg font-bold transition',
            filterStatus === 'diproses'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
          ]"
        >
          ⚙️ Diproses ({{ statusCounts.diproses }})
        </button>

        <button
          @click="filterStatus = 'dikirim'"
          :class="[
            'px-4 py-2 rounded-lg font-bold transition',
            filterStatus === 'dikirim'
              ? 'bg-purple-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
          ]"
        >
          🚚 Dikirim ({{ statusCounts.dikirim }})
        </button>

        <button
          @click="filterStatus = 'selesai'"
          :class="[
            'px-4 py-2 rounded-lg font-bold transition',
            filterStatus === 'selesai'
              ? 'bg-green-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
          ]"
        >
          ✅ Selesai ({{ statusCounts.selesai }})
        </button>

        <button
          @click="filterStatus = 'dibatalkan'"
          :class="[
            'px-4 py-2 rounded-lg font-bold transition',
            filterStatus === 'dibatalkan'
              ? 'bg-red-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
          ]"
        >
          ❌ Dibatalkan ({{ statusCounts.dibatalkan }})
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <p class="text-lg text-gray-600">⏳ Memuat pesanan...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredOrders.length === 0" class="text-center py-12 bg-white rounded-lg shadow-md">
        <p class="text-4xl mb-4">📭</p>
        <p class="text-xl text-gray-600 mb-2">Belum ada pesanan</p>
        <p class="text-gray-500 mb-6">
          {{ filterStatus ? 'Tidak ada pesanan dengan status ini' : 'Mulai belanja sekarang!' }}
        </p>
        <router-link
          to="/products"
          class="inline-block px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold"
        >
          🛍️ Belanja Sekarang
        </router-link>
      </div>

      <!-- Orders List -->
      <div v-else class="space-y-4">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden"
        >
          <!-- Order Header -->
          <div class="flex items-center justify-between p-6 border-b hover:bg-gray-50 cursor-pointer" @click="toggleOrderDetail(order.id)">
            <!-- Left Info -->
            <div class="flex-1">
              <div class="flex items-center gap-4 mb-2">
                <h3 class="text-lg font-bold">{{ order.order_number }}</h3>
                <span :class="[
                  'px-3 py-1 rounded-full font-bold text-white text-sm',
                  getStatusColor(order.status)
                ]">
                  {{ getStatusLabel(order.status) }}
                </span>
              </div>

              <div class="flex gap-6 text-sm text-gray-600">
                <span>📅 {{ formatDate(order.created_at) }}</span>
                <span>📦 {{ order.items?.length || 0 }} item</span>
                <span>📍 {{ order.delivery_city }}</span>
              </div>
            </div>

            <!-- Right Info (Price) -->
            <div class="text-right mr-4">
              <p class="text-2xl font-bold text-green-600">Rp{{ formatPrice(order.total_amount) }}</p>
            </div>

            <!-- Arrow -->
            <div class="text-2xl">
              {{ expandedOrder === order.id ? '▼' : '▶' }}
            </div>
          </div>

          <!-- Order Details (Expandable) -->
          <div v-if="expandedOrder === order.id" class="bg-gray-50 p-6 space-y-4 border-t">
            <!-- Items -->
            <div>
              <h4 class="font-bold mb-3">📦 Item Pesanan</h4>
              <div class="space-y-2">
                <div
                  v-for="item in order.items"
                  :key="item.id"
                  class="flex justify-between p-2 bg-white rounded border border-gray-200"
                >
                  <span>{{ item.medicine?.name }} x{{ item.quantity }}</span>
                  <span class="font-bold">Rp{{ formatPrice(item.subtotal) }}</span>
                </div>
              </div>
            </div>

            <!-- Order Details -->
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-white p-4 rounded border border-gray-200">
                <p class="text-sm text-gray-600 font-bold">Tanggal Pemesanan</p>
                <p class="font-bold">{{ formatDatetime(order.created_at) }}</p>
              </div>

              <div class="bg-white p-4 rounded border border-gray-200">
                <p class="text-sm text-gray-600 font-bold">Metode Pengiriman</p>
                <p class="font-bold">
                  {{ order.shipping_method === 'standard' ? '📦 Standard' : order.shipping_method === 'express' ? '🚀 Express' : '⚡ Same Day' }}
                </p>
              </div>

              <div class="bg-white p-4 rounded border border-gray-200">
                <p class="text-sm text-gray-600 font-bold">Metode Pembayaran</p>
                <p class="font-bold">
                  {{ order.payment_method === 'cod' ? '💵 COD' : '💳 Transfer Bank' }}
                </p>
              </div>

              <div class="bg-white p-4 rounded border border-gray-200">
                <p class="text-sm text-gray-600 font-bold">Status Pembayaran</p>
                <p :class="[
                  'font-bold',
                  order.payment_status === 'completed' ? 'text-green-600' : 'text-orange-600'
                ]">
                  {{ order.payment_status === 'completed' ? '✅ Lunas' : '⏳ Pending' }}
                </p>
              </div>
            </div>

            <!-- Shipping Address -->
            <div class="bg-white p-4 rounded border border-gray-200">
              <p class="text-sm text-gray-600 font-bold mb-2">📍 Alamat Pengiriman</p>
              <p class="font-bold">{{ order.customer_name }}</p>
              <p class="text-sm">{{ order.customer_phone }}</p>
              <p class="text-sm">{{ order.shipping_address || order.delivery_address }}</p>
              <p class="text-sm">{{ order.delivery_city }}, {{ order.shipping_province }} {{ order.shipping_postal_code }}</p>
            </div>

            <!-- Tracking Info -->
            <div v-if="order.tracking_number" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
              <p class="text-sm text-gray-600 font-bold">🎯 Nomor Resi</p>
              <p class="font-bold text-lg">{{ order.tracking_number }}</p>
            </div>

            <!-- Notes -->
            <div v-if="order.notes" class="bg-gray-100 p-4 rounded">
              <p class="text-sm text-gray-600 font-bold">📝 Catatan</p>
              <p>{{ order.notes }}</p>
            </div>

            <!-- Order Summary -->
            <div class="bg-white p-4 rounded border border-gray-200 space-y-2">
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
              <div class="flex justify-between text-lg font-bold border-t pt-2">
                <span>Total</span>
                <span class="text-green-600">Rp{{ formatPrice(order.total_amount) }}</span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 pt-4 border-t">
              <router-link
                :to="{ name: 'ProductDetail', params: { id: order.items?.[0]?.medicine_id } }"
                class="flex-1 text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold"
              >
                🛍️ Pesan Lagi
              </router-link>

              <button
                v-if="order.status === 'pending' && order.payment_status !== 'completed'"
                @click="cancelOrder(order.id)"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-bold"
              >
                ❌ Batalkan
              </button>

              <button
                @click="contactSupport(order)"
                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-bold"
              >
                💬 Hubungi
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-8">
        <button
          v-for="page in totalPages"
          :key="page"
          @click="currentPage = page; fetchOrders()"
          :class="[
            'px-4 py-2 rounded-lg font-bold transition',
            currentPage === page
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-800 hover:bg-gray-100 border'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage, ElMessageBox } from 'element-plus'

const authStore = useAuthStore()

const orders = ref([])
const loading = ref(false)
const expandedOrder = ref(null)
const filterStatus = ref(null)
const currentPage = ref(1)
const totalPages = ref(1)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return dayjs(date).format('DD MMM YYYY')
}

const formatDatetime = (date) => {
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

// ================================
// COMPUTED
// ================================

const filteredOrders = computed(() => {
  if (!filterStatus.value) {
    return orders.value
  }
  return orders.value.filter(order => order.status === filterStatus.value)
})

const totalOrders = computed(() => orders.value.length)

const statusCounts = computed(() => {
  return {
    pending: orders.value.filter(o => o.status === 'pending').length,
    diproses: orders.value.filter(o => o.status === 'diproses').length,
    dikirim: orders.value.filter(o => o.status === 'dikirim').length,
    selesai: orders.value.filter(o => o.status === 'selesai').length,
    dibatalkan: orders.value.filter(o => o.status === 'dibatalkan').length,
  }
})

// ================================
// METHODS
// ================================

const fetchOrders = async () => {
  loading.value = true
  try {
    const response = await api.get('orders', {
      params: {
        page: currentPage.value,
        per_page: 10,
      }
    })

    orders.value = response.data.data.data || []
    totalPages.value = response.data.data.last_page || 1
  } catch (error) {
    ElMessage.error('Gagal memuat pesanan')
  } finally {
    loading.value = false
  }
}

const toggleOrderDetail = (orderId) => {
  if (expandedOrder.value === orderId) {
    expandedOrder.value = null
  } else {
    expandedOrder.value = orderId
  }
}

const cancelOrder = async (orderId) => {
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

    await api.post(`orders/${orderId}/cancel`)
    ElMessage.success('Pesanan berhasil dibatalkan')
    fetchOrders()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal membatalkan pesanan')
    }
  }
}

const contactSupport = (order) => {
  const message = `Halo, saya ingin menanyakan tentang pesanan saya ${order.order_number}`
  const whatsappUrl = `https://wa.me/6281234567890?text=${encodeURIComponent(message)}`
  window.open(whatsappUrl, '_blank')
}

onMounted(() => {
  // Check if user is authenticated
  if (!authStore.isAuthenticated) {
    ElMessage.error('Anda harus login terlebih dahulu')
    return
  }

  fetchOrders()
})
</script>