<template>
    <Head title="Aplikasi Kasir - Point of Sale Modern" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                        <IconShoppingCart :size="22" class="text-white" />
                    </div>
                    <span class="text-xl font-bold text-slate-900 dark:text-white">
                        Aplikasi Kasir
                    </span>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <a
                        href="#features"
                        class="text-sm text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors"
                    >
                        Fitur
                    </a>
                    <a
                        href="#tech"
                        class="text-sm text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors"
                    >
                        Teknologi
                    </a>
                    <a
                        href="#install"
                        class="text-sm text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors"
                    >
                        Instalasi
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/login"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-primary-500 transition-colors"
                    >
                        Masuk
                    </Link>
                    <Link
                        href="/register"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl hover:from-primary-600 hover:to-primary-700 shadow-lg shadow-primary-500/25 transition-all"
                    >
                        Daftar Gratis
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="text-center max-w-4xl mx-auto">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 text-sm font-medium mb-6">
                        <IconDeviceMobile :size="16" />
                        Responsive & Mobile-Friendly
                    </div>

                    <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 dark:text-white leading-tight">
                        Sistem Point of Sale
                        <span class="block mt-2 bg-gradient-to-r from-primary-500 to-primary-600 bg-clip-text text-transparent">
                            Modern & Mudah Digunakan
                        </span>
                    </h1>

                    <p class="mt-6 text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Aplikasi kasir berbasis web untuk warung & toko kecil–menengah.
                        Mendukung pencatatan transaksi, laporan, manajemen produk,
                        pelanggan, dan banyak lagi.
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            href="/register"
                            class="w-full sm:w-auto px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl hover:from-primary-600 hover:to-primary-700 shadow-xl shadow-primary-500/30 transition-all flex items-center justify-center gap-2"
                        >
                            Mulai Sekarang
                            <IconArrowRight :size="20" />
                        </Link>
                        <a
                            href="https://github.com/aryadwiputra/point-of-sales"
                            target="_blank"
                            class="w-full sm:w-auto px-8 py-4 text-base font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-primary-300 dark:hover:border-primary-700 transition-all flex items-center justify-center gap-2"
                        >
                            <IconBrandGithub :size="20" />
                            View Repository
                        </a>
                    </div>
                </div>

                <!-- Dashboard Preview -->
                <div class="mt-16 relative">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-50 dark:from-slate-950 to-transparent z-10 pointer-events-none h-32 bottom-0 top-auto" />
                    <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-2xl bg-white dark:bg-slate-900">
                        <div class="bg-slate-100 dark:bg-slate-800 px-4 py-3 flex items-center gap-2">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-400" />
                                <div class="w-3 h-3 rounded-full bg-yellow-400" />
                                <div class="w-3 h-3 rounded-full bg-green-400" />
                            </div>
                            <div class="flex-1 text-center text-xs text-slate-500">
                                dashboard.aplikasikasir.com
                            </div>
                        </div>
                        <img
                            src="/media/revamp-pos.png"
                            alt="Preview POS Dashboard"
                            class="w-full"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Version Comparison -->
        <section class="py-20 px-6 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-900 dark:to-slate-800">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-100 dark:bg-primary-950 text-primary-600 dark:text-primary-400 text-sm font-medium mb-4">
                        <IconArrowRight :size="16" />
                        Before & After
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white">
                        Perjalanan Evolusi
                    </h2>
                    <p class="mt-4 text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Dari versi pertama hingga redesign modern dengan UI/UX yang lebih baik
                    </p>
                </div>

                <!-- Comparison Grid -->
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- V1 -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-center gap-2 text-slate-500 dark:text-slate-400">
                            <span class="px-3 py-1 bg-slate-200 dark:bg-slate-700 rounded-full text-sm font-medium">
                                Version 1.0
                            </span>
                        </div>
                        <div class="rounded-xl overflow-hidden border border-slate-300 dark:border-slate-600 shadow-lg">
                            <img src="/media/readme-pos.png" alt="POS V1" class="w-full" />
                        </div>
                        <div class="rounded-xl overflow-hidden border border-slate-300 dark:border-slate-600 shadow-lg">
                            <img src="/media/readme-dashboard.png" alt="Dashboard V1" class="w-full" />
                        </div>
                    </div>

                    <!-- Revamp -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-center gap-2">
                            <span class="px-3 py-1 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-full text-sm font-medium">
                                ✨ Revamp 2.0
                            </span>
                        </div>
                        <div class="rounded-xl overflow-hidden border-2 border-primary-500 shadow-lg shadow-primary-500/20">
                            <img src="/media/revamp-pos.png" alt="POS Revamp" class="w-full" />
                        </div>
                        <div class="rounded-xl overflow-hidden border-2 border-primary-500 shadow-lg shadow-primary-500/20">
                            <img src="/media/revamp-dashboard.png" alt="Dashboard Revamp" class="w-full" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 px-6 bg-white dark:bg-slate-900">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white">
                        Fitur Lengkap
                    </h2>
                    <p class="mt-4 text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Semua yang Anda butuhkan untuk mengelola bisnis retail dalam satu aplikasi
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="(feature, i) in features"
                        :key="i"
                        class="group p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 hover:border-primary-200 dark:hover:border-primary-800 transition-all"
                    >
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <component :is="feature.icon" :size="24" class="text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                            {{ feature.title }}
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            {{ feature.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tech Stack -->
        <section id="tech" class="py-20 px-6">
            <div class="max-w-7xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4">
                    Tech Stack
                </h2>
                <p class="text-slate-600 dark:text-slate-400 mb-12">
                    Dibangun dengan teknologi modern yang cepat dan stabil
                </p>

                <div class="flex flex-wrap justify-center gap-4">
                    <div
                        v-for="(tech, i) in techStack"
                        :key="i"
                        class="flex items-center gap-3 px-6 py-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"
                    >
                        <div :class="['w-3 h-3 rounded-full', tech.color]" />
                        <span class="font-medium text-slate-700 dark:text-slate-300">
                            {{ tech.name }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Installation -->
        <section id="install" class="py-20 px-6 bg-white dark:bg-slate-900">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white">
                        Panduan Instalasi
                    </h2>
                    <p class="mt-4 text-slate-600 dark:text-slate-400">
                        Clone repository dan jalankan dalam hitungan menit
                    </p>
                </div>

                <div class="bg-slate-900 dark:bg-slate-800 rounded-2xl p-6 overflow-hidden">
                    <pre class="text-sm text-slate-300 font-mono overflow-x-auto">
git clone https://github.com/aryadwiputra/point-of-sales
cd point-of-sales
composer install
npm install
cp .env.example .env
php artisan key:generate

# Setup database di .env

php artisan migrate --seed
php artisan storage:link
npm run dev
php artisan serve</pre>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-20 px-6">
            <div class="max-w-4xl mx-auto text-center">
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-3xl p-12 text-white">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Siap Memulai?</h2>
                    <p class="text-lg opacity-90 mb-8">
                        Daftarkan bisnis Anda sekarang dan rasakan kemudahannya
                    </p>
                    <Link
                        href="/register"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-white text-primary-600 font-semibold rounded-2xl hover:bg-slate-50 transition-colors"
                    >
                        Daftar Gratis Sekarang
                        <IconArrowRight :size="20" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-8 px-6 border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                        <IconShoppingCart :size="16" class="text-white" />
                    </div>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">
                        Aplikasi Kasir
                    </span>
                </div>
                <p class="text-sm text-slate-500">
                    © {{ new Date().getFullYear() }} Dibuat oleh Arya Dwi Putra
                </p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
    IconShoppingCart,
    IconReceipt,
    IconUsers,
    IconChartBar,
    IconBox,
    IconBrandGithub,
    IconArrowRight,
    IconDeviceMobile,
    IconReportMoney,
} from '@tabler/icons-vue';

const features = [
    {
        icon: IconShoppingCart,
        title: 'Transaksi Cepat',
        desc: 'Proses jual beli dalam hitungan detik',
    },
    {
        icon: IconReceipt,
        title: 'Cetak Struk',
        desc: 'Print thermal 58mm, 80mm, dan invoice',
    },
    {
        icon: IconUsers,
        title: 'Pelanggan & History',
        desc: 'Kelola data pelanggan dan riwayat',
    },
    {
        icon: IconBox,
        title: 'Inventori Produk',
        desc: 'Stok, kategori, dan barcode scanner',
    },
    {
        icon: IconChartBar,
        title: 'Laporan Lengkap',
        desc: 'Penjualan, keuntungan, dan grafik',
    },
    {
        icon: IconReportMoney,
        title: 'Multi Payment',
        desc: 'Tunai, QRIS, dan Midtrans',
    },
];

const techStack = [
    { name: 'Laravel 12', color: 'bg-red-500' },
    { name: 'Inertia.js', color: 'bg-purple-500' },
    { name: 'Vue 3', color: 'bg-cyan-500' },
    { name: 'TailwindCSS', color: 'bg-sky-500' },
    { name: 'MySQL', color: 'bg-orange-500' },
];
</script>

