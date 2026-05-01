<?php
class CustomerController {
    private $model;
    public function __construct() { $this->model = new Customer(); }
    
    public function index() {
        $search = get('search');
        $page = max(1, (int)get('p', 1));
        $customers = $this->model->getAll($search, $page);
        $total = $this->model->count($search);
        $totalPages = ceil($total / PER_PAGE);
        $pageTitle = 'Master Toko / Pelanggan'; $currentPage = 'customers';
        $content = APP_PATH . '/views/customers/index.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function create() {
        $pageTitle = 'Tambah Toko'; $currentPage = 'customers';
        $content = APP_PATH . '/views/customers/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function store() {
        $data = ['nama_toko' => post('nama_toko'), 'nama_pic' => post('nama_pic'), 'phone' => post('phone'), 'alamat' => post('alamat'), 'catatan' => post('catatan')];
        $v = new Validator($data);
        $v->required('nama_toko', 'Nama toko');
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('customers', ['action' => 'create']); return; }
        $this->model->create($data);
        setFlash('success', 'Toko berhasil ditambahkan.');
        redirect('customers');
    }
    
    public function edit() {
        $id = (int)get('id');
        $customer = $this->model->getById($id);
        if (!$customer) { setFlash('error', 'Toko tidak ditemukan.'); redirect('customers'); return; }
        $pageTitle = 'Edit Toko'; $currentPage = 'customers';
        $content = APP_PATH . '/views/customers/edit.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function update() {
        $id = (int)get('id');
        $data = ['nama_toko' => post('nama_toko'), 'nama_pic' => post('nama_pic'), 'phone' => post('phone'), 'alamat' => post('alamat'), 'catatan' => post('catatan')];
        $v = new Validator($data);
        $v->required('nama_toko', 'Nama toko');
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('customers', ['action' => 'edit', 'id' => $id]); return; }
        $this->model->update($id, $data);
        setFlash('success', 'Toko berhasil diupdate.');
        redirect('customers');
    }
    
    public function delete() {
        $id = (int)get('id');
        try {
            $this->model->delete($id);
            setFlash('success', 'Toko berhasil dihapus.');
        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
        }
        redirect('customers');
    }
    
    public function apiSearch() {
        header('Content-Type: application/json');
        echo json_encode($this->model->search(get('q')));
        exit;
    }
}
