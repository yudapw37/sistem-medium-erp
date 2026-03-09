# Plan Implementasi: Old Purchase Aktif & Old Order Aktif + Stock Management

## Overview

Membuat sistem terpisah untuk mengelola data old purchases dan old orders yang sudah diproses (di-final), beserta manajemen stock awal dan running stock.

---

## Struktur Database

### 1. Tabel Baru: `old_purchase_aktif`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| old_purchase_id | bigint | FK ke old_purchases (nullable) |
| nomor_faktur | string | Nomor faktur |
| supplier | string | Nama supplier |
| tanggal_faktur | date | Tanggal faktur |
| harga_total | decimal | Total harga |
| ppn | decimal | PPN |
| subtotal | decimal | Subtotal |
| is_final | boolean | Status final (default false) |
| final_at | timestamp | Waktu di-final |
| created_at | timestamp | |
| updated_at | timestamp | |

### 2. Tabel Baru: `old_purchase_aktif_details`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| old_purchase_aktif_id | bigint | FK ke old_purchase_aktif |
| code_barang | string | Kode barang dari old_ms_barang |
| nama | string | Nama barang |
| qty | integer | Jumlah |
| harga_satuan | decimal | Harga per unit |
| total | decimal | Total |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3. Tabel Baru: `old_order_aktif`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| old_order_id | bigint | FK ke old_order (nullable) |
| code_customer | string | Kode customer |
| nama_pengirim | string | Nama pengirim |
| telephone_pengirim | string | Telp pengirim |
| nama_penerima | string | Nama penerima |
| telephone_penerima | string | Telp penerima |
| alamat | text | Alamat |
| kecamatan | string | Kecamatan |
| kab_kota | string | Kota |
| total_barang | integer | Jumlah barang |
| total_harga | decimal | Total harga |
| total_diskon | decimal | Total diskon |
| diskon_kode_unik | decimal | Diskoun kode unik |
| biaya_expedisi | decimal | Biaya ekspedisi |
| is_final | boolean | Status final (default false) |
| final_at | timestamp | Waktu di-final |
| created_at | timestamp | |
| updated_at | timestamp | |

### 4. Tabel Baru: `old_order_aktif_details`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| old_order_aktif_id | bigint | FK ke old_order_aktif |
| code_order | string | Kode order |
| code_barang | string | Kode barang |
| nama_promo | string | Nama promo |
| jumlah | integer | Jumlah |
| harga | decimal | Harga |
| harga_promo | decimal | Harga promo |
| diskon | decimal | Diskon |
| created_at | timestamp | |
| updated_at | timestamp | |

### 5. Tabel Baru: `old_stock_awal`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| code_barang | string | Kode barang dari old_ms_barang |
| qty | integer | Jumlah stock awal |
| tanggal | date | Tanggal stock awal |
| created_at | timestamp | |
| updated_at | timestamp | |

### 6. Tabel Baru: `old_stock_running`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| code_barang | string | Kode barang |
| old_stock_awal | integer | Stock awal |
| stock_masuk | integer | Stock dari purchase |
| stock_keluar | integer | Stock dari order |
| stock_saldo | integer | Stock saat ini |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Alur Sistem

### A. Old Purchase → Old Purchase Aktif

1. **Import PDF Faktur** (sudah ada)
   - Upload PDF → parsing → simpan ke `old_purchases` + `old_purchase_details`
   - Kolom `code_barang` masih NULL

2. **Mapping Barang** (fitur baru)
   - Tampilkan list item dari `old_purchase_details` yang `code_barang` NULL
   - User pilih mapping dengan `old_ms_barang` berdasarkan nama
   - Simpan `code_barang` ke detail

3. **Finalisasi ke Old Purchase Aktif** (fitur baru)
   - Pilih purchase yang akan di-final
   - Copy ke `old_purchase_aktif` + `old_purchase_aktif_details`
   - Update `is_final = true` di `old_purchase_aktif`
   - Trigger: update `old_stock_running` (tambah stock)

### B. Old Order → Old Order Aktif

1. **Resume Orders** (sudah ada)
   - Tampilkan semua order, user toggle `resume_status` (true/false)

2. **Finalisasi ke Old Order Aktif** (fitur baru)
   - Pilih order dengan `resume_status = true` yang akan di-final
   - Copy ke `old_order_aktif` + `old_order_aktif_details`
   - Update `is_final = true` di `old_order_aktif`
   - Trigger: update `old_stock_running` (kurangi stock)

### C. Stock Awal

1. **Input Stock Awal** (fitur baru)
   - Tampilkan produk yang ada di `old_purchase_details` (sudah di-mapping)
   - User input qty stock awal per produk
   - Simpan ke `old_stock_awal`
   - Auto-create/update `old_stock_running`

### D. Stock Running (Otomatis)

1. **Perhitungan**
   - `stock_saldo = old_stock_awal + stock_masuk - stock_keluar`
   - Setiap ada `old_purchase_aktif` yang di-final → + `stock_masuk`
   - Setiap ada `old_order_aktif` yang di-final → + `stock_keluar`

---

## File yang Perlu Dibuat/Modifikasi

### Models (new)
- `app/Models/OldPurchaseAktif.php`
- `app/Models/OldPurchaseAktifDetail.php`
- `app/Models/OldOrderAktif.php`
- `app/Models/OldOrderAktifDetail.php`
- `app/Models/StockAwal.php`
- `app/Models/StockRunning.php`

### Migrations (new)
- `database/migrations/2026_xx_xx_xxxxxx_create_old_purchase_aktif_table.php`
- `database/migrations/2026_xx_xx_xxxxxx_create_old_purchase_aktif_details_table.php`
- `database/migrations/2026_xx_xx_xxxxxx_create_old_order_aktif_table.php`
- `database/migrations/2026_xx_xx_xxxxxx_create_old_order_aktif_details_table.php`
- `database/migrations/2026_xx_xx_xxxxxx_create_old_stock_awal_table.php`
- `database/migrations/2026_xx_xx_xxxxxx_create_old_stock_running_table.php`

### Controllers (new)
- `app/Http/Controllers/Apps/OldPurchaseAktifController.php`
- `app/Http/Controllers/Apps/OldOrderAktifController.php`
- `app/Http/Controllers/Apps/StockController.php`

### Vue Pages (new)
- `resources/js/Pages/Dashboard/OldPurchases/MapBarang.vue` - Mapping barang
- `resources/js/Pages/Dashboard/OldPurchasesAktif/Index.vue` - List old purchase aktif
- `resources/js/Pages/Dashboard/OldOrdersAktif/Index.vue` - List old order aktif
- `resources/js/Pages/Dashboard/Stock/Index.vue` - Stock awal
- `resources/js/Pages/Dashboard/Stock/Running.vue` - Stock berjalan

### Routes (new)
- Old Purchase Aktif: `/old-purchases-aktif`
- Old Order Aktif: `/old-orders-aktif`
- Stock: `/stock-awal`, `/stock-running`

### Menu Updates
- Tambahkan menu di sidebar untuk:
  - Old Purchase Aktif
  - Old Order Aktif
  - Stock Awal
  - Stock Running

---

## Step-by-Step Implementation

### Step 1: Database Migrations
Buat 6 tabel baru

### Step 2: Models
Buat 6 model baru dengan relasi

### Step 3: Controllers
Buat controller untuk:
- OldPurchaseAktifController (index, store, final, destroy)
- OldOrderAktifController (index, store, final, destroy)
- StockController (index, store, running)

### Step 4: Routes
Tambahkan route untuk controller di atas

### Step 5: Vue Pages
Buat halaman untuk:
- Mapping barang di old purchase
- List old purchase aktif
- List old order aktif
- Input stock awal
- View stock running

### Step 6: Menu
Update Menu.js untuk menampilkan menu baru

### Step 7: Logic Integration
- Update OldPurchaseController: tombol "Ke Aktif" untuk copy ke old_purchase_aktif
- Update OldOrderController: tombol "Ke Aktif" untuk copy ke old_order_aktif
- Auto-update old_stock_running saat finalisasi
