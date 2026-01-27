<template>
    <DashboardLayout>
        <Head title="Laporan Penjualan" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <IconTrendingUp :size="28" class="text-primary-500" />
                        Laporan Penjualan
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Analisis dan ringkasan penjualan
                    </p>
                </div>
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

            <!-- Summary Cards -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <SummaryCard
                    v-for="card in summaryCards"
                    :key="card.title"
                    :title="card.title"
                    :value="card.value"
                    :description="card.description"
                    :icon="card.icon"
                    :gradient="card.gradient"
                />
            </div>

            <!-- Filters Panel -->
            <div
                v-if="showFilters"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 animate-slide-up"
            >
                <form @submit.prevent="applyFilters">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
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
                                Invoice
                            </label>
                            <input
                                type="text"
                                placeholder="TRX-..."
                                v-model="filterData.invoice"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                            />
                        </div>
                        <InputSelect
                            label="Kasir"
                            :data="cashiers"
                            :selected="selectedCashier"
                            :set-selected="handleSelectCashier"
                            placeholder="Semua kasir"
                            :searchable="true"
                        />
                        <InputSelect
                            label="Pelanggan"
                            :data="customers"
                            :selected="selectedCustomer"
                            :set-selected="handleSelectCustomer"
                            placeholder="Semua pelanggan"
                            :searchable="true"
                        />
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
                                <TableTh>Invoice</TableTh>
                                <TableTh>Tanggal</TableTh>
                                <TableTh>Pelanggan</TableTh>
                                <TableTh>Kasir</TableTh>
                                <TableTh class="text-center">Item</TableTh>
                                <TableTh class="text-right">Subtotal</TableTh>
                                <TableTh class="text-right">Diskon</TableTh>
                                <TableTh class="text-right">Total</TableTh>
                                <TableTh class="text-right">Profit</TableTh>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="(trx, i) in rows"
                                :key="trx.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ i + 1 + (currentPage - 1) * perPage }}
                                </TableTd>
                                <TableTd class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ trx.invoice }}
                                </TableTd>
                                <TableTd class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ trx.created_at }}
                                </TableTd>
                                <TableTd class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ trx.customer?.name ?? '-' }}
                                </TableTd>
                                <TableTd class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ trx.cashier?.name ?? '-' }}
                                </TableTd>
                                <TableTd class="text-center">
                                    <span
                                        class="px-2 py-0.5 text-xs font-medium bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-400 rounded-full"
                                    >
                                        {{ trx.total_items ?? 0 }}
                                    </span>
                                </TableTd>
                                <TableTd class="text-right text-sm text-slate-600 dark:text-slate-400">
                                    {{ formatCurrency(Number(trx.grand_total || 0) + Number(trx.discount || 0)) }}
                                </TableTd>
                                <TableTd class="text-right text-sm text-danger-600 dark:text-danger-400">
                                    <span v-if="Number(trx.discount || 0) > 0">
                                        -{{ formatCurrency(Number(trx.discount || 0)) }}
                                        <span v-if="trx.discount_type === 'percent' && trx.discount_percent" class="text-xs ml-1">
                                            ({{ trx.discount_percent }}%)
                                        </span>
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </TableTd>
                                <TableTd class="text-right text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ formatCurrency(Number(trx.grand_total || 0)) }}
                                </TableTd>
                                <TableTd class="text-right text-sm font-semibold text-success-600 dark:text-success-400">
                                    {{ formatCurrency(Number(trx.total_profit || 0)) }}
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
                    <IconDatabaseOff :size="32" class="text-slate-400" />
                </div>
                <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">
                    Tidak Ada Data
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Tidak ada transaksi sesuai filter.
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
    IconCoin,
    IconDatabaseOff,
    IconDiscount2,
    IconReceipt2,
    IconShoppingBag,
    IconTrendingUp,
    IconFilter,
    IconX,
    IconSearch,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import SummaryCard from '@/Components/Dashboard/SummaryCard.vue';

const props = defineProps({
    transactions: Object,
    summary: Object,
    filters: Object,
    cashiers: Array,
    customers: Array,
});

const defaultFilterState = {
    start_date: '',
    end_date: '',
    invoice: '',
    cashier_id: '',
    customer_id: '',
    type: '',
};

const castFilterString = (value) => (typeof value === 'number' ? String(value) : value ?? '');

const showFilters = ref(false);
const filterData = ref({
    ...defaultFilterState,
    start_date: castFilterString(props.filters?.start_date),
    end_date: castFilterString(props.filters?.end_date),
    invoice: castFilterString(props.filters?.invoice),
    cashier_id: castFilterString(props.filters?.cashier_id),
    customer_id: castFilterString(props.filters?.customer_id),
    type: castFilterString(props.filters?.type),
});

const cashierFromFilters = computed(() =>
    props.cashiers.find((c) => castFilterString(c.id) === filterData.value.cashier_id) ?? null
);

const customerFromFilters = computed(() =>
    props.customers.find((c) => castFilterString(c.id) === filterData.value.customer_id) ?? null
);

const selectedCashier = ref(cashierFromFilters.value);
const selectedCustomer = ref(customerFromFilters.value);

watch(cashierFromFilters, (newVal) => {
    selectedCashier.value = newVal;
});

watch(customerFromFilters, (newVal) => {
    selectedCustomer.value = newVal;
});

watch(
    () => props.filters,
    (newFilters) => {
        filterData.value = {
            ...defaultFilterState,
            start_date: castFilterString(newFilters?.start_date),
            end_date: castFilterString(newFilters?.end_date),
            invoice: castFilterString(newFilters?.invoice),
            cashier_id: castFilterString(newFilters?.cashier_id),
            customer_id: castFilterString(newFilters?.customer_id),
            type: castFilterString(newFilters?.type),
        };
    }
);

const handleChange = (field, value) => {
    filterData.value[field] = value;
};

const handleSelectCashier = (value) => {
    selectedCashier.value = value;
    handleChange('cashier_id', value ? String(value.id) : '');
};

const handleSelectCustomer = (value) => {
    selectedCustomer.value = value;
    handleChange('customer_id', value ? String(value.id) : '');
};

const applyFilters = () => {
    router.get(route('reports.sales.index'), filterData.value, {
        preserveScroll: true,
        preserveState: true,
    });
    showFilters.value = false;
};

const resetFilters = () => {
    filterData.value = defaultFilterState;
    selectedCashier.value = null;
    selectedCustomer.value = null;
    router.get(route('reports.sales.index'), defaultFilterState, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const rows = computed(() => props.transactions?.data ?? []);
const paginationLinks = computed(() => props.transactions?.links ?? []);
const currentPage = computed(() => props.transactions?.current_page ?? 1);
const perPage = computed(() =>
    props.transactions?.per_page ? Number(props.transactions?.per_page) : rows.value.length || 1
);

const hasActiveFilters = computed(
    () =>
        filterData.value.invoice ||
        filterData.value.start_date ||
        filterData.value.end_date ||
        filterData.value.cashier_id ||
        filterData.value.customer_id ||
        filterData.value.type
);

const safeSummary = computed(() => ({
    orders_count: props.summary?.orders_count ?? 0,
    revenue_total: props.summary?.revenue_total ?? 0,
    discount_total: props.summary?.discount_total ?? 0,
    items_sold: props.summary?.items_sold ?? 0,
    profit_total: props.summary?.profit_total ?? 0,
    average_order: props.summary?.average_order ?? 0,
}));

const formatCurrency = (value = 0) => {
    const numValue = Number(value) || 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(numValue);
};

const summaryCards = computed(() => [
    {
        title: 'Pendapatan Bersih',
        value: formatCurrency(safeSummary.value.revenue_total),
        description: 'Total setelah diskon',
        icon: IconReceipt2,
        gradient: 'from-primary-500 to-primary-700',
    },
    {
        title: 'Total Profit',
        value: formatCurrency(safeSummary.value.profit_total),
        description: `Rata-rata ${formatCurrency(safeSummary.value.average_order)}`,
        icon: IconCoin,
        gradient: 'from-success-500 to-success-700',
    },
    {
        title: 'Item Terjual',
        value: safeSummary.value.items_sold.toLocaleString('id-ID'),
        description: `${safeSummary.value.orders_count} transaksi`,
        icon: IconShoppingBag,
        gradient: 'from-accent-500 to-accent-700',
    },
    {
        title: 'Diskon Diberikan',
        value: formatCurrency(safeSummary.value.discount_total),
        description: 'Akumulasi promo',
        icon: IconDiscount2,
        gradient: 'from-warning-500 to-warning-600',
    },
]);

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>


