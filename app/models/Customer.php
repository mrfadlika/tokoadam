<?php
class Customer {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function getAll($search = '', $page = 1, $limit = PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $where = "WHERE 1=1";
        
        if ($search) {
            $where .= " AND (c.nama_toko LIKE ? OR c.nama_pic LIKE ? OR c.phone LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM sales s WHERE s.customer_id = c.id) as total_transaksi,
                (SELECT MAX(s.tanggal_transaksi) FROM sales s WHERE s.customer_id = c.id) as terakhir_transaksi
                FROM customers c $where ORDER BY c.updated_at DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function count($search = '') {
        $params = [];
        $where = "WHERE 1=1";
        if ($search) {
            $where .= " AND (nama_toko LIKE ? OR nama_pic LIKE ? OR phone LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM customers $where");
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO customers (nama_toko, nama_pic, phone, alamat, catatan) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nama_toko'], $data['nama_pic'] ?? null,
            $data['phone'] ?? null, $data['alamat'] ?? null, $data['catatan'] ?? null
        ]);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE customers SET nama_toko=?, nama_pic=?, phone=?, alamat=?, catatan=? WHERE id=?"
        );
        return $stmt->execute([
            $data['nama_toko'], $data['nama_pic'] ?? null,
            $data['phone'] ?? null, $data['alamat'] ?? null, $data['catatan'] ?? null, $id
        ]);
    }
    
    public function delete($id) {
        // Check if customer has transactions
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sales WHERE customer_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Toko ini memiliki riwayat transaksi dan tidak bisa dihapus.");
        }
        return $this->db->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
    }
    
    public function search($query) {
        $stmt = $this->db->prepare(
            "SELECT id, nama_toko, nama_pic, phone FROM customers 
             WHERE nama_toko LIKE ? OR nama_pic LIKE ? LIMIT 10"
        );
        $stmt->execute(["%$query%", "%$query%"]);
        return $stmt->fetchAll();
    }
    
    public function getTotalCount() {
        return $this->db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    }
    
    public function getTopCustomers($limit = 5) {
        return $this->db->query(
            "SELECT c.nama_toko, COUNT(s.id) as jumlah_transaksi, COALESCE(SUM(s.total), 0) as total_belanja
             FROM customers c
             JOIN sales s ON s.customer_id = c.id
             GROUP BY c.id
             ORDER BY jumlah_transaksi DESC
             LIMIT $limit"
        )->fetchAll();
    }
}
