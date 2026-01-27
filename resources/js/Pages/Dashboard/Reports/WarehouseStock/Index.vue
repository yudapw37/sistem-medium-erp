<template>
    <DashboardLayout>
        <Head title="Stok per Gudang" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Stok per Gudang</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Ringkasan stok produk di setiap gudang.
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Cari Produk
                    </label>
                    <input
                        v-model="filterData.search"
                        type="text"
                        placeholder="Nama atau barcode..."
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    />
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Kategori
                    </label>
                    <select
                        v-model="filterData.category_id"
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                        <option value="">Semua Kategori</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button
                        @click="applyFilters"
                        class="flex-1 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-medium transition-colors"
                    >
                        Filter
                    </button>
                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-medium transition-colors"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th rowspan="2" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Produk
                            </th>
                            <th rowspan="2" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Barcode
                            </th>
                            <th rowspan="2" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Kategori
                            </th>
                            <th
                                v-for="warehouse in warehouses"
                                :key="warehouse.id"
                                colspan="2"
                                class="px-6 py-2 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-x border-slate-200 dark:border-slate-700"
                            >
                                {{ warehouse.name }}
                            </th>
                            <th colspan="2" class="px-6 py-2 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider bg-primary-50 dark:bg-primary-950/30">
                                Total
                            </th>
                        </tr>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                            <template v-for="warehouse in warehouses" :key="'sub-' + warehouse.id">
                                <th class="px-3 py-2 text-center text-[10px] font-medium text-slate-500 uppercase tracking-tighter border-l border-slate-200 dark:border-slate-700">
                                    Sistem
                                </th>
                                <th class="px-3 py-2 text-center text-[10px] font-medium text-slate-500 uppercase tracking-tighter border-r border-slate-200 dark:border-slate-700 bg-primary-50/30 dark:bg-primary-900/10">
                                    Fisik
                                </th>
                            </template>
                            <th class="px-3 py-2 text-center text-[10px] font-medium text-primary-600 uppercase tracking-tighter bg-primary-50 dark:bg-primary-950/30">
                                S
                            </th>
                            <th class="px-3 py-2 text-center text-[10px] font-medium text-primary-600 uppercase tracking-tighter bg-primary-100 dark:bg-primary-900/50">
                                F
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-if="products.data.length === 0">
                            <td :colspan="3 + (warehouses.length * 2) + 2" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                Tidak ada data produk.
                            </td>
                        </tr>
                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                {{ product.title }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ product.barcode || '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ product.category?.name || '-' }}
                            </td>
                            <template v-for="warehouse in warehouses" :key="product.id + '-' + warehouse.id">
                                <td class="px-4 py-4 text-sm text-center text-slate-500 dark:text-slate-400 border-l border-slate-200 dark:border-slate-700">
                                    {{ product.warehouse_stocks[warehouse.id]?.stock || 0 }}
                                </td>
                                <td class="px-4 py-4 text-sm text-center font-bold text-primary-600 dark:text-primary-400 border-r border-slate-200 dark:border-slate-700 bg-primary-50/20 dark:bg-primary-900/5">
                                    {{ product.warehouse_stocks[warehouse.id]?.stock_fix || 0 }}
                                </td>
                            </template>
                            <td class="px-4 py-4 text-sm text-center text-slate-400 bg-primary-50 dark:bg-primary-950/30">
                                {{ product.total_stock }}
                            </td>
                            <td class="px-4 py-4 text-sm text-center font-bold text-primary-600 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/50">
                                {{ product.total_stock_fix }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="products.data.length > 0" class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                <Pagination :links="products.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';

const props = defineProps({
    products: Object,
    warehouses: Array,
    categories: Array,
    filters: Object,
});

const filterData = ref({
    search: props.filters?.search || '',
    category_id: props.filters?.category_id || '',
});

const hasActiveFilters = computed(() => {
    return filterData.value.search || filterData.value.category_id;
});

const applyFilters = () => {
    router.get(route('reports.warehouse-stock'), filterData.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filterData.value = {
        search: '',
        category_id: '',
    };
    applyFilters();
};
</script>

