<template>
    <DashboardLayout>
        <Head title="Laporan Resume Purchase" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Resume Purchase</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Rekapitulasi pembelian buku dan summary tahunan.
                    </p>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="route('old-purchases.export-products', { year: filters.year, month: filters.month })"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold shadow-lg shadow-emerald-500/20"
                    >
                        <IconFileSpreadsheet :size="18" />
                        Export Produk
                    </a>
                    <a
                        :href="route('old-purchases.export-yearly', { year: filters.year })"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-500/20"
                    >
                        <IconFileSpreadsheet :size="18" />
                        Export Tahunan
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 mb-6">
            <form @submit.prevent="applyFilter" class="flex flex-wrap items-center gap-4">
                <div class="w-full sm:w-32">
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Tahun</label>
                    <select v-model="filterForm.year" class="w-full h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-primary-500">
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Bulan (Opsional)</label>
                    <select v-model="filterForm.month" class="w-full h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-primary-500">
                        <option :value="null">Semua Bulan</option>
                        <option v-for="(name, i) in monthNames" :key="i" :value="i+1">{{ name }}</option>
                    </select>
                </div>
                <div class="w-full sm:w-auto self-end">
                    <button type="submit" class="h-10 px-6 rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-sm font-bold hover:bg-slate-800 transition-all">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left py-4 px-6 font-semibold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="text-left py-4 px-6 font-semibold text-slate-500 uppercase tracking-wider">Nama Buku</th>
                            <th class="text-center py-4 px-6 font-semibold text-slate-500 uppercase tracking-wider">Total Qty</th>
                            <th class="text-right py-4 px-6 font-semibold text-slate-500 uppercase tracking-wider">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(item, index) in items" :key="index" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-4 px-6 text-slate-400">{{ index + 1 }}</td>
                            <td class="py-4 px-6">
                                <span class="font-medium text-slate-900 dark:text-white">{{ item.nama_buku }}</span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex px-2 py-1 rounded-lg bg-primary-500/10 text-primary-600 dark:text-primary-400 font-bold">
                                    {{ formatNumber(item.total_qty) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-slate-900 dark:text-white">
                                {{ formatCurrency(item.total_nominal) }}
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="4" class="py-12 text-center text-slate-400 italic">Tidak ada data untuk periode ini.</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="items.length > 0" class="bg-slate-50 dark:bg-slate-800/50 font-bold border-t border-slate-200 dark:border-slate-700">
                        <tr>
                            <td colspan="2" class="py-4 px-6 text-right uppercase">Total Keseluruhan</td>
                            <td class="py-4 px-6 text-center text-primary-600 dark:text-primary-400">
                                {{ formatNumber(totalQty) }}
                            </td>
                            <td class="py-4 px-6 text-right text-emerald-600 dark:text-emerald-400">
                                {{ formatCurrency(totalAmount) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { IconFileSpreadsheet } from '@tabler/icons-vue';

const props = defineProps({
    items: Array,
    filters: Object,
});

const filterForm = reactive({
    year: props.filters.year,
    month: props.filters.month,
});

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - i);
const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const totalQty = computed(() => props.items.reduce((acc, item) => acc + Number(item.total_qty), 0));
const totalAmount = computed(() => props.items.reduce((acc, item) => acc + Number(item.total_nominal), 0));

const applyFilter = () => {
    router.get(route('old-purchases.resume-report'), filterForm, {
        preserveState: true,
        replace: true
    });
};

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
const route = (name, params) => window.route(name, params);
</script>
