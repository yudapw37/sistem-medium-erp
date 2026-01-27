<template>
    <DashboardLayout>
        <Head title="Pengaturan Payment" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <IconCreditCard :size="28" class="text-primary-500" />
                Pengaturan Payment Gateway
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Konfigurasi metode pembayaran dan gateway
            </p>
        </div>

        <form @submit.prevent="handleSubmit" class="max-w-3xl space-y-6">
            <!-- Default Gateway -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                    <IconCash :size="18" />
                    Gateway Default
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                    Gateway pembayaran default yang digunakan kasir saat membuka halaman transaksi.
                </p>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Pilih Gateway
                    </label>
                    <select
                        v-model="form.default_gateway"
                        class="w-full h-11 px-4 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                    >
                        <option
                            v-for="gw in supportedGateways"
                            :key="gw.value"
                            :value="gw.value"
                            :disabled="!isGatewaySelectable(gw.value)"
                        >
                            {{ gw.label }}{{ !isGatewaySelectable(gw.value) ? ' (nonaktif)' : '' }}
                        </option>
                    </select>
                    <small v-if="errors?.default_gateway" class="text-xs text-danger-500 mt-1">
                        {{ errors.default_gateway }}
                    </small>
                </div>
            </div>

            <!-- Midtrans -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <IconBrandStripe :size="18" />
                        Midtrans Snap
                    </h3>
                    <label
                        :class="[
                            'flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium cursor-pointer transition-all',
                            form.midtrans_enabled
                                ? 'bg-success-100 dark:bg-success-900/50 text-success-700 dark:text-success-400'
                                : 'bg-slate-100 dark:bg-slate-800 text-slate-500',
                        ]"
                    >
                        <Checkbox v-model="form.midtrans_enabled" />
                        {{ form.midtrans_enabled ? 'Aktif' : 'Nonaktif' }}
                    </label>
                </div>
                <div :class="['space-y-4', !form.midtrans_enabled ? 'opacity-50 pointer-events-none' : '']">
                    <div class="grid gap-4 md:grid-cols-2">
                        <Input
                            label="Server Key"
                            type="text"
                            v-model="form.midtrans_server_key"
                            :errors="errors?.midtrans_server_key"
                            placeholder="SB-Mid-server-xxx"
                        />
                        <Input
                            label="Client Key"
                            type="text"
                            v-model="form.midtrans_client_key"
                            :errors="errors?.midtrans_client_key"
                            placeholder="SB-Mid-client-xxx"
                        />
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <Checkbox v-model="form.midtrans_production" />
                        <span class="text-sm text-slate-600 dark:text-slate-400">Mode Produksi</span>
                    </label>
                </div>
            </div>

            <!-- Xendit -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <IconCreditCard :size="18" />
                        Xendit Invoice
                    </h3>
                    <label
                        :class="[
                            'flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium cursor-pointer transition-all',
                            form.xendit_enabled
                                ? 'bg-success-100 dark:bg-success-900/50 text-success-700 dark:text-success-400'
                                : 'bg-slate-100 dark:bg-slate-800 text-slate-500',
                        ]"
                    >
                        <Checkbox v-model="form.xendit_enabled" />
                        {{ form.xendit_enabled ? 'Aktif' : 'Nonaktif' }}
                    </label>
                </div>
                <div :class="['space-y-4', !form.xendit_enabled ? 'opacity-50 pointer-events-none' : '']">
                    <div class="grid gap-4 md:grid-cols-2">
                        <Input
                            label="Secret Key"
                            type="text"
                            v-model="form.xendit_secret_key"
                            :errors="errors?.xendit_secret_key"
                            placeholder="xnd_development_xxx"
                        />
                        <Input
                            label="Public Key"
                            type="text"
                            v-model="form.xendit_public_key"
                            :errors="errors?.xendit_public_key"
                            placeholder="xnd_public_development_xxx"
                        />
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <Checkbox v-model="form.xendit_production" />
                        <span class="text-sm text-slate-600 dark:text-slate-400">Mode Produksi</span>
                    </label>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-medium transition-colors disabled:opacity-50"
                >
                    <IconDeviceFloppy :size="18" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Konfigurasi' }}
                </button>
            </div>
        </form>
    </DashboardLayout>
</template>

<script setup>
import { watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import {
    IconCreditCard,
    IconDeviceFloppy,
    IconBrandStripe,
    IconCash,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Input from '@/Components/Dashboard/Input.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';

const props = defineProps({
    setting: Object,
    supportedGateways: {
        type: Array,
        default: () => [],
    },
});

const { flash, errors } = usePage().props;
const toast = useToast();

const form = useForm({
    default_gateway: props.setting?.default_gateway ?? 'cash',
    midtrans_enabled: props.setting?.midtrans_enabled ?? false,
    midtrans_server_key: props.setting?.midtrans_server_key ?? '',
    midtrans_client_key: props.setting?.midtrans_client_key ?? '',
    midtrans_production: props.setting?.midtrans_production ?? false,
    xendit_enabled: props.setting?.xendit_enabled ?? false,
    xendit_secret_key: props.setting?.xendit_secret_key ?? '',
    xendit_public_key: props.setting?.xendit_public_key ?? '',
    xendit_production: props.setting?.xendit_production ?? false,
});

watch(
    () => flash,
    (newFlash) => {
        if (newFlash?.success) toast.success(newFlash.success);
        if (newFlash?.error) toast.error(newFlash.error);
    },
    { deep: true }
);

const isGatewaySelectable = (gateway) => {
    if (gateway === 'cash') return true;
    if (gateway === 'midtrans') return form.midtrans_enabled;
    if (gateway === 'xendit') return form.xendit_enabled;
    return false;
};

const handleSubmit = () => {
    form.put(route('settings.payments.update'), { preserveScroll: true });
};
</script>


