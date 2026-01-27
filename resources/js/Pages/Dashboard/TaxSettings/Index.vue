<template>
    <DashboardLayout>
        <Head title="Pengaturan Pajak" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pengaturan Pajak</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Kelola pengaturan pajak global untuk sistem
            </p>
        </div>

        <div class="max-w-2xl">
            <form @submit.prevent="submit">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <!-- Tax Toggle -->
                    <div class="flex items-center justify-between mb-6 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <div>
                            <h3 class="font-semibold text-slate-800 dark:text-white">Aktifkan Pajak</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Jika diaktifkan, pajak akan dihitung pada transaksi
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.tax_enabled"
                                class="sr-only peer"
                            />
                            <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-slate-600 peer-checked:bg-primary-500"></div>
                        </label>
                    </div>

                    <!-- Settings (shown only when enabled) -->
                    <div v-if="form.tax_enabled" class="space-y-6">
                        <!-- Default Tax -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Pajak Default
                            </label>
                            <select
                                v-model="form.default_tax_id"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="">Pilih Pajak Default</option>
                                <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                                    {{ tax.code }} - {{ tax.name }} ({{ tax.rate }}%)
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">
                                Pajak ini akan digunakan secara default untuk semua transaksi
                            </p>
                        </div>

                        <!-- Show on Receipt -->
                        <div class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                v-model="form.show_tax_on_receipt"
                                id="show_receipt"
                                class="w-4 h-4 text-primary-500 border-slate-300 rounded focus:ring-primary-500"
                            />
                            <label for="show_receipt" class="text-sm text-slate-700 dark:text-slate-300">
                                Tampilkan detail pajak pada struk
                            </label>
                        </div>

                        <!-- Info Box -->
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                <strong>Info:</strong> Jika belum ada pajak, silakan tambahkan di menu
                                <a :href="route('taxes.index')" class="underline font-medium">Master Pajak</a> terlebih dahulu.
                            </p>
                        </div>
                    </div>

                    <!-- Disabled Info -->
                    <div v-else class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            <strong>Pajak dinonaktifkan.</strong> Semua transaksi tidak akan dikenakan pajak.
                        </p>
                    </div>

                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl disabled:opacity-50"
                        >
                            Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    settings: Object,
    taxes: Array,
    accounts: Array,
});

const form = useForm({
    tax_enabled: props.settings?.tax_enabled ?? false,
    default_tax_id: props.settings?.default_tax_id ?? '',
    show_tax_on_receipt: props.settings?.show_tax_on_receipt ?? true,
});

const submit = () => {
    form.post(route('tax-settings.update'));
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
