<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { 
    IconHelp, 
    IconArrowRight, 
    IconDownload, 
    IconRefresh, 
    IconPackage, 
    IconShoppingCart, 
    IconChartBar,
    IconDatabase,
    IconCalendarEvent,
    IconRepeat
} from '@tabler/icons-vue';

const purchaseFlow = [
    { 
        title: 'Input / Import', 
        desc: 'Data awal di-import dari PDF atau Excel ke tabel "Old Purchase".',
        icon: IconDownload,
        color: 'blue'
    },
    { 
        title: 'Resume Bulanan', 
        desc: 'Data dikumpulkan per bulan di "Resume Old Purchase". Klik "Sync" untuk memindahkan ke Aktif.',
        icon: IconCalendarEvent,
        color: 'indigo'
    },
    { 
        title: 'Old Purchase Aktif', 
        desc: 'Data yang sudah masuk ke "Aktif" siap untuk di-finalisasi (masuk ke stok).',
        icon: IconRefresh,
        color: 'violet'
    },
    { 
        title: 'Sync Stock (Final)', 
        desc: 'Klik "Sync Stock" untuk menambah qty ke "Stock Running" dan mengunci status jadi FINAL.',
        icon: IconPackage,
        color: 'emerald'
    }
];

const orderFlow = [
    { 
        title: 'Import / Input Order', 
        desc: 'Data order lama di-import ke tabel "Old Order".',
        icon: IconShoppingCart,
        color: 'orange'
    },
    { 
        title: 'Resume Bulanan', 
        desc: 'Group per bulan di "Resume Old Order". Klik "Sync" untuk ke Aktif.',
        icon: IconCalendarEvent,
        color: 'amber'
    },
    { 
        title: 'Old Order Aktif', 
        desc: 'Data siap diproses finalisasi stok keluar.',
        icon: IconRefresh,
        color: 'rose'
    },
    { 
        title: 'Sync Stock (Final)', 
        desc: 'Klik "Sync Stock" untuk mengurangi qty dari "Stock Running" dan status jadi FINAL.',
        icon: IconPackage,
        color: 'red'
    }
];

const stockReporting = [
    {
        title: 'Opsi A (Historical)',
        desc: 'Laporan menggunakan tanggal transaksi asli (Tanggal Faktur / Tanggal Order) - bukan tanggal finalisasi.'
    },
    {
        title: 'Data Stock Awal',
        desc: 'Akumulasi semua movement (masuk & keluar) yang terjadi SEBELUM bulan yang dipilih.'
    },
    {
        title: 'Unfinal Feature',
        desc: 'Tombol "Unfinal" di menu Resume Aktif digunakan untuk membatalkan finalisasi dan mengembalikan stok jika terjadi kesalahan input.'
    }
];
</script>

<template>
    <Head title="Helpdesk - Panduan Sistem" />

    <DashboardLayout>
        <div class="max-w-6xl mx-auto space-y-8 pb-12">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-2">
                <div class="p-3 bg-primary-500/10 rounded-2xl">
                    <IconHelp :size="32" class="text-primary-500" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Helpdesk & Panduan Flow</h1>
                    <p class="text-slate-500 text-sm">Penjelasan alur data dari import hingga laporan persediaan.</p>
                </div>
            </div>

            <!-- Flow Purchase -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-12 opacity-5">
                    <IconDownload :size="200" />
                </div>
                
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-8 flex items-center gap-2">
                    <IconDownload class="text-blue-500" />
                    Alur Old Purchase (Stok Masuk)
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <template v-for="(step, idx) in purchaseFlow" :key="idx">
                        <div class="relative group">
                            <!-- Arrow -->
                            <div v-if="idx < purchaseFlow.length - 1" class="hidden md:block absolute top-8 -right-4 z-10 text-slate-300 dark:text-slate-700">
                                <IconArrowRight :size="20" />
                            </div>

                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl h-full border border-slate-100 dark:border-slate-700/50 hover:border-primary-500/30 transition-all duration-300">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" 
                                    :class="`bg-${step.color}-500/10 text-${step.color}-500`">
                                    <component :is="step.icon" :size="24" />
                                </div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 mb-2">{{ step.title }}</h3>
                                <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400 font-medium">
                                    {{ step.desc }}
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <!-- Flow Order -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-12 opacity-5">
                    <IconShoppingCart :size="200" />
                </div>
                
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-8 flex items-center gap-2">
                    <IconShoppingCart class="text-orange-500" />
                    Alur Old Order (Stok Keluar)
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <template v-for="(step, idx) in orderFlow" :key="idx">
                        <div class="relative group">
                            <!-- Arrow -->
                            <div v-if="idx < orderFlow.length - 1" class="hidden md:block absolute top-8 -right-4 z-10 text-slate-300 dark:text-slate-700">
                                <IconArrowRight :size="20" />
                            </div>

                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl h-full border border-slate-100 dark:border-slate-700/50 hover:border-primary-500/30 transition-all duration-300">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" 
                                    :class="`bg-${step.color}-500/10 text-${step.color}-500`">
                                    <component :is="step.icon" :size="24" />
                                </div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 mb-2">{{ step.title }}</h3>
                                <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400 font-medium">
                                    {{ step.desc }}
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <!-- Stock Reporting & Unfinal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Stock Info -->
                <section class="bg-gradient-to-br from-indigo-500 to-primary-600 rounded-3xl p-8 text-white shadow-lg overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <IconChartBar :size="150" />
                    </div>
                    
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <IconChartBar />
                        Laporan Persediaan & Stock
                    </h2>

                    <div class="space-y-4">
                        <template v-for="(info, idx) in stockReporting" :key="idx">
                            <div class="bg-white/10 backdrop-blur-sm p-4 rounded-2xl border border-white/20">
                                <h4 class="font-bold text-sm mb-1">{{ info.title }}</h4>
                                <p class="text-xs text-white/80 leading-relaxed">{{ info.desc }}</p>
                            </div>
                        </template>
                    </div>
                </section>

                <!-- Database Structure Info -->
                <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <IconDatabase :size="150" />
                    </div>
                    
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <IconDatabase class="text-indigo-500" />
                        Tabel Database Terkait
                    </h2>

                    <div class="grid grid-cols-1 gap-3">
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700/50">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center text-xs font-bold">SQL</div>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800 dark:text-slate-200">old_stock_running</div>
                                <div class="text-slate-500">Saldo stok saat ini.</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700/50">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xs font-bold">SQL</div>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800 dark:text-slate-200">old_stock_awal</div>
                                <div class="text-slate-500">Saldo awal pembukuan.</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                                <IconRepeat :size="16" />
                            </div>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800 dark:text-slate-200">Sync logic</div>
                                <div class="text-slate-500">Menggunakan <code>tanggal_faktur</code> (Purchase) & <code>created_at</code> (Order).</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-2xl">
                        <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed font-medium">
                            <strong>Note:</strong> Pastikan melakukan "Sync Stock" tepat waktu agar <code>stock_running</code> selalu akurat dengan fisik gudang.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </DashboardLayout>
</template>
