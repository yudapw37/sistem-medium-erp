<template>
    <div
        ref="receiptRef"
        class="sales-receipt font-mono text-xs leading-tight"
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
                <span>{{ sale?.invoice }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tgl:</span>
                <span>{{ formatDate(sale?.created_at) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Petugas:</span>
                <span>{{ sale?.user?.name || '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span>{{ sale?.customer?.name || 'Umum' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Gudang:</span>
                <span>{{ sale?.warehouse?.name || '-' }}</span>
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
                    <span>{{ item.qty }}x @ {{ formatPrice(item.sell_price) }}</span>
                    <span>{{ formatPrice(item.sell_price * item.qty) }}</span>
                </div>
                <div v-if="item.discount > 0" class="flex justify-between text-[10px]">
                    <span>Diskon:</span>
                    <span>-{{ formatPrice(item.discount) }}</span>
                </div>
            </div>
        </div>

        <pre class="whitespace-pre-wrap">{{ dashLine }}</pre>

        <div class="my-1">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>{{ formatPrice(subtotal) }}</span>
            </div>
            <div v-if="totalItemDiscount > 0" class="flex justify-between">
                <span>Diskon Item</span>
                <span>-{{ formatPrice(totalItemDiscount) }}</span>
            </div>
            <div v-if="sale?.discount > 0" class="flex justify-between">
                <span>Diskon Transaksi</span>
                <span>-{{ formatPrice(sale?.discount) }}</span>
            </div>
            <div v-if="sale?.event_discount > 0" class="flex justify-between">
                <span>Diskon Event</span>
                <span>-{{ formatPrice(sale?.event_discount) }}</span>
            </div>
            <div v-if="sale?.shipping_cost > 0" class="flex justify-between">
                <span>Ongkir</span>
                <span>{{ formatPrice(sale?.shipping_cost) }}</span>
            </div>
            <div v-if="sale?.other_cost > 0" class="flex justify-between">
                <span>Biaya Lain</span>
                <span>{{ formatPrice(sale?.other_cost) }}</span>
            </div>
            <div class="flex justify-between font-bold text-sm">
                <span>TOTAL</span>
                <span>{{ formatPrice(sale?.grand_total) }}</span>
            </div>
        </div>

        <pre class="whitespace-pre-wrap">{{ dashLine }}</pre>

        <div class="my-1">
            <div class="flex justify-between">
                <span>Tipe Bayar:</span>
                <span class="uppercase">{{ sale?.payment_type }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pengiriman:</span>
                <span class="uppercase">{{ shippingLabel }}</span>
            </div>
        </div>

        <div v-if="sale?.shipping_type !== 'pickup'" class="my-1">
            <pre class="whitespace-pre-wrap">{{ dashLine }}</pre>
            <div class="mb-2">
                <p class="font-bold underline mb-1">PENGIRIM:</p>
                <p>{{ sale?.sender_name || '-' }}</p>
                <p>{{ sale?.sender_phone || '-' }}</p>
            </div>
            <div>
                <p class="font-bold underline mb-1">PENERIMA:</p>
                <p>{{ sale?.shipping_name || '-' }}</p>
                <p>{{ sale?.shipping_phone || '-' }}</p>
                <p class="whitespace-pre-wrap">{{ sale?.shipping_address || '-' }}</p>
            </div>
        </div>

        <pre class="whitespace-pre-wrap">{{ line }}</pre>

        <div class="text-center mt-2">
            <p class="text-xs">Terima kasih</p>
            <p class="text-xs">atas kepercayaan Anda</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    sale: Object,
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

const items = computed(() => props.sale?.details ?? []);

const subtotal = computed(() => {
    return items.value.reduce((total, item) => total + (item.sell_price * item.qty), 0);
});

const totalItemDiscount = computed(() => {
    return items.value.reduce((total, item) => total + (item.discount || 0), 0);
});

const shippingLabels = {
    pickup: 'Ambil di Gudang',
    cod: 'COD',
    courier: 'Jasa Kirim',
};
const shippingLabel = computed(() => shippingLabels[props.sale?.shipping_type] || '-');

const line = '='.repeat(32);
const dashLine = '-'.repeat(32);
</script>

<style scoped>
@media print {
    .sales-receipt {
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
