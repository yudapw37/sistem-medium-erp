<template>
    <form @submit.prevent="searchData">
        <div class="relative">
            <input
                type="text"
                v-model="form.search"
                :placeholder="placeholder"
                class="py-2 px-4 pr-11 block w-full rounded-lg text-sm border focus:outline-none focus:ring-0 focus:ring-gray-400 text-gray-700 bg-white border-gray-200 focus:border-gray-200 dark:focus:ring-gray-500 dark:focus:border-gray-800 dark:text-gray-200 dark:bg-gray-950 dark:border-gray-900"
            />
            <div class="absolute inset-y-0 right-0 flex items-center pointer-events-none pr-4">
                <IconSearch class="text-gray-500 w-5 h-5" />
            </div>
        </div>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';

const props = defineProps({
    url: String,
    placeholder: String,
});

const form = useForm({
    search: '',
});

const searchData = () => {
    form.get(`${props.url}?search=${form.search}`);
};
</script>

