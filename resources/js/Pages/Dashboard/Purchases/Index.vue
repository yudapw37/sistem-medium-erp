<template>
    <DashboardLayout>
        <Head title="Pembelian (Purchasing)" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pembelian</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Riwayat pembelian stok dari supplier
                    </p>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="route('purchases.export.excel')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-green-500 hover:bg-green-600 text-white shadow-lg shadow-green-500/30 font-medium transition-all"
                    >
                        <IconFileSpreadsheet :size="20" />
                        <span>Export Excel</span>
                    </a>
                    <a
                        :href="route('purchases.export.pdf')"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white shadow-lg shadow-red-500/30 font-medium transition-all"
                    >
                        <IconFileTypePdf :size="20" />
                        <span>Export PDF</span>
                    </a>
                    <Button
                        type="link"
                        :icon="IconCirclePlus"
                        class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                        label="Pembelian Baru"
                        :href="route('purchases.create')"
                    />
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="w-full sm:w-80">
                <Search :url="route('purchases.index')" placeholder="Cari No. PO..." />
            </div>
        </div>

        <!-- Content -->
        <template v-if="purchases.data.length > 0">
            <TableCard title="Riwayat Pembelian">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>No. PO</TableTh>
                            <TableTh>Tanggal</TableTh>
                            <TableTh>Supplier</TableTh>
                            <TableTh>Gudang</TableTh>
                            <TableTh>Status</TableTh>
                            <TableTh class="text-right">Total</TableTh>
                            <TableTh class="text-center">Aksi</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(purchase, i) in purchases.data"
                            :key="purchase.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (purchases.current_page - 1) * purchases.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ purchase.invoice }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-600 dark:text-slate-400">
                                    {{ new Date(purchase.created_at).toLocaleString('id-ID') }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconTruckDelivery :size="16" class="text-slate-400" />
                                    <span class="text-slate-800 dark:text-slate-200">
                                        {{ purchase.supplier?.name || '-' }}
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconBuildingWarehouse :size="16" class="text-slate-400" />
                                    <span class="text-slate-600 dark:text-slate-400">
                                        {{ purchase.warehouse?.name || '-' }}
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-full text-xs font-semibold',
                                        purchase.status === 'finalized'
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    ]"
                                >
                                    {{ purchase.status === 'finalized' ? 'Finalized' : 'Draft' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-right text-slate-900 dark:text-white font-bold">
                                {{ formatCurrency(purchase.grand_total) }}
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        @click="router.visit(route('purchases.show', purchase.id))"
                                        class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition-colors"
                                        title="Detail"
                                    >
                                        <IconEye :size="18" />
                                    </button>
                                    <button
                                        v-if="purchase.status === 'draft'"
                                        @click="router.visit(route('purchases.edit', purchase.id))"
                                        class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 text-amber-600 dark:text-amber-400 transition-colors"
                                        title="Edit"
                                    >
                                        <IconEdit :size="18" />
                                    </button>
                                    <button
                                        v-if="purchase.status === 'draft'"
                                        @click="handleFinalize(purchase.id)"
                                        class="p-1.5 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30 text-green-600 dark:text-green-400 transition-colors"
                                        title="Finalize"
                                    >
                                        <IconCheck :size="18" />
                                    </button>
                                    <button
                                        v-if="purchase.status === 'draft'"
                                        @click="handleDelete(purchase.id)"
                                        class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                                        title="Hapus"
                                    >
                                        <IconTrash :size="18" />
                                    </button>
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
                Belum Ada Pembelian
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Lakukan pembelian stok pertama Anda.
            </p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Pembelian Baru"
                :href="route('purchases.create')"
            />
        </div>

        <Pagination v-if="purchases?.links && purchases.links.length > 3" :links="purchases.links" />
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import {
    IconCirclePlus,
    IconDatabaseOff,
    IconTruckDelivery,
    IconBuildingWarehouse,
    IconEye,
    IconEdit,
    IconCheck,
    IconTrash,
    IconFileSpreadsheet,
    IconFileTypePdf,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Search from '@/Components/Dashboard/Search.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';

defineProps({
    purchases: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};

const handleFinalize = (id) => {
    if (confirm('Finalize purchase? Stock will be updated and purchase will be locked.')) {
        router.post(route('purchases.finalize', id));
    }
};

const handleDelete = (id) => {
    if (confirm('Delete this draft purchase?')) {
        router.delete(route('purchases.destroy', id));
    }
};
</script>

