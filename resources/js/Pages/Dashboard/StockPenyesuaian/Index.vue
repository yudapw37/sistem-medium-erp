<template>
    <DashboardLayout>
        <Head title="Stock Penyesuaian" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Stock Penyesuaian</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Kelola penyesuaian stok (stok awal, koreksi selisih) dengan jurnal akuntansi
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Penyesuaian Baru"
                    :href="route('stock-penyesuaian.create')"
                />
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Cari Kode
                    </label>
                    <input
                        v-model="filterData.search"
                        type="text"
                        placeholder="SADJ-xxxxx"
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Tipe
                    </label>
                    <select
                        v-model="filterData.type"
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                        <option value="">Semua</option>
                        <option value="in">Masuk</option>
                        <option value="out">Keluar</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Status
                    </label>
                    <select
                        v-model="filterData.status"
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                        <option value="">Semua</option>
                        <option value="draft">Draft</option>
                        <option value="finalized">Finalized</option>
                    </select>
                </div>
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
        <template v-if="penyesuaians.data.length > 0">
            <TableCard title="Daftar Stock Penyesuaian">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Kode</TableTh>
                            <TableTh>Tipe</TableTh>
                            <TableTh>Tanggal</TableTh>
                            <TableTh>Gudang</TableTh>
                            <TableTh>Status</TableTh>
                            <TableTh>Catatan</TableTh>
                            <TableTh class="text-center">Aksi</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(item, i) in penyesuaians.data"
                            :key="item.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (penyesuaians.current_page - 1) * penyesuaians.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ item.code }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-full text-xs font-semibold',
                                        item.type === 'in'
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    ]"
                                >
                                    {{ item.type === 'in' ? 'Masuk' : 'Keluar' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-600 dark:text-slate-400">
                                    {{ new Date(item.date).toLocaleDateString('id-ID') }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconBuildingWarehouse :size="16" class="text-slate-400" />
                                    <span class="text-slate-800 dark:text-slate-200">
                                        {{ item.warehouse?.name || '-' }}
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-full text-xs font-semibold',
                                        item.status === 'finalized'
                                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    ]"
                                >
                                    {{ item.status === 'finalized' ? 'Finalized' : 'Draft' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-600 dark:text-slate-400 text-sm">
                                    {{ item.notes || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        @click="router.visit(route('stock-penyesuaian.show', item.id))"
                                        class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition-colors"
                                        title="Detail"
                                    >
                                        <IconEye :size="18" />
                                    </button>
                                    <template v-if="item.status === 'draft'">
                                        <button
                                            @click="router.visit(route('stock-penyesuaian.edit', item.id))"
                                            class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 text-amber-600 dark:text-amber-400 transition-colors"
                                            title="Edit"
                                        >
                                            <IconEdit :size="18" />
                                        </button>
                                        <button
                                            @click="handleFinalize(item.id)"
                                            class="p-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 transition-colors"
                                            title="Finalize"
                                        >
                                            <IconCheck :size="18" />
                                        </button>
                                        <button
                                            @click="handleDelete(item.id)"
                                            class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                                            title="Hapus"
                                        >
                                            <IconTrash :size="18" />
                                        </button>
                                    </template>
                                </div>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
        </template>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800"
        >
            <div
                class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4"
            >
                <IconDatabaseOff :size="32" class="text-slate-400" :stroke-width="1.5" />
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">
                Belum Ada Stock Penyesuaian
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Buat penyesuaian stok pertama Anda.
            </p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Penyesuaian Baru"
                :href="route('stock-penyesuaian.create')"
            />
        </div>

        <Pagination v-if="penyesuaians?.links && penyesuaians.links.length > 3" :links="penyesuaians.links" />
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    IconCirclePlus,
    IconDatabaseOff,
    IconBuildingWarehouse,
    IconEye,
    IconEdit,
    IconTrash,
    IconCheck,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';

const props = defineProps({
    penyesuaians: Object,
    warehouses: Array,
    filters: Object,
});

const filterData = ref({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    status: props.filters?.status || '',
    warehouse_id: props.filters?.warehouse_id || '',
});

const hasActiveFilters = computed(() => {
    return filterData.value.search || filterData.value.type || filterData.value.status || filterData.value.warehouse_id;
});

const applyFilters = () => {
    router.get(route('stock-penyesuaian.index'), filterData.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filterData.value = {
        search: '',
        type: '',
        status: '',
        warehouse_id: '',
    };
    applyFilters();
};

const handleFinalize = (id) => {
    if (confirm('Finalkan penyesuaian ini? Stok dan jurnal akuntansi akan dibuat.')) {
        router.post(route('stock-penyesuaian.finalize', id));
    }
};

const handleDelete = (id) => {
    if (confirm('Hapus draf penyesuaian ini?')) {
        router.delete(route('stock-penyesuaian.destroy', id));
    }
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
