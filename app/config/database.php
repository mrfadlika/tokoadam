<?php
/**
 * Database Configuration & Connection
 * Uses PDO with MySQL/MariaDB
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'toko_adam');
define('DB_USER', 'root');
define('DB_PASS', 'raffi');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection (singleton)
 */
function getDB() {
    static $pdo = null;
    static $schemaChecked = false;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Show friendly error in development
            die('<div style="font-family:sans-serif;padding:40px;text-align:center;">
                <h2 style="color:#8B1A1A;">Database Connection Error</h2>
                <p>Tidak bisa terhubung ke database. Pastikan MySQL sudah berjalan.</p>
                <p style="color:#999;font-size:13px;">' . htmlspecialchars($e->getMessage()) . '</p>
                <a href="setup.php" style="color:#8B1A1A;">Jalankan Setup</a>
            </div>');
        }
    }

    if (!$schemaChecked) {
        ensureRuntimeSchema($pdo);
        $schemaChecked = true;
    }
    
    return $pdo;
}

function ensureRuntimeSchema(PDO $pdo): void {
    if (tableExists($pdo, 'customers') && !columnExists($pdo, 'customers', 'tipe')) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN tipe ENUM('customer', 'supplier') NOT NULL DEFAULT 'customer' AFTER id");
    }

    if (tableExists($pdo, 'stock_batches')) {
        if (!columnExists($pdo, 'stock_batches', 'supplier_id')) {
            $pdo->exec("ALTER TABLE stock_batches ADD COLUMN supplier_id INT NULL AFTER product_id");
        }
        if (!columnExists($pdo, 'stock_batches', 'nomor_nota')) {
            $pdo->exec("ALTER TABLE stock_batches ADD COLUMN nomor_nota VARCHAR(100) NULL AFTER harga_modal");
        }
        if (!columnExists($pdo, 'stock_batches', 'nota_file')) {
            $pdo->exec("ALTER TABLE stock_batches ADD COLUMN nota_file VARCHAR(255) NULL AFTER nomor_nota");
        }
    }

    if (tableExists($pdo, 'sales')) {
        if (!columnExists($pdo, 'sales', 'status_bayar')) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN status_bayar ENUM('belum_lunas', 'lunas') NOT NULL DEFAULT 'belum_lunas' AFTER catatan");
        }
        if (!columnExists($pdo, 'sales', 'invoice_overrides')) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN invoice_overrides LONGTEXT NULL AFTER catatan");
        }
    }
}

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?"
    );
    $stmt->execute([DB_NAME, $table]);
    return (int)$stmt->fetchColumn() > 0;
}

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $stmt->execute([DB_NAME, $table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}
