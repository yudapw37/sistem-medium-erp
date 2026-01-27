<template>
    <DashboardLayout>
        <Head title="Detail Stock Penyesuaian" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Stock Penyesuaian</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ penyesuaian.code }}
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="route('stock-penyesuaian.index')"
                />
            </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400">Kode</span>
                <p class="font-bold text-slate-900 dark:text-white">{{ penyesuaian.code }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400">Tipe</span>
                <p :class="penyesuaian.type === 'in' ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
                    {{ penyesuaian.type === 'in' ? 'Stok Masuk' : 'Stok Keluar' }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400">Tanggal</span>
                <p class="font-bold text-slate-900 dark:text-white">
                    {{ new Date(penyesuaian.date).toLocaleDateString('id-ID') }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400">Status</span>
                <p>
                    <span
                        :class="[
                            'px-2 py-1 rounded-full text-xs font-semibold',
                            penyesuaian.status === 'finalized'
                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                        ]"
                    >
                        {{ penyesuaian.status === 'finalized' ? 'Finalized' : 'Draft' }}
                    </span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400">Gudang</span>
                <p class="font-bold text-slate-900 dark:text-white">
                    {{ penyesuaian.warehouse?.name || '-' }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400">Dibuat Oleh</span>
                <p class="font-bold text-slate-900 dark:text-white">
                    {{ penyesuaian.user?.name || '-' }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <span class="text-xs text-slate-500 dark:text-slate-400">Catatan</span>
                <p class="text-slate-700 dark:text-slate-300">
                    {{ penyesuaian.notes || '-' }}
                </p>
            </div>
        </div>

        <!-- Details Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Detail Produk</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 text-left text-sm text-slate-500">
                            <th class="pb-3 pl-2 w-12">No</th>
                            <th class="pb-3">Produk</th>
                            <th class="pb-3 text-center">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="(detail, index) in penyesuaian.details" :key="detail.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="py-3 pl-2 text-slate-600 dark:text-slate-400">{{ index + 1 }}</td>
                            <td class="py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-900 dark:text-white text-sm">{{ detail.product?.title }}</span>
                                    <span class="text-xs text-slate-500">{{ detail.product?.barcode }}</span>
                                </div>
                            </td>
                            <td class="py-3 text-center">
                                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ detail.qty }}
                                </span>
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

defineProps({
    penyesuaian: Object,
});

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
