<template>
    <DashboardLayout>
        <Head title="Edit Stock Opname" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Stock Opname</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Edit penghitungan fisik stok gudang.
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="route('stock-opnames.index')"
                />
            </div>
        </div>

        <!-- Settings Row -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <!-- Code Display -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-2 text-sm">Kode</h3>
                <p class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ opname.code }}</p>
            </div>

            <!-- Date Selection -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">Tanggal</h3>
                <input
                    type="date"
                    v-model="form.date"
                    class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
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
                
                <p v-if="loading" class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                    Memuat produk...
                </p>
                <p v-else-if="form.items.length > 0" class="text-xs text-green-600 dark:text-green-400 mt-2">
                    ✓ {{ form.items.length }} produk dimuat
                </p>
            </div>

            <!-- Search Filter -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">Filter Produk</h3>
                
                <div class="relative">
                    <div class="flex items-center gap-2">
                         <IconSearch :size="20" class="text-slate-400" />
                         <input
                            type="text"
                            v-model="searchFilter"
                            placeholder="Cari produk..."
                            :disabled="form.items.length === 0"
                            class="w-full h-10 px-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none disabled:opacity-50"
                        />
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">Informasi</h3>
                <div class="text-sm">
                    <p class="text-slate-600 dark:text-slate-400">Total Produk: <span class="font-bold text-slate-900 dark:text-white">{{ form.items.length }}</span></p>
                    <p class="text-slate-600 dark:text-slate-400">Ditampilkan: <span class="font-bold text-slate-900 dark:text-white">{{ filteredItems.length }}</span></p>
                </div>
            </div>
        </div>

        <!-- Product List and Notes - Side by Side -->
        <div class="flex flex-row gap-6">
            <!-- Product List Table - 3/4 Width -->
            <div style="width: 75%;">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Daftar Produk</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-700 text-left text-sm text-slate-500">
                                    <th class="pb-3 pl-2 w-12">No</th>
                                    <th class="pb-3">Produk</th>
                                    <th class="pb-3">Kategori</th>
                                    <th class="pb-3 text-center w-32">Stok Sistem</th>
                                    <th class="pb-3 text-center w-32">Stok Fisik</th>
                                    <th class="pb-3 text-center w-32">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-if="loading">
                                    <td colspan="6" class="py-8 text-center text-slate-500 italic text-sm">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-5 h-5 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
                                            Memuat produk...
                                        </div>
                                    </td>
                                </tr>
                                <tr v-else-if="filteredItems.length === 0">
                                    <td colspan="6" class="py-8 text-center text-slate-500 italic text-sm">
                                        Tidak ada produk yang sesuai dengan pencarian
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in filteredItems" :key="index" class="group hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="py-3 pl-2 text-slate-600 dark:text-slate-400">{{ index + 1 }}</td>
                                    <td class="py-3">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-900 dark:text-white text-sm">{{ item.title }}</span>
                                            <span class="text-xs text-slate-500">{{ item.barcode }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-xs text-slate-600 dark:text-slate-400">{{ item.category || '-' }}</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {{ item.system_stock }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <input
                                            type="number"
                                            v-model.number="item.physical_stock"
                                            min="0"
                                            class="w-24 px-2 py-1 text-center text-sm rounded-lg border border-slate-200 bg-slate-50 focus:border-primary-500 focus:ring-0 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                                        />
                                    </td>
                                    <td class="py-3 text-center">
                                        <span 
                                            :class="[
                                                'px-3 py-1 rounded-lg text-sm font-semibold',
                                                getDifference(item) > 0 
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                    : getDifference(item) < 0
                                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                                    : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                            ]"
                                        >
                                            {{ getDifference(item) > 0 ? '+' : '' }}{{ getDifference(item) }}
                                        </span>
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
                            placeholder="Catatan stock opname..."
                            class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                        ></textarea>
                    </div>
                    
                    <div class="space-y-3">
                        <Button
                            type="submit"
                            label="Update Stock Opname"
                            class="w-full bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                            :processing="form.processing"
                            :disabled="form.items.length === 0 || !form.warehouse_id || loading"
                            @click="submit"
                        />
                        <Button
                            type="button"
                            label="Batal"
                            class="w-full bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="router.visit(route('stock-opnames.index'))"
                        />
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { IconArrowLeft, IconSearch } from '@tabler/icons-vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';

const props = defineProps({
    opname: Object,
    warehouses: Array,
});

const form = useForm({
    warehouse_id: props.opname.warehouse_id,
    date: props.opname.date,
    notes: props.opname.notes || '',
    items: [],
});

const selectedWarehouse = ref(null);
const loading = ref(false);
const searchFilter = ref('');

const filteredItems = computed(() => {
    if (!searchFilter.value) {
        return form.items;
    }
    
    const search = searchFilter.value.toLowerCase();
    return form.items.filter(item => 
        item.title.toLowerCase().includes(search) ||
        item.barcode.toLowerCase().includes(search) ||
        (item.category && item.category.toLowerCase().includes(search))
    );
});

onMounted(async () => {
    // Set selected warehouse
    selectedWarehouse.value = props.warehouses.find(w => w.id === props.opname.warehouse_id);
    
    // Load all products for the warehouse
    await loadWarehouseProducts();
    
    // Update physical_stock from existing details
    props.opname.details.forEach(detail => {
        const item = form.items.find(i => i.product_id === detail.product_id);
        if (item) {
            item.physical_stock = detail.physical_stock;
        }
    });
});

const handleSelectWarehouse = async (value) => {
    selectedWarehouse.value = value;
    form.warehouse_id = value ? value.id : '';
    
    if (form.warehouse_id) {
        await loadWarehouseProducts();
    } else {
        form.items = [];
    }
};

const loadWarehouseProducts = async () => {
    loading.value = true;
    try {
        const response = await axios.post(route('stock-opnames.getWarehouseProducts'), {
            warehouse_id: form.warehouse_id
        });
        
        form.items = response.data;
    } catch (error) {
        console.error(error);
        alert('Gagal memuat produk');
    } finally {
        loading.value = false;
    }
};

const getDifference = (item) => {
    return (item.physical_stock || 0) - (item.system_stock || 0);
};

const submit = () => {
    form.put(route('stock-opnames.update', props.opname.id));
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>

