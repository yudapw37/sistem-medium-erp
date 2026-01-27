<template>
    <div class="sales-invoice bg-white p-8" style="width: 210mm; min-height: 297mm;">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8 border-b-2 border-slate-800 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ storeName }}</h1>
                <p v-if="storeAddress" class="text-sm text-slate-600">{{ storeAddress }}</p>
                <p v-if="storePhone" class="text-sm text-slate-600">Telp: {{ storePhone }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-slate-800">INVOICE</h2>
                <p class="text-lg font-semibold text-primary-600">{{ sale?.invoice }}</p>
                <p class="text-sm text-slate-600">{{ formatDate(sale?.created_at) }}</p>
            </div>
        </div>

        <!-- Customer & Warehouse Info -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
                <h3 class="text-sm font-bold text-slate-500 uppercase mb-2">Kepada:</h3>
                <p class="font-semibold text-slate-800">{{ sale?.customer?.name || 'Umum' }}</p>
                <p v-if="sale?.customer?.phone" class="text-sm text-slate-600">{{ sale?.customer?.phone }}</p>
                <p v-if="sale?.customer?.address" class="text-sm text-slate-600">{{ sale?.customer?.address }}</p>
            </div>
            <div class="text-right">
                <h3 class="text-sm font-bold text-slate-500 uppercase mb-2">Dari Gudang:</h3>
                <p class="font-semibold text-slate-800">{{ sale?.warehouse?.name }}</p>
                <p class="text-sm text-slate-600">Petugas: {{ sale?.user?.name }}</p>
                <p class="text-sm text-slate-600">
                    Status: 
                    <span :class="sale?.status === 'finalized' ? 'text-green-600' : 'text-yellow-600'" class="font-semibold">
                        {{ sale?.status === 'finalized' ? 'Finalized' : 'Draft' }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full mb-6">
            <thead>
                <tr class="bg-slate-100">
                    <th class="py-3 px-4 text-left text-sm font-bold text-slate-700">No</th>
                    <th class="py-3 px-4 text-left text-sm font-bold text-slate-700">Produk</th>
                    <th class="py-3 px-4 text-right text-sm font-bold text-slate-700">Harga</th>
                    <th class="py-3 px-4 text-right text-sm font-bold text-slate-700">Diskon</th>
                    <th class="py-3 px-4 text-center text-sm font-bold text-slate-700">Qty</th>
                    <th class="py-3 px-4 text-right text-sm font-bold text-slate-700">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in items" :key="item.id" class="border-b border-slate-200">
                    <td class="py-3 px-4 text-sm text-slate-600">{{ index + 1 }}</td>
                    <td class="py-3 px-4">
                        <p class="font-medium text-slate-800">
                            <span v-if="item.bundle" class="text-purple-600 font-bold">[B] </span>
                            {{ item.bundle ? item.bundle.name : item.product?.title }}
                        </p>
                        <p class="text-xs text-slate-500">{{ item.bundle ? item.bundle.code : item.product?.barcode }}</p>
                    </td>
                    <td class="py-3 px-4 text-right text-sm text-slate-600">{{ formatCurrency(item.sell_price) }}</td>
                    <td class="py-3 px-4 text-right text-sm" :class="item.discount > 0 ? 'text-red-500' : 'text-slate-400'">
                        {{ item.discount > 0 ? '-' + formatCurrency(item.discount) : '-' }}
                    </td>
                    <td class="py-3 px-4 text-center text-sm text-slate-800 font-medium">{{ item.qty }}</td>
                    <td class="py-3 px-4 text-right text-sm font-semibold text-slate-800">
                        {{ formatCurrency((item.sell_price * item.qty) - (item.discount || 0)) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end mb-8">
            <div class="w-80">
                <div class="flex justify-between py-2 border-b border-slate-200">
                    <span class="text-slate-600">Subtotal</span>
                    <span class="font-medium">{{ formatCurrency(subtotal) }}</span>
                </div>
                <div v-if="sale?.discount > 0" class="flex justify-between py-2 border-b border-slate-200">
                    <span class="text-slate-600">Diskon Transaksi</span>
                    <span class="text-red-500">-{{ formatCurrency(sale?.discount) }}</span>
                </div>
                <div v-if="sale?.event_discount > 0" class="flex justify-between py-2 border-b border-slate-200">
                    <span class="text-slate-600">Diskon Event</span>
                    <span class="text-purple-500">-{{ formatCurrency(sale?.event_discount) }}</span>
                </div>
                <div v-if="sale?.shipping_cost > 0" class="flex justify-between py-2 border-b border-slate-200">
                    <span class="text-slate-600">Ongkos Kirim</span>
                    <span>{{ formatCurrency(sale?.shipping_cost) }}</span>
                </div>
                <div v-if="sale?.other_cost > 0" class="flex justify-between py-2 border-b border-slate-200">
                    <span class="text-slate-600">Biaya Lainnya</span>
                    <span>{{ formatCurrency(sale?.other_cost) }}</span>
                </div>
                <div class="flex justify-between py-3 bg-slate-800 text-white px-4 rounded-lg mt-2">
                    <span class="font-bold">TOTAL</span>
                    <span class="font-bold text-lg">{{ formatCurrency(sale?.grand_total) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment & Shipping Info -->
        <div class="grid grid-cols-2 gap-8 mb-8 p-4 bg-slate-50 rounded-lg">
            <div>
                <h3 class="text-sm font-bold text-slate-500 uppercase mb-2">Pembayaran</h3>
                <p class="font-semibold text-slate-800 uppercase">{{ sale?.payment_type }}</p>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-500 uppercase mb-2">Pengiriman</h3>
                <p class="font-semibold text-slate-800">{{ shippingLabel }}</p>
            </div>
        </div>

        <!-- Shipping Address -->
        <div v-if="sale?.shipping_type !== 'pickup'" class="mb-8 p-6 border-2 border-slate-200 rounded-xl">
            <h3 class="text-sm font-bold text-slate-500 uppercase mb-4">Informasi Pengiriman</h3>
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase mb-1">Pengirim:</p>
                    <p class="font-bold text-slate-800">{{ sale?.sender_name || '-' }}</p>
                    <p class="text-sm text-slate-600">{{ sale?.sender_phone || '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase mb-1">Penerima:</p>
                    <p class="font-bold text-slate-800">{{ sale?.shipping_name || '-' }}</p>
                    <p class="text-sm text-slate-600">{{ sale?.shipping_phone || '-' }}</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Alamat Pengiriman:</p>
                <p class="text-sm text-slate-600 leading-relaxed">{{ sale?.shipping_address || '-' }}</p>
            </div>
        </div>

        <!-- Notes -->
        <div v-if="sale?.notes" class="mb-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="text-sm font-bold text-slate-500 uppercase mb-2">Catatan</h3>
            <p class="text-sm text-slate-700">{{ sale?.notes }}</p>
        </div>

        <!-- Footer -->
        <div class="text-center text-sm text-slate-500 mt-8 pt-4 border-t border-slate-200">
            <p>Terima kasih atas kepercayaan Anda</p>
            <p class="text-xs mt-1">Dokumen ini dicetak secara otomatis oleh sistem</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    sale: Object,
    storeName: { type: String, default: 'TOKO ANDA' },
    storeAddress: { type: String, default: '' },
    storePhone: { type: String, default: '' },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const formatDate = (value) => {
    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const items = computed(() => props.sale?.details ?? []);
const subtotal = computed(() => items.value.reduce((t, i) => t + (i.sell_price * i.qty) - (i.discount || 0), 0));

const shippingLabels = { pickup: 'Ambil di Gudang', cod: 'COD', courier: 'Jasa Kirim' };
const shippingLabel = computed(() => shippingLabels[props.sale?.shipping_type] || '-');
</script>

<style scoped>
@media print {
    .sales-invoice { width: 210mm !important; min-height: 297mm !important; }
    @page { size: A4; margin: 10mm; }
}
</style>
