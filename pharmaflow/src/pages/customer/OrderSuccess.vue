<template>
  <div class="min-h-screen bg-gradient-to-b from-green-50 to-green-100">
    <!-- Success Section -->
    <div class="max-w-2xl mx-auto px-6 py-16">
      <!-- Success Icon -->
      <div class="text-center mb-8">
        <div class="inline-block">
          <div class="w-24 h-24 rounded-full bg-green-500 flex items-center justify-center text-6xl animate-bounce">
            ✅
          </div>
        </div>
      </div>

      <!-- Success Message -->
      <div class="bg-white rounded-lg shadow-lg p-12 text-center mb-8">
        <h1 class="text-4xl font-bold text-green-600 mb-3">Pesanan Berhasil!</h1>
        <p class="text-xl text-gray-600 mb-2">Terima kasih telah berbelanja di FharmaFlow</p>
        <p class="text-gray-600">Pesanan Anda akan segera diproses oleh tim kami</p>
      </div>

      <!-- Order Details Card -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6">📦 Detail Pesanan</h2>

        <div class="space-y-4 pb-6 border-b">
          <div class="flex justify-between">
            <span class="text-gray-600">Nomor Pesanan</span>
            <span class="font-bold text-lg text-green-600">{{ orderNumber }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-600">Nama Penerima</span>
            <span class="font-bold">{{ customerName }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-600">No. WhatsApp</span>
            <span class="font-bold">{{ customerPhone }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-600">Total Pembayaran</span>
            <span class="font-bold text-lg text-green-600">Rp{{ formatPrice(totalAmount) }}</span>
          </div>
        </div>

        <!-- Status Timeline -->
        <div class="mt-6">
          <h3 class="font-bold text-lg mb-4">📅 Status Pesanan</h3>

          <div class="space-y-4">
            <div class="flex gap-4">
              <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold">
                  ✓
                </div>
                <div class="w-1 h-16 bg-gray-300 my-2"></div>
              </div>
              <div>
                <p class="font-bold">Pesanan Diterima</p>
                <p class="text-sm text-gray-600">Pesanan Anda sudah kami terima</p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                  2
                </div>
                <div class="w-1 h-16 bg-gray-300 my-2"></div>
              </div>
              <div>
                <p class="font-bold text-gray-600">Diproses</p>
                <p class="text-sm text-gray-600">Tim kami sedang menyiapkan pesanan Anda</p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                  3
                </div>
                <div class="w-1 h-16 bg-gray-300 my-2"></div>
              </div>
              <div>
                <p class="font-bold text-gray-600">Dikirim</p>
                <p class="text-sm text-gray-600">Paket dalam perjalanan ke tangan Anda</p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                  4
                </div>
              </div>
              <div>
                <p class="font-bold text-gray-600">Selesai</p>
                <p class="text-sm text-gray-600">Pesanan telah diterima dan selesai</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Next Steps -->
      <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-8">
        <h3 class="font-bold text-lg mb-3">📝 Langkah Selanjutnya</h3>

        <ul class="space-y-2 text-gray-700">
          <li class="flex gap-2">
            <span class="text-blue-600 font-bold">1.</span>
            <span>Kami akan menghubungi Anda melalui WhatsApp <strong>{{ customerPhone }}</strong> untuk konfirmasi</span>
          </li>
          <li class="flex gap-2">
            <span class="text-blue-600 font-bold">2.</span>
            <span>Pesanan akan disiapkan dan dikemas dengan aman</span>
          </li>
          <li class="flex gap-2">
            <span class="text-blue-600 font-bold">3.</span>
            <span>Anda akan menerima notifikasi pengiriman</span>
          </li>
          <li class="flex gap-2">
            <span class="text-blue-600 font-bold">4.</span>
            <span>Terima paket dan selesaikan pembayaran (jika COD)</span>
          </li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="font-bold text-lg mb-4">💬 Hubungi Kami</h3>

        <p class="text-gray-700 mb-3">Jika ada pertanyaan, hubungi kami melalui:</p>

        <div class="space-y-2">
          <a
            href="https://wa.me/6281234567890"
            target="_blank"
            class="flex items-center gap-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition"
          >
            <span class="text-2xl">💬</span>
            <div>
              <p class="font-bold">WhatsApp</p>
              <p class="text-sm text-gray-600">+62 812-3456-7890</p>
            </div>
          </a>

          <a
            href="tel:+6281234567890"
            class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
          >
            <span class="text-2xl">📞</span>
            <div>
              <p class="font-bold">Telepon</p>
              <p class="text-sm text-gray-600">+62 812-3456-7890</p>
            </div>
          </a>

          <a
            href="mailto:support@farmaflow.com"
            class="flex items-center gap-3 p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition"
          >
            <span class="text-2xl">📧</span>
            <div>
              <p class="font-bold">Email</p>
              <p class="text-sm text-gray-600">support@farmaflow.com</p>
            </div>
          </a>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-4">
        <router-link
          to="/products"
          class="flex-1 text-center px-6 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-bold text-lg"
        >
          🛍️ Belanja Lagi
        </router-link>

        <router-link
          to="/order-tracking"
          :query="{ order_number: orderNumber, phone: customerPhone }"
          class="flex-1 text-center px-6 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold text-lg"
        >
          📍 Lacak Pesanan
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const orderNumber = ref('')
const customerPhone = ref('')
const customerName = ref('')
const totalAmount = ref(0)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

onMounted(() => {
  // Get from query params
  orderNumber.value = route.query.order_number || ''
  customerPhone.value = route.query.phone || ''

  // Get from localStorage
  const lastOrder = localStorage.getItem('last_order')
  if (lastOrder) {
    try {
      const order = JSON.parse(lastOrder)
      customerName.value = order.customer_name || 'Pelanggan'
      totalAmount.value = order.total_amount || 0
    } catch (error) {
      console.error('Failed to parse last order:', error)
    }
  }
})
</script>