<template>
    <DashboardLayout>
        <Head title="Edit Stock Penyesuaian" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Stock Penyesuaian</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ penyesuaian.code }}
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="route('stock-penyesuaian.index')"
                />
            </div>
        </div>

        <!-- Settings Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Date Selection -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">Tanggal</h3>
                <input
                    type="date"
                    v-model="form.date"
                    class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
            </div>

            <!-- Type Selection -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">Tipe Penyesuaian</h3>
                <div class="flex gap-2">
                    <button
                        @click="form.type = 'in'"
                        :class="[
                            'flex-1 px-4 py-2 rounded-lg font-medium text-sm transition-colors',
                            form.type === 'in'
                                ? 'bg-green-500 text-white'
                                : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
                        ]"
                    >
                        Masuk
                    </button>
                    <button
                        @click="form.type = 'out'"
                        :class="[
                            'flex-1 px-4 py-2 rounded-lg font-medium text-sm transition-colors',
                            form.type === 'out'
                                ? 'bg-red-500 text-white'
                                : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
                        ]"
                    >
                        Keluar
                    </button>
                </div>
            </div>

            <!-- Warehouse Selection -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">Gudang</h3>
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

            <!-- Info -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">Informasi</h3>
                <div class="text-sm space-y-1">
                    <p class="text-slate-600 dark:text-slate-400">
                        Tipe: <span :class="form.type === 'in' ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
                            {{ form.type === 'in' ? 'Stok Masuk' : 'Stok Keluar' }}
                        </span>
                    </p>
                    <p class="text-slate-600 dark:text-slate-400">
                        Total Item: <span class="font-bold text-slate-900 dark:text-white">{{ form.items.length }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Product Search and Items -->
        <div class="flex flex-row gap-6">
            <!-- Product List Table - 3/4 Width -->
            <div style="width: 75%;">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <!-- Product Search -->
                    <div class="mb-4">
                        <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Cari Produk</h3>
                        <div class="relative">
                            <input
                                type="text"
                                v-model="searchQuery"
                                @input="searchProducts"
                                placeholder="Ketik nama atau barcode produk..."
                                class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            />
                            <!-- Search Results -->
                            <div
                                v-if="searchResults.length > 0"
                                class="absolute w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg z-10 max-h-60 overflow-y-auto"
                            >
                                <div
                                    v-for="product in searchResults"
                                    :key="product.id"
                                    @click="addProduct(product)"
                                    class="p-3 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer border-b border-slate-100 dark:border-slate-700 last:border-b-0"
                                >
                                    <div class="font-medium text-slate-900 dark:text-white">{{ product.title }}</div>
                                    <div class="text-xs text-slate-500">{{ product.barcode }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-700 text-left text-sm text-slate-500">
                                    <th class="pb-3 pl-2 w-12">No</th>
                                    <th class="pb-3">Produk</th>
                                    <th class="pb-3 text-center w-32">Qty</th>
                                    <th class="pb-3 text-center w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-if="form.items.length === 0">
                                    <td colspan="4" class="py-8 text-center text-slate-500 italic text-sm">
                                        Belum ada produk. Cari dan tambahkan produk di atas.
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in form.items" :key="index" class="group hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="py-3 pl-2 text-slate-600 dark:text-slate-400">{{ index + 1 }}</td>
                                    <td class="py-3">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-900 dark:text-white text-sm">{{ item.title }}</span>
                                            <span class="text-xs text-slate-500">{{ item.barcode }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <input
                                            type="number"
                                            v-model.number="item.qty"
                                            min="1"
                                            class="w-24 px-2 py-1 text-center text-sm rounded-lg border border-slate-200 bg-slate-50 focus:border-primary-500 focus:ring-0 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                                        />
                                    </td>
                                    <td class="py-3 text-center">
                                        <button
                                            @click="removeProduct(index)"
                                            class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                                        >
                                            <IconTrash :size="18" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes and Actions - 1/4 Width -->
            <div style="width: 25%;">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 h-full flex flex-col">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Catatan & Aksi</h3>
                    
                    <div class="flex-1 mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Catatan
                        </label>
                        <textarea
                            v-model="form.notes"
                            rows="10"
                            placeholder="Catatan penyesuaian stok..."
                            class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                        ></textarea>
                    </div>
                    
                    <div class="space-y-3">
                        <Button
                            type="submit"
                            label="Simpan Perubahan"
                            class="w-full bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                            :processing="form.processing"
                            :disabled="form.items.length === 0 || !form.warehouse_id"
                            @click="submit"
                        />
                        <Button
                            type="button"
                            label="Batal"
                            class="w-full bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="router.visit(route('stock-penyesuaian.index'))"
                        />
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { IconArrowLeft, IconTrash } from '@tabler/icons-vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';

const props = defineProps({
    penyesuaian: Object,
    warehouses: Array,
});

const form = useForm({
    type: props.penyesuaian.type,
    warehouse_id: props.penyesuaian.warehouse_id,
    date: props.penyesuaian.date?.split('T')[0] || new Date().toISOString().split('T')[0],
    notes: props.penyesuaian.notes || '',
    items: props.penyesuaian.details?.map(d => ({
        product_id: d.product_id,
        title: d.product?.title,
        barcode: d.product?.barcode,
        qty: d.qty,
    })) || [],
});

const selectedWarehouse = ref(null);
const searchQuery = ref('');
const searchResults = ref([]);
let searchTimeout = null;

onMounted(() => {
    if (props.penyesuaian.warehouse_id) {
        selectedWarehouse.value = props.warehouses.find(w => w.id === props.penyesuaian.warehouse_id);
    }
});

const handleSelectWarehouse = (value) => {
    selectedWarehouse.value = value;
    form.warehouse_id = value ? value.id : '';
};

const searchProducts = () => {
    clearTimeout(searchTimeout);
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    
    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.post(route('stock-penyesuaian.searchProduct'), {
                q: searchQuery.value
            });
            searchResults.value = response.data;
        } catch (error) {
            console.error(error);
        }
    }, 300);
};

const addProduct = (product) => {
    const exists = form.items.find(item => item.product_id === product.id);
    if (exists) {
        exists.qty += 1;
    } else {
        form.items.push({
            product_id: product.id,
            title: product.title,
            barcode: product.barcode,
            qty: 1,
        });
    }
    searchQuery.value = '';
    searchResults.value = [];
};

const removeProduct = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    form.put(route('stock-penyesuaian.update', props.penyesuaian.id));
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
