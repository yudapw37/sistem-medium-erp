<template>
    <DashboardLayout>
        <Head :title="`Detail ${adjustment.code}`" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Penyesuaian Stok</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ adjustment.code }}
                        </p>
                        <span
                            :class="[
                                'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                adjustment.status === 'finalized'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            ]"
                        >
                            {{ adjustment.status }}
                        </span>
                    </div>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="route('stock-adjustments.index')"
                />
            </div>
        </div>

        <!-- Adjustment Info -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Tipe</label>
                    <p class="mt-1">
                        <span
                            :class="[
                                'px-3 py-1 rounded-full text-sm font-semibold',
                                adjustment.type === 'in'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            ]"
                        >
                            {{ adjustment.type === 'in' ? 'Stock In' : 'Stock Out' }}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Gudang</label>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white mt-1">
                        {{ adjustment.warehouse?.name }}
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Tanggal</label>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white mt-1">
                        {{ new Date(adjustment.date).toLocaleDateString('id-ID') }}
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Dibuat Oleh</label>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white mt-1">
                        {{ adjustment.user?.name }}
                    </p>
                </div>
                <div class="md:col-span-2" v-if="adjustment.notes">
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Catatan</label>
                    <p class="text-slate-700 dark:text-slate-300 mt-1">
                        {{ adjustment.notes }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <TableCard title="Detail Item">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh class="w-10">No</TableTh>
                        <TableTh>Produk</TableTh>
                        <TableTh class="text-center">Qty</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <tr
                        v-for="(item, index) in adjustment.details"
                        :key="item.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                        <TableTd class="text-center">{{ index + 1 }}</TableTd>
                        <TableTd class="font-medium text-slate-900 dark:text-white">
                            {{ item.product?.title }}
                        </TableTd>
                        <TableTd class="text-center font-semibold text-slate-900 dark:text-white">
                            {{ item.qty }}
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <!-- Actions -->
        <div class="mt-6 flex justify-end gap-3">
            <template v-if="adjustment.status === 'draft'">
                <Button
                    type="link"
                    :icon="IconEdit"
                    class="bg-amber-500 hover:bg-amber-600 text-white"
                    label="Edit"
                    :href="route('stock-adjustments.edit', adjustment.id)"
                />
                <Button
                    :icon="IconCheck"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/30"
                    label="Finalize"
                    @click="handleFinalize(adjustment.id)"
                />
            </template>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { IconArrowLeft, IconEdit, IconCheck } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';

const props = defineProps({
    adjustment: Object,
});

const handleFinalize = (id) => {
    if (confirm('Finalkan penyesuaian stok ini? Stok akan diupdate dan data akan dikunci.')) {
        router.post(route('stock-adjustments.finalize', id));
    }
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>

