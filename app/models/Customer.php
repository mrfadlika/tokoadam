<?php
class Customer {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function getAll($search = '', $page = 1, $limit = PER_PAGE, $filters = []) {
        $offset = ($page - 1) * $limit;
        [$where, $params] = $this->buildFilterSql($search, $filters, 'c');

        $sql = "SELECT c.*, 
                CASE 
                    WHEN c.tipe = 'supplier' THEN (SELECT COUNT(*) FROM stock_batches sb WHERE sb.supplier_id = c.id)
                    ELSE (SELECT COUNT(*) FROM sales s WHERE s.customer_id = c.id)
                END as total_aktivitas,
                CASE
                    WHEN c.tipe = 'supplier' THEN (SELECT MAX(sb.tanggal_masuk) FROM stock_batches sb WHERE sb.supplier_id = c.id)
                    ELSE (SELECT MAX(s.tanggal_transaksi) FROM sales s WHERE s.customer_id = c.id)
                END as terakhir_aktivitas
                FROM customers c $where ORDER BY c.updated_at DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function count($search = '', $filters = []) {
        [$where, $params] = $this->buildFilterSql($search, $filters, 'c');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM customers c $where");
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
            "INSERT INTO customers (tipe, nama_toko, nama_pic, phone, alamat, catatan) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $this->sanitizeType($data['tipe'] ?? 'customer') ?? 'customer',
            $data['nama_toko'],
            $data['nama_pic'] ?? null,
            $data['phone'] ?? null,
            $data['alamat'] ?? null,
            $data['catatan'] ?? null
        ]);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE customers SET tipe=?, nama_toko=?, nama_pic=?, phone=?, alamat=?, catatan=? WHERE id=?"
        );
        return $stmt->execute([
            $this->sanitizeType($data['tipe'] ?? 'customer') ?? 'customer',
            $data['nama_toko'],
            $data['nama_pic'] ?? null,
            $data['phone'] ?? null,
            $data['alamat'] ?? null,
            $data['catatan'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        $contact = $this->getById($id);
        if (!$contact) {
            throw new Exception("Data tidak ditemukan.");
        }

        if (($contact['tipe'] ?? 'customer') === 'supplier') {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM stock_batches WHERE supplier_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Supplier ini sudah dipakai pada barang masuk dan tidak bisa dihapus.");
            }
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM sales WHERE customer_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Pelanggan ini memiliki riwayat transaksi dan tidak bisa dihapus.");
            }
        }

        return $this->db->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
    }
    
    public function search($query, $type = 'customer') {
        $params = ["%$query%", "%$query%", "%$query%"];
        $where = "WHERE (nama_toko LIKE ? OR nama_pic LIKE ? OR phone LIKE ?)";
        $type = $this->sanitizeType($type);
        if ($type) {
            $where .= " AND tipe = ?";
            $params[] = $type;
        }

        $stmt = $this->db->prepare(
            "SELECT id, tipe, nama_toko, nama_pic, phone
             FROM customers
             $where
             ORDER BY updated_at DESC
             LIMIT 10"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getTotalCount($type = null) {
        $type = $this->sanitizeType($type);
        if ($type) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM customers WHERE tipe = ?");
            $stmt->execute([$type]);
            return $stmt->fetchColumn();
        }

        return $this->db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    }
    
    public function getTopCustomers($limit = 5) {
        return $this->db->query(
            "SELECT c.nama_toko, COUNT(s.id) as jumlah_transaksi, COALESCE(SUM(s.total), 0) as total_belanja
             FROM customers c
             JOIN sales s ON s.customer_id = c.id
             WHERE c.tipe = 'customer'
             GROUP BY c.id
             ORDER BY jumlah_transaksi DESC
             LIMIT $limit"
        )->fetchAll();
    }

    private function buildFilterSql($search = '', $filters = [], $alias = '') {
        $prefix = $alias !== '' ? $alias . '.' : '';
        $where = "WHERE 1=1";
        $params = [];
        $type = $this->sanitizeType($filters['type'] ?? null);

        if ($type) {
            $where .= " AND {$prefix}tipe = ?";
            $params[] = $type;
        }

        if ($search) {
            $where .= " AND ({$prefix}nama_toko LIKE ? OR {$prefix}nama_pic LIKE ? OR {$prefix}phone LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        return [$where, $params];
    }

    private function sanitizeType($type) {
        return in_array($type, ['customer', 'supplier'], true) ? $type : null;
    }
}
