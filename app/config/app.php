<?php
/**
 * Application Configuration
 */

// Store info
define('APP_NAME', 'Toko Adam');
define('APP_TAGLINE', 'Sistem Administrasi Grosir');
define('APP_VERSION', '1.0.0');
define('COMPANY_HEADER_TOP', '');
define('COMPANY_ADDRESS', '');
define('COMPANY_PHONE', '');
define('COMPANY_SIGNER_NAME', '');
define('COMPANY_SIGNER_ROLE', '');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL — auto-detect
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . '://' . $host . ($scriptDir === '\\' || $scriptDir === '/' ? '' : $scriptDir);
define('BASE_URL', rtrim($baseUrl, '/'));

// Paths
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', ROOT_PATH . '/app');
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');
define('PRODUCT_IMG_PATH', UPLOAD_PATH . '/products');

// Upload settings
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMG_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Pagination
define('PER_PAGE', 15);

// Invoice format
define('INVOICE_PREFIX', 'INV');
