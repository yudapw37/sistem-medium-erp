<template>
    <DashboardLayout>
        <Head title="Detail Transaksi" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Transaksi</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ sale.invoice }}
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="$page.url.includes('finance') ? route('approvals.finance.index') : route('approvals.warehouse.index')"
                />
            </div>
        </div>

        <!-- Status & Info Card -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Sale Info -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Informasi Transaksi</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Invoice</span>
                        <span class="font-medium text-slate-900 dark:text-white">{{ sale.invoice }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tanggal</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ formatDate(sale.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Customer</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ sale.customer?.name || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Gudang</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ sale.warehouse?.name || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Dibuat Oleh</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ sale.user?.name || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Pembayaran</span>
                        <span class="uppercase font-semibold text-slate-700 dark:text-slate-300">{{ sale.payment_type }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span class="text-slate-500 font-medium">Total</span>
                        <span class="font-bold text-lg text-primary-600 dark:text-primary-400">{{ formatCurrency(sale.grand_total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Approval Timeline -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Status Approval</h3>
                <div class="space-y-4">
                    <!-- Finance Step -->
                    <div class="flex items-start gap-4">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center shrink-0',
                            getApprovalStatus('finance') === 'approved' ? 'bg-green-100 text-green-600' :
                            getApprovalStatus('finance') === 'rejected' ? 'bg-red-100 text-red-600' :
                            getApprovalStatus('finance') === 'pending' ? 'bg-yellow-100 text-yellow-600' :
                            'bg-slate-100 text-slate-400'
                        ]">
                            <IconCheck v-if="getApprovalStatus('finance') === 'approved'" :size="20" />
                            <IconX v-else-if="getApprovalStatus('finance') === 'rejected'" :size="20" />
                            <IconClock v-else :size="20" />
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-slate-900 dark:text-white">Finance</p>
                            <p v-if="getApproval('finance')" class="text-sm text-slate-500">
                                {{ getApproval('finance').user?.name }} - {{ formatDate(getApproval('finance').approved_at) }}
                            </p>
                            <p v-if="getApproval('finance')?.notes" class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                "{{ getApproval('finance').notes }}"
                            </p>
                        </div>
                        <span :class="[
                            'px-2 py-1 rounded-full text-xs font-semibold',
                            getApprovalStatus('finance') === 'approved' ? 'bg-green-100 text-green-700' :
                            getApprovalStatus('finance') === 'rejected' ? 'bg-red-100 text-red-700' :
                            getApprovalStatus('finance') === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                            'bg-slate-100 text-slate-500'
                        ]">
                            {{ getApprovalLabel('finance') }}
                        </span>
                    </div>

                    <!-- Warehouse Step -->
                    <div class="flex items-start gap-4">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center shrink-0',
                            getApprovalStatus('warehouse') === 'approved' ? 'bg-green-100 text-green-600' :
                            getApprovalStatus('warehouse') === 'rejected' ? 'bg-red-100 text-red-600' :
                            getApprovalStatus('warehouse') === 'pending' ? 'bg-yellow-100 text-yellow-600' :
                            'bg-slate-100 text-slate-400'
                        ]">
                            <IconCheck v-if="getApprovalStatus('warehouse') === 'approved'" :size="20" />
                            <IconX v-else-if="getApprovalStatus('warehouse') === 'rejected'" :size="20" />
                            <IconClock v-else :size="20" />
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-slate-900 dark:text-white">Gudang</p>
                            <p v-if="getApproval('warehouse')" class="text-sm text-slate-500">
                                {{ getApproval('warehouse').user?.name }} - {{ formatDate(getApproval('warehouse').approved_at) }}
                            </p>
                            <p v-if="getApproval('warehouse')?.notes" class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                "{{ getApproval('warehouse').notes }}"
                            </p>
                        </div>
                        <span :class="[
                            'px-2 py-1 rounded-full text-xs font-semibold',
                            getApprovalStatus('warehouse') === 'approved' ? 'bg-green-100 text-green-700' :
                            getApprovalStatus('warehouse') === 'rejected' ? 'bg-red-100 text-red-700' :
                            getApprovalStatus('warehouse') === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                            'bg-slate-100 text-slate-500'
                        ]">
                            {{ getApprovalLabel('warehouse') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <TableCard title="Detail Item">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>Produk</TableTh>
                        <TableTh class="text-right">Harga</TableTh>
                        <TableTh class="text-center">Qty</TableTh>
                        <TableTh class="text-right">Diskon</TableTh>
                        <TableTh class="text-right">Subtotal</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <tr
                        v-for="(detail, i) in sale.details"
                        :key="detail.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                    >
                        <TableTd class="text-center">{{ i + 1 }}</TableTd>
                        <TableTd>
                            <span class="font-medium text-slate-900 dark:text-white">
                                {{ detail.product?.title || detail.bundle?.name || '-' }}
                            </span>
                        </TableTd>
                        <TableTd class="text-right">{{ formatCurrency(detail.sell_price) }}</TableTd>
                        <TableTd class="text-center font-medium">{{ detail.qty }}</TableTd>
                        <TableTd class="text-right">
                            <span :class="detail.discount > 0 ? 'text-red-500' : 'text-slate-400'">
                                {{ detail.discount > 0 ? '-' + formatCurrency(detail.discount) : '-' }}
                            </span>
                        </TableTd>
                        <TableTd class="text-right font-bold">
                            {{ formatCurrency((detail.sell_price * detail.qty) - detail.discount) }}
                        </TableTd>
                    </tr>
                    <tr class="bg-slate-50 dark:bg-slate-800/50">
                        <TableTd colspan="5" class="text-right font-bold">TOTAL</TableTd>
                        <TableTd class="text-right font-black text-primary-600 text-lg">
                            {{ formatCurrency(sale.grand_total) }}
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconCheck,
    IconX,
    IconClock,
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
    sale: Object,
});

const getApproval = (type) => {
    return props.sale.approvals?.find(a => a.type === type && a.status !== 'pending');
};

const getApprovalStatus = (type) => {
    const approval = props.sale.approvals?.find(a => a.type === type);
    if (!approval) return 'waiting';
    return approval.status;
};

const getApprovalLabel = (type) => {
    const status = getApprovalStatus(type);
    switch (status) {
        case 'approved': return 'Approved';
        case 'rejected': return 'Rejected';
        case 'pending': return 'Menunggu';
        default: return 'Belum';
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
    if (!value) return '-';
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
