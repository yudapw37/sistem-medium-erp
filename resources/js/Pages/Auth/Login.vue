<template>
    <Head title="Masuk" />

    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950">
        <!-- Left - Form -->
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
                        Selamat Datang Kembali
                    </h1>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">
                        Masuk untuk mengakses dashboard Anda
                    </p>
                </div>

                <!-- Status Message -->
                <div
                    v-if="status"
                    class="mb-6 p-4 rounded-xl bg-success-50 dark:bg-success-950/50 text-success-700 dark:text-success-400 text-sm"
                >
                    {{ status }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
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
                                placeholder="••••••••"
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

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-primary-500 focus:ring-primary-500"
                            />
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                Ingat saya
                            </span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-primary-500 hover:text-primary-600 font-medium"
                        >
                            Lupa Password?
                        </Link>
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
                        <span v-else>Masuk</span>
                    </button>

                    <!-- Register Link -->
                    <p class="text-center text-sm text-slate-600 dark:text-slate-400">
                        Belum punya akun?
                        <Link
                            href="/register"
                            class="text-primary-500 hover:text-primary-600 font-semibold"
                        >
                            Daftar Sekarang
                        </Link>
                    </p>
                </form>
            </div>
        </div>

        <!-- Right - Image/Decoration -->
        <div class="hidden lg:flex flex-1 bg-gradient-to-br from-primary-500 to-primary-700 items-center justify-center p-12">
            <div class="max-w-md text-center text-white">
                <div class="w-24 h-24 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-8">
                    <IconShoppingCart :size="48" />
                </div>
                <h2 class="text-3xl font-bold mb-4">Kelola Bisnis Anda dengan Mudah</h2>
                <p class="text-lg opacity-90">
                    Sistem Medium ERP modern yang membantu Anda mengelola transaksi,
                    inventori, dan laporan keuangan dengan efisien.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <span
                        v-for="(feature, i) in features"
                        :key="i"
                        class="px-4 py-2 bg-white/20 rounded-full text-sm font-medium"
                    >
                        {{ feature }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import {
    IconShoppingCart,
    IconMail,
    IconLock,
    IconEye,
    IconEyeOff,
    IconLoader2,
} from '@tabler/icons-vue';

defineProps({
    status: String,
    canResetPassword: Boolean,
});

const { errors } = usePage().props;
const toast = useToast();
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const features = ['Transaksi Cepat', 'Laporan Real-time', 'Multi User'];

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};

const submit = () => {
    form.post(route('login'), {
        onError: (errors) => {
            // Show error notification
            if (errors.email) {
                toast.error(errors.email);
            } else if (errors.password) {
                toast.error(errors.password);
            } else {
                toast.error('Email atau password salah');
            }
        },
    });
};

onUnmounted(() => {
    form.reset('password');
});
</script>

