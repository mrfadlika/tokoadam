<section class="dashboard-hero">
    <div class="hero-panel">
        <span class="hero-eyebrow">Overview Harian</span>
        <h2>Kontrol toko grosir dalam satu layar.</h2>
        <p>Pantau performa penjualan, stok kritis, dan pelanggan paling aktif tanpa harus berpindah halaman. Dashboard ini dirapikan untuk membantu ambil keputusan lebih cepat.</p>
        <div class="hero-actions">
            <a href="<?= BASE_URL ?>/index.php?page=sales&action=create" class="btn btn-primary">Buat Transaksi</a>
            <a href="<?= BASE_URL ?>/index.php?page=products&action=create" class="btn btn-outline">Tambah Barang</a>
        </div>
        <div class="hero-meta">
            <div class="hero-pill">
                <span class="hero-pill-label">Tanggal Aktif</span>
                <strong><?= formatDate(date('Y-m-d')) ?></strong>
            </div>
            <div class="hero-pill">
                <span class="hero-pill-label">Pelanggan Top</span>
                <strong><?= !empty($data['topCustomers']) ? htmlspecialchars($data['topCustomers'][0]['nama_toko']) : 'Belum ada data' ?></strong>
            </div>
        </div>
    </div>

    <div class="card insight-panel">
        <span class="panel-kicker">Quick Insight</span>
        <h3 class="panel-title">Fokus utama hari ini</h3>
        <div class="insight-list">
            <div class="insight-item">
                <div class="insight-badge">ST</div>
                <div>
                    <strong><?= count($data['lowStock']) ?> item perlu perhatian</strong>
                    <span>Produk di bawah stok minimum untuk diprioritaskan restock.</span>
                </div>
            </div>
            <div class="insight-item">
                <div class="insight-badge">TR</div>
                <div>
                    <strong><?= number_format($data['todayTransactions']) ?> transaksi hari ini</strong>
                    <span>Aktivitas penjualan masuk real-time di dashboard utama.</span>
                </div>
            </div>
            <div class="insight-item">
                <div class="insight-badge">OM</div>
                <div>
                    <strong><?= formatRupiah($data['todayRevenue']) ?> omzet</strong>
                    <span>Total pemasukan harian dari transaksi yang sudah tercatat.</span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon primary">PR</div>
        <div class="stat-info">
            <div class="stat-label">Total Barang</div>
            <div class="stat-value"><?= number_format($data['totalProducts']) ?></div>
            <div class="stat-sub">Produk aktif yang tersedia di sistem</div>
        </div>
    </div>
    <div class="stat-card danger">
        <div class="stat-icon danger">AL</div>
        <div class="stat-info">
            <div class="stat-label">Stok Menipis</div>
            <div class="stat-value"><?= count($data['lowStock']) ?></div>
            <div class="stat-sub">Perlu restock agar penjualan tetap aman</div>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon success">TX</div>
        <div class="stat-info">
            <div class="stat-label">Transaksi Hari Ini</div>
            <div class="stat-value"><?= number_format($data['todayTransactions']) ?></div>
            <div class="stat-sub"><?= formatDate(date('Y-m-d')) ?></div>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon warning">RP</div>
        <div class="stat-info">
            <div class="stat-label">Omzet Hari Ini</div>
            <div class="stat-value"><?= formatRupiah($data['todayRevenue']) ?></div>
            <div class="stat-sub">Nilai penjualan yang sudah masuk</div>
        </div>
    </div>
</div>

<div class="surface-grid">
    <div class="card">
        <div class="card-header">
            <div>
                <h3>Transaksi Terbaru</h3>
                <p class="section-note">Ringkasan transaksi terakhir yang masuk ke sistem.</p>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=sales" class="btn btn-sm btn-outline">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No. Nota</th>
                        <th>Pelanggan</th>
                        <th class="text-right">Total</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['recentSales'])): ?>
                        <tr><td colspan="4"><div class="empty-state"><div class="empty-icon">TR</div><h4>Belum ada transaksi</h4><p>Mulai catat penjualan pertama untuk melihat aktivitas terbaru di sini.</p></div></td></tr>
                    <?php else: foreach ($data['recentSales'] as $sale): ?>
                        <tr>
                            <td><span class="font-mono"><?= $sale['nomor_transaksi'] ?></span></td>
                            <td><?= htmlspecialchars($sale['nama_toko']) ?></td>
                            <td class="text-right fw-bold"><?= formatRupiah($sale['total']) ?></td>
                            <td class="text-muted"><?= timeAgo($sale['created_at']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3>Prioritas Restock</h3>
                <p class="section-note">Produk dengan stok di bawah batas minimum.</p>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=reports&action=stock" class="btn btn-sm btn-outline">Detail Stok</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Minimum</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['lowStock'])): ?>
                        <tr><td colspan="4"><div class="empty-state"><div class="empty-icon">OK</div><h4>Semua stok aman</h4><p>Belum ada produk yang menyentuh batas minimum stok.</p></div></td></tr>
                    <?php else: foreach (array_slice($data['lowStock'], 0, 8) as $item): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($item['nama_barang']) ?></div>
                                <div class="text-muted" style="font-size:0.82rem"><?= $item['kode_barang'] ?></div>
                            </td>
                            <td class="text-center fw-bold <?= $item['stok_total'] == 0 ? 'text-danger' : 'text-warning' ?>"><?= $item['stok_total'] ?></td>
                            <td class="text-center"><?= $item['stok_minimum'] ?></td>
                            <td>
                                <?php if ($item['stok_total'] == 0): ?>
                                    <span class="badge badge-danger">Habis</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Menipis</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($data['topCustomers'])): ?>
<div class="card mt-3">
    <div class="card-header">
        <div>
            <h3>Pelanggan Teratas</h3>
            <p class="section-note">Pelanggan dengan frekuensi transaksi dan nilai belanja tertinggi.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th class="text-center">Jumlah Transaksi</th>
                    <th class="text-right">Total Belanja</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['topCustomers'] as $c): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($c['nama_toko']) ?></td>
                    <td class="text-center"><?= $c['jumlah_transaksi'] ?></td>
                    <td class="text-right fw-bold"><?= formatRupiah($c['total_belanja']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
