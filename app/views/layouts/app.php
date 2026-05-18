<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> - <?= APP_NAME ?></title>
    <meta name="description" content="<?= APP_NAME ?> - <?= APP_TAGLINE ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/print.css">
</head>
<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = $currentPage ?? '';
$action = $action ?? '';
$icon = static function (string $name): string {
    $icons = [
        'store' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10.5V20h16v-9.5M3 9l2-5h14l2 5M7 20v-5h10v5M8 9a2 2 0 0 0 4 0M12 9a2 2 0 0 0 4 0M4 9a2 2 0 0 0 4 0M16 9a2 2 0 0 0 4 0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'dashboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h7V4H4zm9 7h7V11h-7zM4 20h7v-5H4zm9-11h7V4h-7z" fill="currentColor"/></svg>',
        'products' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5-8-4.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'customers' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11A3.5 3.5 0 1 0 9.5 4a3.5 3.5 0 0 0 0 7Zm8.5 10v-2a4 4 0 0 0-3-3.87M15 4.13a3.5 3.5 0 0 1 0 6.74" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'stock' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M7 8h10M7 12h10M7 16h10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.7"/><path d="M5 5h14v14H5z" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'sales' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="19" r="1.75" fill="currentColor"/><circle cx="17" cy="19" r="1.75" fill="currentColor"/><path d="M3 4h2l2.2 10.2A2 2 0 0 0 9.15 16H18a2 2 0 0 0 1.95-1.55L21 8H7" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'history' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5l3 2M3 12a9 9 0 1 0 3-6.7M3 4v5h5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'report' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h7l5 5v13H7z" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.7"/><path d="M14 3v5h5M10 13h6M10 17h6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.7"/></svg>',
        'analytics' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16M7 15l3-3 3 2 4-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'finance' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M16 7.5c0-1.7-1.8-3-4-3s-4 1.3-4 3 1.8 3 4 3 4 1.3 4 3-1.8 3-4 3-4-1.3-4-3" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11A4 4 0 1 0 9 3a4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>',
        'menu' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/></svg>',
    ];

    return $icons[$name] ?? $icons['dashboard'];
};
?>
<body class="page-<?= htmlspecialchars($currentPage ?: 'app') ?>">
    <div class="app-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon"><?= $icon('store') ?></div>
                <div class="brand-copy">
                    <span class="brand-kicker">Retail Control Center</span>
                    <h2><?= APP_NAME ?></h2>
                    <small><?= APP_TAGLINE ?></small>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">Menu Utama</div>
                <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('dashboard') ?></span>
                    <span>Dashboard</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=products" class="nav-item <?= $currentPage === 'products' ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('products') ?></span>
                    <span>Master Barang</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=customers" class="nav-item <?= $currentPage === 'customers' ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('customers') ?></span>
                    <span>Pelanggan & Supplier</span>
                </a>

                <div class="nav-section">Transaksi</div>
                <a href="<?= BASE_URL ?>/index.php?page=stock-in" class="nav-item <?= $currentPage === 'stock-in' ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('stock') ?></span>
                    <span>Barang Masuk</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=sales&action=create" class="nav-item <?= $currentPage === 'sales' && $action === 'create' ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('sales') ?></span>
                    <span>Penjualan</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=sales" class="nav-item <?= $currentPage === 'sales' && $action !== 'create' ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('history') ?></span>
                    <span>Riwayat Transaksi</span>
                </a>

                <div class="nav-section">Laporan</div>
                <a href="<?= BASE_URL ?>/index.php?page=reports&action=sales" class="nav-item <?= $currentPage === 'reports' && ($action === 'sales' || $action === '') ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('report') ?></span>
                    <span>Lap. Penjualan</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=reports&action=stock" class="nav-item <?= $currentPage === 'reports' && $action === 'stock' ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('analytics') ?></span>
                    <span>Lap. Stok</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=reports&action=financial" class="nav-item <?= $currentPage === 'reports' && $action === 'financial' ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('finance') ?></span>
                    <span>Lap. Keuangan</span>
                </a>

                <?php if (isAdmin()): ?>
                <div class="nav-section">Admin</div>
                <a href="<?= BASE_URL ?>/index.php?page=users" class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon('users') ?></span>
                    <span>Manajemen User</span>
                </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-panel">
                    <span class="sidebar-panel-label">Versi Sistem</span>
                    <strong><?= APP_VERSION ?></strong>
                </div>
                <a href="<?= BASE_URL ?>/index.php?page=logout" class="nav-item" data-confirm="Yakin ingin logout?">
                    <span class="nav-icon"><?= $icon('logout') ?></span>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="btn-menu-toggle" id="menuToggle" aria-label="Buka navigasi"><?= $icon('menu') ?></button>
                    <div>
                        <div class="topbar-eyebrow">Workspace</div>
                        <h1><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-chip">
                        <span class="topbar-chip-label">Hari ini</span>
                        <strong><?= date('d M Y') ?></strong>
                    </div>
                    <div class="topbar-user">
                        <div>
                            <div class="user-name"><?= htmlspecialchars(currentUserName()) ?></div>
                            <div class="user-role"><?= htmlspecialchars(currentUserRole()) ?></div>
                        </div>
                        <div class="user-avatar"><?= strtoupper(substr(currentUserName(), 0, 1)) ?></div>
                    </div>
                </div>
            </header>

            <main class="content-area">
                <?php $flash = getFlash(); if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?>">
                        <span class="alert-mark"><?= $flash['type'] === 'success' ? 'OK' : ($flash['type'] === 'error' ? 'ERR' : 'INFO') ?></span>
                        <span><?= htmlspecialchars($flash['message']) ?></span>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (isset($content) && file_exists($content)) require $content; ?>
            </main>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/assets/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/sortable.js"></script>
    <?php if (isset($extraJs)): ?>
        <script src="<?= BASE_URL ?>/public/assets/js/<?= $extraJs ?>"></script>
    <?php endif; ?>
</body>
</html>
