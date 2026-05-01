<!-- Create Product -->
<div class="card" style="max-width:700px">
    <div class="card-header"><h3>📦 Tambah Barang Baru</h3></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=products&action=store" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kode Barang <span class="required">*</span></label>
                    <input type="text" name="kode_barang" class="form-control" placeholder="Contoh: BRG001" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Satuan <span class="required">*</span></label>
                    <input type="text" name="satuan" class="form-control" value="pcs" list="unit-list" required>
                    <datalist id="unit-list">
                        <option value="pcs"><option value="kg"><option value="liter"><option value="dus"><option value="pak"><option value="lusin"><option value="botol"><option value="sachet">
                        <?php if (!empty($units)): foreach ($units as $u): ?><option value="<?= htmlspecialchars($u) ?>"><?php endforeach; endif; ?>
                    </datalist>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Barang <span class="required">*</span></label>
                <input type="text" name="nama_barang" class="form-control" placeholder="Nama lengkap barang" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" class="form-control" placeholder="Contoh: Makanan" list="cat-list">
                    <datalist id="cat-list">
                        <?php if (!empty($categories)): foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; endif; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual Default</label>
                    <input type="number" name="harga_jual" class="form-control input-rupiah" placeholder="0" min="0">
                    <div class="form-hint">Bisa diubah saat transaksi</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Stok Minimum</label>
                    <input type="number" name="stok_minimum" class="form-control" value="10" min="0">
                    <div class="form-hint">Alert jika stok di bawah nilai ini</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Foto Barang</label>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <div class="form-hint">Maks 2MB (JPG, PNG, WebP)</div>
                </div>
            </div>
            <div class="d-flex gap-2" style="margin-top:24px">
                <button type="submit" class="btn btn-primary">💾 Simpan Barang</button>
                <a href="<?= BASE_URL ?>/index.php?page=products" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
