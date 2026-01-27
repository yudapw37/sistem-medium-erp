<template>
    <thead :class="['border-b bg-gray-50 dark:border-gray-900 dark:bg-gray-950', className]">
        <slot />
    </thead>
</template>

<script setup>
defineProps({
    className: String,
});
</script>

