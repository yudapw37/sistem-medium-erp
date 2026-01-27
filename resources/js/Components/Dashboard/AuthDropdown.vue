<template>
    <div v-if="!isMobile" class="relative z-10">
        <button 
            class="flex items-center gap-3 px-3 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 group border border-transparent hover:border-slate-200 dark:hover:border-slate-700" 
            @click="isToggle = !isToggle"
        >
            <div class="text-right hidden sm:block">
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 leading-none mb-1 group-hover:text-primary-600 transition-colors">
                    {{ auth.user.name }}
                </p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-none uppercase tracking-wider font-medium">
                    {{ auth.user.email }}
                </p>
            </div>
            <img
                :src="auth.user.avatar || `https://ui-avatars.com/api/?name=${auth.user.name}&background=6366f1&color=fff`"
                :alt="auth.user.name"
                class="w-10 h-10 rounded-full ring-2 ring-white dark:ring-slate-900 border border-slate-200 dark:border-slate-700"
            />
        </button>
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-out"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="isToggle"
                class="absolute rounded-xl w-48 border mt-2 py-2 right-0 z-[100] bg-white dark:bg-slate-900 shadow-xl border-slate-200 dark:border-slate-800 overflow-hidden"
            >
                <div class="flex flex-col">
                    <button
                        @click="logout"
                        class="px-4 py-2.5 text-sm flex items-center gap-3 text-slate-600 hover:text-red-600 hover:bg-red-50 dark:text-slate-400 dark:hover:text-red-400 dark:hover:bg-red-900/20 transition-colors font-medium"
                    >
                        <IconLogout :stroke-width="1.5" :size="18" />
                        Keluar Aplikasi
                    </button>
                </div>
            </div>
        </Transition>
    </div>
    <div v-else ref="dropdownRef">
        <button class="flex items-center group" @click="isToggle = !isToggle">
            <img
                :src="auth.user.avatar || `https://ui-avatars.com/api/?name=${auth.user.name}`"
                :alt="auth.user.name"
                class="w-10 h-10 rounded-full"
            />
        </button>
        <div
            :class="[
                isToggle ? 'translate-x-0 opacity-100' : '-translate-x-full',
                'fixed top-0 left-0 z-50 w-[300px] h-full transition-all duration-300 transform border-r bg-white dark:bg-gray-950 dark:border-gray-900',
            ]"
        >
            <div class="flex justify-center items-center px-6 py-2 h-16">
                <div class="text-2xl font-bold text-center leading-loose tracking-wider text-gray-900 dark:text-gray-200">
                    KASIR
                </div>
            </div>
            <div class="w-full p-3 flex items-center gap-4 border-b border-t dark:bg-gray-950/50 dark:border-gray-900">
                <img
                    :src="auth.user.avatar || `https://ui-avatars.com/api/?name=${auth.user.name}`"
                    class="w-12 h-12 rounded-full"
                />
                <div class="flex flex-col gap-0.5">
                    <div class="text-sm font-semibold capitalize text-gray-700 dark:text-gray-50">
                        {{ auth.user.name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ auth.user.email }}
                    </div>
                </div>
            </div>
            <div class="w-full flex flex-col overflow-y-auto">
                <div v-for="(item, index) in menuNavigation" :key="index">
                    <div
                        v-if="item.details.some((detail) => detail.permissions === true)"
                        class="text-gray-500 text-xs py-3 px-4 font-bold uppercase"
                    >
                        {{ item.title }}
                    </div>
                    <template
                        v-for="(detail, indexDetail) in item.details"
                        :key="indexDetail"
                    >
                        <LinkItemDropdown
                            v-if="detail.hasOwnProperty('subdetails')"
                            :title="detail.title"
                            :icon="detail.icon"
                            :data="detail.subdetails"
                            :access="detail.permissions"
                            :sidebar-open="true"
                            @click="isToggle = !isToggle"
                        />
                        <LinkItem
                            v-else
                            :title="detail.title"
                            :icon="detail.icon"
                            :href="detail.href"
                            :access="detail.permissions"
                            :sidebar-open="true"
                            @click="isToggle = !isToggle"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { IconLogout } from '@tabler/icons-vue';
import useMenu from '@/Utils/Menu';
import LinkItem from './LinkItem.vue';
import LinkItemDropdown from './LinkItemDropdown.vue';

const props = defineProps({
    auth: Object,
    isMobile: Boolean,
});

const menuNavigation = useMenu();

// Route helper - use window.route from Ziggy
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};

const isToggle = ref(false);
const dropdownRef = ref(null);

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isToggle.value = false;
    }
};

onMounted(() => {
    window.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('mousedown', handleClickOutside);
});

const logout = async (e) => {
    e.preventDefault();
    if (confirm('Apakah Anda yakin ingin keluar?')) {
        router.post(route('logout'));
    }
};
</script>

