-- =====================================================
-- Combined SQL Migration Scripts
-- Run this file to create all new tables
-- =====================================================

-- 1. old_purchase_aktif
CREATE TABLE IF NOT EXISTS old_purchase_aktif (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    old_purchase_id BIGINT UNSIGNED NULL,
    nomor_faktur VARCHAR(255) NOT NULL,
    supplier VARCHAR(255) NULL,
    tanggal_faktur DATE NULL,
    harga_total DECIMAL(15,2) DEFAULT 0,
    ppn DECIMAL(15,2) DEFAULT 0,
    subtotal DECIMAL(15,2) DEFAULT 0,
    is_final TINYINT(1) DEFAULT 0,
    final_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (old_purchase_id) REFERENCES old_purchases(id) ON DELETE SET NULL,
    INDEX idx_nomor_faktur (nomor_faktur),
    INDEX idx_is_final (is_final)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. old_purchase_aktif_details
CREATE TABLE IF NOT EXISTS old_purchase_aktif_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    old_purchase_aktif_id BIGINT UNSIGNED NOT NULL,
    code_barang VARCHAR(255) NULL,
    nama VARCHAR(255) NULL,
    qty INT DEFAULT 0,
    harga_satuan DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (old_purchase_aktif_id) REFERENCES old_purchase_aktif(id) ON DELETE CASCADE,
    INDEX idx_code_barang (code_barang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. old_order_aktif (tanpa FK ke old_order karena old_order menggunakan string id)
CREATE TABLE IF NOT EXISTS old_order_aktif (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    old_order_id BIGINT UNSIGNED NULL,
    code_customer VARCHAR(255) NULL,
    nama_pengirim VARCHAR(255) NULL,
    telephone_pengirim VARCHAR(255) NULL,
    nama_penerima VARCHAR(255) NULL,
    telephone_penerima VARCHAR(255) NULL,
    alamat TEXT NULL,
    kecamatan VARCHAR(255) NULL,
    kab_kota VARCHAR(255) NULL,
    total_barang INT DEFAULT 0,
    total_harga DECIMAL(15,2) DEFAULT 0,
    total_diskon DECIMAL(15,2) DEFAULT 0,
    diskon_kode_unik DECIMAL(15,2) DEFAULT 0,
    biaya_expedisi DECIMAL(15,2) DEFAULT 0,
    is_final TINYINT(1) DEFAULT 0,
    final_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_code_customer (code_customer),
    INDEX idx_is_final (is_final)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. old_order_aktif_details
CREATE TABLE IF NOT EXISTS old_order_aktif_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    old_order_aktif_id BIGINT UNSIGNED NOT NULL,
    code_order VARCHAR(255) NULL,
    code_barang VARCHAR(255) NULL,
    nama_promo VARCHAR(255) NULL,
    jumlah INT DEFAULT 0,
    harga DECIMAL(15,2) DEFAULT 0,
    harga_promo DECIMAL(15,2) DEFAULT 0,
    diskon DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (old_order_aktif_id) REFERENCES old_order_aktif(id) ON DELETE CASCADE,
    INDEX idx_code_order (code_order),
    INDEX idx_code_barang (code_barang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. old_stock_awal
CREATE TABLE IF NOT EXISTS old_stock_awal (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code_barang VARCHAR(255) NOT NULL,
    qty INT DEFAULT 0,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_code_barang (code_barang),
    INDEX idx_code_barang (code_barang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. old_stock_running
CREATE TABLE IF NOT EXISTS old_stock_running (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code_barang VARCHAR(255) NOT NULL,
    stock_awal INT DEFAULT 0,
    stock_masuk INT DEFAULT 0,
    stock_keluar INT DEFAULT 0,
    stock_saldo INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_code_barang (code_barang),
    INDEX idx_code_barang (code_barang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
