<template>
    <Teleport to="body">
        <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="onClose"
            />
            <div
                class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden animate-slide-up"
            >
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">{{ title }}</h3>
                    <button
                        @click="onClose"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    >
                        <IconX :size="20" />
                    </button>
                </div>

                <div class="px-5 py-6 bg-slate-50 dark:bg-slate-800/50">
                    <div class="text-right">
                        <p class="text-3xl font-bold text-slate-900 dark:text-white font-mono">
                            {{ formatDisplay(value) }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="isCurrency"
                    class="grid grid-cols-4 gap-2 px-5 py-3 border-b border-slate-100 dark:border-slate-800"
                >
                    <button
                        v-for="amount in [10000, 20000, 50000, 100000]"
                        :key="amount"
                        @click="handleQuickAmount(amount)"
                        class="py-2 px-2 text-xs font-medium rounded-xl bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors"
                    >
                        +{{ amount / 1000 }}rb
                    </button>
                </div>

                <div class="p-5 grid grid-cols-3 gap-3">
                    <button
                        v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
                        :key="num"
                        @click="handleDigit(String(num))"
                        class="h-14 text-2xl font-semibold rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all"
                    >
                        {{ num }}
                    </button>

                    <button
                        @click="handleClear"
                        class="h-14 text-sm font-semibold rounded-2xl bg-warning-100 dark:bg-warning-900/50 text-warning-700 dark:text-warning-400 hover:bg-warning-200 dark:hover:bg-warning-900 active:scale-95 transition-all"
                    >
                        C
                    </button>

                    <button
                        @click="handleDigit('0')"
                        class="h-14 text-2xl font-semibold rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all"
                    >
                        0
                    </button>

                    <button
                        @click="handleBackspace"
                        class="h-14 flex items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all"
                    >
                        <IconBackspace :size="24" />
                    </button>
                </div>

                <div class="p-5 pt-0">
                    <button
                        @click="handleConfirm"
                        :disabled="!isValid"
                        :class="[
                            'w-full h-14 flex items-center justify-center gap-2 text-lg font-semibold rounded-2xl transition-all',
                            isValid
                                ? 'bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/30 hover:shadow-xl active:scale-[0.98]'
                                : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed',
                        ]"
                    >
                        <IconCheck :size="22" />
                        Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { IconBackspace, IconX, IconCheck } from '@tabler/icons-vue';

const props = defineProps({
    isOpen: Boolean,
    onClose: Function,
    onConfirm: Function,
    title: {
        type: String,
        default: 'Masukkan Angka',
    },
    initialValue: {
        type: Number,
        default: 0,
    },
    minValue: {
        type: Number,
        default: 0,
    },
    maxValue: {
        type: Number,
        default: 999999999,
    },
    isCurrency: {
        type: Boolean,
        default: false,
    },
});

const value = ref(String(props.initialValue || ''));

watch(
    () => props.isOpen,
    (newVal) => {
        if (newVal) {
            value.value = String(props.initialValue || '');
        }
    }
);

const numValue = computed(() => parseInt(value.value, 10) || 0);
const isValid = computed(() => numValue.value >= props.minValue && numValue.value <= props.maxValue);

const formatDisplay = (val) => {
    const num = parseInt(val, 10) || 0;
    if (props.isCurrency) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(num);
    }
    return num.toLocaleString('id-ID');
};

const handleDigit = (digit) => {
    const newValue = value.value === '0' ? digit : value.value + digit;
    const numValue = parseInt(newValue, 10);
    if (numValue > props.maxValue) return;
    value.value = newValue;
};

const handleBackspace = () => {
    value.value = value.value.length > 1 ? value.value.slice(0, -1) : '0';
};

const handleClear = () => {
    value.value = '0';
};

const handleQuickAmount = (amount) => {
    const current = parseInt(value.value, 10) || 0;
    const newValue = current + amount;
    if (newValue <= props.maxValue) {
        value.value = String(newValue);
    }
};

const handleConfirm = () => {
    if (isValid.value) {
        props.onConfirm(numValue.value);
        props.onClose();
    }
};

const handleKeyDown = (e) => {
    if (!props.isOpen) return;

    if (e.key >= '0' && e.key <= '9') {
        handleDigit(e.key);
    } else if (e.key === 'Backspace') {
        handleBackspace();
    } else if (e.key === 'Enter') {
        handleConfirm();
    } else if (e.key === 'Escape') {
        props.onClose();
    } else if (e.key === 'c' || e.key === 'C') {
        handleClear();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

