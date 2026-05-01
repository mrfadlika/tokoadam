<!-- Stock In List -->
<div class="toolbar">
    <form method="GET" class="filter-group">
        <input type="hidden" name="page" value="stock-in">
        <select name="product_id" class="form-control" onchange="this.form.submit()">
            <option value="">Semua Barang</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>" <?= get('product_id') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_barang']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" class="form-control" value="<?= get('date_from') ?>" placeholder="Dari">
        <input type="date" name="date_to" class="form-control" value="<?= get('date_to') ?>" placeholder="Sampai">
        <button type="submit" class="btn btn-outline">🔍 Filter</button>
    </form>
    <a href="<?= BASE_URL ?>/index.php?page=stock-in&action=create" class="btn btn-primary">+ Input Barang Masuk</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>Tanggal</th><th>Kode</th><th>Nama Barang</th><th class="text-right">Qty Masuk</th><th class="text-right">Sisa</th><th class="text-right">Harga Modal</th><th>Keterangan</th><th>Oleh</th></tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📥</div><h4>Belum ada data barang masuk</h4></div></td></tr>
                <?php else: foreach ($batches as $b): ?>
                <tr>
                    <td class="text-nowrap"><?= formatDate($b['tanggal_masuk']) ?></td>
                    <td><span class="font-mono"><?= $b['kode_barang'] ?></span></td>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?page=reports&action=stock_detail&id=<?= $b['product_id'] ?>" class="table-link"><?= htmlspecialchars($b['nama_barang']) ?></a>
                        <div class="table-note">Lihat detail FIFO dan harga</div>
                    </td>
                    <td class="text-right"><?= number_format($b['qty_masuk']) ?> <?= $b['satuan'] ?></td>
                    <td class="text-right fw-bold <?= $b['qty_sisa'] == 0 ? 'text-muted' : '' ?>"><?= number_format($b['qty_sisa']) ?></td>
                    <td class="text-right"><?= formatRupiah($b['harga_modal']) ?></td>
                    <td class="text-muted"><?= truncate(htmlspecialchars($b['keterangan'] ?? '-'), 30) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($b['created_by_name'] ?? '-') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (($totalPages ?? 0) > 1): ?>
    <div class="card-footer">
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= BASE_URL ?>/index.php?page=stock-in&p=<?= $i ?>&product_id=<?= get('product_id') ?>&date_from=<?= get('date_from') ?>&date_to=<?= get('date_to') ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
