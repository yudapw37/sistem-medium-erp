<template>
    <DashboardLayout>
        <Head title="Dashboard" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dashboard</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Ringkasan aktivitas bisnis Anda
                    </p>
                </div>
                <Link
                    :href="route('transactions.index')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium transition-colors shadow-lg shadow-primary-500/30"
                >
                    <IconShoppingCart :size="18" />
                    <span>Transaksi Baru</span>
                </Link>
            </div>

            <!-- Main Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Total Pendapatan"
                    :value="formatCurrency(totalRevenue)"
                    subtitle="Akumulasi semua transaksi"
                    :icon="IconCoin"
                    gradient="from-primary-500 to-primary-700"
                />
                <StatCard
                    title="Total Profit"
                    :value="formatCurrency(totalProfit)"
                    subtitle="Profit bersih"
                    :icon="IconTrendingUp"
                    gradient="from-success-500 to-success-700"
                    trend="up"
                />
                <StatCard
                    title="Rata-Rata Order"
                    :value="formatCurrency(averageOrder)"
                    subtitle="Per transaksi"
                    :icon="IconReceipt"
                    gradient="from-accent-500 to-accent-700"
                />
                <StatCard
                    title="Transaksi Hari Ini"
                    :value="todayTransactions"
                    subtitle="Transaksi"
                    :icon="IconClock"
                    gradient="from-warning-500 to-warning-600"
                />
            </div>

            <!-- Secondary Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <InfoCard title="Total Kategori" :value="totalCategories" :icon="IconCategory" />
                <InfoCard title="Total Produk" :value="totalProducts" :icon="IconBox" />
                <InfoCard title="Total Transaksi" :value="totalTransactions" :icon="IconMoneybag" />
                <InfoCard title="Total Pengguna" :value="totalUsers" :icon="IconUsers" />
            </div>

            <!-- Charts and Lists Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Revenue Chart -->
                <ListCard
                    title="Tren Pendapatan"
                    subtitle="12 data terakhir"
                    :icon="IconChartBar"
                    empty-message="Belum ada data pendapatan"
                >
                    <div v-if="chartData.length > 0" class="h-64">
                        <canvas ref="chartRef"></canvas>
                    </div>
                </ListCard>

                <!-- Top Products -->
                <ListCard
                    title="Produk Terlaris"
                    subtitle="Berdasarkan penjualan"
                    :icon="IconBox"
                    empty-message="Belum ada data produk"
                >
                    <ul v-if="topProducts.length > 0" class="space-y-3">
                        <li
                            v-for="(product, index) in topProducts"
                            :key="index"
                            class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 text-xs font-bold flex items-center justify-center"
                                >
                                    {{ index + 1 }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                        {{ product.name }}
                                    </p>
                                    <p class="text-xs text-slate-500">{{ product.qty }} terjual</p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ formatCurrency(product.total) }}
                            </p>
                        </li>
                    </ul>
                </ListCard>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Transactions -->
                <ListCard
                    title="Transaksi Terbaru"
                    subtitle="5 transaksi terakhir"
                    :icon="IconReceipt"
                    empty-message="Belum ada transaksi"
                >
                    <div v-if="recentTransactions.length > 0" class="space-y-3">
                        <div
                            v-for="(trx, index) in recentTransactions"
                            :key="index"
                            class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50"
                        >
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    {{ trx.invoice }}
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ trx.date }} • {{ trx.customer }}
                                </p>
                                <p class="text-xs text-slate-400">Kasir: {{ trx.cashier }}</p>
                            </div>
                            <p class="text-sm font-bold text-primary-600 dark:text-primary-400">
                                {{ formatCurrency(trx.total) }}
                            </p>
                        </div>
                    </div>
                </ListCard>

                <!-- Top Customers -->
                <ListCard
                    title="Pelanggan Terbaik"
                    subtitle="Berdasarkan nilai pembelian"
                    :icon="IconUsers"
                    empty-message="Belum ada data pelanggan"
                >
                    <ul v-if="topCustomers.length > 0" class="space-y-3">
                        <li
                            v-for="(customer, index) in topCustomers"
                            :key="index"
                            class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-accent-400 to-accent-600 flex items-center justify-center text-white text-sm font-bold"
                                >
                                    {{ customer.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                        {{ customer.name }}
                                    </p>
                                    <p class="text-xs text-slate-500">{{ customer.orders }} transaksi</p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ formatCurrency(customer.total) }}
                            </p>
                        </li>
                    </ul>
                </ListCard>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import {
    IconBox,
    IconCategory,
    IconMoneybag,
    IconUsers,
    IconCoin,
    IconReceipt,
    IconTrendingUp,
    IconArrowUpRight,
    IconArrowDownRight,
    IconShoppingCart,
    IconChartBar,
    IconClock,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import InfoCard from '@/Components/Dashboard/InfoCard.vue';
import ListCard from '@/Components/Dashboard/ListCard.vue';

// Route helper - make route available in template
// Route is set in app.js via globalProperties, so it should be available as $route
// But we'll create a helper function for easier access
const route = (name, params) => {
    // Try window.route first (from Ziggy)
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    // Fallback - route should be available from globalProperties
    return '#';
};

const props = defineProps({
    totalCategories: Number,
    totalProducts: Number,
    totalTransactions: Number,
    totalUsers: Number,
    revenueTrend: Array,
    totalRevenue: Number,
    totalProfit: Number,
    averageOrder: Number,
    todayTransactions: Number,
    topProducts: {
        type: Array,
        default: () => [],
    },
    recentTransactions: {
        type: Array,
        default: () => [],
    },
    topCustomers: {
        type: Array,
        default: () => [],
    },
});

const formatCurrency = (value = 0) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);

const chartRef = ref(null);
const chartInstance = ref(null);

const chartData = computed(() => props.revenueTrend ?? []);

const setupChart = () => {
    if (!chartRef.value || !chartData.value.length) return;

    if (chartInstance.value) {
        chartInstance.value.destroy();
        chartInstance.value = null;
    }

    const labels = chartData.value.map((item) => item.label);
    const totals = chartData.value.map((item) => item.total);

    const ctx = chartRef.value.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0.01)');

    chartInstance.value = new Chart(chartRef.value, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Pendapatan',
                    data: totals,
                    borderColor: '#6366f1',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#6366f1',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#f1f5f9',
                    bodyColor: '#f1f5f9',
                    padding: 12,
                    borderRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: (ctx) => formatCurrency(ctx.raw),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatCurrency(value),
                        color: '#94a3b8',
                        font: { size: 11 },
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)',
                        drawBorder: false,
                    },
                    border: { display: false },
                },
                x: {
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                    },
                    grid: { display: false },
                    border: { display: false },
                },
            },
        },
    });
};

watch(chartData, () => {
    setupChart();
});

onMounted(() => {
    setupChart();
});

onUnmounted(() => {
    if (chartInstance.value) {
        chartInstance.value.destroy();
    }
});
</script>



