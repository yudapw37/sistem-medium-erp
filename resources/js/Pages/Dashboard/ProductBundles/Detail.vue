<template>
    <DashboardLayout>
        <Head :title="`Bundle: ${bundle.name}`" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ bundle.name }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ bundle.code }}</p>
                </div>
                <div class="flex gap-2">
                    <Button
                        type="link"
                        :icon="IconEdit"
                        class="bg-primary-500 hover:bg-primary-600 text-white"
                        label="Edit"
                        :href="route('product-bundles.edit', bundle.id)"
                    />
                    <Button
                        type="link"
                        :icon="IconArrowLeft"
                        class="bg-white border border-slate-200 text-slate-700"
                        label="Kembali"
                        :href="route('product-bundles.index')"
                    />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-6">
                <!-- Informasi Utama -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm font-sans">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Informasi Bundle</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500">Kode Bundle</span>
                            <span class="font-medium text-slate-900 dark:text-white uppercase">{{ bundle.code }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-sm">Harga Jual</span>
                            <span class="font-bold text-primary-600 text-lg">{{ formatCurrency(bundle.sell_price) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500">Status</span>
                            <span
                                :class="bundle.is_active ? 'bg-success-100 text-success-700 dark:bg-success-900/30' : 'bg-slate-100 text-slate-600'"
                                class="px-2 py-0.5 rounded text-xs font-semibold"
                            >
                                {{ bundle.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 text-sm">Prakiraan Stok</span>
                            <span
                                class="font-bold text-lg"
                                :class="availableStock > 0 ? 'text-success-600' : 'text-danger-600'"
                            >
                                {{ availableStock }} <span class="text-sm font-normal text-slate-400">paket</span>
                            </span>
                        </div>
                    </div>

                    <div v-if="bundle.description" class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Deskripsi</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ bundle.description }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <!-- Item List -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Daftar Produk ({{ bundle.items?.length || 0 }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-left">
                                    <th class="px-6 py-3 font-medium">Produk</th>
                                    <th class="px-6 py-3 font-medium text-right">Harga Satuan</th>
                                    <th class="px-6 py-3 font-medium text-center">Qty</th>
                                    <th class="px-6 py-3 font-medium text-right">Subtotal</th>
                                    <th class="px-6 py-3 font-medium text-right">Stok Gudang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="item in bundle.items" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900 dark:text-white">{{ item.product?.title }}</div>
                                        <div class="text-xs text-slate-400">{{ item.product?.barcode }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">{{ formatCurrency(item.product?.sell_price) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-lg font-bold">
                                            {{ item.qty }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-slate-900 dark:text-white">
                                        {{ formatCurrency(item.product?.sell_price * item.qty) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span
                                            :class="item.product?.stock > 0 ? 'text-success-600' : 'text-danger-600'"
                                            class="font-medium"
                                        >
                                            {{ item.product?.stock || 0 }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
                        <div class="max-w-xs ml-auto space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Total Harga Retail</span>
                                <span class="text-slate-400 line-through">{{ formatCurrency(totalRetailPrice) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-primary-600">
                                <span class="font-medium">Harga Paket Bundle</span>
                                <span class="text-xl font-bold">{{ formatCurrency(bundle.sell_price) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-200 dark:border-slate-700 text-success-600 font-bold">
                                <span>Hemat</span>
                                <span>
                                    {{ formatCurrency(totalRetailPrice - bundle.sell_price) }}
                                    <span class="text-xs font-normal ml-1">({{ Math.round((1 - bundle.sell_price / totalRetailPrice) * 100) }}%)</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { IconArrowLeft, IconEdit } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    bundle: Object,
    availableStock: Number
});

const totalRetailPrice = computed(() => {
    return (props.bundle.items || []).reduce((total, item) => {
        return total + ((item.product?.sell_price || 0) * item.qty);
    }, 0);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
