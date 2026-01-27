<template>
    <DashboardLayout>
        <Head :title="`Detail Transaksi - ${transaction.code}`" />

        <div class="mb-6">
            <Link
                :href="route('zero-value-transactions.index')"
                class="inline-flex items-center text-sm text-slate-500 hover:text-primary-600 transition-colors mb-2"
            >
                <IconArrowLeft :size="16" class="mr-1" />
                Kembali ke Daftar
            </Link>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Transaksi</h1>
                    <p class="text-sm text-slate-500">{{ transaction.code }}</p>
                </div>
                <div class="flex gap-2">
                    <template v-if="transaction.status === 'draft'">
                        <Button
                            type="link"
                            class="bg-amber-500 hover:bg-amber-600 text-white shadow-lg shadow-amber-500/30"
                            label="Edit Draft"
                            :href="route('zero-value-transactions.edit', transaction.id)"
                            :icon="IconEdit"
                        />
                        <button
                            @click="handleFinalize"
                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold flex items-center gap-2 shadow-lg shadow-emerald-500/30 transition-all"
                        >
                            <IconCheck :size="18" />
                            Finalize
                        </button>
                    </template>
                    <span
                        v-else
                        class="px-4 py-2 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-xl font-bold flex items-center gap-2"
                    >
                        <IconCheck :size="18" />
                        Finalized
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Transaction Info -->
                <Card title="Informasi Transaksi">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tipe</p>
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded-full text-xs font-semibold',
                                            transaction.type === 'in'
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        ]"
                                    >
                                        {{ transaction.type === 'in' ? 'Stok Masuk' : 'Stok Keluar' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Alasan</p>
                                    <p class="text-slate-900 dark:text-white font-medium">
                                        {{ reasonLabels[transaction.reason] || transaction.reason }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Gudang</p>
                                    <p class="text-slate-900 dark:text-white font-medium">{{ transaction.warehouse?.name }}</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</p>
                                    <p class="text-slate-900 dark:text-white font-medium">
                                        {{ new Date(transaction.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Dibuat Oleh</p>
                                    <p class="text-slate-900 dark:text-white font-medium">{{ transaction.user?.name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Catatan</p>
                                    <p class="text-slate-900 dark:text-white font-medium">{{ transaction.notes || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Item List -->
                <Card title="Daftar Produk">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-100 dark:border-slate-700">
                                <tr>
                                    <th class="px-3 py-3 text-left">Produk</th>
                                    <th class="px-3 py-3 text-center">Qty</th>
                                    <th class="px-3 py-3 text-right">HPP (Satuan)</th>
                                    <th class="px-3 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="item in transaction.details" :key="item.id">
                                    <td class="px-3 py-4">
                                        <div class="font-medium text-slate-900 dark:text-white">{{ item.product.title }}</div>
                                        <div class="text-xs text-slate-500">{{ item.product.code }}</div>
                                    </td>
                                    <td class="px-3 py-4 text-center">{{ item.qty }}</td>
                                    <td class="px-3 py-4 text-right">Rp {{ formatNumber(item.buy_price) }}</td>
                                    <td class="px-3 py-4 text-right">Rp {{ formatNumber(item.qty * item.buy_price) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50 dark:bg-slate-800/50 font-bold">
                                <tr>
                                    <td class="px-3 py-4 text-slate-900 dark:text-white">TOTAL</td>
                                    <td class="px-3 py-4 text-center">{{ totalQty }}</td>
                                    <td class="px-3 py-4"></td>
                                    <td class="px-3 py-4 text-right text-primary-600">Rp {{ formatNumber(totalValue) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </Card>
            </div>

            <!-- Status & History -->
            <div class="space-y-6">
                <Card title="Status Transaksi">
                    <div class="flex items-center gap-3 p-4 rounded-xl" :class="transaction.status === 'finalized' ? 'bg-green-50 dark:bg-green-900/20 text-green-700' : 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700'">
                        <IconCircleCheck v-if="transaction.status === 'finalized'" :size="24" />
                        <IconClock v-else :size="24" />
                        <div>
                            <p class="font-bold text-sm uppercase tracking-wider">
                                {{ transaction.status === 'finalized' ? 'Finalized' : 'Draft' }}
                            </p>
                            <p class="text-xs opacity-80" v-if="transaction.finalized_at">
                                Difinalkan pada {{ new Date(transaction.finalized_at).toLocaleString('id-ID') }}
                            </p>
                            <p class="text-xs opacity-80" v-else>
                                Masih dapat diubah atau dihapus
                            </p>
                        </div>
                    </div>
                </Card>

                <div v-if="transaction.status === 'finalized'" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30 p-4 rounded-2xl">
                    <div class="flex gap-3">
                        <IconBook class="text-blue-600 shrink-0" :size="20" />
                        <div class="text-xs text-blue-800 dark:text-blue-400 space-y-2">
                            <p class="font-bold">Info Jurnal</p>
                            <p>Transaksi ini telah mencatat jurnal akuntansi otomatis.</p>
                            <p v-if="transaction.type === 'out'">Mencatat: <b>Beban Kerugian Barang</b> (Debit) dan <b>Persediaan</b> (Kredit).</p>
                            <p v-else>Mencatat: <b>Persediaan</b> (Debit) dan <b>Pendapatan Lain-lain</b> (Kredit).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconEdit,
    IconCheck,
    IconCircleCheck,
    IconClock,
    IconBook,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from '@/Components/Dashboard/TableCard.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    transaction: Object,
    reasonLabels: Object,
});

const totalQty = computed(() => props.transaction.details.reduce((acc, item) => acc + item.qty, 0));
const totalValue = computed(() => props.transaction.details.reduce((acc, item) => acc + (item.qty * item.buy_price), 0));

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const handleFinalize = () => {
    if (confirm('Finalkan transaksi ini? Stok akan diupdate dan data akan dikunci.')) {
        router.post(route('zero-value-transactions.finalize', props.transaction.id));
    }
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
