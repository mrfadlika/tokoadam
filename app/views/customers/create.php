<!-- Create Contact -->
<div class="card" style="max-width:640px">
    <div class="card-header"><h3>Tambah Kontak Baru</h3></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=customers&action=store">
            <div class="form-group">
                <label class="form-label">Tipe Kontak <span class="required">*</span></label>
                <select name="tipe" class="form-control" required>
                    <option value="customer">Pelanggan</option>
                    <option value="supplier">Supplier</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nama <span class="required">*</span></label>
                <input type="text" name="nama_toko" class="form-control" placeholder="Nama pelanggan atau supplier" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama PIC / Pemilik</label>
                    <input type="text" name="nama_pic" class="form-control" placeholder="Nama contact person">
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="phone" class="form-control" placeholder="08xxx">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Catatan</label>
                <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
            </div>
            <div class="d-flex gap-2" style="margin-top:24px">
                <button type="submit" class="btn btn-primary">Simpan Kontak</button>
                <a href="<?= BASE_URL ?>/index.php?page=customers" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
