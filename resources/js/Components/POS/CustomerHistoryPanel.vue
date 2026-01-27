<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
        <div
            class="bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-3 flex items-center justify-between"
        >
            <div class="flex items-center gap-2 text-white">
                <IconHistory :size="18" />
                <span class="font-semibold text-sm">Riwayat Pelanggan</span>
            </div>
            <button v-if="onClose" @click="onClose" class="text-white/80 hover:text-white">
                <IconX :size="18" />
            </button>
        </div>

        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
            <p class="text-base font-semibold text-slate-900 dark:text-white">{{ customerName }}</p>
        </div>

        <div v-if="loading" class="p-6 flex items-center justify-center">
            <IconLoader2 :size="24" class="animate-spin text-primary-500" />
        </div>

        <template v-else-if="!error && data">
            <div class="grid grid-cols-3 gap-px bg-slate-100 dark:bg-slate-800">
                <div class="bg-white dark:bg-slate-900 p-3 text-center">
                    <div class="flex items-center justify-center mb-1">
                        <IconReceipt :size="16" class="text-primary-500" />
                    </div>
                    <p class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ data.stats.total_transactions }}
                    </p>
                    <p class="text-xs text-slate-500">Transaksi</p>
                </div>
                <div class="bg-white dark:bg-slate-900 p-3 text-center">
                    <div class="flex items-center justify-center mb-1">
                        <IconCoin :size="16" class="text-success-500" />
                    </div>
                    <p class="text-sm font-bold text-success-600 dark:text-success-400">
                        {{ formatPrice(data.stats.total_spent) }}
                    </p>
                    <p class="text-xs text-slate-500">Total Belanja</p>
                </div>
                <div class="bg-white dark:bg-slate-900 p-3 text-center">
                    <div class="flex items-center justify-center mb-1">
                        <IconCalendar :size="16" class="text-slate-500" />
                    </div>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ data.stats.last_visit || '-' }}
                    </p>
                    <p class="text-xs text-slate-500">Kunjungan Terakhir</p>
                </div>
            </div>

            <div
                v-if="data.frequent_products && data.frequent_products.length > 0"
                class="px-4 py-3 border-t border-slate-100 dark:border-slate-800"
            >
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2 flex items-center gap-1">
                    <IconShoppingBag :size="12" />
                    Produk Favorit
                </p>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="product in data.frequent_products"
                        :key="product.id"
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-primary-50 dark:bg-primary-950/50 text-xs font-medium text-primary-700 dark:text-primary-300"
                    >
                        {{ product.title }}
                        <span class="text-primary-500">×{{ product.total_qty }}</span>
                    </span>
                </div>
            </div>

            <div
                v-if="data.recent_transactions && data.recent_transactions.length > 0"
                class="px-4 py-3 border-t border-slate-100 dark:border-slate-800"
            >
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">
                    Transaksi Terakhir
                </p>
                <div class="space-y-2 max-h-[150px] overflow-y-auto">
                    <div
                        v-for="tx in data.recent_transactions"
                        :key="tx.id"
                        class="flex items-center justify-between py-1.5 border-b border-slate-50 dark:border-slate-800/50 last:border-0"
                    >
                        <div>
                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                {{ tx.invoice }}
                            </p>
                            <p class="text-xs text-slate-400">{{ tx.date }}</p>
                        </div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ formatPrice(tx.total) }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="data.stats.total_transactions === 0"
                class="px-4 py-6 text-center"
            >
                <IconHistory :size="32" class="mx-auto text-slate-300 mb-2" />
                <p class="text-sm text-slate-500">Belum ada transaksi</p>
            </div>
        </template>

        <div v-if="error" class="p-6 text-center">
            <p class="text-sm text-danger-500">{{ error }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import {
    IconHistory,
    IconCoin,
    IconCalendar,
    IconShoppingBag,
    IconX,
    IconLoader2,
    IconReceipt,
} from '@tabler/icons-vue';

const props = defineProps({
    customerId: [Number, String],
    customerName: String,
    onClose: Function,
});

const loading = ref(true);
const data = ref(null);
const error = ref(null);

const formatPrice = (value = 0) =>
    value.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    });

watch(
    () => props.customerId,
    async (newId) => {
        if (!newId) return;

        loading.value = true;
        error.value = null;

        try {
            const response = await fetch(route('customers.history', newId), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                data.value = result;
            } else {
                error.value = result.message || 'Gagal memuat data';
            }
        } catch (err) {
            console.error('Customer history error:', err);
            error.value = 'Gagal memuat data pelanggan';
        } finally {
            loading.value = false;
        }
    },
    { immediate: true }
);
</script>

