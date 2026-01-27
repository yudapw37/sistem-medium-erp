<template>
    <div class="relative">
        <div @click="toggleOpen">
            <slot name="trigger" />
        </div>
        
        <Teleport to="body">
            <div v-if="open" class="fixed inset-0 z-40" @click="close"></div>
        </Teleport>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                :class="[
                    'absolute z-50 mt-2 rounded-md shadow-lg',
                    alignmentClasses,
                    widthClasses
                ]"
                @click="close"
            >
                <div :class="['rounded-md ring-1 ring-black ring-opacity-5', contentClasses]">
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: String,
        default: 'py-1 bg-white',
    },
});

const open = ref(false);

const toggleOpen = () => {
    open.value = !open.value;
};

const close = () => {
    open.value = false;
};

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'ltr:origin-top-left rtl:origin-top-right start-0';
    } else if (props.align === 'right') {
        return 'ltr:origin-top-right rtl:origin-top-left end-0';
    }
    return 'origin-top';
});

const widthClasses = computed(() => {
    if (props.width === '48') {
        return 'w-48';
    }
    return '';
});
</script>

