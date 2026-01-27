<template>
    <DashboardLayout>
        <Head title="Tambah Pelanggan" />

        <div class="mb-6">
            <Link
                :href="route('customers.index')"
                class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary-600 mb-3"
            >
                <IconArrowLeft :size="16" />
                Kembali ke Pelanggan
            </Link>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <IconUsers :size="28" class="text-primary-500" />
                Tambah Pelanggan Baru
            </h1>
        </div>

        <form @submit.prevent="submit">
            <div class="max-w-2xl">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <Input
                                type="text"
                                label="Nama Pelanggan"
                                placeholder="Masukkan nama lengkap"
                                :errors="errors.name"
                                v-model="form.name"
                            />
                            <Input
                                type="text"
                                label="No. Handphone"
                                placeholder="08xxxxxxxxxx"
                                :errors="errors.no_telp"
                                v-model="form.no_telp"
                            />
                        </div>
                        <Textarea
                            label="Alamat"
                            placeholder="Alamat lengkap pelanggan"
                            :errors="errors.address"
                            v-model="form.address"
                            :rows="3"
                        />

                        <div class="flex items-center gap-2 mt-2">
                            <input
                                type="checkbox"
                                id="is_member"
                                v-model="form.is_member"
                                class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                            />
                            <label for="is_member" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                                Daftarkan sebagai Member
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                        <Link
                            :href="route('customers.index')"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-medium transition-colors disabled:opacity-50"
                        >
                            <IconDeviceFloppy :size="18" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </DashboardLayout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import {
    IconUsers,
    IconDeviceFloppy,
    IconArrowLeft,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Input from '@/Components/Dashboard/Input.vue';
import Textarea from '@/Components/Dashboard/TextArea.vue';

const { errors } = usePage().props;
const toast = useToast();

const form = useForm({
    name: '',
    no_telp: '',
    address: '',
    is_member: false,
});

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => toast.success('Pelanggan berhasil ditambahkan'),
        onError: () => toast.error('Gagal menyimpan pelanggan'),
    });
};
</script>


