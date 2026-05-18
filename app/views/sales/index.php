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
        <table class="table-compact-mobile" data-sortable>
            <thead>
                <tr>
                    <th data-sort-key="nota">No. Nota</th>
                    <th class="mobile-hide-col" data-sort-key="tanggal">Tanggal</th>
                    <th class="mobile-hide-col" data-sort-key="pelanggan">Pelanggan</th>
                    <th class="text-right" data-sort-key="total">Total</th>
                    <th class="text-center mobile-hide-col text-nowrap" data-sort-key="status">Status Pembayaran</th>
                    <th class="mobile-hide-col">User</th>
                    <th class="text-center mobile-hide-col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">TR</div><h4>Belum ada transaksi</h4></div></td></tr>
                <?php else: foreach ($sales as $s): ?>
                <tr>
                    <td>
                        <span class="font-mono"><?= htmlspecialchars($s['nomor_transaksi']) ?></span>
                        <div class="mobile-only-inline"><?= formatDate($s['tanggal_transaksi']) ?> · <?= htmlspecialchars($s['nama_toko']) ?></div>
                    </td>
                    <td class="text-nowrap mobile-hide-col" data-sort-value="<?= $s['tanggal_transaksi'] ?>"><?= formatDate($s['tanggal_transaksi']) ?></td>
                    <td class="fw-bold mobile-hide-col"><?= htmlspecialchars($s['nama_toko']) ?></td>
                    <td class="text-right fw-bold" data-sort-value="<?= $s['total'] ?>"><?= formatRupiah($s['total']) ?></td>
                    <td class="text-center mobile-hide-col col-fit" data-sort-value="<?= $s['status_bayar'] ?? 'belum_lunas' ?>">
                        <?php $isLunas = ($s['status_bayar'] ?? 'belum_lunas') === 'lunas'; ?>
                        <span class="badge <?= $isLunas ? 'badge-success' : 'badge-warning' ?>">
                            <?= $isLunas ? '✓ Lunas' : '○ Belum Lunas' ?>
                        </span>
                    </td>
                    <td class="text-muted mobile-hide-col"><?= htmlspecialchars($s['created_by_name'] ?? '-') ?></td>
                    <td class="text-center mobile-hide-col">
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>/index.php?page=sales&action=invoice&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline">Nota</a>

                            <?php /* Toggle payment status */ ?>
                            <?php if ($isLunas): ?>
                                <form method="POST" action="<?= BASE_URL ?>/index.php?page=sales&action=update_status" style="display:inline">
                                    <input type="hidden" name="sale_id" value="<?= $s['id'] ?>">
                                    <input type="hidden" name="status_bayar" value="belum_lunas">
                                    <button type="submit" class="btn btn-sm btn-warning" title="Tandai belum lunas">Batal Lunas</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= BASE_URL ?>/index.php?page=sales&action=update_status" style="display:inline">
                                    <input type="hidden" name="sale_id" value="<?= $s['id'] ?>">
                                    <input type="hidden" name="status_bayar" value="lunas">
                                    <button type="submit" class="btn btn-sm btn-success" title="Tandai lunas">Lunas</button>
                                </form>
                            <?php endif; ?>

                            <?php /* Delete - admin only with confirmation modal */ ?>
                            <?php if (isAdmin()): ?>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-sale"
                                    data-sale-id="<?= $s['id'] ?>"
                                    data-sale-nota="<?= htmlspecialchars($s['nomor_transaksi']) ?>"
                                    data-sale-customer="<?= htmlspecialchars($s['nama_toko']) ?>"
                                    data-sale-total="<?= formatRupiah($s['total']) ?>"
                                    title="Hapus transaksi">
                                    Hapus
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline" disabled title="Hanya admin yang dapat menghapus" style="opacity:0.4;cursor:not-allowed">Hapus</button>
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
                <a href="<?= BASE_URL ?>/index.php?page=sales&p=<?= $i ?>&search=<?= urlencode(get('search') ?? '') ?>&customer_id=<?= urlencode(get('customer_id') ?? '') ?>&date_from=<?= urlencode(get('date_from') ?? '') ?>&date_to=<?= urlencode(get('date_to') ?? '') ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal (Admin Only) -->
<?php if (isAdmin()): ?>
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
        <h3 class="confirm-modal-title">Hapus Transaksi?</h3>
        <p class="confirm-modal-desc">Anda yakin ingin menghapus transaksi ini? Stok barang akan dikembalikan secara otomatis.</p>
        <div class="confirm-modal-detail" id="deleteModalDetail">
            <div class="confirm-detail-row">
                <span class="confirm-detail-label">No. Nota</span>
                <span class="confirm-detail-value" id="deleteModalNota">-</span>
            </div>
            <div class="confirm-detail-row">
                <span class="confirm-detail-label">Pelanggan</span>
                <span class="confirm-detail-value" id="deleteModalCustomer">-</span>
            </div>
            <div class="confirm-detail-row">
                <span class="confirm-detail-label">Total</span>
                <span class="confirm-detail-value" id="deleteModalTotal">-</span>
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
            <form method="POST" action="<?= BASE_URL ?>/index.php?page=sales&action=delete" id="deleteModalForm">
                <input type="hidden" name="sale_id" id="deleteModalSaleId" value="">
                <button type="submit" class="btn btn-danger">Ya, Hapus Transaksi</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('deleteModalOverlay');
    const cancelBtn = document.getElementById('deleteModalCancel');

    // Open modal when delete button is clicked
    document.querySelectorAll('.btn-delete-sale').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('deleteModalSaleId').value = this.dataset.saleId;
            document.getElementById('deleteModalNota').textContent = this.dataset.saleNota;
            document.getElementById('deleteModalCustomer').textContent = this.dataset.saleCustomer;
            document.getElementById('deleteModalTotal').textContent = this.dataset.saleTotal;
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
<?php endif; ?>
