<template>
    <DashboardLayout>
        <Head title="Edit Satuan" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Satuan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ unit.code }} - {{ unit.name }}</p>
        </div>

        <div class="max-w-xl">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Kode <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.code"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            />
                            <span v-if="form.errors.code" class="text-sm text-red-500">{{ form.errors.code }}</span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Nama <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            />
                            <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Deskripsi
                            </label>
                            <input
                                v-model="form.description"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                v-model="form.is_active"
                                id="is_active"
                                class="w-4 h-4 text-primary-500 border-slate-300 rounded focus:ring-primary-500"
                            />
                            <label for="is_active" class="text-sm text-slate-700 dark:text-slate-300">Aktif</label>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg disabled:opacity-50"
                        >
                            Simpan
                        </button>
                        <a
                            :href="route('units.index')"
                            class="px-6 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg"
                        >
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    unit: Object,
});

const form = useForm({
    code: props.unit.code,
    name: props.unit.name,
    description: props.unit.description || '',
    is_active: props.unit.is_active,
});

const submit = () => {
    form.put(route('units.update', props.unit.id));
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
