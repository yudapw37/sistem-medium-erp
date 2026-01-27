<template>
    <DashboardLayout>
        <Head title="Riwayat Transaksi" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <IconHistory :size="28" class="text-primary-500" />
                        Riwayat Transaksi
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ transactions?.total || 0 }} transaksi tercatat
                    </p>
                </div>
                <div class="flex gap-2">
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
                    <Link
                        :href="route('transactions.index')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium transition-colors shadow-lg shadow-primary-500/30"
                    >
                        <IconReceipt :size="18" />
                        <span>Transaksi Baru</span>
                    </Link>
                </div>
            </div>

            <!-- Filters Panel -->
            <div
                v-if="showFilters"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 animate-slide-up"
            >
                <form @submit.prevent="applyFilters">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Nomor Invoice
                            </label>
                            <input
                                type="text"
                                placeholder="TRX-..."
                                v-model="filterData.invoice"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
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
                                Tipe Produk
                            </label>
                            <select
                                v-model="filterData.type"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-medium"
                            >
                                <option value="">Semua</option>
                                <option value="product">Eceran</option>
                                <option value="bundle">Bundling</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="flex-1 h-11 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-medium transition-colors"
                            >
                                <IconSearch :size="18" />
                                <span>Cari</span>
                            </button>
                            <button
                                v-if="hasActiveFilters"
                                type="button"
                                @click="resetFilters"
                                class="h-11 px-4 inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            >
                                <IconX :size="18" />
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Transaction List -->
            <div
                v-if="rows.length > 0"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <TableTh>No</TableTh>
                                <TableTh>Invoice</TableTh>
                                <TableTh>Tanggal</TableTh>
                                <TableTh>Pelanggan</TableTh>
                                <TableTh class="text-center">Item</TableTh>
                                <TableTh class="text-right">Subtotal</TableTh>
                                <TableTh class="text-right">Diskon</TableTh>
                                <TableTh class="text-right">Total</TableTh>
                                <TableTh class="text-right">Profit</TableTh>
                                <TableTh class="text-center"></TableTh>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="(transaction, index) in rows"
                                :key="transaction.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ index + 1 + (currentPage - 1) * perPage }}
                                </TableTd>
                                <TableTd>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ transaction.invoice }}
                                    </span>
                                </TableTd>
                                <TableTd class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ transaction.created_at }}
                                </TableTd>
                                <TableTd>
                                    <span
                                        class="px-2 py-1 text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-md"
                                    >
                                        {{ transaction.customer?.name ?? 'Umum' }}
                                    </span>
                                </TableTd>
                                <TableTd class="text-center">
                                    <span
                                        class="px-2 py-1 text-xs font-medium bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-400 rounded-full"
                                    >
                                        {{ transaction.total_items ?? 0 }}
                                    </span>
                                </TableTd>
                                <TableTd class="text-right text-sm text-slate-600 dark:text-slate-400">
                                    {{ formatCurrency(Number(transaction.grand_total || 0) + Number(transaction.discount || 0) + Number(transaction.event_discount || 0)) }}
                                </TableTd>
                                <TableTd class="text-right text-sm text-danger-600 dark:text-danger-400">
                                    <div v-if="Number(transaction.discount || 0) > 0">
                                        -{{ formatCurrency(Number(transaction.discount || 0)) }}
                                        <span v-if="transaction.discount_type === 'percent' && transaction.discount_percent" class="text-xs ml-1">
                                            ({{ transaction.discount_percent }}%)
                                        </span>
                                    </div>
                                    <div v-if="Number(transaction.event_discount || 0) > 0">
                                        -{{ formatCurrency(Number(transaction.event_discount || 0)) }} (Event)
                                    </div>
                                    <span v-if="!Number(transaction.discount || 0) && !Number(transaction.event_discount || 0)" class="text-slate-400">-</span>
                                </TableTd>
                                <TableTd class="text-right text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ formatCurrency(Number(transaction.grand_total || 0)) }}
                                </TableTd>
                                <TableTd class="text-right text-sm font-semibold text-success-600 dark:text-success-400">
                                    {{ formatCurrency(Number(transaction.total_profit || 0)) }}
                                </TableTd>
                                <TableTd class="text-center">
                                    <Link
                                        :href="route('transactions.print', transaction.invoice)"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-950/50 transition-colors"
                                        title="Cetak Struk"
                                    >
                                        <IconPrinter :size="18" />
                                    </Link>
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
                <div
                    class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4"
                >
                    <IconDatabaseOff :size="32" class="text-slate-400" :stroke-width="1.5" />
                </div>
                <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">
                    Belum Ada Transaksi
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ hasActiveFilters ? 'Tidak ada transaksi sesuai filter.' : 'Transaksi akan muncul di sini.' }}
                </p>
            </div>

            <Pagination v-if="links && links.length > 3" :links="links" />
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import {
    IconDatabaseOff,
    IconSearch,
    IconHistory,
    IconReceipt,
    IconPrinter,
    IconFilter,
    IconX,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const defaultFilters = {
    invoice: '',
    start_date: '',
    end_date: '',
    type: '',
};

const filterData = ref({
    ...defaultFilters,
    ...props.filters,
});
const showFilters = ref(false);

watch(
    () => props.filters,
    (newFilters) => {
        filterData.value = {
            ...defaultFilters,
            ...newFilters,
        };
    },
    { immediate: true }
);

const hasActiveFilters = computed(
    () => filterData.value.invoice || filterData.value.start_date || filterData.value.end_date || filterData.value.type
);

const rows = computed(() => props.transactions?.data ?? []);
const links = computed(() => props.transactions?.links ?? []);
const currentPage = computed(() => props.transactions?.current_page ?? 1);
const perPage = computed(() =>
    props.transactions?.per_page ? Number(props.transactions?.per_page) : rows.value.length || 1
);

const applyFilters = () => {
    router.get(route('transactions.history'), filterData.value, {
        preserveScroll: true,
        preserveState: true,
    });
    showFilters.value = false;
};

const resetFilters = () => {
    filterData.value = defaultFilters;
    router.get(route('transactions.history'), defaultFilters, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const formatCurrency = (value = 0) => {
    const numValue = Number(value) || 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(numValue);
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>


