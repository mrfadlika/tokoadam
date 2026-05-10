<?php
class SalesController {
    private $model;
    public function __construct() { $this->model = new Sale(); }
    
    public function index() {
        $filters = ['customer_id' => get('customer_id'), 'date_from' => get('date_from'), 'date_to' => get('date_to'), 'search' => get('search'), 'page' => max(1, (int)get('p', 1))];
        $sales = $this->model->getAll($filters);
        $total = $this->model->count($filters);
        $totalPages = ceil($total / PER_PAGE);
        $page = $filters['page'];
        $customerModel = new Customer();
        $customers = $customerModel->getAll('', 1, 1000, ['type' => 'customer']);
        $pageTitle = 'Riwayat Penjualan'; $currentPage = 'sales';
        $action = 'index';
        $content = APP_PATH . '/views/sales/index.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function create() {
        $pageTitle = 'Transaksi Penjualan'; $currentPage = 'sales';
        $action = 'create';
        $content = APP_PATH . '/views/sales/create.php';
        require APP_PATH . '/views/layouts/app.php';
    }
    
    public function store() {
        $customerid = post('customer_id');
        $tanggal = post('tanggal_transaksi');
        $catatan = post('catatan');
        $productIds = $_POST['product_id'] ?? [];
        $qtys = $_POST['qty'] ?? [];
        $hargaJuals = $_POST['harga_jual'] ?? [];
        
        if (empty($customerid) || empty($tanggal)) { setFlash('error', 'Pelanggan dan tanggal wajib diisi.'); redirect('sales', ['action' => 'create']); return; }
        if (empty($productIds)) { setFlash('error', 'Tambahkan minimal 1 item.'); redirect('sales', ['action' => 'create']); return; }

        $customer = (new Customer())->getById((int)$customerid);
        if (!$customer || ($customer['tipe'] ?? 'customer') !== 'customer') {
            setFlash('error', 'Pelanggan yang dipilih tidak valid.');
            redirect('sales', ['action' => 'create']);
            return;
        }
        
        $items = [];
        for ($i = 0; $i < count($productIds); $i++) {
            if (empty($productIds[$i]) || empty($qtys[$i]) || empty($hargaJuals[$i])) continue;
            $items[] = ['product_id' => (int)$productIds[$i], 'qty' => (int)$qtys[$i], 'harga_jual' => (float)$hargaJuals[$i]];
        }
        
        if (empty($items)) { setFlash('error', 'Tambahkan minimal 1 item yang valid.'); redirect('sales', ['action' => 'create']); return; }
        
        try {
            $saleId = $this->model->create(['customer_id' => $customerid, 'tanggal_transaksi' => $tanggal, 'catatan' => $catatan, 'created_by' => currentUserId()], $items);
            setFlash('success', 'Transaksi berhasil disimpan.');
            redirect('sales', ['action' => 'invoice', 'id' => $saleId]);
        } catch (Exception $e) {
            setFlash('error', 'Gagal: ' . $e->getMessage());
            redirect('sales', ['action' => 'create']);
        }
    }
    
    public function invoice() {
        $id = (int)get('id');
        $sale = $this->model->getById($id);
        if (!$sale) { setFlash('error', 'Transaksi tidak ditemukan.'); redirect('sales'); return; }
        $items = $this->model->getItems($id);
        $invoiceView = $this->buildInvoiceView($sale);
        $pageTitle = 'Nota #' . $sale['nomor_transaksi'];
        $action = 'index';
        require APP_PATH . '/views/sales/invoice.php';
    }

    public function saveInvoice() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->jsonResponse(['ok' => false, 'message' => 'Metode request tidak didukung.'], 405);
        }

        $id = isset($_POST['sale_id']) ? (int)$_POST['sale_id'] : 0;
        $sale = $this->model->getById($id);
        if (!$sale) {
            $this->jsonResponse(['ok' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        $invoiceNo = trim((string)($_POST['nomor_transaksi'] ?? ''));
        $headerTop = trim((string)($_POST['header_top'] ?? ''));
        $companyName = trim((string)($_POST['company_name'] ?? ''));
        $companyAddress = trim((string)($_POST['company_address'] ?? ''));
        $companyPhone = trim((string)($_POST['company_phone'] ?? ''));
        $invoiceTitle = trim((string)($_POST['invoice_title'] ?? ''));
        $tanggalLabel = trim((string)($_POST['tanggal_label'] ?? ''));
        $customerName = trim((string)($_POST['nama_pelanggan'] ?? ''));
        $customerAddress = trim((string)($_POST['alamat_pelanggan'] ?? ''));
        $signerName = trim((string)($_POST['signer_name'] ?? ''));
        $signerRole = trim((string)($_POST['signer_role'] ?? ''));

        if ($invoiceNo === '') {
            $this->jsonResponse(['ok' => false, 'message' => 'Nomor nota wajib diisi.'], 422);
        }
        if (strlen($invoiceNo) > 30) {
            $this->jsonResponse(['ok' => false, 'message' => 'Nomor nota maksimal 30 karakter.'], 422);
        }
        if ($this->model->invoiceNumberExists($invoiceNo, $id)) {
            $this->jsonResponse(['ok' => false, 'message' => 'Nomor nota sudah dipakai transaksi lain.'], 422);
        }

        $limits = [
            'header_top' => [$headerTop, 120, 'Baris kop atas'],
            'company_name' => [$companyName, 150, 'Nama toko/perusahaan'],
            'company_address' => [$companyAddress, 500, 'Alamat toko/perusahaan'],
            'company_phone' => [$companyPhone, 80, 'Nomor telepon'],
            'invoice_title' => [$invoiceTitle, 120, 'Judul nota'],
            'tanggal_label' => [$tanggalLabel, 120, 'Label tanggal'],
            'nama_pelanggan' => [$customerName, 150, 'Nama pelanggan'],
            'alamat_pelanggan' => [$customerAddress, 500, 'Alamat pelanggan'],
            'signer_name' => [$signerName, 100, 'Nama penanda tangan'],
            'signer_role' => [$signerRole, 100, 'Jabatan penanda tangan'],
        ];
        foreach ($limits as [$value, $max, $label]) {
            if (strlen($value) > $max) {
                $this->jsonResponse(['ok' => false, 'message' => $label . ' maksimal ' . $max . ' karakter.'], 422);
            }
        }

        $overrides = [
            'header_top' => $headerTop,
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'company_phone' => $companyPhone,
            'invoice_title' => $invoiceTitle,
            'tanggal_label' => $tanggalLabel,
            'nama_pelanggan' => $customerName,
            'alamat_pelanggan' => $customerAddress,
            'signer_name' => $signerName,
            'signer_role' => $signerRole,
        ];

        try {
            $this->model->updateInvoicePresentation($id, $invoiceNo, $overrides);
        } catch (Exception $e) {
            $this->jsonResponse(['ok' => false, 'message' => 'Perubahan nota gagal disimpan.'], 500);
        }

        $updatedSale = $this->model->getById($id);
        $invoiceView = $this->buildInvoiceView($updatedSale);
        $this->jsonResponse([
            'ok' => true,
            'message' => 'Isi nota berhasil diperbarui.',
            'invoice' => $invoiceView,
        ]);
    }

    private function buildInvoiceView(array $sale): array {
        $overrides = [];
        if (!empty($sale['invoice_overrides'])) {
            $decoded = json_decode((string)$sale['invoice_overrides'], true);
            if (is_array($decoded)) {
                $overrides = $decoded;
            }
        }

        // For presentation fields (not per-transaction), inherit from the latest edited invoice
        $latestOverrides = [];
        if (empty($overrides)) {
            $latestOverrides = $this->model->getLatestOverrides();
        }

        // Helper: resolve value with priority: own overrides → latest overrides → config default
        $resolve = function(string $key, string $configDefault) use ($overrides, $latestOverrides): string {
            if (array_key_exists($key, $overrides)) {
                return (string)$overrides[$key];
            }
            if (array_key_exists($key, $latestOverrides)) {
                return (string)$latestOverrides[$key];
            }
            return $configDefault;
        };

        $defaultSignerName = COMPANY_SIGNER_NAME !== '' ? COMPANY_SIGNER_NAME : '';
        $defaultSignerRole = COMPANY_SIGNER_ROLE !== '' ? '(' . COMPANY_SIGNER_ROLE . ')' : '';

        return [
            'sale_id' => (int)$sale['id'],
            'nomor_transaksi' => $sale['nomor_transaksi'],
            'header_top' => $resolve('header_top', defined('COMPANY_HEADER_TOP') ? (string)COMPANY_HEADER_TOP : ''),
            'company_name' => $resolve('company_name', defined('APP_NAME') ? (string)APP_NAME : ''),
            'company_address' => $resolve('company_address', defined('COMPANY_ADDRESS') ? (string)COMPANY_ADDRESS : ''),
            'company_phone' => $resolve('company_phone', defined('COMPANY_PHONE') ? (string)COMPANY_PHONE : ''),
            'invoice_title' => $resolve('invoice_title', 'Nota Penjualan'),
            // Per-transaction fields: always from this sale's own data/overrides (not inherited)
            'tanggal_label' => array_key_exists('tanggal_label', $overrides) ? (string)$overrides['tanggal_label'] : formatDateWithDay($sale['tanggal_transaksi']),
            'nama_pelanggan' => array_key_exists('nama_pelanggan', $overrides) ? (string)$overrides['nama_pelanggan'] : (string)$sale['nama_toko'],
            'alamat_pelanggan' => array_key_exists('alamat_pelanggan', $overrides) ? (string)$overrides['alamat_pelanggan'] : (string)($sale['alamat_toko'] ?? ''),
            'signer_name' => $resolve('signer_name', $defaultSignerName),
            'signer_role' => $resolve('signer_role', $defaultSignerRole),
        ];
    }

    private function jsonResponse(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
