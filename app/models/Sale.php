<?php
class Sale {
    private $db;
    public function __construct() { $this->db = getDB(); }
    
    public function create($data, $items) {
        $this->db->beginTransaction();
        try {
            $invoiceNo = generateInvoiceNumber();
            $stmt = $this->db->prepare("INSERT INTO sales (nomor_transaksi, customer_id, tanggal_transaksi, total, catatan, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$invoiceNo, $data['customer_id'], $data['tanggal_transaksi'], 0, $data['catatan'] ?? null, $data['created_by'] ?? null]);
            $saleId = $this->db->lastInsertId();
            
            $grandTotal = 0;
            foreach ($items as $item) {
                $subtotal = $item['qty'] * $item['harga_jual'];
                $grandTotal += $subtotal;
                
                $stmt = $this->db->prepare("INSERT INTO sale_items (sale_id, product_id, qty, harga_jual, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$saleId, $item['product_id'], $item['qty'], $item['harga_jual'], $subtotal]);
                $saleItemId = $this->db->lastInsertId();
                
                // FIFO deduction
                $deductions = processStockOut($item['product_id'], $item['qty']);
                foreach ($deductions as $d) {
                    $this->db->prepare("INSERT INTO sale_item_fifo (sale_item_id, stock_batch_id, qty_keluar, harga_modal_batch) VALUES (?, ?, ?, ?)")
                        ->execute([$saleItemId, $d['stock_batch_id'], $d['qty_keluar'], $d['harga_modal_batch']]);
                }
                
                // Record stock movement
                $this->db->prepare("INSERT INTO stock_movements (product_id, tipe, ref_type, ref_id, qty, keterangan) VALUES (?, 'out', 'sale', ?, ?, ?)")
                    ->execute([$item['product_id'], $saleId, $item['qty'], 'Penjualan ' . $invoiceNo]);
            }
            
            $this->db->prepare("UPDATE sales SET total = ? WHERE id = ?")->execute([$grandTotal, $saleId]);
            $this->db->commit();
            return $saleId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getAll($filters = []) {
        $where = "WHERE 1=1"; $params = [];
        if (!empty($filters['customer_id'])) { $where .= " AND s.customer_id = ?"; $params[] = $filters['customer_id']; }
        if (!empty($filters['date_from'])) { $where .= " AND s.tanggal_transaksi >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where .= " AND s.tanggal_transaksi <= ?"; $params[] = $filters['date_to']; }
        if (!empty($filters['search'])) { $where .= " AND (s.nomor_transaksi LIKE ? OR c.nama_toko LIKE ?)"; $params[] = "%{$filters['search']}%"; $params[] = "%{$filters['search']}%"; }
        $page = $filters['page'] ?? 1; $limit = $filters['limit'] ?? PER_PAGE; $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT s.*, c.nama_toko, u.nama as created_by_name FROM sales s JOIN customers c ON c.id = s.customer_id LEFT JOIN users u ON u.id = s.created_by $where ORDER BY s.created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params); return $stmt->fetchAll();
    }
    
    public function count($filters = []) {
        $where = "WHERE 1=1"; $params = [];
        if (!empty($filters['customer_id'])) { $where .= " AND s.customer_id = ?"; $params[] = $filters['customer_id']; }
        if (!empty($filters['date_from'])) { $where .= " AND s.tanggal_transaksi >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where .= " AND s.tanggal_transaksi <= ?"; $params[] = $filters['date_to']; }
        if (!empty($filters['search'])) { $where .= " AND (s.nomor_transaksi LIKE ? OR c.nama_toko LIKE ?)"; $params[] = "%{$filters['search']}%"; $params[] = "%{$filters['search']}%"; }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sales s JOIN customers c ON c.id = s.customer_id $where");
        $stmt->execute($params); return $stmt->fetchColumn();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT s.*, c.nama_toko, c.alamat as alamat_toko, c.phone as phone_toko, u.nama as created_by_name FROM sales s JOIN customers c ON c.id = s.customer_id LEFT JOIN users u ON u.id = s.created_by WHERE s.id = ?");
        $stmt->execute([$id]); return $stmt->fetch();
    }
    
    public function getItems($saleId) {
        $stmt = $this->db->prepare("SELECT si.*, p.nama_barang, p.kode_barang, p.satuan FROM sale_items si JOIN products p ON p.id = si.product_id WHERE si.sale_id = ?");
        $stmt->execute([$saleId]); return $stmt->fetchAll();
    }
    
    public function getTodayCount() {
        return $this->db->query("SELECT COUNT(*) FROM sales WHERE tanggal_transaksi = CURDATE()")->fetchColumn();
    }
    
    public function getTodayTotal() {
        return $this->db->query("SELECT COALESCE(SUM(total), 0) FROM sales WHERE tanggal_transaksi = CURDATE()")->fetchColumn();
    }
    
    public function getRecent($limit = 5) {
        return $this->db->query("SELECT s.*, c.nama_toko FROM sales s JOIN customers c ON c.id = s.customer_id ORDER BY s.created_at DESC LIMIT $limit")->fetchAll();
    }
}
