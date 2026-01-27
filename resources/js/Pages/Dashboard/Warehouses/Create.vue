<template>
    <DashboardLayout>
        <Head title="Tambah Gudang" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tambah Gudang</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Isi form berikut untuk menambahkan gudang baru.
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    label="Kembali"
                    :href="route('warehouses.index')"
                />
            </div>
        </div>

        <div class="max-w-2xl">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <Input
                            label="Nama Gudang"
                            placeholder="Masukkan nama gudang"
                            v-model="form.name"
                            :message="form.errors.name"
                        />
                        
                        <Input
                             label="Lokasi"
                             placeholder="Lokasi gudang"
                             v-model="form.location"
                             :message="form.errors.location"
                         />

                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-900 dark:text-white">Deskripsi</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full p-2.5 bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-slate-800 dark:border-slate-700 dark:placeholder-slate-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 transition-all duration-300"
                                placeholder="Keterangan tambahan"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-2 text-sm text-red-600 dark:text-red-500">
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <Button
                            type="button"
                            label="Batal"
                            class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="router.visit(route('warehouses.index'))"
                        />
                        <Button
                            type="submit"
                            label="Simpan"
                            class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                            :processing="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { IconArrowLeft } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Input from '@/Components/Dashboard/Input.vue';

const form = useForm({
    name: '',
    location: '',
    description: '',
});

const submit = () => {
    form.post(route('warehouses.store'));
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>

