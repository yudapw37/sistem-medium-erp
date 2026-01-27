<template>
    <DashboardLayout>
        <Head title="Laporan Stok" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <IconChartInfographic :size="28" class="text-primary-500" />
                        Laporan Stok
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Riwayat mutasi stok (Masuk/Keluar)
                    </p>
                </div>
                <!-- Filters Toggle -->
                <button
                    @click="showFilters = !showFilters"
                    :class="[
                        'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-medium transition-colors',
                        showFilters || hasActiveFilters
                            ? 'bg-primary-50 border-primary-200 text-primary-700 dark:bg-primary-950/50 dark:border-primary-800 dark:text-primary-400'
                            : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800',
                    ]"
                >
                    <IconFilter :size="18" />
                    <span>Filter</span>
                    <span v-if="hasActiveFilters" class="w-2 h-2 rounded-full bg-primary-500"></span>
                </button>
            </div>

            <!-- Filters Panel -->
            <div
                v-if="showFilters"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 animate-slide-up"
            >
                <form @submit.prevent="applyFilters">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <InputSelect
                                label="Gudang"
                                :data="warehouses"
                                :selected="selectedWarehouse"
                                :set-selected="handleSelectWarehouse"
                                placeholder="Semua Gudang"
                                :searchable="true"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Tanggal Mulai
                            </label>
                            <input
                                type="date"
                                v-model="filterData.start_date"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Tanggal Akhir
                            </label>
                            <input
                                type="date"
                                v-model="filterData.end_date"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Produk
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    placeholder="Cari nama produk..."
                                    v-model="filterData.product"
                                    class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                                />
                                <IconSearch :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            @click="resetFilters"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        >
                            <IconX :size="18" />
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-medium transition-colors"
                        >
                            <IconSearch :size="18" />
                            Terapkan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div
                v-if="rows.length > 0"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <TableTh>No</TableTh>
                                <TableTh>Tanggal</TableTh>
                                <TableTh>Gudang</TableTh>
                                <TableTh>Produk</TableTh>
                                <TableTh class="text-center">Tipe</TableTh>
                                <TableTh class="text-center">Qty</TableTh>
                                <TableTh class="text-center">Perubahan Stok</TableTh>
                                <TableTh>User</TableTh>
                                <TableTh>Keterangan</TableTh>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="(stock, i) in rows"
                                :key="stock.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd>
                                    {{ i + 1 + (currentPage - 1) * perPage }}
                                </TableTd>
                                <TableTd class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ new Date(stock.created_at).toLocaleString('id-ID') }}
                                </TableTd>
                                <TableTd class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ stock.warehouse?.name || '-' }}
                                </TableTd>
                                <TableTd>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden flex-shrink-0">
                                            <img
                                                v-if="stock.product?.image"
                                                :src="getProductImageUrl(stock.product.image)"
                                                :alt="stock.product.title"
                                                class="w-full h-full object-cover"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                <IconBox :size="18" class="text-slate-400" />
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800 dark:text-white">
                                                {{ stock.product?.title || 'Produk Terhapus' }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ stock.product?.barcode || '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </TableTd>
                                <TableTd class="text-center">
                                    <span v-if="stock.type === 'in'" class="px-2 py-1 text-xs font-bold bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400 rounded-full">
                                        MASUK
                                    </span>
                                    <span v-else class="px-2 py-1 text-xs font-bold bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400 rounded-full">
                                        KELUAR
                                    </span>
                                </TableTd>
                                <TableTd class="text-center font-bold">
                                    {{ stock.qty }}
                                </TableTd>
                                <TableTd class="text-center text-sm">
                                    <span class="text-slate-500">{{ stock.previous_stock }}</span>
                                    <span class="mx-2 text-slate-400">→</span>
                                    <span class="text-slate-900 dark:text-white font-medium">{{ stock.current_stock }}</span>
                                </TableTd>
                                <TableTd class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ stock.user?.name || '-' }}
                                </TableTd>
                                <TableTd class="text-sm text-slate-600 dark:text-slate-400">
                                    <div v-if="stock.transaction_id" class="flex items-center gap-1">
                                        <span class="text-primary-500 font-medium">TRX</span>
                                        <span>{{ stock.note || 'Penjualan' }}</span>
                                    </div>
                                    <span v-else>{{ stock.note || '-' }}</span>
                                </TableTd>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800"
            >
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                    <IconDatabaseOff :size="32" class="text-slate-400" />
                </div>
                <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">
                    Tidak Ada Data
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Belum ada riwayat stok.
                </p>
            </div>

            <Pagination v-if="paginationLinks && paginationLinks.length > 3" :links="paginationLinks" />
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    IconChartInfographic,
    IconFilter,
    IconSearch,
    IconX,
    IconDatabaseOff,
    IconBox,
    IconBuildingWarehouse,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';
import { getProductImageUrl } from '@/Utils/imageUrl';

const props = defineProps({
    stocks: Object,
    warehouses: Array,
    filters: Object,
});

const defaultFilterState = {
    start_date: '',
    end_date: '',
    product: '',
    warehouse_id: '',
};

const castFilterString = (value) => value ?? '';

const showFilters = ref(false);
const filterData = ref({
    start_date: castFilterString(props.filters?.start_date),
    end_date: castFilterString(props.filters?.end_date),
    product: castFilterString(props.filters?.product),
    warehouse_id: castFilterString(props.filters?.warehouse_id),
});

const selectedWarehouse = ref(
    props.filters?.warehouse_id 
        ? props.warehouses.find(w => String(w.id) === String(props.filters.warehouse_id)) 
        : null
);

const handleSelectWarehouse = (value) => {
    selectedWarehouse.value = value;
    filterData.value.warehouse_id = value ? value.id : '';
};

const hasActiveFilters = computed(
    () => filterData.value.start_date || filterData.value.end_date || filterData.value.product || filterData.value.warehouse_id
);

const rows = computed(() => props.stocks?.data ?? []);
const paginationLinks = computed(() => props.stocks?.links ?? []);
const currentPage = computed(() => props.stocks?.current_page ?? 1);
const perPage = computed(() => props.stocks?.per_page ?? 10);

const applyFilters = () => {
    router.get(route('reports.stocks.index'), filterData.value, {
        preserveScroll: true,
        preserveState: true,
    });
    showFilters.value = false;
};

const resetFilters = () => {
    filterData.value = { ...defaultFilterState };
    selectedWarehouse.value = null;
    router.get(route('reports.stocks.index'), defaultFilterState, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>

