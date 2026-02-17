<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        default: null,
    },
    label: String,
    errors: String,
});

const emit = defineEmits(['update:modelValue']);

const proxyChecked = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    },
});
</script>

<template>
    <div>
        <div class="flex flex-row items-center gap-2">
            <input
                type="checkbox"
                v-model="proxyChecked"
                :value="value"
                :class="[
                    'rounded-md bg-white border-gray-200 dark:bg-gray-950 dark:border-gray-800 checked:bg-teal-500 focus:ring-teal-500/20',
                ]"
                v-bind="$attrs"
            />
            <label v-if="label" class="text-sm text-gray-700 dark:text-gray-400">
                {{ label }}
            </label>
            <small v-if="errors" class="text-xs text-red-500">{{ errors }}</small>
        </div>
    </div>
</template>

