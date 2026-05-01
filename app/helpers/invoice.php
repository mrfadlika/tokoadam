<?php
/**
 * Invoice Number Generator
 * Format: INV-YYYYMMDD-XXXX
 */

function generateInvoiceNumber() {
    $db = getDB();
    $prefix = INVOICE_PREFIX;
    $dateStr = date('Ymd');
    $pattern = $prefix . '-' . $dateStr . '-%';
    
    // Get the last invoice number for today
    $stmt = $db->prepare("SELECT nomor_transaksi FROM sales WHERE nomor_transaksi LIKE ? ORDER BY nomor_transaksi DESC LIMIT 1");
    $stmt->execute([$pattern]);
    $last = $stmt->fetchColumn();
    
    if ($last) {
        // Extract the sequence number and increment
        $parts = explode('-', $last);
        $seq = (int)end($parts) + 1;
    } else {
        $seq = 1;
    }
    
    return $prefix . '-' . $dateStr . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
}
