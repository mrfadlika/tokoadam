<!-- Create Sale / Kasir Grosir -->
<div class="card">
    <div class="card-header"><h3>🛒 Transaksi Penjualan Baru</h3></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=sales&action=store" id="saleForm">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pilih Toko <span class="required">*</span></label>
                    <input type="text" id="customerSearch" class="form-control" placeholder="Ketik nama toko..." autocomplete="off">
                    <input type="hidden" name="customer_id" id="customerId" required>
                    <div id="customerResult" style="position:relative"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal <span class="required">*</span></label>
                    <input type="date" name="tanggal_transaksi" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            
            <!-- Items Table -->
            <div class="form-group">
                <label class="form-label">Daftar Item</label>
                <div class="table-responsive">
                    <table id="itemsTable">
                        <thead>
                            <tr><th style="width:40%">Barang</th><th style="width:15%">Stok</th><th style="width:12%">Qty</th><th style="width:18%">Harga Jual</th><th style="width:15%" class="text-right">Subtotal</th><th></th></tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right fw-bold" style="font-size:16px">Grand Total:</td>
                                <td class="text-right fw-bold" style="font-size:18px;color:var(--primary)" id="grandTotal">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" class="btn btn-outline btn-sm mt-2" id="addItemBtn">+ Tambah Item</button>
            </div>
            
            <div class="form-group">
                <label class="form-label">Catatan</label>
                <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan transaksi (opsional)"></textarea>
            </div>
            
            <div class="d-flex gap-2" style="margin-top:24px">
                <button type="submit" class="btn btn-primary btn-lg">💾 Simpan & Cetak Nota</button>
                <a href="<?= BASE_URL ?>/index.php?page=sales" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<style>
.autocomplete-dropdown { position:absolute; top:0; left:0; right:0; background:#fff; border:1.5px solid var(--border); border-radius:var(--radius-sm); box-shadow:var(--shadow-lg); z-index:20; max-height:200px; overflow-y:auto; }
.autocomplete-dropdown .dd-item { padding:10px 14px; cursor:pointer; font-size:13px; border-bottom:1px solid var(--border-light); }
.autocomplete-dropdown .dd-item:hover { background:var(--primary-50); }
.autocomplete-dropdown .dd-item small { color:var(--text-muted); }
#itemsTable td { vertical-align: middle; }
#itemsTable input { min-width: 80px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE = '<?= BASE_URL ?>';
    let itemIndex = 0;
    
    // Customer autocomplete
    const custSearch = document.getElementById('customerSearch');
    const custId = document.getElementById('customerId');
    const custResult = document.getElementById('customerResult');
    let custTimer;
    
    custSearch.addEventListener('input', function() {
        clearTimeout(custTimer);
        const q = this.value.trim();
        if (q.length < 2) { custResult.innerHTML = ''; return; }
        custTimer = setTimeout(() => {
            fetch(BASE + '/index.php?page=customers&action=api_search&q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { custResult.innerHTML = '<div class="autocomplete-dropdown"><div class="dd-item" style="color:var(--text-muted)">Tidak ditemukan</div></div>'; return; }
                    let html = '<div class="autocomplete-dropdown">';
                    data.forEach(c => { html += '<div class="dd-item" data-id="'+c.id+'" data-name="'+c.nama_toko+'">'+c.nama_toko+' <small>'+( c.nama_pic||'')+'</small></div>'; });
                    html += '</div>';
                    custResult.innerHTML = html;
                    custResult.querySelectorAll('.dd-item[data-id]').forEach(item => {
                        item.addEventListener('click', function() {
                            custId.value = this.dataset.id;
                            custSearch.value = this.dataset.name;
                            custResult.innerHTML = '';
                        });
                    });
                });
        }, 300);
    });
    
    // Add item row
    function addItemRow() {
        const idx = itemIndex++;
        const tr = document.createElement('tr');
        tr.id = 'row-' + idx;
        tr.innerHTML = `
            <td>
                <input type="text" class="form-control product-search" data-idx="${idx}" placeholder="Cari barang..." autocomplete="off">
                <input type="hidden" name="product_id[]" class="product-id" data-idx="${idx}">
                <div class="product-dropdown" data-idx="${idx}" style="position:relative"></div>
            </td>
            <td><span class="stock-info text-muted" data-idx="${idx}">-</span></td>
            <td><input type="number" name="qty[]" class="form-control item-qty" data-idx="${idx}" min="1" placeholder="0"></td>
            <td><input type="number" name="harga_jual[]" class="form-control item-price" data-idx="${idx}" min="0" placeholder="0"></td>
            <td class="text-right fw-bold item-subtotal" data-idx="${idx}">Rp 0</td>
            <td><button type="button" class="btn btn-sm btn-icon btn-outline remove-row" data-idx="${idx}">✕</button></td>
        `;
        document.getElementById('itemsBody').appendChild(tr);
        
        // Product search for this row
        const searchInput = tr.querySelector('.product-search');
        const dropdown = tr.querySelector('.product-dropdown');
        let pTimer;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(pTimer);
            const q = this.value.trim();
            if (q.length < 2) { dropdown.innerHTML = ''; return; }
            pTimer = setTimeout(() => {
                fetch(BASE + '/index.php?page=products&action=api_search&q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        if (!data.length) { dropdown.innerHTML = '<div class="autocomplete-dropdown"><div class="dd-item" style="color:var(--text-muted)">Tidak ditemukan</div></div>'; return; }
                        let html = '<div class="autocomplete-dropdown">';
                        data.forEach(p => {
                            html += '<div class="dd-item" data-id="'+p.id+'" data-name="'+p.nama_barang+'" data-stock="'+p.stok_total+'" data-price="'+p.harga_jual+'">'+p.nama_barang+' <small>['+p.kode_barang+'] Stok:'+p.stok_total+'</small></div>';
                        });
                        html += '</div>';
                        dropdown.innerHTML = html;
                        dropdown.querySelectorAll('.dd-item[data-id]').forEach(item => {
                            item.addEventListener('click', function() {
                                tr.querySelector('.product-id').value = this.dataset.id;
                                searchInput.value = this.dataset.name;
                                tr.querySelector('.stock-info').textContent = this.dataset.stock;
                                const priceInput = tr.querySelector('.item-price');
                                if (!priceInput.value || priceInput.value == '0') priceInput.value = this.dataset.price;
                                dropdown.innerHTML = '';
                                calcRow(idx);
                            });
                        });
                    });
            }, 300);
        });
        
        // Calc on qty/price change
        tr.querySelector('.item-qty').addEventListener('input', () => calcRow(idx));
        tr.querySelector('.item-price').addEventListener('input', () => calcRow(idx));
        
        // Remove row
        tr.querySelector('.remove-row').addEventListener('click', () => { tr.remove(); calcGrandTotal(); });
    }
    
    function calcRow(idx) {
        const row = document.getElementById('row-' + idx);
        if (!row) return;
        const qty = parseInt(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const subtotal = qty * price;
        row.querySelector('.item-subtotal').textContent = formatRupiah(subtotal);
        calcGrandTotal();
    }
    
    function calcGrandTotal() {
        let total = 0;
        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const qty = parseInt(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            total += qty * price;
        });
        document.getElementById('grandTotal').textContent = formatRupiah(total);
    }
    
    document.getElementById('addItemBtn').addEventListener('click', addItemRow);
    
    // Start with one row
    addItemRow();
    
    // Close dropdowns on click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.product-dropdown') && !e.target.classList.contains('product-search')) {
            document.querySelectorAll('.product-dropdown').forEach(d => d.innerHTML = '');
        }
        if (!custResult.contains(e.target) && e.target !== custSearch) custResult.innerHTML = '';
    });
    
    // Form validation
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        if (!custId.value) { e.preventDefault(); alert('Pilih toko terlebih dahulu.'); return; }
        const rows = document.querySelectorAll('#itemsBody tr');
        if (!rows.length) { e.preventDefault(); alert('Tambahkan minimal 1 item.'); return; }
        let hasItem = false;
        rows.forEach(row => {
            if (row.querySelector('.product-id').value && row.querySelector('.item-qty').value > 0) hasItem = true;
        });
        if (!hasItem) { e.preventDefault(); alert('Isi minimal 1 item dengan qty > 0.'); }
    });
});
</script>
