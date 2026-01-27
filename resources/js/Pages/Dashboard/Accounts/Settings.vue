<template>
    <DashboardLayout>
        <Head title="Setting Akun" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Setting Akun Default</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Mapping akun untuk jurnal otomatis
                    </p>
                </div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <form @submit.prevent="submit">
                <div class="space-y-6">
                    <!-- Penjualan Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <IconShoppingCart :size="20" class="text-purple-500" />
                            Penjualan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <SettingItem
                                v-for="setting in salesSettings"
                                :key="setting.key"
                                :setting="setting"
                                :accounts="accounts"
                                @update="updateSetting"
                            />
                        </div>
                    </div>

                    <hr class="border-slate-200 dark:border-slate-700" />

                    <!-- Pendapatan Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <IconTrendingUp :size="20" class="text-green-500" />
                            Pendapatan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <SettingItem
                                v-for="setting in revenueSettings"
                                :key="setting.key"
                                :setting="setting"
                                :accounts="accounts"
                                @update="updateSetting"
                            />
                        </div>
                    </div>

                    <hr class="border-slate-200 dark:border-slate-700" />

                    <!-- Beban Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <IconTrendingDown :size="20" class="text-red-500" />
                            Beban
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <SettingItem
                                v-for="setting in expenseSettings"
                                :key="setting.key"
                                :setting="setting"
                                :accounts="accounts"
                                @update="updateSetting"
                            />
                        </div>
                    </div>

                    <hr class="border-slate-200 dark:border-slate-700" />

                    <!-- Aset Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <IconWallet :size="20" class="text-blue-500" />
                            Aset
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <SettingItem
                                v-for="setting in assetSettings"
                                :key="setting.key"
                                :setting="setting"
                                :accounts="accounts"
                                @update="updateSetting"
                            />
                        </div>
                    </div>

                    <hr class="border-slate-200 dark:border-slate-700" />

                    <!-- Kewajiban Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <IconReceipt :size="20" class="text-orange-500" />
                            Kewajiban
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <SettingItem
                                v-for="setting in liabilitySettings"
                                :key="setting.key"
                                :setting="setting"
                                :accounts="accounts"
                                @update="updateSetting"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <Button
                        type="submit"
                        :icon="IconCheck"
                        label="Simpan Perubahan"
                        class="bg-primary-500 hover:bg-primary-600 text-white"
                        :disabled="form.processing"
                    />
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h } from 'vue';
import { IconTrendingUp, IconTrendingDown, IconWallet, IconReceipt, IconCheck, IconShoppingCart } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    settings: Array,
    accounts: Array,
});

const form = useForm({
    settings: props.settings.map(s => ({
        key: s.key,
        account_id: s.account_id,
    })),
});

const revenueSettings = computed(() => 
    props.settings.filter(s => ['sales', 'sales_discount', 'sales_return'].includes(s.key))
);

const salesSettings = computed(() => 
    props.settings.filter(s => ['sales_cash', 'sales_credit'].includes(s.key))
);

const expenseSettings = computed(() => 
    props.settings.filter(s => ['cogs', 'purchase', 'purchase_return'].includes(s.key))
);

const assetSettings = computed(() => 
    props.settings.filter(s => ['cash', 'bank', 'accounts_receivable', 'inventory'].includes(s.key))
);

const liabilitySettings = computed(() => 
    props.settings.filter(s => ['accounts_payable'].includes(s.key))
);

const updateSetting = (key, accountId) => {
    const idx = form.settings.findIndex(s => s.key === key);
    if (idx !== -1) {
        form.settings[idx].account_id = accountId;
    }
};

const submit = () => {
    form.post(route('account-settings.update'));
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};

// SettingItem component
const SettingItem = {
    name: 'SettingItem',
    props: {
        setting: Object,
        accounts: Array,
    },
    emits: ['update'],
    setup(props, { emit }) {
        return () => h('div', { class: 'bg-slate-50 dark:bg-slate-800 rounded-xl p-4' }, [
            h('label', { class: 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2' }, props.setting.name),
            h('select', {
                class: 'w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm',
                value: props.setting.account_id || '',
                onChange: (e) => emit('update', props.setting.key, e.target.value || null),
            }, [
                h('option', { value: '' }, '-- Pilih Akun --'),
                ...props.accounts.map(acc => 
                    h('option', { 
                        key: acc.id, 
                        value: acc.id,
                        selected: props.setting.account_id === acc.id
                    }, `${acc.code} - ${acc.name}`)
                )
            ]),
            props.setting.description && h('p', { class: 'text-xs text-slate-500 mt-1' }, props.setting.description),
        ]);
    }
};
</script>
