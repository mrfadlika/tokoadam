<!-- Sales History -->
<div class="toolbar">
    <form method="GET" class="filter-group">
        <input type="hidden" name="page" value="sales">
        <input type="text" name="search" class="form-control" placeholder="Cari nota/pelanggan..." value="<?= htmlspecialchars(get('search')) ?>">
        <select name="customer_id" class="form-control">
            <option value="">Semua Pelanggan</option>
            <?php foreach ($customers as $c): ?>
                <option value="<?= $c['id'] ?>" <?= get('customer_id') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nama_toko']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars(get('date_from')) ?>">
        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars(get('date_to')) ?>">
        <button type="submit" class="btn btn-outline">Filter</button>
    </form>
    <a href="<?= BASE_URL ?>/index.php?page=sales&action=create" class="btn btn-primary">+ Transaksi Baru</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-compact-mobile">
            <thead>
                <tr>
                    <th>No. Nota</th>
                    <th class="mobile-hide-col">Tanggal</th>
                    <th class="mobile-hide-col">Pelanggan</th>
                    <th class="text-right">Total</th>
                    <th class="mobile-hide-col">User</th>
                    <th class="text-center mobile-hide-col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">TR</div><h4>Belum ada transaksi</h4></div></td></tr>
                <?php else: foreach ($sales as $s): ?>
                <tr>
                    <td>
                        <span class="font-mono"><?= htmlspecialchars($s['nomor_transaksi']) ?></span>
                        <div class="mobile-only-inline"><?= formatDate($s['tanggal_transaksi']) ?> · <?= htmlspecialchars($s['nama_toko']) ?></div>
                    </td>
                    <td class="text-nowrap mobile-hide-col"><?= formatDate($s['tanggal_transaksi']) ?></td>
                    <td class="fw-bold mobile-hide-col"><?= htmlspecialchars($s['nama_toko']) ?></td>
                    <td class="text-right fw-bold"><?= formatRupiah($s['total']) ?></td>
                    <td class="text-muted mobile-hide-col"><?= htmlspecialchars($s['created_by_name'] ?? '-') ?></td>
                    <td class="text-center mobile-hide-col">
                        <a href="<?= BASE_URL ?>/index.php?page=sales&action=invoice&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline">Nota</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (($totalPages ?? 0) > 1): ?>
    <div class="card-footer">
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= BASE_URL ?>/index.php?page=sales&p=<?= $i ?>&search=<?= urlencode(get('search') ?? '') ?>&customer_id=<?= urlencode(get('customer_id') ?? '') ?>&date_from=<?= urlencode(get('date_from') ?? '') ?>&date_to=<?= urlencode(get('date_to') ?? '') ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
