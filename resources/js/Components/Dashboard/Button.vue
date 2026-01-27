<template>
    <Link
        v-if="type === 'link'"
        :href="href"
        :class="[baseStyles, sizeStyles, className]"
    >
        <component v-if="icon" :is="icon" />
        <span :class="added === true ? 'hidden lg:block' : ''">
            {{ label }}
        </span>
    </Link>

    <button
        v-else-if="type === 'button'"
        :class="[baseStyles, sizeStyles, className]"
        v-bind="$attrs"
    >
        <component v-if="icon" :is="icon" />
        <span :class="added === true ? 'hidden md:block' : ''">
            {{ label }}
        </span>
    </button>

    <button
        v-else-if="type === 'submit'"
        type="submit"
        :class="[baseStyles, sizeStyles, className]"
        v-bind="$attrs"
    >
        <component v-if="icon" :is="icon" />
        <span :class="added === true ? 'hidden lg:block' : ''">
            {{ label }}
        </span>
    </button>

    <button
        v-else-if="type === 'delete'"
        @click="deleteData"
        :class="[baseStyles, smallStyles, className]"
        v-bind="$attrs"
    >
        <component v-if="icon" :is="icon" />
        <span v-if="label">{{ label }}</span>
    </button>

    <button
        v-else-if="type === 'modal'"
        :class="[baseStyles, smallStyles, className]"
        v-bind="$attrs"
    >
        <component v-if="icon" :is="icon" />
    </button>

    <Link
        v-else-if="type === 'edit'"
        :href="href"
        :class="[baseStyles, smallStyles, className]"
        v-bind="$attrs"
    >
        <component v-if="icon" :is="icon" />
    </Link>

    <button
        v-else-if="type === 'bulk'"
        :class="[baseStyles, sizeStyles, className]"
        v-bind="$attrs"
    >
        <component v-if="icon" :is="icon" />
        <span :class="added === true ? 'hidden lg:block' : ''">
            {{ label }}
        </span>
    </button>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    className: String,
    icon: [Object, Function],
    label: String,
    type: {
        type: String,
        default: 'button',
    },
    href: String,
    added: Boolean,
    url: String,
    id: [String, Number],
});

const baseStyles =
    'inline-flex items-center justify-center gap-2 font-medium transition-all duration-200 active:scale-[0.98]';
const sizeStyles = 'px-4 py-2.5 text-sm rounded-xl';
const smallStyles = 'px-3 py-2 rounded-xl';

const deleteData = async () => {
    const result = await Swal.fire({
        title: 'Hapus Data?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
    });

    if (result.isConfirmed) {
        router.delete(props.url, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Data berhasil dihapus!',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500,
                });
            },
        });
    }
};
</script>

