<!-- Create Stock In -->
<?php
$productOptions = $productOptions ?? [];
$productLookupPayload = [];
foreach ($productOptions as $productOption) {
    $productLookupPayload[] = [
        'id' => (int)$productOption['id'],
        'kode_barang' => (string)$productOption['kode_barang'],
        'nama_barang' => (string)$productOption['nama_barang'],
        'satuan' => (string)($productOption['satuan'] ?? ''),
        'stok_total' => (int)($productOption['stok_total'] ?? 0),
        'label' => trim((string)$productOption['nama_barang']) . ' - ' . trim((string)$productOption['kode_barang']),
    ];
}
?>

<div class="card stock-in-create-card" style="max-width:700px">
    <div class="card-header"><h3>Input Barang Masuk</h3></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=stock-in&action=store" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Pilih Barang <span class="required">*</span></label>
                <div class="product-search-box">
                    <input type="text" id="productSearch" name="product_search" class="form-control" list="productSuggestions" placeholder="Ketik nama atau kode barang, lalu pilih dari daftar..." autocomplete="off" required>
                    <input type="hidden" name="product_id" id="productId">
                    <div id="productResult"></div>
                </div>
                <datalist id="productSuggestions">
                    <?php foreach ($productLookupPayload as $productOption): ?>
                        <option value="<?= htmlspecialchars($productOption['label']) ?>"><?= htmlspecialchars($productOption['nama_barang'] . ' (' . $productOption['kode_barang'] . ')') ?></option>
                    <?php endforeach; ?>
                </datalist>
                <div id="productInfo" class="form-hint">Anda bisa ketik kode atau nama barang. Jika muncul daftar, klik salah satu hasil yang sesuai.</div>
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
.stock-in-create-card,
.stock-in-create-card .card-body { overflow: visible; }
.product-search-box { position: relative; z-index: 20; }
#productResult { position: absolute; top: calc(100% + 8px); left: 0; right: 0; z-index: 40; }
#productDropdown { background:#fff; border:1.5px solid var(--border); border-radius:var(--radius-sm); box-shadow:var(--shadow-lg); max-height:240px; overflow-y:auto; }
#productDropdown .item { padding:10px 14px; cursor:pointer; font-size:13px; border-bottom:1px solid var(--border-light); display:flex; justify-content:space-between; }
#productDropdown .item:hover { background:var(--primary-50); }
#productDropdown .item .code { color:var(--text-muted); font-family:monospace; font-size:12px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productOptions = <?= json_encode($productLookupPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const form = document.querySelector('form[action*="page=stock-in&action=store"]');
    const searchInput = document.getElementById('productSearch');
    const productId = document.getElementById('productId');
    const resultDiv = document.getElementById('productResult');
    const infoDiv = document.getElementById('productInfo');
    const notaInput = document.getElementById('notaFileInput');
    const notaPreviewBox = document.getElementById('notaPreviewBox');
    const notaPreviewImage = document.getElementById('notaPreviewImage');
    const notaPreviewText = document.getElementById('notaPreviewText');
    let timer;
    let selectedLabel = '';

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getSearchTerms(product) {
        return [
            product.label,
            product.nama_barang,
            product.kode_barang,
            product.nama_barang + ' ' + product.kode_barang,
            product.kode_barang + ' ' + product.nama_barang
        ].map(value => String(value || '').toLowerCase());
    }

    function findExactMatch(query) {
        const normalized = String(query || '').trim().toLowerCase();
        if (!normalized) {
            return null;
        }

        const labelMatch = productOptions.find(product => String(product.label || '').toLowerCase() === normalized);
        if (labelMatch) {
            return labelMatch;
        }

        const codeMatches = productOptions.filter(product => String(product.kode_barang || '').toLowerCase() === normalized);
        if (codeMatches.length === 1) {
            return codeMatches[0];
        }

        const nameMatches = productOptions.filter(product => String(product.nama_barang || '').toLowerCase() === normalized);
        if (nameMatches.length === 1) {
            return nameMatches[0];
        }

        return null;
    }

    function applyProductSelection(product) {
        if (!product) {
            productId.value = '';
            selectedLabel = '';
            return;
        }

        productId.value = product.id;
        searchInput.value = product.label;
        selectedLabel = product.label;
        infoDiv.textContent = 'Stok saat ini: ' + product.stok_total + ' ' + product.satuan;
        resultDiv.innerHTML = '';
    }

    function renderDropdown(items, emptyText) {
        if (!items.length) {
            resultDiv.innerHTML = '<div id="productDropdown"><div class="item" style="color:var(--text-muted)">' + escapeHtml(emptyText) + '</div></div>';
            return;
        }

        let html = '<div id="productDropdown">';
        items.forEach(product => {
            html += '<div class="item" data-id="' + escapeHtml(product.id) + '" data-name="' + escapeHtml(product.nama_barang) + '" data-label="' + escapeHtml(product.label) + '" data-stock="' + escapeHtml(product.stok_total) + '" data-unit="' + escapeHtml(product.satuan) + '">';
            html += '<span>' + escapeHtml(product.nama_barang) + ' <span class="code">' + escapeHtml(product.kode_barang) + '</span></span>';
            html += '<span class="badge badge-' + (product.stok_total > 0 ? 'success' : 'danger') + '">Stok: ' + escapeHtml(product.stok_total) + '</span>';
            html += '</div>';
        });
        html += '</div>';
        resultDiv.innerHTML = html;

        resultDiv.querySelectorAll('.item[data-id]').forEach(item => {
            item.addEventListener('click', function() {
                const product = productOptions.find(option => String(option.id) === this.dataset.id) || {
                    id: this.dataset.id,
                    nama_barang: this.dataset.name,
                    label: this.dataset.label,
                    stok_total: this.dataset.stock,
                    satuan: this.dataset.unit
                };
                applyProductSelection(product);
            });
        });
    }

    function updateSearchState(value) {
        const query = String(value || '').trim();
        const exactMatch = findExactMatch(query);
        if (exactMatch) {
            applyProductSelection(exactMatch);
            return;
        }

        if (query !== selectedLabel) {
            productId.value = '';
            selectedLabel = '';
            infoDiv.textContent = query.length === 0
                ? 'Anda bisa ketik kode atau nama barang. Jika muncul daftar, klik salah satu hasil yang sesuai.'
                : 'Pilih barang dari daftar hasil pencarian agar barang yang disimpan sesuai.';
        }

        if (query.length < 2) {
            resultDiv.innerHTML = '';
            return;
        }

        const normalized = query.toLowerCase();
        const matches = productOptions
            .filter(product => getSearchTerms(product).some(term => term.includes(normalized)))
            .slice(0, 10);
        renderDropdown(matches, 'Tidak ditemukan');
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(timer);
        this.setCustomValidity('');
        timer = setTimeout(() => updateSearchState(this.value), 120);
    });

    searchInput.addEventListener('change', function() {
        updateSearchState(this.value);
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            updateSearchState(this.value);
        }
    });

    if (form) {
        form.addEventListener('submit', function(e) {
            searchInput.setCustomValidity('');
            const exactMatch = findExactMatch(searchInput.value);
            if (exactMatch && !productId.value) {
                applyProductSelection(exactMatch);
            }
            if (!searchInput.value.trim()) {
                searchInput.setCustomValidity('Barang wajib diisi.');
                searchInput.reportValidity();
                e.preventDefault();
            } else if (!productId.value) {
                infoDiv.textContent = 'Barang belum dipilih dari daftar. Pilih hasil yang sesuai dulu.';
            }
        });
    }

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
