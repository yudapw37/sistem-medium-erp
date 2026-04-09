<template>
    <DashboardLayout>
        <Head title="Laporan Persediaan Bulanan" />

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Persediaan Bulanan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Pergerakan stok barang berdasarkan data final dari order aktif dan purchase aktif.
            </p>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <!-- Month Selector -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-600 dark:text-slate-400">Bulan:</label>
                    <select
                        v-model="selectedMonth"
                        @change="navigate"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm font-semibold focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                    >
                        <option v-for="m in 12" :key="m" :value="m">
                            {{ monthNames[m] }}
                        </option>
                    </select>
                </div>

                <!-- Year Selector -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-600 dark:text-slate-400">Tahun:</label>
                    <select
                        v-model="selectedYear"
                        @change="navigate"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm font-semibold focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                    >
                        <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>

                <!-- Navigation Arrows -->
                <div class="flex items-center gap-1">
                    <button @click="prevMonth" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 transition-colors">
                        <IconChevronLeft :size="18" />
                    </button>
                    <button @click="nextMonth" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 transition-colors">
                        <IconChevronRight :size="18" />
                    </button>
                </div>

                <!-- Export Button -->
                <button
                    @click="exportExcel"
                    class="h-10 px-4 flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all shadow-lg shadow-emerald-600/20"
                >
                    <IconFileSpreadsheet :size="18" />
                    <span>Export Excel</span>
                </button>

                <!-- Info -->
                <div class="ml-auto text-sm text-slate-500">
                    <span class="font-bold text-slate-800 dark:text-white">{{ report.length }}</span> barang
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <!-- Search -->
            <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                <div class="relative w-full sm:w-72">
                    <IconSearch :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari kode / nama barang..."
                        class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" v-if="filteredReport.length > 0">
                    <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800/60">
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-center">
                            <th rowspan="2" class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase w-8">No</th>
                            <th rowspan="2" class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Kode Barang</th>
                            <th rowspan="2" class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Nama Barang</th>
                            <th rowspan="2" class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Harga (Rp)</th>
                            <th colspan="2" class="py-2 text-xs font-semibold text-slate-500 uppercase bg-blue-50 dark:bg-blue-900/20 text-center border-b border-slate-200 dark:border-slate-700">Stock Awal</th>
                            <th colspan="2" class="py-2 text-xs font-semibold text-slate-500 uppercase bg-emerald-50 dark:bg-emerald-900/20 text-center border-b border-slate-200 dark:border-slate-700">Masuk</th>
                            <th colspan="2" class="py-2 text-xs font-semibold text-slate-500 uppercase bg-red-50 dark:bg-red-900/20 text-center border-b border-slate-200 dark:border-slate-700">Keluar</th>
                            <th colspan="2" class="py-2 text-xs font-semibold text-slate-500 uppercase bg-amber-50 dark:bg-amber-900/20 text-center border-b border-slate-200 dark:border-slate-700">Stock Akhir</th>
                        </tr>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="py-2 px-3 text-xs font-semibold text-slate-500 uppercase text-right bg-blue-50 dark:bg-blue-900/20">Qty</th>
                            <th class="py-2 px-3 text-xs font-semibold text-slate-500 uppercase text-right bg-blue-50 dark:bg-blue-900/20">Nominal</th>
                            <th class="py-2 px-3 text-xs font-semibold text-slate-500 uppercase text-right bg-emerald-50 dark:bg-emerald-900/20">Qty</th>
                            <th class="py-2 px-3 text-xs font-semibold text-slate-500 uppercase text-right bg-emerald-50 dark:bg-emerald-900/20">Nominal</th>
                            <th class="py-2 px-3 text-xs font-semibold text-slate-500 uppercase text-right bg-red-50 dark:bg-red-900/20">Qty</th>
                            <th class="py-2 px-3 text-xs font-semibold text-slate-500 uppercase text-right bg-red-50 dark:bg-red-900/20">Nominal</th>
                            <th class="py-2 px-3 text-xs font-semibold text-slate-500 uppercase text-right bg-amber-50 dark:bg-amber-900/20">Qty</th>
                            <th class="py-2 px-3 text-xs font-semibold text-slate-500 uppercase text-right bg-amber-50 dark:bg-amber-900/20">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, idx) in filteredReport"
                            :key="item.code_barang"
                            class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            :class="{ 'bg-red-50/50 dark:bg-red-900/10': item.stock_akhir < 0 }"
                        >
                            <td class="py-3 px-4 text-slate-400 text-xs">{{ idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="font-mono text-xs text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                                    {{ item.code_barang }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-900 dark:text-white">
                                {{ item.nama_barang || '-' }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-500">
                                {{ formatNumber(item.hpp) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold bg-blue-50/50 dark:bg-blue-900/10"
                                :class="item.stock_awal === 0 ? 'text-slate-300 dark:text-slate-600' : 'text-blue-700 dark:text-blue-400'">
                                {{ formatNumber(item.stock_awal) }}
                            </td>
                            <td class="py-3 px-4 text-right bg-blue-50/50 dark:bg-blue-900/10 text-xs"
                                :class="item.nominal_awal === 0 ? 'text-slate-300 dark:text-slate-600' : 'text-slate-600 dark:text-slate-400'">
                                {{ formatNumber(item.nominal_awal) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold bg-emerald-50/50 dark:bg-emerald-900/10"
                                :class="item.stock_masuk === 0 ? 'text-slate-300 dark:text-slate-600' : 'text-emerald-700 dark:text-emerald-400'">
                                {{ item.stock_masuk > 0 ? '+' : '' }}{{ formatNumber(item.stock_masuk) }}
                            </td>
                            <td class="py-3 px-4 text-right bg-emerald-50/50 dark:bg-emerald-900/10 text-xs"
                                :class="item.nominal_masuk === 0 ? 'text-slate-300 dark:text-slate-600' : 'text-slate-600 dark:text-slate-400'">
                                {{ item.stock_masuk > 0 ? '+' : '' }}{{ formatNumber(item.nominal_masuk) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold bg-red-50/50 dark:bg-red-900/10"
                                :class="item.stock_keluar === 0 ? 'text-slate-300 dark:text-slate-600' : 'text-red-600 dark:text-red-400'">
                                {{ item.stock_keluar > 0 ? '-' : '' }}{{ formatNumber(item.stock_keluar) }}
                            </td>
                            <td class="py-3 px-4 text-right bg-red-50/50 dark:bg-red-900/10 text-xs"
                                :class="item.nominal_keluar === 0 ? 'text-slate-300 dark:text-slate-600' : 'text-slate-600 dark:text-slate-400'">
                                {{ item.stock_keluar > 0 ? '-' : '' }}{{ formatNumber(item.nominal_keluar) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold bg-amber-50/50 dark:bg-amber-900/10"
                                :class="item.stock_akhir < 0 ? 'text-red-600 dark:text-red-400' : item.stock_akhir === 0 ? 'text-slate-300 dark:text-slate-600' : 'text-amber-700 dark:text-amber-400'">
                                {{ formatNumber(item.stock_akhir) }}
                            </td>
                            <td class="py-3 px-4 text-right bg-amber-50/50 dark:bg-amber-900/10 text-xs"
                                :class="item.nominal_akhir < 0 ? 'text-red-600 dark:text-red-400' : item.nominal_akhir === 0 ? 'text-slate-300 dark:text-slate-600' : 'text-slate-600 dark:text-slate-400'">
                                {{ formatNumber(item.nominal_akhir) }}
                            </td>
                        </tr>
                    </tbody>
                    <!-- Totals -->
                    <tfoot class="sticky bottom-0 bg-slate-100 dark:bg-slate-800 border-t-2 border-slate-300 dark:border-slate-600">
                        <tr>
                            <td colspan="4" class="py-3 px-4 font-bold text-slate-900 dark:text-white text-right uppercase text-xs tracking-wider">
                                Total
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-blue-700 dark:text-blue-400 bg-blue-100/50 dark:bg-blue-900/20">
                                {{ formatNumber(totals.stock_awal) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-blue-800 dark:text-blue-500 bg-blue-100/50 dark:bg-blue-900/20 text-xs">
                                {{ formatNumber(totals.nominal_awal) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100/50 dark:bg-emerald-900/20">
                                +{{ formatNumber(totals.stock_masuk) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-800 dark:text-emerald-500 bg-emerald-100/50 dark:bg-emerald-900/20 text-xs">
                                +{{ formatNumber(totals.nominal_masuk) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-red-600 dark:text-red-400 bg-red-100/50 dark:bg-red-900/20">
                                -{{ formatNumber(totals.stock_keluar) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-red-700 dark:text-red-500 bg-red-100/50 dark:bg-red-900/20 text-xs">
                                -{{ formatNumber(totals.nominal_keluar) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-amber-700 dark:text-amber-400 bg-amber-100/50 dark:bg-amber-900/20">
                                {{ formatNumber(totals.stock_akhir) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-amber-800 dark:text-amber-500 bg-amber-100/50 dark:bg-amber-900/20 text-xs">
                                {{ formatNumber(totals.nominal_akhir) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div v-else class="flex flex-col items-center justify-center py-16">
                    <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                        <IconDatabaseOff :size="32" class="text-slate-400" :stroke-width="1.5" />
                    </div>
                    <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">Tidak Ada Data</h3>
                    <p class="text-sm text-slate-500">
                        {{ searchQuery ? 'Tidak ditemukan barang dengan pencarian tersebut.' : 'Belum ada data stok untuk periode ini.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="mt-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800/30 p-4 text-sm text-blue-800 dark:text-blue-300">
            <div class="flex items-start gap-2">
                <IconInfoCircle :size="18" class="mt-0.5 flex-shrink-0" />
                <div>
                    <p class="font-semibold mb-1">Catatan:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs text-blue-700 dark:text-blue-400">
                        <li>Stock Awal = stock awal dasar + total masuk sebelum bulan ini − total keluar sebelum bulan ini</li>
                        <li>Stock Masuk = purchase aktif (final) berdasarkan <strong>tanggal faktur</strong> di bulan ini</li>
                        <li>Stock Keluar = order aktif (final) berdasarkan <strong>tanggal order</strong> di bulan ini</li>
                        <li>Stock Akhir bulan ini = Stock Awal bulan berikutnya</li>
                    </ul>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import {
    IconChevronLeft,
    IconChevronRight,
    IconDatabaseOff,
    IconFileSpreadsheet,
    IconInfoCircle,
    IconSearch,
} from '@tabler/icons-vue';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    report: Array,
    totals: Object,
    filters: Object,
});

const selectedYear = ref(props.filters.year);
const selectedMonth = ref(props.filters.month);
const searchQuery = ref('');

const monthNames = [
    '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const currentYear = new Date().getFullYear();
const yearOptions = Array.from({ length: 11 }, (_, i) => currentYear - 5 + i);

const filteredReport = computed(() => {
    if (!searchQuery.value) return props.report;
    const q = searchQuery.value.toLowerCase();
    return props.report.filter(item =>
        String(item.code_barang).toLowerCase().includes(q) ||
        String(item.nama_barang || '').toLowerCase().includes(q)
    );
});

const navigate = () => {
    router.get(route('old-stock-report.monthly'), {
        year: selectedYear.value,
        month: selectedMonth.value,
    }, {
        preserveState: false,
        preserveScroll: false,
    });
};

const prevMonth = () => {
    if (selectedMonth.value <= 1) {
        selectedMonth.value = 12;
        selectedYear.value--;
    } else {
        selectedMonth.value--;
    }
    navigate();
};

const nextMonth = () => {
    navigate();
};

const exportExcel = () => {
    window.location.href = route('old-stock-report.monthly.export', {
        year: selectedYear.value,
        month: selectedMonth.value,
    });
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
