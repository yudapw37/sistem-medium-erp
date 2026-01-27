<template>
    <div>
        <div :class="['p-4 rounded-t-lg border bg-white dark:bg-gray-950 dark:border-gray-900', className]">
            <div class="flex items-center gap-2 font-semibold text-sm text-gray-700 dark:text-gray-200">
                {{ title }}
            </div>
        </div>
        <div class="bg-white dark:bg-gray-950 rounded-b-lg border-t-0 dark:border-gray-900">
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    className: String,
});
</script>

