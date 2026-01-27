<template>
    <div
        class="thermal-receipt-58 font-mono text-xs"
        style="width: 58mm; padding: 2mm"
    >
        <div class="text-center">
            <p class="font-bold">{{ storeName }}</p>
            <p v-if="storePhone">{{ storePhone }}</p>
        </div>

        <pre>{{ line }}</pre>
        <p>#{{ transaction?.invoice }}</p>
        <p>{{ formatTime(transaction?.created_at) }}</p>
        <pre>{{ line }}</pre>

        <div v-for="(item, i) in items" :key="i" class="mb-1">
            <p class="truncate">{{ item.bundle ? '[B] ' + item.bundle.name : item.product?.title }}</p>
            <div class="flex justify-between">
                <span>{{ item.qty }}x</span>
                <span>{{ formatPrice(item.price) }}</span>
            </div>
        </div>

        <pre>{{ line }}</pre>
        <div class="flex justify-between">
            <span>Subtotal</span>
            <span>{{ formatPrice((transaction?.grand_total || 0) + (transaction?.discount || 0)) }}</span>
        </div>
        <div v-if="(transaction?.discount || 0) > 0" class="flex justify-between">
            <span>Diskon</span>
            <span>-{{ formatPrice(transaction?.discount || 0) }}</span>
        </div>
        <div class="flex justify-between font-bold">
            <span>TOTAL</span>
            <span>{{ formatPrice(transaction?.grand_total) }}</span>
        </div>
        <div class="flex justify-between">
            <span>Bayar</span>
            <span>{{ formatPrice(transaction?.cash) }}</span>
        </div>
        <div class="flex justify-between">
            <span>Kembali</span>
            <span>{{ formatPrice(transaction?.change) }}</span>
        </div>
        <pre>{{ line }}</pre>
        <p class="text-center">Terima kasih!</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    transaction: Object,
    storeName: {
        type: String,
        default: 'TOKO',
    },
    storePhone: {
        type: String,
        default: '',
    },
});

const formatPrice = (price = 0) => {
    return 'Rp' + Number(price || 0).toLocaleString('id-ID');
};

const formatTime = (value) => {
    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const items = computed(() => props.transaction?.details ?? []);
const line = '-'.repeat(24);
</script>

<style scoped>
@media print {
    .thermal-receipt-58 {
        width: 58mm !important;
        font-size: 9pt !important;
    }
    @page {
        size: 58mm auto;
        margin: 0;
    }
}
</style>

