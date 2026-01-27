<template>
    <td :class="['whitespace-nowrap p-4 align-middle text-gray-700 dark:text-gray-400', className]">
        <slot />
    </td>
</template>

<script setup>
defineProps({
    className: String,
});
</script>

