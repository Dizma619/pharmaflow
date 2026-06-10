<template>
  <footer class="bg-gray-800 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 py-8">
      <div class="grid grid-cols-4 gap-8">
        <div>
          <h3 class="font-bold text-lg mb-4">About FharmaFlow</h3>
          <p class="text-gray-400 text-sm">Platform e-commerce apotek terpadu dengan sistem kasir dan ERP.</p>
        </div>
        <div>
          <h3 class="font-bold text-lg mb-4">Quick Links</h3>
          <ul class="space-y-2 text-sm text-gray-400">
            <li><router-link to="/" class="hover:text-white">Home</router-link></li>
            <li><router-link to="/products" class="hover:text-white">Products</router-link></li>
          </ul>
        </div>
        <div>
          <h3 class="font-bold text-lg mb-4">Contact</h3>
          <p class="text-gray-400 text-sm">Email: info@pharmaflow.com</p>
          <p class="text-gray-400 text-sm">Phone: +62 812-3456-7890</p>
        </div>
        <div>
          <h3 class="font-bold text-lg mb-4">Follow Us</h3>
          <div class="flex space-x-4">
            <a href="#" class="text-gray-400 hover:text-white">Facebook</a>
            <a href="#" class="text-gray-400 hover:text-white">Twitter</a>
            <a href="#" class="text-gray-400 hover:text-white">Instagram</a>
          </div>
        </div>
      </div>
      <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400 text-sm">
        <p>&copy; 2024 FharmaFlow. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>