<template>
    <button
        @click="hasStock && $emit('addToCart', product)"
        :disabled="!hasStock || isAdding"
        :class="[
            'group relative flex flex-col bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transition-all duration-200',
            hasStock
                ? 'hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-lg hover:-translate-y-0.5 active:scale-[0.98] cursor-pointer'
                : 'opacity-60 cursor-not-allowed',
        ]"
    >
        <div class="relative aspect-square bg-slate-100 dark:bg-slate-800 overflow-hidden">
            <img
                v-if="product.image"
                :src="getProductImageUrl(product.image)"
                :alt="product.title"
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
                <IconPhoto :size="32" class="text-slate-300 dark:text-slate-600" />
            </div>

            <span
                v-if="lowStock"
                class="absolute top-2 right-2 px-2 py-0.5 text-xs font-medium bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-400 rounded-full"
            >
                Sisa {{ product.stock }}
            </span>

            <span
                v-if="product.type === 'bundle'"
                class="absolute top-2 left-2 px-2 py-0.5 text-xs font-bold bg-primary-500 text-white rounded-full uppercase shadow-sm"
            >
                Bundle
            </span>

            <div
                v-if="!hasStock"
                class="absolute inset-0 bg-slate-900/60 flex items-center justify-center"
            >
                <span class="px-3 py-1 bg-danger-500 text-white text-xs font-semibold rounded-full">
                    Habis
                </span>
            </div>
        </div>

        <div class="flex-1 p-3 flex flex-col justify-between min-h-[80px]">
            <h3 class="text-sm font-medium text-slate-800 dark:text-slate-200 line-clamp-2 leading-tight">
                {{ product.title }}
            </h3>
            <p class="mt-2 text-base font-bold text-primary-600 dark:text-primary-400">
                {{ formatPrice(product.sell_price) }}
            </p>
        </div>

        <div
            v-if="hasStock"
            class="absolute inset-0 bg-primary-500/10 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none flex items-center justify-center"
        >
            <div
                class="bg-primary-500 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-lg transform translate-y-2 group-hover:translate-y-0 transition-transform"
            >
                + Tambah
            </div>
        </div>
    </button>
</template>

<script setup>
import { computed } from 'vue';
import { IconPhoto } from '@tabler/icons-vue';
import { getProductImageUrl } from '@/Utils/imageUrl';

const props = defineProps({
    product: Object,
    isAdding: Boolean,
});

defineEmits(['addToCart']);

const formatPrice = (value = 0) =>
    value.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    });

const hasStock = computed(() => props.product.stock > 0);
const lowStock = computed(() => props.product.stock > 0 && props.product.stock <= 5);
</script>

