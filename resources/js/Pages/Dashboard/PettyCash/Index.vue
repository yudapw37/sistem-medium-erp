<template>
    <DashboardLayout>
        <Head title="Kas Kecil" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Kas Kecil (Petty Cash)</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Pengelolaan kas kecil untuk operasional harian
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="!activePettyCash"
                        type="button"
                        @click="showOpenModal = true"
                        :icon="IconPlus"
                        class="bg-green-500 hover:bg-green-600 text-white"
                        label="Buka Periode Baru"
                    />
                    <template v-if="activePettyCash">
                        <Button
                            type="button"
                            @click="showReplenishModal = true"
                            :icon="IconCash"
                            class="bg-blue-500 hover:bg-blue-600 text-white"
                            label="Isi Ulang"
                        />
                        <Button
                            type="link"
                            :icon="IconReceipt"
                            class="bg-primary-500 hover:bg-primary-600 text-white"
                            label="Pengeluaran"
                            :href="route('petty-cash.expenses')"
                        />
                        <Button
                            type="link"
                            :icon="IconReportAnalytics"
                            class="bg-amber-500 hover:bg-amber-600 text-white"
                            label="Pertanggungjawaban"
                            :href="route('petty-cash.settlement')"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Active Period Card -->
        <div v-if="activePettyCash" class="mb-6">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-sm mb-1">Periode Aktif</p>
                        <h2 class="text-3xl font-bold font-mono">{{ formatCurrency(remainingBalance) }}</h2>
                        <p class="text-emerald-100 text-sm mt-1">Sisa saldo dari {{ formatCurrency(activePettyCash.amount) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-emerald-100 text-sm">{{ activePettyCash.reference }}</p>
                        <p class="text-white font-semibold">{{ formatDate(activePettyCash.date) }}</p>
                        <span class="inline-block mt-2 px-3 py-1 bg-white/20 rounded-full text-sm">
                            <IconCircleCheck :size="16" class="inline mr-1" />
                            Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Active Period -->
        <div v-else class="mb-6">
            <div class="bg-slate-100 dark:bg-slate-800 rounded-2xl p-8 text-center">
                <IconWallet :size="48" class="mx-auto text-slate-400 mb-4" />
                <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300">Belum ada periode aktif</h3>
                <p class="text-slate-500 mt-2">Buka periode baru untuk mulai mencatat kas kecil</p>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div v-if="recentExpenses.length > 0" class="mb-6">
            <TableCard title="Pengeluaran Terakhir">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh>Referensi</TableTh>
                            <TableTh>Tanggal</TableTh>
                            <TableTh>Kategori</TableTh>
                            <TableTh>Keterangan</TableTh>
                            <TableTh class="text-right">Jumlah</TableTh>
                            <TableTh>Status</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr v-for="exp in recentExpenses" :key="exp.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <TableTd class="font-mono text-primary-600">{{ exp.reference }}</TableTd>
                            <TableTd>{{ formatDate(exp.date) }}</TableTd>
                            <TableTd>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ getCategoryLabel(exp.category) }}
                                </span>
                            </TableTd>
                            <TableTd class="max-w-[200px] truncate">{{ exp.description }}</TableTd>
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
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
        </div>

        <!-- History -->
        <TableCard title="Riwayat Kas Kecil">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh>Referensi</TableTh>
                        <TableTh>Tanggal</TableTh>
                        <TableTh>Tipe</TableTh>
                        <TableTh class="text-right">Jumlah</TableTh>
                        <TableTh>Status</TableTh>
                        <TableTh>Keterangan</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="pettyCashes.data.length > 0">
                        <tr v-for="pc in pettyCashes.data" :key="pc.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <TableTd class="font-mono font-bold text-primary-600">{{ pc.reference }}</TableTd>
                            <TableTd>{{ formatDate(pc.date) }}</TableTd>
                            <TableTd>
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    pc.type === 'open' ? 'bg-green-100 text-green-700' : 
                                    pc.type === 'replenish' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700'
                                ]">
                                    {{ pc.type === 'open' ? 'Buka' : pc.type === 'replenish' ? 'Isi Ulang' : 'Tutup' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-right font-mono text-green-600">+{{ formatCurrency(pc.amount) }}</TableTd>
                            <TableTd>
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    pc.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'
                                ]">
                                    {{ pc.status === 'active' ? 'Aktif' : 'Ditutup' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-slate-600 max-w-[200px] truncate">{{ pc.description }}</TableTd>
                        </tr>
                    </template>
                    <tr v-else>
                        <TableTd colspan="6" class="text-center py-8">
                            <p class="text-slate-500">Belum ada riwayat kas kecil</p>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <Pagination v-if="pettyCashes?.links && pettyCashes.links.length > 3" :links="pettyCashes.links" />

        <!-- Open Modal -->
        <Modal :show="showOpenModal" @close="showOpenModal = false">
            <form @submit.prevent="submitOpen" class="p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Buka Periode Kas Kecil</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Dana Awal <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                            <input
                                type="text"
                                :value="formatInputAmount(openForm.amount)"
                                @input="e => openForm.amount = parseInputAmount(e.target.value)"
                                required
                                class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-right font-mono"
                                placeholder="0"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Keterangan</label>
                        <textarea
                            v-model="openForm.description"
                            rows="2"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                            placeholder="Pembukaan kas kecil..."
                        ></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" @click="showOpenModal = false" label="Batal" class="bg-slate-100 text-slate-700" />
                    <Button type="submit" :icon="IconPlus" label="Buka Periode" class="bg-green-500 text-white" :disabled="openForm.processing" />
                </div>
            </form>
        </Modal>

        <!-- Replenish Modal -->
        <Modal :show="showReplenishModal" @close="showReplenishModal = false">
            <form @submit.prevent="submitReplenish" class="p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Isi Ulang Kas Kecil</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Jumlah Isi Ulang <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                            <input
                                type="text"
                                :value="formatInputAmount(replenishForm.amount)"
                                @input="e => replenishForm.amount = parseInputAmount(e.target.value)"
                                required
                                class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-right font-mono"
                                placeholder="0"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Keterangan</label>
                        <textarea
                            v-model="replenishForm.description"
                            rows="2"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                            placeholder="Pengisian ulang kas kecil..."
                        ></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" @click="showReplenishModal = false" label="Batal" class="bg-slate-100 text-slate-700" />
                    <Button type="submit" :icon="IconCash" label="Isi Ulang" class="bg-blue-500 text-white" :disabled="replenishForm.processing" />
                </div>
            </form>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconPlus, IconCash, IconReceipt, IconReportAnalytics, IconCircleCheck, IconWallet } from '@tabler/icons-vue';
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
    pettyCashes: Object,
    recentExpenses: Array,
});

const showOpenModal = ref(false);
const showReplenishModal = ref(false);

const openForm = useForm({ amount: '', description: '' });
const replenishForm = useForm({ amount: '', description: '' });

const categories = {
    transport: 'Transportasi',
    atk: 'ATK',
    listrik: 'Listrik',
    air: 'Air',
    telepon: 'Telepon',
    konsumsi: 'Konsumsi',
    parkir: 'Parkir',
    foto_copy: 'Foto Copy',
    materai: 'Materai',
    kebersihan: 'Kebersihan',
    perlengkapan: 'Perlengkapan',
    lainnya: 'Lainnya',
};

const getCategoryLabel = (cat) => categories[cat] || cat;

const formatInputAmount = (val) => {
    if (!val) return '';
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const parseInputAmount = (val) => {
    return val.replace(/\./g, '').replace(/[^0-9]/g, '');
};

const submitOpen = () => {
    openForm.post(route('petty-cash.open'), {
        onSuccess: () => {
            showOpenModal.value = false;
            openForm.reset();
        }
    });
};

const submitReplenish = () => {
    replenishForm.post(route('petty-cash.replenish'), {
        onSuccess: () => {
            showReplenishModal.value = false;
            replenishForm.reset();
        }
    });
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
