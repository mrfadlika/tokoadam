<?php
/**
 * Database Configuration & Connection
 * Uses PDO with MySQL/MariaDB
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'toko_adam');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection (singleton)
 */
function getDB() {
    static $pdo = null;
    
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
    
    return $pdo;
}
