<?php
class StockInController {
    private $model;
    public function __construct() { $this->model = new StockBatch(); }
    
    public function index() {
        $filters = [
            'product_id' => get('product_id'),
            'supplier_id' => get('supplier_id'),
            'date_from' => get('date_from'),
            'date_to' => get('date_to'),
            'page' => max(1, (int)get('p', 1))
        ];
        $batches = $this->model->getAll($filters);
        $total = $this->model->count($filters);
        $totalPages = ceil($total / PER_PAGE);
        $page = $filters['page'];
        $productModel = new Product();
        $customerModel = new Customer();
        $products = $productModel->getAll('', 1, 1000);
        $suppliers = $customerModel->getAll('', 1, 1000, ['type' => 'supplier']);
        $pageTitle = 'Barang Masuk'; $currentPage = 'stock-in';
        $content = APP_PATH . '/views/stock-in/index.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function create() {
        $customerModel = new Customer();
        $suppliers = $customerModel->getAll('', 1, 1000, ['type' => 'supplier']);
        $pageTitle = 'Input Barang Masuk'; $currentPage = 'stock-in';
        $content = APP_PATH . '/views/stock-in/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function store() {
        $data = [
            'product_id' => post('product_id'),
            'supplier_id' => post('supplier_id') !== '' ? (int)post('supplier_id') : null,
            'tanggal_masuk' => post('tanggal_masuk'),
            'qty_masuk' => post('qty_masuk'),
            'harga_modal' => post('harga_modal'),
            'nomor_nota' => trim((string)post('nomor_nota')),
            'keterangan' => post('keterangan'),
            'created_by' => currentUserId()
        ];
        
        $v = new Validator($data);
        $v->required('product_id', 'Barang')->required('tanggal_masuk', 'Tanggal masuk')
          ->required('qty_masuk', 'Qty masuk')->numeric('qty_masuk', 'Qty masuk')->minValue('qty_masuk', 1, 'Qty masuk')
          ->required('harga_modal', 'Harga modal')->numeric('harga_modal', 'Harga modal')->minValue('harga_modal', 1, 'Harga modal');
        
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('stock-in', ['action' => 'create']); return; }

        if ($data['supplier_id']) {
            $supplier = (new Customer())->getById($data['supplier_id']);
            if (!$supplier || ($supplier['tipe'] ?? 'customer') !== 'supplier') {
                setFlash('error', 'Supplier yang dipilih tidak valid.');
                redirect('stock-in', ['action' => 'create']);
                return;
            }
        }

        $notaFile = $this->uploadNota($_FILES['nota_file'] ?? null);
        if ($notaFile === false) {
            redirect('stock-in', ['action' => 'create']);
            return;
        }
        $data['nota_file'] = $notaFile;
        
        try {
            $this->model->create($data);
            setFlash('success', !empty($data['nota_file']) ? 'Barang masuk dan arsip nota berhasil dicatat.' : 'Barang masuk berhasil dicatat.');
            redirect('stock-in');
        } catch (Exception $e) {
            if (!empty($data['nota_file']) && file_exists(STOCK_NOTE_PATH . '/' . $data['nota_file'])) {
                @unlink(STOCK_NOTE_PATH . '/' . $data['nota_file']);
            }
            setFlash('error', 'Gagal: ' . $e->getMessage());
            redirect('stock-in', ['action' => 'create']);
        }
    }

    private function uploadNota($file) {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            setFlash('error', 'Upload file nota gagal. Silakan coba lagi.');
            return false;
        }

        if (($file['size'] ?? 0) > MAX_NOTE_UPLOAD_SIZE) {
            setFlash('error', 'Ukuran file nota maksimal 4MB.');
            return false;
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_NOTE_EXTENSIONS, true)) {
            setFlash('error', 'Format nota harus PDF, JPG, JPEG, PNG, atau WEBP.');
            return false;
        }

        $mimeType = $file['type'] ?? '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detectedMime = finfo_file($finfo, $file['tmp_name']);
                if ($detectedMime) {
                    $mimeType = $detectedMime;
                }
                finfo_close($finfo);
            }
        }

        if ($mimeType !== '' && !in_array($mimeType, ALLOWED_NOTE_TYPES, true)) {
            setFlash('error', 'Tipe file nota tidak didukung.');
            return false;
        }

        if (!is_dir(STOCK_NOTE_PATH) && !mkdir(STOCK_NOTE_PATH, 0755, true) && !is_dir(STOCK_NOTE_PATH)) {
            setFlash('error', 'Folder upload nota tidak bisa dibuat.');
            return false;
        }

        $filename = 'nota_' . time() . '_' . random_int(1000, 9999) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], STOCK_NOTE_PATH . '/' . $filename)) {
            setFlash('error', 'File nota gagal disimpan.');
            return false;
        }

        return $filename;
    }
}
