<template>
    <div
        class="thermal-receipt font-mono text-xs leading-tight"
        style="width: 80mm; padding: 4mm"
    >
        <div class="text-center mb-2">
            <p class="text-sm font-bold">{{ storeName }}</p>
            <p v-if="storeAddress" class="text-xs">{{ storeAddress }}</p>
            <p v-if="storePhone" class="text-xs">Telp: {{ storePhone }}</p>
        </div>

        <pre class="whitespace-pre-wrap">{{ line }}</pre>

        <div class="my-1">
            <div class="flex justify-between">
                <span>No:</span>
                <span>{{ transaction?.invoice }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tgl:</span>
                <span>{{ formatDate(transaction?.created_at) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span>{{ transaction?.cashier?.name || '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span>{{ transaction?.customer?.name || 'Umum' }}</span>
            </div>
        </div>

        <pre class="whitespace-pre-wrap">{{ line }}</pre>

        <div class="my-1">
            <div v-for="(item, index) in items" :key="item.id || index" class="mb-1">
                <p class="font-medium truncate">{{ item.bundle ? '[B] ' + item.bundle.name : item.product?.title }}</p>
                <div v-if="item.bundle?.items" class="pl-2">
                    <p v-for="bundleItem in item.bundle.items" :key="bundleItem.id" class="text-[10px] opacity-75">
                      - {{ bundleItem.product?.title }} ({{ bundleItem.qty }} pcs)
                    </p>
                </div>
                <div class="flex justify-between">
                    <span>{{ item.qty }}x @ {{ formatPrice(item.price) }}</span>
                    <span>{{ formatPrice(item.price * item.qty) }}</span>
                </div>
            </div>
        </div>

        <pre class="whitespace-pre-wrap">{{ dashLine }}</pre>

        <div class="my-1">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>{{ formatPrice(subtotal) }}</span>
            </div>
            <div v-if="discount > 0" class="flex justify-between">
                <span>Diskon</span>
                <span>-{{ formatPrice(discount) }}</span>
            </div>
            <div class="flex justify-between font-bold text-sm">
                <span>TOTAL</span>
                <span>{{ formatPrice(total) }}</span>
            </div>
        </div>

        <pre class="whitespace-pre-wrap">{{ dashLine }}</pre>

        <div class="my-1">
            <div class="flex justify-between">
                <span>Bayar ({{ paymentMethod }})</span>
                <span>{{ formatPrice(cash) }}</span>
            </div>
            <div v-if="change > 0" class="flex justify-between font-bold">
                <span>Kembali</span>
                <span>{{ formatPrice(change) }}</span>
            </div>
        </div>

        <pre class="whitespace-pre-wrap">{{ line }}</pre>

        <div class="text-center mt-2">
            <p class="text-xs">Terima kasih</p>
            <p class="text-xs">Barang yang sudah dibeli</p>
            <p class="text-xs">tidak dapat ditukar/dikembalikan</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    transaction: Object,
    storeName: {
        type: String,
        default: 'TOKO ANDA',
    },
    storeAddress: {
        type: String,
        default: '',
    },
    storePhone: {
        type: String,
        default: '',
    },
});

const formatPrice = (price = 0) => {
    return 'Rp ' + Number(price || 0).toLocaleString('id-ID');
};

const formatDate = (value) => {
    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const items = computed(() => props.transaction?.details ?? []);

const subtotal = computed(
    () => props.transaction?.grand_total + (props.transaction?.discount || 0)
);
const discount = computed(() => props.transaction?.discount || 0);
const total = computed(() => props.transaction?.grand_total || 0);
const cash = computed(() => props.transaction?.cash || 0);
const change = computed(() => props.transaction?.change || 0);

const paymentLabels = {
    cash: 'TUNAI',
    midtrans: 'MIDTRANS',
    xendit: 'XENDIT',
};
const paymentMethod = computed(
    () => paymentLabels[props.transaction?.payment_method?.toLowerCase()] || 'TUNAI'
);

const line = '='.repeat(32);
const dashLine = '-'.repeat(32);
</script>

<style scoped>
@media print {
    .thermal-receipt {
        width: 80mm !important;
        margin: 0 !important;
        padding: 2mm !important;
        font-size: 10pt !important;
    }
    @page {
        size: 80mm auto;
        margin: 0;
    }
}
</style>

