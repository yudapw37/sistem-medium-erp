<template>
    <header
        class="sticky top-0 z-30 h-16 flex items-center justify-between px-4 md:px-6 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 transition-colors duration-200"
    >
        <!-- Left Section -->
        <div class="flex items-center gap-4">
            <!-- Sidebar Toggle -->
            <button
                @click="$emit('toggle-sidebar')"
                class="hidden md:flex p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors"
                title="Toggle Sidebar"
            >
                <IconMenu2 :size="20" :stroke-width="1.5" />
            </button>

            <!-- Mobile Logo -->
            <div class="md:hidden flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                    <span class="text-white font-bold text-xs">K</span>
                </div>
                <span class="text-lg font-bold text-slate-800 dark:text-white">KASIR</span>
            </div>

            <!-- Current Page Title -->
            <div class="hidden md:flex items-center">
                <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 mr-4" />
                <h1 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                    {{ getCurrentTitle() }}
                </h1>
            </div>
        </div>

        <!-- Right Section -->
        <div class="flex items-center gap-2">
            <!-- Theme Toggle -->
            <button
                @click="$emit('theme-switch')"
                class="p-2.5 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors"
                :title="darkMode ? 'Light Mode' : 'Dark Mode'"
            >
                <IconSun
                    v-if="darkMode"
                    :size="20"
                    :stroke-width="1.5"
                    class="text-amber-500"
                />
                <IconMoon v-else :size="20" :stroke-width="1.5" />
            </button>

            <!-- Notifications -->
            <Notification />

            <!-- Divider -->
            <div class="w-px h-8 bg-slate-200 dark:bg-slate-700 mx-1" />

            <!-- User Dropdown -->
            <AuthDropdown :auth="auth" :is-mobile="isMobile" />
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { IconMenu2, IconMoon, IconSun } from '@tabler/icons-vue';
import useMenu from '@/Utils/Menu';
import AuthDropdown from './AuthDropdown.vue';
import Notification from './Notification.vue';

defineProps({
    darkMode: Boolean,
});

defineEmits(['toggle-sidebar', 'theme-switch']);

const { auth } = usePage().props;
const menuNavigation = useMenu();

const isMobile = ref(false);

const links = computed(() => menuNavigation.flatMap((item) => item.details));
const sublinks = computed(() =>
    links.value
        .filter((item) => item.hasOwnProperty('subdetails'))
        .flatMap((item) => item.subdetails)
);

const getCurrentTitle = () => {
    for (const link of links.value) {
        if (link.hasOwnProperty('subdetails')) {
            const activeSublink = sublinks.value.find((s) => s.active);
            if (activeSublink) return activeSublink.title;
        } else if (link.active) {
            return link.title;
        }
    }
    return 'Dashboard';
};

const handleResize = () => {
    isMobile.value = window.innerWidth <= 768;
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
    handleResize();
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});
</script>

