<template>
    <Link
        v-if="canAccess"
        :href="href"
        :class="linkClasses"
        v-bind="$attrs"
    >
        <span v-if="sidebarOpen" :class="iconClasses">
            <component :is="icon" :size="20" :stroke-width="1.5" />
        </span>
        <span v-if="sidebarOpen" class="truncate">{{ title }}</span>
        <component v-else :is="icon" :size="20" :stroke-width="1.5" />
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    icon: [Object, Function],
    access: Boolean,
    title: String,
    sidebarOpen: Boolean,
});

const { url } = usePage();
const { auth } = usePage().props;

const isActive = computed(() => url.startsWith(props.href));
const canAccess = computed(() => auth.super === true || props.access === true);

const linkClasses = computed(() => {
    const baseClasses = 'flex items-center gap-3 transition-all duration-200 text-slate-600 dark:text-slate-400';
    const activeClasses = isActive.value
        ? 'bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-400 border-l-[3px] border-primary-500'
        : 'hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200 border-l-[3px] border-transparent';

    if (props.sidebarOpen) {
        return `${baseClasses} ${activeClasses} px-4 py-2.5 text-sm font-medium`;
    }

    return `w-full flex justify-center py-3 transition-all duration-200 ${
        isActive.value
            ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/50'
            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'
    }`;
});

const iconClasses = computed(() => {
    return isActive.value ? 'text-primary-600 dark:text-primary-400' : '';
});
</script>

