<template>
    <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:shadow-lg hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200">
        <div class="relative aspect-[3/2] bg-slate-100 dark:bg-slate-800 overflow-hidden">
            <img
                v-if="category.image"
                :src="category.image"
                :alt="category.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
                <IconCategory :size="48" class="text-slate-300 dark:text-slate-600" :stroke-width="1" />
            </div>
            <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/40 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                <Link
                    :href="route('categories.edit', category.id)"
                    class="p-2.5 rounded-xl bg-white text-warning-600 hover:bg-warning-50 shadow-lg transition-colors"
                >
                    <IconPencilCog :size="18" />
                </Link>
                <Button
                    type="delete"
                    :icon="IconTrash"
                    class="p-2.5 rounded-xl bg-white text-danger-600 hover:bg-danger-50 shadow-lg"
                    :url="route('categories.destroy', category.id)"
                />
            </div>
        </div>
        <div class="p-4">
            <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-1">
                {{ category.name }}
            </h3>
            <p v-if="category.description" class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
                {{ category.description }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { IconCategory, IconPencilCog, IconTrash } from '@tabler/icons-vue';
import Button from './Button.vue';

defineProps({
    category: Object,
});
</script>

