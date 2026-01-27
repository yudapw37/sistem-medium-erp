<template>
    <DashboardLayout>
        <Head :title="`Riwayat Penyusutan - ${asset.name}`" />

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Riwayat Penyusutan</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ asset.code }} - {{ asset.name }}
                    </p>
                </div>
                <a
                    :href="route('fixed-assets.index')"
                    class="px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg"
                >
                    Kembali
                </a>
            </div>
        </div>

        <!-- Asset Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Harga Perolehan</p>
                <p class="text-lg font-bold text-slate-800 dark:text-white">
                    Rp {{ Number(asset.acquisition_cost).toLocaleString('id-ID') }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Nilai Residu</p>
                <p class="text-lg font-bold text-slate-800 dark:text-white">
                    Rp {{ Number(asset.salvage_value).toLocaleString('id-ID') }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Akumulasi Penyusutan</p>
                <p class="text-lg font-bold text-orange-600 dark:text-orange-400">
                    Rp {{ Number(asset.accumulated_depreciation).toLocaleString('id-ID') }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Nilai Buku</p>
                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                    Rp {{ Number(asset.book_value).toLocaleString('id-ID') }}
                </p>
            </div>
        </div>

        <!-- Depreciation History -->
        <template v-if="depreciations.length > 0">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-semibold text-slate-800 dark:text-white">Riwayat Penyusutan</h3>
                </div>
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600 dark:text-slate-400">Periode</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-slate-600 dark:text-slate-400">Penyusutan</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-slate-600 dark:text-slate-400">Akumulasi</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-slate-600 dark:text-slate-400">Nilai Buku</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600 dark:text-slate-400">No. Jurnal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="dep in depreciations" :key="dep.id" class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">
                                {{ formatPeriod(dep.period_date) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-orange-600 dark:text-orange-400">
                                Rp {{ Number(dep.depreciation_amount).toLocaleString('id-ID') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-slate-600 dark:text-slate-400">
                                Rp {{ Number(dep.accumulated_depreciation).toLocaleString('id-ID') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-green-600 dark:text-green-400">
                                Rp {{ Number(dep.book_value).toLocaleString('id-ID') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a v-if="dep.journal" :href="route('journals.show', dep.journal.id)" class="text-primary-500 hover:underline">
                                    {{ dep.journal.reference }}
                                </a>
                                <span v-else class="text-slate-400">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <!-- Empty State -->
        <div v-else class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-8 text-center">
            <p class="text-slate-500 dark:text-slate-400">Belum ada riwayat penyusutan untuk aset ini.</p>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    asset: Object,
    depreciations: Array,
});

const formatPeriod = (date) => {
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
