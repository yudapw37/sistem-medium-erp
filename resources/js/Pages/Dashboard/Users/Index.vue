<template>
    <DashboardLayout>
        <Head title="Pengguna" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pengguna</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ users.total || users.data?.length || 0 }} pengguna terdaftar
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="form.selectedUser.length > 0"
                        type="bulk"
                        :icon="IconTrash"
                        class="bg-danger-500 hover:bg-danger-600 text-white"
                        :label="`Hapus ${form.selectedUser.length}`"
                        @click="deleteData(form.selectedUser)"
                    />
                    <Button
                        type="link"
                        :href="route('users.create')"
                        :icon="IconCirclePlus"
                        class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                        label="Tambah Pengguna"
                    />
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
            <div class="w-full sm:w-80">
                <Search :url="route('users.index')" placeholder="Cari pengguna..." />
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="viewMode = 'grid'"
                    :class="[
                        'p-2.5 rounded-lg transition-colors',
                        viewMode === 'grid'
                            ? 'bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400'
                            : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                    ]"
                >
                    <IconLayoutGrid :size="20" />
                </button>
                <button
                    @click="viewMode = 'list'"
                    :class="[
                        'p-2.5 rounded-lg transition-colors',
                        viewMode === 'list'
                            ? 'bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400'
                            : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                    ]"
                >
                    <IconList :size="20" />
                </button>
            </div>
        </div>

        <!-- Content -->
        <template v-if="users.data.length > 0">
            <!-- Grid View -->
            <div
                v-if="viewMode === 'grid'"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
            >
                <UserCard
                    v-for="user in users.data"
                    :key="user.id"
                    :user="user"
                    :is-selected="form.selectedUser.includes(user.id.toString())"
                    :on-select="setSelectedUser"
                    :on-delete="deleteData"
                />
            </div>

            <!-- List View -->
            <template v-else>
                <TableCard title="Data Pengguna">
                    <Table>
                        <TableThead>
                            <tr>
                                <TableTh class="w-10">
                                    <Checkbox
                                        @change="(e) => {
                                            const allUserIds = users.data.map((u) => u.id.toString());
                                            form.selectedUser = e.target.checked ? allUserIds : [];
                                        }"
                                        :checked="form.selectedUser.length === users.data.length"
                                    />
                                </TableTh>
                                <TableTh class="w-10">No</TableTh>
                                <TableTh>Pengguna</TableTh>
                                <TableTh>Group Akses</TableTh>
                                <TableTh></TableTh>
                            </tr>
                        </TableThead>
                        <TableTbody>
                            <tr
                                v-for="(user, i) in users.data"
                                :key="user.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd>
                                    <Checkbox
                                        :value="user.id"
                                        @change="setSelectedUser"
                                        :checked="form.selectedUser.includes(user.id.toString())"
                                    />
                                </TableTd>
                                <TableTd class="text-center">
                                    {{ ++i + (users.current_page - 1) * users.per_page }}
                                </TableTd>
                                <TableTd>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-bold"
                                        >
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                                {{ user.name }}
                                            </p>
                                            <p class="text-xs text-slate-500">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </TableTd>
                                <TableTd>
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="(role, index) in user.roles"
                                            :key="index"
                                            class="px-2 py-0.5 text-xs font-medium bg-accent-100 dark:bg-accent-900/50 text-accent-700 dark:text-accent-400 rounded-full"
                                        >
                                            {{ role.name }}
                                        </span>
                                    </div>
                                </TableTd>
                                <TableTd>
                                    <div class="flex gap-2">
                                        <Button
                                            type="edit"
                                            :icon="IconPencilCog"
                                            class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:border-warning-800 dark:text-warning-400"
                                            :href="route('users.edit', user.id)"
                                        />
                                        <Button
                                            type="delete"
                                            :icon="IconTrash"
                                            class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:border-danger-800 dark:text-danger-400"
                                            :url="route('users.destroy', user.id)"
                                        />
                                    </div>
                                </TableTd>
                            </tr>
                        </TableTbody>
                    </Table>
                </TableCard>
            </template>
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
                Belum Ada Pengguna
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Tambahkan pengguna pertama Anda.
            </p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Tambah Pengguna"
                :href="route('users.create')"
            />
        </div>

        <Pagination v-if="users?.links && users.links.length > 3" :links="users.links" />
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import {
    IconDatabaseOff,
    IconCirclePlus,
    IconTrash,
    IconPencilCog,
    IconLayoutGrid,
    IconList,
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
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import UserCard from '@/Components/Dashboard/UserCard.vue';

const { users } = usePage().props;
const viewMode = ref('grid');

const form = useForm({
    selectedUser: [],
});

const setSelectedUser = (e) => {
    let items = [...form.selectedUser];
    const value = e.target.value.toString();
    if (items.includes(value)) {
        items = items.filter((id) => id !== value);
    } else {
        items.push(value);
    }
    form.selectedUser = items;
};

const deleteData = async (id) => {
    const result = await Swal.fire({
        title: 'Hapus Pengguna?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
    });

    if (result.isConfirmed) {
        form.delete(route('users.destroy', Array.isArray(id) ? id : [id]));
        Swal.fire({
            title: 'Berhasil!',
            text: 'Data berhasil dihapus!',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500,
        });
        form.selectedUser = [];
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


