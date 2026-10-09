<?php
/**
 * INFOSOF Technologies - Pharmacy Management Software Route Table
 * Maps all 37 Modules to their respective Controllers and Actions
 */

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\MedicinesController;
use App\Controllers\CategoriesController;
use App\Controllers\BatchesController;
use App\Controllers\ExpiryController;
use App\Controllers\BarcodeController;
use App\Controllers\SuppliersController;
use App\Controllers\DoctorsController;
use App\Controllers\PatientsController;
use App\Controllers\PrescriptionsController;
use App\Controllers\PrescriptionBillingController;
use App\Controllers\PurchasesController;
use App\Controllers\PurchaseOrdersController;
use App\Controllers\PurchaseReturnsController;
use App\Controllers\PosController;
use App\Controllers\SalesController;
use App\Controllers\SalesReturnsController;
use App\Controllers\InventoryController;
use App\Controllers\ReorderController;
use App\Controllers\FefoController;
use App\Controllers\PricingController;
use App\Controllers\GstController;
use App\Controllers\PaymentsController;
use App\Controllers\AccountsController;
use App\Controllers\ExpensesController;
use App\Controllers\LoyaltyController;
use App\Controllers\NotificationsController;
use App\Controllers\IntegrationController;
use App\Controllers\OnlineOrdersController;
use App\Controllers\DeliveryController;
use App\Controllers\ReportsController;
use App\Controllers\DocumentsController;
use App\Controllers\AuditController;
use App\Controllers\SecurityController;
use App\Controllers\UsersController;
use App\Controllers\SettingsController;
use App\Config\App;

// Root Redirect
$router->get('/', function() {
    header('Location: ' . App::baseURL() . '/dashboard');
    exit;
});

// Authentication
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/profile', 'AuthController@profile');
$router->post('/profile', 'AuthController@profile');

// Module 2: Dashboard
$router->get('/dashboard', 'DashboardController@index');

// Module 4: Medicine Master
$router->get('/medicines', 'MedicinesController@index');
$router->post('/medicines/create', 'MedicinesController@create');
$router->post('/medicines/edit/(:num)', 'MedicinesController@edit');
$router->get('/medicines/delete/(:num)', 'MedicinesController@delete');

// Module 5: Categories & Therapeutic Classification
$router->get('/categories', 'CategoriesController@index');
$router->post('/categories/create', 'CategoriesController@create');
$router->get('/categories/delete/(:num)', 'CategoriesController@delete');

// Module 6: Batches
$router->get('/batches', 'BatchesController@index');
$router->post('/batches/create', 'BatchesController@create');
$router->post('/batches/adjust/(:num)', 'BatchesController@adjustStock');

// Module 7: Expiry Management
$router->get('/expiry', 'ExpiryController@index');
$router->post('/expiry/write-off/(:num)', 'ExpiryController@writeOff');

// Module 8: Barcode & Medicine Scanning
$router->get('/barcode', 'BarcodeController@index');
$router->get('/barcode/print', 'BarcodeController@printLabels');
$router->get('/api/barcode/lookup', 'BarcodeController@lookup');

// Module 9: Suppliers / Distributors
$router->get('/suppliers', 'SuppliersController@index');
$router->post('/suppliers/create', 'SuppliersController@create');

// Module 10: Doctors
$router->get('/doctors', 'DoctorsController@index');
$router->post('/doctors/create', 'DoctorsController@create');

// Module 11: Patients / Customers
$router->get('/patients', 'PatientsController@index');
$router->post('/patients/create', 'PatientsController@create');

// Module 12: Prescriptions
$router->get('/prescriptions', 'PrescriptionsController@index');
$router->get('/prescriptions/upload', 'PrescriptionsController@upload');
$router->get('/prescriptions/create', 'PrescriptionsController@create');
$router->post('/prescriptions/create', 'PrescriptionsController@create');
$router->post('/prescriptions/verify/(:num)', 'PrescriptionsController@verify');
$router->get('/api/prescriptions/(:num)/items', 'PrescriptionsController@getItems');

// Module 13: Prescription-Based Billing
$router->get('/prescription-billing', 'PrescriptionBillingController@index');
$router->get('/prescription_billing', 'PrescriptionBillingController@index');

// Module 14: Purchases
$router->get('/purchases', 'PurchasesController@index');
$router->get('/purchases/create', 'PurchasesController@create');
$router->get('/purchases_create', 'PurchasesController@create');
$router->post('/purchases/create', 'PurchasesController@create');
$router->post('/purchases_create', 'PurchasesController@create');
$router->post('/purchases/pay/(:num)', 'PurchasesController@recordPayment');

// Module 15: Purchase Orders (PO)
$router->get('/purchase-orders', 'PurchaseOrdersController@index');
$router->get('/purchase_orders', 'PurchaseOrdersController@index');
$router->get('/purchase-orders/create', 'PurchaseOrdersController@create');
$router->get('/purchase_orders/create', 'PurchaseOrdersController@create');
$router->post('/purchase-orders/create', 'PurchaseOrdersController@create');
$router->post('/purchase_orders/create', 'PurchaseOrdersController@create');
$router->post('/purchase-orders/approve/(:num)', 'PurchaseOrdersController@approve');
$router->post('/purchase-orders/convert/(:num)', 'PurchaseOrdersController@convertToInvoice');

// Module 16: Purchase Returns
$router->get('/purchase-returns', 'PurchaseReturnsController@index');
$router->get('/purchase_returns', 'PurchaseReturnsController@index');
$router->get('/purchase-returns/create', 'PurchaseReturnsController@create');
$router->get('/purchase_returns/create', 'PurchaseReturnsController@create');
$router->post('/purchase-returns/create', 'PurchaseReturnsController@create');
$router->post('/purchase_returns/create', 'PurchaseReturnsController@create');

// Module 17: Pharmacy POS Counter Billing
$router->get('/pos', 'PosController@index');
$router->post('/pos/checkout', 'PosController@checkout');
$router->get('/api/pos/batches/(:num)', 'PosController@getBatches');

// Module 18: Medicine Sales Management
$router->get('/sales', 'SalesController@index');
$router->get('/sales/invoice/(:num)', 'SalesController@invoice');

// Module 19: Sales Returns
$router->get('/sales-returns', 'SalesReturnsController@index');
$router->get('/sales_returns', 'SalesReturnsController@index');
$router->get('/sales-returns/create', 'SalesReturnsController@create');
$router->get('/sales_returns/create', 'SalesReturnsController@create');
$router->post('/sales-returns/create', 'SalesReturnsController@create');
$router->post('/sales_returns/create', 'SalesReturnsController@create');

// Module 20: Inventory / Stock Management
$router->get('/inventory', 'InventoryController@index');

// Module 21: Smart Reorder Management
$router->get('/reorder', 'ReorderController@index');

// Module 22: FEFO / FIFO Stock Management
$router->get('/fefo', 'FefoController@index');

// Module 23: Medicine Pricing Management
$router->get('/pricing', 'PricingController@index');
$router->post('/pricing/update/(:num)', 'PricingController@updatePrice');

// Module 24: GST & Tax Management
$router->get('/gst', 'GstController@index');

// Module 25: Payment & Accounts Management
$router->get('/payments', 'PaymentsController@index');

// Module 26: Accounts Receivable & Payable
$router->get('/accounts', 'AccountsController@index');
$router->post('/accounts/collect', 'AccountsController@collectCustomerPayment');
$router->post('/accounts/pay-supplier', 'AccountsController@paySupplier');

// Module 27: Expense Management
$router->get('/expenses', 'ExpensesController@index');
$router->post('/expenses/create', 'ExpensesController@create');

// Module 28: Loyalty & Customer Management
$router->get('/loyalty', 'LoyaltyController@index');
$router->post('/loyalty/create-coupon', 'LoyaltyController@createCoupon');

// Module 29: Notification & Alert System
$router->get('/notifications', 'NotificationsController@index');
$router->post('/notifications/mark-read', 'NotificationsController@markAllRead');

// Module 30: WhatsApp / SMS / Email Integration
$router->get('/integration', 'IntegrationController@index');

// Online / E-Pharmacy Order Management
$router->get('/online-orders', 'OnlineOrdersController@index');
$router->get('/online_orders', 'OnlineOrdersController@index');
$router->post('/online-orders/create', 'OnlineOrdersController@create');
$router->post('/online_orders/create', 'OnlineOrdersController@create');
$router->post('/online-orders/update-status/(:num)', 'OnlineOrdersController@updateStatus');
$router->post('/online_orders/update-status/(:num)', 'OnlineOrdersController@updateStatus');
$router->get('/online-orders/details/(:num)', 'OnlineOrdersController@details');
$router->get('/online_orders/details/(:num)', 'OnlineOrdersController@details');

// Module 32: Delivery Management
$router->get('/delivery', 'DeliveryController@index');
$router->post('/delivery/create', 'DeliveryController@create');
$router->post('/delivery/update-status/(:num)', 'DeliveryController@updateStatus');

// Module 33: Reports & Analytics
$router->get('/reports', 'ReportsController@index');

// Module 34: Document & Prescription Storage
$router->get('/documents', 'DocumentsController@index');
$router->post('/documents/upload', 'DocumentsController@upload');

// Module 35: Audit Trail & Activity Logs
$router->get('/audit', 'AuditController@index');

// Module 36: Security, Backup & Access Control
$router->get('/security', 'SecurityController@index');
$router->get('/security/backup', 'SecurityController@downloadBackup');
$router->post('/security/update-permissions', 'SecurityController@updatePermissions');
$router->post('/security/reset-permissions', 'SecurityController@resetPermissions');

// Module 1: User & Role Management
$router->get('/users', 'UsersController@index');
$router->post('/users/create', 'UsersController@create');
$router->post('/users/update/(:num)', 'UsersController@update');
$router->post('/users/delete/(:num)', 'UsersController@delete');
$router->post('/users/toggle-status/(:num)', 'UsersController@toggleStatus');

// Modules 3 & 37: Profile, Settings & Print Center
$router->get('/settings', 'SettingsController@index');
$router->post('/settings/update', 'SettingsController@update');
