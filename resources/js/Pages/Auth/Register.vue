<template>
    <Head title="Daftar" />

    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950">
        <!-- Left - Decoration -->
        <div class="hidden lg:flex flex-1 bg-gradient-to-br from-primary-500 to-primary-700 items-center justify-center p-12">
            <div class="max-w-md text-center text-white">
                <div class="w-24 h-24 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-8">
                    <IconShoppingCart :size="48" />
                </div>
                <h2 class="text-3xl font-bold mb-4">Bergabung Bersama Kami</h2>
                <p class="text-lg opacity-90">
                    Mulai kelola bisnis Anda dengan sistem medium ERP yang modern, cepat, dan mudah digunakan.
                </p>
                <div class="mt-8 space-y-3">
                    <div
                        v-for="(feature, i) in features"
                        :key="i"
                        class="flex items-center justify-center gap-2 text-sm font-medium"
                    >
                        <IconCheck :size="18" class="text-white/80" />
                        {{ feature }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right - Form -->
        <div class="flex-1 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="mb-8">
                    <Link href="/" class="inline-flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                            <IconShoppingCart :size="24" class="text-white" />
                        </div>
                        <span class="text-2xl font-bold text-slate-900 dark:text-white">
                            Aplikasi Medium ERP
                        </span>
                    </Link>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white">
                        Buat Akun Baru
                    </h1>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">
                        Daftarkan bisnis Anda sekarang
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Nama Lengkap
                        </label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <IconUser :size="20" />
                            </div>
                            <input
                                type="text"
                                v-model="form.name"
                                placeholder="Nama Anda"
                                :class="[
                                    'w-full h-12 pl-12 pr-4 rounded-xl border-2 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-4 focus:ring-primary-500/20 transition-all',
                                    errors.name
                                        ? 'border-danger-500 focus:border-danger-500'
                                        : 'border-slate-200 dark:border-slate-700 focus:border-primary-500',
                                ]"
                            />
                        </div>
                        <p v-if="errors.name" class="mt-1.5 text-sm text-danger-500">
                            {{ errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <IconMail :size="20" />
                            </div>
                            <input
                                type="email"
                                v-model="form.email"
                                placeholder="nama@email.com"
                                :class="[
                                    'w-full h-12 pl-12 pr-4 rounded-xl border-2 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-4 focus:ring-primary-500/20 transition-all',
                                    errors.email
                                        ? 'border-danger-500 focus:border-danger-500'
                                        : 'border-slate-200 dark:border-slate-700 focus:border-primary-500',
                                ]"
                            />
                        </div>
                        <p v-if="errors.email" class="mt-1.5 text-sm text-danger-500">
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <IconLock :size="20" />
                            </div>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                placeholder="Minimal 8 karakter"
                                :class="[
                                    'w-full h-12 pl-12 pr-12 rounded-xl border-2 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-4 focus:ring-primary-500/20 transition-all',
                                    errors.password
                                        ? 'border-danger-500 focus:border-danger-500'
                                        : 'border-slate-200 dark:border-slate-700 focus:border-primary-500',
                                ]"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <IconEyeOff v-if="showPassword" :size="20" />
                                <IconEye v-else :size="20" />
                            </button>
                        </div>
                        <p v-if="errors.password" class="mt-1.5 text-sm text-danger-500">
                            {{ errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <IconLock :size="20" />
                            </div>
                            <input
                                :type="showConfirmPassword ? 'text' : 'password'"
                                v-model="form.password_confirmation"
                                placeholder="Ulangi password"
                                :class="[
                                    'w-full h-12 pl-12 pr-12 rounded-xl border-2 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-4 focus:ring-primary-500/20 transition-all',
                                    errors.password_confirmation
                                        ? 'border-danger-500 focus:border-danger-500'
                                        : 'border-slate-200 dark:border-slate-700 focus:border-primary-500',
                                ]"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <IconEyeOff v-if="showConfirmPassword" :size="20" />
                                <IconEye v-else :size="20" />
                            </button>
                        </div>
                        <p v-if="errors.password_confirmation" class="mt-1.5 text-sm text-danger-500">
                            {{ errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-12 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold hover:from-primary-600 hover:to-primary-700 focus:ring-4 focus:ring-primary-500/30 disabled:opacity-50 transition-all flex items-center justify-center gap-2"
                    >
                        <IconLoader2
                            v-if="form.processing"
                            :size="20"
                            class="animate-spin"
                        />
                        <span v-if="form.processing">Memproses...</span>
                        <span v-else>Daftar Sekarang</span>
                    </button>

                    <!-- Login Link -->
                    <p class="text-center text-sm text-slate-600 dark:text-slate-400">
                        Sudah punya akun?
                        <Link
                            href="/login"
                            class="text-primary-500 hover:text-primary-600 font-semibold"
                        >
                            Masuk disini
                        </Link>
                    </p>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    IconShoppingCart,
    IconUser,
    IconMail,
    IconLock,
    IconEye,
    IconEyeOff,
    IconLoader2,
    IconCheck,
} from '@tabler/icons-vue';

const { errors } = usePage().props;

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const features = ['Gratis untuk memulai', 'Setup dalam 5 menit', 'Dukungan penuh'];

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};

const submit = () => {
    form.post(route('register'));
};

onUnmounted(() => {
    form.reset('password', 'password_confirmation');
});
</script>

