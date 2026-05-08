-- ============================================
-- Toko Adam Web Admin - Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS toko_adam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE toko_adam;

-- -------------------------------------------
-- Table: users
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------
-- Table: products
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(30) NOT NULL UNIQUE,
    nama_barang VARCHAR(150) NOT NULL,
    kategori VARCHAR(80) DEFAULT NULL,
    satuan VARCHAR(30) NOT NULL DEFAULT 'pcs',
    foto VARCHAR(255) DEFAULT NULL,
    harga_jual DECIMAL(15,2) NOT NULL DEFAULT 0,
    stok_minimum INT NOT NULL DEFAULT 10,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------
-- Table: customers
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipe ENUM('customer', 'supplier') NOT NULL DEFAULT 'customer',
    nama_toko VARCHAR(150) NOT NULL,
    nama_pic VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(25) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------
-- Table: stock_batches  (FIFO inventory)
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS stock_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    supplier_id INT DEFAULT NULL,
    tanggal_masuk DATE NOT NULL,
    qty_masuk INT NOT NULL,
    qty_sisa INT NOT NULL,
    harga_modal DECIMAL(15,2) NOT NULL,
    nomor_nota VARCHAR(100) DEFAULT NULL,
    nota_file VARCHAR(255) DEFAULT NULL,
    keterangan TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------
-- Table: sales
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_transaksi VARCHAR(30) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    tanggal_transaksi DATE NOT NULL,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    catatan TEXT DEFAULT NULL,
    invoice_overrides LONGTEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------
-- Table: sale_items
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    qty INT NOT NULL,
    harga_jual DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -------------------------------------------
-- Table: sale_item_fifo  (FIFO tracking per sale item)
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS sale_item_fifo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_item_id INT NOT NULL,
    stock_batch_id INT NOT NULL,
    qty_keluar INT NOT NULL,
    harga_modal_batch DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (sale_item_id) REFERENCES sale_items(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_batch_id) REFERENCES stock_batches(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -------------------------------------------
-- Table: price_histories
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS price_histories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    harga_modal DECIMAL(15,2) NOT NULL,
    sumber_batch_id INT DEFAULT NULL,
    tanggal DATE NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (sumber_batch_id) REFERENCES stock_batches(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------
-- Table: stock_movements  (audit trail)
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    tipe ENUM('in','out') NOT NULL,
    ref_type VARCHAR(30) DEFAULT NULL,
    ref_id INT DEFAULT NULL,
    qty INT NOT NULL,
    keterangan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------
-- Seed: default admin user  (password: admin123)
-- Note: hash generated by setup.php
-- -------------------------------------------
