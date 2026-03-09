-- Create old_order_aktif_details table
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
