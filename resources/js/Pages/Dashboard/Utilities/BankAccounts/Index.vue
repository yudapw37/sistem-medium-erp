<template>
    <DashboardLayout>
        <Head title="Daftar Rekening Bank" />

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <IconBuildingBank :size="28" class="text-primary-500" />
                        Daftar Rekening Bank
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Manajemen data rekening bank untuk transaksi.
                    </p>
                </div>
                <Button
                    type="button"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Rekening"
                    @click="openAddModal"
                />
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="w-full sm:w-80">
                        <Search :url="route('bank-accounts.index')" placeholder="Cari nama bank atau akun..." />
                    </div>
                </div>
            </div>

            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>Nama Bank</TableTh>
                        <TableTh>Nama Pemilik</TableTh>
                        <TableTh>No. Rekening</TableTh>
                        <TableTh>Status</TableTh>
                        <TableTh class="w-24 text-center"></TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <tr v-if="bankAccounts.data.length === 0">
                        <td colspan="6" class="py-12 text-center text-slate-500 italic">
                            Belum ada data rekening.
                        </td>
                    </tr>
                    <tr
                        v-for="(account, i) in bankAccounts.data"
                        :key="account.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                        <TableTd class="text-center">
                            {{ ++i + (bankAccounts.current_page - 1) * bankAccounts.per_page }}
                        </TableTd>
                        <TableTd>
                            <span class="font-medium text-slate-900 dark:text-white">
                                {{ account.bank_name }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <span class="text-sm text-slate-700 dark:text-slate-300">
                                {{ account.account_name }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <span class="text-sm font-mono text-slate-600 dark:text-slate-400">
                                {{ account.account_number }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <span
                                :class="[
                                    'px-2 py-1 rounded-full text-[10px] font-bold uppercase',
                                    account.is_active
                                        ? 'bg-success-100 text-success-600 dark:bg-success-900/30 dark:text-success-400'
                                        : 'bg-danger-100 text-danger-600 dark:bg-danger-900/30 dark:text-danger-400',
                                ]"
                            >
                                {{ account.is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <div class="flex justify-center gap-2">
                                <Button
                                    type="button"
                                    :icon="IconPencilCog"
                                    class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:border-warning-800 dark:text-warning-400"
                                    @click="openEditModal(account)"
                                />
                                <Button
                                    type="delete"
                                    :icon="IconTrash"
                                    class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:border-danger-800 dark:text-danger-400"
                                    @click="deleteAccount(account.id)"
                                />
                            </div>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </div>

        <Pagination v-if="bankAccounts?.links && bankAccounts.links.length > 3" :links="bankAccounts.links" />

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        {{ isEdit ? 'Edit Rekening' : 'Tambah Rekening Baru' }}
                    </h2>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <IconX :size="24" />
                    </button>
                </div>
                
                <form @submit.prevent="submit">
                    <div class="p-6 space-y-4">
                        <Input
                            type="text"
                            label="Nama Bank"
                            placeholder="Contoh: BCA, Mandiri, BRI"
                            :errors="form.errors.bank_name"
                            v-model="form.bank_name"
                        />
                        <Input
                            type="text"
                            label="Nama Pemilik"
                            placeholder="Nama sesuai buku tabungan"
                            :errors="form.errors.account_name"
                            v-model="form.account_name"
                        />
                        <Input
                            type="text"
                            label="Nomor Rekening"
                            placeholder="Masukkan nomor rekening"
                            :errors="form.errors.account_number"
                            v-model="form.account_number"
                        />
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="is_active"
                                v-model="form.is_active"
                                class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                            />
                            <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                                Status Aktif
                            </label>
                        </div>
                    </div>

                    <div class="p-6 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-slate-50 dark:bg-slate-800/50">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-medium transition-colors disabled:opacity-50 inline-flex items-center gap-2"
                        >
                            <IconDeviceFloppy :size="18" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    IconBuildingBank,
    IconTrash,
    IconCirclePlus,
    IconX,
    IconDeviceFloppy,
    IconPencilCog,
} from '@tabler/icons-vue';
import { useToast } from 'vue-toastification';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Search from '@/Components/Dashboard/Search.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import Input from '@/Components/Dashboard/Input.vue';

const props = defineProps({
    bankAccounts: Object,
});

const toast = useToast();
const showModal = ref(false);
const isEdit = ref(false);

const form = useForm({
    id: null,
    bank_name: '',
    account_name: '',
    account_number: '',
    is_active: true,
});

const openAddModal = () => {
    isEdit.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (account) => {
    isEdit.value = true;
    form.id = account.id;
    form.bank_name = account.bank_name;
    form.account_name = account.account_name;
    form.account_number = account.account_number;
    form.is_active = !!account.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEdit.value) {
        form.put(route('bank-accounts.update', form.id), {
            onSuccess: () => {
                toast.success('Rekening berhasil diperbarui');
                closeModal();
            },
            onError: () => toast.error('Gagal memperbarui rekening'),
        });
    } else {
        form.post(route('bank-accounts.store'), {
            onSuccess: () => {
                toast.success('Rekening berhasil ditambahkan');
                closeModal();
            },
            onError: () => toast.error('Gagal menambahkan rekening'),
        });
    }
};

const deleteAccount = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus rekening ini?')) {
        router.delete(route('bank-accounts.destroy', id), {
            onSuccess: () => toast.success('Rekening berhasil dihapus'),
        });
    }
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
