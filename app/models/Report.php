<?php
class Report {
    private $db;
    public function __construct() { $this->db = getDB(); }
    
    public function getSalesReport($dateFrom, $dateTo, $customerId = null, $productId = null) {
        [$where, $params] = $this->buildSalesWhere($dateFrom, $dateTo, $customerId, $productId);
        $stmt = $this->db->prepare(
            "SELECT
                s.*,
                c.nama_toko,
                u.nama as created_by_name,
                COALESCE(SUM(si.qty), 0) as total_item,
                COALESCE(SUM(si.subtotal), 0) as total_nominal_terfilter
             FROM sales s
             JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = s.created_by
             JOIN sale_items si ON si.sale_id = s.id
             $where
             GROUP BY s.id
             ORDER BY s.tanggal_transaksi DESC, s.id DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getSalesPerCustomer($dateFrom, $dateTo, $customerId = null, $productId = null) {
        [$where, $params] = $this->buildSalesWhere($dateFrom, $dateTo, $customerId, $productId);
        $stmt = $this->db->prepare(
            "SELECT
                c.nama_toko,
                COUNT(DISTINCT s.id) as jumlah_transaksi,
                COALESCE(SUM(si.subtotal), 0) as total_nominal,
                COALESCE(SUM(si.qty), 0) as total_item
             FROM sales s
             JOIN customers c ON c.id = s.customer_id
             JOIN sale_items si ON si.sale_id = s.id
             $where
             GROUP BY c.id
             ORDER BY total_nominal DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getSalesTotals($dateFrom, $dateTo, $customerId = null, $productId = null) {
        [$where, $params] = $this->buildSalesWhere($dateFrom, $dateTo, $customerId, $productId);
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(DISTINCT s.id) as total_transaksi,
                COALESCE(SUM(si.qty), 0) as total_item,
                COALESCE(SUM(si.subtotal), 0) as total_nominal
             FROM sales s
             JOIN sale_items si ON si.sale_id = s.id
             $where"
        );
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function getStockReport() {
        return $this->db->query(
            "SELECT
                p.*,
                COALESCE(SUM(sb.qty_sisa), 0) as stok_total,
                COUNT(sb.id) as batch_aktif,
                MIN(sb.harga_modal) as harga_modal_min,
                MAX(sb.harga_modal) as harga_modal_max,
                (
                    SELECT ph.harga_modal
                    FROM price_histories ph
                    WHERE ph.product_id = p.id
                    ORDER BY ph.tanggal DESC, ph.id DESC
                    LIMIT 1
                ) as harga_modal_terakhir
             FROM products p
             LEFT JOIN stock_batches sb ON sb.product_id = p.id AND sb.qty_sisa > 0
             WHERE p.is_active = 1
             GROUP BY p.id
             ORDER BY p.nama_barang"
        )->fetchAll();
    }

    public function getProductStockSummary($productId) {
        $stmt = $this->db->prepare(
            "SELECT
                p.*,
                COALESCE(SUM(sb.qty_sisa), 0) as stok_total,
                COUNT(sb.id) as batch_aktif,
                MIN(sb.harga_modal) as harga_modal_min,
                MAX(sb.harga_modal) as harga_modal_max,
                COALESCE(SUM(sb.qty_sisa * sb.harga_modal), 0) as nilai_stok_aktif,
                (
                    SELECT ph.harga_modal
                    FROM price_histories ph
                    WHERE ph.product_id = p.id
                    ORDER BY ph.tanggal DESC, ph.id DESC
                    LIMIT 1
                ) as harga_modal_terakhir,
                (
                    SELECT ph.harga_modal
                    FROM price_histories ph
                    WHERE ph.product_id = p.id
                    ORDER BY ph.tanggal ASC, ph.id ASC
                    LIMIT 1
                ) as harga_modal_pertama,
                (
                    SELECT COUNT(*)
                    FROM price_histories ph
                    WHERE ph.product_id = p.id
                ) as total_perubahan_harga
             FROM products p
             LEFT JOIN stock_batches sb ON sb.product_id = p.id AND sb.qty_sisa > 0
             WHERE p.id = ?
             GROUP BY p.id"
        );
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    public function getProductActiveBatches($productId) {
        $stmt = $this->db->prepare(
            "SELECT sb.*, u.nama as created_by_name, c.nama_toko as supplier_name
             FROM stock_batches sb
             LEFT JOIN users u ON u.id = sb.created_by
             LEFT JOIN customers c ON c.id = sb.supplier_id
             WHERE sb.product_id = ? AND sb.qty_sisa > 0
             ORDER BY sb.tanggal_masuk ASC, sb.id ASC"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getProductStockHistory($productId) {
        $stmt = $this->db->prepare(
            "SELECT
                sb.*,
                u.nama as created_by_name,
                c.nama_toko as supplier_name,
                (sb.qty_masuk - sb.qty_sisa) as qty_keluar
             FROM stock_batches sb
             LEFT JOIN users u ON u.id = sb.created_by
             LEFT JOIN customers c ON c.id = sb.supplier_id
             WHERE sb.product_id = ?
             ORDER BY sb.tanggal_masuk DESC, sb.id DESC"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getProductPriceHistory($productId) {
        $stmt = $this->db->prepare(
            "SELECT
                ph.*,
                sb.qty_masuk,
                sb.keterangan
             FROM price_histories ph
             LEFT JOIN stock_batches sb ON sb.id = ph.sumber_batch_id
             WHERE ph.product_id = ?
             ORDER BY ph.tanggal ASC, ph.id ASC"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getProductPriceChart($productId, $grouping = 'day') {
        $periodExpression = $this->buildPricePeriodExpression($grouping);
        $stmt = $this->db->prepare(
            "SELECT
                $periodExpression as period_key,
                MIN(ph.tanggal) as tanggal_awal,
                MAX(ph.tanggal) as tanggal_akhir,
                ROUND(AVG(ph.harga_modal), 2) as harga_rata,
                MIN(ph.harga_modal) as harga_min,
                MAX(ph.harga_modal) as harga_max,
                COUNT(*) as total_batch
             FROM price_histories ph
             WHERE ph.product_id = ?
             GROUP BY period_key
             ORDER BY period_key ASC"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getProductFifoUsage($productId) {
        $stmt = $this->db->prepare(
            "SELECT
                s.id as sale_id,
                s.nomor_transaksi,
                s.tanggal_transaksi,
                c.nama_toko,
                si.qty as qty_terjual_item,
                si.harga_jual,
                sif.qty_keluar,
                sif.harga_modal_batch,
                (sif.qty_keluar * sif.harga_modal_batch) as total_modal_batch,
                sb.id as stock_batch_id,
                sb.tanggal_masuk as batch_tanggal_masuk
             FROM sale_item_fifo sif
             JOIN sale_items si ON si.id = sif.sale_item_id
             JOIN sales s ON s.id = si.sale_id
             JOIN customers c ON c.id = s.customer_id
             JOIN stock_batches sb ON sb.id = sif.stock_batch_id
             WHERE si.product_id = ?
             ORDER BY s.tanggal_transaksi ASC, s.id ASC, sb.tanggal_masuk ASC, sif.id ASC"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    public function getFinancialSummary($dateFrom, $dateTo) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(si.subtotal), 0) as total_penjualan, COALESCE(SUM(sif.qty_keluar * sif.harga_modal_batch), 0) as total_modal FROM sales s JOIN sale_items si ON si.sale_id = s.id JOIN sale_item_fifo sif ON sif.sale_item_id = si.id WHERE s.tanggal_transaksi BETWEEN ? AND ?");
        $stmt->execute([$dateFrom, $dateTo]); $result = $stmt->fetch();
        $result['laba_kotor'] = $result['total_penjualan'] - $result['total_modal'];
        $result['margin'] = $result['total_penjualan'] > 0 ? round(($result['laba_kotor'] / $result['total_penjualan']) * 100, 1) : 0;
        return $result;
    }

    private function buildPricePeriodExpression($grouping) {
        return match ($grouping) {
            'month' => "DATE_FORMAT(ph.tanggal, '%Y-%m')",
            'year' => "DATE_FORMAT(ph.tanggal, '%Y')",
            default => "DATE_FORMAT(ph.tanggal, '%Y-%m-%d')",
        };
    }

    private function buildSalesWhere($dateFrom, $dateTo, $customerId = null, $productId = null) {
        $where = "WHERE s.tanggal_transaksi BETWEEN ? AND ?";
        $params = [$dateFrom, $dateTo];

        if ($customerId) {
            $where .= " AND s.customer_id = ?";
            $params[] = $customerId;
        }

        if ($productId) {
            $where .= " AND si.product_id = ?";
            $params[] = $productId;
        }

        return [$where, $params];
    }
}
