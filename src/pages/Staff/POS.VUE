<template>
  <div class="flex h-screen w-full bg-slate-50/70 font-sans overflow-hidden text-slate-800">
    
    <div class="flex-1 flex flex-col p-8 overflow-y-auto no-print">
      
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">POS Kasir</h1>
          <p class="text-sm text-slate-500 mt-0.5">Kelola transaksi obat dengan cepat dan efisien</p>
        </div>
        
        <div class="flex items-center gap-2 bg-white border border-slate-200 shadow-sm p-1.5 rounded-xl w-full md:w-96 focus-within:ring-2 focus-within:ring-emerald-500/20 focus-within:border-emerald-500 transition-all">
          <div class="pl-2 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
            </svg>
          </div>
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari nama atau kode obat..." 
            class="w-full px-2 py-1.5 text-sm bg-transparent outline-none text-slate-700 placeholder:text-slate-400" 
            @keyup.enter="fetchMedicines"
            autofocus 
          />
          <button @click="fetchMedicines" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors shadow-sm shadow-emerald-600/10">
            Cari
          </button>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12 flex-1 flex flex-col justify-center items-center">
        <p class="text-slate-500 animate-pulse font-medium">⏳ Memuat data obat dari server...</p>
      </div>

      <div v-else-if="medicines.length === 0" class="text-center py-12 flex-1 flex flex-col justify-center items-center text-slate-400">
        <p class="font-semibold text-slate-600">Obat Tidak Ditemukan</p>
        <p class="text-xs mt-1">Coba masukkan kata kunci pencarian yang lain.</p>
      </div>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        <div 
          v-for="item in medicines" 
          :key="item.id" 
          @click="addToCart(item)"
          class="group bg-white border border-slate-100 p-5 rounded-2xl shadow-sm hover:shadow-md hover:border-emerald-100 transition-all duration-200 flex flex-col justify-between min-h-[200px] relative overflow-hidden cursor-pointer"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
          
          <div class="w-full">
            <div class="flex justify-between items-start mb-3">
              <span 
                :class="item.stock > 10 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider"
              >
                Stok: {{ item.stock }}
              </span>
            </div>
            
            <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 group-hover:text-emerald-700 transition-colors">
              {{ item.name }}
            </h3>
            <p class="text-xs text-slate-400 mt-1">Kode: {{ item.code || '-' }}</p>
          </div>

          <div class="flex items-end justify-between mt-4 pt-3 border-t border-slate-50">
            <div>
              <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Harga</p>
              <p class="text-emerald-600 font-extrabold text-base">
                Rp {{ Number(item.price).toLocaleString('id-ID') }}
              </p>
            </div>
            
            <div class="p-2 bg-slate-50 group-hover:bg-emerald-600 text-slate-400 group-hover:text-white rounded-xl transition-all shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="w-96 bg-white shadow-2xl shadow-slate-200/80 border-l border-slate-100 flex flex-col h-full z-10 no-print">
      
      <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
          </span>
          <span class="font-bold text-slate-900 tracking-tight text-base">Keranjang Belanja</span>
        </div>
        <span 
          :class="totalItems > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'" 
          class="text-xs font-bold px-2.5 py-1 rounded-md transition-colors"
        >
          {{ totalItems }} Item
        </span>
      </div>
      
      <div v-if="cart.length === 0" class="flex-1 p-6 overflow-y-auto flex flex-col justify-center items-center text-center">
         <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4">
           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
           </svg>
         </div>
         <p class="text-sm font-semibold text-slate-700">Keranjang Masih Kosong</p>
         <p class="text-xs text-slate-400 mt-1 max-w-[200px]">Klik obat di sebelah kiri untuk menambahkannya ke transaksi.</p>
      </div>

      <div v-else class="flex-1 p-6 overflow-y-auto space-y-3.5">
        <div 
          v-for="cartItem in cart" 
          :key="cartItem.id" 
          class="flex items-center justify-between p-3.5 bg-slate-50/80 rounded-xl border border-slate-100"
        >
          <div class="flex-1 min-w-0 pr-3">
            <h4 class="font-bold text-sm text-slate-800 truncate">{{ cartItem.name }}</h4>
            <p class="text-xs text-emerald-600 font-extrabold mt-0.5">
              Rp {{ (cartItem.price * cartItem.quantity).toLocaleString('id-ID') }}
            </p>
          </div>
          
          <div class="flex items-center gap-2.5 bg-white border border-slate-200/80 rounded-lg p-1 shadow-sm select-none">
            <button 
              @click.stop="decreaseQty(cartItem)" 
              class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-slate-100 text-slate-500 hover:text-slate-950 font-bold transition-colors text-xs"
            >
              —
            </button>
            <span class="text-xs font-extrabold text-slate-800 w-4 text-center">{{ cartItem.quantity }}</span>
            <button 
              @click.stop="increaseQty(cartItem)" 
              class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-slate-100 text-slate-500 hover:text-slate-950 font-bold transition-colors text-xs"
            >
              +
            </button>
          </div>
        </div>
      </div>
      
      <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex flex-col gap-4">
        <div class="flex justify-between items-center text-sm font-medium text-slate-500">
          <span>Subtotal</span>
          <span class="font-extrabold text-slate-900 text-base">Rp {{ totalPrice.toLocaleString('id-ID') }}</span>
        </div>
        
        <button 
          :disabled="cart.length === 0 || processingCheckout" 
          @click="checkout"
          :class="cart.length === 0 || processingCheckout
            ? 'bg-slate-200 text-slate-400 cursor-not-allowed' 
            : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 active:scale-[0.99] cursor-pointer'"
          class="w-full py-3 font-bold rounded-xl text-sm transition-all text-center"
        >
          {{ processingCheckout ? '⏳ Memproses...' : 'Bayar Sekarang' }}
        </button>
      </div>
    </div>

    <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 transition-opacity print-overlay">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-100 transition-transform printable-area">
        
        <div class="bg-emerald-500 text-white text-center py-8 px-6 no-print">
          <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <h2 class="text-2xl font-bold">Pembayaran Berhasil!</h2>
          <p class="text-emerald-100 mt-1 text-sm">Transaksi telah tercatat di sistem</p>
        </div>

        <div class="p-6 bg-slate-50 print-bg-white">
          <div class="text-center mb-6 hidden print-block">
            <h2 class="text-xl font-bold text-slate-900">APOTEK PHARMAFLOW</h2>
            <p class="text-xs text-slate-500">Jl. Kesehatan No. 123, Kota Anda</p>
            <p class="text-xs text-slate-500">Telp: (021) 12345678</p>
            <div class="border-b-2 border-dashed border-slate-300 mt-4"></div>
          </div>

          <div class="flex justify-between text-xs text-slate-500 mb-4 border-b border-slate-200 pb-4 border-dashed">
            <div>
              <p>No. Transaksi</p>
              <p class="font-bold text-slate-800 text-sm mt-0.5">{{ lastTransaction.receiptNo }}</p>
            </div>
            <div class="text-right">
              <p>Tanggal</p>
              <p class="font-bold text-slate-800 text-sm mt-0.5">{{ lastTransaction.date }}</p>
            </div>
          </div>

          <div class="max-h-48 overflow-y-auto space-y-3 pr-2 mb-4 print-no-scroll">
            <div v-for="item in lastTransaction.items" :key="item.id" class="flex justify-between text-sm">
              <div>
                <p class="font-bold text-slate-800">{{ item.name }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ item.quantity }} x Rp {{ Number(item.price).toLocaleString('id-ID') }}</p>
              </div>
              <p class="font-bold text-slate-800">Rp {{ (item.price * item.quantity).toLocaleString('id-ID') }}</p>
            </div>
          </div>

          <div class="flex justify-between items-center border-t border-slate-200 pt-4 border-dashed mb-6">
            <span class="text-slate-500 font-medium">Total Dibayar</span>
            <span class="text-2xl font-extrabold text-emerald-600 print-text-black">Rp {{ lastTransaction.total.toLocaleString('id-ID') }}</span>
          </div>

          <div class="flex gap-3 no-print">
            <button @click="printReceipt" class="flex-1 bg-white border-2 border-slate-200 text-slate-700 font-bold py-3 rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-colors flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              Cetak
            </button>
            <button @click="closeReceipt" class="flex-1 bg-emerald-600 text-white font-bold py-3 rounded-xl hover:bg-emerald-700 transition-colors shadow-md shadow-emerald-600/20">
              Transaksi Baru
            </button>
          </div>

          <div class="text-center mt-6 hidden print-block">
            <p class="text-xs text-slate-500">Terima kasih atas kunjungan Anda!</p>
            <p class="text-xs text-slate-500">Semoga lekas sembuh.</p>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const medicines = ref([])
const loading = ref(false)
const processingCheckout = ref(false)
const searchQuery = ref('')
const cart = ref([])
const showReceiptModal = ref(false)
const lastTransaction = ref({ items: [], total: 0, date: '', receiptNo: '' })

// 1. Fungsi Mengambil Data Obat dari API Server Dinamis
const fetchMedicines = async () => {
  loading.value = true
  try {
    const params = { per_page: 100 }
    if (searchQuery.value) params.search = searchQuery.value

    const response = await api.get('medicines', { params })
    
    // Sistem fallback pembungkus objek array Laravel API
    if (response.data?.data?.data) {
      medicines.value = response.data.data.data
    } else if (response.data?.data) {
      medicines.value = response.data.data
    } else {
      medicines.value = response.data || []
    }
  } catch (error) {
    console.error('Gagal memuat data obat:', error)
  } finally {
    loading.value = false
  }
}

// Alias agar panggilaan loadMedicines() di checkout aman tidak patah
const loadMedicines = fetchMedicines

const addToCart = (item) => {
  if (item.stock <= 0) {
    alert(`Stok obat ${item.name} sudah tidak mencukupi!`)
    return
  }

  const existingItem = cart.value.find(cartItem => cartItem.id === item.id)
  
  if (existingItem) {
    if (existingItem.quantity < item.stock) {
      existingItem.quantity++
    } else {
      alert(`Batas maksimal transaksi sesuai stok tersedia (${item.stock} item)`)
    }
  } else {
    cart.value.push({ id: item.id, name: item.name, price: item.price, stock: item.stock, quantity: 1 })
  }
}

const increaseQty = (cartItem) => {
  if (cartItem.quantity < cartItem.stock) {
    cartItem.quantity++
  } else {
    alert(`Stok tidak mencukupi untuk menambah jumlah kuantitas.`)
  }
}

const decreaseQty = (cartItem) => {
  if (cartItem.quantity > 1) {
    cartItem.quantity--
  } else {
    cart.value = cart.value.filter(item => item.id !== cartItem.id)
  }
}

const totalItems = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0))
const totalPrice = computed(() => cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0))

// 2. Fungsi Eksekusi Simpan Pembayaran / Checkout POS
const checkout = async () => {
  if (cart.value.length === 0) return

  processingCheckout.value = true
  try {
    const payload = {
      items: cart.value.map(item => ({
        medicine_id: item.id,
        quantity: item.quantity
      })),
      payment_method: 'cash',
      cash_received: totalPrice.value
    }

    const response = await api.post('/transactions', payload)

    if (response.data.success || response.status === 200 || response.status === 201) {
      // Ambil kode referensi transaksi dinamis dari server response
      const serverRef = response.data?.data?.reference_number || response.data?.data?.receipt_no || 'TRX-' + Date.now()

      lastTransaction.value = {
        items: [...cart.value],
        total: totalPrice.value,
        date: new Date().toLocaleString('id-ID'),
        receiptNo: serverRef
      }

      showReceiptModal.value = true
      cart.value = []
      
      // Ambil ulang stok obat terbaru pasca transaksi sukses
      await loadMedicines()
    } else {
      alert(response.data.message || 'Gagal menyimpan transaksi')
    }
  } catch (error) {
    console.error(error)
    alert(error.response?.data?.message || 'Gagal menyimpan data transaksi ke server')
  } finally {
    processingCheckout.value = false
  }
}

const closeReceipt = () => {
  showReceiptModal.value = false
}

const printReceipt = () => {
  window.print() 
}

// 3. Jalankan pemuatan data otomatis saat halaman di-mount pertama kali
onMounted(() => {
  fetchMedicines()
})
</script>

<style>
@media print {
  .no-print {
    display: none !important;
  }
  
  .print-overlay {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: auto !important;
    background: white !important;
    backdrop-filter: none !important;
    align-items: flex-start !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .printable-area {
    box-shadow: none !important;
    border-radius: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 10px !important;
  }

  .print-block {
    display: block !important;
  }
  
  .print-bg-white {
    background-color: white !important;
  }

  .print-no-scroll {
    max-height: none !important;
    overflow: visible !important;
  }

  .print-text-black {
    color: black !important;
  }

  body {
    font-family: 'Courier New', Courier, monospace !important;
  }
}
</style>