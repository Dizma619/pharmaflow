<template>
 <div class="flex h-screen bg-slate-50">

    <!-- SIDEBAR -->
    <aside
  :class="[
    'fixed left-0 top-0 z-50 h-screen w-72 bg-white border-r border-slate-200 flex flex-col transition-all duration-300',
    sidebarOpen
      ? 'translate-x-0'
      : '-translate-x-full'
  ]"
>
      <!-- Logo -->
      <div class="px-6 pt-7 pb-5 border-b border-slate-100">
        <div class="flex items-center gap-4">
          <div
            class="w-14 h-14 rounded-3xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg flex items-center justify-center text-white text-2xl"
          >
            💊
          </div>

          <div>
            <h1 class="text-xl font-bold text-slate-900">
              PharmaFlow
            </h1>

            <p class="text-sm text-slate-500">
              Staff Dashboard
            </p>
          </div>
        </div>

        <!-- User Card -->
        <div
          class="mt-6 rounded-[28px] border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-4"
        >
          <div class="flex items-center gap-4">
            <div
              class="w-14 h-14 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-bold text-lg shadow-md"
            >
              {{
                authStore.user?.name
                  ?.charAt(0)
                  .toUpperCase()
              }}
            </div>

            <div class="flex-1">
              <h3
                class="font-semibold text-slate-800 text-sm"
              >
                {{ authStore.user?.name }}
              </h3>

              <p class="text-xs text-slate-500">
                {{
                  getDepartmentLabel(
                    authStore.user?.department
                  )
                }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- NAVIGATION -->
      <nav
        class="flex-1 overflow-y-auto px-4 py-6 space-y-7"
      >
        <!-- Dashboard -->
        <div>
          <p
            class="menu-title"
          >
            Dashboard
          </p>

          <router-link
            to="/staff/dashboard"
            :class="menuClass('/staff/dashboard')"
          >
            <span>📊</span>
            <span>Dashboard</span>
          </router-link>
        </div>

        <!-- POS -->
        <div>
          <p class="menu-title">
            POS & Transactions
          </p>

          <router-link
            to="/staff/pos"
            :class="menuClass('/staff/pos')"
          >
            <span>🖥️</span>
            <span>POS Kasir</span>
          </router-link>

          <router-link
            to="/staff/transactions"
            :class="menuClass('/staff/transactions')"
          >
            <span>💰</span>
            <span>Transactions</span>
          </router-link>

          <router-link
            to="/staff/daily-report"
            :class="menuClass('/staff/daily-report')"
          >
            <span>📋</span>
            <span>Laporan Harian</span>
          </router-link>
        </div>

        <!-- Inventory -->
        <div>
          <p class="menu-title">
            Inventory
          </p>

          <router-link
            to="/staff/medicines"
            :class="menuClass('/staff/medicines')"
          >
            <span>💊</span>
            <span>Obat</span>
          </router-link>

          <router-link
            to="/staff/categories"
            :class="menuClass('/staff/categories')"
          >
            <span>📂</span>
            <span>Kategori</span>
          </router-link>

          <router-link
            to="/staff/stocks"
            :class="menuClass('/staff/stocks')"
          >
            <span>📦</span>
            <span>Stok</span>
          </router-link>

          <router-link
            to="/staff/warehouses"
            :class="menuClass('/staff/warehouses')"
          >
            <span>🏢</span>
            <span>Gudang</span>
          </router-link>

          <router-link
            to="/staff/shelves"
            :class="menuClass('/staff/shelves')"
          >
            <span>🗂️</span>
            <span>Rak</span>
          </router-link>
        </div>

        <!-- Purchase -->
        <div>
          <p class="menu-title">
            Purchase
          </p>

          <router-link
            to="/staff/purchases"
            :class="menuClass('/staff/purchases')"
          >
            <span>🛒</span>
            <span>Pembelian</span>
          </router-link>

          <router-link
            to="/staff/suppliers"
            :class="menuClass('/staff/suppliers')"
          >
            <span>🏭</span>
            <span>Supplier</span>
          </router-link>
        </div>

        <!-- Management -->
        <div>
          <p class="menu-title">
            Management
          </p>

          <router-link
            to="/staff/orders"
            :class="menuClass('/staff/orders')"
          >
            <span>📦</span>
            <span>Orders</span>
          </router-link>

          <router-link
            to="/staff/employees"
            :class="menuClass('/staff/employees')"
          >
            <span>👥</span>
            <span>Karyawan</span>
          </router-link>

          <router-link
            to="/staff/attendance"
            :class="menuClass('/staff/attendance')"
          >
            <span>✅</span>
            <span>Kehadiran</span>
          </router-link>

          <router-link
            to="/staff/promotions"
            :class="menuClass('/staff/promotions')"
          >
            <span>🎉</span>
            <span>Promosi</span>
          </router-link>

          <router-link
            to="/staff/vouchers"
            :class="menuClass('/staff/vouchers')"
          >
            <span>🎟️</span>
            <span>Voucher</span>
          </router-link>
        </div>
      </nav>

      <!-- Logout -->
      <div
        class="border-t border-slate-200 p-5"
      >
        <button
          @click="logout"
          class="w-full rounded-3xl bg-red-50 text-red-600 py-4 font-semibold hover:bg-red-100 transition"
        >
          🚪 Logout
        </button>
      </div>
    </aside>

    <!-- MAIN -->
   <div
  :class="[
    'flex-1 flex flex-col transition-all duration-300 min-w-0 w-full',
    sidebarOpen
      ? 'ml-72'
      : 'ml-0'
  ]"
>

      <!-- HEADER -->
      <header
        class="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-slate-200 px-8 py-5 flex items-center justify-between"
      >
      <div class="flex items-center gap-4">
  <button
    @click="sidebarOpen = !sidebarOpen"
    class="w-12 h-12 rounded-2xl border border-slate-200 bg-white hover:bg-slate-100 transition flex items-center justify-center text-xl shadow-sm"
  >
    {{ sidebarOpen ? '☰' : '➡️' }}
  </button>

  <div>
    <h1
      class="text-3xl font-bold text-slate-900"
    >
      {{ currentPageTitle }}
    </h1>

    <p class="text-sm text-slate-500 mt-1">
      Welcome back 👋
    </p>
  </div>
</div>

        <div class="flex items-center gap-4">

          <!-- Notification -->
          <div class="relative">
            <button
              @click="toggleNotifications"
              class="relative w-14 h-14 rounded-3xl border border-slate-200 bg-white hover:bg-slate-50 transition flex items-center justify-center text-xl shadow-sm"
            >
              🔔

              <span
                v-if="unreadNotifications > 0"
                class="absolute -top-1 -right-1 min-w-[22px] h-[22px] px-2 rounded-full bg-red-500 text-white text-[11px] font-bold flex items-center justify-center"
              >
                {{
                  unreadNotifications > 99
                    ? '99+'
                    : unreadNotifications
                }}
              </span>
            </button>

            <!-- DROPDOWN -->
            <transition name="dropdown">
              <div
                v-if="showNotificationsDropdown"
                class="absolute right-0 mt-4 w-[430px] bg-white rounded-[32px] border border-slate-200 shadow-2xl overflow-hidden"
              >
                <div
                  class="px-6 py-5 border-b border-slate-100 flex items-center justify-between"
                >
                  <div>
                    <h3
                      class="font-bold text-slate-800 text-lg"
                    >
                      Notifications
                    </h3>

                    <p
                      class="text-xs text-slate-500"
                    >
                      Latest updates
                    </p>
                  </div>

                  <button
                    @click="markAllNotificationsAsRead"
                    class="text-emerald-600 text-sm font-semibold"
                  >
                    Mark all read
                  </button>
                </div>

                <div
                  class="max-h-[450px] overflow-y-auto"
                >
                  <div
                    v-if="notifications.length === 0"
                    class="p-10 text-center text-slate-400"
                  >
                    Tidak ada notifikasi
                  </div>

                  <button
                    v-for="notif in notifications"
                    :key="notif.id"
                    @click="handleNotificationClick(notif)"
                    class="w-full px-6 py-5 border-b border-slate-100 hover:bg-slate-50 transition flex gap-4 text-left"
                  >
                    <div
                      class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-xl"
                    >
                      {{
                        getNotificationIcon(
                          notif.type
                        )
                      }}
                    </div>

                    <div class="flex-1">
                      <div
                        class="flex justify-between gap-3"
                      >
                        <h4
                          class="font-semibold text-slate-800"
                        >
                          {{ notif.title }}
                        </h4>

                        <span
                          class="text-xs text-slate-400 whitespace-nowrap"
                        >
                          {{
                            formatTime(
                              notif.created_at
                            )
                          }}
                        </span>
                      </div>

                      <p
                        class="text-sm text-slate-500 mt-1"
                      >
                        {{ notif.message }}
                      </p>
                    </div>
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </header>

      <!-- PAGE -->
      <main class="flex-1 overflow-x-auto overflow-y-auto p-8 w-full min-w-0">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import {
  ref,
  computed,
  onMounted,
  onUnmounted,
  watch
} from 'vue'

import {
  useRoute,
  useRouter
} from 'vue-router'

import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import 'dayjs/locale/id'

dayjs.extend(relativeTime)
dayjs.locale('id')

/* --------------------------------
   ROUTER & STORE
-------------------------------- */

const authStore =
  useAuthStore()

const route =
  useRoute()

const router =
  useRouter()

/* --------------------------------
   STATE
-------------------------------- */

const notifications =
  ref([])

const unreadNotifications =
  ref(0)

const showNotificationsDropdown =
  ref(false)

let notificationInterval =
  null

const sidebarOpen = ref(true)

/* --------------------------------
   PAGE TITLE
-------------------------------- */

const currentPageTitle =
  computed(() => {
    const titles = {
      dashboard:
        'Dashboard',

      pos:
        'POS Kasir',

      transactions:
        'Riwayat Transaksi',

      'daily-report':
        'Laporan Harian',

      medicines:
        'Manajemen Obat',

      categories:
        'Manajemen Kategori',

      stocks:
        'Manajemen Stok',

      warehouses:
        'Manajemen Gudang',

      shelves:
        'Manajemen Rak',

      purchases:
        'Manajemen Pembelian',

      suppliers:
        'Manajemen Supplier',

      orders:
        'Manajemen Pesanan',

      employees:
        'Manajemen Karyawan',

      attendance:
        'Manajemen Kehadiran',

      promotions:
        'Manajemen Promosi',

      vouchers:
        'Manajemen Voucher',

      sales:
        'Laporan Penjualan',

      profit:
        'Laporan Profit',

      inventory:
        'Laporan Inventory',

      notifications:
        'Notifikasi'
    }

    const parts =
      route.path.split('/')

    const lastPart =
      parts[
        parts.length - 1
      ]

    return (
      titles[lastPart] ||
      'Staff Dashboard'
    )
  })

/* --------------------------------
   ACTIVE MENU
-------------------------------- */

const menuClass = (
  path
) => {
  return route.path.startsWith(
    path
  )
    ? `
      flex items-center gap-3
      rounded-2xl px-4 py-3
      bg-emerald-500
      text-white
      font-medium
      shadow-sm
      transition-all
    `
    : `
      flex items-center gap-3
      rounded-2xl px-4 py-3
      text-slate-700
      hover:bg-slate-100
      transition-all
    `
}

/* --------------------------------
   DEPARTMENT LABEL
-------------------------------- */

const getDepartmentLabel =
  (dept) => {
    const labels = {
      kasir:
        '💵 Kasir',

      inventory:
        '📦 Inventory',

      admin:
        '⚙️ Admin'
    }

    return (
      labels[dept] ||
      'Staff'
    )
  }

/* --------------------------------
   FORMAT TIME
-------------------------------- */

const formatTime = (
  time
) => {
  return dayjs(time)
    .fromNow()
}

/* --------------------------------
   NOTIFICATION ICON
-------------------------------- */

const getNotificationIcon =
  (type) => {
    const icons = {
      stock_warning:
        '⚠️',

      low_stock:
        '🟡',

      expired:
        '❌',

      purchase:
        '🛒',

      order:
        '📦',

      payment:
        '💳',

      system:
        'ℹ️',

      info:
        'ℹ️'
    }

    return (
      icons[type] ||
      '🔔'
    )
  }

/* --------------------------------
   FETCH NOTIFICATIONS
-------------------------------- */

const fetchNotifications =
  async () => {
    try {
      const response =
        await api.get(
          'notifications',
          {
            params: {
              per_page: 10,
              order_by:
                'desc'
            }
          }
        )

      notifications.value =
        response.data.data
          ?.data || []

      unreadNotifications.value =
        response.data
          .unread_count || 0
    } catch (error) {
      console.error(
        'Failed fetch notifications:',
        error
      )
    }
  }

/* --------------------------------
   TOGGLE DROPDOWN
-------------------------------- */

const toggleNotifications =
  () => {
    showNotificationsDropdown.value =
      !showNotificationsDropdown.value

    if (
      showNotificationsDropdown.value
    ) {
      fetchNotifications()
    }
  }

/* --------------------------------
   MARK READ
-------------------------------- */

const markNotificationAsRead =
  async (
    notifId
  ) => {
    try {
      await api.post(
        `notifications/${notifId}/read`
      )

      fetchNotifications()
    } catch (error) {
      console.error(
        error
      )
    }
  }

/* --------------------------------
   MARK ALL READ
-------------------------------- */

const markAllNotificationsAsRead =
  async () => {
    try {
      await api.post(
        'notifications/mark-all-read'
      )

      fetchNotifications()
    } catch (error) {
      console.error(
        error
      )
    }
  }

/* --------------------------------
   HANDLE CLICK
-------------------------------- */

const handleNotificationClick =
  (notif) => {
    if (
      !notif.is_read
    ) {
      markNotificationAsRead(
        notif.id
      )
    }

    switch (
      notif.type
    ) {
      case 'low_stock':
      case 'stock_warning':
        router.push(
          '/staff/stocks'
        )
        break

      case 'purchase':
        router.push(
          '/staff/purchases'
        )
        break

      case 'order':
        router.push(
          '/staff/orders'
        )
        break
    }

    showNotificationsDropdown.value =
      false
  }

/* --------------------------------
   LOGOUT
-------------------------------- */

const logout = async () => {
  try {
    await api.post('/auth/logout')
  } catch (error) {
    console.warn('Logout API gagal, force logout')
  } finally {
    // tetap hapus session
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    localStorage.removeItem('role')

    router.push('/login')
  }
}
/* --------------------------------
   LIFECYCLE
-------------------------------- */

onMounted(() => {
  fetchNotifications()

  notificationInterval =
    setInterval(
      () => {
        fetchNotifications()
      },
      30000
    )
})

onUnmounted(() => {
  if (
    notificationInterval
  ) {
    clearInterval(
      notificationInterval
    )
  }
})

/* --------------------------------
   CLOSE DROPDOWN
-------------------------------- */

watch(
  () => route.path,
  () => {
    showNotificationsDropdown.value =
      false
  }
)
</script>

<style scoped>
/* =========================
   SCROLLBAR
========================= */

::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 999px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* =========================
   SIDEBAR MENU TITLE
========================= */

.menu-title {
  @apply px-4 mb-3 text-[11px]
  font-bold uppercase tracking-[0.2em]
  text-slate-400;
}

/* =========================
   MENU ANIMATION
========================= */

nav a {
  position: relative;
  overflow: hidden;
}

nav a::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    rgba(16, 185, 129, 0.08),
    rgba(16, 185, 129, 0)
  );
  opacity: 0;
  transition: 0.3s ease;
}

nav a:hover::before {
  opacity: 1;
}

/* =========================
   HEADER GLASS EFFECT
========================= */

header {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* =========================
   DROPDOWN ANIMATION
========================= */

.dropdown-enter-active,
.dropdown-leave-active {
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform:
    translateY(-10px)
    scale(0.98);
}

/* =========================
   NOTIFICATION CARD
========================= */

.notification-card {
  transition:
    background 0.25s ease,
    transform 0.25s ease;
}

.notification-card:hover {
  transform: translateY(-2px);
}

/* =========================
   SIDEBAR SHADOW
========================= */

aside {
  box-shadow:
    0 1px 2px rgba(0,0,0,0.04),
    0 8px 30px rgba(15,23,42,0.04);
}

/* =========================
   PREMIUM BUTTON
========================= */

button {
  transition:
    all 0.25s ease;
}

button:active {
  transform: scale(0.97);
}

/* =========================
   PAGE TRANSITION
========================= */

main {
  animation:
    fadeIn 0.35s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform:
      translateY(8px);
  }

  to {
    opacity: 1;
    transform:
      translateY(0);
  }
}

/* =========================
   USER AVATAR GLOW
========================= */

.avatar-glow {
  box-shadow:
    0 10px 25px
    rgba(16, 185, 129, 0.2);
}

/* =========================
   PREMIUM CARD
========================= */

.card-premium {
  background:
    linear-gradient(
      180deg,
      rgba(255,255,255,1),
      rgba(248,250,252,1)
    );

  border:
    1px solid
    rgba(226,232,240,1);

  box-shadow:
    0 10px 40px
    rgba(15,23,42,0.04);
}

/* =========================
   MOBILE
========================= */

@media (max-width: 1024px) {
  aside {
    width: 260px;
  }

  .ml-72 {
    margin-left: 260px;
  }
}

@media (max-width: 768px) {
  aside {
    transform: translateX(-100%);
  }

  .ml-72 {
    margin-left: 0;
  }

  header {
    padding-left: 20px;
    padding-right: 20px;
  }

  main {
    padding: 20px;
  }
}
</style>