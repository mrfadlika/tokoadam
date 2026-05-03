<!-- Create Stock In -->
<div class="card" style="max-width:700px">
    <div class="card-header"><h3>Input Barang Masuk</h3></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=stock-in&action=store" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Pilih Barang <span class="required">*</span></label>
                <input type="text" id="productSearch" class="form-control" placeholder="Ketik nama atau kode barang..." autocomplete="off">
                <input type="hidden" name="product_id" id="productId" required>
                <div id="productResult" style="position:relative"></div>
                <div id="productInfo" class="form-hint"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">Pilih supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= $supplier['id'] ?>"><?= htmlspecialchars($supplier['nama_toko']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor Nota</label>
                    <input type="text" name="nomor_nota" class="form-control" placeholder="Nomor nota pembelian">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tanggal Masuk <span class="required">*</span></label>
                    <input type="date" name="tanggal_masuk" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Qty Masuk <span class="required">*</span></label>
                    <input type="number" name="qty_masuk" class="form-control" min="1" placeholder="Jumlah" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Harga Modal per Unit <span class="required">*</span></label>
                <input type="number" name="harga_modal" class="form-control" min="1" placeholder="Harga beli per satuan" required>
            </div>
            <div class="form-group">
                <label class="form-label">Foto / Arsip Nota Supplier</label>
                <input type="file" name="nota_file" id="notaFileInput" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                <div class="form-hint">Opsional. Upload foto nota dari supplier untuk arsip. Format: PDF, JPG, JPEG, PNG, WEBP. Maksimal 4MB.</div>
                <div id="notaPreviewBox" style="display:none;margin-top:12px">
                    <img id="notaPreviewImage" src="" alt="Preview nota" style="display:none;max-width:220px;max-height:220px;border:1px solid var(--border);border-radius:12px;padding:6px;background:#fff">
                    <div id="notaPreviewText" class="form-hint" style="margin-top:8px"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan pembelian (opsional)"></textarea>
            </div>
            <div class="d-flex gap-2" style="margin-top:24px">
                <button type="submit" class="btn btn-primary">Simpan Barang Masuk</button>
                <a href="<?= BASE_URL ?>/index.php?page=stock-in" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<style>
#productDropdown { position:absolute; top:0; left:0; right:0; background:#fff; border:1.5px solid var(--border); border-radius:var(--radius-sm); box-shadow:var(--shadow-lg); z-index:10; max-height:200px; overflow-y:auto; }
#productDropdown .item { padding:10px 14px; cursor:pointer; font-size:13px; border-bottom:1px solid var(--border-light); display:flex; justify-content:space-between; }
#productDropdown .item:hover { background:var(--primary-50); }
#productDropdown .item .code { color:var(--text-muted); font-family:monospace; font-size:12px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('productSearch');
    const productId = document.getElementById('productId');
    const resultDiv = document.getElementById('productResult');
    const infoDiv = document.getElementById('productInfo');
    const notaInput = document.getElementById('notaFileInput');
    const notaPreviewBox = document.getElementById('notaPreviewBox');
    const notaPreviewImage = document.getElementById('notaPreviewImage');
    const notaPreviewText = document.getElementById('notaPreviewText');
    let timer;

    searchInput.addEventListener('input', function() {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { resultDiv.innerHTML = ''; return; }
        timer = setTimeout(() => {
            fetch('<?= BASE_URL ?>/index.php?page=products&action=api_search&q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!data.length) {
                        resultDiv.innerHTML = '<div id="productDropdown"><div class="item" style="color:var(--text-muted)">Tidak ditemukan</div></div>';
                        return;
                    }
                    let html = '<div id="productDropdown">';
                    data.forEach(p => {
                        html += '<div class="item" data-id="' + p.id + '" data-name="' + p.nama_barang + '" data-stock="' + p.stok_total + '" data-unit="' + p.satuan + '">';
                        html += '<span>' + p.nama_barang + ' <span class="code">' + p.kode_barang + '</span></span>';
                        html += '<span class="badge badge-' + (p.stok_total > 0 ? 'success' : 'danger') + '">Stok: ' + p.stok_total + '</span>';
                        html += '</div>';
                    });
                    html += '</div>';
                    resultDiv.innerHTML = html;
                    resultDiv.querySelectorAll('.item[data-id]').forEach(item => {
                        item.addEventListener('click', function() {
                            productId.value = this.dataset.id;
                            searchInput.value = this.dataset.name;
                            infoDiv.textContent = 'Stok saat ini: ' + this.dataset.stock + ' ' + this.dataset.unit;
                            resultDiv.innerHTML = '';
                        });
                    });
                });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!resultDiv.contains(e.target) && e.target !== searchInput) resultDiv.innerHTML = '';
    });

    if (notaInput) {
        notaInput.addEventListener('change', function() {
            const file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) {
                notaPreviewBox.style.display = 'none';
                notaPreviewImage.style.display = 'none';
                notaPreviewImage.src = '';
                notaPreviewText.textContent = '';
                return;
            }

            notaPreviewBox.style.display = 'block';
            if (file.type.startsWith('image/')) {
                notaPreviewImage.src = URL.createObjectURL(file);
                notaPreviewImage.style.display = 'block';
                notaPreviewText.textContent = 'Preview foto nota supplier yang akan diarsipkan.';
            } else {
                notaPreviewImage.style.display = 'none';
                notaPreviewImage.src = '';
                notaPreviewText.textContent = 'File arsip terpilih: ' + file.name;
            }
        });
    }
});
</script>
