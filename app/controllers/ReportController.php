<?php
class ReportController {
    private $model;
    public function __construct() { $this->model = new Report(); }
    
    public function sales() {
        $dateFrom = get('date_from', date('Y-m-01'));
        $dateTo = get('date_to', date('Y-m-d'));
        $customerId = get('customer_id');
        $productId = get('product_id');
        $salesData = $this->model->getSalesReport($dateFrom, $dateTo, $customerId ?: null, $productId ?: null);
        $totals = $this->model->getSalesTotals($dateFrom, $dateTo, $customerId ?: null, $productId ?: null);
        $perCustomer = $this->model->getSalesPerCustomer($dateFrom, $dateTo, $customerId ?: null, $productId ?: null);
        $customerModel = new Customer();
        $productModel = new Product();
        $customers = $customerModel->getAll('', 1, 1000, ['type' => 'customer']);
        $products = $productModel->getAll('', 1, 1000);
        $pageTitle = 'Laporan Penjualan'; $currentPage = 'reports';
        $action = 'sales';
        $content = APP_PATH . '/views/reports/sales.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function stock() {
        $reportModel = new Report();
        $stockData = $reportModel->getStockReport();
        $pageTitle = 'Laporan Stok'; $currentPage = 'reports';
        $action = 'stock';
        $content = APP_PATH . '/views/reports/stock.php';
        require APP_PATH . '/views/layouts/app.php';
    }

    public function stockDetail() {
        $productId = (int)get('id');
        if ($productId <= 0) {
            setFlash('error', 'Barang yang ingin dilihat tidak valid.');
            redirect('reports', ['action' => 'stock']);
            return;
        }

        $grouping = get('group', 'day');
        if (!in_array($grouping, ['day', 'month', 'year'], true)) {
            $grouping = 'day';
        }

        $stockSupplierId = (int)get('supplier_id');
        if ($stockSupplierId <= 0) {
            $stockSupplierId = null;
        }

        $stockStatus = trim((string)get('stock_status', ''));
        if (!in_array($stockStatus, ['utuh', 'sebagian', 'habis'], true)) {
            $stockStatus = '';
        }

        $stockDateFrom = trim((string)get('stock_date_from', ''));
        $stockDateTo = trim((string)get('stock_date_to', ''));

        $saleCustomerId = (int)get('customer_id');
        if ($saleCustomerId <= 0) {
            $saleCustomerId = null;
        }

        $saleDateFrom = trim((string)get('sale_date_from', ''));
        $saleDateTo = trim((string)get('sale_date_to', ''));

        $stockFilters = [
            'supplier_id' => $stockSupplierId,
            'status' => $stockStatus,
            'date_from' => $stockDateFrom,
            'date_to' => $stockDateTo,
        ];

        $usageFilters = [
            'customer_id' => $saleCustomerId,
            'date_from' => $saleDateFrom,
            'date_to' => $saleDateTo,
        ];

        $summary = $this->model->getProductStockSummary($productId);
        if (!$summary) {
            setFlash('error', 'Barang tidak ditemukan.');
            redirect('reports', ['action' => 'stock']);
            return;
        }

        $activeBatches = $this->model->getProductActiveBatches($productId);
        $stockHistory = $this->model->getProductStockHistory($productId, $stockFilters);
        $priceHistory = $this->model->getProductPriceHistory($productId);
        $priceChart = $this->model->getProductPriceChart($productId, $grouping);
        $fifoUsage = $this->model->getProductFifoUsage($productId, $usageFilters);

        $customerModel = new Customer();
        $suppliers = $customerModel->getAll('', 1, 1000, ['type' => 'supplier']);
        $customers = $customerModel->getAll('', 1, 1000, ['type' => 'customer']);

        $priceChange = 0;
        $priceChangePercent = 0;
        if (count($priceHistory) >= 2) {
            $latest = $priceHistory[count($priceHistory) - 1];
            $previous = $priceHistory[count($priceHistory) - 2];
            $priceChange = (float)$latest['harga_modal'] - (float)$previous['harga_modal'];
            if ((float)$previous['harga_modal'] > 0) {
                $priceChangePercent = round(($priceChange / (float)$previous['harga_modal']) * 100, 1);
            }
        }

        $averageActiveModal = (float)$summary['stok_total'] > 0
            ? round((float)$summary['nilai_stok_aktif'] / (float)$summary['stok_total'], 2)
            : 0;

        $pageTitle = 'Detail Stok - ' . $summary['nama_barang'];
        $currentPage = 'reports';
        $action = 'stock';
        $content = APP_PATH . '/views/reports/stock-detail.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function financial() {
        $dateFrom = get('date_from', date('Y-m-01'));
        $dateTo = get('date_to', date('Y-m-d'));
        $summary = $this->model->getFinancialSummary($dateFrom, $dateTo);
        $perCustomer = $this->model->getSalesPerCustomer($dateFrom, $dateTo);
        $pageTitle = 'Laporan Keuangan'; $currentPage = 'reports';
        $action = 'financial';
        $content = APP_PATH . '/views/reports/financial.php';
        require APP_PATH . '/views/layouts/app.php';
    }
}
