<?php
class StockBatch {
    private $db;
    public function __construct() { $this->db = getDB(); }
    
    public function create($data) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO stock_batches (product_id, tanggal_masuk, qty_masuk, qty_sisa, harga_modal, keterangan, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$data['product_id'], $data['tanggal_masuk'], $data['qty_masuk'], $data['qty_masuk'], $data['harga_modal'], $data['keterangan'] ?? null, $data['created_by'] ?? null]);
            $batchId = $this->db->lastInsertId();
            
            $this->db->prepare("INSERT INTO stock_movements (product_id, tipe, ref_type, ref_id, qty, keterangan) VALUES (?, 'in', 'stock_batch', ?, ?, 'Barang masuk')")->execute([$data['product_id'], $batchId, $data['qty_masuk']]);
            $this->db->prepare("INSERT INTO price_histories (product_id, harga_modal, sumber_batch_id, tanggal) VALUES (?, ?, ?, ?)")->execute([$data['product_id'], $data['harga_modal'], $batchId, $data['tanggal_masuk']]);
            
            $this->db->commit();
            return $batchId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getAll($filters = []) {
        $where = "WHERE 1=1"; $params = [];
        if (!empty($filters['product_id'])) { $where .= " AND sb.product_id = ?"; $params[] = $filters['product_id']; }
        if (!empty($filters['date_from'])) { $where .= " AND sb.tanggal_masuk >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where .= " AND sb.tanggal_masuk <= ?"; $params[] = $filters['date_to']; }
        $page = $filters['page'] ?? 1; $limit = $filters['limit'] ?? PER_PAGE; $offset = ($page - 1) * $limit;
        $sql = "SELECT sb.*, p.nama_barang, p.kode_barang, p.satuan, u.nama as created_by_name FROM stock_batches sb JOIN products p ON p.id = sb.product_id LEFT JOIN users u ON u.id = sb.created_by $where ORDER BY sb.tanggal_masuk DESC, sb.id DESC LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll();
    }
    
    public function count($filters = []) {
        $where = "WHERE 1=1"; $params = [];
        if (!empty($filters['product_id'])) { $where .= " AND product_id = ?"; $params[] = $filters['product_id']; }
        if (!empty($filters['date_from'])) { $where .= " AND tanggal_masuk >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where .= " AND tanggal_masuk <= ?"; $params[] = $filters['date_to']; }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM stock_batches $where"); $stmt->execute($params); return $stmt->fetchColumn();
    }
    
    public function getByProduct($productId) {
        $stmt = $this->db->prepare("SELECT * FROM stock_batches WHERE product_id = ? AND qty_sisa > 0 ORDER BY tanggal_masuk ASC");
        $stmt->execute([$productId]); return $stmt->fetchAll();
    }
}
