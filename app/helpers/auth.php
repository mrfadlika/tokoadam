<?php
/**
 * Authentication & Authorization Helpers
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isStaff() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff';
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUserName() {
    return $_SESSION['user_name'] ?? 'User';
}

function currentUserRole() {
    return $_SESSION['user_role'] ?? '';
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Silakan login terlebih dahulu.');
        redirect('login');
    }
}

/**
 * Redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlash('error', 'Akses ditolak. Hanya admin yang bisa mengakses halaman ini.');
        redirect('dashboard');
    }
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Clear flash message without reading it
 */
function clearFlash() {
    unset($_SESSION['flash']);
}

/**
 * Redirect to a page
 */
function redirect($page = 'dashboard', $params = []) {
    $url = BASE_URL . '/index.php?page=' . $page;
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Set login session
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nama'];
    $_SESSION['user_username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
}

/**
 * Destroy session
 */
function logoutUser() {
    session_unset();
    session_destroy();
}
