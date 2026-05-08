<?php
$stockLink = BASE_URL . '/index.php?page=reports&action=stock';
if (count($items) === 1) {
    $stockLink = BASE_URL . '/index.php?page=reports&action=stock_detail&id=' . $items[0]['product_id'];
}

$titleSuffix = trim((string)($invoiceView['company_name'] ?? '')) !== '' ? (string)$invoiceView['company_name'] : APP_NAME;
$invoiceField = static function (
    string $field,
    string $value,
    string $placeholder = '',
    string $class = '',
    string $emptyDisplay = '-',
    string $tag = 'span'
): string {
    $attrs = 'class="invoice-editable' . ($class !== '' ? ' ' . $class : '') . '" data-field="' . htmlspecialchars($field) . '" data-placeholder="' . htmlspecialchars($placeholder) . '" data-empty-display="' . htmlspecialchars($emptyDisplay) . '"';
    return '<' . $tag . ' ' . $attrs . '>' . htmlspecialchars($value) . '</' . $tag . '>';
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota <?= htmlspecialchars($invoiceView['nomor_transaksi']) ?> - <?= htmlspecialchars($titleSuffix) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4; margin: 10mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #e8e1d8;
            color: #111827;
            padding: 84px 20px 24px;
        }
        .invoice-actions {
            position: fixed;
            inset: 0 0 auto 0;
            z-index: 20;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 14px 24px;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid rgba(17, 24, 39, 0.08);
        }
        .action-grow {
            flex: 1 1 auto;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid transparent;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: wait;
        }
        .btn-primary {
            color: #fff;
            background: #8b1a1a;
            border-color: #8b1a1a;
        }
        .btn-secondary {
            color: #1f2937;
            background: #fff;
            border-color: rgba(17, 24, 39, 0.14);
        }
        .btn-success {
            color: #fff;
            background: #146c43;
            border-color: #146c43;
        }
        .btn-warning {
            color: #1f2937;
            background: #f7e7ba;
            border-color: #e8d093;
        }
        .invoice-status {
            min-height: 18px;
            font-size: 12px;
            color: #6b7280;
        }
        .invoice-status.error {
            color: #b91c1c;
        }
        .invoice-status.success {
            color: #166534;
        }
        .invoice-page {
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
            padding: 30px 34px 42px;
            background: #fff;
            box-shadow: 0 18px 48px rgba(0, 0, 0, 0.12);
        }
        .company-header {
            text-align: center;
            padding-bottom: 16px;
            border-bottom: 2px solid #111827;
        }
        .company-header .top-line {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .company-header .name {
            margin-top: 4px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #8b1a1a;
        }
        .company-contact {
            margin-top: 6px;
            font-size: 12px;
            line-height: 1.5;
            text-align: center;
        }
        .company-contact .invoice-editable {
            min-width: 0;
        }
        .company-contact .address,
        .company-contact .phone {
            display: block;
        }
        .company-contact .phone {
            margin-top: 1px;
        }
        .invoice-title {
            margin: 24px 0 18px;
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .invoice-meta {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .invoice-meta td {
            padding: 3px 0;
            font-size: 14px;
            vertical-align: top;
        }
        .invoice-meta .meta-label {
            width: 160px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .invoice-meta .meta-sep {
            width: 18px;
            text-align: center;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #111827;
            padding: 8px 10px;
            font-size: 13px;
        }
        .invoice-table th {
            text-align: center;
            font-weight: 800;
            text-transform: uppercase;
        }
        .invoice-table td.text-center { text-align: center; }
        .invoice-table td.text-right { text-align: right; }
        .invoice-table tfoot td {
            font-weight: 800;
        }
        .invoice-table .total-label {
            text-align: center;
            text-transform: uppercase;
        }
        .signature-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 48px;
        }
        .signature-box {
            min-width: 240px;
            text-align: center;
            font-size: 13px;
        }
        .signature-space {
            height: 78px;
        }
        .signature-name {
            font-weight: 800;
            text-decoration: underline;
        }
        .signature-role {
            margin-top: 4px;
            font-size: 12px;
        }
        .invoice-editable {
            display: inline-block;
            min-width: 24px;
            white-space: pre-wrap;
            word-break: break-word;
            border-radius: 6px;
            transition: background 120ms ease, outline-color 120ms ease;
        }
        .invoice-editable.is-block {
            display: block;
            width: 100%;
            min-width: 100%;
        }
        .invoice-editable.is-empty::before {
            content: attr(data-empty-display);
            color: #9ca3af;
        }
        .invoice-page.edit-mode .invoice-editable {
            padding: 2px 4px;
            outline: 1px dashed rgba(139, 26, 26, 0.38);
            background: rgba(139, 26, 26, 0.05);
            cursor: text;
        }
        .invoice-page.edit-mode .invoice-editable:focus {
            outline: 2px solid rgba(139, 26, 26, 0.58);
            background: rgba(139, 26, 26, 0.1);
        }
        .invoice-page.edit-mode .invoice-editable.is-empty::before {
            content: attr(data-placeholder);
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .invoice-actions { display: none !important; }
            .invoice-page {
                max-width: none;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-actions">
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Nota</button>
        <button type="button" class="btn btn-secondary" id="editInvoiceBtn">Edit Isi Nota</button>
        <button type="button" class="btn btn-success" id="saveInvoiceBtn" style="display:none">Simpan Perubahan</button>
        <button type="button" class="btn btn-warning" id="cancelInvoiceBtn" style="display:none">Batal</button>
        <a href="<?= htmlspecialchars($stockLink) ?>" class="btn btn-secondary">Lihat Stok FIFO</a>
        <a href="<?= BASE_URL ?>/index.php?page=sales&action=create" class="btn btn-secondary">Transaksi Baru</a>
        <a href="<?= BASE_URL ?>/index.php?page=sales" class="btn btn-secondary">Riwayat Transaksi</a>
        <div class="action-grow"></div>
        <div class="invoice-status" id="invoiceStatus"></div>
    </div>

    <div class="invoice-page" id="invoicePage">
        <div class="company-header">
            <div class="top-line"><?= $invoiceField('header_top', (string)$invoiceView['header_top'], 'Isi baris kop atas', 'is-block', '', 'span') ?></div>
            <div class="name"><?= $invoiceField('company_name', (string)$invoiceView['company_name'], 'Isi nama toko / perusahaan', 'is-block', '', 'span') ?></div>
            <div class="company-contact">
                <span class="address"><?= $invoiceField('company_address', (string)$invoiceView['company_address'], 'Isi alamat toko / perusahaan', '', '', 'span') ?></span>
                <span class="phone"><?= $invoiceField('company_phone', (string)$invoiceView['company_phone'], 'Isi nomor telepon', '', '', 'span') ?></span>
            </div>
        </div>

        <div class="invoice-title"><?= $invoiceField('invoice_title', (string)$invoiceView['invoice_title'], 'Isi judul nota', 'is-block', 'Nota Penjualan', 'span') ?></div>

        <table class="invoice-meta">
            <tr>
                <td class="meta-label">Nomor</td>
                <td class="meta-sep">:</td>
                <td><?= $invoiceField('nomor_transaksi', (string)$invoiceView['nomor_transaksi'], 'Isi nomor nota') ?></td>
            </tr>
            <tr>
                <td class="meta-label">Hari/Tanggal</td>
                <td class="meta-sep">:</td>
                <td><?= $invoiceField('tanggal_label', (string)$invoiceView['tanggal_label'], 'Isi hari / tanggal nota') ?></td>
            </tr>
            <tr>
                <td class="meta-label">Pelanggan</td>
                <td class="meta-sep">:</td>
                <td><?= $invoiceField('nama_pelanggan', (string)$invoiceView['nama_pelanggan'], 'Isi nama pelanggan') ?></td>
            </tr>
            <tr>
                <td class="meta-label">Alamat</td>
                <td class="meta-sep">:</td>
                <td><?= $invoiceField('alamat_pelanggan', (string)$invoiceView['alamat_pelanggan'], 'Isi alamat pelanggan') ?></td>
            </tr>
        </table>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width:48px">No</th>
                    <th>Nama Barang</th>
                    <th style="width:110px">Jumlah</th>
                    <th style="width:86px">Satuan</th>
                    <th style="width:140px">Harga Satuan</th>
                    <th style="width:150px">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($items as $item): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($item['nama_barang']) ?></strong><br>
                            <span style="font-size:11px;color:#6b7280"><?= htmlspecialchars($item['kode_barang']) ?></span>
                        </td>
                        <td class="text-center"><?= number_format($item['qty']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['satuan']) ?></td>
                        <td class="text-right"><?= formatRupiah($item['harga_jual']) ?></td>
                        <td class="text-right"><?= formatRupiah($item['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="total-label">Total</td>
                    <td class="text-right"><?= formatRupiah($sale['total']) ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="signature-wrap">
            <div class="signature-box">
                <div>Dibuat oleh:</div>
                <div class="signature-space"></div>
                <div class="signature-name"><?= $invoiceField('signer_name', (string)$invoiceView['signer_name'], 'Isi nama penanda tangan') ?></div>
                <div class="signature-role">(<?= $invoiceField('signer_role', (string)$invoiceView['signer_role'], 'Isi jabatan penanda tangan') ?>)</div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const saleId = <?= (int)$invoiceView['sale_id'] ?>;
        const saveUrl = '<?= BASE_URL ?>/index.php?page=sales&action=save_invoice';
        const defaultDocumentTitleSuffix = <?= json_encode(APP_NAME, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const invoicePage = document.getElementById('invoicePage');
        const editBtn = document.getElementById('editInvoiceBtn');
        const saveBtn = document.getElementById('saveInvoiceBtn');
        const cancelBtn = document.getElementById('cancelInvoiceBtn');
        const statusBox = document.getElementById('invoiceStatus');
        const editables = Array.from(document.querySelectorAll('.invoice-editable'));
        let editMode = false;
        let snapshot = captureValues();

        function captureValues() {
            const values = {};
            editables.forEach(el => {
                values[el.dataset.field] = getValue(el);
            });
            return values;
        }

        function getValue(el) {
            return (el.textContent || '').replace(/\u200B/g, '').trim();
        }

        function applyValue(el, value) {
            el.textContent = value || '';
            refreshEmptyState(el);
        }

        function refreshEmptyState(el) {
            el.classList.toggle('is-empty', getValue(el) === '');
        }

        function setStatus(message, type) {
            statusBox.textContent = message || '';
            statusBox.className = 'invoice-status' + (type ? ' ' + type : '');
        }

        function setEditMode(active) {
            editMode = active;
            invoicePage.classList.toggle('edit-mode', active);
            editables.forEach(el => {
                el.contentEditable = active ? 'true' : 'false';
                el.spellcheck = false;
                if (!active) {
                    el.blur();
                }
                refreshEmptyState(el);
            });
            editBtn.style.display = active ? 'none' : 'inline-flex';
            saveBtn.style.display = active ? 'inline-flex' : 'none';
            cancelBtn.style.display = active ? 'inline-flex' : 'none';
        }

        function restoreSnapshot(values) {
            editables.forEach(el => {
                applyValue(el, values[el.dataset.field] || '');
            });
        }

        editables.forEach(el => {
            refreshEmptyState(el);
            el.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.execCommand('insertLineBreak');
                }
            });
            el.addEventListener('input', function() {
                refreshEmptyState(el);
            });
        });

        editBtn.addEventListener('click', function() {
            snapshot = captureValues();
            setStatus('Mode edit aktif. Ubah isi nota langsung di lembar ini.', '');
            setEditMode(true);
        });

        cancelBtn.addEventListener('click', function() {
            restoreSnapshot(snapshot);
            setEditMode(false);
            setStatus('Perubahan dibatalkan.', '');
        });

        saveBtn.addEventListener('click', async function() {
            const payload = captureValues();
            const formData = new FormData();
            formData.append('sale_id', String(saleId));
            Object.keys(payload).forEach(key => formData.append(key, payload[key]));

            saveBtn.disabled = true;
            cancelBtn.disabled = true;
            setStatus('Menyimpan perubahan nota...', '');

            try {
                const response = await fetch(saveUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Gagal menyimpan perubahan nota.');
                }

                editables.forEach(el => {
                    const key = el.dataset.field;
                    if (Object.prototype.hasOwnProperty.call(data.invoice, key)) {
                        applyValue(el, data.invoice[key] || '');
                    }
                });
                snapshot = captureValues();
                document.title = 'Nota ' + (data.invoice.nomor_transaksi || '') + ' - ' + (data.invoice.company_name || defaultDocumentTitleSuffix);
                setEditMode(false);
                setStatus(data.message || 'Isi nota berhasil diperbarui.', 'success');
            } catch (error) {
                setStatus(error.message || 'Gagal menyimpan perubahan nota.', 'error');
            } finally {
                saveBtn.disabled = false;
                cancelBtn.disabled = false;
            }
        });
    });
    </script>
</body>
</html>
