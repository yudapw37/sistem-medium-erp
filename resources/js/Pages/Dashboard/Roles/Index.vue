<template>
    <DashboardLayout>
        <Head title="Akses Group" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <IconUserShield :size="28" class="text-primary-500" />
                        Akses Group
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ roles.total || roles.data?.length || 0 }} group terdaftar
                    </p>
                </div>
                <Button
                    type="button"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Group"
                    @click="form.isOpen = true"
                />
            </div>
        </div>

        <!-- Search -->
        <div class="mb-4 w-full sm:w-80">
            <Search :url="route('roles.index')" placeholder="Cari akses group..." />
        </div>

        <!-- Modal -->
        <Modal
            :show="form.isOpen"
            @close="closeModal"
            :title="form.isUpdate ? 'Ubah Akses Group' : 'Tambah Akses Group'"
            :icon="IconUserShield"
        >
            <form @submit.prevent="form.isUpdate ? updateRole() : saveRole()">
                <div class="mb-4">
                    <Input
                        label="Nama group"
                        type="text"
                        placeholder="Masukan nama group"
                        v-model="form.name"
                        :errors="errors.name"
                    />
                </div>
                <div class="mb-4">
                    <ListBox
                        label="Pilih hak akses"
                        :data="permissions"
                        :selected="form.selectedPermission"
                        :set-selected="setSelectedPermission"
                        :errors="errors.selectedPermission"
                    />
                </div>
                <Button
                    type="submit"
                    :icon="IconPencilCheck"
                    class="bg-primary-500 hover:bg-primary-600 text-white w-full justify-center"
                    label="Simpan"
                />
            </form>
        </Modal>

        <!-- Content -->
        <template v-if="roles.data.length > 0">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <RoleCard
                    v-for="role in roles.data"
                    :key="role.id"
                    :role="role"
                    :on-edit="() => handleEdit(role)"
                    :on-delete="() => handleDelete(role.id)"
                />
            </div>
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
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">Belum Ada Group</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Tambahkan group akses pertama.
            </p>
            <Button
                type="button"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Tambah Group"
                @click="form.isOpen = true"
            />
        </div>

        <Pagination v-if="roles?.links && roles.links.length > 3" :links="roles.links" />
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import {
    IconDatabaseOff,
    IconCirclePlus,
    IconUserShield,
    IconPencilCheck,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Input from '@/Components/Dashboard/Input.vue';
import ListBox from '@/Components/Dashboard/ListBox.vue';
import Modal from '@/Components/Dashboard/Modal.vue';
import Search from '@/Components/Dashboard/Search.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import RoleCard from '@/Components/Dashboard/RoleCard.vue';

const { roles, permissions, errors } = usePage().props;

const form = useForm({
    id: '',
    name: '',
    selectedPermission: [],
    isUpdate: false,
    isOpen: false,
});

const setSelectedPermission = (value) => {
    form.selectedPermission = value;
};

const saveRole = () => {
    const data = {
        ...form.data(),
        selectedPermission: form.selectedPermission.map((permission) => permission.id),
    };
    
    router.post(route('roles.store'), data, {
        onSuccess: () => {
            form.selectedPermission = [];
            form.name = '';
            form.isOpen = false;
        },
    });
};

const updateRole = () => {
    const data = {
        ...form.data(),
        selectedPermission: form.selectedPermission.map((permission) => permission.id),
        _method: 'PUT',
    };
    
    router.post(route('roles.update', form.id), data, {
        onSuccess: () => {
            form.id = '';
            form.name = '';
            form.selectedPermission = [];
            form.isUpdate = false;
            form.isOpen = false;
        },
    });
};

const handleEdit = (role) => {
    form.id = role.id;
    form.selectedPermission = role.permissions;
    form.name = role.name;
    form.isUpdate = true;
    form.isOpen = true;
};

const handleDelete = (roleId) => {
    if (confirm('Hapus role ini?')) {
        router.delete(route('roles.destroy', roleId));
    }
};

const closeModal = () => {
    form.isOpen = false;
    form.id = '';
    form.name = '';
    form.selectedPermission = [];
    form.isUpdate = false;
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>


