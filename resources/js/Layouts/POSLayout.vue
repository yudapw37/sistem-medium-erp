<template>
    <div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950">
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-50 h-16 flex items-center justify-between px-4 lg:px-6 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-sm">
            <!-- Left Section - Logo & Time -->
            <div class="flex items-center gap-4 lg:gap-6">
                <!-- Mobile Menu Toggle -->
                <button
                    @click="showMobileMenu = !showMobileMenu"
                    class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                >
                    <IconX
                        v-if="showMobileMenu"
                        :size="22"
                        class="text-slate-600 dark:text-slate-400"
                    />
                    <IconMenu2
                        v-else
                        :size="22"
                        class="text-slate-600 dark:text-slate-400"
                    />
                </button>

                <!-- Logo -->
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">K</span>
                    </div>
                    <span class="hidden sm:block text-lg font-bold text-slate-800 dark:text-white">
                        KASIR
                    </span>
                </Link>

                <!-- Divider -->
                <div class="hidden md:block w-px h-8 bg-slate-200 dark:bg-slate-700" />

                <!-- Time & Date -->
                <div class="hidden md:flex items-center gap-3">
                    <div class="text-2xl font-semibold text-slate-800 dark:text-white tabular-nums">
                        {{ formatTime(currentTime) }}
                    </div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        {{ formatDate(currentTime) }}
                    </div>
                </div>
            </div>

            <!-- Right Section - Actions & User -->
            <div class="flex items-center gap-2 lg:gap-3">
                <!-- Quick Actions -->
                <nav class="hidden lg:flex items-center gap-1">
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors"
                    >
                        <IconHome :size="18" />
                        <span>Dashboard</span>
                    </Link>
                    <Link
                        :href="route('transactions.history')"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors"
                    >
                        <IconHistory :size="18" />
                        <span>Riwayat</span>
                    </Link>
                </nav>

                <!-- Divider -->
                <div class="hidden lg:block w-px h-8 bg-slate-200 dark:bg-slate-700" />

                <!-- Theme Toggle -->
                <button
                    @click="themeSwitcher"
                    class="p-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors min-w-touch min-h-touch flex items-center justify-center"
                    :title="darkMode ? 'Light Mode' : 'Dark Mode'"
                >
                    <IconSun v-if="darkMode" :size="20" class="text-amber-500" />
                    <IconMoon v-else :size="20" class="text-slate-500" />
                </button>

                <!-- User Info -->
                <div class="flex items-center gap-3 pl-2 lg:pl-3 border-l border-slate-200 dark:border-slate-700">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            {{ auth.user.name }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kasir</p>
                    </div>
                    <img
                        :src="auth.user.avatar || `https://ui-avatars.com/api/?name=${auth.user.name}&background=6366f1&color=fff`"
                        :alt="auth.user.name"
                        class="w-9 h-9 rounded-full ring-2 ring-slate-200 dark:ring-slate-700"
                    />
                </div>

                <!-- Logout -->
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="hidden lg:flex p-2.5 rounded-lg text-slate-500 hover:text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950/50 transition-colors min-w-touch min-h-touch items-center justify-center"
                    title="Logout"
                >
                    <IconLogout :size="20" />
                </Link>
            </div>
        </header>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="showMobileMenu"
            class="lg:hidden fixed inset-0 z-40 bg-black/50"
            @click="showMobileMenu = false"
        >
            <div
                class="absolute top-16 left-0 right-0 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-lg animate-slide-up"
                @click.stop
            >
                <nav class="p-4 space-y-2">
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors"
                    >
                        <IconHome :size="20" />
                        <span class="font-medium">Dashboard</span>
                    </Link>
                    <Link
                        :href="route('transactions.history')"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors"
                    >
                        <IconHistory :size="20" />
                        <span class="font-medium">Riwayat Transaksi</span>
                    </Link>
                    <Link
                        :href="route('profile.edit')"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors"
                    >
                        <IconUser :size="20" />
                        <span class="font-medium">Profil</span>
                    </Link>
                    <hr class="border-slate-200 dark:border-slate-700" />
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950/50 transition-colors w-full"
                    >
                        <IconLogout :size="20" />
                        <span class="font-medium">Keluar</span>
                    </Link>
                </nav>
            </div>
        </div>

        <!-- Main Content - Full Height -->
        <main class="flex-1 overflow-hidden">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import {
    IconHome,
    IconHistory,
    IconSun,
    IconMoon,
    IconLogout,
    IconMenu2,
    IconX,
    IconUser,
} from '@tabler/icons-vue';
import { injectTheme } from '@/Composables/useTheme';

const page = usePage();
const toast = useToast();
const { auth } = page.props;
const { darkMode, themeSwitcher } = injectTheme();

const flash = computed(() => page.props.flash);

watch(flash, (newFlash) => {
    if (newFlash?.success) {
        toast.success(newFlash.success);
    }
    if (newFlash?.error) {
        toast.error(newFlash.error);
    }
}, { deep: true, immediate: true });

const currentTime = ref(new Date());
const showMobileMenu = ref(false);

const formatTime = (date) => {
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDate = (date) => {
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        currentTime.value = new Date();
    }, 60000);
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>

