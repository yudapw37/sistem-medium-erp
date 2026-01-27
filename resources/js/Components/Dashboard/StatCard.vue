<template>
    <div :class="['relative overflow-hidden rounded-2xl p-5 bg-gradient-to-br text-white shadow-lg', gradient]">
        <div class="absolute top-0 right-0 w-32 h-32 opacity-20">
            <component
                :is="icon"
                :size="128"
                :stroke-width="0.5"
                class="transform translate-x-8 -translate-y-8"
            />
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-3">
                <div class="p-2 rounded-xl bg-white/20">
                    <component :is="icon" :size="20" :stroke-width="1.5" />
                </div>
                <span class="text-sm font-medium opacity-90">{{ title }}</span>
            </div>
            <p class="text-3xl font-bold">{{ value }}</p>
            <p v-if="subtitle" class="mt-2 text-sm opacity-80 flex items-center gap-1">
                <IconArrowUpRight v-if="trend === 'up'" :size="14" />
                <IconArrowDownRight v-if="trend === 'down'" :size="14" />
                {{ subtitle }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { IconArrowUpRight, IconArrowDownRight } from '@tabler/icons-vue';

defineProps({
    title: String,
    value: String,
    subtitle: String,
    icon: [Object, Function],
    gradient: String,
    trend: String,
});
</script>

