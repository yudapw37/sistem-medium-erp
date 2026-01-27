<template>
    <div class="relative">
        <div class="relative">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                <div
                    v-if="isSearching"
                    class="w-5 h-5 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"
                />
                <IconSearch v-else :size="20" class="text-slate-400" />
            </div>

            <input
                ref="inputRef"
                type="text"
                :value="value"
                @input="(e) => $emit('update:value', e.target.value)"
                @focus="isFocused = true"
                @blur="() => setTimeout(() => (isFocused = false), 200)"
                @keydown="handleKeyDown"
                :placeholder="placeholder"
                :autofocus="autoFocus"
                class="w-full h-14 pl-12 pr-24 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-lg placeholder-slate-400 dark:placeholder-slate-500 focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500 dark:focus:border-primary-500 transition-all"
            />

            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
                <button
                    v-if="value"
                    type="button"
                    @click="clearInput"
                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                >
                    <IconX :size="18" class="text-slate-400" />
                </button>
                <div class="w-px h-6 bg-slate-200 dark:bg-slate-700" />
                <div class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800">
                    <IconBarcode :size="18" class="text-slate-500 dark:text-slate-400" />
                </div>
            </div>
        </div>

        <div
            v-if="showSuggestions"
            class="absolute top-full left-0 right-0 mt-2 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xl max-h-80 overflow-y-auto z-50 animate-slide-up"
        >
            <ul ref="listRef">
                <li v-for="(product, index) in suggestions" :key="product.id">
                    <button
                        type="button"
                        @click="selectProduct(product)"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 text-left transition-colors',
                            index === selectedIndex
                                ? 'bg-primary-50 dark:bg-primary-950/30'
                                : 'hover:bg-slate-50 dark:hover:bg-slate-800',
                        ]"
                    >
                        <div
                            class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden flex-shrink-0"
                        >
                            <img
                                v-if="product.image"
                                :src="getProductImageUrl(product.image)"
                                :alt="product.title"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <IconBarcode :size="20" class="text-slate-400" />
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">
                                {{ product.title }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ product.barcode }} • Stok: {{ product.stock }}
                            </p>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">
                                {{ formatPrice(product.sell_price) }}
                            </p>
                            <span v-if="product.stock <= 0" class="text-xs text-danger-500 font-medium">
                                Habis
                            </span>
                        </div>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { IconSearch, IconX, IconBarcode } from '@tabler/icons-vue';
import { getProductImageUrl } from '@/Utils/imageUrl';

const props = defineProps({
    value: {
        type: String,
        default: '',
    },
    suggestions: {
        type: Array,
        default: () => [],
    },
    isSearching: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: 'Cari produk atau scan barcode...',
    },
    autoFocus: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:value', 'search', 'select']);

const isFocused = ref(false);
const selectedIndex = ref(-1);
const inputRef = ref(null);
const listRef = ref(null);

const showSuggestions = computed(
    () => isFocused.value && props.suggestions.length > 0 && props.value.length > 0
);

watch(
    () => props.suggestions,
    () => {
        selectedIndex.value = -1;
    }
);

watch(selectedIndex, async () => {
    if (listRef.value && selectedIndex.value >= 0) {
        await nextTick();
        const selectedItem = listRef.value.children[selectedIndex.value];
        if (selectedItem) {
            selectedItem.scrollIntoView({ block: 'nearest' });
        }
    }
});

const formatPrice = (value = 0) =>
    value.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    });

const clearInput = () => {
    emit('update:value', '');
    inputRef.value?.focus();
};

const selectProduct = (product) => {
    emit('select', product);
    isFocused.value = false;
    inputRef.value?.blur();
};

const handleKeyDown = (e) => {
    if (!showSuggestions.value) {
        if (e.key === 'Enter') {
            emit('search');
        }
        return;
    }

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            selectedIndex.value =
                selectedIndex.value < props.suggestions.length - 1
                    ? selectedIndex.value + 1
                    : selectedIndex.value;
            break;
        case 'ArrowUp':
            e.preventDefault();
            selectedIndex.value = selectedIndex.value > 0 ? selectedIndex.value - 1 : -1;
            break;
        case 'Enter':
            e.preventDefault();
            if (selectedIndex.value >= 0 && props.suggestions[selectedIndex.value]) {
                selectProduct(props.suggestions[selectedIndex.value]);
            } else {
                emit('search');
            }
            break;
        case 'Escape':
            isFocused.value = false;
            inputRef.value?.blur();
            break;
    }
};
</script>

