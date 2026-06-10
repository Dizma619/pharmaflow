<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">🏪 Katalog Obat</h1>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Sidebar Filters -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-xl font-bold mb-4">Filter</h3>

          <!-- Search -->
          <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">Cari Obat</label>
            <input
              v-model="filters.search"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Nama obat..."
            />
          </div>

          <!-- Category Filter -->
          <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">Kategori</label>
            <select
              v-model="filters.category_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            >
              <option value="">Semua Kategori</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- Stock Filter -->
          <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">Stok</label>
            <label class="flex items-center">
              <input
                type="checkbox"
                v-model="filters.low_stock"
                class="rounded"
              />
              <span class="ml-2 text-gray-700">Stok Menipis</span>
            </label>
          </div>

          <!-- Apply Filters -->
          <button
            @click="fetchMedicines"
            class="w-full bg-green-600 text-white font-semibold py-2 rounded-lg hover:bg-green-700 transition"
          >
            🔍 Terapkan Filter
          </button>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="lg:col-span-3">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <p class="text-xl text-gray-600">⏳ Memuat produk...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="medicines.length === 0" class="text-center py-12">
          <p class="text-xl text-gray-600">😔 Tidak ada produk ditemukan</p>
        </div>

        <!-- Products -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <ProductCard
            v-for="medicine in medicines"
            :key="medicine.id"
            :medicine="medicine"
          />
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-8 flex justify-center gap-2">
          <button
            @click="previousPage"
            :disabled="currentPage === 1"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
          >
            ← Sebelumnya
          </button>

          <button
            v-for="page in totalPages"
            :key="page"
            @click="currentPage = page"
            :class="[
              'px-4 py-2 rounded-lg border transition',
              currentPage === page
                ? 'bg-green-600 text-white border-green-600'
                : 'border-gray-300 hover:border-green-600'
            ]"
          >
            {{ page }}
          </button>

          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
          >
            Selanjutnya →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import ProductCard from '@/components/ProductCard.vue'

const medicines = ref([])
const categories = ref([])
const loading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)

const filters = ref({
  search: '',
  category_id: '',
  low_stock: false,
})

const fetchMedicines = async () => {
  loading.value = true
  try {
    const params = {
      ...filters.value,
      page: currentPage.value,
      per_page: 12,
    }
    const response = await api.get('medicines', { params })
    medicines.value = response.data.data.data || []
    totalPages.value = response.data.data.last_page || 1
  } catch (error) {
    console.error('Failed to fetch medicines:', error)
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get('categories')
    categories.value = response.data.data.data || []
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    fetchMedicines()
  }
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchMedicines()
  }
}

onMounted(() => {
  fetchCategories()
  fetchMedicines()
})
</script>