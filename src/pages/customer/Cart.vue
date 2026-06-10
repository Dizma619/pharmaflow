<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-8 px-6">
      <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold">🛒 Keranjang Belanja</h1>
        <p class="text-blue-100">{{ cartStore.items.length }} item dalam keranjang</p>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">
      <div class="grid grid-cols-3 gap-8">
        <!-- Cart Items (Left - 2 cols) -->
        <div class="col-span-2">
          <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Empty State -->
            <div v-if="cartStore.items.length === 0" class="text-center py-12">
              <p class="text-4xl mb-4">📭</p>
              <p class="text-xl text-gray-600 mb-6">Keranjang Anda masih kosong</p>
              <router-link
                to="/products"
                class="inline-block px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold"
              >
                🛍️ Belanja Sekarang
              </router-link>
            </div>

            <!-- Items List -->
            <div v-else class="space-y-4">
              <div
                v-for="item in cartStore.items"
                :key="item.id"
                class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500 hover:shadow-md transition"
              >
                <!-- Image -->
                <div class="w-20 h-20 rounded-lg bg-blue-100 flex items-center justify-center text-4xl flex-shrink-0">
                  💊
                </div>

                <!-- Item Info -->
                <div class="flex-1">
                  <router-link
                    :to="`/products/${item.id}`"
                    class="text-lg font-bold text-gray-800 hover:text-blue-600 block mb-1"
                  >
                    {{ item.name }}
                  </router-link>
                  <p class="text-green-600 font-bold">Rp{{ formatPrice(item.price) }}</p>
                </div>

                <!-- Quantity Control -->
                <div class="flex items-center gap-2 border border-gray-300 rounded-lg">
                  <button
                    @click="cartStore.updateQuantity(item.id, item.quantity - 1)"
                    class="w-10 h-10 hover:bg-gray-100 flex items-center justify-center"
                  >
                    −
                  </button>
                  <input
                    v-model.number="item.quantity"
                    @change="cartStore.updateQuantity(item.id, item.quantity)"
                    type="number"
                    min="1"
                    class="w-12 text-center border-0 focus:outline-none font-bold"
                  />
                  <button
                    @click="cartStore.updateQuantity(item.id, item.quantity + 1)"
                    class="w-10 h-10 hover:bg-gray-100 flex items-center justify-center"
                  >
                    +
                  </button>
                </div>

                <!-- Subtotal -->
                <div class="text-right">
                  <p class="text-lg font-bold text-green-600">Rp{{ formatPrice(item.price * item.quantity) }}</p>
                  <button
                    @click="cartStore.removeFromCart(item.id)"
                    class="text-red-600 hover:text-red-800 text-sm font-bold mt-1"
                  >
                    🗑️ Hapus
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary (Right - 1 col) -->
        <div class="col-span-1">
          <div class="bg-white rounded-lg shadow-md p-6 sticky top-6 space-y-4">
            <!-- Order Summary -->
            <h2 class="text-2xl font-bold mb-4">📋 Ringkasan</h2>

            <div class="space-y-3 pb-4 border-b">
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

            <div class="flex justify-between items-center text-2xl font-bold bg-green-50 p-4 rounded-lg">
              <span>Total</span>
              <span class="text-green-600">Rp{{ formatPrice(cartStore.total) }}</span>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
              <router-link
                to="/checkout"
                :class="[
                  'block text-center px-4 py-3 rounded-lg font-bold transition',
                  cartStore.items.length === 0
                    ? 'bg-gray-400 text-gray-600 cursor-not-allowed'
                    : 'bg-green-600 text-white hover:bg-green-700'
                ]"
              >
                ✅ Lanjut Checkout
              </router-link>

              <router-link
                to="/products"
                class="block text-center px-4 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-bold"
              >
                🛍️ Lanjut Belanja
              </router-link>

              <button
                v-if="cartStore.items.length > 0"
                @click="clearCart"
                class="w-full px-4 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition font-bold text-sm"
              >
                🗑️ Kosongkan Keranjang
              </button>
            </div>

            <!-- Login CTA -->
            <div v-if="!authStore.isAuthenticated" class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
              <p class="text-sm text-gray-700 mb-2">
                ✨ Daftarkan akun untuk menyimpan riwayat pesanan dan alamat
              </p>
              <div class="flex gap-2">
                <router-link
                  to="/login"
                  class="flex-1 text-center px-2 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition font-bold"
                >
                  Masuk
                </router-link>
                <router-link
                  to="/register"
                  class="flex-1 text-center px-2 py-2 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200 transition font-bold"
                >
                  Daftar
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { ElMessage, ElMessageBox } from 'element-plus'

const cartStore = useCartStore()
const authStore = useAuthStore()

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const clearCart = async () => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin kosongkan keranjang?',
      'Konfirmasi',
      {
        confirmButtonText: 'Ya, Kosongkan',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )
    cartStore.clearCart()
    ElMessage.success('Keranjang dikosongkan')
  } catch {
    // User cancelled
  }
}

onMounted(() => {
  // Load cart from localStorage
  cartStore.loadFromLocalStorage()
})
</script>