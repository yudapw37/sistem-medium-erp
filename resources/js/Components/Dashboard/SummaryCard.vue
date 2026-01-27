<template>
    <div
        :class="[
            'relative overflow-hidden rounded-2xl p-5 bg-gradient-to-br text-white shadow-lg',
            gradient,
        ]"
    >
        <div class="absolute top-0 right-0 w-24 h-24 opacity-20">
            <component
                :is="icon"
                :size="96"
                :stroke-width="0.5"
                class="transform translate-x-4 -translate-y-4"
            />
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-2 rounded-xl bg-white/20">
                    <component :is="icon" :size="18" />
                </div>
                <span class="text-sm font-medium opacity-90">{{ title }}</span>
            </div>
            <p class="text-2xl font-bold">{{ value }}</p>
            <p class="text-sm opacity-80 mt-1">{{ description }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: String,
    description: String,
    icon: [Object, Function],
    gradient: String,
});
</script>

