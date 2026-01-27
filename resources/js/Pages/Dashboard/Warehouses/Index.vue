<template>
    <DashboardLayout>
        <Head title="Gudang" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Gudang</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ warehouses.total || warehouses.data?.length || 0 }} gudang terdaftar
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Gudang"
                    :href="route('warehouses.create')"
                />
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="w-full sm:w-80">
                <Search :url="route('warehouses.index')" placeholder="Cari gudang..." />
            </div>
        </div>

        <!-- Content -->
        <template v-if="warehouses.data.length > 0">
            <TableCard title="Data Gudang">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Nama Gudang</TableTh>
                            <TableTh>Lokasi</TableTh>
                            <TableTh>Deskripsi</TableTh>
                            <TableTh></TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(warehouse, i) in warehouses.data"
                            :key="warehouse.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (warehouses.current_page - 1) * warehouses.per_page }}
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                        <IconBuildingWarehouse :size="20" />
                                    </div>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ warehouse.name }}
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ warehouse.location || '-' }}
                                </p>
                            </TableTd>
                            <TableTd>
                                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
                                    {{ warehouse.description || '-' }}
                                </p>
                            </TableTd>
                            <TableTd>
                                <div class="flex gap-2">
                                    <Button
                                        type="button"
                                        :icon="IconRefresh"
                                        class="border bg-primary-100 border-primary-200 text-primary-600 hover:bg-primary-200 dark:bg-primary-900/50 dark:border-primary-800 dark:text-primary-400"
                                        @click="syncWarehouse(warehouse.id)"
                                        title="Sinkronkan Produk"
                                    />
                                    <Button
                                        type="edit"
                                        :icon="IconPencilCog"
                                        class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:border-warning-800 dark:text-warning-400"
                                        :href="route('warehouses.edit', warehouse.id)"
                                    />
                                    <Button
                                        type="delete"
                                        :icon="IconTrash"
                                        class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:border-danger-800 dark:text-danger-400"
                                        :url="route('warehouses.destroy', warehouse.id)"
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
                Belum Ada Gudang
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Tambahkan gudang pertama Anda.
            </p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Tambah Gudang"
                :href="route('warehouses.create')"
            />
        </div>

        <Pagination v-if="warehouses?.links && warehouses.links.length > 3" :links="warehouses.links" />
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import {
    IconCirclePlus,
    IconDatabaseOff,
    IconPencilCog,
    IconTrash,
    IconBuildingWarehouse,
    IconRefresh,
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

defineProps({
    warehouses: Object,
});

const syncWarehouse = (id) => {
    Swal.fire({
        title: 'Sinkronkan Produk?',
        text: 'Sistem akan memastikan semua produk terdaftar di gudang ini dengan stok awal 0 jika belum ada.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Sinkronkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('warehouses.sync', id), {}, {
                onSuccess: () => {
                    Swal.fire(
                        'Berhasil!',
                        'Produk berhasil disinkronkan.',
                        'success'
                    )
                }
            });
        }
    })
}

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>

