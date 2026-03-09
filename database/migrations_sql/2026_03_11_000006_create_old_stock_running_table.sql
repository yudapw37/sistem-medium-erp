-- Create old_stock_running table
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
