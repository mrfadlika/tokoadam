<!-- Contacts List -->
<div class="toolbar">
    <form method="GET" class="filter-group">
        <input type="hidden" name="page" value="customers">
        <select name="type" class="form-control">
            <option value="">Semua Kontak</option>
            <option value="customer" <?= get('type') === 'customer' ? 'selected' : '' ?>>Pelanggan</option>
            <option value="supplier" <?= get('type') === 'supplier' ? 'selected' : '' ?>>Supplier</option>
        </select>
        <input type="text" name="search" class="form-control" placeholder="Cari pelanggan / supplier..." value="<?= htmlspecialchars($search ?? '') ?>">
        <button type="submit" class="btn btn-outline">Filter</button>
    </form>
    <a href="<?= BASE_URL ?>/index.php?page=customers&action=create" class="btn btn-primary">+ Tambah Kontak</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-compact-mobile">
            <thead>
                <tr>
                    <th>Tipe</th>
                    <th>Nama</th>
                    <th class="mobile-hide-col">PIC</th>
                    <th>Telepon</th>
                    <th class="mobile-hide-col">Alamat</th>
                    <th class="text-center mobile-hide-col">Aktivitas</th>
                    <th class="mobile-hide-col">Terakhir</th>
                    <th class="text-center mobile-hide-col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon">CT</div>
                                <h4>Belum ada data kontak</h4>
                                <p>Tambahkan pelanggan atau supplier pertama untuk mulai tracking.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: foreach ($customers as $c): ?>
                <tr>
                    <td>
                        <span class="badge <?= ($c['tipe'] ?? 'customer') === 'supplier' ? 'badge-warning' : 'badge-primary' ?>">
                            <?= ($c['tipe'] ?? 'customer') === 'supplier' ? 'Supplier' : 'Pelanggan' ?>
                        </span>
                    </td>
                    <td class="fw-bold">
                        <?= htmlspecialchars($c['nama_toko']) ?>
                        <?php if (!empty($c['nama_pic'])): ?>
                            <div class="mobile-only-inline">PIC: <?= htmlspecialchars($c['nama_pic']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="mobile-hide-col"><?= htmlspecialchars($c['nama_pic'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                    <td class="mobile-hide-col"><?= htmlspecialchars(truncate($c['alamat'] ?? '-', 40)) ?></td>
                    <td class="text-center mobile-hide-col"><span class="badge badge-info"><?= $c['total_aktivitas'] ?? 0 ?></span></td>
                    <td class="text-muted mobile-hide-col"><?= !empty($c['terakhir_aktivitas']) ? formatDate($c['terakhir_aktivitas']) : '-' ?></td>
                    <td class="text-center mobile-hide-col">
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>/index.php?page=customers&action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                            <a href="<?= BASE_URL ?>/index.php?page=customers&action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline" data-confirm="Yakin ingin menghapus data ini?">Hapus</a>
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
                <a href="<?= BASE_URL ?>/index.php?page=customers&search=<?= urlencode($search ?? '') ?>&type=<?= urlencode(get('type') ?? '') ?>&p=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
