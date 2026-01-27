<template>
    <DashboardLayout>
        <Head :title="`Kartu Piutang - ${customer.name}`" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Kartu Piutang</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ customer.name }}
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700"
                    label="Kembali"
                    :href="route('receivables.index')"
                />
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 mb-1">Total Penjualan</p>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white">{{ formatCurrency(summary.total_sales) }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 mb-1">Total Dibayar</p>
                <p class="text-2xl font-bold font-mono text-green-600">{{ formatCurrency(summary.total_paid) }}</p>
            </div>
            <div class="bg-gradient-to-r from-red-500 to-orange-500 rounded-2xl p-6 text-white">
                <p class="text-red-100 text-sm mb-1">Sisa Piutang</p>
                <p class="text-2xl font-bold font-mono">{{ formatCurrency(summary.total_remaining) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Sales List -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white">Daftar Penjualan</h3>
                </div>
                <div class="max-h-[400px] overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-800 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Invoice</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Total</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Sisa</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-slate-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sale in sales" :key="sale.id" class="border-b border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3">
                                    <p class="font-mono text-primary-600 font-bold">{{ sale.invoice }}</p>
                                    <p class="text-xs text-slate-400">{{ formatDate(sale.created_at) }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-mono">{{ formatCurrency(sale.grand_total) }}</td>
                                <td class="px-4 py-3 text-right font-mono font-bold" :class="sale.remaining > 0 ? 'text-red-600' : 'text-green-600'">
                                    {{ formatCurrency(sale.remaining) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a v-if="sale.remaining > 0" :href="route('receivables.show', sale.id)" class="text-primary-600 text-sm hover:underline">
                                        Bayar
                                    </a>
                                    <span v-else class="text-green-600 text-xs">Lunas</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white">Riwayat Pembayaran</h3>
                </div>
                <div class="max-h-[400px] overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-800 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Tanggal</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Invoice</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="payments.length > 0">
                                <tr v-for="payment in payments" :key="payment.id" class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="px-4 py-3">
                                        <p class="text-slate-700 dark:text-slate-300">{{ formatDate(payment.payment_date) }}</p>
                                        <p class="text-xs text-slate-400">{{ payment.reference }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ payment.sale?.invoice }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-green-600 font-bold">+{{ formatCurrency(payment.amount) }}</td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada pembayaran</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
    customer: Object,
    sales: Array,
    payments: Array,
    summary: Object,
});

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
