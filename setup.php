<?php
/**
 * Toko Adam - Database Setup + FIFO Demo Seeder
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'toko_adam';
$messages = [];

function addMessage(array &$messages, string $type, string $message): void {
    $messages[] = [$type, $message];
}

function rootPDO(string $host, string $user, string $pass): PDO {
    return new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function appPDO(string $host, string $user, string $pass, string $dbname): PDO {
    return new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function readSchemaStatements(string $schemaPath): array {
    $schema = file_get_contents($schemaPath);
    if ($schema === false) {
        throw new RuntimeException('Gagal membaca file schema database.');
    }

    $schema = preg_replace('/^\s*CREATE DATABASE\b.*?;\s*$/mi', '', $schema);
    $schema = preg_replace('/^\s*USE\b.*?;\s*$/mi', '', $schema);

    $statements = [];
    $buffer = '';
    foreach (preg_split('/\R/', $schema) as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }

        $buffer .= $line . PHP_EOL;
        if (str_ends_with(rtrim($line), ';')) {
            $statements[] = trim($buffer);
            $buffer = '';
        }
    }

    if (trim($buffer) !== '') {
        $statements[] = trim($buffer);
    }

    return $statements;
}

function runSetup(string $host, string $user, string $pass, string $dbname, array &$messages): void {
    $pdo = rootPDO($host, $user, $pass);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    addMessage($messages, 'success', "Database '$dbname' siap digunakan.");

    foreach (readSchemaStatements(__DIR__ . '/database/schema.sql') as $stmt) {
        $pdo->exec($stmt);
    }
    addMessage($messages, 'success', 'Semua tabel berhasil dibuat / diverifikasi.');

    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $check = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ((int)$check === 0) {
        $stmt = $pdo->prepare("INSERT INTO users (nama, username, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrator', 'admin', $adminHash, 'admin']);
        addMessage($messages, 'success', 'User admin dibuat. Username: admin, Password: admin123');
    } else {
        addMessage($messages, 'info', 'User admin sudah tersedia.');
    }

    $dirs = [__DIR__ . '/public/uploads/products'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            addMessage($messages, 'success', "Folder '$dir' berhasil dibuat.");
        }
    }
}

function tablesReady(PDO $db): bool {
    $requiredTables = ['users', 'products', 'customers', 'stock_batches', 'sales', 'sale_items', 'sale_item_fifo', 'stock_movements', 'price_histories'];
    foreach ($requiredTables as $table) {
        $stmt = $db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if (!$stmt->fetchColumn()) {
            return false;
        }
    }

    return true;
}

function ensureDemoProduct(PDO $db): int {
    $stmt = $db->prepare("SELECT id FROM products WHERE kode_barang = ? LIMIT 1");
    $stmt->execute(['DEMO-TELUR-FIFO']);
    $existing = $stmt->fetchColumn();
    if ($existing) {
        return (int)$existing;
    }

    $stmt = $db->prepare(
        "INSERT INTO products (kode_barang, nama_barang, kategori, satuan, harga_jual, stok_minimum, is_active)
         VALUES (?, ?, ?, ?, ?, ?, 1)"
    );
    $stmt->execute(['DEMO-TELUR-FIFO', 'Telur', 'Sembako', 'rak', 70000, 20]);
    return (int)$db->lastInsertId();
}

function ensureDemoCustomer(PDO $db): int {
    $stmt = $db->prepare("SELECT id FROM customers WHERE nama_toko = ? LIMIT 1");
    $stmt->execute(['Toko Demo FIFO']);
    $existing = $stmt->fetchColumn();
    if ($existing) {
        return (int)$existing;
    }

    $stmt = $db->prepare(
        "INSERT INTO customers (nama_toko, nama_pic, phone, alamat, catatan)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        'Toko Demo FIFO',
        'PIC Demo',
        '081234567890',
        'Alamat demo untuk pengujian FIFO',
        'Customer khusus demo FIFO telur',
    ]);
    return (int)$db->lastInsertId();
}

function getAdminId(PDO $db): int {
    $stmt = $db->query("SELECT id FROM users ORDER BY CASE WHEN username = 'admin' THEN 0 ELSE 1 END, id ASC LIMIT 1");
    $adminId = $stmt->fetchColumn();
    if (!$adminId) {
        throw new RuntimeException('User admin belum tersedia. Jalankan setup database terlebih dahulu.');
    }
    return (int)$adminId;
}

function getDemoStatus(string $host, string $user, string $pass, string $dbname): array {
    $status = [
        'db_ready' => false,
        'error' => null,
        'product_id' => null,
        'stock_total' => 0,
        'active_batches' => [],
        'sale_80_done' => false,
        'sale_30_done' => false,
    ];

    try {
        $db = appPDO($host, $user, $pass, $dbname);
        if (!tablesReady($db)) {
            $status['error'] = 'Struktur tabel belum lengkap.';
            return $status;
        }

        $status['db_ready'] = true;
        $stmt = $db->prepare("SELECT id FROM products WHERE kode_barang = ? LIMIT 1");
        $stmt->execute(['DEMO-TELUR-FIFO']);
        $productId = $stmt->fetchColumn();
        if (!$productId) {
            return $status;
        }

        $status['product_id'] = (int)$productId;

        $stmt = $db->prepare(
            "SELECT tanggal_masuk, qty_sisa, harga_modal
             FROM stock_batches
             WHERE product_id = ? AND qty_sisa > 0
             ORDER BY tanggal_masuk ASC, id ASC"
        );
        $stmt->execute([$productId]);
        $status['active_batches'] = $stmt->fetchAll();
        $status['stock_total'] = array_sum(array_map(static fn($row) => (int)$row['qty_sisa'], $status['active_batches']));

        $stmt = $db->prepare("SELECT COUNT(*) FROM sales WHERE catatan = ?");
        $stmt->execute(['DEMO FIFO STEP 1 - JUAL 80 RAK']);
        $status['sale_80_done'] = (int)$stmt->fetchColumn() > 0;

        $stmt = $db->prepare("SELECT COUNT(*) FROM sales WHERE catatan = ?");
        $stmt->execute(['DEMO FIFO STEP 2 - JUAL 30 RAK']);
        $status['sale_30_done'] = (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        $status['error'] = $e->getMessage();
    }

    return $status;
}

function seedDemoStock(string $host, string $user, string $pass, string $dbname, array &$messages): void {
    $db = appPDO($host, $user, $pass, $dbname);
    if (!tablesReady($db)) {
        throw new RuntimeException('Tabel aplikasi belum siap. Jalankan setup database terlebih dahulu.');
    }

    $productId = ensureDemoProduct($db);
    ensureDemoCustomer($db);
    $adminId = getAdminId($db);
    $batches = [
        ['2026-04-21', 100, 60000, 'DEMO FIFO - Telur 100 rak @ 60000'],
        ['2026-04-22', 100, 61000, 'DEMO FIFO - Telur 100 rak @ 61000'],
        ['2026-04-23', 100, 65000, 'DEMO FIFO - Telur 100 rak @ 65000'],
    ];

    $db->beginTransaction();
    try {
        $inserted = 0;
        foreach ($batches as [$tanggal, $qty, $harga, $keterangan]) {
            $check = $db->prepare(
                "SELECT id
                 FROM stock_batches
                 WHERE product_id = ? AND tanggal_masuk = ? AND qty_masuk = ? AND harga_modal = ? AND keterangan = ?
                 LIMIT 1"
            );
            $check->execute([$productId, $tanggal, $qty, $harga, $keterangan]);
            if ($check->fetchColumn()) {
                continue;
            }

            $stmt = $db->prepare(
                "INSERT INTO stock_batches (product_id, tanggal_masuk, qty_masuk, qty_sisa, harga_modal, keterangan, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$productId, $tanggal, $qty, $qty, $harga, $keterangan, $adminId]);
            $batchId = (int)$db->lastInsertId();

            $db->prepare(
                "INSERT INTO stock_movements (product_id, tipe, ref_type, ref_id, qty, keterangan)
                 VALUES (?, 'in', 'stock_batch', ?, ?, ?)"
            )->execute([$productId, $batchId, $qty, $keterangan]);

            $db->prepare(
                "INSERT INTO price_histories (product_id, harga_modal, sumber_batch_id, tanggal)
                 VALUES (?, ?, ?, ?)"
            )->execute([$productId, $harga, $batchId, $tanggal]);

            $inserted++;
        }

        $db->commit();
        if ($inserted > 0) {
            addMessage($messages, 'success', 'Demo FIFO stok berhasil disiapkan: 21 Apr 2026 @ 60000, 22 Apr 2026 @ 61000, 23 Apr 2026 @ 65000.');
        } else {
            addMessage($messages, 'info', 'Batch demo FIFO stok sudah pernah dibuat sebelumnya.');
        }
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

function seedDemoSale(string $host, string $user, string $pass, string $dbname, int $qty, string $date, string $note, array &$messages): void {
    $db = appPDO($host, $user, $pass, $dbname);
    if (!tablesReady($db)) {
        throw new RuntimeException('Tabel aplikasi belum siap. Jalankan setup database terlebih dahulu.');
    }

    $productId = ensureDemoProduct($db);
    $customerId = ensureDemoCustomer($db);
    $adminId = getAdminId($db);

    $check = $db->prepare("SELECT id FROM sales WHERE catatan = ? LIMIT 1");
    $check->execute([$note]);
    if ($check->fetchColumn()) {
        addMessage($messages, 'info', $note . ' sudah pernah dijalankan.');
        return;
    }

    if ($note === 'DEMO FIFO STEP 2 - JUAL 30 RAK') {
        $stepOne = $db->prepare("SELECT id FROM sales WHERE catatan = ? LIMIT 1");
        $stepOne->execute(['DEMO FIFO STEP 1 - JUAL 80 RAK']);
        if (!$stepOne->fetchColumn()) {
            throw new RuntimeException('Jalankan transaksi demo 1 (jual 80 rak) terlebih dahulu.');
        }
    }

    $productStmt = $db->prepare("SELECT harga_jual FROM products WHERE id = ? LIMIT 1");
    $productStmt->execute([$productId]);
    $hargaJual = (float)$productStmt->fetchColumn();

    require_once __DIR__ . '/app/config/app.php';
    require_once __DIR__ . '/app/config/database.php';
    require_once __DIR__ . '/app/helpers/invoice.php';
    require_once __DIR__ . '/app/helpers/fifo.php';
    require_once __DIR__ . '/app/models/Sale.php';

    $saleModel = new Sale();
    $saleId = $saleModel->create(
        [
            'customer_id' => $customerId,
            'tanggal_transaksi' => $date,
            'catatan' => $note,
            'created_by' => $adminId,
        ],
        [[
            'product_id' => $productId,
            'qty' => $qty,
            'harga_jual' => $hargaJual,
        ]]
    );

    addMessage($messages, 'success', "Transaksi demo berhasil dibuat ($note). ID penjualan: $saleId.");
}

function resetDemoData(string $host, string $user, string $pass, string $dbname, array &$messages): void {
    $db = appPDO($host, $user, $pass, $dbname);
    if (!tablesReady($db)) {
        throw new RuntimeException('Tabel aplikasi belum siap. Tidak ada demo yang bisa direset.');
    }

    $db->beginTransaction();
    try {
        $productStmt = $db->prepare("SELECT id FROM products WHERE kode_barang = ? LIMIT 1");
        $productStmt->execute(['DEMO-TELUR-FIFO']);
        $productId = $productStmt->fetchColumn();

        $saleStmt = $db->query(
            "SELECT id
             FROM sales
             WHERE catatan IN ('DEMO FIFO STEP 1 - JUAL 80 RAK', 'DEMO FIFO STEP 2 - JUAL 30 RAK')"
        );
        $saleIds = array_map('intval', array_column($saleStmt->fetchAll(), 'id'));

        if (!empty($saleIds)) {
            $placeholders = implode(', ', array_fill(0, count($saleIds), '?'));

            $itemStmt = $db->prepare("SELECT id FROM sale_items WHERE sale_id IN ($placeholders)");
            $itemStmt->execute($saleIds);
            $saleItemIds = array_map('intval', array_column($itemStmt->fetchAll(), 'id'));

            if (!empty($saleItemIds)) {
                $itemPlaceholders = implode(', ', array_fill(0, count($saleItemIds), '?'));
                $db->prepare("DELETE FROM sale_item_fifo WHERE sale_item_id IN ($itemPlaceholders)")->execute($saleItemIds);
                $db->prepare("DELETE FROM sale_items WHERE id IN ($itemPlaceholders)")->execute($saleItemIds);
            }

            $db->prepare("DELETE FROM sales WHERE id IN ($placeholders)")->execute($saleIds);
        }

        if ($productId) {
            $batchStmt = $db->prepare("SELECT id FROM stock_batches WHERE product_id = ?");
            $batchStmt->execute([(int)$productId]);
            $batchIds = array_map('intval', array_column($batchStmt->fetchAll(), 'id'));

            if (!empty($batchIds)) {
                $batchPlaceholders = implode(', ', array_fill(0, count($batchIds), '?'));
                $db->prepare("DELETE FROM price_histories WHERE sumber_batch_id IN ($batchPlaceholders)")->execute($batchIds);
            }

            $db->prepare("DELETE FROM price_histories WHERE product_id = ?")->execute([(int)$productId]);
            $db->prepare("DELETE FROM stock_movements WHERE product_id = ?")->execute([(int)$productId]);
            $db->prepare("DELETE FROM stock_batches WHERE product_id = ?")->execute([(int)$productId]);
            $db->prepare("DELETE FROM products WHERE id = ?")->execute([(int)$productId]);
        }

        $customerStmt = $db->prepare("DELETE FROM customers WHERE nama_toko = ?");
        $customerStmt->execute(['Toko Demo FIFO']);

        $db->commit();
        addMessage($messages, 'success', 'Data demo FIFO berhasil direset. Anda bisa seed ulang dari langkah awal.');
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

$action = $_POST['form_action'] ?? '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        switch ($action) {
            case 'setup':
                runSetup($host, $user, $pass, $dbname, $messages);
                break;
            case 'seed_demo_stock':
                seedDemoStock($host, $user, $pass, $dbname, $messages);
                break;
            case 'seed_demo_sale_80':
                seedDemoSale($host, $user, $pass, $dbname, 80, '2026-04-24', 'DEMO FIFO STEP 1 - JUAL 80 RAK', $messages);
                break;
            case 'seed_demo_sale_30':
                seedDemoSale($host, $user, $pass, $dbname, 30, '2026-04-25', 'DEMO FIFO STEP 2 - JUAL 30 RAK', $messages);
                break;
            case 'reset_demo_fifo':
                resetDemoData($host, $user, $pass, $dbname, $messages);
                break;
            default:
                addMessage($messages, 'info', 'Tidak ada aksi yang diproses.');
        }
    } catch (Throwable $e) {
        addMessage($messages, 'error', 'Proses gagal: ' . $e->getMessage());
    }
}

$status = getDemoStatus($host, $user, $pass, $dbname);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Toko Adam</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #8b1a1a 0%, #5c1010 45%, #2d2320 100%);
            color: #111827;
            padding: 24px;
        }
        .wrapper {
            width: 100%;
            max-width: 980px;
            margin: 0 auto;
        }
        .card {
            background: #ffffff;
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 28px 70px rgba(0, 0, 0, 0.24);
        }
        h1 {
            color: #8b1a1a;
            font-size: 30px;
            margin-bottom: 10px;
        }
        .sub {
            color: #6b7280;
            margin-bottom: 22px;
            line-height: 1.6;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        .panel {
            padding: 20px;
            border-radius: 18px;
            border: 1px solid rgba(17, 24, 39, 0.08);
            background: #faf8f5;
        }
        .panel h2 {
            font-size: 18px;
            margin-bottom: 8px;
        }
        .panel p {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
        }
        .panel ul {
            margin: 12px 0 0 18px;
            color: #4b5563;
            line-height: 1.7;
            font-size: 14px;
        }
        .msg {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 12px;
            font-size: 14px;
            line-height: 1.5;
        }
        .msg.success { background: #dff5e8; color: #0f5132; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        .msg.info { background: #dbeafe; color: #1d4ed8; }
        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }
        form.inline { display: inline; }
        button,
        .link-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 18px;
            border-radius: 12px;
            border: none;
            background: #8b1a1a;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-secondary {
            background: #ffffff;
            color: #111827;
            border: 1px solid rgba(17, 24, 39, 0.12);
        }
        .status-box {
            margin-top: 18px;
            padding: 20px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid rgba(17, 24, 39, 0.08);
        }
        .status-box h3 {
            margin-bottom: 12px;
            font-size: 18px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .status-item {
            padding: 16px;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid rgba(17, 24, 39, 0.08);
        }
        .status-item .label {
            display: block;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }
        .status-item strong {
            font-size: 22px;
        }
        .batch-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .batch-list th,
        .batch-list td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(17, 24, 39, 0.08);
            text-align: left;
            font-size: 14px;
        }
        .batch-list th {
            color: #6b7280;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.08em;
        }
        .hint {
            margin-top: 14px;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
        }
        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }
        @media (max-width: 860px) {
            .grid,
            .status-grid {
                grid-template-columns: 1fr;
            }
            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h1>Setup Toko Adam</h1>
            <p class="sub">Halaman ini dipakai untuk inisialisasi database sekaligus menyiapkan demo FIFO step-by-step sesuai skenario telur 3 batch, lalu jual 80 rak dan 30 rak.</p>

            <?php foreach ($messages as $msg): ?>
                <div class="msg <?= htmlspecialchars($msg[0]) ?>"><?= htmlspecialchars($msg[1]) ?></div>
            <?php endforeach; ?>

            <div class="grid">
                <div class="panel">
                    <h2>1. Setup Database</h2>
                    <p>Jalankan ini sekali untuk membuat database, tabel, user admin, dan folder upload.</p>
                    <ul>
                        <li>Host: <?= htmlspecialchars($host) ?></li>
                        <li>User: <?= htmlspecialchars($user) ?></li>
                        <li>Database: <?= htmlspecialchars($dbname) ?></li>
                    </ul>
                    <div class="button-row">
                        <form method="POST" class="inline">
                            <input type="hidden" name="form_action" value="setup">
                            <button type="submit">Jalankan Setup</button>
                        </form>
                    </div>
                </div>

                <div class="panel">
                    <h2>2. Demo FIFO Bertahap</h2>
                    <p>Gunakan tiga tombol ini berurutan untuk mendemokan FIFO seperti permintaan:</p>
                    <ul>
                        <li>Seed stok telur 3 batch: 21 Apr 2026 @ 60000, 22 Apr 2026 @ 61000, 23 Apr 2026 @ 65000</li>
                        <li>Transaksi demo 1: jual telur 80 rak</li>
                        <li>Transaksi demo 2: jual telur 30 rak</li>
                    </ul>
                    <div class="button-row">
                        <form method="POST" class="inline">
                            <input type="hidden" name="form_action" value="seed_demo_stock">
                            <button type="submit">Seed 3 Batch Telur</button>
                        </form>
                        <form method="POST" class="inline">
                            <input type="hidden" name="form_action" value="seed_demo_sale_80">
                            <button type="submit" class="btn-secondary">Transaksi 1: Jual 80 Rak</button>
                        </form>
                        <form method="POST" class="inline">
                            <input type="hidden" name="form_action" value="seed_demo_sale_30">
                            <button type="submit" class="btn-secondary">Transaksi 2: Jual 30 Rak</button>
                        </form>
                        <form method="POST" class="inline">
                            <input type="hidden" name="form_action" value="reset_demo_fifo">
                            <button type="submit" class="btn-secondary">Reset Demo FIFO</button>
                        </form>
                    </div>
                    <p class="hint">Sesudah tiap langkah dijalankan, buka Laporan Stok lalu klik item <strong>Telur</strong> untuk melihat sisa batch FIFO, histori barang masuk, audit pemakaian FIFO, dan grafik harga modal.</p>
                </div>
            </div>

            <div class="status-box">
                <h3>Status Demo FIFO</h3>

                <?php if (!$status['db_ready']): ?>
                    <p class="hint">
                        Database belum siap.
                        <?php if ($status['error']): ?>
                            Detail: <?= htmlspecialchars($status['error']) ?>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <div class="status-grid">
                        <div class="status-item">
                            <span class="label">Produk Demo</span>
                            <strong><?= $status['product_id'] ? 'Siap' : 'Belum' ?></strong>
                        </div>
                        <div class="status-item">
                            <span class="label">Stok Aktif</span>
                            <strong><?= number_format($status['stock_total']) ?> rak</strong>
                        </div>
                        <div class="status-item">
                            <span class="label">Progress Penjualan</span>
                            <strong><?= ($status['sale_80_done'] ? '80' : '0') + ($status['sale_30_done'] ? 30 : 0) ?> rak</strong>
                        </div>
                    </div>

                    <table class="batch-list">
                        <thead>
                            <tr><th>Urutan FIFO</th><th>Tanggal Masuk</th><th>Sisa Batch</th><th>Harga Modal</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($status['active_batches'])): ?>
                                <tr><td colspan="4">Belum ada batch demo aktif.</td></tr>
                            <?php else: foreach ($status['active_batches'] as $index => $batch): ?>
                                <tr>
                                    <td><?= $index === 0 ? 'Keluar berikutnya' : 'Antrian ' . ($index + 1) ?></td>
                                    <td><?= htmlspecialchars($batch['tanggal_masuk']) ?></td>
                                    <td><?= number_format((int)$batch['qty_sisa']) ?> rak</td>
                                    <td>Rp <?= number_format((float)$batch['harga_modal'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>

                    <p class="hint">
                        Ekspektasi FIFO:
                        setelah seed stok total menjadi 300 rak.
                        Setelah transaksi 1, sisa menjadi 20 rak @ 60000, 100 rak @ 61000, 100 rak @ 65000.
                        Setelah transaksi 2, batch pertama habis lalu 10 rak diambil dari batch kedua, sehingga sisa akhir menjadi 90 rak @ 61000 dan 100 rak @ 65000.
                    </p>
                <?php endif; ?>
            </div>

            <div class="footer-links">
                <a href="index.php?page=login" class="link-btn">Ke Halaman Login</a>
                <?php if ($status['product_id']): ?>
                    <a href="index.php?page=reports&action=stock_detail&id=<?= $status['product_id'] ?>" class="link-btn btn-secondary">Lihat Detail FIFO Telur</a>
                <?php else: ?>
                    <a href="index.php?page=reports&action=stock" class="link-btn btn-secondary">Buka Laporan Stok</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
