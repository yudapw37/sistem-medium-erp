<template>
    <DashboardLayout>
        <Head title="Hutang" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Hutang Usaha</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Kelola hutang kepada supplier dari pembelian tempo
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconChartPie"
                    class="bg-amber-500 hover:bg-amber-600 text-white"
                    label="Aging Report"
                    :href="route('payables.aging')"
                />
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 mb-1">Total Hutang</p>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white">{{ formatCurrency(summary.total) }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <p class="text-sm text-slate-500 mb-1">Sudah Dibayar</p>
                <p class="text-2xl font-bold font-mono text-green-600">{{ formatCurrency(summary.paid) }}</p>
            </div>
            <div class="bg-gradient-to-r from-red-500 to-orange-500 rounded-2xl p-6 text-white">
                <p class="text-red-100 text-sm mb-1">Sisa Hutang</p>
                <p class="text-2xl font-bold font-mono">{{ formatCurrency(summary.remaining) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-wrap gap-4">
            <select
                v-model="selectedSupplier"
                @change="handleFilter"
                class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
            >
                <option value="">Semua Supplier</option>
                <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
            </select>
            <select
                v-model="selectedStatus"
                @change="handleFilter"
                class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
            >
                <option value="">Semua Status</option>
                <option value="unpaid">Belum Dibayar</option>
                <option value="partial">Sebagian</option>
            </select>
        </div>

        <!-- Payables Table -->
        <TableCard title="Daftar Hutang">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh>Invoice</TableTh>
                        <TableTh>Tanggal</TableTh>
                        <TableTh>Supplier</TableTh>
                        <TableTh class="text-right">Total</TableTh>
                        <TableTh class="text-right">Dibayar</TableTh>
                        <TableTh class="text-right">Sisa</TableTh>
                        <TableTh>Status</TableTh>
                        <TableTh class="text-center">Aksi</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="payables.data.length > 0">
                        <tr v-for="purchase in payables.data" :key="purchase.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <TableTd class="font-mono font-bold text-primary-600">{{ purchase.invoice }}</TableTd>
                            <TableTd>{{ formatDate(purchase.created_at) }}</TableTd>
                            <TableTd>
                                <a :href="route('payables.supplier-card', purchase.supplier_id)" class="text-primary-600 hover:underline">
                                    {{ purchase.supplier?.name }}
                                </a>
                            </TableTd>
                            <TableTd class="text-right font-mono">{{ formatCurrency(purchase.grand_total) }}</TableTd>
                            <TableTd class="text-right font-mono text-green-600">{{ formatCurrency(purchase.paid_amount) }}</TableTd>
                            <TableTd class="text-right font-mono font-bold" :class="purchase.remaining > 0 ? 'text-red-600' : 'text-green-600'">
                                {{ formatCurrency(purchase.remaining) }}
                            </TableTd>
                            <TableTd>
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    purchase.is_paid ? 'bg-green-100 text-green-700' :
                                    purchase.paid_amount > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'
                                ]">
                                    {{ purchase.is_paid ? 'Lunas' : purchase.paid_amount > 0 ? 'Sebagian' : 'Belum Bayar' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-center">
                                <a
                                    :href="route('payables.show', purchase.id)"
                                    class="p-1.5 rounded-lg hover:bg-primary-50 text-primary-600 inline-block"
                                    title="Detail"
                                >
                                    <IconEye :size="18" />
                                </a>
                            </TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="8" class="text-center py-8">
                            <p class="text-slate-500">Tidak ada hutang</p>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <Pagination v-if="payables?.links && payables.links.length > 3" :links="payables.links" />
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconChartPie, IconEye } from '@tabler/icons-vue';
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
    payables: Object,
    summary: Object,
    suppliers: Array,
    filters: Object,
});

const selectedSupplier = ref(props.filters?.supplier_id || '');
const selectedStatus = ref(props.filters?.status || '');

const handleFilter = () => {
    router.get(route('payables.index'), {
        supplier_id: selectedSupplier.value,
        status: selectedStatus.value,
    }, { preserveState: true });
};

const formatDate = (val) => {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
