<template>
    <DashboardLayout>
        <Head title="Old Purchase" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Old Purchase</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Data pembelian buku dari file PDF (Faktur Pajak).
                    </p>
                </div>
                <Link
                    :href="route('old-purchases.upload')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-500 text-white font-semibold hover:bg-primary-600 transition-all shadow-lg shadow-primary-500/25"
                >
                    <IconPlus :size="18" />
                    Upload PDF
                </Link>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('old-purchases.index')" placeholder="Cari Supplier / No. Faktur..." />
                </div>
            </div>
        </div>

        <!-- Content -->
        <template v-if="oldPurchases.data.length > 0">
            <TableCard title="Daftar Old Purchase">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>No. Faktur</TableTh>
                            <TableTh>Supplier</TableTh>
                            <TableTh class="text-right">Subtotal</TableTh>
                            <TableTh class="text-right">PPN</TableTh>
                            <TableTh class="text-right">Total</TableTh>
                            <TableTh>Tanggal</TableTh>
                            <TableTh class="text-center">Aksi</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(purchase, i) in oldPurchases.data"
                            :key="purchase.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (oldPurchases.current_page - 1) * oldPurchases.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ purchase.nomor_faktur || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconTruck :size="16" class="text-slate-400" />
                                    <span class="text-slate-800 dark:text-slate-200">
                                        {{ purchase.supplier }}
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd class="text-right text-slate-600 dark:text-slate-400">
                                {{ formatCurrency(purchase.subtotal) }}
                            </TableTd>
                            <TableTd class="text-right text-slate-600 dark:text-slate-400">
                                {{ formatCurrency(purchase.ppn) }}
                            </TableTd>
                            <TableTd class="text-right text-slate-900 dark:text-white font-bold">
                                {{ formatCurrency(purchase.harga_total) }}
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-600 dark:text-slate-400">
                                    {{ purchase.tanggal_faktur ? new Date(purchase.tanggal_faktur).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="route('old-purchases.show', purchase.id)"
                                        class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition-colors"
                                        title="Detail"
                                    >
                                        <IconEye :size="18" />
                                    </Link>
                                    <button
                                        @click="destroy(purchase.id)"
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
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                <IconDatabaseOff :size="32" class="text-slate-400" :stroke-width="1.5" />
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">
                Tidak Ada Data
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Belum ada data purchase yang diupload.
            </p>
            <Link
                :href="route('old-purchases.upload')"
                class="mt-4 px-4 py-2 rounded-xl bg-primary-500 text-white font-semibold hover:bg-primary-600 transition-all"
            >
                Upload Sekarang
            </Link>
        </div>

        <Pagination v-if="oldPurchases?.links && oldPurchases.links.length > 3" :links="oldPurchases.links" />

    </DashboardLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import {
    IconEye,
    IconDatabaseOff,
    IconPlus,
    IconTruck,
    IconTrash,
} from '@tabler/icons-vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Search from '@/Components/Dashboard/Search.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    oldPurchases: Object,
});

const destroy = (id) => {
    Swal.fire({
        title: 'Apakah anda yakin?',
        text: "Anda tidak akan dapat mengembalikan ini!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('old-purchases.destroy', id), {
                onSuccess: () => {
                    Swal.fire(
                        'Dihapus!',
                        'Data berhasil dihapus.',
                        'success'
                    )
                }
            });
        }
    })
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

// Route helper (inline if ziggy not global)
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
