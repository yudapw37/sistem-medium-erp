<template>
    <DashboardLayout>
        <Head title="Neraca" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Neraca (Balance Sheet)</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Per tanggal: {{ formatDate(asOfDate) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <input
                        type="date"
                        v-model="selectedDate"
                        @change="handleFilter"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                    />
                </div>
            </div>
        </div>

        <!-- Balance Status -->
        <div class="mb-6">
            <div :class="[
                'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold',
                isBalanced ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
            ]">
                <component :is="isBalanced ? IconCheck : IconX" :size="18" />
                {{ isBalanced ? 'Neraca Seimbang' : 'Neraca Tidak Seimbang' }}
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- AKTIVA (Assets) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-blue-500 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <IconWallet :size="20" />
                        AKTIVA (ASET)
                    </h3>
                </div>
                <div class="p-4">
                    <table class="w-full">
                        <tbody>
                            <tr v-for="account in assets" :key="account.id" class="border-b border-slate-100 dark:border-slate-800">
                                <td class="py-2 text-slate-700 dark:text-slate-300">
                                    <span class="font-mono text-xs text-slate-500 mr-2">{{ account.code }}</span>
                                    {{ account.name }}
                                </td>
                                <td class="py-2 text-right font-mono text-slate-900 dark:text-white">
                                    {{ formatCurrency(account.balance) }}
                                </td>
                            </tr>
                            <tr v-if="assets.length === 0">
                                <td colspan="2" class="py-4 text-center text-slate-500">Tidak ada data</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-blue-50 dark:bg-blue-900/20 font-bold">
                                <td class="py-3 px-2 text-blue-700 dark:text-blue-300">Total Aktiva</td>
                                <td class="py-3 px-2 text-right font-mono text-blue-700 dark:text-blue-300">
                                    {{ formatCurrency(totalAssets) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- PASIVA (Liabilities + Equity) -->
            <div class="space-y-6">
                <!-- Kewajiban -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="bg-red-500 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <IconReceipt :size="20" />
                            KEWAJIBAN
                        </h3>
                    </div>
                    <div class="p-4">
                        <table class="w-full">
                            <tbody>
                                <tr v-for="account in liabilities" :key="account.id" class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-2 text-slate-700 dark:text-slate-300">
                                        <span class="font-mono text-xs text-slate-500 mr-2">{{ account.code }}</span>
                                        {{ account.name }}
                                    </td>
                                    <td class="py-2 text-right font-mono text-slate-900 dark:text-white">
                                        {{ formatCurrency(account.balance) }}
                                    </td>
                                </tr>
                                <tr v-if="liabilities.length === 0">
                                    <td colspan="2" class="py-4 text-center text-slate-500">Tidak ada data</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-red-50 dark:bg-red-900/20 font-bold">
                                    <td class="py-3 px-2 text-red-700 dark:text-red-300">Total Kewajiban</td>
                                    <td class="py-3 px-2 text-right font-mono text-red-700 dark:text-red-300">
                                        {{ formatCurrency(totalLiabilities) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Ekuitas -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="bg-purple-500 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <IconBuildingBank :size="20" />
                            EKUITAS
                        </h3>
                    </div>
                    <div class="p-4">
                        <table class="w-full">
                            <tbody>
                                <tr v-for="account in equity" :key="account.id" class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-2 text-slate-700 dark:text-slate-300">
                                        <span class="font-mono text-xs text-slate-500 mr-2">{{ account.code }}</span>
                                        {{ account.name }}
                                    </td>
                                    <td class="py-2 text-right font-mono text-slate-900 dark:text-white">
                                        {{ formatCurrency(account.balance) }}
                                    </td>
                                </tr>
                                <!-- Retained Earnings -->
                                <tr v-if="retainedEarnings !== 0" class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-2 text-slate-700 dark:text-slate-300 italic">
                                        Laba/Rugi Berjalan
                                    </td>
                                    <td class="py-2 text-right font-mono" :class="retainedEarnings >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(retainedEarnings) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-purple-50 dark:bg-purple-900/20 font-bold">
                                    <td class="py-3 px-2 text-purple-700 dark:text-purple-300">Total Ekuitas</td>
                                    <td class="py-3 px-2 text-right font-mono text-purple-700 dark:text-purple-300">
                                        {{ formatCurrency(totalEquity + retainedEarnings) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Total Pasiva -->
                <div class="bg-slate-800 dark:bg-slate-950 rounded-2xl px-6 py-4">
                    <div class="flex justify-between items-center">
                        <span class="text-white font-bold">Total Kewajiban + Ekuitas</span>
                        <span class="text-white font-mono font-bold text-xl">
                            {{ formatCurrency(totalLiabilitiesAndEquity) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconWallet, IconReceipt, IconBuildingBank, IconCheck, IconX } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assets: Array,
    liabilities: Array,
    equity: Array,
    totalAssets: Number,
    totalLiabilities: Number,
    totalEquity: Number,
    retainedEarnings: Number,
    totalLiabilitiesAndEquity: Number,
    asOfDate: String,
    isBalanced: Boolean,
});

const selectedDate = ref(props.asOfDate);

const handleFilter = () => {
    router.get(route('reports.balance-sheet'), { date: selectedDate.value }, { preserveState: true });
};

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
