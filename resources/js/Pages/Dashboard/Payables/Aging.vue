<template>
    <DashboardLayout>
        <Head title="Aging Hutang" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Aging Report Hutang</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Analisis umur hutang kepada supplier
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700"
                    label="Kembali"
                    :href="route('payables.index')"
                />
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-gradient-to-r from-red-500 to-orange-500 rounded-2xl p-6 text-white mb-6">
            <p class="text-red-100 text-sm mb-1">Total Hutang Outstanding</p>
            <p class="text-3xl font-bold font-mono">{{ formatCurrency(totalPayables) }}</p>
        </div>

        <!-- Aging Buckets -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div
                v-for="(bucket, key) in agingData"
                :key="key"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4"
            >
                <p class="text-sm font-semibold text-slate-500 mb-1">{{ bucket.label }}</p>
                <p class="text-xl font-bold font-mono" :class="getColorClass(key)">{{ formatCurrency(bucket.total) }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ bucket.items.length }} invoice</p>
            </div>
        </div>

        <!-- Detailed List -->
        <div v-for="(bucket, key) in agingData" :key="key" class="mb-6" v-if="bucket.items.length > 0">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ bucket.label }}</h3>
                    <span :class="['px-3 py-1 rounded-full text-sm font-bold', getBadgeClass(key)]">
                        {{ formatCurrency(bucket.total) }}
                    </span>
                </div>
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Invoice</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Supplier</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Tanggal</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-slate-600">Hari</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Sisa Hutang</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in bucket.items" :key="item.purchase.id" class="border-b border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-mono text-primary-600 font-bold">{{ item.purchase.invoice }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ item.purchase.supplier?.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ formatDate(item.purchase.created_at) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['px-2 py-1 rounded-full text-xs font-bold', getDaysBadge(item.days_past)]">
                                    {{ item.days_past }} hari
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-red-600">{{ formatCurrency(item.remaining) }}</td>
                            <td class="px-4 py-3 text-center">
                                <a :href="route('payables.show', item.purchase.id)" class="text-primary-600 hover:underline text-sm">
                                    Bayar
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { IconArrowLeft } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    agingData: Object,
    totalPayables: Number,
});

const getColorClass = (key) => {
    const colors = {
        'current': 'text-green-600',
        '31-60': 'text-yellow-600',
        '61-90': 'text-orange-600',
        'over_90': 'text-red-600',
    };
    return colors[key] || 'text-slate-600';
};

const getBadgeClass = (key) => {
    const colors = {
        'current': 'bg-green-100 text-green-700',
        '31-60': 'bg-yellow-100 text-yellow-700',
        '61-90': 'bg-orange-100 text-orange-700',
        'over_90': 'bg-red-100 text-red-700',
    };
    return colors[key] || 'bg-slate-100 text-slate-700';
};

const getDaysBadge = (days) => {
    if (days <= 30) return 'bg-green-100 text-green-700';
    if (days <= 60) return 'bg-yellow-100 text-yellow-700';
    if (days <= 90) return 'bg-orange-100 text-orange-700';
    return 'bg-red-100 text-red-700';
};

const formatDate = (val) => {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
