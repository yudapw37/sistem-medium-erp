<template>
    <DashboardLayout>
        <Head title="Jurnal" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Jurnal Umum</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Daftar jurnal transaksi
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('journals.index')" placeholder="Cari referensi..." />
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
            </div>
        </div>

        <!-- Journals Table -->
        <TableCard title="Daftar Jurnal">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh>Referensi</TableTh>
                        <TableTh>Tanggal</TableTh>
                        <TableTh>Deskripsi</TableTh>
                        <TableTh class="text-right">Debit</TableTh>
                        <TableTh class="text-right">Kredit</TableTh>
                        <TableTh>User</TableTh>
                        <TableTh class="text-center">Aksi</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="journals.data.length > 0">
                        <tr
                            v-for="journal in journals.data"
                            :key="journal.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                        >
                            <TableTd>
                                <span class="font-mono font-bold text-primary-600">{{ journal.reference }}</span>
                            </TableTd>
                            <TableTd>{{ formatDate(journal.date) }}</TableTd>
                            <TableTd>{{ journal.description || '-' }}</TableTd>
                            <TableTd class="text-right font-mono">{{ formatCurrency(journal.total_debit) }}</TableTd>
                            <TableTd class="text-right font-mono">{{ formatCurrency(journal.total_credit) }}</TableTd>
                            <TableTd>{{ journal.user?.name || '-' }}</TableTd>
                            <TableTd class="text-center">
                                <button
                                    @click="router.visit(route('journals.show', journal.id))"
                                    class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-600"
                                    title="Detail"
                                >
                                    <IconEye :size="18" />
                                </button>
                            </TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="7" class="text-center py-12">
                            <p class="text-slate-500">Belum ada jurnal</p>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <Pagination v-if="journals?.links && journals.links.length > 3" :links="journals.links" />
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconEye } from '@tabler/icons-vue';
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
    journals: Object,
    filters: Object,
});

const filters = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const handleFilter = () => {
    router.get(route('journals.index'), {
        q: props.filters?.q,
        ...filters.value,
    }, { preserveState: true });
};

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
