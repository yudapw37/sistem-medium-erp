<template>
    <div
        :class="[
            'border p-4 rounded-lg bg-white dark:bg-gray-950 dark:border-gray-800',
            className,
        ]"
    >
        <div class="flex justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div :class="['p-2 rounded-lg', color]">
                    <component :is="icon" :size="20" :stroke-width="1.5" />
                </div>
                <div class="flex flex-col">
                    <div class="font-semibold text-gray-900 dark:text-gray-200">{{ title }}</div>
                    <div class="text-xs text-gray-500">{{ subtitle }}</div>
                </div>
            </div>
            <div class="font-semibold text-base font-mono p-2 text-gray-900 dark:text-white">
                {{ total }}
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    icon: [Object, Function],
    subtitle: String,
    className: String,
    total: [Number, String],
    color: String,
});
</script>

