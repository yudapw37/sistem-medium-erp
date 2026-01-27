<template>
    <DashboardLayout>
        <Head title="Laporan Slow Moving" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Slow Moving</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Produk dengan pergerakan lambat atau tanpa penjualan.
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-2xl border border-yellow-200 dark:border-yellow-800 p-4">
                <p class="text-sm text-yellow-600 dark:text-yellow-400">Produk Slow Moving</p>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.total_slow }}</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-200 dark:border-red-800 p-4">
                <p class="text-sm text-red-600 dark:text-red-400">Tanpa Penjualan</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.zero_sales }}</p>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 p-4">
                <p class="text-sm text-blue-600 dark:text-blue-400">Periode Analisis</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.period_days }} hari</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Filter</h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">Tanggal Referensi</label>
                    <input
                        type="date"
                        v-model="form.reference_date"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200"
                    />
                </div>
                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">Periode (bulan)</label>
                    <select
                        v-model="form.period_months"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200"
                    >
                        <option :value="1">1 Bulan</option>
                        <option :value="2">2 Bulan</option>
                        <option :value="3">3 Bulan</option>
                        <option :value="6">6 Bulan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">Batas Terjual</label>
                    <select
                        v-model="form.threshold"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200"
                    >
                        <option :value="0">0 (Tidak Terjual)</option>
                        <option :value="5">≤ 5 unit</option>
                        <option :value="10">≤ 10 unit</option>
                        <option :value="20">≤ 20 unit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">Gudang</label>
                    <select
                        v-model="form.warehouse_id"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200"
                    >
                        <option :value="null">Semua Gudang</option>
                        <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <Button
                        type="button"
                        label="Filter"
                        class="bg-primary-500 hover:bg-primary-600 text-white"
                        @click="applyFilter"
                    />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <table class="w-full">
                <TableThead>
                    <tr>
                        <TableTh>Produk</TableTh>
                        <TableTh>Kategori</TableTh>
                        <TableTh class="text-right">Total Terjual</TableTh>
                        <TableTh class="text-right">Avg/Hari</TableTh>
                        <TableTh class="text-center">Status</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <tr v-if="products.data.length === 0">
                        <TableTd colspan="5" class="text-center text-slate-500 py-8">
                            Tidak ada data produk.
                        </TableTd>
                    </tr>
                    <tr
                        v-for="product in products.data"
                        :key="product.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                    >
                        <TableTd>
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ product.title }}</p>
                                <p class="text-xs text-slate-500">{{ product.barcode }}</p>
                            </div>
                        </TableTd>
                        <TableTd>{{ product.category || '-' }}</TableTd>
                        <TableTd class="text-right font-bold" :class="product.total_sold === 0 ? 'text-red-600' : 'text-yellow-600'">
                            {{ product.total_sold }}
                        </TableTd>
                        <TableTd class="text-right">{{ product.avg_daily_sales }}</TableTd>
                        <TableTd class="text-center">
                            <span
                                :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    product.total_sold === 0
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                        : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                ]"
                            >
                                {{ product.total_sold === 0 ? 'Dead Stock' : 'Slow' }}
                            </span>
                        </TableTd>
                    </tr>
                </TableTbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <Pagination :links="products.links" />
    </DashboardLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';

const props = defineProps({
    products: Object,
    warehouses: Array,
    filters: Object,
    stats: Object,
});

const form = reactive({
    reference_date: props.filters.reference_date,
    period_months: props.filters.period_months,
    warehouse_id: props.filters.warehouse_id,
    threshold: props.filters.threshold,
});

const applyFilter = () => {
    router.get(route('reports.slow-moving'), {
        reference_date: form.reference_date,
        period_months: form.period_months,
        warehouse_id: form.warehouse_id,
        threshold: form.threshold,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
