# Point of Sales & ERP – Laravel, Inertia & Vue 3 🚀

Sistem manajemen kasir (POS) dan ERP (Enterprise Resource Planning) modern yang dirancang untuk kecepatan transaksi, manajemen stok akurat antar gudang, dan integrasi akuntansi otomatis.

![Dashboard Preview](public/media/revamp-pos.png "Point of Sales Dashboard Preview")

## 🛠️ Teknologi yang Digunakan

Proyek ini dibangun menggunakan **TALL stack modern** (dengan penyesuaian):
- **Backend**: [Laravel 12](https://laravel.com)
- **Frontend**: [Inertia.js](https://inertiajs.com) + [Vue.js 3](https://vuejs.org) (Composition API)
- **Styling**: [Tailwind CSS](https://tailwindcss.com) + Vanilla CSS
- **Iconography**: [Tabler Icons](https://tabler-icons.io)
- **Charts**: [Chart.js](https://www.chartjs.org/)
- **State Management**: Reactive Vue API
- **Build Tool**: [Vite](https://vitejs.dev/)

## ✨ Fitur Utama

### 1. Point of Sales (Kasir)
- **Quick Checkout**: Pencarian produk via barcode atau nama dengan kalkulasi otomatis.
- **Hold Transaction**: Simpan keranjang sementara dan lanjutkan nanti.
- **Customer CRM**: Tambah pelanggan baru langsung dari kasir dan lihat riwayat belanja mereka secara instan.
- **Shortcut Keyboards**: `/` atau `F5` untuk search, `F2` bayar, `Esc` clear.

### 2. Manajemen Stok & Multi-Gudang
- **Inventory Tracking**: Pantau stok secara real-time di berbagai lokasi gudang.
- **Stock Mutation**: Pencatatan otomatis setiap perpindahan, penambahan, atau pengurangan stok.
- **Zero-Value Transactions**: Modul khusus untuk mencatat barang rusak (damaged), barang expired, bonus, atau hadiah tanpa nilai nominal transaksi, lengkap dengan jurnal akuntansi otomatis.

### 3. Sistem Pre-Order (PO)
- **DP Tracking**: Mendukung pembayaran uang muka (Down Payment).
- **Stock Booking**: Reservasi stok otomatis untuk pesanan PO agar tidak terjual ke pelanggan lain.
- **Auto-Fulfillment**: Integrasi dengan modul Pembelian—stok otomatis dialokasikan ke PO yang menunggu saat barang datang.

### 4. Alur Persetujuan (Approval Flow)
- **Finance Verification**: Verifikasi pembayaran oleh tim keuangan sebelum pesanan diteruskan.
- **Warehouse Fulfillment**: Verifikasi ketersediaan dan pengemasan oleh tim gudang.

### 5. Akuntansi & Laporan
- **Automatic Journaling**: Pencatatan jurnal Debit/Kredit otomatis untuk setiap transaksi penjualan, pelunasan piutang, dan penyesuaian stok.
- **Receivables (Piutang)**: Pantau dan catat pelunasan piutang pelanggan untuk transaksi kredit atau PO.
- **Reports**: Laporan penjualan, laba rugi (profit), dan mutasi stok yang lengkap.

### 6. Pencetakan Dokumen
- **Thermal Receipt**: Support cetak struk ukuran 58mm dan 80mm.
- **A4 Invoice**: Cetak invoice resmi ukuran A4 untuk transaksi besar.

## 🚀 Cara Instalasi

Ikuti langkah berikut untuk menjalankan proyek di lingkungan lokal Anda:

1. **Clone Repository**
   ```bash
   git clone https://github.com/yudapw37/sistem-medium-erp.git
   cd sistem-medium-erp
   ```

2. **Install Dependensi**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   # Update database credentials di file .env Anda
   ```

4. **Setup Database & Storage**
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Jalankan Aplikasi**
   ```bash
   # Terminal 1: Compile assets
   npm run dev

   # Terminal 2: Jalankan web server
   php artisan serve
   ```

### Akun Demo:
- **Admin**: `arya@gmail.com` / password: `password`
- **Kasir**: `cashier@gmail.com` / password: `password`

---
Made with ❤️ by Point of Sales Community.