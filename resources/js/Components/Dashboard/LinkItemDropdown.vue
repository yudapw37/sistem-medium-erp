<template>
    <div v-if="canAccess">
        <button
            v-if="sidebarOpen"
            :class="buttonClasses"
            @click="isOpen = !isOpen"
        >
            <div class="flex items-center gap-x-3.5">
                <component :is="icon" :size="20" :stroke-width="1.5" />
                {{ title }}
            </div>
            <component
                :is="isOpen ? IconChevronUp : IconChevronDown"
                :size="18"
                :stroke-width="1.5"
            />
        </button>
        <button
            v-else
            :class="collapsedButtonClasses"
            @click="isOpen = !isOpen"
        >
            <component
                v-if="!isOpen"
                :is="icon"
                :size="20"
                :stroke-width="1.5"
            />
            <component
                v-else
                :is="IconChevronDown"
                :size="20"
                :stroke-width="1.5"
            />
        </button>

        <template v-if="isOpen">
            <Link
                v-for="(item, i) in data.filter(d => d.permissions === true)"
                :key="i"
                :href="item.href"
                :class="[
                    url === item.href
                        ? 'border-r-2 border-r-gray-400 bg-gray-100 text-gray-700 dark:border-r-gray-500 dark:bg-gray-900 dark:text-white'
                        : '',
                    sidebarOpen
                        ? 'min-w-full flex items-center font-medium gap-x-3.5 px-5 py-3 hover:border-r-2 capitalize hover:cursor-pointer text-sm line-clamp-1 text-gray-500 hover:border-r-gray-700 hover:text-gray-900 dark:text-gray-500 dark:hover:border-r-gray-50 dark:hover:text-gray-100'
                        : 'min-w-full flex justify-center py-3 hover:border-r-2 hover:cursor-pointer text-gray-500 hover:border-r-gray-700 hover:text-gray-900 dark:text-gray-500 dark:hover:border-r-gray-50 dark:hover:text-gray-100',
                ]"
                v-bind="$attrs"
            >
                <component
                    v-if="sidebarOpen"
                    :is="IconCornerDownRight"
                    :size="18"
                    :stroke-width="1.5"
                />
                <component v-else :is="item.icon" :size="20" :stroke-width="1.5" />
                <span v-if="sidebarOpen">{{ item.title }}</span>
            </Link>
        </template>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    IconChevronDown,
    IconChevronUp,
    IconCornerDownRight,
} from '@tabler/icons-vue';

const props = defineProps({
    icon: [Object, Function],
    title: String,
    data: Array,
    access: Boolean,
    sidebarOpen: Boolean,
});

const { url } = usePage();
const { auth } = usePage().props;

const isOpen = ref(false);

const canAccess = computed(() => {
    if (auth.super === true) return true;
    return props.access === true;
});

const buttonClasses =
    'min-w-full flex items-center font-medium gap-x-3.5 px-4 py-3 hover:border-r-2 capitalize hover:cursor-pointer text-sm justify-between text-gray-500 hover:border-r-gray-700 hover:text-gray-900 dark:text-gray-500 dark:hover:border-r-gray-50 dark:hover:text-gray-100';

const collapsedButtonClasses =
    'min-w-full flex justify-center py-3 hover:border-r-2 item-navigation hover:cursor-pointer text-gray-500 hover:border-r-gray-700 hover:text-gray-900 dark:text-gray-500 dark:hover:border-r-gray-50 dark:hover:text-gray-100';
</script>

