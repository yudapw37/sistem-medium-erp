<template>
    <DashboardLayout>
        <Head title="Buku Besar" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Buku Besar</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Riwayat transaksi per akun
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pilih Akun</label>
                    <select
                        v-model="filterForm.account_id"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                    >
                        <option value="">-- Pilih Akun --</option>
                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                            {{ acc.code }} - {{ acc.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Dari Tanggal</label>
                    <input
                        type="date"
                        v-model="filterForm.start_date"
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Sampai Tanggal</label>
                    <input
                        type="date"
                        v-model="filterForm.end_date"
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                    />
                </div>
                <Button
                    type="button"
                    @click="handleFilter"
                    :icon="IconSearch"
                    label="Tampilkan"
                    class="bg-primary-500 hover:bg-primary-600 text-white"
                />
            </div>
        </div>

        <!-- Ledger Table -->
        <div v-if="selectedAccount" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <!-- Account Header -->
            <div class="bg-primary-500 px-6 py-4">
                <h3 class="text-lg font-bold text-white">
                    {{ selectedAccount.code }} - {{ selectedAccount.name }}
                </h3>
                <p class="text-sm text-primary-100">{{ getTypeName(selectedAccount.type) }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Referensi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Keterangan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Debit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Kredit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Balance -->
                        <tr class="bg-slate-100 dark:bg-slate-800/50">
                            <td colspan="5" class="px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Saldo Awal
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900 dark:text-white">
                                {{ formatCurrency(openingBalance) }}
                            </td>
                        </tr>
                        <!-- Entries -->
                        <tr
                            v-for="entry in entries"
                            :key="entry.id"
                            class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30"
                        >
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                {{ formatDate(entry.journal?.date) }}
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-primary-600">
                                {{ entry.journal?.reference }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                {{ entry.description || entry.journal?.description || '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-sm">
                                <span v-if="entry.debit > 0" class="text-green-600">{{ formatCurrency(entry.debit) }}</span>
                                <span v-else class="text-slate-400">-</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-sm">
                                <span v-if="entry.credit > 0" class="text-red-600">{{ formatCurrency(entry.credit) }}</span>
                                <span v-else class="text-slate-400">-</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-sm font-semibold text-slate-900 dark:text-white">
                                {{ formatCurrency(entry.balance) }}
                            </td>
                        </tr>
                        <!-- Empty state -->
                        <tr v-if="entries.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                Tidak ada transaksi pada periode ini
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="entries.length > 0" class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                                Saldo Akhir
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-green-700">
                                {{ formatCurrency(totalDebit) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-red-700">
                                {{ formatCurrency(totalCredit) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                {{ formatCurrency(closingBalance) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center">
            <IconBook :size="48" class="mx-auto text-slate-300 dark:text-slate-600 mb-4" />
            <p class="text-slate-500">Pilih akun untuk melihat buku besar</p>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { IconSearch, IconBook } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    accounts: Array,
    selectedAccount: Object,
    entries: Array,
    openingBalance: Number,
    filters: Object,
});

const filterForm = ref({
    account_id: props.filters?.account_id || '',
    start_date: props.filters?.start_date || new Date().toISOString().slice(0, 7) + '-01',
    end_date: props.filters?.end_date || new Date().toISOString().slice(0, 10),
});

const handleFilter = () => {
    router.get(route('reports.general-ledger'), filterForm.value, { preserveState: true });
};

const totalDebit = computed(() => props.entries?.reduce((sum, e) => sum + parseFloat(e.debit || 0), 0) || 0);
const totalCredit = computed(() => props.entries?.reduce((sum, e) => sum + parseFloat(e.credit || 0), 0) || 0);
const closingBalance = computed(() => props.entries?.length > 0 ? props.entries[props.entries.length - 1].balance : props.openingBalance);

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const getTypeName = (type) => {
    const types = { asset: 'Aset', liability: 'Kewajiban', equity: 'Ekuitas', revenue: 'Pendapatan', expense: 'Beban' };
    return types[type] || type;
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
