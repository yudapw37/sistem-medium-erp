<template>
    <input
        type="checkbox"
        :class="[
            'rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500',
            className,
        ]"
        v-bind="$attrs"
    />
</template>

<script setup>
defineProps({
    className: {
        type: String,
        default: '',
    },
});
</script>

