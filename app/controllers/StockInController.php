<?php
class StockInController {
    private $model;
    public function __construct() { $this->model = new StockBatch(); }
    
    public function index() {
        $filters = ['product_id' => get('product_id'), 'date_from' => get('date_from'), 'date_to' => get('date_to'), 'page' => max(1, (int)get('p', 1))];
        $batches = $this->model->getAll($filters);
        $total = $this->model->count($filters);
        $totalPages = ceil($total / PER_PAGE);
        $page = $filters['page'];
        $productModel = new Product();
        $products = $productModel->getAll('', 1, 1000);
        $pageTitle = 'Barang Masuk'; $currentPage = 'stock-in';
        $content = APP_PATH . '/views/stock-in/index.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function create() {
        $pageTitle = 'Input Barang Masuk'; $currentPage = 'stock-in';
        $content = APP_PATH . '/views/stock-in/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function store() {
        $data = [
            'product_id' => post('product_id'),
            'tanggal_masuk' => post('tanggal_masuk'),
            'qty_masuk' => post('qty_masuk'),
            'harga_modal' => post('harga_modal'),
            'keterangan' => post('keterangan'),
            'created_by' => currentUserId()
        ];
        
        $v = new Validator($data);
        $v->required('product_id', 'Barang')->required('tanggal_masuk', 'Tanggal masuk')
          ->required('qty_masuk', 'Qty masuk')->numeric('qty_masuk', 'Qty masuk')->minValue('qty_masuk', 1, 'Qty masuk')
          ->required('harga_modal', 'Harga modal')->numeric('harga_modal', 'Harga modal')->minValue('harga_modal', 1, 'Harga modal');
        
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('stock-in', ['action' => 'create']); return; }
        
        try {
            $this->model->create($data);
            setFlash('success', 'Barang masuk berhasil dicatat.');
            redirect('stock-in');
        } catch (Exception $e) {
            setFlash('error', 'Gagal: ' . $e->getMessage());
            redirect('stock-in', ['action' => 'create']);
        }
    }
}
