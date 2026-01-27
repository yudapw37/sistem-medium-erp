<template>
    <DashboardLayout>
        <Head title="Pelanggan" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        {{ $page.props.filters?.member ? 'Member' : 'Pelanggan' }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ customers.total || 0 }} pelanggan terdaftar
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Pelanggan"
                    :href="route('customers.create')"
                />
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
            <div class="w-full sm:w-80">
                <Search :url="route('customers.index')" placeholder="Cari pelanggan..." />
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
                    title="Grid View"
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
                    title="List View"
                >
                    <IconList :size="20" />
                </button>
            </div>
        </div>

        <!-- Content -->
        <template v-if="customers.data.length > 0">
            <!-- Grid View -->
            <div
                v-if="viewMode === 'grid'"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
            >
                <CustomerCard
                    v-for="customer in customers.data"
                    :key="customer.id"
                    :customer="customer"
                />
            </div>

            <!-- List View -->
            <template v-else>
                <TableCard title="Data Pelanggan">
                    <Table>
                        <TableThead>
                            <tr>
                                <TableTh class="w-10">No</TableTh>
                                <TableTh>Pelanggan</TableTh>
                                <TableTh>No. Telepon</TableTh>
                                <TableTh>Alamat</TableTh>
                                <TableTh></TableTh>
                            </tr>
                        </TableThead>
                        <TableTbody>
                            <tr
                                v-for="(customer, i) in customers.data"
                                :key="customer.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd class="text-center">
                                    {{ ++i + (customers.current_page - 1) * customers.per_page }}
                                </TableTd>
                                <TableTd>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-gradient-to-br from-accent-400 to-accent-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                        >
                                            {{ customer.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                                    {{ customer.name }}
                                                </p>
                                                <span v-if="customer.is_member" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400 uppercase">
                                                    Member
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </TableTd>
                                <TableTd>
                                    <span class="text-sm text-slate-600 dark:text-slate-400">
                                        {{ customer.no_telp || '-' }}
                                    </span>
                                </TableTd>
                                <TableTd>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1">
                                        {{ customer.address || '-' }}
                                    </p>
                                </TableTd>
                                <TableTd>
                                    <div class="flex gap-2">
                                        <Button
                                            type="edit"
                                            :icon="IconPencilCog"
                                            class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:border-warning-800 dark:text-warning-400"
                                            :href="route('customers.edit', customer.id)"
                                        />
                                        <Button
                                            type="delete"
                                            :icon="IconTrash"
                                            class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:border-danger-800 dark:text-danger-400"
                                            :url="route('customers.destroy', customer.id)"
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
                Belum Ada Pelanggan
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Tambahkan pelanggan pertama Anda.
            </p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Tambah Pelanggan"
                :href="route('customers.create')"
            />
        </div>

        <Pagination v-if="customers?.links && customers.links.length > 3" :links="customers.links" />
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    IconCirclePlus,
    IconDatabaseOff,
    IconPencilCog,
    IconTrash,
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
import CustomerCard from '@/Components/Dashboard/CustomerCard.vue';

defineProps({
    customers: Object,
});

const viewMode = ref('grid');

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>


