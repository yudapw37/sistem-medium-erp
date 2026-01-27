<template>
    <DashboardLayout>
        <Head title="History Gudang Approval" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">History Gudang Approval</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Riwayat approval/reject oleh Gudang
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('approvals.warehouse.history')" placeholder="Cari No. Invoice..." />
                </div>
                <div class="flex items-center gap-2">
                    <input
                        type="date"
                        v-model="filters.start_date"
                        @change="handleFilter"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                    />
                    <span class="text-slate-400">-</span>
                    <input
                        type="date"
                        v-model="filters.end_date"
                        @change="handleFilter"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                    />
                </div>
                <div class="w-full sm:w-40">
                    <select
                        v-model="filters.status"
                        @change="handleFilter"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                    >
                        <option value="">Semua Status</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <TableCard title="History Approval">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>Invoice</TableTh>
                        <TableTh>Customer</TableTh>
                        <TableTh>Gudang</TableTh>
                        <TableTh>Status</TableTh>
                        <TableTh>Approver</TableTh>
                        <TableTh>Tanggal</TableTh>
                        <TableTh>Catatan</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="approvals.data.length > 0">
                        <tr
                            v-for="(approval, i) in approvals.data"
                            :key="approval.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (approvals.current_page - 1) * approvals.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ approval.sale?.invoice || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>{{ approval.sale?.customer?.name || '-' }}</TableTd>
                            <TableTd>{{ approval.sale?.warehouse?.name || '-' }}</TableTd>
                            <TableTd>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-full text-xs font-semibold',
                                        approval.status === 'approved'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700'
                                    ]"
                                >
                                    {{ approval.status === 'approved' ? 'Approved' : 'Rejected' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconUser :size="16" class="text-slate-400" />
                                    <span>{{ approval.user?.name || '-' }}</span>
                                </div>
                            </TableTd>
                            <TableTd>{{ formatDate(approval.approved_at) }}</TableTd>
                            <TableTd>
                                <span class="text-sm text-slate-600 dark:text-slate-400 max-w-[150px] truncate block" :title="approval.notes">
                                    {{ approval.notes || '-' }}
                                </span>
                            </TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="8" class="text-center py-12">
                            <p class="text-slate-500">Belum ada history approval</p>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <Pagination v-if="approvals?.links && approvals.links.length > 3" :links="approvals.links" />
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconUser } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Search from '@/Components/Dashboard/Search.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';

const props = defineProps({
    approvals: Object,
    filters: Object,
});

const filters = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    status: props.filters?.status || '',
});

const handleFilter = () => {
    router.get(route('approvals.warehouse.history'), {
        q: props.filters?.q,
        ...filters.value,
    }, { preserveState: true });
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
