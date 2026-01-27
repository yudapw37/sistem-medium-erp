<template>
    <ul class="mt-2 lg:mt-5 justify-end flex items-center gap-1">
        <template v-for="(item, i) in links" :key="i">
            <Link
                v-if="item.url != null"
                :href="item.url"
                :class="[
                    style,
                    item.label.includes('Previous') || item.label.includes('Next')
                        ? ''
                        : item.active
                          ? 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-50'
                          : 'bg-white dark:bg-gray-950',
                ]"
            >
                <IconChevronLeft
                    v-if="item.label.includes('Previous')"
                    :size="20"
                    :stroke-width="1.5"
                />
                <IconChevronRight
                    v-else-if="item.label.includes('Next')"
                    :size="20"
                    :stroke-width="1.5"
                />
                <span v-else>{{ item.label }}</span>
            </Link>
        </template>
    </ul>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { IconChevronRight, IconChevronLeft } from '@tabler/icons-vue';

defineProps({
    links: Array,
});

const style =
    'p-1 text-sm border rounded-md bg-white text-gray-500 hover:bg-gray-100 dark:bg-gray-950 dark:text-gray-400 dark:hover:bg-gray-900 dark:border-gray-900';
</script>

