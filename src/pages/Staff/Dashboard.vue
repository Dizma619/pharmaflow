```vue
<template>
  <div class="space-y-8">

    <!-- HERO -->
    <section
      class="relative overflow-hidden rounded-[32px] border border-emerald-100 bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-700 p-8 text-white shadow-xl"
    >
      <div
        class="absolute -right-10 -top-10 h-52 w-52 rounded-full bg-white/10 blur-3xl"
      />

      <div
        class="absolute -bottom-16 -left-10 h-52 w-52 rounded-full bg-white/10 blur-3xl"
      />

      <div
        class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between"
      >
        <div>
          <p
            class="mb-3 text-sm font-medium text-emerald-100"
          >
            STAFF DASHBOARD
          </p>

          <h1
            class="text-4xl font-bold tracking-tight"
          >
            Halo,
            {{
              authStore.user?.name
            }}
            👋
          </h1>

          <p
            class="mt-3 max-w-2xl text-emerald-100"
          >
            Monitor penjualan,
            inventory, attendance
            dan performa apotek
            secara real-time.
          </p>
        </div>

        <!-- QUICK STATUS -->
        <div
          class="grid grid-cols-2 gap-4"
        >
          <div
            class="rounded-3xl border border-white/20 bg-white/10 px-6 py-5 backdrop-blur-xl"
          >
            <p
              class="text-sm text-emerald-100"
            >
              Hari Ini
            </p>

            <h3
              class="mt-2 text-2xl font-bold"
            >
              {{
                dayjs().format(
                  'DD MMM'
                )
              }}
            </h3>
          </div>

          <div
            class="rounded-3xl border border-white/20 bg-white/10 px-6 py-5 backdrop-blur-xl"
          >
            <p
              class="text-sm text-emerald-100"
            >
              Staff Aktif
            </p>

            <h3
              class="mt-2 text-2xl font-bold"
            >
            {{ summary?.employees?.active }}
            </h3>
          </div>
        </div>
      </div>
    </section>

    <!-- FILTER -->
    <section
      class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm"
    >
      <div
        class="flex flex-col gap-4 xl:flex-row xl:items-end"
      >
        <div class="flex-1">
          <label
            class="mb-2 block text-sm font-semibold text-slate-600"
          >
            Dari Tanggal
          </label>

          <input
            v-model="startDate"
            type="date"
            class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 outline-none transition focus:border-emerald-400 focus:bg-white"
          />
        </div>

        <div class="flex-1">
          <label
            class="mb-2 block text-sm font-semibold text-slate-600"
          >
            Sampai Tanggal
          </label>

          <input
            v-model="endDate"
            type="date"
            class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 outline-none transition focus:border-emerald-400 focus:bg-white"
          />
        </div>

        <button
          @click="
            fetchDashboard
          "
          class="h-14 rounded-2xl bg-emerald-500 px-8 font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-1 hover:bg-emerald-600"
        >
          🔍 Filter Data
        </button>
      </div>
    </section>

    <!-- LOADING -->
    <div
      v-if="loading"
      class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4"
    >
      <div
        v-for="i in 4"
        :key="i"
        class="h-40 animate-pulse rounded-[32px] bg-slate-200"
      />
    </div>

    <!-- CONTENT -->
    <div
      v-else
      class="space-y-8"
    >

      <!-- STATS -->
      <section
        class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4"
      >

        <!-- SALES TODAY -->
        <div
          class="card-premium rounded-[32px] p-6"
        >
          <div
            class="mb-6 flex items-center justify-between"
          >
            <div
              class="rounded-2xl bg-emerald-100 p-4 text-2xl"
            >
              💰
            </div>

            <span
              class="rounded-full bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-600"
            >
              Today
            </span>
          </div>

          <p
            class="text-sm text-slate-500"
          >
            Penjualan Hari Ini
          </p>

          <h2
            class="mt-3 text-3xl font-bold text-slate-900"
          >
            Rp{{
              formatPrice(
                summary.sales
                  .today_sales
              )
            }}
          </h2>

          <p
            class="mt-2 text-sm text-slate-500"
          >
            {{
              summary.sales
                .today_transactions
            }}
            transaksi
          </p>
        </div>

        <!-- TOTAL SALES -->
        <div
          class="card-premium rounded-[32px] p-6"
        >
          <div
            class="mb-6 flex items-center justify-between"
          >
            <div
              class="rounded-2xl bg-blue-100 p-4 text-2xl"
            >
              📈
            </div>

            <span
              class="rounded-full bg-blue-50 px-4 py-2 text-xs font-semibold text-blue-600"
            >
              Revenue
            </span>
          </div>

          <p
            class="text-sm text-slate-500"
          >
            Total Penjualan
          </p>

          <h2
            class="mt-3 text-3xl font-bold text-slate-900"
          >
            Rp{{
              formatPrice(
                summary.sales
                  .total_revenue
              )
            }}
          </h2>

          <p
            class="mt-2 text-sm text-slate-500"
          >
            {{
              summary.sales
                .total_orders
            }}
            pesanan
          </p>
        </div>

        <!-- LOW STOCK -->
        <div
          class="card-premium rounded-[32px] p-6"
        >
          <div
            class="mb-6 flex items-center justify-between"
          >
            <div
              class="rounded-2xl bg-orange-100 p-4 text-2xl"
            >
              📦
            </div>

            <span
              class="rounded-full bg-orange-50 px-4 py-2 text-xs font-semibold text-orange-600"
            >
              Warning
            </span>
          </div>

          <p
            class="text-sm text-slate-500"
          >
            Stok Menipis
          </p>

          <h2
            class="mt-3 text-3xl font-bold text-slate-900"
          >
            {{
              summary.inventory
                .low_stock_count
            }}
          </h2>
        </div>

        <!-- EMPLOYEE -->
        <div
          class="card-premium rounded-[32px] p-6"
        >
          <div
            class="mb-6 flex items-center justify-between"
          >
            <div
              class="rounded-2xl bg-purple-100 p-4 text-2xl"
            >
              👥
            </div>

            <span
              class="rounded-full bg-purple-50 px-4 py-2 text-xs font-semibold text-purple-600"
            >
              Employee
            </span>
          </div>

          <p
            class="text-sm text-slate-500"
          >
            Karyawan Aktif
          </p>

          <h2
            class="mt-3 text-3xl font-bold text-slate-900"
          >
            {{
              summary.employees
                .active
            }}
          </h2>

          <p
            class="mt-2 text-sm text-slate-500"
          >
            {{
              attendance.present
            }}
            hadir hari ini
          </p>
        </div>
      </section>

      <!-- QUICK ACTION -->
      <section
        class="card-premium rounded-[32px] p-8"
      >
        <div
          class="mb-6 flex items-center justify-between"
        >
          <div>
            <h2
              class="text-2xl font-bold text-slate-900"
            >
              ⚡ Quick Action
            </h2>

            <p
              class="text-slate-500"
            >
              Akses cepat menu
              staff
            </p>
          </div>
        </div>

        <div
          class="grid grid-cols-2 gap-5 md:grid-cols-3 xl:grid-cols-6"
        >
          <router-link
            to="/staff/pos"
            class="quick-card"
          >
            🖥️
            <span>POS</span>
          </router-link>

          <router-link
            to="/staff/medicines"
            class="quick-card"
          >
            💊
            <span>Obat</span>
          </router-link>

          <router-link
            to="/staff/stocks"
            class="quick-card"
          >
            📦
            <span>Stok</span>
          </router-link>

          <router-link
            to="/staff/purchases"
            class="quick-card"
          >
            🛒
            <span>Pembelian</span>
          </router-link>

          <router-link
            to="/staff/employees"
            class="quick-card"
          >
            👥
            <span>Karyawan</span>
          </router-link>

          <router-link
            to="/staff/reports/sales"
            class="quick-card"
          >
            📊
            <span>Laporan</span>
          </router-link>
        </div>
      </section>
    </div>
  </div>
</template>
```


```vue
<script setup>
import {
  ref,
  onMounted
} from 'vue'

import api from '@/services/api'
import dayjs from 'dayjs'

import {
  useAuthStore
} from '@/stores/auth'

/* ==========================
   STORE
========================== */

const authStore =
  useAuthStore()

/* ==========================
   DATE FILTER
========================== */

const startDate =
  ref(
    dayjs()
      .startOf('month')
      .format(
        'YYYY-MM-DD'
      )
  )

const endDate =
  ref(
    dayjs().format(
      'YYYY-MM-DD'
    )
  )

/* ==========================
   STATE
========================== */

const loading =
  ref(false)

const summary =
  ref({
    sales: {
      today_sales: 0,
      today_transactions: 0,
      total_revenue: 0,
      total_orders: 0
    },

    inventory: {
      total_medicines: 0,
      low_stock_count: 0,
      expired_count: 0,
      out_of_stock: 0,
      expiring_soon: 0
    },

    employees: {
      active: 0
    }
  })

const attendance =
  ref({
    present: 0,
    absent: 0,
    sick: 0,
    leave: 0
  })

/* ==========================
   FORMAT PRICE
========================== */

const formatPrice =
  (price) => {
    return new Intl.NumberFormat(
      'id-ID'
    ).format(
      price || 0
    )
  }

/* ==========================
   FETCH DASHBOARD
========================== */

const fetchDashboard =
  async () => {
    loading.value = true

    try {
      const response =
        await api.get(
          '/dashboard/summary',
          {
            params: {
              start_date:
                startDate.value,
              end_date:
                endDate.value
            }
          }
        )

      console.log(
        'DASHBOARD RESPONSE:',
        response.data
      )

      if (
        response.data &&
        response.data.data
      ) {
        summary.value =
          response.data.data
      }

    } catch (error) {
      console.error(
        'Dashboard Error:',
        error.response?.data ||
        error
      )
    } finally {
      loading.value =
        false
    }
  }

const fetchAttendance =
  async () => {
    try {
      const response =
        await api.get(
          '/attendance/today'
        )

      console.log(
        'ATTENDANCE RESPONSE:',
        response.data
      )

      attendance.value =
        response.data?.data?.summary ??
        response.data?.summary ??
        {
          present: 0,
          absent: 0,
          sick: 0,
          leave: 0
        }

    } catch (
      error
    ) {
      console.error(
        'Attendance Error:',
        error.response?.data ||
        error.message
      )

      attendance.value = {
        present: 0,
        absent: 0,
        sick: 0,
        leave: 0
      }
    }
  }
/* ==========================
   REFRESH DASHBOARD
========================== */

const refreshDashboard =
  async () => {
    await Promise.all([
      fetchDashboard(),
      fetchAttendance()
    ])
  }

/* ==========================
   LIFECYCLE
========================== */

onMounted(() => {
  refreshDashboard()
})
</script>
```
```vue id="stfstyle09"
<style scoped>
/* ==========================
   PAGE ANIMATION
========================== */

main {
  animation:
    fadeIn 0.35s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform:
      translateY(10px);
  }

  to {
    opacity: 1;
    transform:
      translateY(0);
  }
}

/* ==========================
   PREMIUM CARD
========================== */

.card-premium {
  position: relative;
  overflow: hidden;

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
    0 1px 2px
      rgba(
        15,
        23,
        42,
        0.03
      ),
    0 20px 40px
      rgba(
        15,
        23,
        42,
        0.04
      );

  transition:
    all 0.3s ease;
}

.card-premium:hover {
  transform:
    translateY(-6px);

  box-shadow:
    0 30px 60px
      rgba(
        15,
        23,
        42,
        0.08
      );
}

/* ==========================
   SHINE EFFECT
========================== */

.card-premium::before {
  content: '';

  position: absolute;
  top: -100%;
  left: -100%;

  width: 300px;
  height: 300px;

  background:
    radial-gradient(
      circle,
      rgba(
        255,
        255,
        255,
        0.4
      ),
      transparent
    );

  transition:
    all 0.6s ease;
}

.card-premium:hover::before {
  top: -40%;
  left: -20%;
}

/* ==========================
   QUICK ACTION
========================== */

.quick-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  gap: 12px;

  min-height: 150px;

  border-radius: 28px;

  background:
    linear-gradient(
      180deg,
      #ffffff,
      #f8fafc
    );

  border:
    1px solid
    #e2e8f0;

  box-shadow:
    0 10px 30px
    rgba(
      15,
      23,
      42,
      0.04
    );

  font-weight: 600;
  color: #0f172a;

  transition:
    all 0.28s ease;
}

.quick-card:hover {
  transform:
    translateY(-6px);

  border-color:
    #10b981;

  background:
    linear-gradient(
      180deg,
      #ecfdf5,
      #ffffff
    );

  box-shadow:
    0 25px 50px
    rgba(
      16,
      185,
      129,
      0.15
    );
}

.quick-card span:first-child {
  font-size: 40px;
}

.quick-card span {
  font-size: 15px;
}

/* ==========================
   FILTER SECTION
========================== */

input[type='date'] {
  transition:
    all 0.25s ease;
}

input[type='date']:focus {
  box-shadow:
    0 0 0 4px
    rgba(
      16,
      185,
      129,
      0.1
    );
}

/* ==========================
   BUTTON EFFECT
========================== */

button {
  transition:
    all 0.25s ease;
}

button:hover {
  transform:
    translateY(-2px);
}

button:active {
  transform:
    scale(0.98);
}

/* ==========================
   LOADING CARD
========================== */

.animate-pulse {
  border-radius: 32px;
  background:
    linear-gradient(
      90deg,
      #e2e8f0 25%,
      #f1f5f9 50%,
      #e2e8f0 75%
    );

  background-size:
    400% 100%;

  animation:
    shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% {
    background-position:
      100% 0;
  }

  100% {
    background-position:
      -100% 0;
  }
}

/* ==========================
   HERO SECTION
========================== */

section:first-child {
  box-shadow:
    0 20px 50px
    rgba(
      16,
      185,
      129,
      0.18
    );
}

/* ==========================
   RESPONSIVE
========================== */

@media (
  max-width: 1024px
) {
  .quick-card {
    min-height: 120px;
  }
}

@media (
  max-width: 768px
) {
  h1 {
    font-size: 30px;
  }

  .quick-card {
    min-height: 100px;
    border-radius: 24px;
  }

  .card-premium {
    border-radius: 24px;
  }
}
</style>
```
