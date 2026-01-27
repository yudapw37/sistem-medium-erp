<template>
    <DashboardLayout>
        <Head title="Detail Import Transaksi" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Import Transaksi</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ importLog.filename }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button
                        type="link"
                        :icon="IconArrowLeft"
                        class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        label="Kembali"
                        :href="route('sales.index')"
                    />
                    <Button
                        v-if="hasDraftTransactions"
                        type="button"
                        :icon="IconCheck"
                        :class="[
                            canFinalize
                                ? 'bg-green-500 hover:bg-green-600 text-white shadow-lg shadow-green-500/30'
                                : 'bg-slate-300 text-slate-500 cursor-not-allowed'
                        ]"
                        :label="canFinalize ? 'Finalize Semua' : 'Stok Belum Siap'"
                        :disabled="!canFinalize || isProcessing"
                        @click="handleFinalize"
                    />
                </div>
            </div>
        </div>

        <!-- Import Summary Card -->
        <div class="mb-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ importLog.total_rows }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total Baris</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ importLog.success_count }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Transaksi Sukses</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600">{{ importLog.failed_count }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Baris Gagal</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ importLog.user?.name || '-' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Diimport Oleh</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ formatDate(importLog.created_at) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tanggal Import</p>
                </div>
            </div>
        </div>

        <!-- Stock Comparison Card -->
        <TableCard title="Perbandingan Stok Produk" class="mb-6">
            <template #header>
                <div class="flex items-center gap-2 text-sm">
                    <span :class="insufficientCount > 0 ? 'text-red-600' : 'text-green-600'" class="font-semibold">
                        {{ insufficientCount > 0 ? `${insufficientCount} produk stok kurang` : 'Semua stok mencukupi' }}
                    </span>
                </div>
            </template>
            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>Produk</TableTh>
                        <TableTh>Barcode</TableTh>
                        <TableTh>Gudang</TableTh>
                        <TableTh class="text-center">Total Qty Import</TableTh>
                        <TableTh class="text-center">Stok Tersedia</TableTh>
                        <TableTh class="text-center">Selisih</TableTh>
                        <TableTh class="text-center">Status</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="stockComparison.length > 0">
                        <tr
                            v-for="(item, i) in stockComparison"
                            :key="`${item.product_id}_${item.warehouse_id}`"
                            :class="[
                                'transition-colors',
                                !item.is_sufficient ? 'bg-red-50 dark:bg-red-900/10' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'
                            ]"
                        >
                            <TableTd class="text-center">{{ i + 1 }}</TableTd>
                            <TableTd>
                                <span class="font-medium text-slate-900 dark:text-white">{{ item.product_title }}</span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-500 text-sm">{{ item.product_barcode }}</span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconBuildingWarehouse :size="14" class="text-slate-400" />
                                    <span class="text-sm">{{ item.warehouse_name }}</span>
                                </div>
                            </TableTd>
                            <TableTd class="text-center">
                                <span class="font-bold text-primary-600 dark:text-primary-400">{{ item.total_qty }}</span>
                            </TableTd>
                            <TableTd class="text-center">
                                <span :class="item.current_stock < item.total_qty ? 'text-red-600 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                    {{ item.current_stock }}
                                </span>
                            </TableTd>
                            <TableTd class="text-center">
                                <span v-if="item.shortage > 0" class="text-red-600 font-bold">
                                    -{{ item.shortage }}
                                </span>
                                <span v-else class="text-green-600 font-semibold">
                                    +{{ item.current_stock - item.total_qty }}
                                </span>
                            </TableTd>
                            <TableTd class="text-center">
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-full text-xs font-semibold',
                                        item.is_sufficient
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                    ]"
                                >
                                    {{ item.is_sufficient ? 'Cukup' : 'Kurang' }}
                                </span>
                            </TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="8" class="text-center py-8 text-slate-500">
                            Tidak ada data perbandingan stok
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <!-- Sales Import List -->
        <TableCard title="Daftar Transaksi Import" class="mb-6">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>No. Invoice</TableTh>
                        <TableTh>Customer</TableTh>
                        <TableTh>Gudang</TableTh>
                        <TableTh>Pembayaran</TableTh>
                        <TableTh class="text-right">Total</TableTh>
                        <TableTh class="text-center">Status</TableTh>
                        <TableTh class="text-center">Aksi</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="salesImport.length > 0">
                        <tr
                            v-for="(sale, i) in salesImport"
                            :key="sale.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer"
                            :class="selectedSale?.id === sale.id ? 'bg-primary-50 dark:bg-primary-900/20' : ''"
                            @click="selectSale(sale)"
                        >
                            <TableTd class="text-center">{{ i + 1 }}</TableTd>
                            <TableTd>
                                <span class="font-bold text-slate-900 dark:text-white">{{ sale.invoice }}</span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconUser :size="16" class="text-slate-400" />
                                    <span>{{ sale.customer?.name || '-' }}</span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconBuildingWarehouse :size="16" class="text-slate-400" />
                                    <span>{{ sale.warehouse?.name || '-' }}</span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <span class="uppercase text-xs font-semibold">{{ sale.payment_type }}</span>
                            </TableTd>
                            <TableTd class="text-right font-bold text-slate-900 dark:text-white">
                                {{ formatCurrency(sale.grand_total) }}
                            </TableTd>
                            <TableTd class="text-center">
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-full text-xs font-semibold',
                                        sale.status === 'finalized'
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    ]"
                                >
                                    {{ sale.status === 'finalized' ? 'Finalized' : 'Draft' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-center">
                                <button
                                    @click.stop="selectSale(sale)"
                                    class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition-colors"
                                    title="Lihat Detail"
                                >
                                    <IconEye :size="18" />
                                </button>
                            </TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="8" class="text-center py-8 text-slate-500">
                            Tidak ada transaksi import
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <!-- Sale Details Import -->
        <TableCard :title="selectedSale ? `Detail Item - ${selectedSale.invoice}` : 'Detail Item (Pilih transaksi di atas)'">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>Produk</TableTh>
                        <TableTh>Barcode</TableTh>
                        <TableTh class="text-right">Harga</TableTh>
                        <TableTh class="text-center">Qty</TableTh>
                        <TableTh class="text-right">Diskon</TableTh>
                        <TableTh class="text-right">Subtotal</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="selectedSale && selectedSale.details?.length > 0">
                        <tr
                            v-for="(detail, i) in selectedSale.details"
                            :key="detail.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">{{ i + 1 }}</TableTd>
                            <TableTd>
                                <span class="font-medium text-slate-900 dark:text-white">
                                    {{ detail.product?.title || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-500 text-sm">{{ detail.product?.barcode || '-' }}</span>
                            </TableTd>
                            <TableTd class="text-right">{{ formatCurrency(detail.sell_price) }}</TableTd>
                            <TableTd class="text-center font-medium">{{ detail.qty }}</TableTd>
                            <TableTd class="text-right">
                                <span :class="detail.discount > 0 ? 'text-red-500' : 'text-slate-400'">
                                    {{ detail.discount > 0 ? '-' + formatCurrency(detail.discount) : '-' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-right font-bold text-slate-900 dark:text-white">
                                {{ formatCurrency((detail.sell_price * detail.qty) - detail.discount) }}
                            </TableTd>
                        </tr>
                        <!-- Total Row -->
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <TableTd colspan="6" class="text-right font-bold text-slate-600 dark:text-slate-400">
                                TOTAL
                            </TableTd>
                            <TableTd class="text-right font-black text-primary-600 dark:text-primary-400 text-lg">
                                {{ formatCurrency(selectedSale.grand_total) }}
                            </TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="7" class="text-center py-8 text-slate-500">
                            <div class="flex flex-col items-center">
                                <IconPackage :size="32" class="text-slate-300 mb-2" />
                                <p>{{ selectedSale ? 'Tidak ada item' : 'Pilih transaksi untuk melihat detail item' }}</p>
                            </div>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    IconArrowLeft,
    IconUser,
    IconBuildingWarehouse,
    IconEye,
    IconPackage,
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

const props = defineProps({
    importLog: Object,
    salesImport: Array,
    stockComparison: Array,
});

const selectedSale = ref(props.salesImport.length > 0 ? props.salesImport[0] : null);
const isProcessing = ref(false);

const insufficientCount = computed(() => {
    return props.stockComparison.filter(item => !item.is_sufficient).length;
});

const canFinalize = computed(() => {
    return insufficientCount.value === 0 && props.salesImport.length > 0;
});

const hasDraftTransactions = computed(() => {
    return props.salesImport.some(sale => sale.status === 'draft');
});

const selectSale = (sale) => {
    selectedSale.value = sale;
};

const handleFinalize = () => {
    if (!canFinalize.value) return;
    
    const count = props.salesImport.filter(s => s.status === 'draft').length;
    if (confirm(`Finalize ${count} transaksi import? Stok akan dikurangi dan transaksi akan masuk ke daftar penjualan utama.`)) {
        isProcessing.value = true;
        router.post(route('sales.import.finalize', props.importLog.id), {}, {
            onFinish: () => {
                isProcessing.value = false;
            }
        });
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const formatDate = (value) => {
    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
