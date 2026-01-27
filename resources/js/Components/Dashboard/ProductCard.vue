<template>
    <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:shadow-lg hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200">
        <div class="relative aspect-square bg-slate-100 dark:bg-slate-800 overflow-hidden">
            <img
                v-if="product.image"
                :src="getProductImageUrl(product.image)"
                :alt="product.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
                <IconPhoto :size="48" class="text-slate-300 dark:text-slate-600" :stroke-width="1" />
            </div>

            <!-- Stock Badge -->
            <div class="absolute top-2 right-2">
                <span
                    v-if="outOfStock"
                    class="px-2 py-1 text-xs font-semibold bg-danger-500 text-white rounded-full"
                >
                    Habis
                </span>
                <span
                    v-else-if="lowStock"
                    class="px-2 py-1 text-xs font-semibold bg-warning-500 text-white rounded-full"
                >
                    Stok: {{ product.stock }}
                </span>
                <span v-else class="px-2 py-1 text-xs font-medium bg-slate-900/60 text-white rounded-full">
                    Stok: {{ product.stock }}
                </span>
            </div>

            <!-- Action Buttons Overlay -->
            <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/40 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                <Link
                    :href="route('products.edit', product.id)"
                    class="p-2.5 rounded-xl bg-white text-warning-600 hover:bg-warning-50 shadow-lg transition-colors"
                >
                    <IconPencilCog :size="18" />
                </Link>
                <Button
                    type="delete"
                    :icon="IconTrash"
                    class="p-2.5 rounded-xl bg-white text-danger-600 hover:bg-danger-50 shadow-lg"
                    :url="route('products.destroy', product.id)"
                />
            </div>
        </div>

        <div class="p-4">
            <div class="flex items-start justify-between gap-2 mb-2">
                <span class="px-2 py-0.5 text-xs font-medium bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-400 rounded-md">
                    {{ product.category?.name || 'Kategori' }}
                </span>
            </div>
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 line-clamp-2 mb-1">
                {{ product.title }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 mb-3">
                {{ product.barcode }}
            </p>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Harga Beli</p>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">
                        {{ formatCurrency(product.buy_price) }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400 dark:text-slate-500">Harga Jual</p>
                    <p class="text-base font-bold text-primary-600 dark:text-primary-400">
                        {{ formatCurrency(product.sell_price) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { IconPhoto, IconPencilCog, IconTrash } from '@tabler/icons-vue';
import { getProductImageUrl } from '@/Utils/imageUrl';
import Button from './Button.vue';

const props = defineProps({
    product: Object,
});

const formatCurrency = (value = 0) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);

const lowStock = computed(() => props.product.stock > 0 && props.product.stock <= 5);
const outOfStock = computed(() => props.product.stock === 0);
</script>

