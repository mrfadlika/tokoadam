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
        $productModel = new Product();
        $suppliers = $customerModel->getAll('', 1, 1000, ['type' => 'supplier']);
        $productOptions = $productModel->getActiveLookupList(1000);
        $pageTitle = 'Input Barang Masuk'; $currentPage = 'stock-in';
        $content = APP_PATH . '/views/stock-in/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function store() {
        $productResolution = $this->resolveProductSelection(
            post('product_id'),
            trim((string)($_POST['product_search'] ?? ''))
        );
        if ($productResolution['error']) {
            setFlash('error', $productResolution['error']);
            redirect('stock-in', ['action' => 'create']);
            return;
        }

        $data = [
            'product_id' => $productResolution['id'],
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

    private function resolveProductSelection($postedId, $productSearch) {
        $productModel = new Product();
        $postedId = (int)$postedId;

        if ($postedId > 0) {
            $product = $productModel->getById($postedId);
            if ($product && (int)($product['is_active'] ?? 0) === 1) {
                return ['id' => $postedId, 'error' => null];
            }

            return ['id' => '', 'error' => 'Barang yang dipilih tidak valid atau sudah nonaktif.'];
        }

        if ($productSearch === '') {
            return ['id' => '', 'error' => 'Barang wajib diisi.'];
        }

        $match = $productModel->resolveExactActive($productSearch);
        if ($match['status'] === 'found' && !empty($match['product']['id'])) {
            return ['id' => (int)$match['product']['id'], 'error' => null];
        }

        if ($match['status'] === 'ambiguous') {
            return ['id' => '', 'error' => 'Nama barang lebih dari satu. Pilih barang dari daftar pencarian.'];
        }

        return ['id' => '', 'error' => 'Barang tidak ditemukan. Ketik kode/nama yang valid lalu pilih dari daftar.'];
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

        if (!is_dir(STOCK_NOTE_PATH) && !@mkdir(STOCK_NOTE_PATH, 0755, true) && !is_dir(STOCK_NOTE_PATH)) {
            setFlash('error', 'Folder upload nota tidak bisa dibuat.');
            return false;
        }

        if (!is_writable(STOCK_NOTE_PATH)) {
            setFlash('error', 'Folder upload nota tidak punya izin tulis.');
            return false;
        }

        $filename = 'nota_' . time() . '_' . random_int(1000, 9999) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], STOCK_NOTE_PATH . '/' . $filename)) {
            setFlash('error', 'File nota gagal disimpan.');
            return false;
        }

        return $filename;
    }

    public function edit() {
        $id = (int)get('id');
        $batch = $this->model->getById($id);
        if (!$batch) {
            setFlash('error', 'Barang masuk tidak ditemukan.');
            redirect('stock-in');
            return;
        }

        $isUsed = $this->model->isUsedInSale($id);

        $customerModel = new Customer();
        $productModel = new Product();
        $suppliers = $customerModel->getAll('', 1, 1000, ['type' => 'supplier']);
        $productOptions = $productModel->getActiveLookupList(1000);
        
        $productOptionsLookup = [];
        foreach ($productOptions as $opt) {
            $productOptionsLookup[] = [
                'id' => (int)$opt['id'],
                'kode_barang' => (string)$opt['kode_barang'],
                'nama_barang' => (string)$opt['nama_barang'],
                'satuan' => (string)($opt['satuan'] ?? ''),
                'stok_total' => (int)($opt['stok_total'] ?? 0),
                'label' => trim((string)$opt['nama_barang']) . ' - ' . trim((string)$opt['kode_barang']),
            ];
        }

        $pageTitle = 'Edit Barang Masuk'; $currentPage = 'stock-in';
        $content = APP_PATH . '/views/stock-in/edit.php';
        require APP_PATH . '/views/layouts/app.php';
    }

    public function update() {
        $id = (int)get('id');
        $batch = $this->model->getById($id);
        if (!$batch) {
            setFlash('error', 'Barang masuk tidak ditemukan.');
            redirect('stock-in');
            return;
        }

        $isUsed = $this->model->isUsedInSale($id);

        if ($isUsed) {
            $productId = $batch['product_id'];
            $qtyMasuk = $batch['qty_masuk'];
        } else {
            $productResolution = $this->resolveProductSelection(
                post('product_id'),
                trim((string)($_POST['product_search'] ?? ''))
            );
            if ($productResolution['error']) {
                setFlash('error', $productResolution['error']);
                redirect('stock-in', ['action' => 'edit', 'id' => $id]);
                return;
            }
            $productId = $productResolution['id'];
            $qtyMasuk = post('qty_masuk');
        }

        $data = [
            'product_id' => $productId,
            'supplier_id' => post('supplier_id') !== '' ? (int)post('supplier_id') : null,
            'tanggal_masuk' => post('tanggal_masuk'),
            'qty_masuk' => $qtyMasuk,
            'harga_modal' => post('harga_modal'),
            'nomor_nota' => trim((string)post('nomor_nota')),
            'keterangan' => post('keterangan'),
            'nota_file' => null
        ];

        $v = new Validator($data);
        $v->required('product_id', 'Barang')->required('tanggal_masuk', 'Tanggal masuk')
          ->required('qty_masuk', 'Qty masuk')->numeric('qty_masuk', 'Qty masuk')->minValue('qty_masuk', 1, 'Qty masuk')
          ->required('harga_modal', 'Harga modal')->numeric('harga_modal', 'Harga modal')->minValue('harga_modal', 1, 'Harga modal');

        if ($v->hasErrors()) {
            setFlash('error', $v->getFirstError());
            redirect('stock-in', ['action' => 'edit', 'id' => $id]);
            return;
        }

        if ($data['supplier_id']) {
            $supplier = (new Customer())->getById($data['supplier_id']);
            if (!$supplier || ($supplier['tipe'] ?? 'customer') !== 'supplier') {
                setFlash('error', 'Supplier yang dipilih tidak valid.');
                redirect('stock-in', ['action' => 'edit', 'id' => $id]);
                return;
            }
        }

        if (isset($_FILES['nota_file']) && ($_FILES['nota_file']['error'] !== UPLOAD_ERR_NO_FILE)) {
            $notaFile = $this->uploadNota($_FILES['nota_file']);
            if ($notaFile === false) {
                redirect('stock-in', ['action' => 'edit', 'id' => $id]);
                return;
            }
            $data['nota_file'] = $notaFile;

            if (!empty($batch['nota_file'])) {
                $oldFilePath = STOCK_NOTE_PATH . '/' . $batch['nota_file'];
                if (file_exists($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }
        }

        try {
            $this->model->update($id, $data);
            setFlash('success', 'Barang masuk berhasil diperbarui.');
            redirect('stock-in');
        } catch (Exception $e) {
            if (!empty($data['nota_file']) && file_exists(STOCK_NOTE_PATH . '/' . $data['nota_file'])) {
                @unlink(STOCK_NOTE_PATH . '/' . $data['nota_file']);
            }
            setFlash('error', 'Gagal: ' . $e->getMessage());
            redirect('stock-in', ['action' => 'edit', 'id' => $id]);
        }
    }

    public function delete() {
        $id = (int)get('id');
        $batch = $this->model->getById($id);
        if (!$batch) {
            setFlash('error', 'Barang masuk tidak ditemukan.');
            redirect('stock-in');
            return;
        }

        try {
            $this->model->delete($id);
            setFlash('success', 'Catatan barang masuk berhasil dihapus.');
        } catch (Exception $e) {
            setFlash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
        redirect('stock-in');
    }
}
