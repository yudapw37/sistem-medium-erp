<template>
    <DashboardLayout>
        <Head title="Laporan HPP" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Harga Pokok Penjualan (HPP)</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Periode: {{ formatDate(filters.start_date) }} s/d {{ formatDate(filters.end_date) }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        :href="exportExcelUrl"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm font-medium"
                    >
                        <IconFileSpreadsheet :size="18" />
                        Export Excel
                    </a>
                    <a
                        :href="exportPdfUrl"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-medium"
                    >
                        <IconFileTypePdf :size="18" />
                        Print PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai</label>
                    <input
                        type="date"
                        v-model="form.start_date"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Akhir</label>
                    <input
                        type="date"
                        v-model="form.end_date"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
                    <select
                        v-model="form.category_id"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm"
                    >
                        <option value="">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button
                        @click="handleFilter"
                        class="h-10 px-6 bg-primary-500 hover:bg-primary-600 text-white rounded-xl text-sm font-medium flex items-center gap-2"
                    >
                        <IconFilter :size="18" />
                        Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 p-5">
                <p class="text-sm text-blue-600 dark:text-blue-400 mb-1">Total HPP</p>
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ formatCurrency(summary.totalCogs) }}</p>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-2xl border border-green-200 dark:border-green-800 p-5">
                <p class="text-sm text-green-600 dark:text-green-400 mb-1">Total Qty Terjual</p>
                <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ formatNumber(summary.totalQty) }}</p>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-2xl border border-purple-200 dark:border-purple-800 p-5">
                <p class="text-sm text-purple-600 dark:text-purple-400 mb-1">Jumlah Produk</p>
                <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ summary.productCount }}</p>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Barcode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Nama Produk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Kategori</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">HPP/Unit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Qty Terjual</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Total HPP</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Avg Jual</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(item, index) in cogsData" :key="item.product_id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ index + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-slate-600 dark:text-slate-400">{{ item.barcode || '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">{{ item.title }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ item.category_name || '-' }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white">{{ formatCurrency(item.current_buy_price) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white">{{ formatNumber(item.total_qty) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-right font-semibold text-blue-600 dark:text-blue-400">{{ formatCurrency(item.total_cogs) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white">{{ formatCurrency(item.avg_sell_price) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-right">
                                <span :class="item.margin >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    {{ formatCurrency(item.margin) }} ({{ item.margin_percent }}%)
                                </span>
                            </td>
                        </tr>
                        <tr v-if="cogsData.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                Tidak ada data HPP untuk periode ini
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-100 dark:bg-slate-800">
                        <tr class="font-bold">
                            <td colspan="5" class="px-4 py-3 text-sm text-slate-900 dark:text-white">TOTAL</td>
                            <td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white">{{ formatNumber(summary.totalQty) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-right text-blue-600 dark:text-blue-400">{{ formatCurrency(summary.totalCogs) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { IconFileSpreadsheet, IconFileTypePdf, IconFilter } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    cogsData: Array,
    summary: Object,
    filters: Object,
    categories: Array,
});

const form = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    category_id: props.filters.category_id || '',
});

const exportExcelUrl = computed(() => {
    const params = new URLSearchParams({
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        ...(form.value.category_id && { category_id: form.value.category_id }),
    });
    return route('reports.cogs.export.excel') + '?' + params.toString();
});

const exportPdfUrl = computed(() => {
    const params = new URLSearchParams({
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        ...(form.value.category_id && { category_id: form.value.category_id }),
    });
    return route('reports.cogs.export.pdf') + '?' + params.toString();
});

const handleFilter = () => {
    router.get(route('reports.cogs'), {
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        category_id: form.value.category_id || null,
    }, { preserveState: true });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
