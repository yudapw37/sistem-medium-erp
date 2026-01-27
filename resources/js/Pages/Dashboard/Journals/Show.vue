<template>
    <DashboardLayout>
        <Head :title="`Jurnal ${journal.reference}`" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ journal.reference }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ journal.description || 'Detail jurnal' }}
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50"
                    label="Kembali"
                    :href="route('journals.index')"
                />
            </div>
        </div>

        <!-- Journal Info -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Referensi</p>
                    <p class="font-mono font-bold text-primary-600">{{ journal.reference }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Tanggal</p>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ formatDate(journal.date) }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">User</p>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ journal.user?.name || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Status</p>
                    <span
                        :class="[
                            'px-2 py-1 rounded-full text-xs font-semibold',
                            journal.total_debit === journal.total_credit
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'
                        ]"
                    >
                        {{ journal.total_debit === journal.total_credit ? 'Balance' : 'Unbalance' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Entries Table -->
        <TableCard title="Detail Entry">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh>Kode Akun</TableTh>
                        <TableTh>Nama Akun</TableTh>
                        <TableTh>Deskripsi</TableTh>
                        <TableTh class="text-right">Debit</TableTh>
                        <TableTh class="text-right">Kredit</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <tr
                        v-for="entry in journal.entries"
                        :key="entry.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                    >
                        <TableTd>
                            <span class="font-mono">{{ entry.account?.code }}</span>
                        </TableTd>
                        <TableTd>{{ entry.account?.name }}</TableTd>
                        <TableTd class="text-slate-600 dark:text-slate-400">{{ entry.description || '-' }}</TableTd>
                        <TableTd class="text-right font-mono">
                            <span v-if="entry.debit > 0" class="text-green-600">{{ formatCurrency(entry.debit) }}</span>
                            <span v-else class="text-slate-400">-</span>
                        </TableTd>
                        <TableTd class="text-right font-mono">
                            <span v-if="entry.credit > 0" class="text-red-600">{{ formatCurrency(entry.credit) }}</span>
                            <span v-else class="text-slate-400">-</span>
                        </TableTd>
                    </tr>
                    <!-- Totals Row -->
                    <tr class="bg-slate-100 dark:bg-slate-800 font-bold">
                        <TableTd colspan="3" class="text-right">Total</TableTd>
                        <TableTd class="text-right font-mono text-green-700">{{ formatCurrency(journal.total_debit) }}</TableTd>
                        <TableTd class="text-right font-mono text-red-700">{{ formatCurrency(journal.total_credit) }}</TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { IconArrowLeft } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';

const props = defineProps({
    journal: Object,
});

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
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
