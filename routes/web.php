<?php

use App\Http\Controllers\Apps\CategoryController;
use App\Http\Controllers\Apps\CustomerController;
use App\Http\Controllers\Apps\CustomerAddressController;
use App\Http\Controllers\Apps\BankAccountController;
use App\Http\Controllers\Apps\MasterAddressController;
use App\Http\Controllers\Apps\ShippingMethodController;
use App\Http\Controllers\Apps\PaymentSettingController;
use App\Http\Controllers\Apps\ProductController;
use App\Http\Controllers\Apps\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\ProfitReportController;
use App\Http\Controllers\Reports\SalesReportController;
use App\Http\Controllers\Apps\ReportStockController;
use App\Http\Controllers\Apps\StockAdjustmentController;
use App\Http\Controllers\Apps\StockAdjustmentReportController;
use App\Http\Controllers\Apps\StockOpnameController;
use App\Http\Controllers\Apps\SupplierController;
use App\Http\Controllers\Apps\PurchaseController;
use App\Http\Controllers\Apps\PurchaseReturnController;
use App\Http\Controllers\Apps\ProductBundleController;
use App\Http\Controllers\Apps\SaleController;
use App\Http\Controllers\Apps\SaleApprovalController;
use App\Http\Controllers\Apps\SaleReturnController;
use App\Http\Controllers\Apps\WarehouseController;
use App\Http\Controllers\Apps\WarehouseStockController;
use App\Http\Controllers\Apps\MinimumStockReportController;
use App\Http\Controllers\Apps\FastMovingReportController;
use App\Http\Controllers\Apps\SlowMovingReportController;
use App\Http\Controllers\Apps\AccountController;
use App\Http\Controllers\Apps\AccountSettingController;
use App\Http\Controllers\Apps\JournalController;
use App\Http\Controllers\Apps\ProfitLossReportController;
use App\Http\Controllers\Apps\GeneralLedgerController;
use App\Http\Controllers\Apps\BalanceSheetController;
use App\Http\Controllers\Apps\CashFlowController;
use App\Http\Controllers\Apps\CashTransactionController;
use App\Http\Controllers\Apps\PettyCashController;
use App\Http\Controllers\Apps\ReceivableController;
use App\Http\Controllers\Apps\PayableController;
use App\Http\Controllers\Apps\FixedAssetController;
use App\Http\Controllers\Apps\TaxSettingController;
use App\Http\Controllers\Apps\TaxController;
use App\Http\Controllers\Apps\TaxReportController;
use App\Http\Controllers\Apps\UnitController;
use App\Http\Controllers\Apps\StockPenyesuaianController;
use App\Http\Controllers\Apps\ZeroValueTransactionController;
use App\Http\Controllers\Apps\OldOrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

// ============================================================
// TEMPORARY: Route untuk menjalankan migrasi tanpa terminal
// Akses via browser: https://yourdomain.com/run-migrate/your-secret-token-here
// HAPUS ROUTE INI SETELAH MIGRASI SELESAI!
// ============================================================
Route::get('/run-migrate/{token}', function ($token) {
    // Ganti token ini dengan string rahasia Anda sendiri
    $secretToken = 'erp-migrate-2026-secret';

    if ($token !== $secretToken) {
        abort(404);
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();

        return '<pre style="background:#1a1a2e;color:#0f0;padding:20px;font-family:monospace;">'
            . '✅ Migrasi berhasil dijalankan!' . PHP_EOL . PHP_EOL
            . htmlspecialchars($output)
            . '</pre>';
    } catch (\Exception $e) {
        return '<pre style="background:#1a1a2e;color:#f00;padding:20px;font-family:monospace;">'
            . '❌ Migrasi gagal!' . PHP_EOL . PHP_EOL
            . htmlspecialchars($e->getMessage())
            . '</pre>';
    }
});

Route::group(['prefix' => 'dashboard', 'middleware' => ['auth']], function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'permission:dashboard-access'])->name('dashboard');
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permissions-access')->name('permissions.index');
    // roles route
    Route::resource('/roles', RoleController::class)
        ->except(['create', 'edit', 'show'])
        ->middlewareFor('index', 'permission:roles-access')
        ->middlewareFor('store', 'permission:roles-create')
        ->middlewareFor('update', 'permission:roles-update')
        ->middlewareFor('destroy', 'permission:roles-delete');
    // users route
    Route::resource('/users', UserController::class)
        ->except('show')
        ->middlewareFor('index', 'permission:users-access')
        ->middlewareFor(['create', 'store'], 'permission:users-create')
        ->middlewareFor(['edit', 'update'], 'permission:users-update')
        ->middlewareFor('destroy', 'permission:users-delete');

    Route::resource('categories', CategoryController::class)
        ->middlewareFor(['index', 'show'], 'permission:categories-access')
        ->middlewareFor(['create', 'store'], 'permission:categories-create')
        ->middlewareFor(['edit', 'update'], 'permission:categories-edit')
        ->middlewareFor('destroy', 'permission:categories-delete');

    Route::post('warehouses/{warehouse}/sync', [WarehouseController::class, 'syncProducts'])->name('warehouses.sync');
    Route::resource('warehouses', WarehouseController::class)
        ->middleware(['permission:products-access']); // Using products permission

    Route::resource('products', ProductController::class)
        ->middlewareFor(['index', 'show'], 'permission:products-access')
        ->middlewareFor(['create', 'store'], 'permission:products-create')
        ->middlewareFor(['edit', 'update'], 'permission:products-edit')
        ->middlewareFor('destroy', 'permission:products-delete');
    Route::post('products/{product}/sync-units', [ProductController::class, 'syncUnits'])
        ->middleware(['permission:products-edit'])
        ->name('products.sync-units');
    Route::resource('units', UnitController::class)
        ->middleware(['permission:products-access']);
    Route::get('api/units', [UnitController::class, 'list'])->name('api.units.list');
    Route::resource('customers', CustomerController::class)
        ->middlewareFor(['index', 'show'], 'permission:customers-access')
        ->middlewareFor('edit', 'permission:customers-edit')
        ->middlewareFor('update', 'permission:customers-edit')
        ->middlewareFor('destroy', 'permission:customers-delete');

    Route::resource('customer-addresses', CustomerAddressController::class)
        ->middleware(['permission:customers-access']);

    Route::resource('suppliers', SupplierController::class)
        ->middleware(['permission:products-access']); // Using products permission for now to simplify

    Route::post('/product-bundles/searchProduct', [ProductBundleController::class, 'searchProduct'])->name('product-bundles.searchProduct');
    Route::post('/product-bundles/{id}/checkStock', [ProductBundleController::class, 'checkStock'])->name('product-bundles.checkStock');
    Route::resource('product-bundles', ProductBundleController::class)
        ->middleware(['permission:products-access']);

    Route::post('/purchases/searchProduct', [PurchaseController::class, 'searchProduct'])->name('purchases.searchProduct');
    Route::post('/purchases/{id}/finalize', [PurchaseController::class, 'finalize'])->name('purchases.finalize');
    Route::get('/purchases/export/excel', [PurchaseController::class, 'exportExcel'])->name('purchases.export.excel');
    Route::get('/purchases/export/pdf', [PurchaseController::class, 'exportPdf'])->name('purchases.export.pdf');
    Route::resource('purchases', PurchaseController::class)
        ->middleware(['permission:products-access']);

    Route::post('/purchase-returns/searchProduct', [PurchaseReturnController::class, 'searchProduct'])->name('purchase-returns.searchProduct');
    Route::post('/purchase-returns/{id}/finalize', [PurchaseReturnController::class, 'finalize'])->name('purchase-returns.finalize');
    Route::resource('purchase-returns', PurchaseReturnController::class)
        ->middleware(['permission:products-access']);

    // Stock Adjustment (Penyesuaian Stok)
    Route::post('/stock-adjustments/searchProduct', [StockAdjustmentController::class, 'searchProduct'])->name('stock-adjustments.searchProduct');
    Route::post('/stock-adjustments/{id}/finalize', [StockAdjustmentController::class, 'finalize'])->name('stock-adjustments.finalize');
    Route::resource('stock-adjustments', StockAdjustmentController::class)
        ->middleware(['permission:products-access']);

    // Stock Opname
    Route::post('/stock-opnames/searchProduct', [StockOpnameController::class, 'searchProduct'])->name('stock-opnames.searchProduct');
    Route::post('/stock-opnames/getProductStock', [StockOpnameController::class, 'getProductStock'])->name('stock-opnames.getProductStock');
    Route::post('/stock-opnames/getWarehouseProducts', [StockOpnameController::class, 'getWarehouseProducts'])->name('stock-opnames.getWarehouseProducts');
    Route::post('/stock-opnames/{id}/finalize', [StockOpnameController::class, 'finalize'])->name('stock-opnames.finalize');
    Route::resource('stock-opnames', StockOpnameController::class)
        ->middleware(['permission:products-access']);

    // Stock Penyesuaian (with accounting journal)
    Route::post('/stock-penyesuaian/searchProduct', [StockPenyesuaianController::class, 'searchProduct'])->name('stock-penyesuaian.searchProduct');
    Route::post('/stock-penyesuaian/{id}/finalize', [StockPenyesuaianController::class, 'finalize'])->name('stock-penyesuaian.finalize');
    Route::resource('stock-penyesuaian', StockPenyesuaianController::class)
        ->middleware(['permission:products-access']);

    // Zero-Value Transaction (Barang Rusak, Expired, Bonus, dll)
    Route::post('/zero-value-transactions/searchProduct', [ZeroValueTransactionController::class, 'searchProduct'])->name('zero-value-transactions.searchProduct');
    Route::post('/zero-value-transactions/{id}/finalize', [ZeroValueTransactionController::class, 'finalize'])->name('zero-value-transactions.finalize');
    Route::resource('zero-value-transactions', ZeroValueTransactionController::class)
        ->middleware(['permission:products-access']);

    Route::post('/sale-returns/searchProduct', [SaleReturnController::class, 'searchProduct'])->name('sale-returns.searchProduct');
    Route::post('/sale-returns/{id}/finalize', [SaleReturnController::class, 'finalize'])->name('sale-returns.finalize');
    Route::resource('sale-returns', SaleReturnController::class)
        ->middleware(['permission:transactions-access']);

    Route::post('/sales/searchProduct', [SaleController::class, 'searchProduct'])->name('sales.searchProduct');
    Route::post('/sales/{id}/finalize', [SaleController::class, 'finalize'])->name('sales.finalize');
    Route::get('/sales/import/template', [SaleController::class, 'downloadTemplate'])->name('sales.import.template');
    Route::post('/sales/import', [SaleController::class, 'import'])->name('sales.import');
    Route::get('/sales/import/{id}/errors', [SaleController::class, 'importErrors'])->name('sales.import.errors');
    Route::get('/sales/import/{id}/show', [SaleController::class, 'importShow'])->name('sales.import.show');
    Route::post('/sales/import/{id}/finalize', [SaleController::class, 'finalizeImport'])->name('sales.import.finalize');
    Route::resource('sales', SaleController::class)
        ->middleware(['permission:transactions-access']);

    // Old Orders (Legacy System)
    Route::get('/old-orders', [OldOrderController::class, 'index'])->middleware('permission:transactions-access')->name('old-orders.index');
    Route::get('/old-orders/resume', [OldOrderController::class, 'resume'])->middleware('permission:transactions-access')->name('old-orders.resume');
    Route::get('/old-orders/resume-report', [OldOrderController::class, 'resumeReport'])->middleware('permission:transactions-access')->name('old-orders.resume-report');
    Route::get('/old-orders/resume-report-detail', [OldOrderController::class, 'resumeReportDetail'])->middleware('permission:transactions-access')->name('old-orders.resume-report-detail');
    Route::get('/old-orders/resume/{year}/{month}', [OldOrderController::class, 'resumeDetail'])->middleware('permission:transactions-access')->name('old-orders.resume-detail');
    Route::put('/old-orders/{id}/toggle-status', [OldOrderController::class, 'toggleResumeStatus'])->middleware('permission:transactions-access')->name('old-orders.toggle-status');
    Route::get('/old-orders/resume/{year}/{month}/export-excel', [OldOrderController::class, 'exportResumeExcel'])->middleware('permission:transactions-access')->name('old-orders.resume-export-excel');
    Route::get('/old-orders/product-resume', [OldOrderController::class, 'productResume'])->middleware('permission:transactions-access')->name('old-orders.product-resume');
    Route::get('/old-orders/product-resume/export-excel', [OldOrderController::class, 'exportProductResumeExcel'])->middleware('permission:transactions-access')->name('old-orders.product-resume-export-excel');
    Route::get('/old-orders/resume-report/export-excel', [OldOrderController::class, 'exportResumeReportExcel'])->middleware('permission:transactions-access')->name('old-orders.resume-report-export-excel');
    Route::get('/old-orders/by-date', [OldOrderController::class, 'ordersByDate'])->middleware('permission:transactions-access')->name('old-orders.by-date');
    Route::post('/old-orders/bulk-print', [OldOrderController::class, 'bulkPrint'])->middleware('permission:transactions-access')->name('old-orders.bulk-print');
    Route::get('/old-orders/{id}/print', [OldOrderController::class, 'print'])->middleware('permission:transactions-access')->name('old-orders.print');
    Route::get('/old-orders/{id}', [OldOrderController::class, 'show'])->middleware('permission:transactions-access')->name('old-orders.show');

    // Sale Approval Routes
    Route::get('/approvals/finance', [SaleApprovalController::class, 'financeIndex'])
        ->middleware('permission:sales-approve-finance')
        ->name('approvals.finance.index');
    Route::get('/approvals/finance/history', [SaleApprovalController::class, 'financeHistory'])
        ->middleware('permission:sales-approve-finance')
        ->name('approvals.finance.history');
    Route::post('/approvals/finance/{id}/approve', [SaleApprovalController::class, 'financeApprove'])
        ->middleware('permission:sales-approve-finance')
        ->name('approvals.finance.approve');
    Route::post('/approvals/finance/{id}/reject', [SaleApprovalController::class, 'financeReject'])
        ->middleware('permission:sales-approve-finance')
        ->name('approvals.finance.reject');

    Route::get('/approvals/warehouse', [SaleApprovalController::class, 'warehouseIndex'])
        ->middleware('permission:sales-approve-warehouse')
        ->name('approvals.warehouse.index');
    Route::get('/approvals/warehouse/history', [SaleApprovalController::class, 'warehouseHistory'])
        ->middleware('permission:sales-approve-warehouse')
        ->name('approvals.warehouse.history');
    Route::post('/approvals/warehouse/{id}/approve', [SaleApprovalController::class, 'warehouseApprove'])
        ->middleware('permission:sales-approve-warehouse')
        ->name('approvals.warehouse.approve');
    Route::post('/approvals/warehouse/{id}/reject', [SaleApprovalController::class, 'warehouseReject'])
        ->middleware('permission:sales-approve-warehouse')
        ->name('approvals.warehouse.reject');

    Route::get('/approvals/{id}/show', [SaleApprovalController::class, 'show'])
        ->middleware('permission:sales-approval-access')
        ->name('approvals.show');

    //route customer history
    Route::get('/customers/{customer}/history', [CustomerController::class, 'getHistory'])->middleware('permission:transactions-access')->name('customers.history');

    //route customer store via AJAX (no redirect)
    Route::post('/customers/store-ajax', [CustomerController::class, 'storeAjax'])->middleware('permission:customers-create')->name('customers.storeAjax');

    //route transaction
    Route::get('/transactions', [TransactionController::class, 'index'])->middleware('permission:transactions-access')->name('transactions.index');

    //route transaction searchProduct
    Route::post('/transactions/searchProduct', [TransactionController::class, 'searchProduct'])->middleware('permission:transactions-access')->name('transactions.searchProduct');

    //route transaction addToCart
    Route::post('/transactions/addToCart', [TransactionController::class, 'addToCart'])->middleware('permission:transactions-access')->name('transactions.addToCart');

    //route transaction destroyCart
    Route::delete('/transactions/{cart_id}/destroyCart', [TransactionController::class, 'destroyCart'])->middleware('permission:transactions-access')->name('transactions.destroyCart');

    //route transaction updateCart
    Route::patch('/transactions/{cart_id}/updateCart', [TransactionController::class, 'updateCart'])->middleware('permission:transactions-access')->name('transactions.updateCart');

    //route hold transaction
    Route::post('/transactions/hold', [TransactionController::class, 'holdCart'])->middleware('permission:transactions-access')->name('transactions.hold');
    Route::post('/transactions/{holdId}/resume', [TransactionController::class, 'resumeCart'])->middleware('permission:transactions-access')->name('transactions.resume');
    Route::delete('/transactions/{holdId}/clearHold', [TransactionController::class, 'clearHold'])->middleware('permission:transactions-access')->name('transactions.clearHold');
    Route::get('/transactions/held', [TransactionController::class, 'getHeldCarts'])->middleware('permission:transactions-access')->name('transactions.held');

    //route transaction store
    Route::post('/transactions/store', [TransactionController::class, 'store'])->middleware('permission:transactions-access')->name('transactions.store');
    Route::get('/transactions/{invoice}/print', [TransactionController::class, 'print'])->middleware('permission:transactions-access')->name('transactions.print');
    Route::get('/transactions/history', [TransactionController::class, 'history'])->middleware('permission:transactions-access')->name('transactions.history');

    Route::get('/settings/payments', [PaymentSettingController::class, 'edit'])->middleware('permission:payment-settings-access')->name('settings.payments.edit');
    Route::put('/settings/payments', [PaymentSettingController::class, 'update'])->middleware('permission:payment-settings-access')->name('settings.payments.update');

    // Stock Adjustments
    Route::post('/stock-adjustments/searchProduct', [StockAdjustmentController::class, 'searchProduct'])->name('stock-adjustments.searchProduct');
    Route::post('/stock-adjustments/{id}/finalize', [StockAdjustmentController::class, 'finalize'])->name('stock-adjustments.finalize');
    Route::resource('stock-adjustments', StockAdjustmentController::class)
        ->middleware(['permission:products-access']);

    // Stock Opname
    Route::post('/stock-opnames/searchProduct', [StockOpnameController::class, 'searchProduct'])->name('stock-opnames.searchProduct');
    Route::post('/stock-opnames/getProductStock', [StockOpnameController::class, 'getProductStock'])->name('stock-opnames.getProductStock');
    Route::post('/stock-opnames/getWarehouseProducts', [StockOpnameController::class, 'getWarehouseProducts'])->name('stock-opnames.getWarehouseProducts');
    Route::post('/stock-opnames/{id}/finalize', [StockOpnameController::class, 'finalize'])->name('stock-opnames.finalize');
    Route::resource('stock-opnames', StockOpnameController::class)
        ->middleware(['permission:products-access']);


    //reports
    Route::get('/reports/sales', [SalesReportController::class, 'index'])->middleware('permission:reports-access')->name('reports.sales.index');
    Route::get('/reports/profits', [ProfitReportController::class, 'index'])->middleware('permission:profits-access')->name('reports.profits.index');
    Route::get('/reports/stocks', [ReportStockController::class, 'index'])->middleware('permission:reports-access')->name('reports.stocks.index');
    Route::get('/reports/warehouse-stock', [WarehouseStockController::class, 'index'])->middleware('permission:reports-access')->name('reports.warehouse-stock');
    Route::get('/reports/bundle-warehouse-stock', [\App\Http\Controllers\Apps\BundleWarehouseStockController::class, 'index'])->middleware('permission:reports-access')->name('reports.bundle-warehouse-stock');
    Route::get('/reports/minimum-stock', [MinimumStockReportController::class, 'index'])->middleware('permission:reports-access')->name('reports.minimum-stock');
    Route::get('/reports/fast-moving', [FastMovingReportController::class, 'index'])->middleware('permission:reports-access')->name('reports.fast-moving');
    Route::get('/reports/slow-moving', [SlowMovingReportController::class, 'index'])->middleware('permission:reports-access')->name('reports.slow-moving');
    Route::get('/reports/stock-adjustments', [StockAdjustmentReportController::class, 'index'])->middleware('permission:reports-access')->name('reports.stock-adjustments.index');

    // Utilities
    Route::resource('bank-accounts', BankAccountController::class)->middleware(['permission:products-access']); // Reusing permission for simplicity
    Route::resource('master-addresses', MasterAddressController::class)->middleware(['permission:products-access']);
    Route::resource('shipping-methods', ShippingMethodController::class)->middleware(['permission:products-access']);

    // Accounting
    Route::resource('accounts', AccountController::class)->middleware(['permission:products-access']);
    Route::resource('fixed-assets', FixedAssetController::class)->middleware(['permission:products-access']);
    Route::post('fixed-assets/process-depreciation', [FixedAssetController::class, 'processDepreciation'])->middleware(['permission:products-access'])->name('fixed-assets.process-depreciation');
    Route::post('fixed-assets/{fixedAsset}/finalize', [FixedAssetController::class, 'finalize'])->middleware(['permission:products-access'])->name('fixed-assets.finalize');
    Route::get('fixed-assets/{fixedAsset}/depreciation-history', [FixedAssetController::class, 'depreciationHistory'])->middleware(['permission:products-access'])->name('fixed-assets.depreciation-history');
    Route::get('account-settings', [AccountSettingController::class, 'index'])->middleware(['permission:products-access'])->name('account-settings.index');
    Route::post('account-settings', [AccountSettingController::class, 'update'])->middleware(['permission:products-access'])->name('account-settings.update');
    Route::get('journals', [JournalController::class, 'index'])->middleware(['permission:products-access'])->name('journals.index');
    Route::get('journals/{id}', [JournalController::class, 'show'])->middleware(['permission:products-access'])->name('journals.show');
    Route::get('reports/profit-loss', [ProfitLossReportController::class, 'index'])->middleware(['permission:reports-access'])->name('reports.profit-loss');
    Route::get('reports/general-ledger', [GeneralLedgerController::class, 'index'])->middleware(['permission:reports-access'])->name('reports.general-ledger');
    Route::get('reports/balance-sheet', [BalanceSheetController::class, 'index'])->middleware(['permission:reports-access'])->name('reports.balance-sheet');
    Route::get('reports/cash-flow', [CashFlowController::class, 'index'])->middleware(['permission:reports-access'])->name('reports.cash-flow');
    Route::get('reports/cogs', [\App\Http\Controllers\Apps\COGSReportController::class, 'index'])->middleware(['permission:reports-access'])->name('reports.cogs');
    Route::get('reports/cogs/export/excel', [\App\Http\Controllers\Apps\COGSReportController::class, 'exportExcel'])->middleware(['permission:reports-access'])->name('reports.cogs.export.excel');
    Route::get('reports/cogs/export/pdf', [\App\Http\Controllers\Apps\COGSReportController::class, 'exportPdf'])->middleware(['permission:reports-access'])->name('reports.cogs.export.pdf');
    Route::resource('cash-transactions', CashTransactionController::class)->middleware(['permission:products-access']);
    Route::post('cash-transactions/{id}/finalize', [CashTransactionController::class, 'finalize'])->middleware(['permission:products-access'])->name('cash-transactions.finalize');

    // Tax Management
    Route::get('tax-settings', [TaxSettingController::class, 'index'])->middleware(['permission:products-access'])->name('tax-settings.index');
    Route::post('tax-settings', [TaxSettingController::class, 'update'])->middleware(['permission:products-access'])->name('tax-settings.update');
    Route::resource('taxes', TaxController::class)->middleware(['permission:products-access']);
    Route::get('reports/tax', [TaxReportController::class, 'index'])->middleware(['permission:reports-access'])->name('reports.tax');

    // Petty Cash
    Route::get('petty-cash', [PettyCashController::class, 'index'])->middleware(['permission:products-access'])->name('petty-cash.index');
    Route::post('petty-cash/open', [PettyCashController::class, 'open'])->middleware(['permission:products-access'])->name('petty-cash.open');
    Route::post('petty-cash/replenish', [PettyCashController::class, 'replenish'])->middleware(['permission:products-access'])->name('petty-cash.replenish');
    Route::get('petty-cash/expenses', [PettyCashController::class, 'expenses'])->middleware(['permission:products-access'])->name('petty-cash.expenses');
    Route::post('petty-cash/expenses', [PettyCashController::class, 'storeExpense'])->middleware(['permission:products-access'])->name('petty-cash.expenses.store');
    Route::post('petty-cash/expenses/{id}/approve', [PettyCashController::class, 'approveExpense'])->middleware(['permission:products-access'])->name('petty-cash.expenses.approve');
    Route::post('petty-cash/expenses/{id}/reject', [PettyCashController::class, 'rejectExpense'])->middleware(['permission:products-access'])->name('petty-cash.expenses.reject');
    Route::get('petty-cash/settlement', [PettyCashController::class, 'settlement'])->middleware(['permission:products-access'])->name('petty-cash.settlement');
    Route::post('petty-cash/close', [PettyCashController::class, 'close'])->middleware(['permission:products-access'])->name('petty-cash.close');

    // Receivables (Piutang)
    Route::group(['middleware' => ['permission:products-access']], function () {
        Route::get('receivables', [ReceivableController::class, 'index'])->name('receivables.index');
        Route::get('receivables/payment-history', [ReceivableController::class, 'paymentHistory'])->name('receivables.payment-history');
        Route::get('receivables/aging', [ReceivableController::class, 'aging'])->name('receivables.aging');
        Route::get('receivables/customer/{customerId}', [ReceivableController::class, 'customerCard'])->name('receivables.customer-card');
        Route::get('receivables/{saleId}', [ReceivableController::class, 'show'])->name('receivables.show');
        Route::post('receivables/{saleId}/payment', [ReceivableController::class, 'storePayment'])->name('receivables.payment');

        // Payables (Hutang)
        Route::get('payables', [PayableController::class, 'index'])->name('payables.index');
        Route::get('payables/payment-history', [PayableController::class, 'paymentHistory'])->name('payables.payment-history');
        Route::get('payables/aging', [PayableController::class, 'aging'])->name('payables.aging');
        Route::get('payables/supplier/{supplierId}', [PayableController::class, 'supplierCard'])->name('payables.supplier-card');
        Route::get('payables/{purchaseId}', [PayableController::class, 'show'])->name('payables.show');
        Route::post('payables/{purchaseId}/payment', [PayableController::class, 'storePayment'])->name('payables.payment');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
