<template>
    <div>
        <button
            @click="showHistory = true"
            class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-primary-500 transition-colors"
            title="Lihat riwayat"
        >
            <IconHistory :size="16" />
        </button>

        <Teleport to="body">
            <div
                v-if="showHistory"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            >
                <div class="w-full max-w-sm animate-in zoom-in-95 duration-200">
                    <CustomerHistoryPanel
                        :customer-id="customerId"
                        :customer-name="customerName"
                        @close="showHistory = false"
                    />
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { IconHistory } from '@tabler/icons-vue';
import CustomerHistoryPanel from './CustomerHistoryPanel.vue';

const props = defineProps({
    customerId: [Number, String],
    customerName: String,
});

const showHistory = ref(false);
</script>

