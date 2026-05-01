<!-- Edit Customer -->
<div class="card" style="max-width:600px">
    <div class="card-header"><h3>✏️ Edit Toko</h3></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=customers&action=update&id=<?= $customer['id'] ?>">
            <div class="form-group">
                <label class="form-label">Nama Toko <span class="required">*</span></label>
                <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($customer['nama_toko']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama PIC / Pemilik</label>
                    <input type="text" name="nama_pic" class="form-control" value="<?= htmlspecialchars($customer['nama_pic'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($customer['alamat'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Catatan</label>
                <textarea name="catatan" class="form-control" rows="2"><?= htmlspecialchars($customer['catatan'] ?? '') ?></textarea>
            </div>
            <div class="d-flex gap-2" style="margin-top:24px">
                <button type="submit" class="btn btn-primary">💾 Update Toko</button>
                <a href="<?= BASE_URL ?>/index.php?page=customers" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
