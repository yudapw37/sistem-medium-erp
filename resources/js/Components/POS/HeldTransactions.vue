<template>
    <div v-if="heldCarts && heldCarts.length > 0">
        <button
            v-if="!isExpanded"
            @click="isExpanded = true"
            class="w-full px-3 py-2 flex items-center justify-between bg-amber-50 dark:bg-amber-950/30 border-b border-amber-200 dark:border-amber-800/50 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors"
        >
            <div class="flex items-center gap-2">
                <div
                    class="w-6 h-6 rounded-md bg-amber-500 flex items-center justify-center text-white text-xs font-bold"
                >
                    {{ heldCarts.length }}
                </div>
                <span class="text-sm font-medium text-amber-700 dark:text-amber-300">
                    Transaksi Ditahan
                </span>
                <span class="text-xs text-amber-600 dark:text-amber-400">
                    • {{ formatPrice(totalHeldAmount) }}
                </span>
            </div>
            <IconChevronDown :size="16" class="text-amber-600" />
        </button>

        <div
            v-else
            class="border-b border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-950/30"
        >
            <div
                class="flex items-center justify-between px-3 py-2 border-b border-amber-200/50 dark:border-amber-800/30"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="w-6 h-6 rounded-md bg-amber-500 flex items-center justify-center text-white text-xs font-bold"
                    >
                        {{ heldCarts.length }}
                    </div>
                    <span class="text-sm font-medium text-amber-700 dark:text-amber-300">
                        Transaksi Ditahan
                    </span>
                </div>
                <button
                    @click="isExpanded = false"
                    class="w-6 h-6 rounded flex items-center justify-center hover:bg-amber-200 dark:hover:bg-amber-900/50"
                >
                    <IconChevronUp :size="16" class="text-amber-600" />
                </button>
            </div>

            <div class="max-h-[140px] overflow-y-auto">
                <div
                    v-for="hold in heldCarts"
                    :key="hold.hold_id"
                    class="px-3 py-2 border-b border-amber-100/50 dark:border-amber-900/30 last:border-0 flex items-center justify-between gap-2"
                >
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-amber-800 dark:text-amber-200 truncate">
                            {{ hold.label }}
                        </p>
                        <p class="text-xs text-amber-600 dark:text-amber-400">
                            {{ hold.items_count }} item • {{ formatPrice(hold.total) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button
                            @click="handleResume(hold.hold_id)"
                            :disabled="resumingId === hold.hold_id || hasActiveCart"
                            class="px-2 py-1 rounded bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium disabled:opacity-50 flex items-center gap-1"
                            :title="hasActiveCart ? 'Kosongkan keranjang dulu' : 'Lanjutkan'"
                        >
                            <div
                                v-if="resumingId === hold.hold_id"
                                class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"
                            />
                            <IconPlayerPlay v-else :size="12" />
                        </button>
                        <button
                            @click="handleDelete(hold.hold_id)"
                            :disabled="deletingId === hold.hold_id"
                            class="p-1 rounded hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-600 disabled:opacity-50"
                        >
                            <IconTrash :size="12" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import {
    IconClock,
    IconPlayerPlay,
    IconTrash,
    IconChevronDown,
    IconChevronUp,
} from '@tabler/icons-vue';

const props = defineProps({
    heldCarts: {
        type: Array,
        default: () => [],
    },
    hasActiveCart: {
        type: Boolean,
        default: false,
    },
});

const toast = useToast();
const isExpanded = ref(false);
const resumingId = ref(null);
const deletingId = ref(null);

const formatPrice = (value = 0) =>
    value.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    });

const totalHeldAmount = computed(() =>
    props.heldCarts.reduce((sum, h) => sum + h.total, 0)
);

const handleResume = (holdId) => {
    if (props.hasActiveCart) {
        toast.error('Selesaikan atau tahan transaksi aktif terlebih dahulu');
        return;
    }

    resumingId.value = holdId;

    router.post(
        route('transactions.resume', holdId),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Transaksi dilanjutkan');
                resumingId.value = null;
                isExpanded.value = false;
            },
            onError: (errors) => {
                toast.error(errors.message || 'Gagal melanjutkan transaksi');
                resumingId.value = null;
            },
        }
    );
};

const handleDelete = (holdId) => {
    if (!confirm('Hapus transaksi yang ditahan ini?')) return;

    deletingId.value = holdId;

    router.delete(route('transactions.clearHold', holdId), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Transaksi dihapus');
            deletingId.value = null;
        },
        onError: () => {
            toast.error('Gagal menghapus transaksi');
            deletingId.value = null;
        },
    });
};
</script>

