<!-- Stock In List -->
<div class="toolbar">
    <form method="GET" class="filter-group">
        <input type="hidden" name="page" value="stock-in">
        <select name="product_id" class="form-control">
            <option value="">Semua Barang</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>" <?= get('product_id') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_barang']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="supplier_id" class="form-control">
            <option value="">Semua Supplier</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= $supplier['id'] ?>" <?= get('supplier_id') == $supplier['id'] ? 'selected' : '' ?>><?= htmlspecialchars($supplier['nama_toko']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" class="form-control" value="<?= get('date_from') ?>" placeholder="Dari">
        <input type="date" name="date_to" class="form-control" value="<?= get('date_to') ?>" placeholder="Sampai">
        <button type="submit" class="btn btn-outline">Filter</button>
    </form>
    <a href="<?= BASE_URL ?>/index.php?page=stock-in&action=create" class="btn btn-primary">+ Input Barang Masuk</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Supplier</th>
                    <th>No. Nota</th>
                    <th>Arsip Nota</th>
                    <th class="text-right">Qty Masuk</th>
                    <th class="text-right">Sisa</th>
                    <th class="text-right">Harga Modal</th>
                    <th>Keterangan</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <div class="empty-icon">IN</div>
                                <h4>Belum ada data barang masuk</h4>
                            </div>
                        </td>
                    </tr>
                <?php else: foreach ($batches as $b): ?>
                <tr>
                    <td class="text-nowrap"><?= formatDate($b['tanggal_masuk']) ?></td>
                    <td><span class="font-mono"><?= htmlspecialchars($b['kode_barang']) ?></span></td>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?page=reports&action=stock_detail&id=<?= $b['product_id'] ?>" class="table-link"><?= htmlspecialchars($b['nama_barang']) ?></a>
                        <div class="table-note">Lihat detail FIFO dan harga</div>
                    </td>
                    <td><?= htmlspecialchars($b['supplier_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($b['nomor_nota'] ?? '-') ?></td>
                    <td>
                        <?php if (!empty($b['nota_file'])): ?>
                            <?php
                            $notaUrl = BASE_URL . '/public/uploads/stock-notes/' . rawurlencode($b['nota_file']);
                            $notaExt = strtolower(pathinfo($b['nota_file'], PATHINFO_EXTENSION));
                            $isImageNota = in_array($notaExt, ['jpg', 'jpeg', 'png', 'webp'], true);
                            ?>
                            <?php if ($isImageNota): ?>
                                <a href="<?= htmlspecialchars($notaUrl) ?>" target="_blank" rel="noopener" title="Buka foto nota">
                                    <img src="<?= htmlspecialchars($notaUrl) ?>" alt="Foto nota" style="width:54px;height:54px;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
                                </a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($notaUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">Buka Arsip</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?= number_format($b['qty_masuk']) ?> <?= htmlspecialchars($b['satuan']) ?></td>
                    <td class="text-right fw-bold <?= $b['qty_sisa'] == 0 ? 'text-muted' : '' ?>"><?= number_format($b['qty_sisa']) ?></td>
                    <td class="text-right"><?= formatRupiah($b['harga_modal']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars(truncate($b['keterangan'] ?? '-', 30)) ?></td>
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
                <a href="<?= BASE_URL ?>/index.php?page=stock-in&p=<?= $i ?>&product_id=<?= urlencode(get('product_id') ?? '') ?>&supplier_id=<?= urlencode(get('supplier_id') ?? '') ?>&date_from=<?= urlencode(get('date_from') ?? '') ?>&date_to=<?= urlencode(get('date_to') ?? '') ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
