<?php
$formatModalRange = static function ($min, $max) {
    if ($min === null || $max === null) {
        return '-';
    }

    if ((float)$min === (float)$max) {
        return formatRupiah($min);
    }

    return formatRupiah($min) . ' - ' . formatRupiah($max);
};

$trendLabel = 'Belum ada perubahan';
$trendClass = 'text-muted';
if ($priceChange > 0) {
    $trendLabel = 'Naik ' . formatRupiah($priceChange) . ' (' . $priceChangePercent . '%)';
    $trendClass = 'text-danger';
} elseif ($priceChange < 0) {
    $trendLabel = 'Turun ' . formatRupiah(abs($priceChange)) . ' (' . abs($priceChangePercent) . '%)';
    $trendClass = 'text-success';
} elseif (!empty($priceHistory)) {
    $trendLabel = 'Harga terakhir tidak berubah';
}

$chartWidth = 760;
$chartHeight = 300;
$chartLeft = 56;
$chartRight = 24;
$chartTop = 22;
$chartBottom = 44;
$chartPoints = [];
$valueLabels = [];
$axisLabels = [];

if (!empty($priceChart)) {
    $values = array_map(static fn($point) => (float)$point['harga_rata'], $priceChart);
    $minValue = min($values);
    $maxValue = max($values);

    if ($minValue === $maxValue) {
        $padding = max(1, $minValue * 0.08);
        $minValue -= $padding;
        $maxValue += $padding;
    }

    $plotWidth = $chartWidth - $chartLeft - $chartRight;
    $plotHeight = $chartHeight - $chartTop - $chartBottom;
    $lastIndex = count($priceChart) - 1;
    $labelStep = max(1, (int)ceil(count($priceChart) / 6));

    foreach ($priceChart as $index => $point) {
        $x = $lastIndex > 0
            ? $chartLeft + ($plotWidth * $index / $lastIndex)
            : $chartLeft + ($plotWidth / 2);
        $y = $chartTop + (($maxValue - (float)$point['harga_rata']) / ($maxValue - $minValue)) * $plotHeight;

        $chartPoints[] = [
            'x' => round($x, 2),
            'y' => round($y, 2),
            'label' => formatPeriodLabel($point['period_key'], $grouping, true),
            'value' => (float)$point['harga_rata'],
        ];

        if ($index % $labelStep === 0 || $index === $lastIndex) {
            $axisLabels[] = $chartPoints[$index];
        }
    }

    for ($i = 0; $i <= 4; $i++) {
        $value = $maxValue - (($maxValue - $minValue) / 4) * $i;
        $y = $chartTop + (($maxValue - $value) / ($maxValue - $minValue)) * $plotHeight;
        $valueLabels[] = [
            'value' => $value,
            'y' => round($y, 2),
        ];
    }
}

$linePoints = implode(' ', array_map(static fn($point) => $point['x'] . ',' . $point['y'], $chartPoints));
$areaPoints = '';
if (!empty($chartPoints)) {
    $areaPoints = $chartPoints[0]['x'] . ',' . ($chartHeight - $chartBottom) . ' ' . $linePoints . ' ' . $chartPoints[count($chartPoints) - 1]['x'] . ',' . ($chartHeight - $chartBottom);
}

$baseDetailQuery = [
    'page' => 'reports',
    'action' => 'stock_detail',
    'id' => (int)$summary['id'],
];

$detailFilters = [
    'supplier_id' => $stockSupplierId,
    'stock_status' => $stockStatus,
    'stock_date_from' => $stockDateFrom,
    'stock_date_to' => $stockDateTo,
    'customer_id' => $saleCustomerId,
    'sale_date_from' => $saleDateFrom,
    'sale_date_to' => $saleDateTo,
];

$buildDetailUrl = static function (array $params = []) use ($baseDetailQuery, $detailFilters) {
    $query = array_merge($baseDetailQuery, ['group' => $params['group'] ?? 'day'], $detailFilters, $params);
    $query = array_filter($query, static fn($value) => $value !== null && $value !== '');
    return BASE_URL . '/index.php?' . http_build_query($query);
};

$resetDetailUrl = BASE_URL . '/index.php?' . http_build_query($baseDetailQuery + ['group' => $grouping]);
$hasDetailFilters = $stockSupplierId || $stockStatus !== '' || $stockDateFrom !== '' || $stockDateTo !== '' || $saleCustomerId || $saleDateFrom !== '' || $saleDateTo !== '';
?>

<div class="toolbar no-print">
    <div>
        <h3 style="font-size:14px;color:var(--muted)">Detail FIFO dan fluktuasi harga</h3>
        <p class="section-note">Halaman ini merangkum sisa batch aktif, riwayat barang masuk, dan batch mana saja yang terpakai saat penjualan.</p>
    </div>
    <div class="btn-group">
        <a href="<?= BASE_URL ?>/index.php?page=reports&action=stock" class="btn btn-outline">Kembali ke Laporan Stok</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="detail-header">
            <div>
                <span class="panel-kicker">Stock intelligence</span>
                <h2 class="panel-title"><?= htmlspecialchars($summary['nama_barang']) ?></h2>
                <div class="detail-tags">
                    <span class="detail-tag">Kode: <?= htmlspecialchars($summary['kode_barang']) ?></span>
                    <span class="detail-tag">Kategori: <?= htmlspecialchars($summary['kategori'] ?: '-') ?></span>
                    <span class="detail-tag">Satuan: <?= htmlspecialchars($summary['satuan']) ?></span>
                    <span class="detail-tag">Minimum: <?= number_format((int)$summary['stok_minimum']) ?></span>
                </div>
            </div>
            <div class="detail-header-side">
                <span class="metric-kicker">Harga modal terakhir</span>
                <div class="metric-value"><?= $summary['harga_modal_terakhir'] !== null ? formatRupiah($summary['harga_modal_terakhir']) : '-' ?></div>
                <div class="metric-sub">Update terakhir dari histori barang masuk.</div>
                <div class="metric-sub <?= $trendClass ?>"><?= $trendLabel ?></div>
            </div>
        </div>

        <div class="metric-grid">
            <div class="metric-card">
                <span class="metric-kicker">Stok aktif</span>
                <div class="metric-value"><?= number_format((int)$summary['stok_total']) ?></div>
                <div class="metric-sub">Sisa stok yang masih tersedia untuk dijual.</div>
            </div>
            <div class="metric-card">
                <span class="metric-kicker">Batch aktif</span>
                <div class="metric-value"><?= number_format((int)$summary['batch_aktif']) ?></div>
                <div class="metric-sub">Jumlah batch yang masih antre di FIFO.</div>
            </div>
            <div class="metric-card">
                <span class="metric-kicker">Rentang modal aktif</span>
                <div class="metric-value" style="font-size:1.3rem"><?= $formatModalRange($summary['harga_modal_min'], $summary['harga_modal_max']) ?></div>
                <div class="metric-sub">Harga modal terendah sampai tertinggi dari batch yang masih tersisa.</div>
            </div>
            <div class="metric-card">
                <span class="metric-kicker">Rata-rata modal aktif</span>
                <div class="metric-value"><?= $averageActiveModal > 0 ? formatRupiah($averageActiveModal) : '-' ?></div>
                <div class="metric-sub">Rata-rata modal dari stok aktif yang saat ini tersisa.</div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2 mt-3">
    <div class="card">
        <div class="card-header">
            <div>
                <h3>Grafik Fluktuasi Harga Modal</h3>
                <p class="section-note">Gunakan filter day, month, atau year untuk melihat perubahan harga modal.</p>
            </div>
            <div class="segmented-control no-print">
                <a href="<?= $buildDetailUrl(['group' => 'day']) ?>" class="segmented-link <?= $grouping === 'day' ? 'active' : '' ?>">Day</a>
                <a href="<?= $buildDetailUrl(['group' => 'month']) ?>" class="segmented-link <?= $grouping === 'month' ? 'active' : '' ?>">Month</a>
                <a href="<?= $buildDetailUrl(['group' => 'year']) ?>" class="segmented-link <?= $grouping === 'year' ? 'active' : '' ?>">Year</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($priceChart)): ?>
                <div class="empty-state">
                    <div class="empty-icon">GR</div>
                    <h4>Belum ada histori harga</h4>
                    <p>Begitu barang masuk dicatat, grafik fluktuasi harga modal akan muncul di sini.</p>
                </div>
            <?php else: ?>
                <div class="chart-meta">
                    <div class="chart-meta-item">
                        <span class="metric-kicker">Harga pertama</span>
                        <div class="metric-value" style="font-size:1.35rem"><?= $summary['harga_modal_pertama'] !== null ? formatRupiah($summary['harga_modal_pertama']) : '-' ?></div>
                    </div>
                    <div class="chart-meta-item">
                        <span class="metric-kicker">Harga tertinggi</span>
                        <div class="metric-value" style="font-size:1.35rem"><?= formatRupiah(max(array_map(static fn($point) => (float)$point['harga_max'], $priceChart))) ?></div>
                    </div>
                    <div class="chart-meta-item">
                        <span class="metric-kicker">Harga terendah</span>
                        <div class="metric-value" style="font-size:1.35rem"><?= formatRupiah(min(array_map(static fn($point) => (float)$point['harga_min'], $priceChart))) ?></div>
                    </div>
                </div>

                <div class="chart-shell">
                    <svg viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight ?>" class="chart-svg" role="img" aria-label="Grafik fluktuasi harga modal">
                        <defs>
                            <linearGradient id="priceArea" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#c76b3c" stop-opacity="0.28"></stop>
                                <stop offset="100%" stop-color="#c76b3c" stop-opacity="0.03"></stop>
                            </linearGradient>
                        </defs>

                        <?php foreach ($valueLabels as $line): ?>
                            <line x1="<?= $chartLeft ?>" y1="<?= $line['y'] ?>" x2="<?= $chartWidth - $chartRight ?>" y2="<?= $line['y'] ?>" stroke="rgba(24, 33, 39, 0.08)" stroke-dasharray="4 6"></line>
                            <text x="<?= $chartLeft - 10 ?>" y="<?= $line['y'] + 4 ?>" text-anchor="end" font-size="11" fill="#8e98a3"><?= formatRupiah($line['value'], false) ?></text>
                        <?php endforeach; ?>

                        <?php if ($areaPoints !== ''): ?>
                            <polygon points="<?= $areaPoints ?>" fill="url(#priceArea)"></polygon>
                            <polyline points="<?= $linePoints ?>" fill="none" stroke="#c76b3c" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                        <?php endif; ?>

                        <?php foreach ($chartPoints as $point): ?>
                            <circle cx="<?= $point['x'] ?>" cy="<?= $point['y'] ?>" r="5.5" fill="#133c3a" stroke="#ffffff" stroke-width="3"></circle>
                        <?php endforeach; ?>

                        <?php foreach ($axisLabels as $point): ?>
                            <text x="<?= $point['x'] ?>" y="<?= $chartHeight - 14 ?>" text-anchor="middle" font-size="11" fill="#6e7781"><?= htmlspecialchars($point['label']) ?></text>
                        <?php endforeach; ?>
                    </svg>
                </div>

                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-dot" style="background:#c76b3c"></span>Rata-rata harga modal per <?= htmlspecialchars($grouping) ?></span>
                    <span class="legend-item"><span class="legend-dot" style="background:#133c3a"></span>Titik histori harga dari barang masuk</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3>Batch FIFO Aktif</h3>
                <p class="section-note">Baris paling atas adalah batch yang akan keluar terlebih dahulu pada transaksi berikutnya.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-compact-mobile">
                <thead>
                    <tr><th>Urutan</th><th class="mobile-hide-col">Tanggal Masuk</th><th class="text-right mobile-hide-col">Qty Masuk</th><th class="text-right">Sisa</th><th class="text-right mobile-hide-col">Harga Modal</th><th class="text-right mobile-hide-col">Nilai Sisa</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($activeBatches)): ?>
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">0</div><h4>Tidak ada batch aktif</h4><p>Barang ini sedang tidak memiliki stok tersisa.</p></div></td></tr>
                    <?php else: foreach ($activeBatches as $index => $batch): ?>
                        <tr>
                            <td>
                                <?php if ($index === 0): ?>
                                    <span class="badge badge-success">Keluar berikutnya</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Antrian <?= $index + 1 ?></span>
                                <?php endif; ?>
                                <div class="mobile-only-inline"><?= formatDate($batch['tanggal_masuk']) ?></div>
                            </td>
                            <td class="text-nowrap mobile-hide-col"><?= formatDate($batch['tanggal_masuk']) ?></td>
                            <td class="text-right mobile-hide-col"><?= number_format($batch['qty_masuk']) ?> <?= htmlspecialchars($summary['satuan']) ?></td>
                            <td class="text-right fw-bold"><?= number_format($batch['qty_sisa']) ?></td>
                            <td class="text-right mobile-hide-col"><?= formatRupiah($batch['harga_modal']) ?></td>
                            <td class="text-right mobile-hide-col"><?= formatRupiah($batch['qty_sisa'] * $batch['harga_modal']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-3 no-print">
    <div class="card-header">
        <div>
            <h3>Filter Detail Stok</h3>
            <p class="section-note">Filter ini hanya memengaruhi tabel riwayat barang masuk dan audit FIFO di bawah.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-group">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="action" value="stock_detail">
            <input type="hidden" name="id" value="<?= (int)$summary['id'] ?>">
            <input type="hidden" name="group" value="<?= htmlspecialchars($grouping) ?>">

            <select name="supplier_id" class="form-control">
                <option value="">Semua Supplier</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier['id'] ?>" <?= (string)$stockSupplierId === (string)$supplier['id'] ? 'selected' : '' ?>><?= htmlspecialchars($supplier['nama_toko']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="stock_status" class="form-control">
                <option value="">Semua Status Batch</option>
                <option value="utuh" <?= $stockStatus === 'utuh' ? 'selected' : '' ?>>Masih utuh</option>
                <option value="sebagian" <?= $stockStatus === 'sebagian' ? 'selected' : '' ?>>Terpakai sebagian</option>
                <option value="habis" <?= $stockStatus === 'habis' ? 'selected' : '' ?>>Habis terpakai</option>
            </select>

            <input type="date" name="stock_date_from" class="form-control" value="<?= htmlspecialchars($stockDateFrom) ?>" title="Tanggal masuk dari">
            <input type="date" name="stock_date_to" class="form-control" value="<?= htmlspecialchars($stockDateTo) ?>" title="Tanggal masuk sampai">

            <select name="customer_id" class="form-control">
                <option value="">Semua Pelanggan</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['id'] ?>" <?= (string)$saleCustomerId === (string)$customer['id'] ? 'selected' : '' ?>><?= htmlspecialchars($customer['nama_toko']) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="sale_date_from" class="form-control" value="<?= htmlspecialchars($saleDateFrom) ?>" title="Tanggal jual dari">
            <input type="date" name="sale_date_to" class="form-control" value="<?= htmlspecialchars($saleDateTo) ?>" title="Tanggal jual sampai">

            <button type="submit" class="btn btn-outline">Filter</button>
            <?php if ($hasDetailFilters): ?>
                <a href="<?= $resetDetailUrl ?>" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <div>
            <h3>Riwayat Barang Masuk dan Harga Modal</h3>
            <p class="section-note">Di sini terlihat kapan batch masuk, supplier asal, nomor nota, serta sisa batch yang masih tertahan di stok.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table-compact-mobile">
            <thead>
                <tr><th>Tanggal</th><th>Supplier</th><th class="mobile-hide-col">No. Nota</th><th class="mobile-hide-col">Arsip Nota</th><th class="text-right">Qty Masuk</th><th class="text-right mobile-hide-col">Qty Keluar</th><th class="text-right mobile-hide-col">Sisa</th><th class="text-right mobile-hide-col">Harga Modal</th><th class="mobile-hide-col">Status</th><th class="mobile-hide-col">Keterangan</th><th class="mobile-hide-col">Oleh</th></tr>
            </thead>
            <tbody>
                <?php if (empty($stockHistory)): ?>
                    <tr><td colspan="11"><div class="empty-state"><div class="empty-icon">IN</div><h4>Belum ada histori barang masuk</h4></div></td></tr>
                <?php else: foreach ($stockHistory as $row): ?>
                    <tr>
                        <td class="text-nowrap"><?= formatDate($row['tanggal_masuk']) ?></td>
                        <td><?= htmlspecialchars($row['supplier_name'] ?? '-') ?></td>
                        <td class="mobile-hide-col"><?= htmlspecialchars($row['nomor_nota'] ?? '-') ?></td>
                        <td class="mobile-hide-col">
                            <?php if (!empty($row['nota_file'])): ?>
                                <?php
                                $notaUrl = BASE_URL . '/public/uploads/stock-notes/' . rawurlencode($row['nota_file']);
                                $notaExt = strtolower(pathinfo($row['nota_file'], PATHINFO_EXTENSION));
                                $isImageNota = in_array($notaExt, ['jpg', 'jpeg', 'png', 'webp'], true);
                                ?>
                                <?php if ($isImageNota): ?>
                                    <a href="<?= htmlspecialchars($notaUrl) ?>" target="_blank" rel="noopener" title="Buka foto nota">
                                        <img src="<?= htmlspecialchars($notaUrl) ?>" alt="Foto nota" style="width:54px;height:54px;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
                                    </a>
                                <?php else: ?>
                                    <a href="<?= htmlspecialchars($notaUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">Buka Arsip</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?= number_format($row['qty_masuk']) ?> <?= htmlspecialchars($summary['satuan']) ?>
                            <div class="mobile-only-inline">Sisa: <?= number_format($row['qty_sisa']) ?></div>
                        </td>
                        <td class="text-right mobile-hide-col"><?= number_format($row['qty_keluar']) ?></td>
                        <td class="text-right fw-bold mobile-hide-col"><?= number_format($row['qty_sisa']) ?></td>
                        <td class="text-right mobile-hide-col"><?= formatRupiah($row['harga_modal']) ?></td>
                        <td class="mobile-hide-col">
                            <?php if ((int)$row['qty_sisa'] === 0): ?>
                                <span class="badge badge-neutral">Habis terpakai</span>
                            <?php elseif ((int)$row['qty_sisa'] === (int)$row['qty_masuk']): ?>
                                <span class="badge badge-success">Masih utuh</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Terpakai sebagian</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted mobile-hide-col"><?= htmlspecialchars($row['keterangan'] ?: '-') ?></td>
                        <td class="text-muted mobile-hide-col"><?= htmlspecialchars($row['created_by_name'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <div>
            <h3>Audit Pemakaian FIFO Saat Penjualan</h3>
            <p class="section-note">Setiap baris menunjukkan batch mana yang benar-benar terpotong ketika transaksi penjualan disimpan.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table-compact-mobile">
            <thead>
                <tr><th class="mobile-hide-col">Tanggal Jual</th><th>Nota</th><th class="mobile-hide-col">Pelanggan</th><th class="mobile-hide-col">Batch Asal</th><th class="text-right">Qty Keluar</th><th class="text-right mobile-hide-col">Modal Batch</th><th class="text-right">Total Modal</th></tr>
            </thead>
            <tbody>
                <?php if (empty($fifoUsage)): ?>
                    <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">TX</div><h4>Belum ada transaksi penjualan</h4><p>Begitu penjualan terjadi, jejak batch FIFO yang terpakai akan terlihat di sini.</p></div></td></tr>
                <?php else: foreach ($fifoUsage as $usage): ?>
                    <tr>
                        <td class="text-nowrap mobile-hide-col"><?= formatDate($usage['tanggal_transaksi']) ?></td>
                        <td>
                            <span class="font-mono"><?= htmlspecialchars($usage['nomor_transaksi']) ?></span>
                            <div class="mobile-only-inline"><?= formatDate($usage['tanggal_transaksi']) ?> · <?= htmlspecialchars($usage['nama_toko']) ?></div>
                        </td>
                        <td class="fw-bold mobile-hide-col"><?= htmlspecialchars($usage['nama_toko']) ?></td>
                        <td class="mobile-hide-col">Batch <?= formatDate($usage['batch_tanggal_masuk']) ?></td>
                        <td class="text-right"><?= number_format($usage['qty_keluar']) ?> <?= htmlspecialchars($summary['satuan']) ?></td>
                        <td class="text-right mobile-hide-col"><?= formatRupiah($usage['harga_modal_batch']) ?></td>
                        <td class="text-right fw-bold"><?= formatRupiah($usage['total_modal_batch']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
