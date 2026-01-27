<template>
    <DashboardLayout>
        <Head title="Aset Tetap" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Aset Tetap</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ assets.total || assets.data?.length || 0 }} aset terdaftar
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        type="button"
                        :icon="IconRefresh"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white"
                        label="Proses Penyusutan"
                        @click="showDepreciationModal = true"
                    />
                    <Button
                        type="link"
                        :icon="IconCirclePlus"
                        class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                        label="Tambah Aset"
                        :href="route('fixed-assets.create')"
                    />
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Total Harga Perolehan</p>
                <p class="text-xl font-bold text-slate-800 dark:text-white">
                    Rp {{ Number(summary.totalAssets).toLocaleString('id-ID') }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Total Nilai Buku</p>
                <p class="text-xl font-bold text-green-600 dark:text-green-400">
                    Rp {{ Number(summary.totalBookValue).toLocaleString('id-ID') }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Total Akumulasi Penyusutan</p>
                <p class="text-xl font-bold text-orange-600 dark:text-orange-400">
                    Rp {{ Number(summary.totalDepreciation).toLocaleString('id-ID') }}
                </p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="w-full sm:w-80">
                <Search :url="route('fixed-assets.index')" placeholder="Cari aset..." />
            </div>
        </div>

        <!-- Content -->
        <template v-if="assets.data.length > 0">
            <TableCard title="Data Aset Tetap">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Kode</TableTh>
                            <TableTh>Nama Aset</TableTh>
                            <TableTh>Kategori</TableTh>
                            <TableTh>Harga Perolehan</TableTh>
                            <TableTh>Akum. Penyusutan</TableTh>
                            <TableTh>Nilai Buku</TableTh>
                            <TableTh>Status</TableTh>
                            <TableTh></TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(asset, i) in assets.data"
                            :key="asset.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (assets.current_page - 1) * assets.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-mono text-sm">{{ asset.code }}</span>
                            </TableTd>
                            <TableTd>
                                <span class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ asset.name }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ asset.category || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="font-medium">
                                    Rp {{ Number(asset.acquisition_cost).toLocaleString('id-ID') }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-orange-600 dark:text-orange-400">
                                    Rp {{ Number(asset.accumulated_depreciation).toLocaleString('id-ID') }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="font-medium text-green-600 dark:text-green-400">
                                    Rp {{ Number(asset.book_value).toLocaleString('id-ID') }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex flex-col gap-1">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full text-center',
                                            asset.status === 'active'
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
                                        ]"
                                    >
                                        {{ asset.status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                    <span
                                        v-if="!asset.is_finalized"
                                        class="px-2 py-1 text-xs rounded-full text-center bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400"
                                    >
                                        Draft
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <div class="flex gap-2">
                                    <!-- Finalize Button for Draft Assets -->
                                    <Button
                                        v-if="!asset.is_finalized"
                                        type="button"
                                        :icon="IconCheck"
                                        class="border bg-emerald-100 border-emerald-200 text-emerald-600 hover:bg-emerald-200 dark:bg-emerald-900/50 dark:border-emerald-800 dark:text-emerald-400"
                                        @click="openFinalizeModal(asset)"
                                        title="Finalisasi"
                                    />
                                    <Button
                                        v-if="asset.is_finalized"
                                        type="link"
                                        :icon="IconHistory"
                                        class="border bg-blue-100 border-blue-200 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/50 dark:border-blue-800 dark:text-blue-400"
                                        :href="route('fixed-assets.depreciation-history', asset.id)"
                                        title="Riwayat Penyusutan"
                                    />
                                    <Button
                                        type="edit"
                                        :icon="IconPencilCog"
                                        class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:border-warning-800 dark:text-warning-400"
                                        :href="route('fixed-assets.edit', asset.id)"
                                    />
                                    <Button
                                        type="delete"
                                        :icon="IconTrash"
                                        class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:border-danger-800 dark:text-danger-400"
                                        :url="route('fixed-assets.destroy', asset.id)"
                                    />
                                </div>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
        </template>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800"
        >
            <div
                class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4"
            >
                <IconDatabaseOff :size="32" class="text-slate-400" :stroke-width="1.5" />
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">
                Belum Ada Aset Tetap
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Tambahkan aset tetap pertama Anda.
            </p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Tambah Aset"
                :href="route('fixed-assets.create')"
            />
        </div>

        <Pagination v-if="assets?.links && assets.links.length > 3" :links="assets.links" />

        <!-- Depreciation Modal -->
        <div v-if="showDepreciationModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Proses Penyusutan Bulanan</h3>
                <form @submit.prevent="processDepreciation">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Periode (Bulan)
                        </label>
                        <input
                            v-model="depreciationForm.period"
                            type="month"
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                        />
                    </div>
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg mb-4">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            Sistem akan membuat jurnal penyusutan untuk semua aset aktif pada periode ini.
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="depreciationForm.processing"
                            class="flex-1 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg disabled:opacity-50"
                        >
                            Proses
                        </button>
                        <button
                            type="button"
                            @click="showDepreciationModal = false"
                            class="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Finalize Modal -->
        <div v-if="showFinalizeModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Finalisasi Aset Tetap</h3>
                <div class="mb-4 p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        <strong>{{ selectedAsset?.code }}</strong> - {{ selectedAsset?.name }}
                    </p>
                    <p class="text-lg font-bold text-slate-800 dark:text-white">
                        Rp {{ Number(selectedAsset?.acquisition_cost || 0).toLocaleString('id-ID') }}
                    </p>
                </div>
                <form @submit.prevent="submitFinalize">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Akun Kas/Bank (Pembayaran) <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="finalizeForm.cash_account_id"
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="">Pilih Akun</option>
                            <option v-for="acc in cashAccounts" :key="acc.id" :value="acc.id">
                                {{ acc.code }} - {{ acc.name }}
                            </option>
                        </select>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg mb-4">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Sistem akan membuat jurnal pembelian aset:
                            <br />• Debit: Akun Aset Tetap
                            <br />• Credit: Akun Kas/Bank yang dipilih
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="finalizeForm.processing || !finalizeForm.cash_account_id"
                            class="flex-1 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg disabled:opacity-50"
                        >
                            Finalisasi
                        </button>
                        <button
                            type="button"
                            @click="showFinalizeModal = false"
                            class="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    IconCirclePlus,
    IconDatabaseOff,
    IconPencilCog,
    IconTrash,
    IconRefresh,
    IconHistory,
    IconCheck,
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

const props = defineProps({
    assets: Object,
    summary: Object,
    cashAccounts: Array,
});

const showDepreciationModal = ref(false);
const showFinalizeModal = ref(false);
const selectedAsset = ref(null);

const depreciationForm = useForm({
    period: new Date().toISOString().slice(0, 7),
});

const finalizeForm = useForm({
    cash_account_id: '',
});

const processDepreciation = () => {
    depreciationForm.post(route('fixed-assets.process-depreciation'), {
        onSuccess: () => {
            showDepreciationModal.value = false;
        },
    });
};

const openFinalizeModal = (asset) => {
    selectedAsset.value = asset;
    finalizeForm.cash_account_id = '';
    showFinalizeModal.value = true;
};

const submitFinalize = () => {
    finalizeForm.post(route('fixed-assets.finalize', selectedAsset.value.id), {
        onSuccess: () => {
            showFinalizeModal.value = false;
            selectedAsset.value = null;
        },
    });
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
