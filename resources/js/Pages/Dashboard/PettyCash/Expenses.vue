<template>
    <DashboardLayout>
        <Head title="Pengeluaran Kas Kecil" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pengeluaran Kas Kecil</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Sisa: <span class="font-bold text-emerald-600">{{ formatCurrency(remainingBalance) }}</span>
                        dari {{ formatCurrency(activePettyCash?.amount || 0) }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        type="link"
                        :icon="IconArrowLeft"
                        class="bg-white border border-slate-200 text-slate-700"
                        label="Kembali"
                        :href="route('petty-cash.index')"
                    />
                    <Button
                        type="button"
                        @click="showAddModal = true"
                        :icon="IconPlus"
                        class="bg-primary-500 hover:bg-primary-600 text-white"
                        label="Tambah Pengeluaran"
                    />
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-wrap gap-4">
            <select
                v-model="selectedCategory"
                @change="handleFilter"
                class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
            >
                <option value="">Semua Kategori</option>
                <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
            </select>
            <select
                v-model="selectedStatus"
                @change="handleFilter"
                class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
            >
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
            </select>
        </div>

        <!-- Expenses Table -->
        <TableCard title="Daftar Pengeluaran">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh>Referensi</TableTh>
                        <TableTh>Tanggal</TableTh>
                        <TableTh>Kategori</TableTh>
                        <TableTh>Keterangan</TableTh>
                        <TableTh>No. Bon</TableTh>
                        <TableTh class="text-right">Jumlah</TableTh>
                        <TableTh>Status</TableTh>
                        <TableTh class="text-center">Aksi</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="expenses.data.length > 0">
                        <tr v-for="exp in expenses.data" :key="exp.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <TableTd class="font-mono font-bold text-primary-600">{{ exp.reference }}</TableTd>
                            <TableTd>{{ formatDate(exp.date) }}</TableTd>
                            <TableTd>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ categories[exp.category] || exp.category }}
                                </span>
                            </TableTd>
                            <TableTd class="max-w-[200px] truncate">{{ exp.description }}</TableTd>
                            <TableTd class="font-mono text-slate-500">{{ exp.receipt_number || '-' }}</TableTd>
                            <TableTd class="text-right font-mono text-red-600">-{{ formatCurrency(exp.amount) }}</TableTd>
                            <TableTd>
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    exp.status === 'approved' ? 'bg-green-100 text-green-700' : 
                                    exp.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'
                                ]">
                                    {{ exp.status === 'approved' ? 'Disetujui' : exp.status === 'rejected' ? 'Ditolak' : 'Pending' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-center">
                                <div v-if="exp.status === 'pending'" class="flex items-center justify-center gap-1">
                                    <button @click="approveExpense(exp.id)" class="p-1.5 rounded-lg hover:bg-green-50 text-green-600" title="Setujui">
                                        <IconCheck :size="18" />
                                    </button>
                                    <button @click="rejectExpense(exp.id)" class="p-1.5 rounded-lg hover:bg-red-50 text-red-600" title="Tolak">
                                        <IconX :size="18" />
                                    </button>
                                </div>
                                <span v-else class="text-slate-400 text-xs">
                                    {{ exp.approver?.name }}
                                </span>
                            </TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="8" class="text-center py-8">
                            <p class="text-slate-500">Belum ada pengeluaran</p>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <Pagination v-if="expenses?.links && expenses.links.length > 3" :links="expenses.links" />

        <!-- Add Expense Modal -->
        <Modal :show="showAddModal" @close="showAddModal = false">
            <form @submit.prevent="submitExpense" class="p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Tambah Pengeluaran</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                v-model="expenseForm.date"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select v-model="expenseForm.category" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                <option value="">-- Pilih --</option>
                                <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Jumlah <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                                <input
                                    type="text"
                                    :value="formatInputAmount(expenseForm.amount)"
                                    @input="e => expenseForm.amount = parseInputAmount(e.target.value)"
                                    required
                                    class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-right font-mono"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">No. Bon/Kuitansi</label>
                            <input
                                type="text"
                                v-model="expenseForm.receipt_number"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
                                placeholder="Opsional"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Keterangan <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="expenseForm.description"
                            required
                            rows="2"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
                            placeholder="Untuk keperluan apa..."
                        ></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" @click="showAddModal = false" label="Batal" class="bg-slate-100 text-slate-700" />
                    <Button type="submit" :icon="IconPlus" label="Simpan" class="bg-primary-500 text-white" :disabled="expenseForm.processing" />
                </div>
            </form>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconArrowLeft, IconPlus, IconCheck, IconX } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import Modal from '@/Components/Dashboard/Modal.vue';

const props = defineProps({
    activePettyCash: Object,
    remainingBalance: Number,
    expenses: Object,
    categories: Object,
    filters: Object,
});

const showAddModal = ref(false);
const selectedCategory = ref(props.filters?.category || '');
const selectedStatus = ref(props.filters?.status || '');

const categories = props.categories;

const expenseForm = useForm({
    date: new Date().toISOString().slice(0, 10),
    category: '',
    amount: '',
    description: '',
    receipt_number: '',
});

const formatInputAmount = (val) => {
    if (!val) return '';
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const parseInputAmount = (val) => {
    return val.replace(/\./g, '').replace(/[^0-9]/g, '');
};

const handleFilter = () => {
    router.get(route('petty-cash.expenses'), {
        category: selectedCategory.value,
        status: selectedStatus.value,
    }, { preserveState: true });
};

const submitExpense = () => {
    expenseForm.post(route('petty-cash.expenses.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            expenseForm.reset();
        }
    });
};

const approveExpense = (id) => {
    router.post(route('petty-cash.expenses.approve', id));
};

const rejectExpense = (id) => {
    router.post(route('petty-cash.expenses.reject', id));
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
