<template>
    <div>
        <Head title="Invoice Penjualan" />

        <div
            class="min-h-screen bg-slate-100 dark:bg-slate-950 py-8 px-4 print:bg-white print:p-0"
        >
            <div class="max-w-4xl mx-auto space-y-6">
                <!-- Action Bar -->
                <div class="flex flex-wrap items-center justify-between gap-3 print:hidden">
                    <Link
                        :href="route('transactions.index')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        <IconArrowLeft :size="18" />
                        Kembali ke kasir
                    </Link>

                    <div class="flex items-center gap-2">
                        <div class="flex bg-slate-200 dark:bg-slate-800 rounded-xl p-1">
                            <button
                                @click="printMode = 'invoice'"
                                :class="[
                                    'px-3 py-2 rounded-lg text-xs font-medium transition-all',
                                    printMode === 'invoice'
                                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700',
                                ]"
                            >
                                <IconFileInvoice :size="16" class="inline mr-1" />
                                Invoice
                            </button>
                            <button
                                @click="printMode = 'thermal80'"
                                :class="[
                                    'px-3 py-2 rounded-lg text-xs font-medium transition-all',
                                    printMode === 'thermal80'
                                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700',
                                ]"
                            >
                                <IconReceipt :size="16" class="inline mr-1" />
                                Struk 80mm
                            </button>
                            <button
                                @click="printMode = 'thermal58'"
                                :class="[
                                    'px-3 py-2 rounded-lg text-xs font-medium transition-all',
                                    printMode === 'thermal58'
                                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700',
                                ]"
                            >
                                <IconReceipt :size="16" class="inline mr-1" />
                                Struk 58mm
                            </button>
                        </div>

                        <a
                            v-if="showPaymentLink"
                            :href="transaction.payment_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-primary-200 dark:border-primary-800 text-sm font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/50 transition-colors"
                        >
                            <IconExternalLink :size="18" />
                            Pembayaran
                        </a>

                        <button
                            type="button"
                            @click="handlePrint"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-sm font-semibold text-white shadow-lg shadow-primary-500/30 transition-colors"
                        >
                            <IconPrinter :size="18" />
                            Cetak
                        </button>
                    </div>
                </div>

                <!-- Thermal Receipt Preview -->
                <div
                    v-if="printMode === 'thermal80' || printMode === 'thermal58'"
                    class="flex justify-center print:block"
                >
                    <div
                        class="bg-white rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xl p-4 print:shadow-none print:border-0 print:p-0 print:rounded-none"
                    >
                        <ThermalReceipt
                            v-if="printMode === 'thermal80'"
                            :transaction="transaction"
                            store-name="TOKO ANDA"
                            store-address="Jl. Contoh No. 123"
                            store-phone="08123456789"
                        />
                        <ThermalReceipt58mm
                            v-else
                            :transaction="transaction"
                            store-name="TOKO"
                            store-phone="08123456789"
                        />
                    </div>
                </div>

                <!-- Invoice View -->
                <div
                    v-if="printMode === 'invoice'"
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xl print:shadow-none print:border-slate-300"
                >
                    <div
                        class="bg-gradient-to-r from-primary-500 to-primary-700 px-6 py-6 text-white print:bg-slate-100 print:text-slate-900"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <IconReceipt :size="24" />
                                    <span class="text-sm font-medium opacity-90 print:opacity-100">
                                        INVOICE
                                    </span>
                                </div>
                                <p class="text-2xl font-bold">{{ transaction.invoice }}</p>
                                <p class="text-sm opacity-80 print:opacity-100 mt-1">
                                    {{ formatDateTime(transaction.created_at) }}
                                </p>
                            </div>

                            <div class="text-right">
                                <span
                                    :class="[
                                        'inline-block px-3 py-1 text-xs font-semibold rounded-full',
                                        paymentStatusColor,
                                    ]"
                                >
                                    {{ paymentStatusLabel }}
                                </span>
                                <p class="text-sm opacity-80 print:opacity-100 mt-2">
                                    {{ paymentMethodLabel }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid md:grid-cols-2 gap-6 px-6 py-6 border-b border-slate-100 dark:border-slate-800"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2"
                            >
                                Pelanggan
                            </p>
                            <p class="text-base font-semibold text-slate-900 dark:text-white">
                                {{ transaction.customer?.name ?? 'Umum' }}
                            </p>
                            <p v-if="transaction.customer?.address" class="text-sm text-slate-600 dark:text-slate-400">
                                {{ transaction.customer.address }}
                            </p>
                            <p v-if="transaction.customer?.phone" class="text-sm text-slate-600 dark:text-slate-400">
                                {{ transaction.customer.phone }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2"
                            >
                                Kasir
                            </p>
                            <p class="text-base font-semibold text-slate-900 dark:text-white">
                                {{ transaction.cashier?.name ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <th
                                        class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                                    >
                                        Produk
                                    </th>
                                    <th
                                        class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                                    >
                                        Harga
                                    </th>
                                    <th
                                        class="pb-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                                    >
                                        Qty
                                    </th>
                                    <th
                                        class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                                    >
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="(item, index) in items" :key="item.id ?? index">
                                    <td class="py-3">
                                        <p class="font-medium text-slate-900 dark:text-white">
                                            {{ item.bundle ? '[BUNDLE] ' + item.bundle.name : item.product?.title }}
                                        </p>
                                        <div v-if="item.bundle?.items" class="mt-1 space-y-0.5">
                                            <p v-for="bundleItem in item.bundle.items" :key="bundleItem.id" class="text-[10px] text-slate-500 flex items-center gap-1">
                                                <IconArrowRight :size="10" />
                                                {{ bundleItem.product?.title }} ({{ bundleItem.qty }} pcs)
                                            </p>
                                        </div>
                                        <p v-if="item.product?.barcode || item.bundle?.code" class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ item.bundle?.code || item.product.barcode }}
                                        </p>
                                    </td>
                                    <td class="py-3 text-right text-slate-600 dark:text-slate-400">
                                        {{ formatPrice(item.price) }}
                                    </td>
                                    <td class="py-3 text-center text-slate-600 dark:text-slate-400">
                                        {{ item.qty || item.quantity }}
                                    </td>
                                    <td class="py-3 text-right font-semibold text-slate-900 dark:text-white">
                                        {{ formatPrice((item.price || 0) * (item.qty || item.quantity || 0)) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-6">
                        <div class="max-w-xs ml-auto space-y-2 text-sm">
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Subtotal</span>
                                <span>
                                    {{ formatPrice(transaction.grand_total + (transaction.discount || 0) + (transaction.event_discount || 0)) }}
                                </span>
                            </div>
                            <div v-if="(transaction.discount || 0) > 0" class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>
                                    Diskon
                                    <span v-if="transaction.discount_type === 'percent' && transaction.discount_percent" class="text-xs ml-1">
                                        ({{ transaction.discount_percent }}%)
                                    </span>
                                </span>
                                <span>- {{ formatPrice(transaction.discount || 0) }}</span>
                            </div>
                            <div v-if="(transaction.event_discount || 0) > 0" class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>
                                    Diskon Event
                                    <span v-if="transaction.event_discount_type === 'percent' && transaction.event_discount_percent" class="text-xs ml-1">
                                        ({{ transaction.event_discount_percent }}%)
                                    </span>
                                </span>
                                <span>- {{ formatPrice(transaction.event_discount || 0) }}</span>
                            </div>
                            <div
                                class="flex justify-between text-lg font-bold text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-700"
                            >
                                <span>Total</span>
                                <span>{{ formatPrice(transaction.grand_total) }}</span>
                            </div>
                            <template v-if="paymentMethodKey === 'cash'">
                                <div class="flex justify-between text-slate-600 dark:text-slate-400 pt-2">
                                    <span>Tunai</span>
                                    <span>{{ formatPrice(transaction.cash) }}</span>
                                </div>
                                <div class="flex justify-between text-success-600 dark:text-success-400 font-medium">
                                    <span>Kembali</span>
                                    <span>{{ formatPrice(transaction.change) }}</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div
                        class="px-6 py-4 text-center border-t border-slate-100 dark:border-slate-800"
                    >
                        <p class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            Terima kasih telah berbelanja
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconPrinter,
    IconExternalLink,
    IconReceipt,
    IconFileInvoice,
} from '@tabler/icons-vue';
import ThermalReceipt from '@/Components/Receipt/ThermalReceipt.vue';
import ThermalReceipt58mm from '@/Components/Receipt/ThermalReceipt58mm.vue';

const props = defineProps({
    transaction: Object,
});

const printMode = ref('invoice');

const formatPrice = (price = 0) =>
    Number(price || 0).toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    });

const formatDateTime = (value) =>
    new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const items = computed(() => props.transaction?.details ?? []);

const paymentLabels = {
    cash: 'Tunai',
    midtrans: 'Midtrans',
    xendit: 'Xendit',
};
const paymentMethodKey = computed(() => (props.transaction?.payment_method || 'cash').toLowerCase());
const paymentMethodLabel = computed(() => paymentLabels[paymentMethodKey.value] ?? 'Tunai');

const paymentStatuses = {
    paid: 'Lunas',
    pending: 'Menunggu',
    failed: 'Gagal',
    expired: 'Kedaluwarsa',
};
const paymentStatusKey = computed(() => (props.transaction?.payment_status || '').toLowerCase());
const paymentStatusLabel = computed(() =>
    paymentStatuses[paymentStatusKey.value] ??
    (paymentMethodKey.value === 'cash' ? 'Lunas' : 'Menunggu')
);

const statusColors = {
    paid: 'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-400',
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-400',
    failed: 'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-400',
    expired: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
};
const paymentStatusColor = computed(() => statusColors[paymentStatusKey.value] ?? statusColors.paid);

const isNonCash = computed(() => paymentMethodKey.value !== 'cash');
const showPaymentLink = computed(() => isNonCash.value && props.transaction.payment_url);

const handlePrint = () => {
    window.print();
};
</script>


