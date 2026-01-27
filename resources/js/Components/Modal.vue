<template>
    <Transition
        :show="show"
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 flex overflow-y-auto px-4 py-6 sm:px-0 items-center z-50 transform transition-all"
            @click="close"
        >
            <div class="absolute inset-0 bg-gray-500/75" />

            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div
                    v-if="show"
                    :class="[
                        'mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto',
                        maxWidthClass,
                    ]"
                    @click.stop
                >
                    <slot />
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const maxWidthClass = computed(() => {
    const classes = {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    };
    return classes[props.maxWidth] || classes['2xl'];
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};
</script>

