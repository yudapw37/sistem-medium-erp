<template>
    <DashboardLayout>
        <Head title="Produk Resume" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Produk Resume</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Rekapitulasi penjualan buku dari order yang terkonfirmasi (Resume Status = True).
                    </p>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 mb-6 shadow-sm">
            <div class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Bulan</label>
                    <select
                        v-model="filterForm.month"
                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm font-medium focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                    >
                        <option v-for="opt in monthOptions" :key="opt.value" :value="opt.value">
                            {{ opt.name }}
                        </option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Tahun</label>
                    <select
                        v-model="filterForm.year"
                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm font-medium focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                    >
                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                    </select>
                </div>
                <button
                    @click="applyFilter"
                    class="h-11 px-6 rounded-xl bg-primary-500 hover:bg-primary-600 active:scale-95 text-white text-sm font-bold transition-all shadow-lg shadow-primary-500/25 flex items-center justify-center gap-2"
                >
                    <IconFilter :size="18" />
                    Filter
                </button>
                <a
                    v-if="books.length > 0"
                    :href="route('old-orders.product-resume-export-excel', { month: filterForm.month, year: filterForm.year })"
                    target="_blank"
                    class="h-11 px-6 rounded-xl bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white text-sm font-bold transition-all shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2"
                >
                    <IconFileSpreadsheet :size="18" />
                    Export Excel
                </a>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-widest w-16">No</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-widest">Nama Buku</th>
                            <th class="text-center py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-widest">Jumlah Buku</th>
                            <th class="text-right py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-widest">Total Harga Buku</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(book, index) in books" :key="index" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-4 px-6 text-slate-500 font-medium">{{ index + 1 }}</td>
                            <td class="py-4 px-6 font-semibold text-slate-900 dark:text-white">{{ book.nama_buku || 'Tanpa Judul' }}</td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-bold border border-blue-100 dark:border-blue-900/30">
                                    {{ book.total_jumlah }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                {{ formatCurrency(book.total_harga) }}
                            </td>
                        </tr>
                        <tr v-if="books.length === 0">
                            <td colspan="4" class="py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <IconBooks :size="48" class="text-slate-300 dark:text-slate-700 mb-2" />
                                    <p class="text-slate-400 font-medium">Tidak ada data buku untuk periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="books.length > 0">
                        <tr class="bg-slate-50 dark:bg-slate-800/50 font-bold border-t border-slate-200 dark:border-slate-700">
                            <td colspan="2" class="py-5 px-6 text-right text-slate-900 dark:text-white uppercase tracking-widest text-xs">Total Keseluruhan</td>
                            <td class="py-5 px-6 text-center">
                                <span class="text-slate-900 dark:text-white font-black text-lg">
                                    {{ totalJumlah }}
                                </span>
                            </td>
                            <td class="py-5 px-6 text-right">
                                <span class="text-emerald-600 dark:text-emerald-400 font-black text-lg tabular-nums">
                                    {{ formatCurrency(totalHarga) }}
                                </span>
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
import { IconFilter, IconBooks, IconFileSpreadsheet } from '@tabler/icons-vue';
import { reactive, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    books: Array,
    filter: Object,
});

const filterForm = reactive({
    month: props.filter.month,
    year: props.filter.year,
});

const monthOptions = [
    { name: 'Januari', value: 1 },
    { name: 'Februari', value: 2 },
    { name: 'Maret', value: 3 },
    { name: 'April', value: 4 },
    { name: 'Mei', value: 5 },
    { name: 'Juni', value: 6 },
    { name: 'Juli', value: 7 },
    { name: 'Agustus', value: 8 },
    { name: 'September', value: 9 },
    { name: 'Oktober', value: 10 },
    { name: 'November', value: 11 },
    { name: 'Desember', value: 12 },
];

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const range = [];
    for (let i = currentYear; i >= 2015; i--) {
        range.push(i);
    }
    return range;
});

const totalJumlah = computed(() => {
    return props.books.reduce((acc, book) => acc + (parseInt(book.total_jumlah) || 0), 0);
});

const totalHarga = computed(() => {
    return props.books.reduce((acc, book) => acc + (parseFloat(book.total_harga) || 0), 0);
});

const applyFilter = () => {
    router.get(route('old-orders.product-resume'), {
        month: filterForm.month,
        year: filterForm.year,
    }, {
        preserveState: true,
        replace: true,
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
