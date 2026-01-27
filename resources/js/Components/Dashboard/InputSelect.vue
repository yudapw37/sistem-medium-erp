<template>
    <div class="flex flex-col gap-2">
        <label v-if="label" class="text-gray-600 text-sm">{{ label }}</label>
        <div class="relative">
            <button
                type="button"
                @click="!disabled && (isOpen = !isOpen)"
                :disabled="disabled"
                :class="[
                    'w-full px-3 py-1.5 border text-sm rounded-md focus:outline-none focus:ring-0 flex justify-between items-center gap-8 bg-white text-gray-700 focus:border-gray-200 border-gray-200 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-gray-700 dark:border-gray-800 transition-all',
                    disabled ? 'opacity-50 cursor-not-allowed bg-slate-50 dark:bg-slate-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800'
                ]"
            >
                <span v-if="multiple">
                    {{ selected.length > 0 ? selected.map((item) => item[displayKey]).join(', ') : placeholder }}
                </span>
                <span v-else>
                    {{ selected ? selected[displayKey] : placeholder }}
                </span>
                <IconChevronDown :size="20" :stroke-width="1.5" class="transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''" />
            </button>

            <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-if="isOpen"
                    class="absolute z-50 mt-1 w-full p-4 border rounded-lg flex flex-col gap-2 bg-gray-100 dark:border-gray-900 dark:bg-gray-950"
                    @click.stop
                >
                    <input
                        v-if="searchable"
                        type="text"
                        v-model="search"
                        placeholder="Search..."
                        class="w-full px-3 py-1.5 mb-2 text-sm border rounded-md bg-white text-gray-700 border-gray-200 focus:outline-none focus:border-gray-300 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-800 dark:focus:border-gray-700"
                        @click.stop
                    />
                    <div
                        v-for="item in filteredData"
                        :key="item.id"
                        @click="selectItem(item)"
                        :class="[
                            'text-sm cursor-pointer px-3 py-1.5 rounded-lg flex items-center gap-2 bg-white text-gray-700 hover:bg-gray-200 border dark:bg-gray-900 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800',
                            isSelected(item) && 'bg-teal-50 dark:bg-teal-950/20',
                        ]"
                    >
                        <component
                            :is="isSelected(item) ? IconCircleFilled : IconCircle"
                            :size="15"
                            :stroke-width="1.5"
                            :class="isSelected(item) ? 'text-teal-500' : ''"
                        />
                        {{ item[displayKey] }}
                    </div>
                </div>
            </Transition>
        </div>
        <small v-if="errors" class="text-xs text-red-500">{{ errors }}</small>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { IconChevronDown, IconCircle, IconCircleFilled } from '@tabler/icons-vue';

const props = defineProps({
    selected: [Object, Array],
    data: Array,
    setSelected: Function,
    label: String,
    errors: String,
    placeholder: String,
    multiple: {
        type: Boolean,
        default: false,
    },
    searchable: {
        type: Boolean,
        default: false,
    },
    displayKey: {
        type: String,
        default: 'name',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const isOpen = ref(false);
const search = ref('');

const filteredData = computed(() => {
    if (!props.searchable || !search.value) {
        return props.data;
    }
    return props.data.filter((item) =>
        item[props.displayKey]?.toLowerCase().includes(search.value.toLowerCase())
    );
});

const isSelected = (item) => {
    if (props.multiple) {
        return props.selected.some((sel) => sel.id === item.id);
    }
    return props.selected?.id === item.id;
};

const selectItem = (item) => {
    if (props.multiple) {
        const newSelected = [...props.selected];
        const index = newSelected.findIndex((sel) => sel.id === item.id);
        if (index > -1) {
            newSelected.splice(index, 1);
        } else {
            newSelected.push(item);
        }
        props.setSelected(newSelected);
    } else {
        props.setSelected(item);
        isOpen.value = false;
    }
};

const handleClickOutside = (event) => {
    if (!event.target.closest('.relative')) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

