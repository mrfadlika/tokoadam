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
?>

<div class="toolbar">
    <div>
        <h3 style="font-size:14px;color:var(--muted)">Laporan stok per <?= formatDate(date('Y-m-d')) ?></h3>
        <p class="section-note">Klik nama barang untuk melihat sisa batch FIFO, riwayat barang masuk, dan grafik fluktuasi harga modal.</p>
    </div>
    <button type="button" class="btn btn-outline no-print" onclick="window.print()">Cetak</button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-compact-mobile">
            <thead>
                <tr>
                    <th class="mobile-hide-col">Kode</th>
                    <th>Nama Barang</th>
                    <th class="mobile-hide-col">Kategori</th>
                    <th class="mobile-hide-col">Satuan</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center mobile-hide-col">Batch Aktif</th>
                    <th class="text-right mobile-hide-col">Modal Aktif</th>
                    <th class="text-right mobile-hide-col">Modal Terakhir</th>
                    <th class="text-center mobile-hide-col">Minimum</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stockData)): ?>
                    <tr><td colspan="10"><div class="empty-state"><div class="empty-icon">ST</div><h4>Belum ada data stok</h4><p>Mulai catat barang masuk untuk melihat analisa FIFO.</p></div></td></tr>
                <?php else: foreach ($stockData as $s): $stok = (int)$s['stok_total']; ?>
                    <tr>
                        <td class="mobile-hide-col"><span class="font-mono"><?= htmlspecialchars($s['kode_barang']) ?></span></td>
                        <td>
                            <a href="<?= BASE_URL ?>/index.php?page=reports&action=stock_detail&id=<?= $s['id'] ?>" class="table-link"><?= htmlspecialchars($s['nama_barang']) ?></a>
                            <div class="table-note">Lihat detail FIFO dan harga modal</div>
                            <div class="mobile-only-inline"><span class="font-mono"><?= htmlspecialchars($s['kode_barang']) ?></span></div>
                        </td>
                        <td class="mobile-hide-col"><?= htmlspecialchars($s['kategori'] ?? '-') ?></td>
                        <td class="mobile-hide-col"><?= htmlspecialchars($s['satuan']) ?></td>
                        <td class="text-center fw-bold <?= $stok <= $s['stok_minimum'] ? ($stok === 0 ? 'text-danger' : 'text-warning') : 'text-success' ?>"><?= number_format($stok) ?></td>
                        <td class="text-center mobile-hide-col"><?= number_format((int)$s['batch_aktif']) ?></td>
                        <td class="text-right mobile-hide-col"><?= $formatModalRange($s['harga_modal_min'], $s['harga_modal_max']) ?></td>
                        <td class="text-right mobile-hide-col"><?= $s['harga_modal_terakhir'] !== null ? formatRupiah($s['harga_modal_terakhir']) : '-' ?></td>
                        <td class="text-center mobile-hide-col"><?= number_format((int)$s['stok_minimum']) ?></td>
                        <td>
                            <?php if ($stok === 0): ?>
                                <span class="badge badge-danger">Habis</span>
                            <?php elseif ($stok <= $s['stok_minimum']): ?>
                                <span class="badge badge-warning">Menipis</span>
                            <?php else: ?>
                                <span class="badge badge-success">Aman</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
