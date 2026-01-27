<template>
    <div v-if="!isMobile" class="relative z-50">
        <button class="flex items-center rounded-md group p-2 relative" @click="isOpen = !isOpen">
            <div
                v-if="data.length > 0"
                class="absolute text-[8px] font-semibold border border-rose-500/40 bg-rose-500/10 text-rose-500 hover:bg-rose-500/20 top-0 -right-2 rounded-md px-1.5 py-0.5 group-hover:scale-125 duration-300 ease-in"
            >
                {{ data.length }}
            </div>
            <IconBell :stroke-width="1.5" :size="18" class="text-gray-700 dark:text-gray-400" />
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
                v-if="isOpen"
                class="absolute rounded-lg w-[500px] border md:right-0 z-[100] bg-white dark:bg-gray-950 dark:border-gray-900"
            >
                <div class="flex justify-between items-center gap-2 p-4 border-b dark:border-gray-900">
                    <div class="text-lg font-bold text-gray-700 dark:text-gray-200">Notifikasi</div>
                    <IconDots class="text-gray-500 dark:text-gray-200" :size="24" />
                </div>
                <div class="p-4">
                    <div class="flex flex-col gap-2 items-start h-60 overflow-y-auto">
                        <div v-if="data.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                            Tidak ada notifikasi
                        </div>
                        <div
                            v-for="(item, i) in data"
                            :key="i"
                            class="flex items-center justify-between w-full p-4 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-900"
                        >
                            <div class="flex items-center gap-4">
                                <component :is="item.icon" />
                                <div>
                                    <div class="font-semibold text-sm text-gray-700 dark:text-gray-200">
                                        {{ item.user }}
                                        <sup class="text-xs font-mono text-gray-400 ml-1">{{ item.time }}</sup>
                                    </div>
                                    <div class="text-gray-500 text-sm">{{ item.title }}</div>
                                </div>
                            </div>
                            <component
                                :is="item.is_read == 1 ? IconCircleCheckFilled : IconCircleCheck"
                                :size="20"
                                :stroke-width="1.5"
                                class="text-gray-500 dark:text-gray-400"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
    <div v-else ref="notificationRef">
        <button class="flex items-center rounded-md group p-2 relative" @click="isOpen = !isOpen">
            <div
                v-if="data.length > 0"
                class="absolute text-[8px] font-semibold border border-rose-500/40 bg-rose-500/10 text-rose-500 hover:bg-rose-500/20 top-0 -right-2 rounded-md px-1.5 py-0.5 group-hover:scale-125 duration-300 ease-in"
            >
                {{ data.length }}
            </div>
            <IconBell :stroke-width="1.5" :size="18" class="text-gray-500 dark:text-gray-400" />
        </button>
        <div
            :class="[
                isOpen ? 'translate-x-0 opacity-100' : 'translate-x-full',
                'fixed top-0 right-0 z-50 w-[300px] h-full transition-all duration-300 transform border-l bg-white dark:bg-gray-950 dark:border-gray-900',
            ]"
        >
            <div class="flex justify-between items-center gap-2 p-4 border-b mt-2 dark:border-gray-900">
                <div class="text-base font-bold text-gray-500 dark:text-gray-400">Notifications</div>
                <IconDots class="text-gray-500 dark:text-gray-400" :size="24" />
            </div>
            <div class="p-4">
                <div class="flex flex-col gap-2 items-start overflow-y-auto h-screen">
                    <div
                        v-for="(item, i) in data"
                        :key="i"
                        class="flex items-center justify-between w-full p-4 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-900"
                    >
                        <div class="flex items-center gap-4">
                            <component :is="item.icon" />
                            <div class="w-full">
                                <div class="font-semibold text-sm line-clamp-1 text-gray-700 dark:text-gray-200">
                                    {{ item.user }}
                                    <sup class="text-xs font-mono text-gray-400 ml-1">{{ item.time }}</sup>
                                </div>
                                <div class="text-gray-500 text-sm line-clamp-1 max-w-[155px]">{{ item.title }}</div>
                            </div>
                        </div>
                        <component
                            :is="item.is_read == 1 ? IconCircleCheckFilled : IconCircleCheck"
                            :size="20"
                            :stroke-width="1.5"
                            class="text-gray-500 dark:text-gray-400"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import {
    IconBell,
    IconDots,
    IconCircleCheck,
    IconCircleCheckFilled,
} from '@tabler/icons-vue';

const data = ref([]);

const isMobile = ref(false);
const isOpen = ref(false);
const notificationRef = ref(null);

const handleClickOutside = (event) => {
    if (notificationRef.value && !notificationRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

const handleResize = () => {
    isMobile.value = window.innerWidth <= 768;
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
    window.addEventListener('mousedown', handleClickOutside);
    handleResize();
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('mousedown', handleClickOutside);
});
</script>

