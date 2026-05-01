<?php
class UserController {
    private $model;
    public function __construct() { $this->model = new User(); }
    
    public function index() {
        $users = $this->model->getAll();
        $pageTitle = 'Manajemen User'; $currentPage = 'users';
        $content = APP_PATH . '/views/users/index.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function create() {
        $pageTitle = 'Tambah User'; $currentPage = 'users';
        $content = APP_PATH . '/views/users/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function store() {
        $data = ['nama' => post('nama'), 'username' => post('username'), 'password' => $_POST['password'] ?? '', 'role' => post('role', 'staff')];
        $v = new Validator($data);
        $v->required('nama', 'Nama')->required('username', 'Username')->required('password', 'Password')
          ->unique('username', 'users', 'username', null, 'Username');
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('users', ['action' => 'create']); return; }
        $this->model->create($data);
        setFlash('success', 'User berhasil ditambahkan.');
        redirect('users');
    }
    
    public function edit() {
        $id = (int)get('id');
        $user = $this->model->getById($id);
        if (!$user) { setFlash('error', 'User tidak ditemukan.'); redirect('users'); return; }
        $pageTitle = 'Edit User'; $currentPage = 'users';
        $content = APP_PATH . '/views/users/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function update() {
        $id = (int)get('id');
        $data = ['nama' => post('nama'), 'username' => post('username'), 'password' => $_POST['password'] ?? '', 'role' => post('role', 'staff')];
        $v = new Validator($data);
        $v->required('nama', 'Nama')->required('username', 'Username')
          ->unique('username', 'users', 'username', $id, 'Username');
        if ($v->hasErrors()) { setFlash('error', $v->getFirstError()); redirect('users', ['action' => 'edit', 'id' => $id]); return; }
        $this->model->update($id, $data);
        setFlash('success', 'User berhasil diupdate.');
        redirect('users');
    }
    
    public function delete() {
        $id = (int)get('id');
        if ($id == currentUserId()) { setFlash('error', 'Tidak bisa menghapus akun sendiri.'); redirect('users'); return; }
        $this->model->delete($id);
        setFlash('success', 'User berhasil dinonaktifkan.');
        redirect('users');
    }
}
