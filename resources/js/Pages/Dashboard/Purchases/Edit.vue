<template>
    <DashboardLayout>
        <Head title="Edit Pembelian" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Pembelian</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Edit transaksi pembelian {{ purchase.invoice }}.
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="route('purchases.index')"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Settings -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Supplier Selection -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Informasi Supplier</h3>
                    
                    <div class="space-y-4">
                        <InputSelect
                            label="Pilih Supplier"
                            :data="suppliers"
                            :selected="selectedSupplier"
                            :set-selected="handleSelectSupplier"
                            placeholder="Pilih Supplier..."
                            :searchable="true"
                            :errors="form.errors.supplier_id"
                        />

                        <div v-if="selectedSupplier" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <p class="font-medium text-slate-900 dark:text-white">{{ selectedSupplier.name }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ selectedSupplier.address || '-' }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ selectedSupplier.phone || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Warehouse Selection -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Gudang Tujuan</h3>
                    
                    <InputSelect
                        label="Pilih Gudang"
                        :data="warehouses"
                        :selected="selectedWarehouse"
                        :set-selected="handleSelectWarehouse"
                        placeholder="Pilih Gudang..."
                        :searchable="true"
                        :errors="form.errors.warehouse_id"
                    />
                </div>

                <!-- Payment Type Selection -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Tipe Pembayaran</h3>
                    
                    <div class="flex gap-4">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" v-model="form.payment_type" value="cash" class="hidden peer" />
                            <div class="p-3 text-center rounded-xl border border-slate-200 dark:border-slate-700 peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-500/10 peer-checked:text-primary-600 transition-all font-medium text-sm">
                                Tunai
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" v-model="form.payment_type" value="tempo" class="hidden peer" />
                            <div class="p-3 text-center rounded-xl border border-slate-200 dark:border-slate-700 peer-checked:border-amber-500 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-500/10 peer-checked:text-amber-600 transition-all font-medium text-sm">
                                Tempo
                            </div>
                        </label>
                    </div>
                </div>

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

                <!-- Product Search -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Cari Produk</h3>
                    
                    <div class="relative">
                        <div class="flex items-center gap-2 mb-2">
                             <IconSearch :size="20" class="text-slate-400" />
                             <input
                                type="text"
                                v-model="searchQuery"
                                @input="handleSearch"
                                placeholder="Scan barcode atau ketik nama..."
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
                            />
                        </div>

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
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Stok: {{ product.stock }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Item List -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 h-full flex flex-col">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Daftar Item</h3>
                    
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-700 text-left text-sm text-slate-500">
                                    <th class="pb-3 pl-2">Produk</th>
                                    <th class="pb-3 text-right">Harga Beli</th>
                                    <th class="pb-3 text-center w-24">Qty</th>
                                    <th class="pb-3 text-right">Subtotal</th>
                                    <th class="pb-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-if="form.items.length === 0">
                                    <td colspan="5" class="py-8 text-center text-slate-500 italic text-sm">
                                        Belum ada item dipilih
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in form.items" :key="index" class="group">
                                    <td class="py-3 pl-2">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-900 dark:text-white text-sm">{{ item.title }}</span>
                                            <span class="text-xs text-slate-500">{{ item.barcode }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-right">
                                        <input
                                            type="number"
                                            v-model="item.buy_price"
                                            min="0" class="w-32 px-2 py-1 text-right text-sm rounded-lg border border-slate-200 bg-slate-50 focus:border-primary-500 focus:ring-0 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                                        />
                                    </td>
                                    <td class="py-3 text-center">
                                        <input
                                            type="number"
                                            v-model="item.qty" min="0" class="w-20 px-2 py-1 text-center text-sm rounded-lg border border-slate-200 bg-slate-50 focus:border-primary-500 focus:ring-0 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                                        />
                                    </td>
                                    <td class="py-3 text-right font-medium text-slate-900 dark:text-white text-sm">
                                        {{ formatCurrency(item.buy_price * item.qty) }}
                                    </td>
                                    <td class="py-3 text-center">
                                        <button @click="removeItem(index)" class="text-slate-400 hover:text-danger-500 transition-colors">
                                            <IconTrash :size="18" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Summary -->
                    <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700">
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-lg font-bold text-slate-700 dark:text-slate-300">Total Pembelian</span>
                            <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ formatCurrency(grandTotal) }}</span>
                        </div>
                        
                        <div class="flex justify-end gap-3">
                            <Button
                                type="button"
                                label="Batal"
                                class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="router.visit(route('purchases.index'))"
                            />
                            <Button
                                type="submit"
                                label="Simpan Transaksi"
                                class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                                :processing="form.processing"
                                :disabled="form.items.length === 0 || !form.supplier_id || !form.warehouse_id"
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
import { Head, useForm, router } from '@inertiajs/vue3';
import { IconArrowLeft, IconSearch, IconTrash } from '@tabler/icons-vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';
import { debounce } from 'lodash';

const props = defineProps({
    suppliers: Array,
    warehouses: Array,
    purchase: Object,
});

const form = useForm({
    supplier_id: props.purchase?.supplier_id || '',
    warehouse_id: props.purchase?.warehouse_id || '',
    grand_total: props.purchase?.grand_total || 0,
    payment_type: props.purchase?.payment_type || 'cash',
    notes: props.purchase?.notes || '',
    items: props.purchase?.details?.map(detail => ({
        product_id: detail.product_id,
        title: detail.product?.title,
        barcode: detail.product?.barcode,
        buy_price: detail.buy_price,
        qty: detail.qty,
    })) || [],
});

const selectedSupplier = ref(props.purchase?.supplier || null);
const selectedWarehouse = ref(props.purchase?.warehouse || null);
const searchQuery = ref('');
const searchResults = ref([]);

const handleSelectSupplier = (value) => {
    selectedSupplier.value = value;
    form.supplier_id = value ? value.id : '';
};

const handleSelectWarehouse = (value) => {
    selectedWarehouse.value = value;
    form.warehouse_id = value ? value.id : '';
};

const handleSearch = debounce(async () => {
    if (!searchQuery.value) {
        searchResults.value = [];
        return;
    }
    
    try {
        const response = await axios.post(route('purchases.searchProduct'), {
            q: searchQuery.value
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error(error);
    }
}, 300);

const addProduct = (product) => {
    const existing = form.items.find(item => item.product_id === product.id);
    if (existing) {
        existing.qty++;
    } else {
        form.items.push({
            product_id: product.id,
            title: product.title,
            barcode: product.barcode,
            buy_price: product.buy_price,
            qty: 1
        });
    }
    searchQuery.value = '';
    searchResults.value = [];
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const grandTotal = computed(() => {
    return form.items.reduce((total, item) => total + (item.buy_price * item.qty), 0);
});

watch(grandTotal, (val) => {
    form.grand_total = val;
});

const submit = () => {
    form.put(route('purchases.update', props.purchase.id));
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



