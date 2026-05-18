<?php
class StockBatch {
    private $db;
    public function __construct() { $this->db = getDB(); }
    
    public function create($data) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO stock_batches (product_id, supplier_id, tanggal_masuk, qty_masuk, qty_sisa, harga_modal, nomor_nota, nota_file, keterangan, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $data['product_id'],
                $data['supplier_id'] ?? null,
                $data['tanggal_masuk'],
                $data['qty_masuk'],
                $data['qty_masuk'],
                $data['harga_modal'],
                $data['nomor_nota'] ?? null,
                $data['nota_file'] ?? null,
                $data['keterangan'] ?? null,
                $data['created_by'] ?? null
            ]);
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
        if (!empty($filters['supplier_id'])) { $where .= " AND sb.supplier_id = ?"; $params[] = $filters['supplier_id']; }
        if (!empty($filters['date_from'])) { $where .= " AND sb.tanggal_masuk >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where .= " AND sb.tanggal_masuk <= ?"; $params[] = $filters['date_to']; }
        $page = $filters['page'] ?? 1; $limit = $filters['limit'] ?? PER_PAGE; $offset = ($page - 1) * $limit;
        $sql = "SELECT sb.*, p.nama_barang, p.kode_barang, p.satuan, u.nama as created_by_name, c.nama_toko as supplier_name
                FROM stock_batches sb
                JOIN products p ON p.id = sb.product_id
                LEFT JOIN users u ON u.id = sb.created_by
                LEFT JOIN customers c ON c.id = sb.supplier_id
                $where
                ORDER BY sb.tanggal_masuk DESC, sb.id DESC LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll();
    }
    
    public function count($filters = []) {
        $where = "WHERE 1=1"; $params = [];
        if (!empty($filters['product_id'])) { $where .= " AND product_id = ?"; $params[] = $filters['product_id']; }
        if (!empty($filters['supplier_id'])) { $where .= " AND supplier_id = ?"; $params[] = $filters['supplier_id']; }
        if (!empty($filters['date_from'])) { $where .= " AND tanggal_masuk >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where .= " AND tanggal_masuk <= ?"; $params[] = $filters['date_to']; }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM stock_batches $where"); $stmt->execute($params); return $stmt->fetchColumn();
    }
    
    public function getByProduct($productId) {
        $stmt = $this->db->prepare("SELECT * FROM stock_batches WHERE product_id = ? AND qty_sisa > 0 ORDER BY tanggal_masuk ASC");
        $stmt->execute([$productId]); return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT sb.*, p.nama_barang, p.kode_barang, p.satuan FROM stock_batches sb JOIN products p ON p.id = sb.product_id WHERE sb.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function isUsedInSale($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sale_item_fifo WHERE stock_batch_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return true;
        }
        
        $stmt = $this->db->prepare("SELECT qty_masuk, qty_sisa FROM stock_batches WHERE id = ?");
        $stmt->execute([$id]);
        $batch = $stmt->fetch();
        if ($batch && (float)$batch['qty_sisa'] < (float)$batch['qty_masuk']) {
            return true;
        }
        
        return false;
    }

    public function update($id, $data) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM stock_batches WHERE id = ?");
            $stmt->execute([$id]);
            $old = $stmt->fetch();
            if (!$old) {
                throw new Exception("Batch tidak ditemukan.");
            }

            $isUsed = $this->isUsedInSale($id);
            if ($isUsed) {
                if ((int)$old['product_id'] !== (int)$data['product_id'] || (int)$old['qty_masuk'] !== (int)$data['qty_masuk']) {
                    throw new Exception("Barang atau Qty masuk tidak bisa diubah karena batch ini sudah digunakan dalam transaksi penjualan.");
                }
            }

            $qtySisa = $isUsed ? ($old['qty_sisa'] + ($data['qty_masuk'] - $old['qty_masuk'])) : $data['qty_masuk'];
            if ($qtySisa < 0) {
                throw new Exception("Jumlah sisa tidak boleh negatif.");
            }

            $stmt = $this->db->prepare(
                "UPDATE stock_batches 
                 SET product_id = ?, supplier_id = ?, tanggal_masuk = ?, qty_masuk = ?, qty_sisa = ?, harga_modal = ?, nomor_nota = ?, nota_file = ?, keterangan = ? 
                 WHERE id = ?"
            );
            $stmt->execute([
                $data['product_id'],
                $data['supplier_id'] !== '' ? (int)$data['supplier_id'] : null,
                $data['tanggal_masuk'],
                $data['qty_masuk'],
                $qtySisa,
                $data['harga_modal'],
                $data['nomor_nota'] !== '' ? $data['nomor_nota'] : null,
                $data['nota_file'] !== null ? $data['nota_file'] : $old['nota_file'],
                $data['keterangan'] !== '' ? $data['keterangan'] : null,
                $id
            ]);

            $stmt = $this->db->prepare("UPDATE stock_movements SET product_id = ?, qty = ? WHERE ref_type = 'stock_batch' AND ref_id = ?");
            $stmt->execute([$data['product_id'], $data['qty_masuk'], $id]);

            $stmt = $this->db->prepare("UPDATE price_histories SET product_id = ?, harga_modal = ?, tanggal = ? WHERE sumber_batch_id = ?");
            $stmt->execute([$data['product_id'], $data['harga_modal'], $data['tanggal_masuk'], $id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $this->db->beginTransaction();
        try {
            if ($this->isUsedInSale($id)) {
                throw new Exception("Barang masuk tidak bisa dihapus karena sudah digunakan dalam transaksi penjualan.");
            }

            $stmt = $this->db->prepare("SELECT nota_file FROM stock_batches WHERE id = ?");
            $stmt->execute([$id]);
            $batch = $stmt->fetch();

            $stmt = $this->db->prepare("DELETE FROM stock_movements WHERE ref_type = 'stock_batch' AND ref_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM price_histories WHERE sumber_batch_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM stock_batches WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            if ($batch && !empty($batch['nota_file'])) {
                $filePath = STOCK_NOTE_PATH . '/' . $batch['nota_file'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
