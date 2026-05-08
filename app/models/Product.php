<?php
class Product {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function getAll($search = '', $page = 1, $limit = PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (p.nama_barang LIKE ? OR p.kode_barang LIKE ? OR p.kategori LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
        }
        
        $sql = "SELECT p.*, COALESCE(SUM(sb.qty_sisa), 0) as stok_total 
                FROM products p 
                LEFT JOIN stock_batches sb ON sb.product_id = p.id AND sb.qty_sisa > 0
                $where
                GROUP BY p.id
                ORDER BY p.updated_at DESC
                LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function count($search = '') {
        $where = "WHERE 1=1";
        $params = [];
        if ($search) {
            $where .= " AND (nama_barang LIKE ? OR kode_barang LIKE ? OR kategori LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products $where");
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT p.*, COALESCE(SUM(sb.qty_sisa), 0) as stok_total 
             FROM products p 
             LEFT JOIN stock_batches sb ON sb.product_id = p.id AND sb.qty_sisa > 0
             WHERE p.id = ? GROUP BY p.id"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function resolveExactActive($query) {
        $query = trim((string)$query);
        if ($query === '') {
            return ['status' => 'empty', 'product' => null];
        }

        $stmt = $this->db->prepare(
            "SELECT id
             FROM products
             WHERE is_active = 1 AND LOWER(kode_barang) = LOWER(?)
             LIMIT 1"
        );
        $stmt->execute([$query]);
        $codeMatchId = $stmt->fetchColumn();
        if ($codeMatchId) {
            return ['status' => 'found', 'product' => $this->getById((int)$codeMatchId)];
        }

        $stmt = $this->db->prepare(
            "SELECT id
             FROM products
             WHERE is_active = 1 AND LOWER(nama_barang) = LOWER(?)
             ORDER BY id ASC
             LIMIT 2"
        );
        $stmt->execute([$query]);
        $nameMatches = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (count($nameMatches) === 1) {
            return ['status' => 'found', 'product' => $this->getById((int)$nameMatches[0])];
        }

        return [
            'status' => count($nameMatches) > 1 ? 'ambiguous' : 'not_found',
            'product' => null
        ];
    }
    
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO products (kode_barang, nama_barang, kategori, satuan, foto, harga_jual, stok_minimum) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['kode_barang'], $data['nama_barang'], $data['kategori'] ?? null,
            $data['satuan'], $data['foto'] ?? null, $data['harga_jual'] ?? 0,
            $data['stok_minimum'] ?? 10
        ]);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach (['kode_barang', 'nama_barang', 'kategori', 'satuan', 'harga_jual', 'stok_minimum', 'is_active'] as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (isset($data['foto'])) {
            $fields[] = "foto = ?";
            $params[] = $data['foto'];
        }
        
        $params[] = $id;
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }
    
    public function delete($id) {
        return $this->db->prepare("UPDATE products SET is_active = 0 WHERE id = ?")->execute([$id]);
    }
    
    public function getLowStock() {
        return $this->db->query(
            "SELECT p.*, COALESCE(SUM(sb.qty_sisa), 0) as stok_total
             FROM products p
             LEFT JOIN stock_batches sb ON sb.product_id = p.id AND sb.qty_sisa > 0
             WHERE p.is_active = 1
             GROUP BY p.id
             HAVING stok_total <= p.stok_minimum
             ORDER BY stok_total ASC"
        )->fetchAll();
    }
    
    public function getTotalCount() {
        return $this->db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
    }
    
    public function search($query) {
        $stmt = $this->db->prepare(
            "SELECT p.id, p.kode_barang, p.nama_barang, p.satuan, p.harga_jual,
                    COALESCE(SUM(sb.qty_sisa), 0) as stok_total
             FROM products p
             LEFT JOIN stock_batches sb ON sb.product_id = p.id AND sb.qty_sisa > 0
             WHERE p.is_active = 1 AND (p.nama_barang LIKE ? OR p.kode_barang LIKE ?)
             GROUP BY p.id LIMIT 10"
        );
        $stmt->execute(["%$query%", "%$query%"]);
        return $stmt->fetchAll();
    }
    
    public function getCategories() {
        return $this->db->query("SELECT DISTINCT kategori FROM products WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getUnits() {
        return $this->db->query("SELECT DISTINCT satuan FROM products ORDER BY satuan")->fetchAll(PDO::FETCH_COLUMN);
    }
}
