<template>
  <div>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-green-500 to-blue-600 text-white py-20">
      <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 gap-8 items-center">
          <div>
            <h1 class="text-5xl font-bold mb-4">⚕️ FharmaFlow</h1>
            <p class="text-xl mb-4">Platform E-Commerce Apotek Terpadu dengan Sistem Kasir dan ERP</p>
            <div class="flex gap-4">
              <router-link
                to="/products"
                class="px-8 py-3 bg-white text-green-600 font-bold rounded-lg hover:bg-gray-100 transition"
              >
                🛍️ Belanja Sekarang
              </router-link>
              <a
                href="#features"
                class="px-8 py-3 border-2 border-white rounded-lg hover:bg-white hover:bg-opacity-10 transition"
              >
                📖 Pelajari Lebih Lanjut
              </a>
            </div>
          </div>
          <div class="text-center">
            <div class="text-8xl">💊</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12">Fitur Unggulan</h2>
        <div class="grid grid-cols-3 gap-8">
          <!-- Feature 1 -->
          <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition">
            <div class="text-5xl mb-4">🛒</div>
            <h3 class="text-2xl font-bold mb-2">E-Commerce</h3>
            <p class="text-gray-600">Belanja obat dengan mudah dari rumah. Sistem checkout yang aman dengan berbagai metode pembayaran.</p>
          </div>

          <!-- Feature 2 -->
          <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition">
            <div class="text-5xl mb-4">🖥️</div>
            <h3 class="text-2xl font-bold mb-2">Sistem Kasir</h3>
            <p class="text-gray-600">POS modern untuk transaksi langsung. Laporan real-time dan manajemen transaksi yang efisien.</p>
          </div>

          <!-- Feature 3 -->
          <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition">
            <div class="text-5xl mb-4">📊</div>
            <h3 class="text-2xl font-bold mb-2">Dashboard Analytics</h3>
            <p class="text-gray-600">Visualisasi data penjualan, inventory, dan keuangan dalam satu dashboard komprehensif.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-4xl font-bold mb-12">Produk Terbaru</h2>
        <div v-if="loading" class="text-center">
          <p class="text-xl text-gray-600">Memuat produk...</p>
        </div>
        <div v-else class="grid grid-cols-4 gap-6">
          <ProductCard
            v-for="medicine in recentMedicines"
            :key="medicine.id"
            :medicine="medicine"
          />
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-green-600 text-white py-16">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Dapatkan Diskon 10% untuk Pembelian Pertama!</h2>
        <p class="text-xl mb-6">Gunakan kode: <strong class="bg-green-700 px-3 py-1 rounded">WELCOME10</strong></p>
        <router-link
          to="/products"
          class="inline-block px-8 py-3 bg-white text-green-600 font-bold rounded-lg hover:bg-gray-100 transition"
        >
          Mulai Belanja Sekarang
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import ProductCard from '@/components/ProductCard.vue'

const recentMedicines = ref([])
const loading = ref(false)

const fetchRecentMedicines = async () => {
  loading.value = true
  try {
    const response = await api.get('medicines?per_page=8&order_by=created_at&order_dir=desc')
    recentMedicines.value = response.data.data.data || []
  } catch (error) {
    console.error('Failed to fetch medicines:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchRecentMedicines()
})
</script>