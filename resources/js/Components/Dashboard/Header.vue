<template>
    <div class="flex w-full items-center justify-between gap-4 mb-2">
        <div>
            <div class="text-lg font-bold capitalize text-gray-200">{{ title }}</div>
            <div class="text-gray-500 text-sm">{{ subtitle }}</div>
        </div>
        <slot />
    </div>
</template>

<script setup>
defineProps({
    title: String,
    subtitle: String,
});
</script>

