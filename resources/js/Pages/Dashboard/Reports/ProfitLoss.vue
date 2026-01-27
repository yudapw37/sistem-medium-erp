<template>
    <DashboardLayout>
        <Head title="Laporan Laba Rugi" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Laba Rugi</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Periode: {{ formatPeriod(month) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <input
                        type="month"
                        v-model="selectedMonth"
                        @change="handleFilter"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                    />
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-green-50 dark:bg-green-900/20 rounded-2xl border border-green-200 dark:border-green-800 p-5">
                <p class="text-sm text-green-600 dark:text-green-400 mb-1">Total Pendapatan</p>
                <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ formatCurrency(totalRevenue) }}</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-200 dark:border-red-800 p-5">
                <p class="text-sm text-red-600 dark:text-red-400 mb-1">Total Beban</p>
                <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ formatCurrency(totalExpense) }}</p>
            </div>
            <div :class="[
                'rounded-2xl border p-5',
                profitLoss >= 0 
                    ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' 
                    : 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800'
            ]">
                <p :class="[
                    'text-sm mb-1',
                    profitLoss >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400'
                ]">
                    {{ profitLoss >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}
                </p>
                <p :class="[
                    'text-2xl font-bold',
                    profitLoss >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-orange-700 dark:text-orange-300'
                ]">
                    {{ formatCurrency(Math.abs(profitLoss)) }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-green-500 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <IconTrendingUp :size="20" />
                        PENDAPATAN
                    </h3>
                </div>
                <div class="p-4">
                    <table class="w-full">
                        <tbody>
                            <template v-for="account in revenueAccounts" :key="account.id">
                                <tr v-if="account.level === 2" class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-2 text-slate-700 dark:text-slate-300">
                                        <span class="font-mono text-xs text-slate-500 mr-2">{{ account.code }}</span>
                                        {{ account.name }}
                                    </td>
                                    <td class="py-2 text-right font-mono text-slate-900 dark:text-white">
                                        {{ formatCurrency(account.total) }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-green-50 dark:bg-green-900/20 font-bold">
                                <td class="py-3 px-2 text-green-700 dark:text-green-300">Total Pendapatan</td>
                                <td class="py-3 px-2 text-right font-mono text-green-700 dark:text-green-300">
                                    {{ formatCurrency(totalRevenue) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Expenses -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-red-500 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <IconTrendingDown :size="20" />
                        BEBAN
                    </h3>
                </div>
                <div class="p-4">
                    <table class="w-full">
                        <tbody>
                            <template v-for="account in expenseAccounts" :key="account.id">
                                <tr v-if="account.level === 2" class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-2 text-slate-700 dark:text-slate-300">
                                        <span class="font-mono text-xs text-slate-500 mr-2">{{ account.code }}</span>
                                        {{ account.name }}
                                    </td>
                                    <td class="py-2 text-right font-mono text-slate-900 dark:text-white">
                                        {{ formatCurrency(account.total) }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-red-50 dark:bg-red-900/20 font-bold">
                                <td class="py-3 px-2 text-red-700 dark:text-red-300">Total Beban</td>
                                <td class="py-3 px-2 text-right font-mono text-red-700 dark:text-red-300">
                                    {{ formatCurrency(totalExpense) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconTrendingUp, IconTrendingDown } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    revenueAccounts: Array,
    expenseAccounts: Array,
    totalRevenue: Number,
    totalExpense: Number,
    profitLoss: Number,
    month: String,
    startDate: String,
    endDate: String,
});

const selectedMonth = ref(props.month);

const handleFilter = () => {
    router.get(route('reports.profit-loss'), {
        month: selectedMonth.value,
    }, { preserveState: true });
};

const formatPeriod = (month) => {
    const date = new Date(month + '-01');
    return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
