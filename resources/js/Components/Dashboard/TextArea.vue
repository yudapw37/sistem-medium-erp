<template>
    <div class="flex flex-col gap-2">
        <label v-if="label" :for="textareaId" class="text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ label }}
        </label>
        <textarea
            :id="textareaId"
            :name="name || textareaId"
            :rows="rows"
            :value="modelValue"
            :class="[
                'w-full px-4 py-3 text-sm rounded-xl border bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all duration-200 resize-none',
                errors
                    ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500/20'
                    : 'border-slate-200 dark:border-slate-700',
                className,
            ]"
            @input="$emit('update:modelValue', $event.target.value)"
            v-bind="$attrs"
        />
        <small v-if="errors" class="text-xs text-danger-500 dark:text-danger-400">
            {{ errors }}
        </small>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: String,
    className: String,
    errors: String,
    name: String,
    id: String,
    modelValue: String,
    rows: {
        type: Number,
        default: 4,
    },
});

defineEmits(['update:modelValue']);

const textareaId = computed(() => {
    return props.id || props.name || `textarea-${Math.random().toString(36).substr(2, 9)}`;
});
</script>

