<template>
    <div class="flex flex-col gap-2">
        <label v-if="label" class="text-gray-600 text-sm">{{ label }}</label>
        <div class="relative">
            <button
                type="button"
                @click="isOpen = !isOpen"
                class="w-full px-3 py-1.5 border text-sm rounded-md focus:outline-none focus:ring-0 flex justify-between items-center gap-8 bg-white text-gray-700 focus:border-gray-200 border-gray-200 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-gray-700 dark:border-gray-800"
            >
                <span>{{ preview }}</span>
                <IconChevronDown :size="20" :stroke-width="1.5" />
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
                    class="absolute z-50 mt-1 w-full p-4 border rounded-lg flex flex-wrap gap-2 bg-gray-100 dark:border-gray-900 dark:bg-gray-950"
                    @click.stop
                >
                    <div
                        v-for="item in data"
                        :key="item.id"
                        @click="toggleItem(item)"
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
                        {{ item.name }}
                    </div>
                </div>
            </Transition>
        </div>
        <small v-if="errors" class="text-xs text-red-500">{{ errors }}</small>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { IconChevronDown, IconCircle, IconCircleFilled } from '@tabler/icons-vue';

const props = defineProps({
    selected: {
        type: Array,
        default: () => [],
    },
    data: {
        type: Array,
        default: () => [],
    },
    setSelected: {
        type: Function,
        required: true,
    },
    label: String,
    errors: String,
});

const isOpen = ref(false);

const preview = computed(() => {
    if (props.selected.length === 0) {
        return 'Pilih Hak Akses';
    }
    if (props.selected.length >= 4) {
        return `jumlah hak akses terpilih ${props.selected.length}`;
    }
    return props.selected.map((item) => item.name).join(', ');
});

const isSelected = (item) => {
    return props.selected.some((selectedItem) => selectedItem.id === item.id);
};

const toggleItem = (item) => {
    const newSelected = [...props.selected];
    const index = newSelected.findIndex((selectedItem) => selectedItem.id === item.id);
    
    if (index > -1) {
        newSelected.splice(index, 1);
    } else {
        newSelected.push(item);
    }
    
    props.setSelected(newSelected);
};
</script>

