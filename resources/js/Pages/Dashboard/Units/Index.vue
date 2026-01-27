<template>
    <DashboardLayout>
        <Head title="Master Satuan" />

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Master Satuan</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Kelola satuan produk (pcs, box, karton, dll)
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Satuan"
                    :href="route('units.create')"
                />
            </div>
        </div>

        <div class="mb-4">
            <div class="w-full sm:w-80">
                <Search :url="route('units.index')" placeholder="Cari satuan..." />
            </div>
        </div>

        <template v-if="units.data.length > 0">
            <TableCard title="Daftar Satuan">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Kode</TableTh>
                            <TableTh>Nama</TableTh>
                            <TableTh>Deskripsi</TableTh>
                            <TableTh>Status</TableTh>
                            <TableTh></TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(unit, i) in units.data"
                            :key="unit.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (units.current_page - 1) * units.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-mono font-bold text-primary-600">{{ unit.code }}</span>
                            </TableTd>
                            <TableTd>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ unit.name }}</span>
                            </TableTd>
                            <TableTd>
                                <span class="text-sm text-slate-500 dark:text-slate-400">{{ unit.description || '-' }}</span>
                            </TableTd>
                            <TableTd>
                                <span :class="[
                                    'px-2 py-1 text-xs rounded-full',
                                    unit.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'
                                ]">
                                    {{ unit.is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex gap-2">
                                    <Button
                                        type="edit"
                                        :icon="IconPencilCog"
                                        class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200"
                                        :href="route('units.edit', unit.id)"
                                    />
                                    <Button
                                        type="delete"
                                        :icon="IconTrash"
                                        class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200"
                                        :url="route('units.destroy', unit.id)"
                                    />
                                </div>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
        </template>

        <div v-else class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                <IconRuler :size="32" class="text-slate-400" :stroke-width="1.5" />
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">Belum Ada Satuan</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Tambahkan satuan pertama.</p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Tambah Satuan"
                :href="route('units.create')"
            />
        </div>

        <Pagination v-if="units?.links && units.links.length > 3" :links="units.links" />
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { IconCirclePlus, IconPencilCog, IconTrash, IconRuler } from '@tabler/icons-vue';
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
    units: Object,
});

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
