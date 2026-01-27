<template>
    <DashboardLayout>
        <Head title="Laporan Pajak" />

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Pajak</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Rekap PPN Keluaran dan PPN Masukan
                    </p>
                </div>
            </div>
        </div>

        <!-- Tax Disabled Warning -->
        <div v-if="!taxEnabled" class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl">
            <p class="text-yellow-700 dark:text-yellow-300">
                <strong>Pajak Tidak Aktif.</strong> Aktifkan pajak di menu Pengaturan Pajak untuk melihat laporan.
            </p>
        </div>

        <!-- Period Filter -->
        <div class="mb-6 flex flex-wrap gap-4 items-center">
            <select
                v-model="selectedMonth"
                class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg dark:bg-slate-800 dark:text-white"
            >
                <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
            <select
                v-model="selectedYear"
                class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg dark:bg-slate-800 dark:text-white"
            >
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>
            <button
                @click="filterReport"
                class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg"
            >
                Tampilkan
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- PPN Keluaran (from Sales) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <IconArrowUp :size="24" class="text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">PPN Keluaran</p>
                        <p class="text-xs text-slate-400">Dari Penjualan</p>
                    </div>
                </div>
                <p class="text-2xl font-bold text-green-600">
                    Rp {{ formatCurrency(summary.ppnKeluaran) }}
                </p>
                <p class="text-sm text-slate-500 mt-2">
                    {{ summary.salesCount }} transaksi | Subtotal: Rp {{ formatCurrency(summary.salesSubtotal) }}
                </p>
            </div>

            <!-- PPN Masukan (from Purchases) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <IconArrowDown :size="24" class="text-red-600" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">PPN Masukan</p>
                        <p class="text-xs text-slate-400">Dari Pembelian</p>
                    </div>
                </div>
                <p class="text-2xl font-bold text-red-600">
                    Rp {{ formatCurrency(summary.ppnMasukan) }}
                </p>
                <p class="text-sm text-slate-500 mt-2">
                    {{ summary.purchaseCount }} pembelian | Subtotal: Rp {{ formatCurrency(summary.purchaseSubtotal) }}
                </p>
            </div>

            <!-- Net Tax -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div :class="[
                        'w-12 h-12 rounded-xl flex items-center justify-center',
                        summary.netTax >= 0 ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-purple-100 dark:bg-purple-900/30'
                    ]">
                        <IconReceiptTax :size="24" :class="summary.netTax >= 0 ? 'text-blue-600' : 'text-purple-600'" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ summary.netTax >= 0 ? 'PPN Kurang Bayar' : 'PPN Lebih Bayar' }}
                        </p>
                        <p class="text-xs text-slate-400">Keluaran - Masukan</p>
                    </div>
                </div>
                <p :class="[
                    'text-2xl font-bold',
                    summary.netTax >= 0 ? 'text-blue-600' : 'text-purple-600'
                ]">
                    Rp {{ formatCurrency(Math.abs(summary.netTax)) }}
                </p>
                <p class="text-sm text-slate-500 mt-2">
                    {{ summary.netTax >= 0 ? 'Harus disetor ke negara' : 'Dapat dikreditkan' }}
                </p>
            </div>
        </div>

        <!-- Summary Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Ringkasan Periode</h3>
            <table class="w-full">
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <tr>
                        <td class="py-3 text-slate-600 dark:text-slate-400">Total Penjualan (DPP)</td>
                        <td class="py-3 text-right font-medium text-slate-800 dark:text-white">Rp {{ formatCurrency(summary.salesSubtotal) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-slate-600 dark:text-slate-400">PPN Keluaran (11%)</td>
                        <td class="py-3 text-right font-medium text-green-600">+ Rp {{ formatCurrency(summary.ppnKeluaran) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-slate-600 dark:text-slate-400">Total Pembelian (DPP)</td>
                        <td class="py-3 text-right font-medium text-slate-800 dark:text-white">Rp {{ formatCurrency(summary.purchaseSubtotal) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-slate-600 dark:text-slate-400">PPN Masukan (11%)</td>
                        <td class="py-3 text-right font-medium text-red-600">- Rp {{ formatCurrency(summary.ppnMasukan) }}</td>
                    </tr>
                    <tr class="bg-slate-50 dark:bg-slate-800/50">
                        <td class="py-3 font-semibold text-slate-800 dark:text-white">PPN Terhutang / Lebih Bayar</td>
                        <td :class="[
                            'py-3 text-right font-bold text-lg',
                            summary.netTax >= 0 ? 'text-blue-600' : 'text-purple-600'
                        ]">
                            {{ summary.netTax >= 0 ? '' : '(' }}Rp {{ formatCurrency(Math.abs(summary.netTax)) }}{{ summary.netTax >= 0 ? '' : ')' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { IconArrowUp, IconArrowDown, IconReceiptTax } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    taxEnabled: Boolean,
    month: Number,
    year: Number,
    years: Array,
    summary: Object,
});

const months = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
];

const selectedMonth = ref(props.month);
const selectedYear = ref(props.year);

const filterReport = () => {
    router.get(route('reports.tax'), {
        month: selectedMonth.value,
        year: selectedYear.value,
    }, { preserveState: true });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID').format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
