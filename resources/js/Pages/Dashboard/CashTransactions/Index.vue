<template>
    <DashboardLayout>
        <Head title="Kas Masuk/Keluar" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Kas Masuk / Keluar</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Transaksi kas non-penjualan
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        type="link"
                        :icon="IconPlus"
                        class="bg-green-500 hover:bg-green-600 text-white"
                        label="Kas Masuk"
                        :href="route('cash-transactions.create', { type: 'in' })"
                    />
                    <Button
                        type="link"
                        :icon="IconMinus"
                        class="bg-red-500 hover:bg-red-600 text-white"
                        label="Kas Keluar"
                        :href="route('cash-transactions.create', { type: 'out' })"
                    />
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('cash-transactions.index')" placeholder="Cari referensi..." />
                </div>
                <select
                    v-model="filterForm.type"
                    @change="handleFilter"
                    class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                >
                    <option value="">Semua Tipe</option>
                    <option value="in">Kas Masuk</option>
                    <option value="out">Kas Keluar</option>
                </select>
                <select
                    v-model="filterForm.status"
                    @change="handleFilter"
                    class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                >
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="finalized">Final</option>
                </select>
                <input
                    type="date"
                    v-model="filterForm.start_date"
                    @change="handleFilter"
                    class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                />
                <span class="text-slate-400">-</span>
                <input
                    type="date"
                    v-model="filterForm.end_date"
                    @change="handleFilter"
                    class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                />
            </div>
        </div>

        <!-- Transactions Table -->
        <TableCard title="Daftar Transaksi Kas">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh>Referensi</TableTh>
                        <TableTh>Tanggal</TableTh>
                        <TableTh>Tipe</TableTh>
                        <TableTh>Status</TableTh>
                        <TableTh>Akun Kas</TableTh>
                        <TableTh>Akun Lawan</TableTh>
                        <TableTh class="text-right">Jumlah</TableTh>
                        <TableTh class="text-center">Aksi</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="transactions.data.length > 0">
                        <tr
                            v-for="trx in transactions.data"
                            :key="trx.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                        >
                            <TableTd>
                                <span class="font-mono font-bold text-primary-600">{{ trx.reference }}</span>
                            </TableTd>
                            <TableTd>{{ formatDate(trx.date) }}</TableTd>
                            <TableTd>
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    trx.type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                ]">
                                    {{ trx.type === 'in' ? 'Masuk' : 'Keluar' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    trx.status === 'finalized' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'
                                ]">
                                    {{ trx.status === 'finalized' ? 'Final' : 'Draft' }}
                                </span>
                            </TableTd>
                            <TableTd>{{ trx.cash_account?.name }}</TableTd>
                            <TableTd>{{ trx.account?.name }}</TableTd>
                            <TableTd class="text-right font-mono" :class="trx.type === 'in' ? 'text-green-600' : 'text-red-600'">
                                {{ trx.type === 'in' ? '+' : '-' }}{{ formatCurrency(trx.amount) }}
                            </TableTd>
                            <TableTd class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- View Detail Button -->
                                    <a
                                        :href="route('cash-transactions.show', trx.id)"
                                        class="p-1.5 rounded-lg hover:bg-primary-50 text-primary-600"
                                        title="Detail"
                                    >
                                        <IconEye :size="18" />
                                    </a>
                                    <!-- Finalize Button (only for draft) -->
                                    <button
                                        v-if="trx.status === 'draft'"
                                        @click="confirmFinalize(trx)"
                                        class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-600"
                                        title="Finalize"
                                    >
                                        <IconCheck :size="18" />
                                    </button>
                                    <!-- Edit Button (only for draft) -->
                                    <a
                                        v-if="trx.status === 'draft'"
                                        :href="route('cash-transactions.edit', trx.id)"
                                        class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-600"
                                        title="Edit"
                                    >
                                        <IconEdit :size="18" />
                                    </a>
                                    <!-- Delete Button (only for draft) -->
                                    <button
                                        v-if="trx.status === 'draft'"
                                        @click="confirmDelete(trx)"
                                        class="p-1.5 rounded-lg hover:bg-red-50 text-red-600"
                                        title="Hapus"
                                    >
                                        <IconTrash :size="18" />
                                    </button>
                                    <!-- Lock icon for finalized -->
                                    <span v-if="trx.status === 'finalized'" class="text-slate-400" title="Terkunci">
                                        <IconLock :size="18" />
                                    </span>
                                </div>
                            </TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="8" class="text-center py-12">
                            <p class="text-slate-500">Belum ada transaksi kas</p>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <Pagination v-if="transactions?.links && transactions.links.length > 3" :links="transactions.links" />

        <!-- Finalize Modal -->
        <Modal :show="showFinalizeModal" @close="showFinalizeModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Konfirmasi Finalize</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-6">
                    Apakah Anda yakin ingin mem-finalize transaksi <strong>{{ selectedTransaction?.reference }}</strong>?
                    <br><br>
                    <span class="text-amber-600">⚠️ Setelah di-finalize, transaksi tidak dapat diedit atau dihapus dan jurnal akan otomatis dibuat.</span>
                </p>
                <div class="flex justify-end gap-3">
                    <Button
                        type="button"
                        @click="showFinalizeModal = false"
                        label="Batal"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-700"
                    />
                    <Button
                        type="button"
                        @click="finalizeTransaction"
                        :icon="IconCheck"
                        label="Finalize"
                        class="bg-blue-500 hover:bg-blue-600 text-white"
                    />
                </div>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Konfirmasi Hapus</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-6">
                    Apakah Anda yakin ingin menghapus transaksi <strong>{{ selectedTransaction?.reference }}</strong>?
                </p>
                <div class="flex justify-end gap-3">
                    <Button
                        type="button"
                        @click="showDeleteModal = false"
                        label="Batal"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-700"
                    />
                    <Button
                        type="button"
                        @click="deleteTransaction"
                        :icon="IconTrash"
                        label="Hapus"
                        class="bg-red-500 hover:bg-red-600 text-white"
                    />
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconPlus, IconMinus, IconTrash, IconEdit, IconCheck, IconLock, IconEye } from '@tabler/icons-vue';
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
    transactions: Object,
    filters: Object,
});

const filterForm = ref({
    type: props.filters?.type || '',
    status: props.filters?.status || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const showDeleteModal = ref(false);
const showFinalizeModal = ref(false);
const selectedTransaction = ref(null);

const handleFilter = () => {
    router.get(route('cash-transactions.index'), {
        q: props.filters?.q,
        ...filterForm.value,
    }, { preserveState: true });
};

const confirmFinalize = (trx) => {
    selectedTransaction.value = trx;
    showFinalizeModal.value = true;
};

const finalizeTransaction = () => {
    router.post(route('cash-transactions.finalize', selectedTransaction.value.id), {}, {
        onSuccess: () => {
            showFinalizeModal.value = false;
            selectedTransaction.value = null;
        }
    });
};

const confirmDelete = (trx) => {
    selectedTransaction.value = trx;
    showDeleteModal.value = true;
};

const deleteTransaction = () => {
    router.delete(route('cash-transactions.destroy', selectedTransaction.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedTransaction.value = null;
        }
    });
};

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
