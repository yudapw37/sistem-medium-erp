<template>
    <DashboardLayout>
        <Head title="Master Alamat se-Indonesia" />

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <IconMapPin :size="28" class="text-primary-500" />
                        Master Alamat se-Indonesia
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Data wilayah (Provinsi, Kota, Kecamatan) untuk referensi.
                    </p>
                </div>
                <Button
                    type="button"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Alamat Master"
                    @click="openAddModal"
                />
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="w-full sm:w-80">
                        <Search :url="route('master-addresses.index')" placeholder="Cari wilayah..." />
                    </div>
                </div>
            </div>

            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>Provinsi</TableTh>
                        <TableTh>Kota / Kabupaten</TableTh>
                        <TableTh>Kecamatan</TableTh>
                        <TableTh>Kode Pos</TableTh>
                        <TableTh class="w-24 text-center"></TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <tr v-if="masterAddresses.data.length === 0">
                        <td colspan="6" class="py-12 text-center text-slate-500 italic">
                            Belum ada data wilayah.
                        </td>
                    </tr>
                    <tr
                        v-for="(addr, i) in masterAddresses.data"
                        :key="addr.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                        <TableTd class="text-center">
                            {{ ++i + (masterAddresses.current_page - 1) * masterAddresses.per_page }}
                        </TableTd>
                        <TableTd>
                            <span class="font-medium text-slate-900 dark:text-white">
                                {{ addr.province }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <span class="text-sm text-slate-700 dark:text-slate-300">
                                {{ addr.city }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                {{ addr.district }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <span class="text-sm font-mono text-slate-500 dark:text-slate-400">
                                {{ addr.postal_code || '-' }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <div class="flex justify-center gap-2">
                                <Button
                                    type="button"
                                    :icon="IconPencilCog"
                                    class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:border-warning-800 dark:text-warning-400"
                                    @click="openEditModal(addr)"
                                />
                                <Button
                                    type="delete"
                                    :icon="IconTrash"
                                    class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:border-danger-800 dark:text-danger-400"
                                    @click="deleteAddress(addr.id)"
                                />
                            </div>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </div>

        <Pagination v-if="masterAddresses?.links && masterAddresses.links.length > 3" :links="masterAddresses.links" />

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        {{ isEdit ? 'Edit Alamat Master' : 'Tambah Alamat Master' }}
                    </h2>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <IconX :size="24" />
                    </button>
                </div>
                
                <form @submit.prevent="submit">
                    <div class="p-6 space-y-4">
                        <Input
                            type="text"
                            label="Provinsi"
                            placeholder="Contoh: Jawa Timur"
                            :errors="form.errors.province"
                            v-model="form.province"
                        />
                        <Input
                            type="text"
                            label="Kota / Kabupaten"
                            placeholder="Contoh: Surabaya"
                            :errors="form.errors.city"
                            v-model="form.city"
                        />
                        <Input
                            type="text"
                            label="Kecamatan"
                            placeholder="Contoh: Genteng"
                            :errors="form.errors.district"
                            v-model="form.district"
                        />
                        <Input
                            type="text"
                            label="Kode Pos"
                            placeholder="Contoh: 60271"
                            :errors="form.errors.postal_code"
                            v-model="form.postal_code"
                        />
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
    IconMapPin,
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
    masterAddresses: Object,
});

const toast = useToast();
const showModal = ref(false);
const isEdit = ref(false);

const form = useForm({
    id: null,
    province: '',
    city: '',
    district: '',
    postal_code: '',
});

const openAddModal = () => {
    isEdit.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (addr) => {
    isEdit.value = true;
    form.id = addr.id;
    form.province = addr.province;
    form.city = addr.city;
    form.district = addr.district;
    form.postal_code = addr.postal_code || '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEdit.value) {
        form.put(route('master-addresses.update', form.id), {
            onSuccess: () => {
                toast.success('Alamat master berhasil diperbarui');
                closeModal();
            },
            onError: () => toast.error('Gagal memperbarui alamat master'),
        });
    } else {
        form.post(route('master-addresses.store'), {
            onSuccess: () => {
                toast.success('Alamat master berhasil ditambahkan');
                closeModal();
            },
            onError: () => toast.error('Gagal menambahkan alamat master'),
        });
    }
};

const deleteAddress = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data wilayah ini?')) {
        router.delete(route('master-addresses.destroy', id), {
            onSuccess: () => toast.success('Data wilayah berhasil dihapus'),
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
