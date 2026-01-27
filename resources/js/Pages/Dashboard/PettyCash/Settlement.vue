<template>
    <DashboardLayout>
        <Head title="Pertanggungjawaban Kas Kecil" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pertanggungjawaban Kas Kecil</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Periode: {{ activePettyCash?.reference }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        type="link"
                        :icon="IconArrowLeft"
                        class="bg-white border border-slate-200 text-slate-700"
                        label="Kembali"
                        :href="route('petty-cash.index')"
                    />
                    <Button
                        v-if="pendingCount === 0"
                        type="button"
                        @click="confirmClose"
                        :icon="IconLock"
                        class="bg-red-500 hover:bg-red-600 text-white"
                        label="Tutup Periode"
                    />
                </div>
            </div>
        </div>

        <!-- Warning for pending -->
        <div v-if="pendingCount > 0" class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <IconAlertTriangle class="text-amber-500" :size="24" />
                <div>
                    <p class="font-semibold text-amber-700">Ada {{ pendingCount }} pengeluaran yang belum disetujui</p>
                    <p class="text-sm text-amber-600">Setujui atau tolak semua pengeluaran sebelum menutup periode.</p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 mb-1">Dana Awal</p>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white">{{ formatCurrency(totalAmount) }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 mb-1">Total Pengeluaran</p>
                <p class="text-2xl font-bold font-mono text-red-600">{{ formatCurrency(totalExpenses) }}</p>
            </div>
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-6 text-white">
                <p class="text-emerald-100 text-sm mb-1">Sisa Kas</p>
                <p class="text-2xl font-bold font-mono">{{ formatCurrency(remainingBalance) }}</p>
            </div>
        </div>

        <!-- Breakdown by Category -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                <h3 class="font-bold text-slate-900 dark:text-white">Rincian per Kategori</h3>
            </div>
            <div class="p-6">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left py-2 text-sm font-semibold text-slate-600">Kategori</th>
                            <th class="text-center py-2 text-sm font-semibold text-slate-600">Jumlah Transaksi</th>
                            <th class="text-right py-2 text-sm font-semibold text-slate-600">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in expensesByCategory" :key="item.category" class="border-b border-slate-50 dark:border-slate-800">
                            <td class="py-3 text-slate-700 dark:text-slate-300">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ item.label }}
                                </span>
                            </td>
                            <td class="py-3 text-center text-slate-600">{{ item.count }} transaksi</td>
                            <td class="py-3 text-right font-mono text-red-600">{{ formatCurrency(item.total) }}</td>
                        </tr>
                        <tr v-if="expensesByCategory.length === 0">
                            <td colspan="3" class="py-8 text-center text-slate-500">Belum ada pengeluaran yang disetujui</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-800">
                        <tr class="font-bold">
                            <td class="py-3 px-2">Total</td>
                            <td class="py-3 text-center">{{ expensesByCategory.reduce((a, b) => a + b.count, 0) }} transaksi</td>
                            <td class="py-3 text-right font-mono text-red-700 px-2">{{ formatCurrency(totalExpenses) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Close Confirmation Modal -->
        <Modal :show="showCloseModal" @close="showCloseModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Konfirmasi Tutup Periode</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-4">
                    Apakah Anda yakin ingin menutup periode kas kecil ini?
                </p>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 mb-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-slate-500">Dana Awal</span>
                        <span class="font-mono">{{ formatCurrency(totalAmount) }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-slate-500">Total Pengeluaran</span>
                        <span class="font-mono text-red-600">-{{ formatCurrency(totalExpenses) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700 font-bold">
                        <span>Sisa</span>
                        <span class="font-mono text-emerald-600">{{ formatCurrency(remainingBalance) }}</span>
                    </div>
                </div>
                <p class="text-amber-600 text-sm mb-6">
                    ⚠️ Setelah ditutup, jurnal akuntansi akan otomatis dibuat dan periode tidak dapat dibuka kembali.
                </p>
                <div class="flex justify-end gap-3">
                    <Button type="button" @click="showCloseModal = false" label="Batal" class="bg-slate-100 text-slate-700" />
                    <Button type="button" @click="closePeriod" :icon="IconLock" label="Tutup Periode" class="bg-red-500 text-white" />
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconArrowLeft, IconLock, IconAlertTriangle } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Modal from '@/Components/Dashboard/Modal.vue';

const props = defineProps({
    activePettyCash: Object,
    remainingBalance: Number,
    totalAmount: Number,
    totalExpenses: Number,
    expensesByCategory: Array,
    pendingCount: Number,
});

const showCloseModal = ref(false);

const confirmClose = () => {
    showCloseModal.value = true;
};

const closePeriod = () => {
    router.post(route('petty-cash.close'), {}, {
        onSuccess: () => {
            showCloseModal.value = false;
        }
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
