<template>
    <DashboardLayout>
        <Head title="Laba Kotor Old" />

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laba Kotor Old</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Perhitungan Harga Pokok Penjualan (HPP) dan Laba Kotor berdasarkan data Old Transaction.
            </p>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex flex-wrap items-center gap-4">
                <div class="w-40">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Tahun</label>
                    <select v-model="filter.year" class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <div class="w-48">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Bulan</label>
                    <select v-model="filter.month" class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
                    </select>
                </div>
                <div class="flex items-end h-10 gap-2">
                    <button
                        @click="search"
                        class="px-5 py-2 rounded-lg bg-primary-500 text-white text-sm font-bold hover:bg-primary-600 transition-all flex items-center gap-2"
                    >
                        <IconSearch :size="18" />
                        Tampilkan
                    </button>
                    <button
                        @click="exportExcel"
                        class="px-5 py-2 rounded-lg bg-emerald-500 text-white text-sm font-bold hover:bg-emerald-600 transition-all flex items-center gap-2"
                    >
                        <IconFileDownload :size="18" />
                        Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <IconFileAnalytics class="text-primary-500" :size="20" />
                    RESUME LABA KOTOR PENJUALAN
                </h3>
            </div>
            
            <div class="p-0">
                <table class="w-full text-left border-collapse">
                    <tbody>
                        <!-- Pendapatan -->
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="p-4 pl-8 font-semibold text-slate-700 dark:text-slate-300">PENJUALAN BARANG JADI / DAGANGAN</td>
                            <td class="p-4 text-center text-slate-600 dark:text-slate-400 w-32">{{ summary.income.qty }} pcs</td>
                            <td class="p-4 pr-8 text-right font-bold text-slate-900 dark:text-white w-48">{{ formatCurrency(summary.income.total) }}</td>
                        </tr>
                        <tr class="bg-primary-50/30 dark:bg-primary-900/10 border-b border-slate-100 dark:border-slate-800">
                            <td class="p-4 pl-12 font-bold text-primary-700 dark:text-primary-400">Total Pendapatan</td>
                            <td class="p-4 text-center font-bold text-primary-700 dark:text-primary-400">{{ summary.income.qty }} pcs</td>
                            <td class="p-4 pr-8 text-right font-bold text-primary-700 dark:text-primary-400 border-double border-b-4 border-primary-500/20">
                                {{ formatCurrency(summary.income.total) }}
                            </td>
                        </tr>

                        <!-- HPP Section -->
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <td colspan="3" class="p-4 pl-8 bg-slate-100/50 dark:bg-slate-800/50 font-bold text-xs uppercase tracking-wider text-slate-500">Perhitungan HPP</td>
                        </tr>
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="p-4 pl-12 text-slate-700 dark:text-slate-300">Persediaan Awal Barang Jadi</td>
                            <td class="p-4 text-center text-slate-600 dark:text-slate-400">{{ summary.inventory_start.qty }} pcs</td>
                            <td class="p-4 pr-8 text-right font-semibold text-slate-900 dark:text-white">{{ formatCurrency(summary.inventory_start.total) }}</td>
                        </tr>
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="p-4 pl-12 text-slate-700 dark:text-slate-300">Pembelian Barang Dagangan</td>
                            <td class="p-4 text-center text-slate-600 dark:text-slate-400">{{ summary.purchase.qty }} pcs</td>
                            <td class="p-4 pr-8 text-right font-semibold text-slate-900 dark:text-white">{{ formatCurrency(summary.purchase.total) }}</td>
                        </tr>
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="p-4 pl-12 text-slate-700 dark:text-slate-300 italic">Persediaan Akhir Barang Jadi</td>
                            <td class="p-4 text-center text-slate-600 dark:text-slate-400 italic">{{ summary.inventory_end.qty }} pcs</td>
                            <td class="p-4 pr-8 text-right font-semibold text-slate-600 dark:text-slate-400 italic">({{ formatCurrency(summary.inventory_end.total) }})</td>
                        </tr>
                        
                        <!-- Total HPP -->
                        <tr class="bg-amber-50/30 dark:bg-amber-900/10 border-b border-slate-100 dark:border-slate-800">
                            <td class="p-4 pl-12 font-bold text-amber-700 dark:text-amber-400">Harga Pokok Penjualan (HPP)</td>
                            <td class="p-4"></td>
                            <td class="p-4 pr-8 text-right font-bold text-amber-700 dark:text-amber-400">
                                {{ formatCurrency(summary.hpp) }}
                            </td>
                        </tr>

                        <!-- Laba Kotor -->
                        <tr class="bg-emerald-500/10 border-b border-slate-100 dark:border-slate-800">
                            <td class="p-6 pl-12 text-lg font-black text-emerald-700 dark:text-emerald-400 uppercase">Laba Kotor Penjualan</td>
                            <td class="p-6"></td>
                            <td class="p-6 pr-8 text-right text-xl font-black text-emerald-700 dark:text-emerald-400 shadow-inner">
                                {{ formatCurrency(summary.profit) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { IconSearch, IconFileAnalytics, IconFileDownload } from '@tabler/icons-vue'

const props = defineProps({
    summary: Object,
    filters: Object,
})

const filter = reactive({
    year: props.filters.year,
    month: props.filters.month,
})

const years = Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i)
const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

const search = () => {
    router.get(route('old-profit-report.index'), filter, {
        preserveState: true,
        replace: true,
    })
}

const exportExcel = () => {
    window.open(route('old-profit-report.export', filter), '_blank')
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value || 0)
}
</script>

<style scoped>
.border-double {
    border-bottom-style: double;
}
</style>
