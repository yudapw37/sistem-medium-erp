<template>
    <DashboardLayout>
        <Head title="Detail Old Purchase" />

        <!-- Header -->
        <div class="mb-6">
            <Link
                :href="route('old-purchases.index')"
                class="inline-flex items-center gap-2 text-slate-500 hover:text-primary-500 transition-colors mb-2"
            >
                <IconArrowLeft :size="18" />
                <span>Kembali ke Daftar</span>
            </Link>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Purchase</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ oldPurchase.nomor_faktur }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Information Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <TableCard title="Informasi Faktur">
                    <div class="p-6 space-y-4">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No. Faktur</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ oldPurchase.nomor_faktur || '-' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Supplier</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ oldPurchase.supplier }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal Faktur</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ oldPurchase.tanggal_faktur ? new Date(oldPurchase.tanggal_faktur).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                            </span>
                        </div>
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col pt-4">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sumber File</span>
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ oldPurchase.pdf_filename }}</span>
                            <span class="text-[10px] text-slate-400">Halaman {{ oldPurchase.pdf_page }}</span>
                        </div>
                    </div>
                </TableCard>

                <div class="bg-primary-600 rounded-2xl p-6 text-white shadow-xl shadow-primary-500/20">
                    <h3 class="text-xs font-bold uppercase tracking-widest opacity-70 mb-1">Total Pembayaran</h3>
                    <div class="text-2xl font-black mb-4">{{ formatCurrency(oldPurchase.harga_total) }}</div>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-center bg-white/10 p-2 rounded-lg">
                            <span class="opacity-70">Subtotal (DPP)</span>
                            <span class="font-bold">{{ formatCurrency(oldPurchase.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between items-center bg-white/10 p-2 rounded-lg">
                            <span class="opacity-70">PPN (11%)</span>
                            <span class="font-bold">{{ formatCurrency(oldPurchase.ppn) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="lg:col-span-3">
                <TableCard title="Daftar Barang">
                    <Table>
                        <TableThead>
                            <tr>
                                <TableTh class="w-10">No</TableTh>
                                <TableTh>Kode Barang</TableTh>
                                <TableTh>Nama Barang</TableTh>
                                <TableTh class="text-center">Qty</TableTh>
                                <TableTh class="text-right">Harga Satuan</TableTh>
                                <TableTh class="text-right">Total</TableTh>
                            </tr>
                        </TableThead>
                        <TableTbody>
                            <tr
                                v-for="(detail, i) in oldPurchase.details"
                                :key="detail.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd class="text-center">{{ ++i }}</TableTd>
                                <TableTd>
                                    <span
                                        v-if="detail.code_barang"
                                        class="px-2 py-1 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-mono font-bold text-xs"
                                    >
                                        {{ detail.code_barang }}
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-1 rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 font-medium text-xs"
                                    >
                                        Belum Mapping
                                    </span>
                                </TableTd>
                                <TableTd>
                                    <span class="text-slate-800 dark:text-slate-200 font-medium">
                                        {{ detail.nama }}
                                    </span>
                                </TableTd>
                                <TableTd class="text-center">
                                    <span class="px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300">
                                        {{ detail.qty }}
                                    </span>
                                </TableTd>
                                <TableTd class="text-right text-slate-600 dark:text-slate-400">
                                    {{ formatCurrency(detail.harga_satuan) }}
                                </TableTd>
                                <TableTd class="text-right font-bold text-slate-900 dark:text-white">
                                    {{ formatCurrency(detail.total) }}
                                </TableTd>
                            </tr>
                        </TableTbody>
                        <tfoot>
                            <tr class="bg-slate-50 dark:bg-slate-800/50">
                                <td colspan="5" class="p-4 text-right font-bold text-slate-500 uppercase tracking-wider text-xs">Total Harga Jual</td>
                                <td class="p-4 text-right font-black text-slate-900 dark:text-white underline decoration-primary-500/50 underline-offset-4">
                                    {{ formatCurrency(oldPurchase.subtotal) }}
                                </td>
                            </tr>
                        </tfoot>
                    </Table>
                </TableCard>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
    IconArrowLeft,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';

const props = defineProps({
    oldPurchase: Object,
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
</script>
