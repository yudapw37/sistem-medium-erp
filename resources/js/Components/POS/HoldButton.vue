<template>
    <div v-if="hasItems">
        <div v-if="showLabelInput" class="flex gap-2">
            <input
                type="text"
                v-model="label"
                @keydown="handleKeyDown"
                placeholder="Label (opsional)"
                class="flex-1 h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm"
                autofocus
            />
            <button
                @click="handleHold"
                :disabled="isHolding"
                class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold disabled:opacity-50"
            >
                {{ isHolding ? '...' : 'OK' }}
            </button>
            <button
                @click="showLabelInput = false"
                class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
            >
                <IconX :size="14" class="text-slate-500" />
            </button>
        </div>
        <button
            v-else
            @click="showLabelInput = true"
            class="flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-lg border border-dashed border-amber-400 dark:border-amber-700 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 text-xs font-medium transition-colors"
        >
            <IconClock :size="14" />
            Tahan
        </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { IconClock, IconX } from '@tabler/icons-vue';

const props = defineProps({
    hasItems: {
        type: Boolean,
        default: false,
    },
    onHold: {
        type: Function,
        required: true,
    },
    isHolding: {
        type: Boolean,
        default: false,
    },
});

const showLabelInput = ref(false);
const label = ref('');

const handleHold = () => {
    props.onHold(label.value || null);
    label.value = '';
    showLabelInput.value = false;
};

const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
        handleHold();
    }
    if (e.key === 'Escape') {
        showLabelInput.value = false;
    }
};
</script>

