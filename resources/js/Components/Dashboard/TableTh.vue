<template>
    <th
        scope="col"
        :class="['h-12 px-4 text-left align-middle font-medium text-gray-700 dark:text-gray-400', className]"
    >
        <slot />
    </th>
</template>

<script setup>
defineProps({
    className: String,
});
</script>

