<template>
    <DashboardLayout>
        <Head title="Stok Bundling per Gudang" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Stok Bundling per Gudang</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Monitoring ketersediaan paket bundling berdasarkan stok eceran di setiap gudang</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <IconSearch :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama atau kode bundle..."
                            class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 uppercase text-xs font-bold tracking-wider">
                            <th class="px-6 py-4 min-w-[200px]">Produk Bundling</th>
                            <th v-for="warehouse in warehouses" :key="warehouse.id" class="px-6 py-4 text-center min-w-[120px]">
                                {{ warehouse.name }}
                            </th>
                            <th class="px-6 py-4 text-center font-bold text-primary-600 bg-primary-50/30 dark:bg-primary-900/10">Total Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="bundles.data.length === 0">
                            <td :colspan="warehouses.length + 2" class="px-6 py-10 text-center text-slate-500 italic">
                                Tidak ada data bundle ditemukan.
                            </td>
                        </tr>
                        <tr v-for="bundle in bundles.data" :key="bundle.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ bundle.name }}</span>
                                    <span class="text-xs text-slate-500 uppercase">{{ bundle.code }}</span>
                                </div>
                            </td>
                            <td v-for="warehouse in warehouses" :key="warehouse.id" class="px-6 py-4 text-center">
                                <span 
                                    :class="[
                                        'px-2 py-1 rounded-lg font-semibold',
                                        bundle.warehouse_stocks[warehouse.id] > 0 
                                            ? 'bg-success-100 text-success-700 dark:bg-success-900/20 dark:text-success-400' 
                                            : 'bg-danger-100 text-danger-700 dark:bg-danger-900/20 dark:text-danger-400'
                                    ]"
                                >
                                    {{ bundle.warehouse_stocks[warehouse.id] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-primary-600 bg-primary-50/10 dark:bg-primary-900/5">
                                {{ bundle.total_stock }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-slate-200 dark:border-slate-800">
                <Pagination :links="bundles.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { IconSearch, IconBuildingWarehouse, IconPackage } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import { debounce } from 'lodash';

const props = defineProps({
    bundles: Object,
    warehouses: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(route('reports.bundle-warehouse-stock'), { search: value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300));

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
