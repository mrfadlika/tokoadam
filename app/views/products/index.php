<!-- Products List -->
<div class="toolbar">
    <form method="GET" class="search-box">
        <input type="hidden" name="page" value="products">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Cari barang..." value="<?= htmlspecialchars($search ?? '') ?>">
    </form>
    <a href="<?= BASE_URL ?>/index.php?page=products&action=create" class="btn btn-primary">+ Tambah Barang</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th class="text-right">Harga Jual</th>
                    <th class="text-center">Stok</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📦</div><h4>Belum ada barang</h4><p>Mulai tambahkan barang pertama</p></div></td></tr>
                <?php else: foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php if ($p['foto']): ?>
                            <img src="<?= BASE_URL ?>/public/uploads/products/<?= $p['foto'] ?>" class="product-thumb" alt="">
                        <?php else: ?>
                            <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;font-size:18px;background:#F3F4F6">📦</div>
                        <?php endif; ?>
                    </td>
                    <td><span class="font-mono"><?= $p['kode_barang'] ?></span></td>
                    <td class="fw-bold"><?= htmlspecialchars($p['nama_barang']) ?></td>
                    <td><?= $p['kategori'] ? '<span class="badge badge-info">' . htmlspecialchars($p['kategori']) . '</span>' : '-' ?></td>
                    <td><?= $p['satuan'] ?></td>
                    <td class="text-right"><?= formatRupiah($p['harga_jual']) ?></td>
                    <td class="text-center">
                        <?php $stok = (int)$p['stok_total']; ?>
                        <span class="fw-bold <?= $stok <= $p['stok_minimum'] ? ($stok == 0 ? 'text-danger' : 'text-warning') : 'text-success' ?>">
                            <?= $stok ?>
                        </span>
                    </td>
                    <td>
                        <?= $p['is_active'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>' ?>
                    </td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>/index.php?page=products&action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
                            <a href="<?= BASE_URL ?>/index.php?page=products&action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" data-confirm="Yakin ingin menonaktifkan barang ini?">🗑️</a>
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
                <a href="<?= BASE_URL ?>/index.php?page=products&search=<?= urlencode($search) ?>&p=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
