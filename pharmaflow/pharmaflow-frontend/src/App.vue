<template>
  <router-view />
</template>

<script setup>
// Script yang sudah Anda buat biarkan saja di sini
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'

const route = useRoute()

const hiddenRoutes = [
  '/login',
  '/register',
  '/staff',
  '/owner',
]

const showPublicLayout = computed(() => {
  return !hiddenRoutes.some(path =>
    route.path.startsWith(path)
  )
})
</script>

<style>
/* CSS bawaan Anda biarkan saja di sini */
html { scroll-behavior: smooth; }
body { margin: 0; padding: 0; background: #f8fafc; font-family: sans-serif; }
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
#app { min-height: 100vh; }
</style>