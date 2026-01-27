<template>
    <DashboardLayout>
        <Head title="Transaksi Baru" />

        <!-- Warehouse Selection Overlay -->
        <Teleport to="body">
            <div v-if="!form.warehouse_id" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>
                <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">
                    <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <IconBuildingWarehouse :size="32" class="text-primary-600 dark:text-primary-400" />
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Pilih Gudang</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Pilih gudang sumber sebelum memulai transaksi.</p>
                    
                    <div class="grid grid-cols-1 gap-3">
                        <button
                            v-for="warehouse in warehouses"
                            :key="warehouse.id"
                            @click="selectWarehouseFromOverlay(warehouse)"
                            class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all text-left"
                        >
                            <div class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center">
                                <IconBuildingWarehouse :size="20" class="text-slate-500" />
                            </div>
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ warehouse.name }}</p>
                                <p class="text-xs text-slate-500">{{ warehouse.address || 'Alamat belum diisi' }}</p>
                            </div>
                        </button>
                    </div>
                    
                    <Link
                        :href="route('sales.index')"
                        class="inline-flex items-center gap-2 mt-6 text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300"
                    >
                        <IconArrowLeft :size="16" />
                        Kembali ke Daftar Transaksi
                    </Link>
                </div>
            </div>
        </Teleport>

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Transaksi Baru</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Buat transaksi penjualan baru secara manual.
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="route('sales.index')"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Settings -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Payment Type -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Tipe Pembayaran</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="form.payment_type = 'cash'"
                            :class="[
                                'px-4 py-2 rounded-xl text-sm font-medium transition-all border',
                                form.payment_type === 'cash'
                                    ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400'
                                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
                            ]"
                        >
                            Cash
                        </button>
                        <button
                            type="button"
                            @click="form.payment_type = 'tempo'"
                            :class="[
                                'px-4 py-2 rounded-xl text-sm font-medium transition-all border',
                                form.payment_type === 'tempo'
                                    ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400'
                                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
                            ]"
                        >
                            Tempo
                        </button>
                    </div>
                </div>

                <!-- Customer Selection -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Informasi Customer</h3>
                    
                    <div class="space-y-4">
                        <InputSelect
                            label="Pilih Customer"
                            :data="customers"
                            :selected="selectedCustomer"
                            :set-selected="handleSelectCustomer"
                            placeholder="Pilih Customer..."
                            :searchable="true"
                            :errors="form.errors.customer_id"
                        />

                        <div v-if="selectedCustomer" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 relative group">
                            <p class="font-medium text-slate-900 dark:text-white">{{ selectedCustomer.name }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ selectedCustomer.phone || '-' }}</p>
                            
                            <button 
                                v-if="selectedCustomer.addresses && selectedCustomer.addresses.length > 0"
                                type="button"
                                @click="showAddressModal = true"
                                class="mt-3 w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                            >
                                <IconMapPin :size="14" />
                                Pilih Alamat
                            </button>

                            <div v-else class="mt-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                                <div class="flex gap-2 text-amber-600 dark:text-amber-400">
                                    <IconAlertCircle :size="16" class="mt-0.5 flex-shrink-0" />
                                    <p class="text-[10px] text-amber-700 dark:text-amber-300 leading-normal font-medium">
                                        Customer belum ada alamat yang terdaftar. buat di <span class="font-bold">pelanggan - daftar alamat</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pre-Order Toggle -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Pre-Order (PO)</h3>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_preorder" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none dark:bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500"></div>
                        </label>
                    </div>
                    
                    <transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="transform -translate-y-2 opacity-0"
                        enter-to-class="transform translate-y-0 opacity-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="transform translate-y-0 opacity-100"
                        leave-to-class="transform -translate-y-2 opacity-0"
                    >
                        <div v-if="form.is_preorder" class="space-y-4 pt-2">
                            <div class="p-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl mb-4">
                                <p class="text-[10px] text-amber-700 dark:text-amber-300 leading-normal">
                                    <span class="font-bold">Info:</span> Transaksi PO tidak mengurangi stok saat ini. Stok akan direservasi (booked).
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1 font-bold lowercase">Estimasi Tanggal Ready</label>
                                <input
                                    v-model="form.estimated_ready_date"
                                    type="date"
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none text-sm"
                                    :class="{'border-danger-500': form.errors.estimated_ready_date}"
                                />
                                <p v-if="form.errors.estimated_ready_date" class="text-xs text-danger-500 mt-1">{{ form.errors.estimated_ready_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1 font-bold lowercase">DP / Pembayaran Masuk</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">Rp</span>
                                    <input
                                        v-model.number="form.paid_amount"
                                        type="number"
                                        placeholder="0"
                                        class="w-full h-11 pl-9 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none text-sm"
                                        :class="{'border-danger-500': form.errors.paid_amount}"
                                    />
                                </div>
                                <p v-if="form.errors.paid_amount" class="text-xs text-danger-500 mt-1">{{ form.errors.paid_amount }}</p>
                                <p class="text-[10px] text-slate-500 mt-1">Kosongkan jika belum ada pembayaran.</p>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Address Selection Modal -->
                <Modal :show="showAddressModal" @close="showAddressModal = false" :title="'Daftar Alamat ' + (selectedCustomer?.name || '')">
                    <div class="p-6">
                        <div class="space-y-4">
                            <div 
                                v-for="address in selectedCustomer?.addresses" 
                                :key="address.id"
                                @click="selectAddress(address)"
                                class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary-500 dark:hover:border-primary-500 cursor-pointer transition-all group bg-slate-50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <span class="px-2 py-1 rounded-md bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 text-[10px] font-bold uppercase tracking-wider">
                                        {{ address.label }}
                                    </span>
                                    <span v-if="address.is_default" class="text-[10px] text-success-600 font-medium">Utama</span>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                                        <IconUser :size="14" class="text-slate-400" />
                                        {{ address.name }}
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                        <IconPhone :size="14" class="text-slate-400" />
                                        {{ address.phone }}
                                    </div>
                                    <div class="flex items-start gap-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed mt-2">
                                        <IconMapPin :size="14" class="text-slate-400 flex-shrink-0 mt-0.5" />
                                        {{ address.address }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <Button 
                                type="button" 
                                label="Tutup" 
                                class="bg-slate-100 text-slate-600 hover:bg-slate-200" 
                                @click="showAddressModal = false" 
                            />
                        </div>
                    </div>
                </Modal>

                <!-- Shipping Type -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Jenis Pengiriman</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            type="button"
                            @click="form.shipping_type = 'pickup'"
                            :class="[
                                'px-2 py-2 rounded-xl text-[10px] font-bold transition-all border uppercase',
                                form.shipping_type === 'pickup'
                                    ? 'bg-indigo-50 border-indigo-500 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400'
                                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
                            ]"
                        >
                            Ambil
                        </button>
                        <button
                            type="button"
                            @click="form.shipping_type = 'cod'"
                            :class="[
                                'px-2 py-2 rounded-xl text-[10px] font-bold transition-all border uppercase',
                                form.shipping_type === 'cod'
                                    ? 'bg-indigo-50 border-indigo-500 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400'
                                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
                            ]"
                        >
                            COD
                        </button>
                        <button
                            type="button"
                            @click="form.shipping_type = 'courier'"
                            :class="[
                                'px-2 py-2 rounded-xl text-[10px] font-bold transition-all border uppercase',
                                form.shipping_type === 'courier'
                                    ? 'bg-indigo-50 border-indigo-500 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400'
                                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
                            ]"
                        >
                            Kurir
                        </button>
                    </div>
                </div>

                <!-- Shipping Details (Conditional) -->
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform -translate-y-2 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-2 opacity-0"
                >
                    <div v-if="form.shipping_type !== 'pickup'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                        <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Detail Pengirim & Penerima</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">Nama Pengirim</label>
                                    <input
                                        v-model="form.sender_name"
                                        type="text"
                                        placeholder="Nama pengirim..."
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none text-sm"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">No. Telp Pengirim</label>
                                    <input
                                        v-model="form.sender_phone"
                                        type="text"
                                        placeholder="No. Telp..."
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none text-sm"
                                    />
                                </div>
                            </div>
                            <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
                            <div>
                                <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">Nama Penerima</label>
                                <input
                                    v-model="form.shipping_name"
                                    type="text"
                                    placeholder="Masukkan nama penerima..."
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">No. Telp Penerima</label>
                                <input
                                    v-model="form.shipping_phone"
                                    type="text"
                                    placeholder="Masukkan nomor telepon..."
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">Alamat Lengkap</label>
                                <textarea
                                    v-model="form.shipping_address"
                                    placeholder="Masukkan alamat pengiriman..."
                                    class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none resize-none text-sm"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

                <!-- Notes/Catatan -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Catatan</h3>
                    <textarea
                        v-model="form.notes"
                        placeholder="Catatan/keterangan (opsional)"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none resize-none"
                        rows="3"
                    ></textarea>
                </div>



            </div>

            <!-- Right Column: Item List -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Controls Grid -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    <!-- Warehouse Card -->
                    <div class="md:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <IconBuildingWarehouse :size="20" class="text-slate-400" />
                            <h3 class="font-semibold text-slate-900 dark:text-white">Informasi Gudang</h3>
                        </div>
                        <div class="flex items-center gap-3 p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800">
                            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-800 rounded-lg flex items-center justify-center">
                                <IconBuildingWarehouse :size="20" class="text-primary-600 dark:text-primary-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 dark:text-white truncate">{{ selectedWarehouse?.name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ selectedWarehouse?.address || 'Alamat belum diisi' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Product Search Card -->
                    <div class="md:col-span-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <IconSearch :size="20" class="text-slate-400" />
                            <h3 class="font-semibold text-slate-900 dark:text-white">Cari Produk</h3>
                        </div>
                        
                        <div class="relative">
                            <input
                                type="text"
                                v-model="searchQuery"
                                @input="handleSearch"
                                :disabled="!form.warehouse_id"
                                :placeholder="!form.warehouse_id ? 'Pilih gudang terlebih dahulu...' : 'Scan barcode atau ketik nama...'"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                            />

                            <!-- Search Results -->
                            <div v-if="searchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl max-h-64 overflow-y-auto">
                                <div
                                    v-for="product in searchResults"
                                    :key="product.id"
                                    @click="addProduct(product)"
                                    class="p-3 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer border-b border-slate-100 dark:border-slate-700 last:border-0"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex-shrink-0">
                                            <img
                                                v-if="product.image"
                                                :src="product.image"
                                                class="w-full h-full object-cover rounded-lg"
                                            />
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white text-sm">{{ product.title }}</p>
                                            <p class="text-xs text-slate-500">{{ product.barcode }}</p>
                                        </div>
                                        <div class="ml-auto text-right">
                                            <p class="text-xs font-semibold" :class="product.type === 'bundle' ? 'text-purple-600' : 'text-slate-700 dark:text-slate-300'">
                                                {{ product.type === 'bundle' ? 'Bundling' : 'Stok: ' + product.stock }}
                                            </p>
                                            <p v-if="product.type === 'bundle'" class="text-[10px] text-slate-500">Tersedia: {{ product.stock }}</p>
                                            <p class="text-xs text-primary-600 font-bold mt-1">{{ formatCurrency(product.sell_price) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 flex flex-col">
                        <h3 class="font-semibold text-slate-900 dark:text-white mb-6">Daftar Item</h3>
                    
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-700 text-left text-sm text-slate-500">
                                    <th class="pb-3 pl-2">Produk</th>
                                    <th class="pb-3 text-right">Harga Jual</th>
                                    <th class="pb-3 text-right w-28">Diskon</th>
                                    <th class="pb-3 text-center w-24">Qty</th>
                                    <th class="pb-3 text-right">Subtotal</th>
                                    <th class="pb-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-if="form.items.length === 0">
                                    <td colspan="6" class="py-8 text-center text-slate-500 italic text-sm">
                                        Belum ada item dipilih
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in form.items" :key="index" class="group">
                                    <td class="py-2 pl-2">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-900 dark:text-white text-sm">
                                                <span v-if="item.bundle_id" class="text-purple-600 font-bold mr-1">[B]</span>
                                                {{ item.title }}
                                            </span>
                                            <span class="text-xs text-slate-500">{{ item.barcode }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2 text-right">
                                        <input
                                            type="number"
                                            v-model="item.sell_price"
                                            min="0" class="w-32 px-2 py-1 text-right text-sm rounded-lg border border-slate-200 bg-slate-50 focus:border-primary-500 focus:ring-0 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                                        />
                                    </td>
                                    <td class="py-2 text-right">
                                        <input
                                            type="number"
                                            v-model.number="item.discount"
                                            min="0"
                                            placeholder="0"
                                            class="w-28 px-2 py-1 text-right text-sm rounded-lg border border-slate-200 bg-slate-50 focus:border-primary-500 focus:ring-0 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                                        />
                                    </td>
                                    <td class="py-2 text-center">
                                        <input
                                            type="number"
                                            v-model="item.qty"
                                            @change="validateQty(index)"
                                            min="1" class="w-20 px-2 py-1 text-center text-sm rounded-lg border border-slate-200 bg-slate-50 focus:border-primary-500 focus:ring-0 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                                        />
                                    </td>
                                    <td class="py-2 text-right font-medium text-slate-900 dark:text-white text-sm">
                                        {{ formatCurrency((item.sell_price * item.qty) - (item.discount || 0)) }}
                                    </td>
                                    <td class="py-2 text-center">
                                        <button @click="removeItem(index)" class="text-slate-400 hover:text-danger-500 transition-colors">
                                            <IconTrash :size="18" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Consolidated Costs & Discounts Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Regular Discount -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Diskon</label>
                            <div class="flex gap-2 mb-2">
                                <button
                                    type="button"
                                    @click="form.discount_type = 'amount'"
                                    :class="[
                                        'flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors border',
                                        form.discount_type === 'amount'
                                            ? 'bg-primary-500 border-primary-500 text-white shadow-sm'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
                                    ]"
                                >
                                    Nominal
                                </button>
                                <button
                                    type="button"
                                    @click="form.discount_type = 'percent'"
                                    :class="[
                                        'flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors border',
                                        form.discount_type === 'percent'
                                            ? 'bg-primary-500 border-primary-500 text-white shadow-sm'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
                                    ]"
                                >
                                    Persentase
                                </button>
                            </div>
                            <div class="relative">
                                <span v-if="form.discount_type === 'amount'" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input
                                    v-if="form.discount_type === 'amount'"
                                    v-model.number="form.discount"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="w-full h-11 pl-9 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all"
                                />
                                <div v-else class="relative">
                                    <input
                                        v-model.number="form.discount_percent"
                                        type="number"
                                        min="0"
                                        max="100"
                                        placeholder="0"
                                        class="w-full h-11 pl-4 pr-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all"
                                    />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
                                </div>
                            </div>
                            <p v-if="discountAmount > 0" class="text-xs text-success-600 mt-2 font-medium">Potongan: {{ formatCurrency(discountAmount) }}</p>
                        </div>

                        <!-- Event Discount -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Diskon Event</label>
                            <div class="flex gap-2 mb-2">
                                <button
                                    type="button"
                                    @click="form.event_discount_type = 'amount'"
                                    :class="[
                                        'flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors border',
                                        form.event_discount_type === 'amount'
                                            ? 'bg-purple-600 border-purple-600 text-white shadow-sm'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
                                    ]"
                                >
                                    Nominal
                                </button>
                                <button
                                    type="button"
                                    @click="form.event_discount_type = 'percent'"
                                    :class="[
                                        'flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors border',
                                        form.event_discount_type === 'percent'
                                            ? 'bg-purple-600 border-purple-600 text-white shadow-sm'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400'
                                    ]"
                                >
                                    Persentase
                                </button>
                            </div>
                            <div class="relative">
                                <span v-if="form.event_discount_type === 'amount'" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input
                                    v-if="form.event_discount_type === 'amount'"
                                    v-model.number="form.event_discount"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="w-full h-11 pl-9 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all"
                                />
                                <div v-else class="relative">
                                    <input
                                        v-model.number="form.event_discount_percent"
                                        type="number"
                                        min="0"
                                        max="100"
                                        placeholder="0"
                                        class="w-full h-11 pl-4 pr-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all"
                                    />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
                                </div>
                            </div>
                            <p v-if="eventDiscountAmount > 0" class="text-xs text-purple-600 mt-2 font-medium">Potongan Event: {{ formatCurrency(eventDiscountAmount) }}</p>
                        </div>

                        <!-- Shipping Cost -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Ongkos Kirim</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input
                                    type="number"
                                    v-model="form.shipping_cost"
                                    min="0"
                                    placeholder="0"
                                    class="w-full pl-9 pr-4 h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none text-sm"
                                />
                            </div>
                        </div>

                        <!-- Other Cost -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Biaya Lainnya</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input
                                    type="number"
                                    v-model="form.other_cost"
                                    min="0"
                                    placeholder="0"
                                    class="w-full pl-9 pr-4 h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Grand Total Display -->
                    <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 uppercase tracking-wider font-bold">Total Akhir</p>
                            <p class="text-3xl font-black text-primary-600 dark:text-primary-400">{{ formatCurrency(grandTotal) }}</p>
                        </div>
                        <div class="flex gap-3">
                            <Button
                                type="button"
                                label="Batal"
                                class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="router.visit(route('sales.index'))"
                            />
                            <Button
                                type="submit"
                                label="Simpan Transaksi"
                                class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30 px-8"
                                :processing="form.processing"
                                :disabled="form.items.length === 0 || !form.customer_id || !form.warehouse_id"
                                @click="submit"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { IconArrowLeft, IconSearch, IconTrash, IconBuildingWarehouse, IconMapPin, IconPhone, IconUser, IconAlertCircle } from '@tabler/icons-vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';
import Modal from '@/Components/Dashboard/Modal.vue';
import { debounce } from 'lodash';

const props = defineProps({
    customers: Array,
    warehouses: Array,
});

const form = useForm({
    customer_id: '',
    warehouse_id: '',
    grand_total: 0,
    shipping_cost: 0,
    other_cost: 0,
    discount: 0,
    discount_type: 'amount',
    discount_percent: 0,
    event_discount: 0,
    event_discount_type: 'amount',
    event_discount_percent: 0,
    shipping_name: '',
    shipping_phone: '',
    shipping_address: '',
    sender_name: '',
    sender_phone: '',
    payment_type: 'cash',
    shipping_type: 'pickup',
    is_preorder: false,
    preorder_status: 'pending',
    estimated_ready_date: '',
    paid_amount: 0,
    notes: '',
    items: [],
});

const selectedCustomer = ref(null);
const selectedWarehouse = ref(null);
const searchQuery = ref('');
const searchResults = ref([]);

const showAddressModal = ref(false);

const handleSelectCustomer = (value) => {
    selectedCustomer.value = value;
    form.customer_id = value ? value.id : '';
    
    // reset shipping info
    form.shipping_name = '';
    form.shipping_phone = '';
    form.shipping_address = '';

    if (value && value.addresses && value.addresses.length > 0) {
        showAddressModal.value = true;
    }
};

const selectAddress = (address) => {
    form.shipping_name = address.name;
    form.shipping_phone = address.phone;
    form.shipping_address = address.address;
    showAddressModal.value = false;
};

const handleSelectWarehouse = (value) => {
    selectedWarehouse.value = value;
    form.warehouse_id = value ? value.id : '';
};

const selectWarehouseFromOverlay = (warehouse) => {
    selectedWarehouse.value = warehouse;
    form.warehouse_id = warehouse.id;
};

const handleSearch = debounce(async () => {
    if (!searchQuery.value) {
        searchResults.value = [];
        return;
    }
    
    try {
        const response = await axios.post(route('sales.searchProduct'), {
            q: searchQuery.value,
            warehouse_id: form.warehouse_id
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error(error);
    }
}, 300);

const addProduct = (product) => {
    const isBundle = product.type === 'bundle';
    const existing = form.items.find(item => isBundle ? item.bundle_id === product.id : item.product_id === product.id);
    
    // Check stock (skip if PO)
    const currentQty = existing ? existing.qty : 0;
    if (!form.is_preorder && currentQty + 1 > product.stock) {
        alert('Stok tidak mencukupi! (Tersedia: ' + product.stock + ')');
        return;
    }

    if (existing) {
        existing.qty++;
    } else {
        form.items.push({
            product_id: isBundle ? null : product.id,
            bundle_id: isBundle ? product.id : null,
            title: product.title,
            barcode: product.barcode,
            sell_price: product.sell_price,
            discount: 0,
            qty: 1,
            stock: product.stock,
            type: product.type
        });
    }
    searchQuery.value = '';
    searchResults.value = [];
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const validateQty = (index) => {
    const item = form.items[index];
    if (!form.is_preorder && item.qty > item.stock) {
        alert('Stok tidak mencukupi! (Tersedia: ' + item.stock + ')');
        item.qty = item.stock;
    }
    if (item.qty < 1) {
        item.qty = 1;
    }
};

const subtotal = computed(() => {
    return form.items.reduce((total, item) => {
        const itemTotal = (item.sell_price * item.qty) - (item.discount || 0);
        return total + itemTotal;
    }, 0);
});

const discountAmount = computed(() => {
    if (form.discount_type === 'percent') {
        return Math.round((subtotal.value * (form.discount_percent || 0)) / 100);
    }
    return form.discount || 0;
});

const eventDiscountAmount = computed(() => {
    const afterDiscount = subtotal.value - discountAmount.value;
    if (form.event_discount_type === 'percent') {
        return Math.round((afterDiscount * (form.event_discount_percent || 0)) / 100);
    }
    return form.event_discount || 0;
});

const grandTotal = computed(() => {
    const itemsTotal = subtotal.value - discountAmount.value - eventDiscountAmount.value;
    return itemsTotal + parseInt(form.shipping_cost || 0) + parseInt(form.other_cost || 0);
});

watch(grandTotal, (val) => {
    form.grand_total = val;
});

watch([discountAmount], ([discVal]) => {
    if (form.discount_type === 'percent') {
        form.discount = discVal;
    }
});

watch([eventDiscountAmount], ([eventVal]) => {
    if (form.event_discount_type === 'percent') {
        form.event_discount = eventVal;
    }
});

const submit = () => {
    form.post(route('sales.store'));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>



