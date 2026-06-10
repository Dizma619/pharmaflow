<template>
  <div class="flex h-screen bg-slate-50 overflow-hidden">

    <!-- SIDEBAR -->
    <aside
      class="fixed left-0 top-0 z-50 h-screen w-72 bg-white border-r border-slate-200 flex flex-col"
    >
      <!-- Logo -->
      <div class="px-6 pt-7 pb-5 border-b border-slate-100">
        <div class="flex items-center gap-4">
          <div
            class="w-14 h-14 rounded-3xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg flex items-center justify-center text-white text-2xl"
          >
            👑
          </div>

          <div>
            <h1 class="text-xl font-bold text-slate-900">
              PharmaFlow
            </h1>

            <p class="text-sm text-slate-500">
              Owner Dashboard
            </p>
          </div>
        </div>

        <!-- OWNER CARD -->
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
                👑 Full Access Owner
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
          <p class="menu-title">
            Dashboard
          </p>

          <router-link
            to="/owner/dashboard"
            :class="
              menuClass('/owner/dashboard')
            "
          >
            <span>📊</span>
            <span>Dashboard</span>
          </router-link>

          <router-link
            to="/owner/analytics"
            :class="
              menuClass('/owner/analytics')
            "
          >
            <span>📉</span>
            <span>Analytics</span>
          </router-link>
        </div>

        <!-- Reports -->
        <div>
          <p class="menu-title">
            Reports
          </p>

          <router-link
            to="/owner/reports/sales"
            :class="
              menuClass('/owner/reports/sales')
            "
          >
            <span>📈</span>
            <span>Sales Report</span>
          </router-link>

          <router-link
            to="/owner/reports/profit"
            :class="
              menuClass('/owner/reports/profit')
            "
          >
            <span>💹</span>
            <span>Profit Report</span>
          </router-link>

          <router-link
            to="/owner/reports/financial"
            :class="
              menuClass('/owner/reports/financial')
            "
          >
            <span>💰</span>
            <span>Financial Report</span>
          </router-link>

          <router-link
            to="/owner/reports/cashflow"
            :class="
              menuClass('/owner/reports/cashflow')
            "
          >
            <span>💵</span>
            <span>Cashflow</span>
          </router-link>
        </div>

        <!-- Inventory -->
        <div>
          <p class="menu-title">
            Inventory
          </p>

          <router-link
            to="/owner/medicines"
            :class="
              menuClass('/owner/medicines')
            "
          >
            <span>💊</span>
            <span>Medicines</span>
          </router-link>

          <router-link
            to="/owner/categories"
            :class="
              menuClass('/owner/categories')
            "
          >
            <span>📂</span>
            <span>Categories</span>
          </router-link>

          <router-link
            to="/owner/stocks"
            :class="
              menuClass('/owner/stocks')
            "
          >
            <span>📦</span>
            <span>Stocks</span>
          </router-link>

          <router-link
            to="/owner/purchases"
            :class="
              menuClass('/owner/purchases')
            "
          >
            <span>🛒</span>
            <span>Purchases</span>
          </router-link>

          <router-link
            to="/owner/suppliers"
            :class="
              menuClass('/owner/suppliers')
            "
          >
            <span>🏭</span>
            <span>Suppliers</span>
          </router-link>
        </div>

        <!-- Management -->
        <div>
          <p class="menu-title">
            Management
          </p>

          <router-link
            to="/owner/orders"
            :class="
              menuClass('/owner/orders')
            "
          >
            <span>📦</span>
            <span>Orders</span>
          </router-link>

          <router-link
            to="/owner/employees"
            :class="
              menuClass('/owner/employees')
            "
          >
            <span>👥</span>
            <span>Employees</span>
          </router-link>

          <router-link
            to="/owner/promotions"
            :class="
              menuClass('/owner/promotions')
            "
          >
            <span>🎉</span>
            <span>Promotions</span>
          </router-link>

          <router-link
            to="/owner/vouchers"
            :class="
              menuClass('/owner/vouchers')
            "
          >
            <span>🎟️</span>
            <span>Vouchers</span>
          </router-link>

          <router-link
            to="/owner/notifications"
            :class="
              menuClass('/owner/notifications')
            "
          >
            <span>🔔</span>
            <span>Notifications</span>

            <span
              v-if="
                unreadNotifications > 0
              "
              class="ml-auto min-w-[22px] h-[22px] px-2 rounded-full bg-red-500 text-white text-[11px] font-bold flex items-center justify-center"
            >
              {{
                unreadNotifications > 99
                  ? '99+'
                  : unreadNotifications
              }}
            </span>
          </router-link>
        </div>
      </nav>

      <!-- LOGOUT -->
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
      class="ml-72 flex-1 flex flex-col"
    >

      <!-- HEADER -->
      <header
        class="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-slate-200 px-8 py-5 flex items-center justify-between"
      >
        <div>
          <h1
            class="text-3xl font-bold text-slate-900"
          >
            {{ currentPageTitle }}
          </h1>

          <p
            class="text-sm text-slate-500 mt-1"
          >
            Welcome back Owner 👋
          </p>
        </div>

        <div
          class="flex items-center gap-4"
        >
          <!-- Notification -->
          <div class="relative">
            <button
              @click="
                toggleNotifications
              "
              class="relative w-14 h-14 rounded-3xl border border-slate-200 bg-white hover:bg-slate-50 transition flex items-center justify-center text-xl shadow-sm"
            >
              🔔

              <span
                v-if="
                  unreadNotifications > 0
                "
                class="absolute -top-1 -right-1 min-w-[22px] h-[22px] px-2 rounded-full bg-red-500 text-white text-[11px] font-bold flex items-center justify-center"
              >
                {{
                  unreadNotifications > 99
                    ? '99+'
                    : unreadNotifications
                }}
              </span>
            </button>
          </div>

          <!-- User -->
          <div
            class="flex items-center gap-3 rounded-3xl border border-slate-200 px-4 py-2 bg-white"
          >
            <div>
              <p
                class="font-semibold text-slate-800"
              >
                {{
                  authStore.user?.name
                }}
              </p>

              <p
                class="text-xs text-slate-500"
              >
                👑 Owner
              </p>
            </div>

            <div
              class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-bold"
            >
              {{
                authStore.user?.name
                  ?.charAt(0)
                  .toUpperCase()
              }}
            </div>
          </div>
        </div>
      </header>

      <!-- CONTENT -->
      <main
        class="flex-1 overflow-y-auto p-8"
      >
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

/* ============================
   STORE & ROUTER
============================ */

const authStore =
  useAuthStore()

const route =
  useRoute()

const router =
  useRouter()

/* ============================
   STATE
============================ */

const notifications =
  ref([])

const unreadNotifications =
  ref(0)

const showNotificationsDropdown =
  ref(false)

let notificationInterval =
  null

/* ============================
   PAGE TITLE
============================ */

const currentPageTitle =
  computed(() => {
    const titles = {
      dashboard:
        'Dashboard',

      sales:
        'Sales Report',

      profit:
        'Profit Report',

      financial:
        'Financial Report',

      cashflow:
        'Cashflow Report',

      analytics:
        'Analytics',

      medicines:
        'Medicines',

      categories:
        'Categories',

      suppliers:
        'Suppliers',

      warehouses:
        'Warehouses',

      stocks:
        'Stocks',

      purchases:
        'Purchases',

      orders:
        'Orders',

      transactions:
        'Transactions',

      employees:
        'Employees',

      promotions:
        'Promotions',

      vouchers:
        'Vouchers',

      notifications:
        'Notifications'
    }

    const parts =
      route.path.split('/')

    const lastPart =
      parts[
        parts.length - 1
      ]

    return (
      titles[lastPart] ||
      'Owner Dashboard'
    )
  })

/* ============================
   MENU ACTIVE
============================ */

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

/* ============================
   FORMAT TIME
============================ */

const formatTime = (
  time
) => {
  return dayjs(time)
    .fromNow()
}

/* ============================
   NOTIFICATION ICON
============================ */

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

/* ============================
   FETCH NOTIFICATIONS
============================ */

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

/* ============================
   TOGGLE NOTIFICATION
============================ */

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

/* ============================
   MARK AS READ
============================ */

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
        'Failed mark notification:',
        error
      )
    }
  }

/* ============================
   MARK ALL READ
============================ */

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

/* ============================
   HANDLE CLICK
============================ */

const handleNotificationClick =
  (
    notif
  ) => {
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
          '/owner/stocks'
        )
        break

      case 'purchase':
        router.push(
          '/owner/purchases'
        )
        break

      case 'order':
        router.push(
          '/owner/orders'
        )
        break
    }

    showNotificationsDropdown.value =
      false
  }

/* ============================
   LOGOUT
============================ */

const logout =
  async () => {
    try {
      await api.post(
        'auth/logout'
      )

      authStore.logout()

      router.push(
        '/login'
      )
    } catch (
      error
    ) {
      console.error(
        'Logout failed:',
        error
      )
    }
  }

/* ============================
   LIFECYCLE
============================ */

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

/* ============================
   WATCH ROUTE
============================ */

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
   MENU TITLE
========================= */

.menu-title {
  @apply px-4 mb-3
  text-[11px]
  font-bold
  uppercase
  tracking-[0.2em]
  text-slate-400;
}

/* =========================
   SIDEBAR
========================= */

aside {
  box-shadow:
    0 1px 2px
      rgba(15, 23, 42, 0.04),
    0 20px 40px
      rgba(15, 23, 42, 0.05);
}

/* =========================
   SIDEBAR MENU EFFECT
========================= */

nav a {
  position: relative;
  overflow: hidden;
}

nav a::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      90deg,
      rgba(
        16,
        185,
        129,
        0.08
      ),
      rgba(
        16,
        185,
        129,
        0
      )
    );

  opacity: 0;
  transition: 0.25s ease;
}

nav a:hover::before {
  opacity: 1;
}

/* =========================
   HEADER GLASS EFFECT
========================= */

header {
  backdrop-filter:
    blur(24px);

  -webkit-backdrop-filter:
    blur(24px);
}

/* =========================
   BUTTON EFFECT
========================= */

button {
  transition:
    all 0.25s ease;
}

button:hover {
  transform:
    translateY(-1px);
}

button:active {
  transform:
    scale(0.98);
}

/* =========================
   NOTIFICATION BADGE
========================= */

.notification-badge {
  box-shadow:
    0 6px 20px
    rgba(
      239,
      68,
      68,
      0.35
    );
}

/* =========================
   USER CARD
========================= */

.user-card {
  background:
    linear-gradient(
      180deg,
      rgba(
        255,
        255,
        255,
        1
      ),
      rgba(
        248,
        250,
        252,
        1
      )
    );

  border:
    1px solid
    rgba(
      226,
      232,
      240,
      1
    );

  box-shadow:
    0 10px 30px
    rgba(
      15,
      23,
      42,
      0.04
    );
}

/* =========================
   OWNER CARD
========================= */

.owner-card {
  background:
    linear-gradient(
      135deg,
      rgba(
        236,
        253,
        245,
        1
      ),
      rgba(
        255,
        255,
        255,
        1
      )
    );

  border:
    1px solid
    rgba(
      167,
      243,
      208,
      1
    );

  box-shadow:
    0 12px 28px
    rgba(
      16,
      185,
      129,
      0.08
    );
}

/* =========================
   MAIN PAGE ANIMATION
========================= */

main {
  animation:
    fadeIn
    0.35s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform:
      translateY(
        10px
      );
  }

  to {
    opacity: 1;
    transform:
      translateY(
        0
      );
  }
}

/* =========================
   DROPDOWN
========================= */

.dropdown-enter-active,
.dropdown-leave-active {
  transition:
    opacity
      0.2s ease,
    transform
      0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform:
    translateY(
      -10px
    )
    scale(0.98);
}

/* =========================
   PREMIUM CARD
========================= */

.card-premium {
  background:
    linear-gradient(
      180deg,
      rgba(
        255,
        255,
        255,
        1
      ),
      rgba(
        248,
        250,
        252,
        1
      )
    );

  border:
    1px solid
    rgba(
      226,
      232,
      240,
      1
    );

  box-shadow:
    0 10px 40px
    rgba(
      15,
      23,
      42,
      0.04
    );
}

/* =========================
   MOBILE RESPONSIVE
========================= */

@media (
  max-width: 1024px
) {
  aside {
    width: 260px;
  }

  .ml-72 {
    margin-left:
      260px;
  }
}

@media (
  max-width: 768px
) {
  aside {
    transform:
      translateX(
        -100%
      );
  }

  .ml-72 {
    margin-left: 0;
  }

  header {
    padding-left:
      20px;

    padding-right:
      20px;
  }

  main {
    padding:
      20px;
  }
}
</style>