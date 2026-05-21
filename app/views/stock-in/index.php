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
        <table class="table-compact-mobile" data-sortable>
            <thead>
                <tr>
                    <th class="w-90" data-sort-key="tanggal">Tanggal</th>
                    <th class="mobile-hide-col w-90" data-sort-key="kode">Kode</th>
                    <th data-sort-key="nama">Nama Barang</th>
                    <th class="mobile-hide-col w-100" data-sort-key="supplier">Supplier</th>
                    <th class="mobile-hide-col w-90">No. Nota</th>
                    <th class="mobile-hide-col w-80">Arsip Nota</th>
                    <th class="text-right w-80" data-sort-key="qty_masuk">Qty Masuk</th>
                    <th class="text-right mobile-hide-col w-60" data-sort-key="sisa">Sisa</th>
                    <th class="text-right mobile-hide-col w-100" data-sort-key="harga_modal">Harga Modal</th>
                    <th class="mobile-hide-col">Keterangan</th>
                    <th class="mobile-hide-col w-80">Oleh</th>
                    <th class="text-center mobile-hide-col w-80">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr>
                        <td colspan="12">
                            <div class="empty-state">
                                <div class="empty-icon">IN</div>
                                <h4>Belum ada data barang masuk</h4>
                            </div>
                        </td>
                    </tr>
                <?php else: foreach ($batches as $b): ?>
                <tr>
                    <td class="text-nowrap" data-sort-value="<?= $b['tanggal_masuk'] ?>"><?= formatDate($b['tanggal_masuk']) ?></td>
                    <td class="mobile-hide-col"><span class="font-mono"><?= htmlspecialchars($b['kode_barang']) ?></span></td>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?page=reports&action=stock_detail&id=<?= $b['product_id'] ?>" class="table-link"><?= htmlspecialchars($b['nama_barang']) ?></a>
                        <div class="table-note">Lihat detail FIFO dan harga</div>
                        <div class="mobile-only-inline">
                            <?= htmlspecialchars($b['supplier_name'] ?? '-') ?> · <?= formatRupiah($b['harga_modal']) ?>
                        </div>
                    </td>
                    <td class="mobile-hide-col"><?= htmlspecialchars($b['supplier_name'] ?? '-') ?></td>
                    <td class="mobile-hide-col"><?= htmlspecialchars($b['nomor_nota'] ?? '-') ?></td>
                    <td class="mobile-hide-col">
                        <?php if (!empty($b['nota_file'])): ?>
                            <?php 
                            $notaUrl = BASE_URL . '/public/uploads/stock-notes/' . rawurlencode($b['nota_file']);
                            $notaExt = strtolower(pathinfo($b['nota_file'], PATHINFO_EXTENSION));
                            $isImageNota = in_array($notaExt, ['jpg', 'jpeg', 'png', 'webp'], true);
                            ?>
                            <?php if ($isImageNota): ?>
                                <a href="<?= htmlspecialchars($notaUrl) ?>" target="_blank" rel="noopener" title="Buka foto nota">
                                    <img src="<?= htmlspecialchars($notaUrl) ?>" alt="Foto nota" style="width:48px;height:48px;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
                                </a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($notaUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">Buka</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right fw-bold" data-sort-value="<?= $b['qty_masuk'] ?>">
                        <?= number_format($b['qty_masuk']) ?> <span style="font-weight:400;color:var(--muted)"><?= htmlspecialchars($b['satuan']) ?></span>
                        <div class="mobile-only-inline">Sisa: <span class="<?= (int)$b['qty_sisa'] > 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($b['qty_sisa']) ?></span></div>
                    </td>
                    <td class="text-right mobile-hide-col" data-sort-value="<?= $b['qty_sisa'] ?>">
                        <span class="fw-bold <?= (int)$b['qty_sisa'] > 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($b['qty_sisa']) ?></span>
                    </td>
                    <td class="text-right mobile-hide-col" data-sort-value="<?= $b['harga_modal'] ?>"><?= formatRupiah($b['harga_modal']) ?></td>
                    <td class="text-muted mobile-hide-col"><?= htmlspecialchars($b['keterangan'] ?: '-') ?></td>
                    <td class="text-muted mobile-hide-col"><?= htmlspecialchars($b['created_by_name'] ?: '-') ?></td>
                    <td class="text-center mobile-hide-col">
                        <?php $isUsed = (int)$b['qty_sisa'] < (int)$b['qty_masuk']; ?>
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>/index.php?page=stock-in&action=edit&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                            <?php if ($isUsed): ?>
                                <button type="button" class="btn btn-sm btn-outline" disabled title="Tidak bisa dihapus karena barang sudah terjual" style="opacity:0.4;cursor:not-allowed">Hapus</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-batch"
                                    data-batch-id="<?= $b['id'] ?>"
                                    data-batch-product="<?= htmlspecialchars($b['nama_barang']) ?>"
                                    data-batch-date="<?= formatDate($b['tanggal_masuk']) ?>"
                                    data-batch-qty="<?= number_format($b['qty_masuk']) ?> <?= htmlspecialchars($b['satuan']) ?>"
                                    title="Hapus barang masuk">
                                    Hapus
                                </button>
                            <?php endif; ?>
                        </div>
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
                <a href="<?= BASE_URL ?>/index.php?page=stock-in&p=<?= $i ?>&product_id=<?= urlencode(get('product_id') ?? '') ?>&supplier_id=<?= urlencode(get('supplier_id') ?? '') ?>&date_from=<?= urlencode(get('date_from') ?? '') ?>&date_to=<?= urlencode(get('date_to') ?? '') ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="confirm-modal-overlay" id="deleteModalOverlay">
    <div class="confirm-modal">
        <div class="confirm-modal-icon confirm-modal-icon-danger">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 class="confirm-modal-title">Hapus Barang Masuk?</h3>
        <p class="confirm-modal-desc">Anda yakin ingin menghapus data barang masuk ini? Stok barang akan disesuaikan secara otomatis.</p>
        <div class="confirm-modal-detail" id="deleteModalDetail">
            <div class="confirm-detail-row">
                <span class="confirm-detail-label">Barang</span>
                <span class="confirm-detail-value" id="deleteModalProduct">-</span>
            </div>
            <div class="confirm-detail-row">
                <span class="confirm-detail-label">Tanggal Masuk</span>
                <span class="confirm-detail-value" id="deleteModalDate">-</span>
            </div>
            <div class="confirm-detail-row">
                <span class="confirm-detail-label">Jumlah (Qty)</span>
                <span class="confirm-detail-value" id="deleteModalQty">-</span>
            </div>
        </div>
        <div class="confirm-modal-warning">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <span>Tindakan ini tidak dapat dibatalkan!</span>
        </div>
        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-outline" id="deleteModalCancel">Batal</button>
            <form method="POST" action="" id="deleteModalForm">
                <button type="submit" class="btn btn-danger">Ya, Hapus Data</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('deleteModalOverlay');
    const cancelBtn = document.getElementById('deleteModalCancel');
    const form = document.getElementById('deleteModalForm');

    // Open modal when delete button is clicked
    document.querySelectorAll('.btn-delete-batch').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('deleteModalProduct').textContent = this.dataset.batchProduct;
            document.getElementById('deleteModalDate').textContent = this.dataset.batchDate;
            document.getElementById('deleteModalQty').textContent = this.dataset.batchQty;
            form.action = '<?= BASE_URL ?>/index.php?page=stock-in&action=delete&id=' + this.dataset.batchId;
            overlay.classList.add('is-active');
            document.body.style.overflow = 'hidden';
        });
    });

    // Close modal
    function closeDeleteModal() {
        overlay.classList.remove('is-active');
        document.body.style.overflow = '';
    }

    cancelBtn.addEventListener('click', closeDeleteModal);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeDeleteModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-active')) closeDeleteModal();
    });
});
</script>
