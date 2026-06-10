```vue
<template>
  <div class="w-full min-h-screen bg-gray-50 p-6 overflow-x-auto">
  <div class="space-y-6 max-w-none w-full">

      <!-- HEADER -->
      <div
        class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-3xl shadow-xl p-8 text-white"
      >
        <div
          class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
        >
          <div>
            <h1 class="text-4xl font-bold mb-2">
              💊 Manajemen Obat
            </h1>

            <p class="text-green-100 text-lg">
              Kelola data obat, stok,
              harga, dan expired dengan aman.
            </p>
          </div>

          <button
            @click="openForm()"
            class="px-6 py-4 rounded-2xl bg-white text-green-700 font-bold shadow-lg hover:scale-105 transition duration-300"
          >
            ➕ Tambah Obat
          </button>
        </div>
      </div>

<!-- STATS -->
<div
  class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5"
>
  <!-- Total Obat -->
  <div
    class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition"
  >
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-500 text-sm font-semibold">
          Total Obat
        </p>

        <h2 class="text-3xl font-bold text-blue-600 mt-2">
          {{ stats.total_medicines }}
        </h2>
      </div>

      <div
        class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center text-2xl"
      >
        💊
      </div>
    </div>
  </div>

  <!-- Low Stock -->
  <div
    class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition"
  >
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-500 text-sm font-semibold">
          Stok Menipis
        </p>

        <h2 class="text-3xl font-bold text-orange-500 mt-2">
          {{ stats.low_stock }}
        </h2>
      </div>

      <div
        class="w-14 h-14 rounded-2xl bg-orange-100 flex items-center justify-center text-2xl"
      >
        📦
      </div>
    </div>
  </div>

  <!-- Expired -->
  <div
    class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition"
  >
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-500 text-sm font-semibold">
          Obat Expired
        </p>

        <h2 class="text-3xl font-bold text-red-500 mt-2">
          {{ stats.expired }}
        </h2>
      </div>

      <div
        class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center text-2xl"
      >
        ⚠️
      </div>
    </div>
  </div>

  <!-- Total Value -->
  <div
    class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition"
  >
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-500 text-sm font-semibold">
          Nilai Inventory
        </p>

        <h2 class="text-xl font-bold text-green-600 mt-2">
          Rp{{ formatPrice(stats.total_value) }}
        </h2>
      </div>

      <div
        class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center text-2xl"
      >
        💰
      </div>
    </div>
  </div>
</div>

<!-- FILTER SECTION -->
<div
  class="bg-white rounded-[32px] border border-slate-200 shadow-sm p-6 space-y-5"
>
  <div
    class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
  >
    <div>
      <h2
        class="text-2xl font-bold text-slate-900"
      >
        🔍 Filter Obat
      </h2>

      <p
        class="text-slate-500 text-sm mt-1"
      >
        Cari dan filter data obat
      </p>
    </div>

    <div class="flex gap-3">
      <button
        @click="fetchMedicines"
        class="px-5 py-3 rounded-2xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition"
      >
        🔄 Refresh
      </button>

      <button
        @click="resetFilters"
        class="px-5 py-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold transition"
      >
        Reset
      </button>
    </div>
  </div>

  <!-- FILTER GRID -->
  <div
    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4"
  >
    <!-- SEARCH -->
    <div class="xl:col-span-2">
      <label
        class="block text-sm font-semibold text-slate-700 mb-2"
      >
        Cari Obat
      </label>

      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama obat / kode..."
          class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-50 px-5 pr-12 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition"
        />

        <span
          class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"
        >
          🔎
        </span>
      </div>
    </div>

    <!-- CATEGORY -->
    <div>
      <label
        class="block text-sm font-semibold text-slate-700 mb-2"
      >
        Kategori
      </label>

      <select
        v-model="filterCategory"
        class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-50 px-4 focus:outline-none focus:ring-4 focus:ring-emerald-100"
      >
        <option value="">
          Semua
        </option>

        <option
          v-for="cat in categories"
          :key="cat.id"
          :value="cat.id"
        >
          {{ cat.name }}
        </option>
      </select>
    </div>

    <!-- SUPPLIER -->
    <div>
      <label
        class="block text-sm font-semibold text-slate-700 mb-2"
      >
        Supplier
      </label>

      <select
        v-model="filterSupplier"
      class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-50 px-4 focus:outline-none focus:ring-4 focus:ring-emerald-100"
      >
        <option value="">
          Semua
        </option>

        <option
          v-for="sup in suppliers"
          :key="sup.id"
          :value="sup.id"
        >
          {{ sup.name }}
        </option>
      </select>
    </div>

    <!-- STOCK -->
    <div>
      <label
        class="block text-sm font-semibold text-slate-700 mb-2"
      >
        Status
      </label>

      <select
        v-model="filterStockStatus"  
        class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-50 px-4 focus:outline-none focus:ring-4 focus:ring-emerald-100"
      >
        <option value="">
          Semua
        </option>

        <option value="tersedia">
          ✅ Tersedia
        </option>

        <option value="menipis">
          🟠 Menipis
        </option>

        <option value="habis">
          🔴 Habis
        </option>
      </select>
    </div>
  </div>
</div>


<!-- PREMIUM TABLE -->
<div
  class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden"
>
  <!-- HEADER -->
  <div
    class="px-7 py-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
  >
    <div>
      <h2
        class="text-2xl font-bold text-slate-900"
      >
        💊 Daftar Obat
      </h2>

      <p
        class="text-slate-500 text-sm mt-1"
      >
        Total
        <span
          class="font-bold text-emerald-600"
        >
          {{ medicines.length }}
        </span>
        obat
      </p>
    </div>

    <button
      @click="openForm()"
      class="px-6 py-3 rounded-[20px] bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition"
    >
      ➕ Tambah Obat
    </button>
  </div>

  <!-- TABLE -->
  <div class="w-full overflow-x-auto">
    <table
      class="w-full min-w-[1450px]"
    >
      <thead
        class="bg-slate-50 sticky top-0 z-10"
      >
        <tr
          class="border-b border-slate-200"
        >
          <th class="px-6 py-5 text-left font-bold text-slate-700">
            Obat
          </th>

          <th class="px-6 py-5 text-left font-bold text-slate-700">
            Kategori
          </th>

          <th class="px-6 py-5 text-left font-bold text-slate-700">
            Supplier
          </th>

          <th class="px-6 py-5 text-right font-bold text-slate-700">
            Harga Pokok
          </th>

          <th class="px-6 py-5 text-right font-bold text-slate-700">
            Harga Jual
          </th>

          <th class="px-6 py-5 text-center font-bold text-slate-700">
            Stok
          </th>

          <th class="px-6 py-5 text-center font-bold text-slate-700">
            Expired
          </th>

          <th class="px-6 py-5 text-center font-bold text-slate-700">
            Status
          </th>

          <th class="px-6 py-5 text-center font-bold text-slate-700">
            Aksi
          </th>
        </tr>
      </thead>

     <tbody v-if="!loading">
        <tr
          v-for="medicine in medicines"
          :key="medicine.id"
          class="border-b border-slate-100 hover:bg-emerald-50 transition"
        >
          <!-- OBAT -->
          <td class="px-6 py-5">
            <div>
              <h3
                class="font-bold text-slate-800"
              >
                {{ medicine.name }}
              </h3>

              <p
                class="text-sm text-slate-400 mt-1"
              >
                {{ medicine.code }}
              </p>
            </div>
          </td>

          <!-- KATEGORI -->
          <td class="px-6 py-5">
            <span
              class="px-4 py-2 rounded-full bg-blue-50 text-blue-700 font-semibold text-sm"
            >
              {{
                medicine.category?.name || '-'
              }}
            </span>
          </td>

          <!-- SUPPLIER -->
          <td class="px-6 py-5 font-medium text-slate-700">
            {{
              medicine.supplier?.name || '-'
            }}
          </td>

          <!-- HARGA -->
          <td class="px-6 py-5 text-right font-semibold text-slate-700">
            Rp{{ formatPrice(medicine.base_price) }}
          </td>

          <td class="px-6 py-5 text-right font-bold text-emerald-600">
            Rp{{ formatPrice(medicine.selling_price) }}
          </td>

          <!-- STOCK -->
          <td class="px-6 py-5 text-center">
            <span
              :class="[
                'px-4 py-2 rounded-full text-sm font-bold text-white',
                medicine.total_stock === 0
                  ? 'bg-red-500'
                  : medicine.total_stock <= medicine.stock_minimum
                  ? 'bg-orange-500'
                  : 'bg-emerald-500'
              ]"
            >
              {{
                medicine.total_stock || 0
              }}
            </span>
          </td>

          <!-- EXPIRED -->
          <td class="px-6 py-5 text-center">
            {{
              medicine.expired_date
                ? formatDate(medicine.expired_date)
                : '-'
            }}
          </td>

          <!-- STATUS -->
          <td class="px-6 py-5 text-center">
            <span
              :class="[
                'px-4 py-2 rounded-full text-sm font-bold',
                medicine.status === 'aktif'
                  ? 'bg-emerald-100 text-emerald-700'
                  : 'bg-slate-200 text-slate-600'
              ]"
            >
              {{
                medicine.status === 'aktif'
                  ? 'Aktif'
                  : 'Nonaktif'
              }}
            </span>
          </td>

          <!-- ACTION -->
          <td class="px-6 py-5">
            <div
              class="flex justify-center gap-2"
            >
              <button
                @click="viewDetail(medicine)"
                class="w-11 h-11 rounded-2xl bg-blue-50 hover:bg-blue-100 transition"
              >
                👁
              </button>

              <button
                @click="openForm(medicine)"
                class="w-11 h-11 rounded-2xl bg-amber-50 hover:bg-amber-100 transition"
              >
                ✏️
              </button>

              <button
                @click="archiveMedicine(medicine)"
                class="w-11 h-11 rounded-2xl bg-red-50 hover:bg-red-100 transition"
              >
                🗑️
              </button>
            </div>
          </td>
        </tr>
      </tbody>
 
<!-- LOADING -->
<tbody v-else>
  <tr>
    <td
      colspan="9"
      class="py-20 text-center"
    >
      <div
        class="flex flex-col items-center gap-4"
      >
        <!-- Spinner -->
        <div
          class="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"
        />

        <p
          class="text-slate-500 font-medium"
        >
          Memuat data obat...
        </p>
      </div>
    </td>
  </tr>
</tbody>
    </table>
  </div>
</div>

      <!-- DETAIL MODAL -->
      <div
        v-if="selectedMedicine"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      >
        <div
          class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto"
        >
          <!-- HEADER -->
          <div
            class="sticky top-0 bg-white border-b px-8 py-6 flex justify-between items-center z-10"
          >
            <div>
              <h2
                class="text-3xl font-bold text-gray-800"
              >
                💊 Detail Obat
              </h2>

              <p class="text-gray-500 mt-1">
                Informasi lengkap obat
              </p>
            </div>

            <button
              @click="selectedMedicine = null"
              class="w-12 h-12 rounded-full bg-gray-100 hover:bg-gray-200 transition text-2xl"
            >
              ✕
            </button>
          </div>

          <!-- CONTENT -->
          <div class="p-8 space-y-6">

            <!-- BASIC INFO -->
            <div
              class="grid grid-cols-1 md:grid-cols-2 gap-5"
            >
              <div
                class="bg-gray-50 rounded-2xl p-5"
              >
                <p
                  class="text-sm text-gray-500 mb-1"
                >
                  Nama Obat
                </p>

                <h3
                  class="text-xl font-bold text-gray-800"
                >
                  {{
                    selectedMedicine.name
                  }}
                </h3>
              </div>

              <div
                class="bg-gray-50 rounded-2xl p-5"
              >
                <p
                  class="text-sm text-gray-500 mb-1"
                >
                  Kode Obat
                </p>

                <h3
                  class="text-xl font-bold text-gray-800"
                >
                  {{
                    selectedMedicine.code
                  }}
                </h3>
              </div>

              <div
                class="bg-gray-50 rounded-2xl p-5"
              >
                <p
                  class="text-sm text-gray-500 mb-1"
                >
                  Kategori
                </p>

                <h3
                  class="font-semibold text-gray-700"
                >
                  {{
                    selectedMedicine
                      .category?.name ||
                    '-'
                  }}
                </h3>
              </div>

              <div
                class="bg-gray-50 rounded-2xl p-5"
              >
                <p
                  class="text-sm text-gray-500 mb-1"
                >
                  Supplier
                </p>

                <h3
                  class="font-semibold text-gray-700"
                >
                  {{
                    selectedMedicine
                      .supplier?.name ||
                    '-'
                  }}
                </h3>
              </div>
            </div>

            <!-- PRICE -->
            <div
              class="bg-green-50 border border-green-100 rounded-3xl p-6"
            >
              <h3
                class="text-xl font-bold mb-5 text-green-700"
              >
                💰 Informasi Harga
              </h3>

              <div
                class="grid grid-cols-1 md:grid-cols-3 gap-4"
              >
                <div
                  class="bg-white rounded-2xl p-5"
                >
                  <p
                    class="text-sm text-gray-500"
                  >
                    Harga Pokok
                  </p>

                  <h3
                    class="font-bold text-xl mt-2"
                  >
                    Rp{{
                      formatPrice(
                        selectedMedicine.base_price
                      )
                    }}
                  </h3>
                </div>

                <div
                  class="bg-white rounded-2xl p-5"
                >
                  <p
                    class="text-sm text-gray-500"
                  >
                    Markup
                  </p>

                  <h3
                    class="font-bold text-xl mt-2"
                  >
                    {{
                      selectedMedicine.markup_percentage
                    }}%
                  </h3>
                </div>

                <div
                  class="bg-white rounded-2xl p-5"
                >
                  <p
                    class="text-sm text-gray-500"
                  >
                    Harga Jual
                  </p>

                  <h3
                    class="font-bold text-xl mt-2 text-green-600"
                  >
                    Rp{{
                      formatPrice(
                        selectedMedicine.selling_price
                      )
                    }}
                  </h3>
                </div>
              </div>
            </div>

            <!-- STOCK -->
            <div
              class="bg-blue-50 border border-blue-100 rounded-3xl p-6"
            >
              <h3
                class="text-xl font-bold mb-5 text-blue-700"
              >
                📦 Informasi Stok
              </h3>

              <div
                class="grid grid-cols-1 md:grid-cols-3 gap-4"
              >
                <div
                  class="bg-white rounded-2xl p-5"
                >
                  <p
                    class="text-sm text-gray-500"
                  >
                    Minimum
                  </p>

                  <h3
                    class="font-bold text-xl"
                  >
                    {{
                      selectedMedicine.stock_minimum
                    }}
                  </h3>
                </div>

                <div
                  class="bg-white rounded-2xl p-5"
                >
                  <p
                    class="text-sm text-gray-500"
                  >
                    Total Stok
                  </p>

                  <h3
                    class="font-bold text-xl"
                  >
                    {{
                      selectedMedicine.total_stock ||
                      0
                    }}
                  </h3>
                </div>

                <div
                  class="bg-white rounded-2xl p-5"
                >
                  <p
                    class="text-sm text-gray-500"
                  >
                    Maximum
                  </p>

                  <h3
                    class="font-bold text-xl"
                  >
                    {{
                      selectedMedicine.stock_maximum
                    }}
                  </h3>
                </div>
              </div>
            </div>

            <!-- DESCRIPTION -->
            <div
              v-if="
                selectedMedicine.description
              "
              class="bg-gray-50 rounded-3xl p-6"
            >
              <h3
                class="text-xl font-bold mb-3"
              >
                📝 Deskripsi
              </h3>

              <p
                class="text-gray-700 leading-relaxed"
              >
                {{
                  selectedMedicine.description
                }}
              </p>
            </div>

            <!-- FOOTER -->
            <button
              @click="selectedMedicine = null"
              class="w-full py-4 rounded-2xl bg-gray-200 hover:bg-gray-300 transition font-bold text-gray-700"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>

      <!-- CREATE / EDIT MODAL -->
      <div
        v-if="showForm"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      >
        <div
          class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto"
        >
          <!-- HEADER -->
          <div
            class="sticky top-0 bg-white border-b px-8 py-6 flex justify-between items-center"
          >
            <div>
              <h2
                class="text-3xl font-bold"
              >
                {{
                  editingId
                    ? '✏ Edit Obat'
                    : '➕ Tambah Obat'
                }}
              </h2>

              <p class="text-gray-500">
                Isi data obat dengan lengkap
              </p>
            </div>

            <button
              @click="closeForm"
              class="w-12 h-12 rounded-full bg-gray-100 hover:bg-gray-200 transition text-2xl"
            >
              ✕
            </button>
          </div>

          <!-- FORM -->
          <form
            @submit.prevent="saveMedicine"
            class="p-8 space-y-6"
          >
            <!-- BASIC -->
            <div
              class="grid grid-cols-1 md:grid-cols-2 gap-5"
            >
              <div>
                <label
                  class="block font-semibold mb-2"
                >
                  Kode Obat *
                </label>

                <input
                  v-model="form.code"
                  type="text"
                  required
                  class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                />
              </div>

              <div>
                <label
                  class="block font-semibold mb-2"
                >
                  Nama Obat *
                </label>

                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                />
              </div>
            </div>
         
            <!-- DESCRIPTION -->
            <div>
              <label
                class="block font-semibold mb-2"
              >
                Deskripsi
              </label>

              <textarea
                v-model="form.description"
                rows="4"
                class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none resize-none"
                placeholder="Deskripsi obat..."
              />
            </div>

            <!-- CATEGORY + SUPPLIER -->
            <div
              class="grid grid-cols-1 md:grid-cols-2 gap-5"
            >
              <div>
                <label
                  class="block font-semibold mb-2"
                >
                  Kategori *
                </label>

                <select
                  v-model.number="
                    form.category_id
                  "
                  required
                  class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                >
                  <option value="">
                    Pilih kategori
                  </option>

                  <option
                    v-for="cat in categories"
                    :key="cat.id"
                    :value="cat.id"
                  >
                    {{ cat.name }}
                  </option>
                </select>
              </div>

              <div>
                <label
                  class="block font-semibold mb-2"
                >
                  Supplier *
                </label>

                <select
                  v-model.number="
                    form.supplier_id
                  "
                  required
                  class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                >
                  <option value="">
                    Pilih supplier
                  </option>

                  <option
                    v-for="sup in suppliers"
                    :key="sup.id"
                    :value="sup.id"
                  >
                    {{ sup.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- PRICE -->
            <div
              class="bg-green-50 rounded-3xl p-6 border border-green-100"
            >
              <h3
                class="text-xl font-bold mb-5 text-green-700"
              >
                💰 Harga Obat
              </h3>

              <div
                class="grid grid-cols-1 md:grid-cols-3 gap-5"
              >
                <div>
                  <label
                    class="block font-semibold mb-2"
                  >
                    Harga Pokok *
                  </label>

                  <input
                    v-model.number="
                      form.base_price
                    "
                    type="number"
                    min="0"
                    required
                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                  />
                </div>

                <div>
                  <label
                    class="block font-semibold mb-2"
                  >
                    Markup (%)
                  </label>

                  <input
                    v-model.number="
                      form.markup_percentage
                    "
                    type="number"
                    min="0"
                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                  />
                </div>

                <div>
                  <label
                    class="block font-semibold mb-2"
                  >
                    Harga Jual
                  </label>

                   <input
                    :value="
                      calculateSellingPrice
                    "
                    readonly
                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-100 font-bold text-green-700"
                  />
                </div>
              </div>
            </div>

            <!-- STOCK -->
            <div
              class="bg-blue-50 rounded-3xl p-6 border border-blue-100"
            >
              <h3
                class="text-xl font-bold mb-5 text-blue-700"
              >
                📦 Pengaturan Stok
              </h3>

              <div
                class="grid grid-cols-1 md:grid-cols-3 gap-5"
              >
                <div>
                  <label
                    class="block font-semibold mb-2"
                  >
                    Minimum Stock
                  </label>

                  <input
                    v-model.number="
                      form.stock_minimum
                    "
                    type="number"
                    min="0"
                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-blue-200 outline-none"
                  />
                </div>

                <div>
                  <label
                    class="block font-semibold mb-2"
                  >
                    Maximum Stock
                  </label>

                  <input
                    v-model.number="
                      form.stock_maximum
                    "
                    type="number"
                    min="0"
                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-blue-200 outline-none"
                  />
                </div>

                <div>
                  <label
                    class="block font-semibold mb-2"
                  >
                    Expired Date
                  </label>

                  <input
                    v-model="
                      form.expired_date
                    "
                    type="date"
                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-blue-200 outline-none"
                  />
                </div>
              </div>
            </div>

            <!-- STATUS -->
            <div
              class="bg-gray-50 rounded-2xl p-5"
            >
              <label
                class="flex items-center gap-3 cursor-pointer"
              >
                <input
                  v-model="form.status"
                  type="checkbox"
                  class="w-5 h-5"
                  true-value="aktif"
                  false-value="tidak_aktif"
                />

                <span
                  class="font-semibold"
                >
                  Status Aktif
                </span>
              </label>
            </div>

            <!-- ACTION BUTTON -->
            <div
              class="flex gap-3 pt-4"
            >
              <button
                type="submit"
                :disabled="
                  savingForm
                "
                class="flex-1 py-4 rounded-2xl bg-green-600 hover:bg-green-700 text-white font-bold transition disabled:bg-gray-400"
              >
                {{
                  savingForm
                    ? '⏳ Menyimpan...'
                    : editingId
                    ? '💾 Update Obat'
                    : '✅ Simpan Obat'
                }}
              </button>

              <button
                type="button"
            @click="closeForm"
                class="flex-1 py-4 rounded-2xl bg-gray-200 hover:bg-gray-300 font-bold text-gray-700 transition"
              >
                ❌ Batal
              </button>
            </div>
          </form>
        </div>
      </div>
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
import api from '@/services/api'

import {
  ElMessage,
  ElMessageBox,
} from 'element-plus'

/* =========================
   STATE
========================= */

const medicines = ref([])
const categories = ref([])
const suppliers = ref([])

const loading = ref(false)
const savingForm = ref(false)

const showForm = ref(false)
const selectedMedicine =
  ref(null)

const editingId = ref(null)

const searchQuery =
  ref('')

const filterCategory =
  ref('')

const filterSupplier =
  ref('')

const filterStockStatus =
  ref('')

const stats = ref({
  total_medicines: 0,
  low_stock: 0,
  expired: 0,
  total_value: 0,
})

/* =========================
   FORM
========================= */

const defaultForm = () => ({
  code: '',
  name: '',
  description: '',

  category_id: '',
  supplier_id: '',

  base_price: 0,
  markup_percentage: 25,

  stock_minimum: 10,
  stock_maximum: 100,

  expired_date: '',

  unit: 'pcs',

  status: 'aktif',
})

const form =
  ref(defaultForm())

/* =========================
   COMPUTED
========================= */

const calculateSellingPrice =
  computed(() => {
    const base =
      Number(
        form.value.base_price
      ) || 0

    const markup =
      Number(
        form.value
          .markup_percentage
      ) || 0

    return Math.round(
      base + (base * markup) / 100
    )
  })




/* =========================
   HELPERS
========================= */

const formatPrice = (
  price
) => {
  return new Intl.NumberFormat(
    'id-ID'
  ).format(price || 0)
  
}
const formatDate = (
  date
) => {
  if (!date) return '-'

  return new Date(
    date
  ).toLocaleDateString(
    'id-ID',
    {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    }
  )
}
/* =========================
   FETCH MEDICINES
========================= */

const fetchMedicines = async () => {
  loading.value = true

  try {
    const params = {
      per_page: 100,
    }

    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    if (filterCategory.value) {
      params.category_id = filterCategory.value
    }

    if (filterSupplier.value) {
      params.supplier_id = filterSupplier.value
    }

    if (filterStockStatus.value) {
      params.stock_status = filterStockStatus.value
    }

    const response = await api.get('/medicines', { params })

    // 1. Simpan data obat ke state medicines
    medicines.value = (response.data.data?.data || response.data.data || []).map((medicine) => ({
      ...medicine,
      total_stock: medicine.stocks?.reduce(
        (total, stock) => total + Number(stock.quantity),
        0
      ) || 0,
    }))

    // 2. Hitung statistik dashboard obat
    if (response.data.stats) {
      stats.value = response.data.stats
    } else {
      const totalMedicines = medicines.value.length
      
      const lowStock = medicines.value.filter(m => {
        return m.total_stock <= (Number(m.stock_minimum) || 5) 
      }).length

      const expired = medicines.value.filter(m => {
        return m.stocks?.some(s => s.expired_date && new Date(s.expired_date) <= new Date())
      }).length

      const totalValue = medicines.value.reduce((acc, m) => {
        return acc + (m.total_stock * (Number(m.price) || 0))
      }, 0)

      stats.value = {
        total_medicines: totalMedicines,
        low_stock: lowStock,
        expired: expired,
        total_value: totalValue,
      }
    }

  } catch (error) {
    console.error('ERROR MEDICINES:', error)
    console.log('Response:', error.response?.data)

    ElMessage.error(
      error.response?.data?.message ||
      error.message ||
      'Gagal memuat data obat'
    )
  } // <-- TANDA PENUTUP CATCH ADA DI SINI
  finally {
    loading.value = false
  }
} // <-- TUTUPAN UTUH FUNGSI FETCHMEDICINES DI SINI

/* =========================
   FETCH CATEGORY
========================= */

const fetchCategories =
  async () => {
    try {
      const response =
        await api.get(
          '/categories',
          {
            params: {
              per_page: 100,
            },
          }
        )

      categories.value =
        response.data.data
          ?.data || []
    } catch (error) {
      console.error(error)
    }
  }

/* =========================
   FETCH SUPPLIER
========================= */

const fetchSuppliers =
  async () => {
    try {
      const response =
        await api.get(
          '/suppliers',
          {
            params: {
              per_page: 100,
            },
          }
        )

      suppliers.value =
        response.data.data
          ?.data || []
    } catch (error) {
      console.error(error)
    }
  }
/* =========================
   OPEN FORM
========================= */

const openForm = (
  medicine = null
) => {

  if (medicine) {
    editingId.value =
      medicine.id

    form.value = {
      code:
        medicine.code || '',

      name:
        medicine.name || '',

      description:
        medicine.description ||
        '',

      category_id:
        medicine.category_id ||
        '',

      supplier_id:
        medicine.supplier_id ||
        '',

      base_price:
        Number(
          medicine.base_price
        ) || 0,

      markup_percentage:
        Number(
          medicine.markup_percentage
        ) || 25,

      stock_minimum:
        Number(
          medicine.stock_minimum
        ) || 10,

      stock_maximum:
        Number(
          medicine.stock_maximum
        ) || 100,

      expired_date:
        medicine.expired_date
          ? medicine.expired_date.slice(
              0,
              10
            )
          : '',

      unit:
        medicine.unit ||
        'pcs',

      status:
        medicine.status ||
        'aktif',
    }
  } else {
    editingId.value =
      null

    form.value =
      defaultForm()
  }

  showForm.value = true
}

/* =========================
   CLOSE FORM
========================= */

const closeForm = () => {
  showForm.value = false

  editingId.value =
    null

  form.value =
    defaultForm()
}
/* =========================
   SAVE MEDICINE
========================= */

const saveMedicine = async () => {
  savingForm.value = true

  try {
    const payload = {
      ...form.value,
      base_price: Number(form.value.base_price),
      markup_percentage: Number(
        form.value.markup_percentage
      ),
      selling_price:
        calculateSellingPrice.value,
      stock_minimum: Number(
        form.value.stock_minimum
      ),
      stock_maximum: Number(
        form.value.stock_maximum
      ),
    }

    if (editingId.value) {
      await api.put(
        `/medicines/${editingId.value}`,
        payload
      )

      ElMessage.success(
        'Obat berhasil diperbarui'
      )
    } else {
      await api.post(
        '/medicines',
        payload
      )

      ElMessage.success(
        'Obat berhasil ditambahkan'
      )
    }

    showForm.value = false

    editingId.value = null

    form.value = defaultForm()

    await fetchMedicines()
  } catch (error) {
    console.error(error)

    ElMessage.error(
      error.response?.data?.message ||
      'Gagal menyimpan obat'
    )
  } finally {
    savingForm.value = false
  }
}

  
/* =========================
   DETAIL
========================= */

const viewDetail = (
  medicine
) => {
  selectedMedicine.value =
    medicine
}

const archiveMedicine =
  async (
    medicine
  ) => {
    try {
      await ElMessageBox.confirm(
        `Arsipkan ${medicine.name}?`,
        'Konfirmasi',
        {
          confirmButtonText:
            'Ya',
          cancelButtonText:
            'Batal',
          type: 'warning',
        }
      )

      await api.delete(
        `/medicines/${medicine.id}`
      )

      ElMessage.success(
        'Obat berhasil diarsipkan'
      )

      fetchMedicines()
    } catch (error) {
      if (
        error !== 'cancel'
      ) {
        console.error(
          error
        )

        ElMessage.error(
          'Gagal mengarsipkan obat'
        )
      }
    }
  }


/* =========================
   RESET FILTER
========================= */
const resetFilters = () => {
  searchQuery.value = ''
  filterCategory.value = ''
  filterSupplier.value = ''
  filterStockStatus.value = ''

  fetchMedicines()
}


/* =========================
   REFRESH DATA
========================= */

const refreshData =
  async () => {
    await Promise.all([
      fetchMedicines(),
      fetchCategories(),
      fetchSuppliers(),
    ])
  }

/* =========================
   WATCH SEARCH
========================= */

let searchTimeout =
  null

watch(
  searchQuery,
  (value) => {
    clearTimeout(
      searchTimeout
    )

    searchTimeout =
      setTimeout(() => {
        fetchMedicines()
      }, 500)
  }
)

/* =========================
   CLEANUP
========================= */

onUnmounted(() => {
  clearTimeout(
    searchTimeout
  )
})

/* =========================
   WATCH FILTER
========================= */

watch(
  [
    filterCategory,
    filterSupplier,
    filterStockStatus,
  ],
  () => {
    fetchMedicines()
  }
)

/* =========================
   MOUNTED
========================= */

onMounted(async () => {
  await refreshData()
})

</script>



<style scoped>
.page-wrapper {
  @apply space-y-8;
}

.card-premium {
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(18px);

  border: 1px solid
    rgba(226,232,240,0.7);

  box-shadow:
    0 10px 40px
      rgba(15,23,42,0.08);

  transition: all .3s ease;
}

.card-premium:hover {
  transform: translateY(-2px);
}

.stat-card {
  @apply relative overflow-hidden rounded-[30px] p-7;
}

.stat-card::before {
  content: '';

  position: absolute;
  top: -30px;
  right: -30px;

  width: 120px;
  height: 120px;

  border-radius: 999px;

  background:
    rgba(255,255,255,0.2);
}

.glass-filter {
  background:
    linear-gradient(
      135deg,
      rgba(255,255,255,0.95),
      rgba(248,250,252,0.9)
    );

  backdrop-filter:
    blur(20px);

  border:
    1px solid
    rgba(226,232,240,.7);

  box-shadow:
    0 12px 40px
    rgba(15,23,42,.06);
}

.filter-label {
  @apply block text-sm font-bold text-slate-700 mb-2;
}

.premium-input {
  width: 100%;

  border-radius: 20px;

  border: 1px solid
    rgb(226 232 240);

  background: white;

  padding:
    14px 18px;

  font-size: 14px;
  font-weight: 500;

  color:
    rgb(30 41 59);

  transition:
    all .25s ease;
}

.premium-input:focus {
  outline: none;

  border-color:
    rgb(16 185 129);

  box-shadow:
    0 0 0 4px
    rgba(16,185,129,.12);
}

.premium-input::placeholder {
  color:
    rgb(148 163 184);
}

.table-head {
  @apply px-6 py-5 text-sm font-black uppercase tracking-wider;
}

.table-cell {
  @apply px-6 py-5 text-sm text-slate-700;
}

.stock-badge {
  @apply px-4 py-2 rounded-full text-xs font-black text-white inline-flex justify-center items-center min-w-[55px];
}

.action-btn {
  @apply w-11 h-11 rounded-2xl text-white flex items-center justify-center transition font-bold shadow-md;
}

.action-btn:hover {
  transform:
    translateY(-2px)
    scale(1.04);
}

.form-label {
  @apply block text-sm font-bold text-slate-700 mb-2;
}

.detail-card {
  @apply rounded-[24px] bg-slate-50 p-5 border border-slate-200;
}

.detail-label {
  @apply text-sm text-slate-500 mb-1 font-semibold;
}

.detail-value {
  @apply text-lg font-bold text-slate-900;
}

/* SCROLLBAR */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background:
    transparent;
}

::-webkit-scrollbar-thumb {
  background:
    rgba(148,163,184,.4);

  border-radius:
    999px;
}

::-webkit-scrollbar-thumb:hover {
  background:
    rgba(100,116,139,.6);
}

/* RESPONSIVE */
@media (max-width: 1024px) {
  .table-head,
  .table-cell {
    @apply px-4 py-4 text-xs;
  }

  .premium-input {
    padding:
      12px 14px;
  }
}

@media (max-width: 768px) {
  .stat-card {
    @apply p-5;
  }

  .action-btn {
    @apply w-9 h-9 text-xs;
  }
}
</style>
```
