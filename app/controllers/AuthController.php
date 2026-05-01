<?php
class AuthController {
    public function showLogin() {
        if (isLoggedIn()) { redirect('dashboard'); }
        require APP_PATH . '/views/auth/login.php';
    }
    
    public function doLogin() {
        $username = post('username');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
            require APP_PATH . '/views/auth/login.php';
            return;
        }
        
        $userModel = new User();
        $user = $userModel->findByUsername($username);
        
        if (!$user || !$userModel->verifyPassword($user, $password)) {
            $error = 'Username atau password salah.';
            require APP_PATH . '/views/auth/login.php';
            return;
        }
        
        clearFlash();
        loginUser($user);
        redirect('dashboard');
    }
}
