<?php
/**
 * Toko Adam Web Admin - Front Controller
 */
session_start();

// Load configuration
require_once __DIR__ . '/app/config/app.php';
require_once __DIR__ . '/app/config/database.php';

// Load helpers
require_once __DIR__ . '/app/helpers/auth.php';
require_once __DIR__ . '/app/helpers/format.php';
require_once __DIR__ . '/app/helpers/validation.php';
require_once __DIR__ . '/app/helpers/invoice.php';
require_once __DIR__ . '/app/helpers/fifo.php';

// Load models
require_once __DIR__ . '/app/models/User.php';
require_once __DIR__ . '/app/models/Product.php';
require_once __DIR__ . '/app/models/Customer.php';
require_once __DIR__ . '/app/models/StockBatch.php';
require_once __DIR__ . '/app/models/Sale.php';
require_once __DIR__ . '/app/models/Report.php';

// Get current page
$page = get('page', 'dashboard');
$action = get('action', 'index');

// Public pages (no auth required)
$publicPages = ['login', 'setup'];

// Route the request
if (in_array($page, $publicPages)) {
    require_once __DIR__ . '/app/controllers/AuthController.php';
    $controller = new AuthController();
    
    if ($page === 'login') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->doLogin();
        } else {
            $controller->showLogin();
        }
    }
} else {
    // Require authentication
    requireLogin();
    
    switch ($page) {
        case 'dashboard':
            require_once __DIR__ . '/app/controllers/DashboardController.php';
            $controller = new DashboardController();
            $controller->index();
            break;
            
        case 'products':
            require_once __DIR__ . '/app/controllers/ProductController.php';
            $controller = new ProductController();
            switch ($action) {
                case 'create': $controller->create(); break;
                case 'store': $controller->store(); break;
                case 'edit': $controller->edit(); break;
                case 'update': $controller->update(); break;
                case 'delete': $controller->delete(); break;
                case 'api_search': $controller->apiSearch(); break;
                case 'api_detail': $controller->apiDetail(); break;
                default: $controller->index(); break;
            }
            break;
            
        case 'customers':
            require_once __DIR__ . '/app/controllers/CustomerController.php';
            $controller = new CustomerController();
            switch ($action) {
                case 'create': $controller->create(); break;
                case 'store': $controller->store(); break;
                case 'edit': $controller->edit(); break;
                case 'update': $controller->update(); break;
                case 'delete': $controller->delete(); break;
                case 'api_search': $controller->apiSearch(); break;
                default: $controller->index(); break;
            }
            break;
            
        case 'stock-in':
            require_once __DIR__ . '/app/controllers/StockInController.php';
            $controller = new StockInController();
            switch ($action) {
                case 'create': $controller->create(); break;
                case 'store': $controller->store(); break;
                case 'edit': $controller->edit(); break;
                case 'update': $controller->update(); break;
                case 'delete': $controller->delete(); break;
                default: $controller->index(); break;
            }
            break;
            
        case 'sales':
            require_once __DIR__ . '/app/controllers/SalesController.php';
            $controller = new SalesController();
            switch ($action) {
                case 'create': $controller->create(); break;
                case 'store': $controller->store(); break;
                case 'invoice': $controller->invoice(); break;
                case 'save_invoice': $controller->saveInvoice(); break;
                case 'update_status': $controller->updateStatus(); break;
                case 'delete': $controller->delete(); break;
                default: $controller->index(); break;
            }
            break;
            
        case 'reports':
            require_once __DIR__ . '/app/controllers/ReportController.php';
            $controller = new ReportController();
            switch ($action) {
                case 'stock': $controller->stock(); break;
                case 'stock_detail': $controller->stockDetail(); break;
                case 'financial': $controller->financial(); break;
                default: $controller->sales(); break;
            }
            break;
            
        case 'users':
            requireAdmin();
            require_once __DIR__ . '/app/controllers/UserController.php';
            $controller = new UserController();
            switch ($action) {
                case 'create': $controller->create(); break;
                case 'store': $controller->store(); break;
                case 'edit': $controller->edit(); break;
                case 'update': $controller->update(); break;
                case 'delete': $controller->delete(); break;
                default: $controller->index(); break;
            }
            break;
            
        case 'logout':
            logoutUser();
            header('Location: ' . BASE_URL . '/index.php?page=login');
            exit;
            
        default:
            redirect('dashboard');
            break;
    }
}
