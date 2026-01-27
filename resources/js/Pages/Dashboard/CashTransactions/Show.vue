<template>
    <DashboardLayout>
        <Head title="Detail Transaksi Kas" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        Detail Transaksi Kas
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ transaction.reference }}
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50"
                    label="Kembali"
                    :href="route('cash-transactions.index')"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Transaction Info -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div :class="[
                    'px-6 py-4',
                    transaction.type === 'in' ? 'bg-green-500' : 'bg-red-500'
                ]">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <component :is="transaction.type === 'in' ? IconArrowDownLeft : IconArrowUpRight" :size="20" />
                        {{ transaction.type === 'in' ? 'Kas Masuk' : 'Kas Keluar' }}
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">No. Referensi</span>
                        <span class="font-mono font-bold text-primary-600">{{ transaction.reference }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Tanggal</span>
                        <span class="text-slate-900 dark:text-white">{{ formatDate(transaction.date) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Status</span>
                        <span :class="[
                            'px-3 py-1 rounded-full text-sm font-semibold',
                            transaction.status === 'finalized' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'
                        ]">
                            {{ transaction.status === 'finalized' ? 'Final' : 'Draft' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Akun Kas/Bank</span>
                        <span class="text-slate-900 dark:text-white">
                            <span class="font-mono text-xs text-slate-400 mr-1">{{ transaction.cash_account?.code }}</span>
                            {{ transaction.cash_account?.name }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Akun Lawan</span>
                        <span class="text-slate-900 dark:text-white">
                            <span class="font-mono text-xs text-slate-400 mr-1">{{ transaction.account?.code }}</span>
                            {{ transaction.account?.name }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Jumlah</span>
                        <span class="text-2xl font-bold font-mono" :class="transaction.type === 'in' ? 'text-green-600' : 'text-red-600'">
                            {{ transaction.type === 'in' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                        </span>
                    </div>
                    <div class="py-2" v-if="transaction.description">
                        <span class="text-slate-500 block mb-2">Keterangan</span>
                        <p class="text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 rounded-xl p-3">
                            {{ transaction.description }}
                        </p>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Dibuat oleh</span>
                        <span class="text-slate-900 dark:text-white">{{ transaction.user?.name }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2" v-if="transaction.finalized_at">
                        <span class="text-slate-500">Di-finalize pada</span>
                        <span class="text-slate-900 dark:text-white">{{ formatDateTime(transaction.finalized_at) }}</span>
                    </div>
                </div>
            </div>

            <!-- Journal Info -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-slate-700 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <IconBooks :size="20" />
                        Jurnal Akuntansi
                    </h3>
                </div>
                <div class="p-6">
                    <template v-if="transaction.journal">
                        <div class="mb-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Referensi Jurnal</span>
                                <span class="font-mono text-primary-600">{{ transaction.journal.reference }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Tanggal</span>
                                <span class="text-slate-900 dark:text-white">{{ formatDate(transaction.journal.date) }}</span>
                            </div>
                        </div>

                        <!-- Journal Entries -->
                        <table class="w-full mt-4">
                            <thead class="bg-slate-50 dark:bg-slate-800">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Akun</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Debit</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="entry in transaction.journal.entries"
                                    :key="entry.id"
                                    class="border-b border-slate-100 dark:border-slate-800"
                                >
                                    <td class="px-3 py-2 text-sm text-slate-700 dark:text-slate-300">
                                        <span class="font-mono text-xs text-slate-400 mr-1">{{ entry.account?.code }}</span>
                                        {{ entry.account?.name }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-sm">
                                        <span v-if="entry.debit > 0" class="text-green-600">{{ formatCurrency(entry.debit) }}</span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-sm">
                                        <span v-if="entry.credit > 0" class="text-red-600">{{ formatCurrency(entry.credit) }}</span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-100 dark:bg-slate-800">
                                <tr class="font-bold">
                                    <td class="px-3 py-2 text-sm">Total</td>
                                    <td class="px-3 py-2 text-right font-mono text-sm text-green-700">
                                        {{ formatCurrency(transaction.journal.total_debit) }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-sm text-red-700">
                                        {{ formatCurrency(transaction.journal.total_credit) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </template>
                    <template v-else>
                        <div class="text-center py-8">
                            <IconAlertCircle :size="48" class="mx-auto text-yellow-400 mb-4" />
                            <p class="text-slate-500">Jurnal belum dibuat</p>
                            <p class="text-sm text-slate-400 mt-1">Finalize transaksi untuk membuat jurnal</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { IconArrowLeft, IconArrowDownLeft, IconArrowUpRight, IconBooks, IconAlertCircle } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    transaction: Object,
});

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};

const formatDateTime = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID', { 
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
