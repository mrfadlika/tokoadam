<!-- Financial Report -->
<div class="toolbar">
    <form method="GET" class="filter-group">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="action" value="financial">
        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
        <button type="submit" class="btn btn-outline">🔍 Filter</button>
        <button type="button" class="btn btn-outline no-print" onclick="window.print()">🖨️ Cetak</button>
    </form>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="stat-card">
        <div class="stat-icon success">💵</div>
        <div class="stat-info">
            <div class="stat-label">Total Penjualan</div>
            <div class="stat-value"><?= formatRupiah($summary['total_penjualan']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">📦</div>
        <div class="stat-info">
            <div class="stat-label">Total Modal (FIFO)</div>
            <div class="stat-value"><?= formatRupiah($summary['total_modal']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">📈</div>
        <div class="stat-info">
            <div class="stat-label">Laba Kotor</div>
            <div class="stat-value <?= $summary['laba_kotor'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= formatRupiah($summary['laba_kotor']) ?></div>
            <div class="stat-sub">Margin: <?= $summary['margin'] ?>%</div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>📊 Ringkasan Keuangan</h3><span class="text-muted"><?= formatDate($dateFrom) ?> — <?= formatDate($dateTo) ?></span></div>
    <div class="card-body">
        <table style="max-width:500px">
            <tr><td style="padding:10px 16px">Total Penjualan</td><td class="text-right fw-bold" style="padding:10px 16px"><?= formatRupiah($summary['total_penjualan']) ?></td></tr>
            <tr><td style="padding:10px 16px">Total Modal (FIFO)</td><td class="text-right fw-bold" style="padding:10px 16px;color:var(--warning)">(<?= formatRupiah($summary['total_modal']) ?>)</td></tr>
            <tr style="border-top:2px solid var(--border)"><td style="padding:10px 16px" class="fw-bold">Estimasi Laba Kotor</td><td class="text-right fw-bold" style="padding:10px 16px;font-size:18px;color:<?= $summary['laba_kotor'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>"><?= formatRupiah($summary['laba_kotor']) ?></td></tr>
        </table>
    </div>
</div>

<?php if (!empty($perCustomer)): ?>
<div class="card">
    <div class="card-header"><h3>🏬 Penjualan per Toko</h3></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Toko</th><th class="text-center">Transaksi</th><th class="text-right">Total Nominal</th></tr></thead>
            <tbody>
                <?php foreach ($perCustomer as $pc): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($pc['nama_toko']) ?></td>
                    <td class="text-center"><?= $pc['jumlah_transaksi'] ?></td>
                    <td class="text-right fw-bold"><?= formatRupiah($pc['total_nominal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
