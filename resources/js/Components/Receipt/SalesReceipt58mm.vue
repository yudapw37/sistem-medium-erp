<template>
    <div
        class="sales-receipt-58 font-mono text-[9px] leading-tight"
        style="width: 58mm; padding: 2mm"
    >
        <div class="text-center mb-1">
            <p class="font-bold text-[10px]">{{ storeName }}</p>
            <p v-if="storePhone">{{ storePhone }}</p>
        </div>

        <pre>{{ line }}</pre>
        <p>#{{ sale?.invoice }}</p>
        <p>{{ formatDate(sale?.created_at) }}</p>
        <pre>{{ line }}</pre>

        <div v-for="(item, i) in items" :key="i" class="mb-1">
            <p class="truncate">{{ item.bundle ? '[B] ' + item.bundle.name : item.product?.title }}</p>
            <div class="flex justify-between">
                <span>{{ item.qty }}x @ {{ formatPrice(item.sell_price) }}</span>
                <span>{{ formatPrice((item.sell_price * item.qty) - (item.discount || 0)) }}</span>
            </div>
            <div v-if="item.discount > 0" class="flex justify-between text-[8px]">
                <span>Disc:</span>
                <span>-{{ formatPrice(item.discount) }}</span>
            </div>
        </div>

        <pre>{{ line }}</pre>
        <div class="flex justify-between">
            <span>Subtotal</span>
            <span>{{ formatPrice(subtotal) }}</span>
        </div>
        <div v-if="sale?.discount > 0" class="flex justify-between">
            <span>Diskon</span>
            <span>-{{ formatPrice(sale?.discount) }}</span>
        </div>
        <div v-if="sale?.event_discount > 0" class="flex justify-between">
            <span>Disc Event</span>
            <span>-{{ formatPrice(sale?.event_discount) }}</span>
        </div>
        <div v-if="sale?.shipping_cost > 0" class="flex justify-between">
            <span>Ongkir</span>
            <span>{{ formatPrice(sale?.shipping_cost) }}</span>
        </div>
        <div v-if="sale?.other_cost > 0" class="flex justify-between">
            <span>Lainnya</span>
            <span>{{ formatPrice(sale?.other_cost) }}</span>
        </div>
        <div class="flex justify-between font-bold">
            <span>TOTAL</span>
            <span>{{ formatPrice(sale?.grand_total) }}</span>
        </div>
        <div v-if="sale?.shipping_type !== 'pickup'" class="mt-1 border-t border-dashed">
            <p>S: {{ sale?.sender_name || '-' }} / {{ sale?.sender_phone || '-' }}</p>
            <p>R: {{ sale?.shipping_name || '-' }} / {{ sale?.shipping_phone || '-' }}</p>
        </div>
        <pre>{{ line }}</pre>
        <p class="text-center">Terima kasih!</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    sale: Object,
    storeName: { type: String, default: 'TOKO' },
    storePhone: { type: String, default: '' },
});

const formatPrice = (price = 0) => 'Rp' + Number(price || 0).toLocaleString('id-ID');
const formatDate = (value) => new Date(value).toLocaleString('id-ID', {
    day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit',
});

const items = computed(() => props.sale?.details ?? []);
const subtotal = computed(() => items.value.reduce((t, i) => t + (i.sell_price * i.qty) - (i.discount || 0), 0));
const line = '-'.repeat(24);
</script>

<style scoped>
@media print {
    .sales-receipt-58 { width: 58mm !important; font-size: 9pt !important; }
    @page { size: 58mm auto; margin: 0; }
}
</style>
