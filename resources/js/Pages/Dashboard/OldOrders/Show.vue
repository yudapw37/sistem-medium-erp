<template>
    <DashboardLayout>
        <Head :title="`Detail Old Order #${order.id}`" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Old Order</h1>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Code Order: {{ order.id }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        type="link"
                        :icon="IconArrowLeft"
                        class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        label="Kembali"
                        :href="route('old-orders.index')"
                    />
                    <a
                        :href="route('old-orders.print', order.id)"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-primary-500 text-white hover:bg-primary-600 transition-all"
                    >
                        <IconPrinter :size="18" />
                        Cetak Invoice
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Details Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Informasi Order</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Code Order</p>
                            <p class="font-bold text-primary-600 dark:text-primary-400 text-lg">{{ order.id }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Customer</p>
                            <p class="font-medium text-slate-900 dark:text-white">{{ order.customer?.nama || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Tanggal</p>
                            <p class="font-medium text-slate-900 dark:text-white">
                                {{ order.created_at ? new Date(order.created_at).toLocaleString('id-ID') : '-' }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">PENGIRIM</h4>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ order.nama_pengirim || '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ order.telephone_pengirim || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">PENERIMA</h4>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ order.nama_penerima || '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ order.telephone_penerima || '-' }}</p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-slate-50 dark:border-slate-800/50">
                                <p class="text-xs text-slate-500 uppercase tracking-wider">Alamat</p>
                                <p class="text-sm text-slate-900 dark:text-white mt-1">{{ order.alamat || '-' }}</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ [order.kecamatan, order.kab_kota].filter(Boolean).join(', ') || '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Total Barang</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ order.total_barang }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Total Harga</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ formatCurrency(order.total_harga) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Total Diskon</span>
                                <span class="font-medium text-danger-500">{{ order.totalDiskon > 0 ? '-' + formatCurrency(order.totalDiskon) : '-' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Diskon Kode Unik</span>
                                <span class="font-medium text-danger-500">{{ order.diskonKodeUnik > 0 ? '-' + formatCurrency(order.diskonKodeUnik) : '-' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Biaya Expedisi</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ formatCurrency(order.biayaExpedisi) }}</span>
                            </div>
                            <div class="flex justify-between text-sm pt-3 border-t border-slate-100 dark:border-slate-800">
                                <span class="font-bold text-slate-700 dark:text-slate-300">GRAND TOTAL</span>
                                <span class="font-black text-primary-600 dark:text-primary-400 text-lg">
                                    {{ formatCurrency(grandTotal) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Daftar Item</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/50 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="px-6 py-4">No</th>
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4 text-center">Qty</th>
                                    <th class="px-6 py-4 text-right">Berat</th>
                                    <th class="px-6 py-4 text-right">Harga</th>
                                    <th class="px-6 py-4 text-right">Diskon (%)</th>
                                    <th class="px-6 py-4 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="(item, i) in groupedItems" :key="i" class="text-sm">
                                    <td class="px-6 py-4 text-slate-500">{{ i + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <!-- Bundling promo: nama promo + list semua barang di bawah -->
                                        <template v-if="item.isBundle">
                                            <span class="font-medium text-slate-900 dark:text-white">
                                                {{ item.nama_promo }}
                                            </span>
                                            <div class="mt-1 pl-3 border-l-2 border-slate-200 dark:border-slate-700 space-y-0.5">
                                                <div v-for="(child, j) in item.children" :key="j" class="text-xs text-slate-400 flex items-center gap-1">
                                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                    {{ child.barang?.judul_buku || '-' }} ({{ child.jumlah }}x)
                                                </div>
                                            </div>
                                        </template>
                                        <!-- Item biasa tanpa promo -->
                                        <template v-else>
                                            <span class="font-medium text-slate-900 dark:text-white">
                                                {{ item.barang?.judul_buku || '-' }}
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-900 dark:text-white font-medium">
                                        {{ item.totalQty || item.jumlah }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">
                                        <template v-if="item.isBundle">
                                            {{ (item.totalBerat / 1000).toFixed(2) }} kg
                                        </template>
                                        <template v-else>
                                            {{ ((item.barang?.berat || 0) / 1000).toFixed(2) }} kg
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">
                                        <template v-if="item.isBundle">
                                            {{ formatCurrency(item.harga_promo) }}
                                        </template>
                                        <template v-else>
                                            {{ formatCurrency(item.harga) }}
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span v-if="item.diskon > 0" class="text-danger-500 font-medium">
                                            {{ item.diskon }}%
                                        </span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-slate-900 dark:text-white font-bold">
                                        {{ formatCurrency(item.subtotal) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot v-if="order.details && order.details.length > 0">
                                <tr class="bg-slate-50 dark:bg-slate-800/30">
                                    <td colspan="6" class="px-6 py-4 text-right font-bold text-slate-600 dark:text-slate-400">
                                        TOTAL ITEM
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-primary-600 dark:text-primary-400 text-lg">
                                        {{ formatCurrency(itemsTotal) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Empty items -->
                    <div v-if="!order.details || order.details.length === 0" class="p-12 text-center">
                        <p class="text-slate-400">Tidak ada detail item.</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { IconArrowLeft, IconPrinter } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    order: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const calcSubtotal = (detail, isPromo = null) => {
    // Determine if this is a real promo item
    const hasPromo = isPromo !== null ? isPromo : (detail.nama_promo && detail.nama_promo !== '-' && detail.code_promo && detail.code_promo !== '-');
    const price = hasPromo && detail.harga_promo > 0 ? detail.harga_promo : detail.harga;
    const qty = detail.jumlah || 0;
    let subtotal = price * qty;
    if (detail.diskon > 0) {
        subtotal = subtotal - (subtotal * detail.diskon / 100);
    }
    return subtotal;
};

// Group promo items as bundles
const groupedItems = computed(() => {
    if (!props.order.details) return [];

    const items = [];
    const promoMap = new Map();

    for (const detail of props.order.details) {
        const hasPromo = detail.nama_promo && detail.nama_promo !== '-' && detail.code_promo && detail.code_promo !== '-';
        if (hasPromo) {
            // Group by nama_promo
            const key = detail.nama_promo;
            if (!promoMap.has(key)) {
                const bundle = {
                    isBundle: true,
                    nama_promo: detail.nama_promo,
                    harga_promo: detail.harga_promo || 0,
                    diskon: detail.diskon || 0,
                    totalQty: 0,
                    totalBerat: 0,
                    subtotal: 0,
                    children: [],
                };
                promoMap.set(key, bundle);
                items.push(bundle);
            }
            const bundle = promoMap.get(key);
            bundle.children.push(detail);
            bundle.totalQty += detail.jumlah || 0;
            bundle.totalBerat += parseFloat(detail.barang?.berat || 0) * (detail.jumlah || 0);
            bundle.subtotal += calcSubtotal(detail, true);
        } else {
            // Item biasa
            items.push({
                ...detail,
                isBundle: false,
                subtotal: calcSubtotal(detail, false),
            });
        }
    }

    return items;
});

const itemsTotal = computed(() => {
    return groupedItems.value.reduce((total, item) => total + item.subtotal, 0);
});

const grandTotal = computed(() => {
    const harga = parseFloat(props.order.total_harga) || 0;
    const diskon = parseFloat(props.order.totalDiskon) || 0;
    const diskonKode = parseFloat(props.order.diskonKodeUnik) || 0;
    const expedisi = parseFloat(props.order.biayaExpedisi) || 0;
    return harga - diskon - diskonKode + expedisi;
});

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
