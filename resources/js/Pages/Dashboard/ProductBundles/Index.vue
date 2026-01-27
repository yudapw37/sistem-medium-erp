<template>
    <DashboardLayout>
        <Head title="Bundling Produk" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Bundling Produk</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Kelola paket bundling produk
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconPlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Bundle Baru"
                    :href="route('product-bundles.create')"
                />
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari bundle..."
                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
                />
            </div>

            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="text-center">No</TableTh>
                        <TableTh>Kode</TableTh>
                        <TableTh>Nama Bundle</TableTh>
                        <TableTh class="text-center">Jumlah Item</TableTh>
                        <TableTh class="text-right">Harga Jual</TableTh>
                        <TableTh class="text-center">Status</TableTh>
                        <TableTh class="text-center">Aksi</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <tr
                        v-for="(bundle, i) in bundles.data"
                        :key="bundle.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                        <TableTd class="text-center">
                            {{ ++i + (bundles.current_page - 1) * bundles.per_page }}
                        </TableTd>
                        <TableTd>
                            <span class="font-bold text-slate-900 dark:text-white">
                                {{ bundle.code }}
                            </span>
                        </TableTd>
                        <TableTd>
                            <div class="flex items-center gap-2">
                                <IconPackage :size="16" class="text-primary-500" />
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ bundle.name }}
                                </span>
                            </div>
                        </TableTd>
                        <TableTd class="text-center">
                            <span class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-medium">
                                {{ bundle.items?.length || 0 }} item
                            </span>
                        </TableTd>
                        <TableTd class="text-right font-semibold text-slate-900 dark:text-white">
                            {{ formatCurrency(bundle.sell_price) }}
                        </TableTd>
                        <TableTd class="text-center">
                            <span
                                :class="[
                                    'px-3 py-1 rounded-lg text-xs font-semibold',
                                    bundle.is_active
                                        ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                ]"
                            >
                                {{ bundle.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </TableTd>
                        <TableTd class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <Button
                                    type="link"
                                    :icon="IconEye"
                                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300"
                                    :href="route('product-bundles.show', bundle.id)"
                                />
                                <Button
                                    type="link"
                                    :icon="IconEdit"
                                    class="bg-primary-100 hover:bg-primary-200 text-primary-700 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 dark:text-primary-400"
                                    :href="route('product-bundles.edit', bundle.id)"
                                />
                                <Button
                                    type="button"
                                    :icon="IconTrash"
                                    class="bg-danger-100 hover:bg-danger-200 text-danger-700 dark:bg-danger-900/30 dark:hover:bg-danger-900/50 dark:text-danger-400"
                                    @click="deleteBundle(bundle.id)"
                                />
                            </div>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>

            <div class="p-6 border-t border-slate-200 dark:border-slate-800">
                <Pagination :links="bundles.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { IconPlus, IconPackage, IconEye, IconEdit, IconTrash } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import { debounce } from 'lodash';

const props = defineProps({
    bundles: Object,
});

const search = ref('');

watch(search, debounce((value) => {
    router.get(route('product-bundles.index'), { q: value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300));

const deleteBundle = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus bundle ini?')) {
        router.delete(route('product-bundles.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
