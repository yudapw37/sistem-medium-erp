<template>
    <DashboardLayout>
        <Head title="Detail Stock Opname" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Stock Opname</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Informasi lengkap stock opname.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="opname.status === 'draft'"
                        type="button"
                        :icon="IconCheck"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white"
                        label="Finalize"
                        @click="handleFinalize"
                    />
                    <Button
                        type="link"
                        :icon="IconArrowLeft"
                        class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        label="Kembali"
                        :href="route('stock-opnames.index')"
                    />
                </div>
            </div>
        </div>

        <!-- Header Info -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Kode</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ opname.code }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Tanggal</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ new Date(opname.date).toLocaleDateString('id-ID') }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Gudang</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ opname.warehouse?.name }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Status</p>
                <span
                    :class="[
                        'inline-block px-3 py-1 rounded-full text-sm font-semibold',
                        opname.status === 'finalized'
                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                    ]"
                >
                    {{ opname.status === 'finalized' ? 'Finalized' : 'Draft' }}
                </span>
            </div>
        </div>

        <!-- Details Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Detail Produk</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 text-left text-sm text-slate-500">
                            <th class="pb-3 pl-2">No</th>
                            <th class="pb-3">Produk</th>
                            <th class="pb-3 text-center">Stok Aktual</th>
                            <th class="pb-3 text-center">Stok Sistem (Fix)</th>
                            <th class="pb-3 text-center">Stok Fisik</th>
                            <th class="pb-3 text-center">Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="(detail, index) in opname.details" :key="detail.id">
                            <td class="py-3 pl-2 text-slate-600 dark:text-slate-400">{{ index + 1 }}</td>
                            <td class="py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-900 dark:text-white">{{ detail.product?.title }}</span>
                                    <span class="text-xs text-slate-500">{{ detail.product?.barcode }}</span>
                                </div>
                            </td>
                            <td class="py-3 text-center">
                                <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-sm font-medium text-purple-700 dark:text-purple-400">
                                    {{ detail.current_stock || 0 }}
                                </span>
                            </td>
                            <td class="py-3 text-center">
                                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ detail.system_stock }}
                                </span>
                            </td>
                            <td class="py-3 text-center">
                                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-sm font-medium text-blue-700 dark:text-blue-400">
                                    {{ detail.physical_stock }}
                                </span>
                            </td>
                            <td class="py-3 text-center">
                                <span 
                                    :class="[
                                        'px-3 py-1 rounded-lg text-sm font-semibold',
                                        detail.difference > 0 
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            : detail.difference < 0
                                            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                            : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                    ]"
                                >
                                    {{ detail.difference > 0 ? '+' : '' }}{{ detail.difference }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <div v-if="opname.notes" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-2">Catatan</h3>
            <p class="text-slate-600 dark:text-slate-400">{{ opname.notes }}</p>
        </div>

        <!-- Additional Info -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Informasi Tambahan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-500 dark:text-slate-400">Dibuat oleh</p>
                    <p class="font-medium text-slate-900 dark:text-white">{{ opname.user?.name }}</p>
                </div>
                <div>
                    <p class="text-slate-500 dark:text-slate-400">Dibuat pada</p>
                    <p class="font-medium text-slate-900 dark:text-white">
                        {{ new Date(opname.created_at).toLocaleString('id-ID') }}
                    </p>
                </div>
                <div v-if="opname.status === 'finalized'">
                    <p class="text-slate-500 dark:text-slate-400">Difinalisasi pada</p>
                    <p class="font-medium text-slate-900 dark:text-white">
                        {{ new Date(opname.finalized_at).toLocaleString('id-ID') }}
                    </p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { IconArrowLeft, IconCheck } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    opname: Object,
});

const handleFinalize = () => {
    if (confirm('Finalkan stock opname ini? Stok akan diupdate sesuai hasil penghitungan fisik dan data akan dikunci.')) {
        router.post(route('stock-opnames.finalize', props.opname.id));
    }
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>

