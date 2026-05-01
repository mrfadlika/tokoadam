<?php
/**
 * FIFO (First In, First Out) Inventory Helper
 */

function getAvailableStock($productId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COALESCE(SUM(qty_sisa), 0) FROM stock_batches WHERE product_id = ? AND qty_sisa > 0");
    $stmt->execute([$productId]);
    return (int) $stmt->fetchColumn();
}

function processStockOut($productId, $qtyNeeded) {
    $db = getDB();
    $available = getAvailableStock($productId);
    if ($available < $qtyNeeded) {
        throw new Exception("Stok tidak cukup. Tersedia: $available, Dibutuhkan: $qtyNeeded");
    }
    $stmt = $db->prepare(
        "SELECT id, qty_sisa, harga_modal, tanggal_masuk FROM stock_batches 
         WHERE product_id = ? AND qty_sisa > 0 ORDER BY tanggal_masuk ASC, id ASC"
    );
    $stmt->execute([$productId]);
    $batches = $stmt->fetchAll();
    $remaining = $qtyNeeded;
    $deductions = [];
    foreach ($batches as $batch) {
        if ($remaining <= 0) break;
        $takeQty = min($remaining, $batch['qty_sisa']);
        $db->prepare("UPDATE stock_batches SET qty_sisa = qty_sisa - ? WHERE id = ?")->execute([$takeQty, $batch['id']]);
        $deductions[] = [
            'stock_batch_id' => $batch['id'],
            'qty_keluar' => $takeQty,
            'harga_modal_batch' => $batch['harga_modal'],
        ];
        $remaining -= $takeQty;
    }
    return $deductions;
}

function calculateAverageModal($deductions) {
    $totalCost = 0; $totalQty = 0;
    foreach ($deductions as $d) {
        $totalCost += $d['qty_keluar'] * $d['harga_modal_batch'];
        $totalQty += $d['qty_keluar'];
    }
    return $totalQty > 0 ? round($totalCost / $totalQty, 2) : 0;
}

function getStockBreakdown($productId) {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT id, tanggal_masuk, qty_masuk, qty_sisa, harga_modal FROM stock_batches 
         WHERE product_id = ? AND qty_sisa > 0 ORDER BY tanggal_masuk ASC"
    );
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}
