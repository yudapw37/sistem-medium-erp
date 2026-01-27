<template>
    <DashboardLayout>
        <Head title="Daftar Alamat Pelanggan" />

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <IconAddressBook :size="28" class="text-primary-500" />
                        Daftar Alamat Pelanggan
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Data alamat pengiriman yang tersimpan untuk pelanggan.
                    </p>
                </div>
                <Button
                    type="button"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Alamat"
                    @click="showModal = true"
                />
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="w-full sm:w-80">
                        <Search :url="route('customer-addresses.index')" placeholder="Cari pelanggan atau alamat..." />
                    </div>
                </div>
            </div>

            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>Pelanggan</TableTh>
                        <TableTh>Label / Nama</TableTh>
                        <TableTh>No. Telp</TableTh>
                        <TableTh>Alamat</TableTh>
                        <TableTh></TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <tr v-if="addresses.data.length === 0">
                        <td colspan="6" class="py-12 text-center text-slate-500 italic">
                            Belum ada data alamat tersimpan.
                        </td>
                    </tr>
                    <tr
                        v-for="(address, i) in addresses.data"
                        :key="address.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                        <TableTd class="text-center">
                            {{ ++i + (addresses.current_page - 1) * addresses.per_page }}
                        </TableTd>
                        <TableTd>
                            <span class="font-medium text-slate-900 dark:text-white">
                                {{ address.customer?.name }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-primary-600 uppercase tracking-wider mb-0.5">
                                    {{ address.label }}
                                </span>
                                <span class="text-sm text-slate-700 dark:text-slate-300">
                                    {{ address.name }}
                                </span>
                            </div>
                        </TableTd>
                        <TableTd>
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                {{ address.phone }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1 max-w-xs">
                                {{ address.address }}
                            </p>
                        </TableTd>
                        <TableTd>
                            <div class="flex gap-2">
                                <Button
                                    type="button"
                                    :icon="IconPencilCog"
                                    class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:border-warning-800 dark:text-warning-400"
                                    @click="editAddress(address)"
                                />
                                <Button
                                    type="delete"
                                    :icon="IconTrash"
                                    class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:border-danger-800 dark:text-danger-400"
                                    @click="deleteAddress(address.id)"
                                />
                            </div>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </div>

        <Pagination v-if="addresses?.links && addresses.links.length > 3" :links="addresses.links" />

        <!-- Add Address Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        {{ isEdit ? 'Edit Alamat' : 'Tambah Alamat Baru' }}
                    </h2>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <IconX :size="24" />
                    </button>
                </div>
                
                <form @submit.prevent="submit">
                    <div class="p-6 space-y-4">
                        <InputSelect
                            v-if="!isEdit"
                            label="Pilih Pelanggan"
                            :data="customers"
                            :selected="selectedCustomer"
                            :set-selected="handleSelectCustomer"
                            placeholder="Cari Pelanggan..."
                            :searchable="true"
                            :errors="form.errors.customer_id"
                        />
                        <div v-else>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Pelanggan</label>
                            <div class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl text-slate-700 dark:text-slate-300 font-medium">
                                {{ selectedCustomer?.name }}
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                type="text"
                                label="Label Alamat"
                                placeholder="Contoh: Rumah, Kantor"
                                :errors="form.errors.label"
                                v-model="form.label"
                            />
                            <Input
                                type="text"
                                label="Nama Penerima"
                                placeholder="Nama lengkap penerima"
                                :errors="form.errors.name"
                                v-model="form.name"
                            />
                        </div>

                        <Input
                            type="text"
                            label="No. Telepon Penerima"
                            placeholder="08xxxxxxxxxx"
                            :errors="form.errors.phone"
                            v-model="form.phone"
                        />

                        <Textarea
                            label="Alamat Lengkap"
                            placeholder="Masukkan alamat lengkap..."
                            :errors="form.errors.address"
                            v-model="form.address"
                            :rows="3"
                        />
                    </div>

                    <div class="p-6 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-slate-50 dark:bg-slate-800/50">
                        <button
                            type="button"
                            @click="showModal = false"
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
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Alamat' }}
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
    IconAddressBook,
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
import Textarea from '@/Components/Dashboard/TextArea.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';

const props = defineProps({
    addresses: Object,
    customers: Array,
});

const toast = useToast();
const showModal = ref(false);
const isEdit = ref(false);
const selectedCustomer = ref(null);

const form = useForm({
    id: null,
    customer_id: '',
    label: '',
    name: '',
    phone: '',
    address: '',
});

const closeModal = () => {
    showModal.value = false;
    isEdit.value = false;
    form.reset();
    selectedCustomer.value = null;
};

const editAddress = (address) => {
    isEdit.value = true;
    selectedCustomer.value = address.customer;
    form.id = address.id;
    form.customer_id = address.customer_id;
    form.label = address.label;
    form.name = address.name;
    form.phone = address.phone;
    form.address = address.address;
    showModal.value = true;
};

const handleSelectCustomer = (value) => {
    selectedCustomer.value = value;
    form.customer_id = value ? value.id : '';
};

const submit = () => {
    if (isEdit.value) {
        form.put(route('customer-addresses.update', form.id), {
            onSuccess: () => {
                toast.success('Alamat berhasil diperbarui');
                closeModal();
            },
            onError: () => toast.error('Gagal memperbarui alamat'),
        });
    } else {
        form.post(route('customer-addresses.store'), {
            onSuccess: () => {
                toast.success('Alamat berhasil ditambahkan');
                closeModal();
            },
            onError: (errors) => {
                if (errors.customer_id) {
                    toast.error(errors.customer_id);
                } else {
                    toast.error('Gagal menambahkan alamat. Periksa inputan Anda.');
                }
            }
        });
    }
};

const deleteAddress = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus alamat ini?')) {
        router.delete(route('customer-addresses.destroy', id), {
            onSuccess: () => toast.success('Alamat berhasil dihapus'),
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
