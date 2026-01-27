<template>
    <DashboardLayout>
        <Head title="Tambah Pengguna" />

        <div class="mb-6">
            <Link
                :href="route('users.index')"
                class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary-600 mb-3"
            >
                <IconArrowLeft :size="16" />
                Kembali ke Pengguna
            </Link>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <IconUserPlus :size="28" class="text-primary-500" />
                Tambah Pengguna Baru
            </h1>
        </div>

        <form @submit.prevent="submit">
            <div class="max-w-2xl space-y-6">
                <!-- Account Info -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">
                        Informasi Akun
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input
                            type="text"
                            label="Nama Lengkap"
                            placeholder="Masukkan nama"
                            v-model="form.name"
                            :errors="errors.name"
                        />
                        <Input
                            type="email"
                            label="Email"
                            placeholder="email@example.com"
                            v-model="form.email"
                            :errors="errors.email"
                        />
                        <Input
                            type="password"
                            label="Kata Sandi"
                            placeholder="Minimal 8 karakter"
                            v-model="form.password"
                            :errors="errors.password"
                        />
                        <Input
                            type="password"
                            label="Konfirmasi Kata Sandi"
                            placeholder="Ulangi kata sandi"
                            v-model="form.password_confirmation"
                            :errors="errors.password_confirmation"
                        />
                    </div>
                </div>

                <!-- Roles -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                        <IconShield :size="16" />
                        Akses Group
                    </h3>
                    <div class="flex flex-wrap gap-4">
                        <label
                            v-for="(role, i) in roles"
                            :key="i"
                            :class="[
                                'flex items-center gap-2.5 px-4 py-3 rounded-xl border cursor-pointer transition-all',
                                form.selectedRoles.includes(role.name)
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/50'
                                    : 'border-slate-200 dark:border-slate-700 hover:border-primary-300',
                            ]"
                        >
                            <Checkbox
                                :value="role.name"
                                @change="setSelectedRoles"
                                :checked="form.selectedRoles.includes(role.name)"
                            />
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300 capitalize">
                                {{ role.name }}
                            </span>
                        </label>
                    </div>
                    <p v-if="errors.selectedRoles" class="text-xs text-danger-500 mt-3">
                        {{ errors.selectedRoles }}
                    </p>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3">
                    <Link
                        :href="route('users.index')"
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
        </form>
    </DashboardLayout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import {
    IconUserPlus,
    IconDeviceFloppy,
    IconArrowLeft,
    IconShield,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Input from '@/Components/Dashboard/Input.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';

const { roles, errors } = usePage().props;
const toast = useToast();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    selectedRoles: [],
});

const setSelectedRoles = (e) => {
    let items = [...form.selectedRoles];
    if (items.includes(e.target.value)) {
        items = items.filter((name) => name !== e.target.value);
    } else {
        items.push(e.target.value);
    }
    form.selectedRoles = items;
};

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => toast.success('Pengguna berhasil ditambahkan'),
        onError: () => toast.error('Gagal menyimpan pengguna'),
    });
};
</script>


