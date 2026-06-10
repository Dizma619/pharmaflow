import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // ========================================
  // AUTH ROUTES
  // ========================================
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/pages/auth/Login.vue'),
    meta: { requiresAuth: false },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/pages/auth/Register.vue'),
    meta: { requiresAuth: false },
  },

  // ========================================
  // CUSTOMER / PUBLIC ROUTES
  // ========================================
  {
    path: '/',
    name: 'Home',
    component: () => import('@/pages/customer/Home.vue'),
    meta: { requiresAuth: false },
  },

  {
    path: '/products',
    name: 'Products',
    component: () => import('@/pages/customer/Products.vue'),
    meta: { requiresAuth: false },
  },

  {
    path: '/products/:id',
    name: 'ProductDetail',
    component: () =>
      import('@/pages/customer/ProductDetail.vue'),
    meta: { requiresAuth: false },
  },

  // ========================================
  // CART & CHECKOUT
  // GUEST BOLEH AKSES
  // ========================================
  {
    path: '/cart',
    name: 'Cart',
    component: () =>
      import('@/pages/customer/Cart.vue'),
    meta: { requiresAuth: false },
  },

  {
    path: '/checkout',
    name: 'Checkout',
    component: () =>
      import('@/pages/customer/Checkout.vue'),
    meta: { requiresAuth: false },
  },

  {
    path: '/order-success',
    name: 'OrderSuccess',
    component: () =>
      import('@/pages/customer/OrderSuccess.vue'),
    meta: { requiresAuth: false },
  },

  {
    path: '/order-tracking',
    name: 'OrderTracking',
    component: () =>
      import('@/pages/customer/OrderTracking.vue'),
    meta: { requiresAuth: false },
  },

  // ========================================
  // CUSTOMER PROTECTED ROUTES
  // LOGIN CUSTOMER
  // ========================================
  {
    path: '/orders',
    name: 'CustomerOrders',
    component: () =>
      import('@/pages/customer/Orders.vue'),
    meta: {
      requiresAuth: true,
      role: 'customer',
    },
  },

  {
    path: '/orders/:id',
    name: 'OrderDetail',
    component: () =>
      import('@/pages/customer/OrderDetail.vue'),
    meta: {
      requiresAuth: true,
      role: 'customer',
    },
  },

  {
    path: '/profile',
    name: 'CustomerProfile',
    component: () =>
      import('@/pages/customer/Profile.vue'),
    meta: {
      requiresAuth: true,
      role: 'customer',
    },
  },

  // ========================================
  // STAFF ROUTES
  // ========================================
  {
    path: '/staff',
    name: 'StaffLayout',
    component: () =>
      import('@/layouts/StaffLayout.vue'),
    meta: {
      requiresAuth: true,
      role: 'staff',
    },

    children: [
      {
        path: 'dashboard',
        name: 'StaffDashboard',
        component: () =>
          import('@/pages/staff/Dashboard.vue'),
      },

      // POS
      {
        path: 'pos',
        name: 'POS',
        component: () =>
          import('@/pages/staff/POS.vue'),
      },

      {
        path: 'transactions',
        name: 'Transactions',
        component: () =>
          import('@/pages/staff/Transactions.vue'),
      },

      {
        path: 'daily-report',
        name: 'DailyReport',
        component: () =>
          import('@/pages/staff/DailyReport.vue'),
      },

      // Inventory
      {
        path: 'medicines',
        name: 'Medicines',
        component: () =>
          import('@/pages/staff/Medicines.vue'),
      },

      {
        path: 'categories',
        name: 'Categories',
        component: () =>
          import('@/pages/staff/Categories.vue'),
      },

      {
        path: 'suppliers',
        name: 'Suppliers',
        component: () =>
          import('@/pages/staff/Suppliers.vue'),
      },

      {
        path: 'warehouses',
        name: 'Warehouses',
        component: () =>
          import('@/pages/staff/Warehouses.vue'),
      },

      {
        path: 'shelves',
        name: 'Shelves',
        component: () =>
          import('@/pages/staff/Shelves.vue'),
      },

      {
        path: 'stocks',
        name: 'Stocks',
        component: () =>
          import('@/pages/staff/Stocks.vue'),
      },

      {
        path: 'purchases',
        name: 'Purchases',
        component: () =>
          import('@/pages/staff/Purchases.vue'),
      },

      // Management
      {
        path: 'employees',
        name: 'Employees',
        component: () =>
          import('@/pages/staff/Employees.vue'),
      },

      {
        path: 'attendance',
        name: 'Attendance',
        component: () =>
          import('@/pages/staff/Attendance.vue'),
      },

      {
        path: 'orders',
        name: 'StaffOrders',
        component: () =>
          import('@/pages/staff/Orders.vue'),
      },

      {
        path: 'promotions',
        name: 'Promotions',
        component: () =>
          import('@/pages/staff/Promotions.vue'),
      },

      {
        path: 'vouchers',
        name: 'Vouchers',
        component: () =>
          import('@/pages/staff/Vouchers.vue'),
      },

      {
        path: 'notifications',
        name: 'StaffNotifications',
        component: () =>
          import('@/pages/staff/Notifications.vue'),
      },

      // Reports
      {
        path: 'reports/sales',
        name: 'SalesReport',
        component: () =>
          import(
            '@/pages/staff/Reports/SalesReport.vue'
          ),
      },

      {
        path: 'reports/profit',
        name: 'ProfitReport',
        component: () =>
          import(
            '@/pages/staff/Reports/ProfitReport.vue'
          ),
      },

      {
        path: 'reports/inventory',
        name: 'InventoryReport',
        component: () =>
          import(
            '@/pages/staff/Reports/InventoryReport.vue'
          ),
      },
    ],
  },

  // ========================================
  // OWNER ROUTES
  // ========================================
  {
    path: '/owner',
    name: 'OwnerLayout',
    component: () =>
      import('@/layouts/OwnerLayout.vue'),

    meta: {
      requiresAuth: true,
      role: 'owner',
    },

    children: [
      {
        path: 'dashboard',
        name: 'OwnerDashboard',
        component: () =>
          import('@/pages/owner/Dashboard.vue'),
      },

      // Reports
      {
        path: 'reports/sales',
        name: 'OwnerSalesReport',
        component: () =>
          import(
            '@/pages/owner/Reports/SalesReport.vue'
          ),
      },

      {
        path: 'reports/profit',
        name: 'OwnerProfitReport',
        component: () =>
          import(
            '@/pages/owner/Reports/ProfitReport.vue'
          ),
      },

      {
        path: 'reports/inventory',
        name: 'OwnerInventoryReport',
        component: () =>
          import(
            '@/pages/owner/Reports/InventoryReport.vue'
          ),
      },

      {
        path: 'reports/financial',
        name: 'FinancialReport',
        component: () =>
          import(
            '@/pages/owner/Reports/FinancialReport.vue'
          ),
      },

      {
        path: 'reports/cashflow',
        name: 'CashflowReport',
        component: () =>
          import(
            '@/pages/owner/Reports/CashflowReport.vue'
          ),
      },

      {
        path: 'analytics',
        name: 'Analytics',
        component: () =>
          import('@/pages/owner/Analytics.vue'),
      },

      // Management
      {
        path: 'medicines',
        name: 'OwnerMedicines',
        component: () =>
          import(
            '@/pages/owner/Management/Medicines.vue'
          ),
      },

      {
        path: 'categories',
        name: 'OwnerCategories',
        component: () =>
          import(
            '@/pages/owner/Management/Categories.vue'
          ),
      },

      {
        path: 'suppliers',
        name: 'OwnerSuppliers',
        component: () =>
          import(
            '@/pages/owner/Management/Suppliers.vue'
          ),
      },

      {
        path: 'warehouses',
        name: 'OwnerWarehouses',
        component: () =>
          import(
            '@/pages/owner/Management/Warehouses.vue'
          ),
      },

      {
        path: 'stocks',
        name: 'OwnerStocks',
        component: () =>
          import(
            '@/pages/owner/Management/Stocks.vue'
          ),
      },

      {
        path: 'purchases',
        name: 'OwnerPurchases',
        component: () =>
          import(
            '@/pages/owner/Management/Purchases.vue'
          ),
      },

      {
        path: 'notifications',
        name: 'OwnerNotifications',
        component: () =>
          import(
            '@/pages/owner/Notifications.vue'
          ),
      },
    ],
  },

  // ========================================
  // 404
  // ========================================
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () =>
      import('@/pages/NotFound.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ========================================
// NAVIGATION GUARD
// ========================================
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      next({
        name: 'Login',
        query: {
          redirect: to.fullPath,
        },
      })
      return
    }

    if (to.meta.role) {
      const allowedRoles =
        Array.isArray(to.meta.role)
          ? to.meta.role
          : [to.meta.role]

      if (
        allowedRoles.includes(
          authStore.user?.role
        )
      ) {
        next()
        return
      }

      // Redirect by role
      switch (authStore.user?.role) {
        case 'staff':
          next({
            name: 'StaffDashboard',
          })
          break

        case 'owner':
          next({
            name: 'OwnerDashboard',
          })
          break

        case 'customer':
          next({
            name: 'Home',
          })
          break

        default:
          next({
            name: 'Login',
          })
      }

      return
    }
  }

  next()
})

export default router