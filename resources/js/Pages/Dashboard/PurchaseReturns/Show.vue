<template>
    <DashboardLayout>
        <Head :title="`Detail Return Pembelian ${purchaseReturn.invoice}`" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Return Pembelian</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ purchaseReturn.invoice }}
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="route('purchase-returns.index')"
                />
            </div>
        </div>

        <!-- return Info -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Supplier</label>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white mt-1">
                        {{ purchaseReturn.supplier?.name }}
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Gudang</label>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white mt-1">
                        {{ purchaseReturn.warehouse?.name }}
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Tanggal</label>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white mt-1">
                        {{ new Date(purchaseReturn.created_at).toLocaleString('id-ID') }}
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Status</label>
                    <p class="mt-1">
                        <span
                            :class="[
                                'px-3 py-1 rounded-full text-sm font-semibold',
                                purchaseReturn.status === 'finalized'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            ]"
                        >
                            {{ purchaseReturn.status === 'finalized' ? 'Finalized' : 'Draft' }}
                        </span>
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
                        <TableTh class="text-right">Harga Beli</TableTh>
                        <TableTh class="text-center">Qty</TableTh>
                        <TableTh class="text-right">Subtotal</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <tr
                        v-for="(item, index) in purchaseReturn.details"
                        :key="item.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                        <TableTd class="text-center">{{ index + 1 }}</TableTd>
                        <TableTd class="font-medium text-slate-900 dark:text-white">
                            {{ item.product?.title }}
                        </TableTd>
                        <TableTd class="text-right text-slate-700 dark:text-slate-300">
                            {{ formatCurrency(item.buy_price) }}
                        </TableTd>
                        <TableTd class="text-center font-semibold text-slate-900 dark:text-white">
                            {{ item.qty }}
                        </TableTd>
                        <TableTd class="text-right font-bold text-slate-900 dark:text-white">
                            {{ formatCurrency(item.buy_price * item.qty) }}
                        </TableTd>
                    </tr>
                    <tr class="bg-slate-50 dark:bg-slate-800 font-bold">
                        <TableTd colspan="4" class="text-right">Grand Total</TableTd>
                        <TableTd class="text-right text-primary-600 dark:text-primary-400 text-lg">
                            {{ formatCurrency(purchaseReturn.grand_total) }}
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>

        <!-- Actions -->
        <div v-if="purchaseReturn.status === 'draft'" class="mt-6 flex justify-end gap-3">
            <Button
                type="link"
                :icon="IconEdit"
                class="bg-amber-500 hover:bg-amber-600 text-white"
                label="Edit"
                :href="route('purchase-returns.edit', purchaseReturn.id)"
            />
            <Button
                type="button"
                :icon="IconCheck"
                class="bg-green-500 hover:bg-green-600 text-white"
                label="Finalize"
                @click="handleFinalize"
            />
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
    purchaseReturn: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};

const handleFinalize = () => {
    if (confirm('Finalize return? Stock will be updated and return will be locked.')) {
        router.post(route('purchase-returns.finalize', props.purchaseReturn.id));
    }
};
</script>

