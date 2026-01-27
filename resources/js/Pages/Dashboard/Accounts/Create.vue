<template>
    <DashboardLayout>
        <Head title="Tambah Akun" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tambah Akun</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Tambah akun baru ke Chart of Accounts
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50"
                    label="Kembali"
                    :href="route('accounts.index')"
                />
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Kode Akun <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.code"
                            type="text"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                            placeholder="Contoh: 1-1100"
                            required
                        />
                        <p v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Nama Akun <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                            placeholder="Nama akun"
                            required
                        />
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Tipe Akun <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.type"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                            required
                        >
                            <option value="">Pilih Tipe</option>
                            <option value="asset">Aset</option>
                            <option value="liability">Kewajiban</option>
                            <option value="equity">Ekuitas</option>
                            <option value="revenue">Pendapatan</option>
                            <option value="expense">Beban</option>
                        </select>
                        <p v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Parent Akun
                        </label>
                        <select
                            v-model="form.parent_id"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                        >
                            <option value="">Tidak Ada (Root)</option>
                            <option v-for="acc in parentAccounts" :key="acc.id" :value="acc.id">
                                {{ acc.code }} - {{ acc.name }}
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Deskripsi
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                            placeholder="Deskripsi akun (opsional)"
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <Button
                        type="link"
                        label="Batal"
                        class="bg-slate-100 text-slate-600 hover:bg-slate-200"
                        :href="route('accounts.index')"
                    />
                    <Button
                        type="submit"
                        :icon="IconCheck"
                        label="Simpan"
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
import { IconArrowLeft, IconCheck } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    parentAccounts: Array,
});

const form = useForm({
    code: '',
    name: '',
    type: '',
    parent_id: '',
    description: '',
});

const submit = () => {
    form.post(route('accounts.store'));
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
