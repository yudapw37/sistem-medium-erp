<script setup>
import { onMounted, ref } from 'vue';
import mermaid from 'mermaid';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    IconZoomMoney,
    IconArrowRight,
    IconBook,
    IconArrowUp,
    IconArrowDown,
    IconCheck,
    IconInfoCircle,
    IconAlertTriangle,
    IconGift,
    IconTrash,
} from '@tabler/icons-vue';

const steps = [
    {
        title: '1. Buat Transaksi Baru',
        desc: 'Pilih Tipe (Masuk/Keluar), Alasan, Gudang, dan Tanggal.',
        icon: IconZoomMoney,
        color: 'teal',
    },
    {
        title: '2. Pilih Produk',
        desc: 'Cari produk dari gudang yang dipilih. Masukkan Qty dan Harga Beli (buy_price) per item.',
        icon: IconArrowRight,
        color: 'cyan',
    },
    {
        title: '3. Simpan Draft',
        desc: 'Dokumen tersimpan dengan status Draft. Stok dan jurnal belum berubah.',
        icon: IconBook,
        color: 'sky',
    },
    {
        title: '4. Finalisasi',
        desc: 'Klik Finalize. Stok berubah dan jurnal akuntansi otomatis dibuat berdasarkan tipe dan alasan.',
        icon: IconCheck,
        color: 'emerald',
    },
];

const reasons = {
    out: [
        { reason: 'expired', label: 'Barang Kadaluarsa', desc: 'Produk melebihi tanggal expired, tidak bisa dijual.' },
        { reason: 'damaged', label: 'Barang Rusak', desc: 'Produk rusak saat penyimpanan atau akibat kecelakaan.' },
        { reason: 'donation', label: 'Hibah / Sumbangan', desc: 'Barang diberikan ke pihak lain tanpa imbalan.' },
        { reason: 'sample', label: 'Sample', desc: 'Produk diberikan sebagai sample kepada prospek/pelanggan.' },
        { reason: 'promotion', label: 'Promosi', desc: 'Produk digunakan untuk kegiatan promosi/marketing.' },
        { reason: 'lost', label: 'Barang Hilang', desc: 'Kehilangan yang teridentifikasi setelah investigasi.' },
    ],
    in: [
        { reason: 'bonus', label: 'Bonus / Hadiah', desc: 'Barang diterima sebagai bonus dari supplier tanpa pembayaran.' },
        { reason: 'found', label: 'Barang Temuan', desc: 'Barang ditemukan yang sebelumnya tidak tercatat.' },
        { reason: 'return_sample', label: 'Return Sample', desc: 'Barang sample dikembalikan dan masuk ke stok lagi.' },
    ],
};

const journalRules = [
    {
        type: 'Transaksi Keluar (out)',
        icon: IconArrowDown,
        color: 'red',
        debit: 'Beban Kerugian / Beban Penyesuaian (stock_adjustment_out)',
        credit: 'Persediaan / Inventory',
        desc: 'Stok keluar tanpa pemasukan. Barang rusak, expired, hibah, sample, dll. Dicatat sebagai beban/kerugian.',
    },
    {
        type: 'Transaksi Masuk (in)',
        icon: IconArrowUp,
        color: 'emerald',
        debit: 'Persediaan / Inventory',
        credit: 'Pendapatan Lain-lain (other_income / stock_adjustment_in)',
        desc: 'Stok masuk tanpa pengeluaran biaya. Bonus, hadiah, barang temuan. Dicatat sebagai Pendapatan Lain-lain.',
    },
];

const keyPoints = [
    {
        title: 'Harga Beli Manual',
        desc: 'Berbeda dengan fitur lain, ZVT meminta input harga beli (buy_price) secara manual per item saat pembuatan dokumen.',
    },
    {
        title: 'Prefix Kode Jurnal',
        desc: 'Kode jurnal menggunakan prefix "ZVT-" untuk memudahkan penelusuran di buku besar.',
    },
    {
        title: 'Deskripsi Lengkap',
        desc: 'Deskripsi jurnal menyertakan kode transaksi dan alasan (reason label) agar lebih informatif.',
    },
    {
        title: 'Nilai 0 = Tanpa Jurnal',
        desc: 'Jika total nilai (qty × buy_price) = 0, jurnal akuntansi tidak dibuat meski transaksi tetap difinalisasi.',
    },
];

const dfdRef = ref(null);

onMounted(() => {
    mermaid.initialize({
        startOnLoad: false,
        theme: 'default',
        flowchart: { useMaxWidth: false, htmlLabels: true, nodeSpacing: 60, rankSpacing: 80 },
        fontSize: 15,
    });
    mermaid.run({ querySelector: '.mermaid' });
});

const downloadPdf = async () => {
    const svgEl = dfdRef.value?.querySelector('svg');
    if (!svgEl) { alert('Diagram belum siap.'); return; }
    const { default: jsPDF } = await import('jspdf');
    let svgW = svgEl.viewBox?.baseVal?.width || svgEl.getBoundingClientRect().width;
    let svgH = svgEl.viewBox?.baseVal?.height || svgEl.getBoundingClientRect().height;
    const svgClone = svgEl.cloneNode(true);
    svgClone.setAttribute('width', svgW); svgClone.setAttribute('height', svgH);
    svgClone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    const svgData = new XMLSerializer().serializeToString(svgClone);
    const svgBase64 = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
    await new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => {
            const scale = 2;
            const canvas = document.createElement('canvas');
            canvas.width = svgW * scale; canvas.height = svgH * scale;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.scale(scale, scale); ctx.drawImage(img, 0, 0, svgW, svgH);
            const imgData = canvas.toDataURL('image/png');
            const pxToMm = 0.264583;
            const pageW = Math.max(svgW * pxToMm, 50); const pageH = Math.max(svgH * pxToMm, 50);
            const pdf = new jsPDF({ orientation: pageW > pageH ? 'landscape' : 'portrait', unit: 'mm', format: [pageW, pageH] });
            pdf.addImage(imgData, 'PNG', 0, 0, pageW, pageH);
            pdf.save('DFD-Zero-Value-Transaction.pdf'); resolve();
        };
        img.onerror = (e) => { alert('Gagal export.'); reject(e); };
        img.src = svgBase64;
    });
};
</script>

<template>
    <Head title="Helpdesk - Zero-Value Transaction" />

    <DashboardLayout>
        <div class="max-w-6xl mx-auto space-y-8 pb-12">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-2">
                <div class="p-3 bg-teal-500/10 rounded-2xl">
                    <IconZoomMoney :size="32" class="text-teal-500" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">SOP Zero-Value Transaction & Jurnal</h1>
                    <p class="text-slate-500 text-sm">Penjelasan lengkap transaksi barang nilai nol (rusak, kadaluarsa, bonus, hibah) beserta penjurnalan otomatis.</p>
                </div>
            </div>

            <!-- Apa itu ZVT -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <IconZoomMoney class="text-teal-500" />
                    Apa itu Zero-Value Transaction?
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                    Zero-Value Transaction (ZVT) adalah dokumen untuk mencatat pergerakan stok yang <strong>tidak melibatkan transaksi jual-beli uang</strong>.
                    Misalnya: barang rusak dibuang, sampel diberikan gratis, barang expired dimusnahkan, atau barang bonus diterima dari supplier.
                    Meskipun tidak ada uang yang berpindah, perubahan stok dan nilai persediaan tetap harus dicatat dalam buku besar.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Reasons OUT -->
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <IconTrash :size="18" class="text-rose-500" />
                            <h3 class="font-bold text-rose-600 dark:text-rose-400 text-sm uppercase tracking-wider">Alasan Keluar (Out)</h3>
                        </div>
                        <div class="space-y-2">
                            <template v-for="r in reasons.out" :key="r.reason">
                                <div class="flex items-start gap-3 p-3 bg-rose-50 dark:bg-rose-900/10 rounded-xl border border-rose-100 dark:border-rose-800/30">
                                    <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-800/30 text-rose-700 dark:text-rose-300 text-xs font-mono rounded-md mt-0.5 shrink-0">{{ r.reason }}</span>
                                    <div>
                                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ r.label }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ r.desc }}</div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Reasons IN -->
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <IconGift :size="18" class="text-emerald-500" />
                            <h3 class="font-bold text-emerald-600 dark:text-emerald-400 text-sm uppercase tracking-wider">Alasan Masuk (In)</h3>
                        </div>
                        <div class="space-y-2">
                            <template v-for="r in reasons.in" :key="r.reason">
                                <div class="flex items-start gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/10 rounded-xl border border-emerald-100 dark:border-emerald-800/30">
                                    <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-800/30 text-emerald-700 dark:text-emerald-300 text-xs font-mono rounded-md mt-0.5 shrink-0">{{ r.reason }}</span>
                                    <div>
                                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ r.label }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ r.desc }}</div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Alur Steps -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-12 opacity-5">
                    <IconZoomMoney :size="200" />
                </div>

                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-8 flex items-center gap-2">
                    <IconArrowRight class="text-teal-500" />
                    Alur Pembuatan Zero-Value Transaction
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <template v-for="(step, idx) in steps" :key="idx">
                        <div class="relative group">
                            <div v-if="idx < steps.length - 1" class="hidden md:block absolute top-8 -right-4 z-10 text-slate-300 dark:text-slate-700">
                                <IconArrowRight :size="20" />
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl h-full border border-slate-100 dark:border-slate-700/50 hover:border-teal-500/30 transition-all duration-300">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
                                    :class="`bg-${step.color}-500/10 text-${step.color}-500`">
                                    <component :is="step.icon" :size="24" />
                                </div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 mb-2">{{ step.title }}</h3>
                                <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400 font-medium">{{ step.desc }}</p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-8 p-4 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800/50 rounded-2xl">
                    <p class="text-xs text-teal-700 dark:text-teal-400 leading-relaxed font-medium">
                        <strong>🔍 Perbedaan utama ZVT vs Penyesuaian:</strong> Pada ZVT, harga beli diinput <strong>manual</strong> per baris item (bukan diambil otomatis dari master produk). Ini memberikan fleksibilitas untuk mencatat nilai barang rusak yang mungkin berbeda dari harga perolehan normal.
                    </p>
                </div>
            </section>

            <!-- Aturan Jurnal -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <IconBook class="text-indigo-500" />
                    Aturan Jurnal Akuntansi Otomatis
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <template v-for="(rule, idx) in journalRules" :key="idx">
                        <div class="rounded-2xl border p-6 space-y-4"
                            :class="rule.color === 'red'
                                ? 'border-red-200 dark:border-red-800/50 bg-red-50/50 dark:bg-red-900/10'
                                : 'border-emerald-200 dark:border-emerald-800/50 bg-emerald-50/50 dark:bg-emerald-900/10'">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                    :class="rule.color === 'red' ? 'bg-red-500/10 text-red-500' : 'bg-emerald-500/10 text-emerald-500'">
                                    <component :is="rule.icon" :size="20" />
                                </div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ rule.type }}</h3>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ rule.desc }}</p>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-bold font-mono">DEBIT</span>
                                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ rule.debit }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-bold font-mono">KREDIT</span>
                                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ rule.credit }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <!-- Key Points & Journal Examples -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <section class="bg-gradient-to-br from-teal-500 to-cyan-600 rounded-3xl p-8 text-white shadow-lg overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <IconInfoCircle :size="150" />
                    </div>
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <IconInfoCircle />
                        Hal Penting yang Perlu Diketahui
                    </h2>
                    <div class="space-y-4 relative z-10">
                        <template v-for="(point, idx) in keyPoints" :key="idx">
                            <div class="bg-white/10 backdrop-blur-sm p-4 rounded-2xl border border-white/20 hover:bg-white/20 transition-all">
                                <h4 class="font-bold text-sm mb-1">{{ point.title }}</h4>
                                <p class="text-xs text-white/80 leading-relaxed">{{ point.desc }}</p>
                            </div>
                        </template>
                    </div>
                </section>

                <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <IconAlertTriangle :size="150" />
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <IconAlertTriangle class="text-amber-500" />
                        Contoh Jurnal
                    </h2>

                    <div class="space-y-5 relative z-10">
                        <div>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">ZVT Keluar: Rusak 10 unit @ Rp 25.000</p>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                    <thead class="bg-slate-100 dark:bg-slate-800">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-bold text-slate-700 dark:text-slate-300">Akun</th>
                                            <th class="text-right px-3 py-2 font-bold text-blue-600">Debit</th>
                                            <th class="text-right px-3 py-2 font-bold text-purple-600">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <tr class="bg-white dark:bg-slate-900/50">
                                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300">Beban Kerugian (Rusak)</td>
                                            <td class="px-3 py-2 text-right font-mono text-blue-600">250.000</td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-400">-</td>
                                        </tr>
                                        <tr class="bg-white dark:bg-slate-900/50">
                                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300 pl-6">Persediaan (Inventory)</td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-400">-</td>
                                            <td class="px-3 py-2 text-right font-mono text-purple-600">250.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">ZVT Masuk: Bonus 20 unit @ Rp 12.000</p>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                    <thead class="bg-slate-100 dark:bg-slate-800">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-bold text-slate-700 dark:text-slate-300">Akun</th>
                                            <th class="text-right px-3 py-2 font-bold text-blue-600">Debit</th>
                                            <th class="text-right px-3 py-2 font-bold text-purple-600">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <tr class="bg-white dark:bg-slate-900/50">
                                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300">Persediaan (Inventory)</td>
                                            <td class="px-3 py-2 text-right font-mono text-blue-600">240.000</td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-400">-</td>
                                        </tr>
                                        <tr class="bg-white dark:bg-slate-900/50">
                                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300 pl-6">Pendapatan Lain-lain (Bonus)</td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-400">-</td>
                                            <td class="px-3 py-2 text-right font-mono text-purple-600">240.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- DFD Mermaid -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Visualisasi Alur Zero-Value Transaction (DFD)</h2>
                    <button
                        @click="downloadPdf"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-500/30 transition-all duration-200 active:scale-95"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0 0l-4-4m4 4l4-4" />
                        </svg>
                        Download PDF
                    </button>
                </div>
                <div ref="dfdRef" class="w-full py-4 bg-white" style="min-height: 400px;">
                    <pre class="mermaid" style="width: 100%; font-size: 14px;">
flowchart TD
    U("Admin / Gudang") -->|"Pilih Tipe + Alasan"| D("Buat Dokumen ZVT")
    D -->|"Cari Produk di Gudang"| ITEM("Tambah Item\n+Qty & Buy Price Manual")
    ITEM -->|"Simpan"| DFT["Status: Draft"]
    DFT -->|"Klik Finalize"| CALC["Hitung Total Nilai\n= Σ(Qty × buy_price input)"]

    CALC -->|"Tipe Masuk"| INS["stock_fix += qty"]
    CALC -->|"Tipe Keluar"| OUS["stock_fix -= qty"]

    INS --> LOG[("Log Mutasi\n(product_stocks)")]
    OUS --> LOG

    CALC --> JRN{"Total Nilai > 0?"}
    JRN -->|"Tidak"| DONE["✓ Finalized, Tanpa Jurnal"]
    JRN -->|"Ya"| JRNL[["Buat Jurnal\nPrefix: ZVT-"]]

    JRNL -->|"Tipe Keluar"| JOUT["Debit: Beban Kerugian\nKredit: Inventory\n(Rusak/Expired/Sample/dll)"]
    JRNL -->|"Tipe Masuk"| JIN["Debit: Inventory\nKredit: Pendapatan Lain\n(Bonus/Temuan/dll)"]

    JIN --> STATUS["Status = Finalized ✓"]
    JOUT --> STATUS
    DONE --> STATUS

    classDef actor fill:#f87171,color:white,stroke:#ef4444,stroke-width:2px
    classDef process fill:#2dd4bf,color:white,stroke:#0d9488,stroke-width:2px
    classDef db fill:#fcd34d,stroke:#f59e0b,stroke-width:2px
    classDef journal fill:#a78bfa,color:white,stroke:#7c3aed,stroke-width:2px

    class U actor
    class D,ITEM,CALC,INS,OUS process
    class LOG db
    class JRNL,JIN,JOUT journal
                    </pre>
                </div>
            </section>
        </div>
    </DashboardLayout>
</template>
