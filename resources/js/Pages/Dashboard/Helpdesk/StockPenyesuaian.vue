<script setup>
import { onMounted, ref } from 'vue';
import mermaid from 'mermaid';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    IconAdjustments,
    IconArrowRight,
    IconBook,
    IconArrowUp,
    IconArrowDown,
    IconCheck,
    IconInfoCircle,
    IconAlertTriangle,
} from '@tabler/icons-vue';

const steps = [
    {
        title: '1. Buat Penyesuaian',
        desc: 'Pilih Tipe (Masuk/Keluar), Gudang, dan Tanggal. Tentukan alasan penyesuaian di kolom Catatan.',
        icon: IconAdjustments,
        color: 'violet',
    },
    {
        title: '2. Tambah Item',
        desc: 'Cari produk lalu masukkan qty yang akan disesuaikan. Bisa multiple produk sekaligus dalam satu dokumen.',
        icon: IconArrowRight,
        color: 'indigo',
    },
    {
        title: '3. Simpan Draft',
        desc: 'Simpan sebagai Draft. Stok belum berubah. Dokumen masih dapat diedit atau dihapus.',
        icon: IconBook,
        color: 'sky',
    },
    {
        title: '4. Finalisasi',
        desc: 'Klik Finalize. Qty stok berubah sesuai tipe (tambah/kurang) dan jurnal akuntansi dibuat otomatis.',
        icon: IconCheck,
        color: 'emerald',
    },
];

const journalRules = [
    {
        type: 'Penyesuaian Masuk (in)',
        icon: IconArrowUp,
        color: 'emerald',
        debit: 'Persediaan / Inventory',
        credit: 'Penyesuaian Stok Masuk (stock_adjustment_in / equity)',
        desc: 'Stok bertambah. Terjadi saat ada temuan fisik, barang kembali dari display, koreksi input kurang, dll.',
    },
    {
        type: 'Penyesuaian Keluar (out)',
        icon: IconArrowDown,
        color: 'red',
        debit: 'Beban Penyesuaian (stock_adjustment_out)',
        credit: 'Persediaan / Inventory',
        desc: 'Stok berkurang. Terjadi saat barang sample, tester, penyusutan, koreksi input lebih, dll.',
    },
];

const useCases = [
    { title: 'Masuk (in)', desc: 'Temuan barang tidak tercatat, koreksi import salah kurang, barang kembali display, barang titipan masuk.', color: 'emerald' },
    { title: 'Keluar (out)', desc: 'Sample/tester diberikan, barang untuk promosi, koreksi import salah lebih, kehilangan kecil yang teridentifikasi.', color: 'rose' },
];

const keyPoints = [
    {
        title: 'Nilai Jurnal',
        desc: 'Nilai = Qty × buy_price produk. Pastikan harga beli produk sudah diisi dengan benar di master produk.',
    },
    {
        title: 'Prefix Kode',
        desc: 'Kode dokumen menggunakan prefix "SADJ-" diikuti tanggal dan kode acak. Jurnal memakai prefix "SADJ".',
    },
    {
        title: 'Berbeda dengan Stock Opname',
        desc: 'Stock Penyesuaian bersifat tambah/kurang (incremental), bukan menimpa stok total seperti Stock Opname.',
    },
    {
        title: 'Tidak Ada Nilai 0',
        desc: 'Jika total nilai semua item adalah 0 (harga beli belum diset), jurnal akuntansi tidak akan dibuat.',
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
            pdf.save('DFD-Stock-Penyesuaian.pdf'); resolve();
        };
        img.onerror = (e) => { alert('Gagal export.'); reject(e); };
        img.src = svgBase64;
    });
};
</script>

<template>
    <Head title="Helpdesk - Stock Penyesuaian" />

    <DashboardLayout>
        <div class="max-w-6xl mx-auto space-y-8 pb-12">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-2">
                <div class="p-3 bg-violet-500/10 rounded-2xl">
                    <IconAdjustments :size="32" class="text-violet-500" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">SOP Stock Penyesuaian & Jurnal</h1>
                    <p class="text-slate-500 text-sm">Panduan alur penyesuaian stok masuk/keluar beserta pencatatan jurnal akuntansi double-entry otomatis.</p>
                </div>
            </div>

            <!-- Perbedaan dengan Opname -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <template v-for="(uc, idx) in useCases" :key="idx">
                    <div class="rounded-2xl p-6 border"
                        :class="uc.color === 'emerald'
                            ? 'border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-900/10'
                            : 'border-rose-200 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-900/10'">
                        <div class="flex items-center gap-3 mb-3">
                            <component :is="uc.color === 'emerald' ? IconArrowUp : IconArrowDown"
                                :class="uc.color === 'emerald' ? 'text-emerald-500' : 'text-rose-500'"
                                :size="22" />
                            <h3 class="font-bold text-slate-800 dark:text-slate-200">Tipe {{ uc.title }}</h3>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ uc.desc }}</p>
                    </div>
                </template>
            </div>

            <!-- Alur Steps -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-12 opacity-5">
                    <IconAdjustments :size="200" />
                </div>

                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-8 flex items-center gap-2">
                    <IconAdjustments class="text-violet-500" />
                    Alur Pembuatan Stock Penyesuaian
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <template v-for="(step, idx) in steps" :key="idx">
                        <div class="relative group">
                            <div v-if="idx < steps.length - 1" class="hidden md:block absolute top-8 -right-4 z-10 text-slate-300 dark:text-slate-700">
                                <IconArrowRight :size="20" />
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl h-full border border-slate-100 dark:border-slate-700/50 hover:border-violet-500/30 transition-all duration-300">
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

            <!-- Key Points & Journal Example -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <section class="bg-gradient-to-br from-violet-500 to-indigo-600 rounded-3xl p-8 text-white shadow-lg overflow-hidden relative">
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
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Penyesuaian Masuk: 10 unit @ Rp 20.000</p>
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
                                            <td class="px-3 py-2 text-right font-mono text-blue-600">200.000</td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-400">-</td>
                                        </tr>
                                        <tr class="bg-white dark:bg-slate-900/50">
                                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300 pl-6">Modal / Penyesuaian Masuk</td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-400">-</td>
                                            <td class="px-3 py-2 text-right font-mono text-purple-600">200.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Penyesuaian Keluar: 5 unit @ Rp 30.000</p>
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
                                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300">Beban Penyesuaian Stok</td>
                                            <td class="px-3 py-2 text-right font-mono text-blue-600">150.000</td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-400">-</td>
                                        </tr>
                                        <tr class="bg-white dark:bg-slate-900/50">
                                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300 pl-6">Persediaan (Inventory)</td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-400">-</td>
                                            <td class="px-3 py-2 text-right font-mono text-purple-600">150.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-xl">
                            <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                <strong>Rumus Nilai:</strong> Total Nilai = Σ (Qty × buy_price per produk)
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- DFD Mermaid -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Visualisasi Alur Stock Penyesuaian (DFD)</h2>
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
    U("Admin / Gudang") -->|"Pilih Tipe + Gudang"| D("Buat Dokumen Penyesuaian")
    D -->|"Cari Produk"| ITEM("Tambah Item + Qty")
    ITEM -->|"Simpan"| DFT["Status: Draft"]
    DFT -->|"Klik Finalize"| CALC["Hitung Total Nilai\n= Σ(Qty × buy_price)"]

    CALC -->|"Tipe Masuk"| INS["stock += qty\nstock_fix += qty"]
    CALC -->|"Tipe Keluar"| OUS["stock -= qty\nstock_fix -= qty"]

    INS --> LOG[("Log Mutasi")]
    OUS --> LOG

    CALC -->|"Total Nilai > 0?"| JRN{"Ada Nilai Jurnal?"}
    JRN -->|"Tidak"| DONE["✓ Finalized, Tanpa Jurnal"]
    JRN -->|"Ya"| JRNL[["Buat Jurnal Akuntansi"]]

    JRNL -->|"Tipe Masuk"| JIN["Debit: Inventory\nKredit: Modal/Equity"]
    JRNL -->|"Tipe Keluar"| JOUT["Debit: Beban Penyesuaian\nKredit: Inventory"]

    JIN --> STATUS["Status = Finalized ✓"]
    JOUT --> STATUS
    DONE --> STATUS

    classDef actor fill:#f87171,color:white,stroke:#ef4444,stroke-width:2px
    classDef process fill:#a78bfa,color:white,stroke:#7c3aed,stroke-width:2px
    classDef db fill:#fcd34d,stroke:#f59e0b,stroke-width:2px
    classDef journal fill:#60a5fa,color:white,stroke:#3b82f6,stroke-width:2px

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
