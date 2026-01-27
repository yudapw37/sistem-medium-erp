<template>
    <div ref="containerRef" class="relative">
        <label v-if="label" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            {{ label }}
        </label>

        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="isOpen = !isOpen"
                :class="[
                    'flex-1 h-12 px-4 rounded-xl text-left flex items-center gap-3 border-2 transition-all duration-200 bg-white dark:bg-slate-900',
                    isOpen
                        ? 'border-primary-500 ring-4 ring-primary-500/20'
                        : error
                          ? 'border-danger-500'
                          : 'border-slate-200 dark:border-slate-700',
                ]"
            >
                <div
                    :class="[
                        'w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0',
                        selected
                            ? 'bg-primary-100 dark:bg-primary-900/50'
                            : 'bg-slate-100 dark:bg-slate-800',
                    ]"
                >
                    <IconUser
                        :size="18"
                        :class="
                            selected
                                ? 'text-primary-600 dark:text-primary-400'
                                : 'text-slate-400'
                        "
                    />
                </div>
                <div class="flex-1 min-w-0">
                    <template v-if="selected">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">
                            {{ selected.name }}
                        </p>
                        <p v-if="selected.phone" class="text-xs text-slate-500 dark:text-slate-400 truncate">
                            {{ selected.phone }}
                        </p>
                    </template>
                    <p v-else class="text-sm text-slate-400 dark:text-slate-500">{{ placeholder }}</p>
                </div>
                <IconChevronDown
                    :size="18"
                    :class="['text-slate-400 transition-transform', isOpen ? 'rotate-180' : '']"
                />
            </button>

            <CustomerHistoryButton
                v-if="selected"
                :customer-id="selected.id"
                :customer-name="selected.name"
            />

            <button
                type="button"
                @click="showAddModal = true"
                class="h-12 w-12 rounded-xl border-2 border-dashed border-primary-300 dark:border-primary-700 text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-950/30 flex items-center justify-center transition-colors"
                title="Tambah pelanggan baru"
            >
                <IconUserPlus :size="20" />
            </button>
        </div>

        <p v-if="error" class="mt-1 text-xs text-danger-500">{{ error }}</p>

        <div
            v-if="isOpen"
            class="absolute top-full left-0 right-0 mt-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xl z-50 animate-slide-up overflow-hidden"
        >
            <div class="p-3 border-b border-slate-100 dark:border-slate-800">
                <div class="relative">
                    <IconSearch
                        :size="18"
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        ref="inputRef"
                        type="text"
                        v-model="search"
                        placeholder="Cari nama/telepon..."
                        class="w-full h-10 pl-10 pr-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                    />
                </div>
            </div>

            <div class="max-h-60 overflow-y-auto scrollbar-thin">
                <ul v-if="filteredCustomers.length > 0">
                    <li v-for="customer in filteredCustomers" :key="customer.id">
                        <button
                            type="button"
                            @click="handleSelect(customer)"
                            :class="[
                                'w-full flex items-center gap-3 px-4 py-3 text-left transition-colors',
                                selected?.id === customer.id
                                    ? 'bg-primary-50 dark:bg-primary-950/30'
                                    : 'hover:bg-slate-50 dark:hover:bg-slate-800',
                            ]"
                        >
                            <div
                                :class="[
                                    'w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0',
                                    selected?.id === customer.id
                                        ? 'bg-primary-500 text-white'
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-500',
                                ]"
                            >
                                <IconCheck v-if="selected?.id === customer.id" :size="16" />
                                <span v-else class="text-sm font-medium">
                                    {{ customer.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">
                                    {{ customer.name }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                    {{ customer.phone || customer.email || '-' }}
                                </p>
                            </div>
                        </button>
                    </li>
                </ul>
                <div v-else class="py-8 text-center text-slate-400 dark:text-slate-500">
                    <IconUser :size="24" class="mx-auto mb-2 opacity-50" />
                    <p class="text-sm">Pelanggan tidak ditemukan</p>
                    <button
                        type="button"
                        @click="
                            isOpen = false;
                            showAddModal = true;
                        "
                        class="mt-2 text-sm text-primary-500 hover:text-primary-600 font-medium"
                    >
                        + Tambah pelanggan baru
                    </button>
                </div>
            </div>
        </div>

        <AddCustomerModal
            :is-open="showAddModal"
            @close="showAddModal = false"
            @success="handleAddCustomerSuccess"
        />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    IconUser,
    IconSearch,
    IconCheck,
    IconChevronDown,
    IconUserPlus,
} from '@tabler/icons-vue';
import AddCustomerModal from './AddCustomerModal.vue';
import CustomerHistoryButton from './CustomerHistoryButton.vue';

const props = defineProps({
    customers: {
        type: Array,
        default: () => [],
    },
    selected: Object,
    placeholder: {
        type: String,
        default: 'Pilih pelanggan...',
    },
    error: String,
    label: String,
    onCustomerAdded: Function,
});

const emit = defineEmits(['update:selected']);

const isOpen = ref(false);
const search = ref('');
const showAddModal = ref(false);
const containerRef = ref(null);
const inputRef = ref(null);

const filteredCustomers = computed(() => {
    return props.customers.filter(
        (customer) =>
            customer.name.toLowerCase().includes(search.value.toLowerCase()) ||
            customer.phone?.toLowerCase().includes(search.value.toLowerCase())
    );
});

const handleClickOutside = (event) => {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});

watch(isOpen, (newVal) => {
    if (newVal && inputRef.value) {
        inputRef.value.focus();
    }
});

const handleSelect = (customer) => {
    emit('update:selected', customer);
    isOpen.value = false;
    search.value = '';
};

const handleAddCustomerSuccess = (newCustomer) => {
    showAddModal.value = false;
    router.reload({ only: ['customers'] });
    props.onCustomerAdded?.(newCustomer);
};
</script>

