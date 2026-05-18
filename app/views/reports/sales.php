<?php $isProductFiltered = !empty($productId); ?>

<!-- Sales Report -->
<div class="toolbar">
    <form method="GET" class="filter-group">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="action" value="sales">
        <select name="customer_id" class="form-control">
            <option value="">Semua Pelanggan</option>
            <?php foreach ($customers as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($customerId ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nama_toko']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="product_id" class="form-control">
            <option value="">Semua Produk</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= $product['id'] ?>" <?= ($productId ?? '') == $product['id'] ? 'selected' : '' ?>><?= htmlspecialchars($product['nama_barang']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
        <button type="submit" class="btn btn-outline">Filter</button>
        <button type="button" class="btn btn-outline no-print" onclick="window.print()">Cetak</button>
    </form>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card">
        <div class="stat-icon success">TX</div>
        <div class="stat-info">
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value"><?= number_format($totals['total_transaksi'] ?? 0) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">IT</div>
        <div class="stat-info">
            <div class="stat-label">Total Item</div>
            <div class="stat-value"><?= number_format($totals['total_item'] ?? 0) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">RP</div>
        <div class="stat-info">
            <div class="stat-label"><?= $isProductFiltered ? 'Nominal Produk' : 'Total Nominal' ?></div>
            <div class="stat-value"><?= formatRupiah($totals['total_nominal'] ?? 0) ?></div>
        </div>
    </div>
</div>

<?php if (!empty($perCustomer)): ?>
<div class="card mb-3">
    <div class="card-header"><h3>Rekap per Pelanggan</h3></div>
    <div class="table-responsive">
        <table data-sortable>
            <thead><tr><th data-sort-key="pelanggan">Pelanggan</th><th class="text-center" data-sort-key="transaksi">Jml Transaksi</th><th class="text-right" data-sort-key="item">Total Item</th><th class="text-right" data-sort-key="nominal"><?= $isProductFiltered ? 'Nominal Produk' : 'Total Nominal' ?></th></tr></thead>
            <tbody>
                <?php foreach ($perCustomer as $pc): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($pc['nama_toko']) ?></td>
                    <td class="text-center" data-sort-value="<?= (int)$pc['jumlah_transaksi'] ?>"><?= number_format($pc['jumlah_transaksi']) ?></td>
                    <td class="text-right" data-sort-value="<?= (int)$pc['total_item'] ?>"><?= number_format($pc['total_item']) ?></td>
                    <td class="text-right fw-bold" data-sort-value="<?= $pc['total_nominal'] ?>"><?= formatRupiah($pc['total_nominal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div>
            <h3>Detail Transaksi</h3>
            <span class="text-muted"><?= formatDate($dateFrom) ?> - <?= formatDate($dateTo) ?></span>
        </div>
        <?php if ($isProductFiltered): ?>
            <span class="text-muted">Qty dan nominal dihitung dari produk yang dipilih.</span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table-compact-mobile" data-sortable>
            <thead>
                <tr>
                    <th data-sort-key="nota">No. Nota</th>
                    <th class="mobile-hide-col" data-sort-key="tanggal">Tanggal</th>
                    <th class="mobile-hide-col" data-sort-key="pelanggan">Pelanggan</th>
                    <th class="text-right mobile-hide-col" data-sort-key="qty">Qty Item</th>
                    <th class="text-right" data-sort-key="nominal"><?= $isProductFiltered ? 'Nominal Produk' : 'Total Nominal' ?></th>
                    <th class="mobile-hide-col">User</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($salesData)): ?>
                    <tr><td colspan="6" class="text-center text-muted" style="padding:32px">Tidak ada data</td></tr>
                <?php else: foreach ($salesData as $s): ?>
                <tr>
                    <td>
                        <span class="font-mono"><?= htmlspecialchars($s['nomor_transaksi']) ?></span>
                        <div class="mobile-only-inline"><?= formatDate($s['tanggal_transaksi']) ?> · <?= htmlspecialchars($s['nama_toko']) ?></div>
                    </td>
                    <td class="mobile-hide-col" data-sort-value="<?= $s['tanggal_transaksi'] ?>"><?= formatDate($s['tanggal_transaksi']) ?></td>
                    <td class="fw-bold mobile-hide-col"><?= htmlspecialchars($s['nama_toko']) ?></td>
                    <td class="text-right mobile-hide-col" data-sort-value="<?= (int)($s['total_item'] ?? 0) ?>"><?= number_format($s['total_item'] ?? 0) ?></td>
                    <td class="text-right fw-bold" data-sort-value="<?= $s['total_nominal_terfilter'] ?? 0 ?>"><?= formatRupiah($s['total_nominal_terfilter'] ?? 0) ?></td>
                    <td class="text-muted mobile-hide-col"><?= htmlspecialchars($s['created_by_name'] ?? '-') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
