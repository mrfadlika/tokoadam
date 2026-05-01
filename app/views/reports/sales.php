<!-- Sales Report -->
<div class="toolbar">
    <form method="GET" class="filter-group">
        <input type="hidden" name="page" value="reports">
        <select name="customer_id" class="form-control">
            <option value="">Semua Toko</option>
            <?php foreach ($customers as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($customerId ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nama_toko']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
        <button type="submit" class="btn btn-outline">🔍 Filter</button>
        <button type="button" class="btn btn-outline no-print" onclick="window.print()">🖨️ Cetak</button>
    </form>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
    <div class="stat-card">
        <div class="stat-icon success">🛒</div>
        <div class="stat-info">
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value"><?= number_format($totals['total_transaksi']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">💰</div>
        <div class="stat-info">
            <div class="stat-label">Total Nominal</div>
            <div class="stat-value"><?= formatRupiah($totals['total_nominal']) ?></div>
        </div>
    </div>
</div>

<?php if (!empty($perCustomer)): ?>
<div class="card mb-3">
    <div class="card-header"><h3>📊 Rekap per Toko</h3></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Toko</th><th class="text-center">Jml Transaksi</th><th class="text-right">Total Item</th><th class="text-right">Total Nominal</th></tr></thead>
            <tbody>
                <?php foreach ($perCustomer as $pc): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($pc['nama_toko']) ?></td>
                    <td class="text-center"><?= $pc['jumlah_transaksi'] ?></td>
                    <td class="text-right"><?= number_format($pc['total_item']) ?></td>
                    <td class="text-right fw-bold"><?= formatRupiah($pc['total_nominal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h3>📋 Detail Transaksi</h3><span class="text-muted"><?= formatDate($dateFrom) ?> — <?= formatDate($dateTo) ?></span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>No. Nota</th><th>Tanggal</th><th>Toko</th><th class="text-right">Total</th><th>Kasir</th></tr></thead>
            <tbody>
                <?php if (empty($salesData)): ?>
                    <tr><td colspan="5" class="text-center text-muted" style="padding:32px">Tidak ada data</td></tr>
                <?php else: foreach ($salesData as $s): ?>
                <tr>
                    <td><span class="font-mono"><?= $s['nomor_transaksi'] ?></span></td>
                    <td><?= formatDate($s['tanggal_transaksi']) ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($s['nama_toko']) ?></td>
                    <td class="text-right fw-bold"><?= formatRupiah($s['total']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($s['created_by_name'] ?? '-') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
