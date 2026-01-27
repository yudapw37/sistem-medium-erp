<template>
    <div class="min-h-screen flex bg-slate-100 dark:bg-slate-950 transition-colors duration-200">
        <Sidebar :sidebar-open="sidebarOpen" />
        <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
            <Navbar
                @toggle-sidebar="toggleSidebar"
                @theme-switch="themeSwitcher"
                :dark-mode="darkMode"
            />
            <main class="flex-1 overflow-y-auto">
                <div class="w-full py-6 px-4 md:px-6 lg:px-8 pb-20 md:pb-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Sidebar from '@/Components/Dashboard/Sidebar.vue';
import Navbar from '@/Components/Dashboard/Navbar.vue';
import { injectTheme } from '@/Composables/useTheme';

const page = usePage();
const toast = useToast();
const flash = computed(() => page.props.flash);

watch(flash, (newFlash) => {
    if (newFlash?.success) {
        toast.success(newFlash.success);
    }
    if (newFlash?.error) {
        toast.error(newFlash.error);
    }
}, { deep: true, immediate: true });

const { darkMode, themeSwitcher } = injectTheme();

const sidebarOpen = ref(localStorage.getItem('sidebarOpen') === 'true');

watch(sidebarOpen, (newValue) => {
    localStorage.setItem('sidebarOpen', newValue);
});

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};
</script>

