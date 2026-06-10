<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-800 text-white py-8 px-6">
      <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold">✅ Checkout</h1>
        <p class="text-green-100">Selesaikan pesanan Anda dalam 3 langkah mudah</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <p class="text-lg text-gray-600">⏳ Memproses pesanan...</p>
    </div>

    <!-- Main Content -->
    <div v-else class="max-w-6xl mx-auto px-6 py-8">
      <!-- Empty Cart -->
      <div v-if="cartStore.items.length === 0" class="text-center py-12 bg-white rounded-lg shadow-md">
        <p class="text-4xl mb-4">📭</p>
        <p class="text-xl text-gray-600 mb-6">Keranjang Anda kosong</p>
        <router-link
          to="/products"
          class="inline-block px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold"
        >
          🛍️ Belanja Sekarang
        </router-link>
      </div>

      <!-- Checkout Form -->
      <div v-else class="grid grid-cols-3 gap-8">
        <!-- Form Section (Left - 2 cols) -->
        <div class="col-span-2 space-y-6">
          <!-- STEP 1: Customer Info -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold">
                1
              </div>
              <h2 class="text-2xl font-bold">👤 Informasi Pelanggan</h2>
            </div>

            <form class="space-y-4" @submit.prevent="validateStep1">
              <!-- Name -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap *</label>
                <input
                  v-model="cartStore.guestInfo.customer_name"
                  type="text"
                  placeholder="Contoh: Budi Santoso"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                  required
                />
              </div>

              <!-- Phone -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">No. WhatsApp *</label>
                <input
                  v-model="cartStore.guestInfo.phone"
                  type="tel"
                  placeholder="Contoh: 081234567890 atau +6281234567890"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                  required
                />
                <p class="text-xs text-gray-600 mt-1">Format: gunakan 0 atau +62 di awal nomor</p>
              </div>

              <!-- Email (Optional, for authenticated users) -->
              <div v-if="authStore.isAuthenticated">
                <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                <input
                  :value="authStore.user?.email"
                  type="email"
                  disabled
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100"
                />
              </div>
            </form>
          </div>

          <!-- STEP 2: Shipping Address -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold">
                2
              </div>
              <h2 class="text-2xl font-bold">📍 Alamat Pengiriman</h2>
            </div>

            <form class="space-y-4" @submit.prevent="validateStep2">
              <!-- Address -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Alamat Lengkap *</label>
                <textarea
                  v-model="cartStore.guestInfo.address"
                  placeholder="Contoh: Jl. Merdeka No. 123, Blok A, RT 04/RW 05"
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                  required
                ></textarea>
                <p class="text-xs text-gray-600 mt-1">Minimal 10 karakter (termasuk nama jalan, nomor rumah, RT/RW)</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- City -->
                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Kota/Kabupaten *</label>
                  <input
                    v-model="cartStore.guestInfo.city"
                    type="text"
                    placeholder="Contoh: Jakarta Selatan"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    required
                  />
                </div>

                <!-- Province -->
                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Provinsi *</label>
                  <input
                    v-model="cartStore.guestInfo.province"
                    type="text"
                    placeholder="Contoh: DKI Jakarta"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    required
                  />
                </div>
              </div>

              <!-- Postal Code -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Kode Pos *</label>
                <input
                  v-model="cartStore.guestInfo.postal_code"
                  type="text"
                  placeholder="Contoh: 12210"
                  maxlength="5"
                  pattern="[0-9]{5}"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                  required
                />
                <p class="text-xs text-gray-600 mt-1">Kode pos harus 5 digit angka</p>
              </div>

              <!-- Notes (Optional) -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Pengiriman (Opsional)</label>
                <textarea
                  v-model="cartStore.guestInfo.notes"
                  placeholder="Contoh: Rumah di sebelah toko kelontong, pintu kuning"
                  rows="2"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                ></textarea>
              </div>
            </form>
          </div>

          <!-- STEP 3: Shipping & Payment -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold">
                3
              </div>
              <h2 class="text-2xl font-bold">🚚 Pengiriman & Pembayaran</h2>
            </div>

            <form class="space-y-6" @submit.prevent="submitOrder">
              <!-- Shipping Method -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-3">Metode Pengiriman *</label>
                <div class="space-y-2">
                  <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition" :class="cartStore.guestInfo.shipping_method === 'standard' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400'">
                    <input
                      v-model="cartStore.guestInfo.shipping_method"
                      type="radio"
                      value="standard"
                      class="w-4 h-4"
                    />
                    <span class="ml-3 flex-1">
                      <span class="font-bold">📦 Standard (1-2 hari kerja)</span>
                      <span class="text-gray-600 text-sm">Rp{{ formatPrice(10000) }}</span>
                    </span>
                  </label>

                  <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition" :class="cartStore.guestInfo.shipping_method === 'express' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400'">
                    <input
                      v-model="cartStore.guestInfo.shipping_method"
                      type="radio"
                      value="express"
                      class="w-4 h-4"
                    />
                    <span class="ml-3 flex-1">
                      <span class="font-bold">🚀 Express (Besok pagi)</span>
                      <span class="text-gray-600 text-sm">Rp{{ formatPrice(25000) }}</span>
                    </span>
                  </label>

                  <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition" :class="cartStore.guestInfo.shipping_method === 'same_day' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400'">
                    <input
                      v-model="cartStore.guestInfo.shipping_method"
                      type="radio"
                      value="same_day"
                      class="w-4 h-4"
                    />
                    <span class="ml-3 flex-1">
                      <span class="font-bold">⚡ Same Day (Hari yang sama)</span>
                      <span class="text-gray-600 text-sm">Rp{{ formatPrice(50000) }}</span>
                    </span>
                  </label>
                </div>
              </div>

              <!-- Payment Method -->
              <div class="border-t pt-6">
                <label class="block text-sm font-bold text-gray-700 mb-3">Metode Pembayaran *</label>
                <div class="space-y-2">
                  <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition" :class="cartStore.guestInfo.payment_method === 'cod' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'">
                    <input
                      v-model="cartStore.guestInfo.payment_method"
                      type="radio"
                      value="cod"
                      class="w-4 h-4"
                    />
                    <span class="ml-3 flex-1">
                      <span class="font-bold">💵 Bayar di Tempat (COD)</span>
                      <span class="text-gray-600 text-sm">Bayar saat paket sampai</span>
                    </span>
                  </label>

                  <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition" :class="cartStore.guestInfo.payment_method === 'midtrans' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'">
                    <input
                      v-model="cartStore.guestInfo.payment_method"
                      type="radio"
                      value="midtrans"
                      class="w-4 h-4"
                    />
                    <span class="ml-3 flex-1">
                      <span class="font-bold">💳 Transfer Bank / Kartu Kredit</span>
                      <span class="text-gray-600 text-sm">Bayar melalui Midtrans (lebih aman)</span>
                    </span>
                  </label>
                </div>
              </div>

              <!-- Terms & Conditions -->
              <div class="border-t pt-6">
                <label class="flex items-start gap-3 cursor-pointer">
                  <input
                    v-model="agreeTerms"
                    type="checkbox"
                    class="w-5 h-5 mt-1"
                  />
                  <span class="text-sm text-gray-700">
                    Saya setuju dengan
                    <a href="#" class="text-blue-600 hover:underline">syarat dan ketentuan</a>
                    serta
                    <a href="#" class="text-blue-600 hover:underline">kebijakan privasi</a>
                  </span>
                </label>
              </div>

              <!-- Submit Button -->
              <button
                :disabled="!agreeTerms || loading"
                class="w-full px-6 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition font-bold text-lg"
              >
                {{ loading ? '⏳ Memproses...' : '✅ Pesan Sekarang' }}
              </button>
            </form>
          </div>
        </div>

        <!-- Order Summary (Right - 1 col) -->
        <div class="col-span-1">
          <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
            <!-- Header -->
            <h2 class="text-2xl font-bold mb-6">📦 Ringkasan Pesanan</h2>

            <!-- Items -->
            <div class="space-y-3 mb-4 pb-4 border-b max-h-48 overflow-y-auto">
              <div
                v-for="item in cartStore.items"
                :key="item.id"
                class="flex justify-between text-sm"
              >
                <span class="text-gray-600">{{ item.name }} x{{ item.quantity }}</span>
                <span class="font-bold">Rp{{ formatPrice(item.price * item.quantity) }}</span>
              </div>
            </div>

            <!-- Totals -->
            <div class="space-y-2 text-sm mb-4">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-bold">Rp{{ formatPrice(cartStore.subtotal) }}</span>
              </div>

              <div class="flex justify-between">
                <span class="text-gray-600">Ongkir</span>
                <span class="font-bold">Rp{{ formatPrice(cartStore.shippingCost) }}</span>
              </div>

              <div v-if="cartStore.discountAmount > 0" class="flex justify-between text-red-600">
                <span>Diskon</span>
                <span class="font-bold">-Rp{{ formatPrice(cartStore.discountAmount) }}</span>
              </div>
            </div>

            <!-- Grand Total -->
            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border-2 border-green-300">
              <span class="font-bold text-lg">Total</span>
              <span class="text-2xl font-bold text-green-600">Rp{{ formatPrice(cartStore.total) }}</span>
            </div>

            <!-- Info Box -->
            <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
              <p class="text-xs text-gray-700 mb-2">
                ✨ Pesanan Anda akan diproses dalam 1-2 jam setelah pembayaran diterima.
              </p>
              <p class="text-xs text-gray-700">
                📞 Tim kami siap membantu melalui WhatsApp jika ada pertanyaan.
              </p>
            </div>

            <!-- Back to Cart -->
            <router-link
              to="/cart"
              class="block text-center text-blue-600 hover:text-blue-800 text-sm font-bold mt-4"
            >
              ← Kembali ke Keranjang
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { ElMessage, ElMessageBox } from 'element-plus'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

const loading = ref(false)
const agreeTerms = ref(false)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

/**
 * Validate Step 1: Customer Info
 */
const validateStep1 = () => {
  const { customer_name, phone } = cartStore.guestInfo

  if (!customer_name || customer_name.trim() === '') {
    ElMessage.warning('Nama lengkap wajib diisi')
    return false
  }

  const phoneRegex = /^(\+62|0)[0-9]{9,12}$/
  if (!phoneRegex.test(phone)) {
    ElMessage.warning('Format nomor WhatsApp tidak valid (gunakan 0 atau +62)')
    return false
  }

  return true
}

/**
 * Validate Step 2: Shipping Address
 */
const validateStep2 = () => {
  const { address, city, province, postal_code } = cartStore.guestInfo

  if (!address || address.trim().length < 10) {
    ElMessage.warning('Alamat harus diisi minimal 10 karakter')
    return false
  }

  if (!city || city.trim() === '') {
    ElMessage.warning('Kota/Kabupaten wajib diisi')
    return false
  }

  if (!province || province.trim() === '') {
    ElMessage.warning('Provinsi wajib diisi')
    return false
  }

  const postalRegex = /^[0-9]{5}$/
  if (!postalRegex.test(postal_code)) {
    ElMessage.warning('Kode pos harus 5 digit angka')
    return false
  }

  return true
}

/**
 * Submit Order
 */
const submitOrder = async () => {
  // Validate all steps
  if (!validateStep1()) return
  if (!validateStep2()) return
  if (!agreeTerms.value) {
    ElMessage.warning('Anda harus menyetujui syarat dan ketentuan')
    return
  }

  if (cartStore.items.length === 0) {
    ElMessage.warning('Keranjang Anda kosong')
    return
  }

  loading.value = true

  try {
    // Get order data
    const orderData = cartStore.getOrderData()

    // Create order via API
    const response = await api.post('orders', orderData)

    const order = response.data.data
    const orderNumber = response.data.order_number

    // Save order info to localStorage untuk tracking
    localStorage.setItem('last_order', JSON.stringify({
      order_id: order.id,
      order_number: orderNumber,
      customer_phone: cartStore.guestInfo.phone,
      total_amount: cartStore.total,
    }))

    // Reset cart
    cartStore.resetAfterOrder()

    // Redirect ke payment atau success page
    if (cartStore.guestInfo.payment_method === 'cod') {
      // COD - langsung ke success
      await api.post('payments/cod/confirm', {
        order_id: order.id,
      })

      router.push({
        name: 'OrderSuccess',
        query: {
          order_number: orderNumber,
          phone: cartStore.guestInfo.phone,
        },
      })
    } else {
      // Midtrans payment
      const paymentResponse = await api.post('payments/snap-token', {
        order_id: order.id,
      })

      // Redirect ke Midtrans Snap
      if (paymentResponse.data.snap_token) {
        window.snap.pay(paymentResponse.data.snap_token, {
          onSuccess: function () {
            router.push({
              name: 'OrderSuccess',
              query: {
                order_number: orderNumber,
                phone: cartStore.guestInfo.phone,
              },
            })
          },
          onPending: function () {
            router.push({
              name: 'OrderTracking',
              query: {
                order_number: orderNumber,
                phone: cartStore.guestInfo.phone,
              },
            })
          },
          onError: function () {
            ElMessage.error('Pembayaran gagal, silahkan coba lagi')
            router.push('/checkout')
          },
        })
      }
    }
  } catch (error) {
    console.error('Order error:', error)
    ElMessage.error(error.response?.data?.message || 'Gagal membuat pesanan')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Load cart from localStorage
  cartStore.loadFromLocalStorage()

  // Load Midtrans script
  if (!window.snap) {
    const script = document.createElement('script')
    script.src = 'https://app.midtrans.com/snap/snap.js'
    script.setAttribute('data-client-key', import.meta.env.VITE_MIDTRANS_CLIENT_KEY)
    document.body.appendChild(script)
  }
})
</script>