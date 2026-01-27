<template>
    <tbody :class="['divide-y bg-white dark:divide-gray-900 dark:bg-gray-950', className]">
        <slot />
    </tbody>
</template>

<script setup>
defineProps({
    className: String,
});
</script>

