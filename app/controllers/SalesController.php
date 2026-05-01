<?php
class SalesController {
    private $model;
    public function __construct() { $this->model = new Sale(); }
    
    public function index() {
        $filters = ['customer_id' => get('customer_id'), 'date_from' => get('date_from'), 'date_to' => get('date_to'), 'search' => get('search'), 'page' => max(1, (int)get('p', 1))];
        $sales = $this->model->getAll($filters);
        $total = $this->model->count($filters);
        $totalPages = ceil($total / PER_PAGE);
        $page = $filters['page'];
        $customerModel = new Customer();
        $customers = $customerModel->getAll('', 1, 1000);
        $pageTitle = 'Riwayat Penjualan'; $currentPage = 'sales';
        $content = APP_PATH . '/views/sales/index.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function create() {
        $pageTitle = 'Transaksi Penjualan'; $currentPage = 'sales';
        $content = APP_PATH . '/views/sales/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function store() {
        $customerid = post('customer_id');
        $tanggal = post('tanggal_transaksi');
        $catatan = post('catatan');
        $productIds = $_POST['product_id'] ?? [];
        $qtys = $_POST['qty'] ?? [];
        $hargaJuals = $_POST['harga_jual'] ?? [];
        
        if (empty($customerid) || empty($tanggal)) { setFlash('error', 'Toko dan tanggal wajib diisi.'); redirect('sales', ['action' => 'create']); return; }
        if (empty($productIds)) { setFlash('error', 'Tambahkan minimal 1 item.'); redirect('sales', ['action' => 'create']); return; }
        
        $items = [];
        for ($i = 0; $i < count($productIds); $i++) {
            if (empty($productIds[$i]) || empty($qtys[$i]) || empty($hargaJuals[$i])) continue;
            $items[] = ['product_id' => (int)$productIds[$i], 'qty' => (int)$qtys[$i], 'harga_jual' => (float)$hargaJuals[$i]];
        }
        
        if (empty($items)) { setFlash('error', 'Tambahkan minimal 1 item yang valid.'); redirect('sales', ['action' => 'create']); return; }
        
        try {
            $saleId = $this->model->create(['customer_id' => $customerid, 'tanggal_transaksi' => $tanggal, 'catatan' => $catatan, 'created_by' => currentUserId()], $items);
            setFlash('success', 'Transaksi berhasil disimpan.');
            redirect('sales', ['action' => 'invoice', 'id' => $saleId]);
        } catch (Exception $e) {
            setFlash('error', 'Gagal: ' . $e->getMessage());
            redirect('sales', ['action' => 'create']);
        }
    }
    
    public function invoice() {
        $id = (int)get('id');
        $sale = $this->model->getById($id);
        if (!$sale) { setFlash('error', 'Transaksi tidak ditemukan.'); redirect('sales'); return; }
        $items = $this->model->getItems($id);
        $pageTitle = 'Nota #' . $sale['nomor_transaksi'];
        require APP_PATH . '/views/sales/invoice.php';
    }
}
