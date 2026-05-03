<?php
$stockLink = BASE_URL . '/index.php?page=reports&action=stock';
if (count($items) === 1) {
    $stockLink = BASE_URL . '/index.php?page=reports&action=stock_detail&id=' . $items[0]['product_id'];
}

$signerName = COMPANY_SIGNER_NAME !== '' ? COMPANY_SIGNER_NAME : ($sale['created_by_name'] ?? 'Administrator');
$signerRole = COMPANY_SIGNER_ROLE !== '' ? COMPANY_SIGNER_ROLE : 'Owner/Manager';
$companyAddressLine = trim(COMPANY_ADDRESS);
$companyPhoneLine = trim(COMPANY_PHONE);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota <?= htmlspecialchars($sale['nomor_transaksi']) ?> - <?= APP_NAME ?></title>
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
            gap: 10px;
            padding: 14px 24px;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid rgba(17, 24, 39, 0.08);
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
            margin-top: 6px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .company-header .address,
        .company-header .phone {
            margin-top: 6px;
            font-size: 12px;
            line-height: 1.5;
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
        .invoice-notes {
            margin-top: 14px;
            font-size: 13px;
            line-height: 1.6;
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
        <a href="<?= htmlspecialchars($stockLink) ?>" class="btn btn-secondary">Lihat Stok FIFO</a>
        <a href="<?= BASE_URL ?>/index.php?page=sales&action=create" class="btn btn-secondary">Transaksi Baru</a>
        <a href="<?= BASE_URL ?>/index.php?page=sales" class="btn btn-secondary">Riwayat Transaksi</a>
    </div>

    <div class="invoice-page">
        <div class="company-header">
            <?php if (COMPANY_HEADER_TOP !== ''): ?>
                <div class="top-line"><?= htmlspecialchars(COMPANY_HEADER_TOP) ?></div>
            <?php endif; ?>
            <div class="name"><?= htmlspecialchars(APP_NAME) ?></div>
            <?php if ($companyAddressLine !== ''): ?>
                <div class="address"><?= htmlspecialchars($companyAddressLine) ?></div>
            <?php endif; ?>
            <?php if ($companyPhoneLine !== ''): ?>
                <div class="phone"><?= htmlspecialchars($companyPhoneLine) ?></div>
            <?php endif; ?>
        </div>

        <div class="invoice-title">Nota Penjualan</div>

        <table class="invoice-meta">
            <tr>
                <td class="meta-label">Nomor</td>
                <td class="meta-sep">:</td>
                <td><?= htmlspecialchars($sale['nomor_transaksi']) ?></td>
            </tr>
            <tr>
                <td class="meta-label">Hari/Tanggal</td>
                <td class="meta-sep">:</td>
                <td><?= formatDateWithDay($sale['tanggal_transaksi']) ?></td>
            </tr>
            <tr>
                <td class="meta-label">Pelanggan</td>
                <td class="meta-sep">:</td>
                <td><?= htmlspecialchars($sale['nama_toko']) ?></td>
            </tr>
            <?php if (!empty($sale['alamat_toko'])): ?>
                <tr>
                    <td class="meta-label">Alamat</td>
                    <td class="meta-sep">:</td>
                    <td><?= htmlspecialchars($sale['alamat_toko']) ?></td>
                </tr>
            <?php endif; ?>
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
                <div class="signature-name"><?= htmlspecialchars($signerName) ?></div>
                <div class="signature-role">(<?= htmlspecialchars($signerRole) ?>)</div>
            </div>
        </div>
    </div>
</body>
</html>
