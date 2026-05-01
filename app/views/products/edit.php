<!-- Edit Product -->
<div class="card" style="max-width:700px">
    <div class="card-header"><h3>✏️ Edit Barang</h3></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=products&action=update&id=<?= $product['id'] ?>" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kode Barang <span class="required">*</span></label>
                    <input type="text" name="kode_barang" class="form-control" value="<?= htmlspecialchars($product['kode_barang']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Satuan <span class="required">*</span></label>
                    <input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($product['satuan']) ?>" list="unit-list" required>
                    <datalist id="unit-list">
                        <option value="pcs"><option value="kg"><option value="liter"><option value="dus"><option value="pak"><option value="lusin">
                        <?php if (!empty($units)): foreach ($units as $u): ?><option value="<?= htmlspecialchars($u) ?>"><?php endforeach; endif; ?>
                    </datalist>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Barang <span class="required">*</span></label>
                <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($product['nama_barang']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" class="form-control" value="<?= htmlspecialchars($product['kategori'] ?? '') ?>" list="cat-list">
                    <datalist id="cat-list"><?php if (!empty($categories)): foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; endif; ?></datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual Default</label>
                    <input type="number" name="harga_jual" class="form-control" value="<?= $product['harga_jual'] ?>" min="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Stok Minimum</label>
                    <input type="number" name="stok_minimum" class="form-control" value="<?= $product['stok_minimum'] ?>" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Foto Barang</label>
                    <?php if ($product['foto']): ?>
                        <div class="mb-1"><img src="<?= BASE_URL ?>/public/uploads/products/<?= $product['foto'] ?>" class="product-thumb-lg" alt=""></div>
                    <?php endif; ?>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <div class="form-hint">Kosongkan jika tidak ingin mengubah foto</div>
                </div>
            </div>
            <div class="d-flex gap-2" style="margin-top:24px">
                <button type="submit" class="btn btn-primary">💾 Update Barang</button>
                <a href="<?= BASE_URL ?>/index.php?page=products" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($batches)): ?>
<div class="card mt-3" style="max-width:700px">
    <div class="card-header"><h3>📊 Batch Stok (FIFO)</h3></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Tgl Masuk</th><th class="text-right">Qty Masuk</th><th class="text-right">Sisa</th><th class="text-right">Harga Modal</th></tr></thead>
            <tbody>
                <?php foreach ($batches as $b): ?>
                <tr>
                    <td><?= formatDate($b['tanggal_masuk']) ?></td>
                    <td class="text-right"><?= $b['qty_masuk'] ?></td>
                    <td class="text-right fw-bold"><?= $b['qty_sisa'] ?></td>
                    <td class="text-right"><?= formatRupiah($b['harga_modal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
