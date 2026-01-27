<template>
    <DashboardLayout>
        <Head title="Master Pajak" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Master Pajak</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Kelola jenis-jenis pajak (PPN, PPh, dll)
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Pajak"
                    :href="route('taxes.create')"
                />
            </div>
        </div>

        <!-- Content -->
        <template v-if="taxes.data.length > 0">
            <TableCard title="Daftar Pajak">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Kode</TableTh>
                            <TableTh>Nama</TableTh>
                            <TableTh>Tarif</TableTh>
                            <TableTh>Tipe</TableTh>
                            <TableTh>Berlaku Untuk</TableTh>
                            <TableTh>Status</TableTh>
                            <TableTh></TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(tax, i) in taxes.data"
                            :key="tax.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (taxes.current_page - 1) * taxes.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-mono font-bold text-primary-600">{{ tax.code }}</span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ tax.name }}</span>
                                    <span v-if="tax.is_default" class="px-2 py-0.5 text-xs rounded bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                                        Default
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <span class="font-mono text-lg font-bold text-emerald-600">{{ tax.rate }}%</span>
                            </TableTd>
                            <TableTd>
                                <span :class="[
                                    'px-2 py-1 text-xs rounded-full',
                                    tax.type === 'excluded' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'
                                ]">
                                    {{ tax.type === 'excluded' ? 'Diluar Harga' : 'Termasuk Harga' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ getAppliesLabel(tax.applies_to) }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span :class="[
                                    'px-2 py-1 text-xs rounded-full',
                                    tax.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'
                                ]">
                                    {{ tax.is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex gap-2">
                                    <Button
                                        type="edit"
                                        :icon="IconPencilCog"
                                        class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200"
                                        :href="route('taxes.edit', tax.id)"
                                    />
                                    <Button
                                        type="delete"
                                        :icon="IconTrash"
                                        class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200"
                                        :url="route('taxes.destroy', tax.id)"
                                    />
                                </div>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
        </template>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                <IconReceiptTax :size="32" class="text-slate-400" :stroke-width="1.5" />
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">Belum Ada Pajak</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Tambahkan jenis pajak pertama.</p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Tambah Pajak"
                :href="route('taxes.create')"
            />
        </div>

        <Pagination v-if="taxes?.links && taxes.links.length > 3" :links="taxes.links" />
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { IconCirclePlus, IconPencilCog, IconTrash, IconReceiptTax } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';

defineProps({
    taxes: Object,
});

const getAppliesLabel = (value) => {
    const labels = {
        'sales': 'Penjualan',
        'purchases': 'Pembelian',
        'both': 'Keduanya',
    };
    return labels[value] || value;
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
