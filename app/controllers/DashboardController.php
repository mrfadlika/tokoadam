<?php
class DashboardController {
    public function index() {
        $productModel = new Product();
        $saleModel = new Sale();
        $customerModel = new Customer();
        
        $data = [
            'totalProducts' => $productModel->getTotalCount(),
            'lowStock' => $productModel->getLowStock(),
            'todayTransactions' => $saleModel->getTodayCount(),
            'todayRevenue' => $saleModel->getTodayTotal(),
            'recentSales' => $saleModel->getRecent(5),
            'topCustomers' => $customerModel->getTopCustomers(5),
        ];
        
        $pageTitle = 'Dashboard';
        $currentPage = 'dashboard';
        $content = APP_PATH . '/views/dashboard/index.php';
        require APP_PATH . '/views/layouts/app.php';
    }
}
