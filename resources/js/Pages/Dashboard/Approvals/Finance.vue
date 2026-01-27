<template>
    <DashboardLayout>
        <Head title="Verifikasi Finance" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Verifikasi Finance</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Daftar transaksi menunggu approval dari Finance
                    </p>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-4">
            <div class="w-full sm:w-80">
                <Search :url="route('approvals.finance.index')" placeholder="Cari No. Invoice..." />
            </div>
        </div>

        <!-- Table -->
        <TableCard title="Pending Finance Approval">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>No. Invoice</TableTh>
                        <TableTh>Tanggal</TableTh>
                        <TableTh>Customer</TableTh>
                        <TableTh>Gudang</TableTh>
                        <TableTh>Dibuat Oleh</TableTh>
                        <TableTh class="text-right">Total</TableTh>
                        <TableTh class="text-center">Aksi</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="sales.data.length > 0">
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
                                    <span class="font-bold text-slate-900 dark:text-white">{{ sale.invoice }}</span>
                                    <span v-if="sale.is_preorder" class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-tight">
                                        Pre-Order
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-600 dark:text-slate-400">
                                    {{ formatDate(sale.created_at) }}
                                </span>
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
                            <TableTd>{{ sale.user?.name || '-' }}</TableTd>
                            <TableTd class="text-right font-bold text-slate-900 dark:text-white">
                                {{ formatCurrency(sale.grand_total) }}
                            </TableTd>
                            <TableTd class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        @click="openApproveModal(sale)"
                                        class="p-1.5 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30 text-green-600 dark:text-green-400 transition-colors"
                                        title="Approve"
                                    >
                                        <IconCheck :size="18" />
                                    </button>
                                    <button
                                        @click="openRejectModal(sale)"
                                        class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                                        title="Reject"
                                    >
                                        <IconX :size="18" />
                                    </button>
                                    <button
                                        @click="router.visit(route('approvals.show', sale.id))"
                                        class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition-colors"
                                        title="Detail"
                                    >
                                        <IconEye :size="18" />
                                    </button>
                                </div>
                            </TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-3">
                                    <IconCheck :size="28" class="text-green-500" />
                                </div>
                                <p class="text-slate-500 dark:text-slate-400">Tidak ada transaksi yang menunggu approval</p>
                            </div>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <Pagination v-if="sales?.links && sales.links.length > 3" :links="sales.links" />

        <!-- Approve Modal -->
        <Modal :show="showApproveModal" @close="showApproveModal = false" title="Approve Transaksi">
            <div class="p-6">
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                    <p class="text-sm text-green-700 dark:text-green-300">
                        Anda akan meng-approve transaksi <strong>{{ selectedSale?.invoice }}</strong>. 
                        Transaksi akan diteruskan ke Gudang untuk approval selanjutnya.
                    </p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea
                        v-model="approvalForm.notes"
                        rows="3"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500"
                        placeholder="Catatan approval..."
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <Button type="button" label="Batal" class="bg-slate-100 text-slate-600 hover:bg-slate-200" @click="showApproveModal = false" />
                    <Button type="button" :icon="IconCheck" label="Approve" class="bg-green-500 hover:bg-green-600 text-white" @click="submitApprove" :disabled="approvalForm.processing" />
                </div>
            </div>
        </Modal>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" @close="showRejectModal = false" title="Tolak Transaksi">
            <div class="p-6">
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                    <p class="text-sm text-red-700 dark:text-red-300">
                        Anda akan menolak transaksi <strong>{{ selectedSale?.invoice }}</strong>. 
                        Stok akan dikembalikan dan transaksi akan dikembalikan ke Sales untuk diperbaiki.
                    </p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="rejectForm.notes"
                        rows="3"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                        placeholder="Masukkan alasan penolakan..."
                        required
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <Button type="button" label="Batal" class="bg-slate-100 text-slate-600 hover:bg-slate-200" @click="showRejectModal = false" />
                    <Button type="button" :icon="IconX" label="Tolak" class="bg-red-500 hover:bg-red-600 text-white" @click="submitReject" :disabled="!rejectForm.notes || rejectForm.processing" />
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    IconUser,
    IconBuildingWarehouse,
    IconCheck,
    IconX,
    IconEye,
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
import Modal from '@/Components/Dashboard/Modal.vue';

const props = defineProps({
    sales: Object,
    filters: Object,
});

const showApproveModal = ref(false);
const showRejectModal = ref(false);
const selectedSale = ref(null);

const approvalForm = useForm({ notes: '' });
const rejectForm = useForm({ notes: '' });

const openApproveModal = (sale) => {
    selectedSale.value = sale;
    approvalForm.notes = '';
    showApproveModal.value = true;
};

const openRejectModal = (sale) => {
    selectedSale.value = sale;
    rejectForm.notes = '';
    showRejectModal.value = true;
};

const submitApprove = () => {
    approvalForm.post(route('approvals.finance.approve', selectedSale.value.id), {
        onSuccess: () => {
            showApproveModal.value = false;
        },
    });
};

const submitReject = () => {
    rejectForm.post(route('approvals.finance.reject', selectedSale.value.id), {
        onSuccess: () => {
            showRejectModal.value = false;
        },
    });
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
