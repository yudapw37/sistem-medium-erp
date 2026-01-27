<template>
    <DashboardLayout>
        <Head title="Transaksi (Penjualan)" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Transaksi (Penjualan)</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Manajemen transaksi penjualan barang.
                    </p>
                </div>
                <Button
                    v-if="activeTab === 'transactions'"
                    type="link"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Transaksi Baru"
                    :href="route('sales.create')"
                />
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="flex gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl w-fit">
                <button
                    @click="activeTab = 'transactions'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2',
                        activeTab === 'transactions'
                            ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <IconReceipt :size="18" />
                    Transaksi
                </button>
                <button
                    @click="activeTab = 'import'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2',
                        activeTab === 'import'
                            ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <IconFileImport :size="18" />
                    Import Transaksi
                </button>
            </div>
        </div>

        <!-- Tab: Transactions -->
        <div v-if="activeTab === 'transactions'">
            <!-- Toolbar -->
            <div class="mb-4">
                <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                    <div class="w-full sm:w-64">
                        <Search :url="route('sales.index')" placeholder="Cari No. Invoice..." />
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="date"
                            v-model="filterForm.start_date"
                            @change="handleFilter"
                            class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        />
                        <span class="text-slate-400">-</span>
                        <input
                            type="date"
                            v-model="filterForm.end_date"
                            @change="handleFilter"
                            class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        />
                    </div>
                    <div class="w-full sm:w-48">
                        <select
                            v-model="filterForm.approval_status"
                            @change="handleFilter"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-medium"
                        >
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending_finance">Pending Finance</option>
                            <option value="waiting_stock">Menunggu Stok (PO)</option>
                            <option value="pending_warehouse">Pending Gudang</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <button
                        v-if="filterForm.start_date || filterForm.end_date || filterForm.approval_status"
                        @click="clearFilters"
                        class="h-10 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-all"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Content -->
            <template v-if="sales.data.length > 0">
                <TableCard title="Riwayat Transaksi">
                    <Table>
                        <TableThead>
                            <tr>
                                <TableTh class="w-10">No</TableTh>
                                <TableTh>No. Transaksi</TableTh>
                                <TableTh>Tanggal</TableTh>
                                <TableTh>Customer</TableTh>
                                <TableTh>Gudang</TableTh>
                                <TableTh>Status</TableTh>
                                <TableTh>Approval</TableTh>
                                <TableTh class="text-right">Total</TableTh>
                                <TableTh class="text-center">Aksi</TableTh>
                            </tr>
                        </TableThead>
                        <TableTbody>
                            <tr
                                v-for="(sale, i) in sales.data"
                                :key="sale.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd class="text-center">
                                    {{ ++i + (sales.current_page - 1) * sales.per_page }}
                                </TableTd>
                                <TableTd>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-900 dark:text-white">
                                            {{ sale.invoice }}
                                        </span>
                                        <span v-if="sale.is_preorder" class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-tight">
                                            Pre-Order
                                        </span>
                                    </div>
                                </TableTd>
                                <TableTd>
                                    <span class="text-slate-600 dark:text-slate-400">
                                        {{ new Date(sale.created_at).toLocaleString('id-ID') }}
                                    </span>
                                </TableTd>
                                <TableTd>
                                    <div class="flex items-center gap-2">
                                        <IconUser :size="16" class="text-slate-400" />
                                        <span class="text-slate-800 dark:text-slate-200">
                                            {{ sale.customer?.name || '-' }}
                                        </span>
                                    </div>
                                </TableTd>
                                <TableTd>
                                    <div class="flex items-center gap-2">
                                        <IconBuildingWarehouse :size="16" class="text-slate-400" />
                                        <span class="text-slate-600 dark:text-slate-400">
                                            {{ sale.warehouse?.name || '-' }}
                                        </span>
                                    </div>
                                </TableTd>
                                <TableTd>
                                    <div class="flex flex-col gap-1">
                                        <span
                                            :class="[
                                                'px-2 py-1 rounded-full text-xs font-semibold inline-block w-fit',
                                                sale.status === 'finalized'
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                            ]"
                                        >
                                            {{ sale.status === 'finalized' ? 'Final' : 'Draft' }}
                                        </span>
                                        <span v-if="sale.is_preorder" 
                                            :class="[
                                                'px-2 py-0.5 rounded text-[10px] font-bold inline-block w-fit uppercase tracking-tighter',
                                                sale.preorder_status === 'ready' 
                                                    ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400'
                                                    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                            ]"
                                        >
                                            PO: {{ sale.preorder_status === 'ready' ? 'SIAP' : 'PENDING' }}
                                        </span>
                                    </div>
                                </TableTd>
                                <TableTd>
                                    <div class="flex flex-col gap-1">
                                        <span
                                            :class="[
                                                'px-2 py-1 rounded-full text-xs font-semibold inline-block w-fit',
                                                getApprovalStatusClass(sale.approval_status)
                                            ]"
                                        >
                                            {{ getApprovalStatusLabel(sale.approval_status) }}
                                        </span>
                                        <span
                                            v-if="sale.rejection_notes"
                                            class="text-xs text-red-600 dark:text-red-400 max-w-[150px] truncate"
                                            :title="sale.rejection_notes"
                                        >
                                            {{ sale.rejection_notes }}
                                        </span>
                                    </div>
                                </TableTd>
                                <TableTd class="text-right text-slate-900 dark:text-white font-bold">
                                    {{ formatCurrency(sale.grand_total) }}
                                </TableTd>
                                <TableTd>
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            @click="router.visit(route('sales.show', sale.id))"
                                            class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition-colors"
                                            title="Detail"
                                        >
                                            <IconEye :size="18" />
                                        </button>
                                        <button
                                            v-if="sale.approval_status === 'draft' || sale.approval_status === 'rejected'"
                                            @click="router.visit(route('sales.edit', sale.id))"
                                            class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 text-amber-600 dark:text-amber-400 transition-colors"
                                            title="Edit"
                                        >
                                            <IconEdit :size="18" />
                                        </button>
                                        <button
                                            v-if="sale.approval_status === 'draft' || sale.approval_status === 'rejected'"
                                            @click="handleFinalize(sale.id)"
                                            class="p-1.5 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30 text-green-600 dark:text-green-400 transition-colors"
                                            :title="sale.approval_status === 'rejected' ? 'Re-submit' : 'Finalize'"
                                        >
                                            <IconCheck :size="18" />
                                        </button>
                                        <button
                                            v-if="sale.approval_status === 'draft'"
                                            @click="handleDelete(sale.id)"
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
                    Belum Ada Transaksi
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                    Lakukan transaksi penjualan pertama Anda.
                </p>
                <Button
                    type="link"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white"
                    label="Transaksi Baru"
                    :href="route('sales.create')"
                />
            </div>

            <Pagination v-if="sales?.links && sales.links.length > 3" :links="sales.links" />
        </div>

        <!-- Tab: Import Transactions -->
        <div v-if="activeTab === 'import'">
            <!-- Toolbar -->
            <div class="mb-4">
                <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div class="w-full sm:w-80">
                        <div class="relative">
                            <IconSearch :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                            <input
                                type="text"
                                v-model="importSearch"
                                placeholder="Cari riwayat import..."
                                class="w-full h-10 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                            />
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a
                            :href="route('sales.import.template')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-all"
                        >
                            <IconDownload :size="18" />
                            Download Template
                        </a>
                        <Button
                            type="button"
                            :icon="IconUpload"
                            class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                            label="Import Excel"
                            @click="showImportModal = true"
                        />
                    </div>
                </div>
            </div>

            <!-- Import History Table -->
            <TableCard title="Riwayat Import">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Nama File</TableTh>
                            <TableTh>Tanggal Import</TableTh>
                            <TableTh>Diimport Oleh</TableTh>
                            <TableTh class="text-center">Total Baris</TableTh>
                            <TableTh class="text-center">Sukses</TableTh>
                            <TableTh class="text-center">Gagal</TableTh>
                            <TableTh class="text-center">Status</TableTh>
                            <TableTh class="text-center">Aksi</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <template v-if="importLogs.data && importLogs.data.length > 0">
                            <tr
                                v-for="(log, i) in importLogs.data"
                                :key="log.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd class="text-center">
                                    {{ ++i + (importLogs.current_page - 1) * importLogs.per_page }}
                                </TableTd>
                                <TableTd>
                                    <span class="font-medium text-slate-900 dark:text-white">
                                        {{ log.filename }}
                                    </span>
                                </TableTd>
                                <TableTd>
                                    <span class="text-slate-600 dark:text-slate-400">
                                        {{ new Date(log.created_at).toLocaleString('id-ID') }}
                                    </span>
                                </TableTd>
                                <TableTd>
                                    <span class="text-slate-800 dark:text-slate-200">
                                        {{ log.user?.name || '-' }}
                                    </span>
                                </TableTd>
                                <TableTd class="text-center font-medium">
                                    {{ log.total_rows }}
                                </TableTd>
                                <TableTd class="text-center">
                                    <span class="text-green-600 font-semibold">{{ log.success_count }}</span>
                                </TableTd>
                                <TableTd class="text-center">
                                    <span :class="log.failed_count > 0 ? 'text-red-600 font-semibold' : 'text-slate-400'">
                                        {{ log.failed_count }}
                                    </span>
                                </TableTd>
                                <TableTd class="text-center">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded-full text-xs font-semibold',
                                            log.status === 'completed'
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                : log.status === 'failed'
                                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                                    : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        ]"
                                    >
                                        {{ log.status === 'completed' ? 'Selesai' : log.status === 'failed' ? 'Gagal' : 'Proses' }}
                                    </span>
                                </TableTd>
                                <TableTd class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button
                                            @click="router.visit(route('sales.import.show', log.id))"
                                            class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition-colors"
                                            title="Lihat Detail"
                                        >
                                            <IconEye :size="18" />
                                        </button>
                                        <button
                                            v-if="log.failed_count > 0"
                                            @click="showErrors(log.id)"
                                            class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                                            title="Lihat Error"
                                        >
                                            <IconAlertCircle :size="18" />
                                        </button>
                                    </div>
                                </TableTd>
                            </tr>
                        </template>
                        <tr v-else>
                            <TableTd colspan="9" class="text-center py-12">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                                        <IconFileImport :size="28" class="text-slate-400" />
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada riwayat import</p>
                                    <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Klik tombol "Import Excel" untuk memulai</p>
                                </div>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
        </div>

        <!-- Import Modal -->
        <Modal :show="showImportModal" @close="showImportModal = false" title="Import Transaksi dari Excel">
            <div class="p-6">
                <form @submit.prevent="handleImport">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Pilih File Excel
                        </label>
                        <div 
                            class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-8 text-center hover:border-primary-500 transition-colors cursor-pointer"
                            @click="$refs.fileInput.click()"
                            @dragover.prevent
                            @drop.prevent="handleDrop"
                        >
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".xlsx,.xls"
                                class="hidden"
                                @change="handleFileChange"
                            />
                            <div v-if="!selectedFile">
                                <IconUpload :size="40" class="mx-auto text-slate-400 mb-3" />
                                <p class="text-slate-600 dark:text-slate-400 text-sm">
                                    Klik atau drag file Excel ke sini
                                </p>
                                <p class="text-slate-400 text-xs mt-1">
                                    Format: .xlsx, .xls (Max 10MB)
                                </p>
                            </div>
                            <div v-else class="flex items-center justify-center gap-3">
                                <IconFileSpreadsheet :size="32" class="text-green-500" />
                                <div class="text-left">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ selectedFile.name }}</p>
                                    <p class="text-xs text-slate-500">{{ formatFileSize(selectedFile.size) }}</p>
                                </div>
                                <button
                                    type="button"
                                    @click.stop="selectedFile = null"
                                    class="p-1 rounded-full hover:bg-red-100 text-red-500"
                                >
                                    <IconX :size="18" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl mb-6">
                        <div class="flex gap-3">
                            <IconAlertTriangle :size="20" class="text-amber-600 flex-shrink-0 mt-0.5" />
                            <div class="text-sm text-amber-700 dark:text-amber-300">
                                <p class="font-semibold mb-1">Perhatian:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Pastikan format file sesuai dengan template</li>
                                    <li>Customer dan produk harus sudah terdaftar di sistem</li>
                                    <li>Transaksi akan dibuat sebagai DRAFT</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button
                            type="button"
                            label="Batal"
                            class="bg-slate-100 text-slate-600 hover:bg-slate-200"
                            @click="showImportModal = false"
                        />
                        <Button
                            type="submit"
                            :icon="IconUpload"
                            label="Import"
                            class="bg-primary-500 hover:bg-primary-600 text-white"
                            :disabled="!selectedFile || importForm.processing"
                            :processing="importForm.processing"
                        />
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Error Modal -->
        <Modal :show="showErrorModal" @close="showErrorModal = false" title="Detail Error Import">
            <div class="p-6">
                <div class="mb-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ errorDetails.total_rows }}</p>
                            <p class="text-xs text-slate-500">Total Baris</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ errorDetails.success_count }}</p>
                            <p class="text-xs text-slate-500">Sukses</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-red-600">{{ errorDetails.failed_count }}</p>
                            <p class="text-xs text-slate-500">Gagal</p>
                        </div>
                    </div>
                </div>
                <div class="max-h-64 overflow-y-auto space-y-2">
                    <div
                        v-for="(error, i) in errorDetails.errors"
                        :key="i"
                        class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-300"
                    >
                        {{ error }}
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <Button
                        type="button"
                        label="Tutup"
                        class="bg-slate-100 text-slate-600 hover:bg-slate-200"
                        @click="showErrorModal = false"
                    />
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    IconCirclePlus,
    IconDatabaseOff,
    IconUser,
    IconBuildingWarehouse,
    IconEye,
    IconEdit,
    IconCheck,
    IconTrash,
    IconReceipt,
    IconFileImport,
    IconSearch,
    IconDownload,
    IconUpload,
    IconAlertCircle,
    IconAlertTriangle,
    IconFileSpreadsheet,
    IconX,
} from '@tabler/icons-vue';
import { ref } from 'vue';
import axios from 'axios';
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
import Modal from '@/Components/Dashboard/Modal.vue';

const props = defineProps({
    sales: Object,
    importLogs: Object,
    filters: Object,
});

const activeTab = ref('transactions');
const importSearch = ref('');
const showImportModal = ref(false);
const showErrorModal = ref(false);
const selectedFile = ref(null);
const errorDetails = ref({ errors: [], total_rows: 0, success_count: 0, failed_count: 0 });

const importForm = useForm({
    file: null,
});

const filterForm = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    approval_status: props.filters?.approval_status || '',
});

const handleFilter = () => {
    router.get(
        route('sales.index'),
        {
            q: props.filters?.q,
            start_date: filterForm.value.start_date,
            end_date: filterForm.value.end_date,
            approval_status: filterForm.value.approval_status,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const clearFilters = () => {
    filterForm.value.start_date = '';
    filterForm.value.end_date = '';
    filterForm.value.approval_status = '';
    router.get(route('sales.index'), {}, { preserveState: true });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
};

// Approval status helpers
const getApprovalStatusClass = (status) => {
    switch (status) {
        case 'draft':
            return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
        case 'pending_finance':
            return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'waiting_stock':
            return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
        case 'pending_warehouse':
            return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        case 'completed':
            return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
        case 'rejected':
            return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-slate-100 text-slate-600';
    }
};

const getApprovalStatusLabel = (status) => {
    switch (status) {
        case 'draft':
            return 'Draft';
        case 'pending_finance':
            return 'Pending Finance';
        case 'waiting_stock':
            return 'Menunggu Stok (PO)';
        case 'pending_warehouse':
            return 'Pending Gudang';
        case 'completed':
            return 'Completed';
        case 'rejected':
            return 'Ditolak';
        default:
            return status || 'Draft';
    }
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};

const handleFinalize = (id) => {
    if (confirm('Finalisasi transaksi? Stok akan dikurangi dan transaksi akan dikunci.')) {
        router.post(route('sales.finalize', id));
    }
};

const handleDelete = (id) => {
    if (confirm('Hapus draf transaksi ini?')) {
        router.delete(route('sales.destroy', id));
    }
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        selectedFile.value = file;
    }
};

const handleDrop = (event) => {
    const file = event.dataTransfer.files[0];
    if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
        selectedFile.value = file;
    }
};

const handleImport = () => {
    if (!selectedFile.value) return;

    importForm.file = selectedFile.value;
    router.post(route('sales.import'), {
        file: selectedFile.value,
    }, {
        forceFormData: true,
        onSuccess: () => {
            showImportModal.value = false;
            selectedFile.value = null;
        },
    });
};

const showErrors = async (logId) => {
    try {
        const response = await axios.get(route('sales.import.errors', logId));
        errorDetails.value = response.data;
        showErrorModal.value = true;
    } catch (error) {
        console.error('Failed to load errors:', error);
    }
};
</script>
