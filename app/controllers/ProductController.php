<?php
class ProductController {
    private $model;
    public function __construct() { $this->model = new Product(); }
    
    public function index() {
        $search = get('search');
        $page = max(1, (int)get('p', 1));
        $products = $this->model->getAll($search, $page);
        $total = $this->model->count($search);
        $totalPages = ceil($total / PER_PAGE);
        $pageTitle = 'Master Barang'; $currentPage = 'products';
        $content = APP_PATH . '/views/products/index.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function create() {
        $categories = $this->model->getCategories();
        $units = $this->model->getUnits();
        $pageTitle = 'Tambah Barang'; $currentPage = 'products';
        $content = APP_PATH . '/views/products/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function store() {
        $data = ['kode_barang' => post('kode_barang'), 'nama_barang' => post('nama_barang'), 'kategori' => post('kategori'), 'satuan' => post('satuan'), 'harga_jual' => post('harga_jual', 0), 'stok_minimum' => post('stok_minimum', 10)];
        
        $v = new Validator($data);
        $v->required('kode_barang', 'Kode barang')->required('nama_barang', 'Nama barang')->required('satuan', 'Satuan')
          ->unique('kode_barang', 'products', 'kode_barang', null, 'Kode barang');
        
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('products', ['action' => 'create']); return; }
        
        // Handle photo upload
        if (!empty($_FILES['foto']['name'])) {
            $foto = $this->uploadPhoto($_FILES['foto']);
            if ($foto) $data['foto'] = $foto;
        }
        
        $this->model->create($data);
        setFlash('success', 'Barang berhasil ditambahkan.');
        redirect('products');
    }
    
    public function edit() {
        $id = (int)get('id');
        $product = $this->model->getById($id);
        if (!$product) { setFlash('error', 'Barang tidak ditemukan.'); redirect('products'); return; }
        $categories = $this->model->getCategories();
        $units = $this->model->getUnits();
        $batches = getStockBreakdown($id);
        $pageTitle = 'Edit Barang'; $currentPage = 'products';
        $content = APP_PATH . '/views/products/edit.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function update() {
        $id = (int)get('id');
        $data = ['kode_barang' => post('kode_barang'), 'nama_barang' => post('nama_barang'), 'kategori' => post('kategori'), 'satuan' => post('satuan'), 'harga_jual' => post('harga_jual', 0), 'stok_minimum' => post('stok_minimum', 10)];
        
        $v = new Validator($data);
        $v->required('kode_barang', 'Kode barang')->required('nama_barang', 'Nama barang')
          ->unique('kode_barang', 'products', 'kode_barang', $id, 'Kode barang');
        
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('products', ['action' => 'edit', 'id' => $id]); return; }
        
        if (!empty($_FILES['foto']['name'])) {
            $foto = $this->uploadPhoto($_FILES['foto']);
            if ($foto) $data['foto'] = $foto;
        }
        
        $this->model->update($id, $data);
        setFlash('success', 'Barang berhasil diupdate.');
        redirect('products');
    }
    
    public function delete() {
        $id = (int)get('id');
        $this->model->delete($id);
        setFlash('success', 'Barang berhasil dinonaktifkan.');
        redirect('products');
    }
    
    public function apiSearch() {
        header('Content-Type: application/json');
        $q = get('q');
        echo json_encode($this->model->search($q));
        exit;
    }
    
    public function apiDetail() {
        header('Content-Type: application/json');
        $id = (int)get('id');
        $p = $this->model->getById($id);
        echo json_encode($p ?: ['error' => 'Not found']);
        exit;
    }
    
    private function uploadPhoto($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > MAX_UPLOAD_SIZE) { setFlash('error', 'Ukuran foto maks 2MB.'); return null; }
        if (!in_array($file['type'], ALLOWED_IMG_TYPES)) { setFlash('error', 'Format foto harus JPG, PNG, atau WebP.'); return null; }
        if (!is_dir(PRODUCT_IMG_PATH)) mkdir(PRODUCT_IMG_PATH, 0755, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], PRODUCT_IMG_PATH . '/' . $filename);
        return $filename;
    }
}
