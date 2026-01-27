<template>
    <DashboardLayout>
        <Head title="Laporan Stok In/Out" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Stok In/Out</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Riwayat penyesuaian stok manual berdasarkan periode.
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Cari Kode
                    </label>
                    <input
                        v-model="filterData.search"
                        type="text"
                        placeholder="ADJ-xxxxx"
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    />
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Dari Tanggal
                    </label>
                    <input
                        v-model="filterData.start_date"
                        type="date"
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Sampai Tanggal
                    </label>
                    <input
                        v-model="filterData.end_date"
                        type="date"
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    />
                </div>

                <!-- Warehouse -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Gudang
                    </label>
                    <select
                        v-model="filterData.warehouse_id"
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                        <option value="">Semua Gudang</option>
                        <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                            {{ warehouse.name }}
                        </option>
                    </select>
                </div>

                <!-- Action -->
                <div class="flex items-end gap-2">
                    <button
                        @click="applyFilters"
                        class="flex-1 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-medium transition-colors"
                    >
                        Filter
                    </button>
                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-medium transition-colors"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Kode
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Tipe
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Gudang
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Detail Barang
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Oleh
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-if="adjustments.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                Tidak ada data penyesuaian stok.
                            </td>
                        </tr>
                        <tr
                            v-for="adj in adjustments.data"
                            :key="adj.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors align-top"
                        >
                            <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">
                                {{ adj.code }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ new Date(adj.date).toLocaleDateString('id-ID') }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-full text-[10px] font-bold uppercase',
                                        adj.type === 'in'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700',
                                    ]"
                                >
                                    {{ adj.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ adj.warehouse?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <ul class="space-y-1">
                                    <li v-for="item in adj.details" :key="item.id" class="text-xs text-slate-600 dark:text-slate-400">
                                        • {{ item.product?.title }} ({{ item.qty }})
                                    </li>
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ adj.user?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-full text-[10px] font-bold uppercase',
                                        adj.status === 'finalized'
                                            ? 'bg-blue-100 text-blue-700'
                                            : 'bg-yellow-100 text-yellow-700',
                                    ]"
                                >
                                    {{ adj.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="adjustments.data.length > 0" class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                <Pagination :links="adjustments.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';

const props = defineProps({
    adjustments: Object,
    warehouses: Array,
    filters: Object,
});

const filterData = ref({
    search: props.filters?.search || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    warehouse_id: props.filters?.warehouse_id || '',
    type: props.filters?.type || '',
});

const hasActiveFilters = computed(() => {
    return filterData.value.search || filterData.value.start_date || filterData.value.end_date || filterData.value.warehouse_id || filterData.value.type;
});

const applyFilters = () => {
    router.get(route('reports.stock-adjustments.index'), filterData.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filterData.value = {
        search: '',
        start_date: '',
        end_date: '',
        warehouse_id: '',
        type: '',
    };
    applyFilters();
};
</script>

