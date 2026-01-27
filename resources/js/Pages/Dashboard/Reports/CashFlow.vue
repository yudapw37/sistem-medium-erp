<template>
    <DashboardLayout>
        <Head title="Laporan Arus Kas" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Arus Kas</h1>
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

        <!-- Opening Balance -->
        <div class="bg-slate-100 dark:bg-slate-800 rounded-2xl p-4 mb-6">
            <div class="flex justify-between items-center">
                <span class="text-slate-700 dark:text-slate-300 font-medium">Saldo Kas Awal Periode</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white text-lg">
                    {{ formatCurrency(openingBalance) }}
                </span>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Arus Kas Operasional -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-blue-500 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <IconBuildingStore :size="20" />
                        ARUS KAS DARI AKTIVITAS OPERASI
                    </h3>
                </div>
                <div class="p-4">
                    <table class="w-full">
                        <tbody>
                            <tr v-for="item in operatingActivities" :key="item.name" class="border-b border-slate-100 dark:border-slate-800">
                                <td class="py-2 text-slate-700 dark:text-slate-300">{{ item.name }}</td>
                                <td class="py-2 text-right font-mono" :class="item.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(item.amount) }}
                                </td>
                            </tr>
                            <tr v-if="operatingActivities.length === 0">
                                <td colspan="2" class="py-4 text-center text-slate-500">Tidak ada transaksi</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-blue-50 dark:bg-blue-900/20 font-bold">
                                <td class="py-3 px-2 text-blue-700 dark:text-blue-300">Arus Kas Bersih dari Operasi</td>
                                <td class="py-3 px-2 text-right font-mono" :class="totalOperating >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ formatCurrency(totalOperating) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Arus Kas Investasi -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-purple-500 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <IconChartLine :size="20" />
                        ARUS KAS DARI AKTIVITAS INVESTASI
                    </h3>
                </div>
                <div class="p-4">
                    <table class="w-full">
                        <tbody>
                            <tr v-for="item in investingActivities" :key="item.name" class="border-b border-slate-100 dark:border-slate-800">
                                <td class="py-2 text-slate-700 dark:text-slate-300">{{ item.name }}</td>
                                <td class="py-2 text-right font-mono" :class="item.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(item.amount) }}
                                </td>
                            </tr>
                            <tr v-if="investingActivities.length === 0">
                                <td colspan="2" class="py-4 text-center text-slate-500">Tidak ada transaksi</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-purple-50 dark:bg-purple-900/20 font-bold">
                                <td class="py-3 px-2 text-purple-700 dark:text-purple-300">Arus Kas Bersih dari Investasi</td>
                                <td class="py-3 px-2 text-right font-mono" :class="totalInvesting >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ formatCurrency(totalInvesting) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Arus Kas Pendanaan -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-orange-500 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <IconCash :size="20" />
                        ARUS KAS DARI AKTIVITAS PENDANAAN
                    </h3>
                </div>
                <div class="p-4">
                    <table class="w-full">
                        <tbody>
                            <tr v-for="item in financingActivities" :key="item.name" class="border-b border-slate-100 dark:border-slate-800">
                                <td class="py-2 text-slate-700 dark:text-slate-300">{{ item.name }}</td>
                                <td class="py-2 text-right font-mono" :class="item.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(item.amount) }}
                                </td>
                            </tr>
                            <tr v-if="financingActivities.length === 0">
                                <td colspan="2" class="py-4 text-center text-slate-500">Tidak ada transaksi</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-orange-50 dark:bg-orange-900/20 font-bold">
                                <td class="py-3 px-2 text-orange-700 dark:text-orange-300">Arus Kas Bersih dari Pendanaan</td>
                                <td class="py-3 px-2 text-right font-mono" :class="totalFinancing >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ formatCurrency(totalFinancing) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="mt-6 space-y-3">
            <!-- Net Cash Flow -->
            <div class="bg-slate-800 dark:bg-slate-950 rounded-2xl px-6 py-4">
                <div class="flex justify-between items-center">
                    <span class="text-white font-bold">Kenaikan/(Penurunan) Kas Bersih</span>
                    <span class="font-mono font-bold text-xl" :class="netCashFlow >= 0 ? 'text-green-400' : 'text-red-400'">
                        {{ formatCurrency(netCashFlow) }}
                    </span>
                </div>
            </div>

            <!-- Closing Balance -->
            <div class="bg-primary-500 rounded-2xl px-6 py-4">
                <div class="flex justify-between items-center">
                    <span class="text-white font-bold">Saldo Kas Akhir Periode</span>
                    <span class="text-white font-mono font-bold text-xl">
                        {{ formatCurrency(closingBalance) }}
                    </span>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconBuildingStore, IconChartLine, IconCash } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    operatingActivities: Array,
    investingActivities: Array,
    financingActivities: Array,
    totalOperating: Number,
    totalInvesting: Number,
    totalFinancing: Number,
    netCashFlow: Number,
    openingBalance: Number,
    closingBalance: Number,
    month: String,
    startDate: String,
    endDate: String,
});

const selectedMonth = ref(props.month);

const handleFilter = () => {
    router.get(route('reports.cash-flow'), { month: selectedMonth.value }, { preserveState: true });
};

const formatPeriod = (month) => {
    const date = new Date(month + '-01');
    return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
