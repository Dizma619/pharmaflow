<template>
  <div class="grid grid-cols-4 gap-6 h-full">
    <!-- Products List (Left - 3 columns) -->
    <div class="col-span-3 space-y-4">
      <!-- Search Bar & Categories -->
      <div class="bg-white rounded-lg shadow-md p-4 space-y-4">
        <!-- Search -->
        <div class="flex gap-2">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="🔍 Cari obat atau scan barcode..."
            @keyup.enter="searchMedicine"
            class="flex-1 px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold"
            autofocus
          />
          <button
            @click="searchMedicine"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
          >
            🔍 Cari
          </button>
        </div>

        <!-- Categories -->
        <div class="flex gap-2 overflow-x-auto pb-2">
          <button
            @click="selectedCategory = null; fetchMedicines()"
            :class="[
              'px-4 py-2 rounded-lg whitespace-nowrap font-semibold transition',
              selectedCategory === null
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
            ]"
          >
            📚 Semua ({{ medicineCount }})
          </button>
          <button
            v-for="cat in categories"
            :key="cat.id"
            @click="selectedCategory = cat.id; fetchMedicines()"
            :class="[
              'px-4 py-2 rounded-lg whitespace-nowrap font-semibold transition',
              selectedCategory === cat.id
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
            ]"
          >
            {{ cat.name }}
          </button>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="bg-white rounded-lg shadow-md p-4">
        <div v-if="loadingMedicines" class="text-center py-12">
          <p class="text-lg text-gray-600">⏳ Memuat produk...</p>
        </div>
        <div v-else-if="medicines.length === 0" class="text-center py-12">
          <p class="text-lg text-gray-600">❌ Produk tidak ditemukan</p>
        </div>
        <div v-else class="grid grid-cols-4 gap-4">
          <button
            v-for="medicine in medicines"
            :key="medicine.id"
            @click="addToCart(medicine)"
            :disabled="medicine.total_stock === 0"
            class="p-4 border-2 border-gray-300 rounded-lg hover:border-blue-600 hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition text-left hover:shadow-lg"
          >
            <!-- Icon -->
            <p class="text-4xl mb-2">💊</p>

            <!-- Name -->
            <p class="font-bold text-sm line-clamp-2 mb-2">{{ medicine.name }}</p>

            <!-- Price -->
            <p class="text-green-600 font-bold text-lg mb-2">Rp{{ formatPrice(medicine.selling_price) }}</p>

            <!-- Stock -->
            <div class="flex justify-between items-center text-xs">
              <span class="text-gray-600">Stok:</span>
              <span :class="[
                'px-2 py-1 rounded font-bold',
                medicine.total_stock === 0 ? 'bg-red-100 text-red-800' :
                medicine.total_stock <= medicine.stock_minimum ? 'bg-orange-100 text-orange-800' :
                'bg-green-100 text-green-800'
              ]">
                {{ medicine.total_stock }}
              </span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Cart/Checkout (Right - 1 column) -->
    <div class="col-span-1">
      <div class="bg-white rounded-lg shadow-md p-6 h-full flex flex-col sticky top-6 max-h-[calc(100vh-30px)]">
        <!-- Header -->
        <div class="mb-4 pb-4 border-b">
          <h2 class="text-2xl font-bold">🛒 CART</h2>
          <p class="text-sm text-gray-600">{{ cartItems.length }} item</p>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto space-y-3 mb-4">
          <div v-if="cartItems.length === 0" class="text-center py-8 text-gray-600">
            <p class="text-lg">📭</p>
            <p>Keranjang kosong</p>
          </div>

          <div
            v-for="item in cartItems"
            :key="item.medicine_id"
            class="p-3 bg-gray-50 rounded-lg border-l-4 border-blue-500 hover:shadow-md transition"
          >
            <!-- Item Header -->
            <div class="flex justify-between items-start mb-2">
              <div class="flex-1">
                <p class="font-bold text-sm line-clamp-2">{{ item.name }}</p>
                <p class="text-blue-600 font-semibold text-sm">Rp{{ formatPrice(item.unit_price) }}</p>
              </div>
              <button
                @click="removeFromCart(item.medicine_id)"
                class="text-red-600 hover:text-red-800 font-bold text-lg hover:bg-red-100 rounded-full w-6 h-6 flex items-center justify-center"
              >
                ×
              </button>
            </div>

            <!-- Quantity Control -->
            <div class="flex items-center gap-2 justify-between">
              <div class="flex items-center gap-1 bg-white border border-gray-300 rounded">
                <button
                  @click="updateQuantity(item.medicine_id, item.quantity - 1)"
                  class="w-6 h-6 text-gray-600 hover:bg-gray-100 flex items-center justify-center"
                >
                  −
                </button>
                <input
                  v-model.number="item.quantity"
                  type="number"
                  min="1"
                  @change="updateQuantity(item.medicine_id, item.quantity)"
                  class="w-8 text-center border-0 focus:outline-none focus:ring-0 font-bold"
                />
                <button
                  @click="updateQuantity(item.medicine_id, item.quantity + 1)"
                  class="w-6 h-6 text-gray-600 hover:bg-gray-100 flex items-center justify-center"
                >
                  +
                </button>
              </div>
              <p class="font-bold text-green-600">Rp{{ formatPrice(item.quantity * item.unit_price) }}</p>
            </div>
          </div>
        </div>

        <!-- Discount Section -->
        <div class="border-t pt-3 mb-4">
          <label class="block text-sm font-semibold mb-2">💰 Diskon (Rp)</label>
          <input
            v-model.number="discountAmount"
            type="number"
            min="0"
            max="9999999"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold"
          />
        </div>

        <!-- Payment Method -->
        <div class="border-t pt-3 mb-4">
          <label class="block text-sm font-semibold mb-2">💳 Metode Pembayaran</label>
          <select
            v-model="paymentMethod"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold"
          >
            <option value="cash">💵 Tunai</option>
            <option value="transfer">🏦 Transfer</option>
            <option value="credit_card">💳 Kartu Kredit</option>
          </select>
        </div>

        <!-- Cash Received (for cash payment) -->
        <div v-if="paymentMethod === 'cash'" class="border-t pt-3 mb-4">
          <label class="block text-sm font-semibold mb-2">💵 Uang Diterima</label>
          <input
            v-model.number="cashReceived"
            type="number"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold"
            placeholder="0"
          />
          <div class="bg-blue-50 p-3 rounded-lg mt-2">
            <p class="text-sm text-gray-600">🔄 Kembalian</p>
            <p :class="[
              'text-2xl font-bold',
              change >= 0 ? 'text-blue-600' : 'text-red-600'
            ]">
              Rp{{ formatPrice(change) }}
            </p>
          </div>
        </div>

        <!-- Totals -->
        <div class="border-t pt-4 space-y-2 mb-4">
          <div class="flex justify-between text-gray-600">
            <span>Subtotal</span>
            <span class="font-semibold">Rp{{ formatPrice(subtotal) }}</span>
          </div>
          <div class="flex justify-between text-red-600">
            <span>Diskon</span>
            <span class="font-semibold">-Rp{{ formatPrice(discountAmount) }}</span>
          </div>
          <div class="flex justify-between text-2xl font-bold bg-gradient-to-r from-green-50 to-green-100 p-3 rounded-lg border-2 border-green-300">
            <span>Total</span>
            <span class="text-green-600">Rp{{ formatPrice(total) }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2">
          <button
            @click="completeTransaction"
            :disabled="cartItems.length === 0 || loading || (paymentMethod === 'cash' && change < 0)"
            class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed transition font-bold text-lg"
          >
            {{ loading ? '⏳ Processing...' : '✅ Selesaikan' }}
          </button>

          <button
            @click="clearCart"
            :disabled="cartItems.length === 0"
            class="w-full px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 disabled:opacity-50 transition font-semibold"
          >
            🗑️ Bersihkan
          </button>

          <button
            @click="printReceipt"
            :disabled="!lastTransaction"
            class="w-full px-4 py-2 bg-blue-300 text-blue-800 rounded-lg hover:bg-blue-400 disabled:opacity-50 transition font-semibold"
          >
            🖨️ Cetak
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage, ElMessageBox } from 'element-plus'

const medicines = ref([])
const categories = ref([])
const cartItems = ref([])
const searchQuery = ref('')
const selectedCategory = ref(null)
const loadingMedicines = ref(false)
const loading = ref(false)

const discountAmount = ref(0)
const paymentMethod = ref('cash')
const cashReceived = ref(0)
const medicineCount = ref(0)
const lastTransaction = ref(null)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const fetchMedicines = async () => {
  loadingMedicines.value = true
  try {
    const params = { per_page: 100 }
    if (selectedCategory.value) {
      params.category_id = selectedCategory.value
    }
    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    const response = await api.get('medicines', { params })
    medicines.value = response.data.data.data || []
    medicineCount.value = response.data.data.total || 0

    // Calculate total stock for each medicine
    medicines.value.forEach(med => {
      med.total_stock = med.stocks?.reduce((sum, stock) => sum + stock.quantity, 0) || 0
    })
  } catch (error) {
    ElMessage.error('Gagal memuat produk')
  } finally {
    loadingMedicines.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get('categories?per_page=100')
    categories.value = response.data.data.data || []
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const searchMedicine = () => {
  fetchMedicines()
}

const addToCart = (medicine) => {
  const existingItem = cartItems.value.find(item => item.medicine_id === medicine.id)

  if (existingItem) {
    if (existingItem.quantity < medicine.total_stock) {
      existingItem.quantity += 1
      ElMessage.success(`${medicine.name} ditambah (${existingItem.quantity})`)
    } else {
      ElMessage.warning('Stok tidak cukup')
    }
  } else {
    cartItems.value.push({
      medicine_id: medicine.id,
      name: medicine.name,
      unit_price: medicine.selling_price,
      quantity: 1,
    })
    ElMessage.success(`${medicine.name} ditambahkan ke keranjang`)
  }
}

const removeFromCart = (medicineId) => {
  const item = cartItems.value.find(i => i.medicine_id === medicineId)
  if (item) {
    cartItems.value = cartItems.value.filter(i => i.medicine_id !== medicineId)
    ElMessage.success('Item dihapus dari keranjang')
  }
}

const updateQuantity = (medicineId, quantity) => {
  const item = cartItems.value.find(i => i.medicine_id === medicineId)
  if (item) {
    const medicine = medicines.value.find(m => m.id === medicineId)
    if (quantity > medicine.total_stock) {
      ElMessage.warning('Stok tidak cukup')
      item.quantity = medicine.total_stock
    } else {
      item.quantity = Math.max(1, quantity)
    }
  }
}

const clearCart = async () => {
  try {
    await ElMessageBox.confirm(
      'Bersihkan keranjang?',
      'Konfirmasi',
      {
        confirmButtonText: 'Ya, Bersihkan',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    cartItems.value = []
    discountAmount.value = 0
    cashReceived.value = 0
    paymentMethod.value = 'cash'
    ElMessage.success('Keranjang dibersihkan')
  } catch {
    // Batal
  }
}

const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
})

const total = computed(() => {
  return Math.max(0, subtotal.value - discountAmount.value)
})

const change = computed(() => {
  return Math.max(0, cashReceived.value - total.value)
})

const completeTransaction = async () => {
  if (cartItems.length === 0) {
    ElMessage.warning('Keranjang kosong')
    return
  }

  if (paymentMethod.value === 'cash' && change.value < 0) {
    ElMessage.warning(`Uang kurang Rp${formatPrice(Math.abs(change.value))}`)
    return
  }

  loading.value = true
  try {
    const response = await api.post('transactions', {
      items: cartItems.value.map(item => ({
        medicine_id: item.medicine_id,
        quantity: item.quantity,
      })),
      discount_amount: discountAmount.value,
      payment_method: paymentMethod.value,
      payment_status: 'lunas',
      cash_received: paymentMethod.value === 'cash' ? cashReceived.value : null,
    })

    lastTransaction.value = response.data.data

    ElMessage.success('✅ Transaksi berhasil!')

    // Reset form
    cartItems.value = []
    discountAmount.value = 0
    cashReceived.value = 0
    paymentMethod.value = 'cash'
    searchQuery.value = ''
    selectedCategory.value = null
    fetchMedicines()
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Transaksi gagal')
  } finally {
    loading.value = false
  }
}

const printReceipt = () => {
  if (!lastTransaction.value) {
    ElMessage.warning('Tidak ada transaksi untuk dicetak')
    return
  }

  // TODO: Implement receipt printing
  ElMessage.info('Fitur print receipt akan segera tersedia')
}

onMounted(() => {
  fetchCategories()
  fetchMedicines()
})
</script>