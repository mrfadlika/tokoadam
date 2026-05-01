<!-- Customers List -->
<div class="toolbar">
    <form method="GET" class="search-box">
        <input type="hidden" name="page" value="customers">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Cari toko..." value="<?= htmlspecialchars($search ?? '') ?>">
    </form>
    <a href="<?= BASE_URL ?>/index.php?page=customers&action=create" class="btn btn-primary">+ Tambah Toko</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>Nama Toko</th><th>PIC</th><th>Telepon</th><th>Alamat</th><th class="text-center">Transaksi</th><th>Terakhir</th><th class="text-center">Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🏬</div><h4>Belum ada toko</h4><p>Tambahkan toko pelanggan pertama</p></div></td></tr>
                <?php else: foreach ($customers as $c): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($c['nama_toko']) ?></td>
                    <td><?= htmlspecialchars($c['nama_pic'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                    <td><?= truncate(htmlspecialchars($c['alamat'] ?? '-'), 30) ?></td>
                    <td class="text-center"><span class="badge badge-primary"><?= $c['total_transaksi'] ?? 0 ?></span></td>
                    <td class="text-muted"><?= $c['terakhir_transaksi'] ? formatDate($c['terakhir_transaksi']) : '-' ?></td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>/index.php?page=customers&action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
                            <a href="<?= BASE_URL ?>/index.php?page=customers&action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline" data-confirm="Yakin ingin menghapus toko ini?">🗑️</a>
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
                <a href="<?= BASE_URL ?>/index.php?page=customers&search=<?= urlencode($search ?? '') ?>&p=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
