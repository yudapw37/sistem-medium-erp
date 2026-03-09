-- Create old_purchase_aktif table
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
