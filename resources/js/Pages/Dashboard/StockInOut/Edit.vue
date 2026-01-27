<template>
    <DashboardLayout>
        <Head title="Edit Penyesuaian Stok" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Penyesuaian Stok</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Edit penyesuaian {{ adjustment.code }}.
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="route('stock-adjustments.index')"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Settings -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Type Selection -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Tipe Penyesuaian</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all" :class="form.type === 'in' ? 'border-green-500 bg-green-50 dark:bg-green-950/30' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" v-model="form.type" value="in" class="w-4 h-4 text-green-600" />
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">Stock In</p>
                                <p class="text-xs text-slate-500">Tambah stok</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all" :class="form.type === 'out' ? 'border-red-500 bg-red-50 dark:bg-red-950/30' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" v-model="form.type" value="out" class="w-4 h-4 text-red-600" />
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">Stock Out</p>
                                <p class="text-xs text-slate-500">Kurangi stok</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Date Selection -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Tanggal</h3>
                    <input
                        type="date"
                        v-model="form.date"
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    />
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
                                    <th class="pb-3 text-center w-24">Qty</th>
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
                                    <td class="py-3 text-center">
                                        <input
                                            type="number"
                                            v-model="item.qty" min="0" class="w-20 px-2 py-1 text-center text-sm rounded-lg border border-slate-200 bg-slate-50 focus:border-primary-500 focus:ring-0 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                                        />
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
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Catatan
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Alasan penyesuaian stok..."
                                class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            ></textarea>
                        </div>
                        
                        <div class="flex justify-end gap-3">
                            <Button
                                type="button"
                                label="Batal"
                                class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="router.visit(route('stock-adjustments.index'))"
                            />
                            <Button
                                type="submit"
                                label="Simpan Penyesuaian"
                                class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                                :processing="form.processing"
                                :disabled="form.items.length === 0 || !form.warehouse_id || !form.type"
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
    warehouses: Array,
    adjustment: Object,
});

const form = useForm({
    type: props.adjustment?.type || 'in',
    warehouse_id: props.adjustment?.warehouse_id || '',
    date: props.adjustment?.date || new Date().toISOString().split('T')[0],
    notes: props.adjustment?.notes || '',
    items: props.adjustment?.details?.map(detail => ({
        product_id: detail.product_id,
        title: detail.product?.title,
        barcode: detail.product?.barcode,
        qty: detail.qty,
    })) || [],
});

const selectedWarehouse = ref(props.adjustment?.warehouse || null);
const searchQuery = ref('');
const searchResults = ref([]);

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
        const response = await axios.post(route('stock-adjustments.searchProduct'), {
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
    form.put(route('stock-adjustments.update', props.adjustment.id));
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


