<?php
class CustomerController {
    private $model;
    public function __construct() { $this->model = new Customer(); }
    
    public function index() {
        $search = get('search');
        $type = $this->sanitizeType(get('type'));
        $page = max(1, (int)get('p', 1));
        $filters = $type ? ['type' => $type] : [];
        $customers = $this->model->getAll($search, $page, PER_PAGE, $filters);
        $total = $this->model->count($search, $filters);
        $totalPages = ceil($total / PER_PAGE);
        $pageTitle = 'Master Pelanggan & Supplier'; $currentPage = 'customers';
        $content = APP_PATH . '/views/customers/index.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function create() {
        $pageTitle = 'Tambah Kontak'; $currentPage = 'customers';
        $content = APP_PATH . '/views/customers/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function store() {
        $data = [
            'tipe' => $this->sanitizeType(post('tipe')) ?? 'customer',
            'nama_toko' => post('nama_toko'),
            'nama_pic' => post('nama_pic'),
            'phone' => post('phone'),
            'alamat' => post('alamat'),
            'catatan' => post('catatan')
        ];
        $v = new Validator($data);
        $v->required('nama_toko', 'Nama');
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('customers', ['action' => 'create']); return; }
        $this->model->create($data);
        setFlash('success', $this->getTypeLabel($data['tipe']) . ' berhasil ditambahkan.');
        redirect('customers');
    }
    
    public function edit() {
        $id = (int)get('id');
        $customer = $this->model->getById($id);
        if (!$customer) { setFlash('error', 'Data tidak ditemukan.'); redirect('customers'); return; }
        $pageTitle = 'Edit Kontak'; $currentPage = 'customers';
        $content = APP_PATH . '/views/customers/edit.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function update() {
        $id = (int)get('id');
        $data = [
            'tipe' => $this->sanitizeType(post('tipe')) ?? 'customer',
            'nama_toko' => post('nama_toko'),
            'nama_pic' => post('nama_pic'),
            'phone' => post('phone'),
            'alamat' => post('alamat'),
            'catatan' => post('catatan')
        ];
        $v = new Validator($data);
        $v->required('nama_toko', 'Nama');
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('customers', ['action' => 'edit', 'id' => $id]); return; }
        $this->model->update($id, $data);
        setFlash('success', $this->getTypeLabel($data['tipe']) . ' berhasil diupdate.');
        redirect('customers');
    }
    
    public function delete() {
        $id = (int)get('id');
        try {
            $customer = $this->model->getById($id);
            $this->model->delete($id);
            $type = $customer['tipe'] ?? 'customer';
            setFlash('success', $this->getTypeLabel($type) . ' berhasil dihapus.');
        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
        }
        redirect('customers');
    }
    
    public function apiSearch() {
        header('Content-Type: application/json');
        echo json_encode($this->model->search(get('q'), get('type', 'customer')));
        exit;
    }

    private function sanitizeType($type) {
        return in_array($type, ['customer', 'supplier'], true) ? $type : null;
    }

    private function getTypeLabel($type) {
        return $type === 'supplier' ? 'Supplier' : 'Pelanggan';
    }
}
