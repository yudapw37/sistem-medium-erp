<template>
    <div class="h-full flex flex-col">
        <!-- Search Bar -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="w-full sm:w-40 flex-shrink-0">
                    <select
                        :value="saleType"
                        @change="(e) => $emit('update:saleType', e.target.value)"
                        class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-medium"
                    >
                        <option value="all">Semua</option>
                        <option value="product">Eceran</option>
                        <option value="bundle">Bundling</option>
                    </select>
                </div>
                <div class="relative flex-1">
                    <input
                        ref="searchInputRef"
                        type="text"
                        :value="searchQuery"
                        @input="(e) => $emit('update:searchQuery', e.target.value)"
                        @keydown="(e) => e.key === 'Enter' && $emit('search')"
                        :placeholder="'Cari ' + (saleType === 'bundle' ? 'bundling' : 'produk') + '...'"
                        class="w-full h-12 pl-4 pr-12 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 dark:focus:border-primary-500 transition-all text-base"
                        :disabled="isSearching"
                    />
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <div
                            v-if="isSearching"
                            class="w-5 h-5 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"
                        />
                        <IconShoppingBag v-else :size="20" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Tabs -->
        <div
            v-if="saleType !== 'bundle'"
            class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 overflow-x-auto scrollbar-hide"
        >
            <div class="flex gap-2">
                <button
                    @click="$emit('update:selectedCategory', null)"
                    :class="[
                        'px-4 py-2.5 rounded-xl text-sm font-medium whitespace-nowrap transition-all duration-200 min-h-touch',
                        !selectedCategory
                            ? 'bg-primary-500 text-white shadow-md shadow-primary-500/30'
                            : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700',
                    ]"
                >
                    Semua
                </button>
                <button
                    v-for="category in categories"
                    :key="category.id"
                    @click="$emit('update:selectedCategory', category.id)"
                    :class="[
                        'px-4 py-2.5 rounded-xl text-sm font-medium whitespace-nowrap transition-all duration-200 min-h-touch',
                        selectedCategory === category.id
                            ? 'bg-primary-500 text-white shadow-md shadow-primary-500/30'
                            : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700',
                    ]"
                >
                    {{ category.name }}
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="flex-1 overflow-y-auto p-4 scrollbar-thin">
            <div
                v-if="filteredProducts.length > 0"
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3"
            >
                <ProductCard
                    v-for="product in filteredProducts"
                    :key="product.id"
                    :product="product"
                    :is-adding="addingProductId === product.id"
                    @add-to-cart="$emit('addToCart', product)"
                />
            </div>
            <div
                v-else
                class="h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-600"
            >
                <IconShoppingBag :size="48" :stroke-width="1.5" class="mb-3" />
                <p class="text-sm">
                    {{ searchQuery ? 'Produk tidak ditemukan' : 'Tidak ada produk' }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, watch, nextTick } from 'vue';
import { IconShoppingBag } from '@tabler/icons-vue';
import ProductCard from './ProductCard.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    selectedCategory: [Number, String, null],
    searchQuery: {
        type: String,
        default: '',
    },
    isSearching: {
        type: Boolean,
        default: false,
    },
    addingProductId: [Number, String, null],
    searchInputRef: Object,
    saleType: {
        type: String,
        default: 'all',
    },
});

const emit = defineEmits([
    'update:selectedCategory',
    'update:searchQuery',
    'update:saleType',
    'search',
    'addToCart',
]);

const filteredProducts = computed(() => {
    return props.products.filter((product) => {
        const matchesCategory =
            !props.selectedCategory || product.category_id === props.selectedCategory;
        const matchesSearch =
            !props.searchQuery ||
            product.title.toLowerCase().includes(props.searchQuery.toLowerCase()) ||
            product.barcode?.toLowerCase().includes(props.searchQuery.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});
</script>

