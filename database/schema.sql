-- =======================================================
-- INFOSOF TECHNOLOGIES - Pharmacy Management Software
-- Complete Database Schema (MySQL 5.x / 8.x Compatible)
-- Supports all 37 Modules
-- =======================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------
-- 1. Users & Authentication
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'cashier',
  `phone` VARCHAR(20) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 1b. Role Permissions Matrix
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role` VARCHAR(50) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `can_view` TINYINT(1) NOT NULL DEFAULT 1,
  `can_create` TINYINT(1) NOT NULL DEFAULT 0,
  `can_edit` TINYINT(1) NOT NULL DEFAULT 0,
  `can_delete` TINYINT(1) NOT NULL DEFAULT 0,
  `can_approve` TINYINT(1) NOT NULL DEFAULT 0,
  `can_export` TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY `role_module_unique` (`role`, `module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 3. Pharmacy Profile & Company Settings
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pharmacy_profile` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pharmacy_name` VARCHAR(150) NOT NULL DEFAULT 'INFOSOF Care Pharmacy',
  `legal_name` VARCHAR(150) NOT NULL DEFAULT 'INFOSOF Technologies Healthcare Pvt Ltd',
  `gstin` VARCHAR(25) NOT NULL DEFAULT '27AAAAA0000A1Z5',
  `drug_license_no` VARCHAR(50) NOT NULL DEFAULT 'DL-20B-109283 / 21B-109284',
  `pharmacy_reg_no` VARCHAR(50) NOT NULL DEFAULT 'REG-PHARM-2024-8891',
  `pharmacist_name` VARCHAR(100) NOT NULL DEFAULT 'Dr. Rajesh Sharma, B.Pharm',
  `pharmacist_reg_no` VARCHAR(50) NOT NULL DEFAULT 'PCI-RPH-449102',
  `address` TEXT NOT NULL,
  `city` VARCHAR(50) NOT NULL DEFAULT 'Mumbai',
  `state` VARCHAR(50) NOT NULL DEFAULT 'Maharashtra',
  `country` VARCHAR(50) NOT NULL DEFAULT 'India',
  `pincode` VARCHAR(15) NOT NULL DEFAULT '400001',
  `phone` VARCHAR(25) NOT NULL DEFAULT '+91 22 2849 5500',
  `mobile` VARCHAR(25) NOT NULL DEFAULT '+91 98200 12345',
  `email` VARCHAR(100) NOT NULL DEFAULT 'info@infosofpharmacy.com',
  `website` VARCHAR(100) NOT NULL DEFAULT 'https://pharmacy.infosof.com',
  `invoice_prefix` VARCHAR(10) NOT NULL DEFAULT 'INV-',
  `po_prefix` VARCHAR(10) NOT NULL DEFAULT 'PO-',
  `currency_symbol` VARCHAR(10) NOT NULL DEFAULT '₹',
  `tax_inclusive` TINYINT(1) NOT NULL DEFAULT 1,
  `thermal_header` TEXT DEFAULT NULL,
  `thermal_footer` TEXT DEFAULT NULL,
  `terms_conditions` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 5. Medicine Categories & Therapeutic Classifications
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 4. Medicine Master
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `medicines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `brand_name` VARCHAR(100) NOT NULL,
  `category_id` INT NOT NULL,
  `dosage_form` VARCHAR(50) NOT NULL DEFAULT 'Tablet',
  `strength` VARCHAR(50) NOT NULL DEFAULT '500 mg',
  `composition` TEXT NOT NULL,
  `manufacturer` VARCHAR(100) NOT NULL,
  `pack_size` VARCHAR(50) NOT NULL DEFAULT '1x10 Tablets',
  `unit` VARCHAR(30) NOT NULL DEFAULT 'Strip',
  `hsn_code` VARCHAR(20) NOT NULL DEFAULT '300490',
  `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 12.00,
  `requires_prescription` TINYINT(1) NOT NULL DEFAULT 0,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `storage_conditions` VARCHAR(100) DEFAULT 'Store in a cool, dry place away from sunlight',
  `min_stock_level` INT NOT NULL DEFAULT 20,
  `reorder_level` INT NOT NULL DEFAULT 50,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_med_category` (`category_id`),
  KEY `idx_med_name` (`name`),
  KEY `idx_med_brand` (`brand_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 6 & 22. Batches (FEFO / FIFO & Batch Tracking)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `batches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `medicine_id` INT NOT NULL,
  `batch_number` VARCHAR(50) NOT NULL,
  `mfg_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `quantity` INT NOT NULL DEFAULT 0,
  `initial_quantity` INT NOT NULL DEFAULT 0,
  `purchase_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `mrp` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `wholesale_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `barcode` VARCHAR(50) NOT NULL,
  `status` ENUM('active', 'quarantined', 'expired', 'depleted') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_med_batch` (`medicine_id`, `batch_number`),
  KEY `idx_batch_exp` (`expiry_date`),
  KEY `idx_batch_barcode` (`barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 9. Suppliers / Distributors
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `company_name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(50) NOT NULL DEFAULT 'Mumbai',
  `state` VARCHAR(50) NOT NULL DEFAULT 'Maharashtra',
  `gstin` VARCHAR(25) DEFAULT NULL,
  `drug_license_no` VARCHAR(50) DEFAULT NULL,
  `payment_terms_days` INT NOT NULL DEFAULT 30,
  `current_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `performance_rating` DECIMAL(3,2) NOT NULL DEFAULT 4.50,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 10. Doctors
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `doctors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `registration_number` VARCHAR(50) NOT NULL,
  `specialization` VARCHAR(100) NOT NULL DEFAULT 'General Physician',
  `hospital_clinic` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 11. Patients / Customers
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `patients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_code` VARCHAR(30) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `age` INT DEFAULT NULL,
  `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
  `phone` VARCHAR(25) NOT NULL UNIQUE,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `allergies` TEXT DEFAULT NULL,
  `chronic_conditions` TEXT DEFAULT NULL,
  `blood_group` VARCHAR(10) DEFAULT NULL,
  `loyalty_points` INT NOT NULL DEFAULT 0,
  `loyalty_tier` ENUM('Bronze', 'Silver', 'Gold', 'Platinum') NOT NULL DEFAULT 'Bronze',
  `outstanding_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 12. Prescriptions
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `prescription_number` VARCHAR(50) NOT NULL UNIQUE,
  `patient_id` INT NOT NULL,
  `doctor_id` INT DEFAULT NULL,
  `prescription_date` DATE NOT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'verified', 'partially_dispensed', 'completed', 'rejected') NOT NULL DEFAULT 'pending',
  `pharmacist_id` INT DEFAULT NULL,
  `pharmacist_notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_rx_patient` (`patient_id`),
  KEY `idx_rx_doctor` (`doctor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 12b. Prescription Items
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `prescription_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `prescription_id` INT NOT NULL,
  `medicine_id` INT NOT NULL,
  `dosage` VARCHAR(50) NOT NULL DEFAULT '1 Tablet',
  `frequency` VARCHAR(50) NOT NULL DEFAULT '1-0-1 (After Food)',
  `duration` VARCHAR(50) NOT NULL DEFAULT '5 Days',
  `qty_prescribed` INT NOT NULL DEFAULT 10,
  `qty_dispensed` INT NOT NULL DEFAULT 0,
  `instructions` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 15. Purchase Orders (PO)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `po_number` VARCHAR(50) NOT NULL UNIQUE,
  `supplier_id` INT NOT NULL,
  `order_date` DATE NOT NULL,
  `expected_date` DATE NOT NULL,
  `status` ENUM('draft', 'pending_approval', 'approved', 'converted_to_invoice', 'cancelled') NOT NULL DEFAULT 'draft',
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `approved_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `purchase_order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `po_id` INT NOT NULL,
  `medicine_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `expected_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 14. Purchases (Purchase Invoices)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL,
  `po_id` INT DEFAULT NULL,
  `supplier_id` INT NOT NULL,
  `invoice_date` DATE NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('paid', 'partial', 'unpaid') NOT NULL DEFAULT 'unpaid',
  `payment_mode` ENUM('cash', 'card', 'upi', 'bank_transfer', 'cheque', 'credit') NOT NULL DEFAULT 'credit',
  `notes` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `purchase_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `purchase_id` INT NOT NULL,
  `medicine_id` INT NOT NULL,
  `batch_id` INT DEFAULT NULL,
  `batch_number` VARCHAR(50) NOT NULL,
  `mfg_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `quantity` INT NOT NULL,
  `free_qty` INT NOT NULL DEFAULT 0,
  `purchase_rate` DECIMAL(10,2) NOT NULL,
  `mrp` DECIMAL(10,2) NOT NULL,
  `selling_price` DECIMAL(10,2) NOT NULL,
  `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 12.00,
  `cgst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `igst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 16. Purchase Returns
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `purchase_returns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `return_number` VARCHAR(50) NOT NULL UNIQUE,
  `purchase_id` INT DEFAULT NULL,
  `supplier_id` INT NOT NULL,
  `return_date` DATE NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `return_reason` ENUM('expired', 'damaged', 'recall', 'excess', 'other') NOT NULL DEFAULT 'expired',
  `refund_type` ENUM('supplier_credit', 'bank_refund', 'cash_refund') NOT NULL DEFAULT 'supplier_credit',
  `status` ENUM('pending', 'approved', 'completed') NOT NULL DEFAULT 'completed',
  `notes` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `purchase_return_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `return_id` INT NOT NULL,
  `medicine_id` INT NOT NULL,
  `batch_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `rate` DECIMAL(10,2) NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 17 & 18. Sales & Counter Billing (POS)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `prescription_id` INT DEFAULT NULL,
  `patient_id` INT DEFAULT NULL,
  `customer_name` VARCHAR(100) DEFAULT 'Walk-in Customer',
  `customer_phone` VARCHAR(25) DEFAULT NULL,
  `doctor_id` INT DEFAULT NULL,
  `doctor_name` VARCHAR(100) DEFAULT NULL,
  `sale_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `igst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `loyalty_points_used` INT NOT NULL DEFAULT 0,
  `loyalty_discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `round_off` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `change_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_mode` ENUM('cash', 'card', 'upi', 'bank_transfer', 'credit', 'split') NOT NULL DEFAULT 'cash',
  `payment_status` ENUM('paid', 'partial', 'unpaid') NOT NULL DEFAULT 'paid',
  `is_credit_sale` TINYINT(1) NOT NULL DEFAULT 0,
  `cashier_id` INT DEFAULT NULL,
  `print_count` INT NOT NULL DEFAULT 0,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_sales_inv` (`invoice_number`),
  KEY `idx_sales_date` (`sale_date`),
  KEY `idx_sales_patient` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_id` INT NOT NULL,
  `medicine_id` INT NOT NULL,
  `batch_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `mrp` DECIMAL(10,2) NOT NULL,
  `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 12.00,
  `cgst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `igst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 19. Sales Returns
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sale_returns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `return_number` VARCHAR(50) NOT NULL UNIQUE,
  `sale_id` INT NOT NULL,
  `patient_id` INT DEFAULT NULL,
  `return_date` DATE NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `refund_type` ENUM('cash_refund', 'credit_note', 'upi_refund') NOT NULL DEFAULT 'cash_refund',
  `refund_status` ENUM('completed', 'pending') NOT NULL DEFAULT 'completed',
  `created_by` INT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sale_return_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `return_id` INT NOT NULL,
  `sale_item_id` INT NOT NULL,
  `medicine_id` INT NOT NULL,
  `batch_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `return_reason` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 20. Inventory Stock Movement Audit Ledger
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `medicine_id` INT NOT NULL,
  `batch_id` INT NOT NULL,
  `movement_type` ENUM('opening', 'purchase', 'sale', 'purchase_return', 'sale_return', 'stock_adjustment', 'expiry_writeoff') NOT NULL,
  `quantity` INT NOT NULL,
  `previous_qty` INT NOT NULL,
  `new_qty` INT NOT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` INT DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  `notes` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_stock_med` (`medicine_id`),
  KEY `idx_stock_batch` (`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 26. Accounts / Financial Ledgers
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `financial_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `trans_code` VARCHAR(50) NOT NULL UNIQUE,
  `trans_type` ENUM('income', 'expense', 'customer_collection', 'supplier_payment', 'refund') NOT NULL,
  `entity_type` ENUM('customer', 'supplier', 'general') NOT NULL DEFAULT 'general',
  `entity_id` INT DEFAULT NULL,
  `entity_name` VARCHAR(100) DEFAULT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` INT DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_mode` ENUM('cash', 'card', 'upi', 'bank_transfer', 'cheque') NOT NULL DEFAULT 'cash',
  `trans_date` DATE NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 27. Expense Management
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `expense_date` DATE NOT NULL,
  `category` ENUM('Electricity', 'Rent', 'Staff Salary', 'Transportation', 'Maintenance', 'Internet & Software', 'Packaging', 'Miscellaneous') NOT NULL DEFAULT 'Miscellaneous',
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_mode` ENUM('cash', 'card', 'upi', 'bank_transfer', 'cheque') NOT NULL DEFAULT 'cash',
  `payee` VARCHAR(100) NOT NULL,
  `reference_no` VARCHAR(50) DEFAULT NULL,
  `receipt_file` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 28. Loyalty Coupons & Rewards
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loyalty_coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) NOT NULL UNIQUE,
  `description` VARCHAR(200) NOT NULL,
  `discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` DECIMAL(10,2) NOT NULL DEFAULT 500.00,
  `expiry_date` DATE NOT NULL,
  `usage_count` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `loyalty_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `sale_id` INT DEFAULT NULL,
  `points_change` INT NOT NULL,
  `reason` VARCHAR(200) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 29. Notification & Alert System
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('low_stock', 'out_of_stock', 'near_expiry', 'expired', 'payment_due', 'po_pending', 'prescription_pending', 'system') NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `severity` ENUM('info', 'warning', 'danger', 'success') NOT NULL DEFAULT 'info',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 31. Online / E-Pharmacy Orders
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `online_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(25) NOT NULL,
  `customer_email` VARCHAR(100) DEFAULT NULL,
  `delivery_address` TEXT NOT NULL,
  `prescription_file` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'verified', 'processing', 'packed', 'dispatched', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('unpaid', 'paid', 'cod') NOT NULL DEFAULT 'cod',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `online_order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `online_order_id` INT NOT NULL,
  `medicine_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 32. Delivery Management
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `deliveries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tracking_number` VARCHAR(50) NOT NULL UNIQUE,
  `sale_id` INT DEFAULT NULL,
  `online_order_id` INT DEFAULT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(25) NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `delivery_executive_name` VARCHAR(100) NOT NULL,
  `delivery_executive_phone` VARCHAR(25) NOT NULL,
  `status` ENUM('assigned', 'out_for_delivery', 'delivered', 'failed') NOT NULL DEFAULT 'assigned',
  `delivery_fee` DECIMAL(8,2) NOT NULL DEFAULT 40.00,
  `estimated_delivery` DATETIME DEFAULT NULL,
  `delivered_at` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 34. Document Storage & Prescription Digital Archive
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` ENUM('Prescription', 'Supplier Invoice', 'Drug License', 'Registration', 'Product Sheet', 'Customer Record', 'Other') NOT NULL DEFAULT 'Other',
  `file_path` VARCHAR(255) NOT NULL,
  `file_size_kb` INT NOT NULL DEFAULT 0,
  `file_type` VARCHAR(50) DEFAULT 'image/jpeg',
  `entity_type` VARCHAR(50) DEFAULT NULL,
  `entity_id` INT DEFAULT NULL,
  `uploaded_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 35. Audit Trail & Activity Logs
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `record_id` INT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_mod` (`module`),
  KEY `idx_audit_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 37. System Settings
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
