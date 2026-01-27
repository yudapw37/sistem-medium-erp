<template>
    <label
        :class="['block font-medium text-sm text-gray-700', className]"
        v-bind="$attrs"
    >
        <slot>{{ value }}</slot>
    </label>
</template>

<script setup>
defineProps({
    value: String,
    className: {
        type: String,
        default: '',
    },
});
</script>

