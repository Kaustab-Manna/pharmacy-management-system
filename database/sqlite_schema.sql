-- =======================================================
-- INFOSOF TECHNOLOGIES - Pharmacy Management Software
-- SQLite Schema (Zero-Configuration Local Development)
-- Supports all 37 Modules
-- =======================================================

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL UNIQUE,
  email TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL,
  full_name TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'cashier',
  phone TEXT DEFAULT NULL,
  is_active INTEGER NOT NULL DEFAULT 1,
  two_factor_enabled INTEGER NOT NULL DEFAULT 0,
  last_login TEXT DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS role_permissions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  role TEXT NOT NULL,
  module TEXT NOT NULL,
  can_view INTEGER NOT NULL DEFAULT 1,
  can_create INTEGER NOT NULL DEFAULT 0,
  can_edit INTEGER NOT NULL DEFAULT 0,
  can_delete INTEGER NOT NULL DEFAULT 0,
  can_approve INTEGER NOT NULL DEFAULT 0,
  can_export INTEGER NOT NULL DEFAULT 1,
  UNIQUE (role, module)
);

CREATE TABLE IF NOT EXISTS pharmacy_profile (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  pharmacy_name TEXT NOT NULL DEFAULT 'INFOSOF Care Pharmacy',
  legal_name TEXT NOT NULL DEFAULT 'INFOSOF Technologies Healthcare Pvt Ltd',
  gstin TEXT NOT NULL DEFAULT '27AAAAA0000A1Z5',
  drug_license_no TEXT NOT NULL DEFAULT 'DL-20B-109283 / 21B-109284',
  pharmacy_reg_no TEXT NOT NULL DEFAULT 'REG-PHARM-2024-8891',
  pharmacist_name TEXT NOT NULL DEFAULT 'Dr. Rajesh Sharma, B.Pharm',
  pharmacist_reg_no TEXT NOT NULL DEFAULT 'PCI-RPH-449102',
  address TEXT NOT NULL,
  city TEXT NOT NULL DEFAULT 'Mumbai',
  state TEXT NOT NULL DEFAULT 'Maharashtra',
  country TEXT NOT NULL DEFAULT 'India',
  pincode TEXT NOT NULL DEFAULT '400001',
  phone TEXT NOT NULL DEFAULT '+91 22 2849 5500',
  mobile TEXT NOT NULL DEFAULT '+91 98200 12345',
  email TEXT NOT NULL DEFAULT 'info@infosofpharmacy.com',
  website TEXT NOT NULL DEFAULT 'https://pharmacy.infosof.com',
  invoice_prefix TEXT NOT NULL DEFAULT 'INV-',
  po_prefix TEXT NOT NULL DEFAULT 'PO-',
  currency_symbol TEXT NOT NULL DEFAULT '₹',
  tax_inclusive INTEGER NOT NULL DEFAULT 1,
  thermal_header TEXT DEFAULT NULL,
  thermal_footer TEXT DEFAULT NULL,
  terms_conditions TEXT DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS categories (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL UNIQUE,
  slug TEXT NOT NULL UNIQUE,
  description TEXT DEFAULT NULL,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medicines (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  brand_name TEXT NOT NULL,
  category_id INTEGER NOT NULL,
  dosage_form TEXT NOT NULL DEFAULT 'Tablet',
  strength TEXT NOT NULL DEFAULT '500 mg',
  composition TEXT NOT NULL,
  manufacturer TEXT NOT NULL,
  pack_size TEXT NOT NULL DEFAULT '1x10 Tablets',
  unit TEXT NOT NULL DEFAULT 'Strip',
  hsn_code TEXT NOT NULL DEFAULT '300490',
  gst_rate REAL NOT NULL DEFAULT 12.00,
  requires_prescription INTEGER NOT NULL DEFAULT 0,
  image_url TEXT DEFAULT NULL,
  description TEXT DEFAULT NULL,
  storage_conditions TEXT DEFAULT 'Store in a cool, dry place away from sunlight',
  min_stock_level INTEGER NOT NULL DEFAULT 20,
  reorder_level INTEGER NOT NULL DEFAULT 50,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS batches (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  medicine_id INTEGER NOT NULL,
  batch_number TEXT NOT NULL,
  mfg_date TEXT NOT NULL,
  expiry_date TEXT NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 0,
  initial_quantity INTEGER NOT NULL DEFAULT 0,
  purchase_price REAL NOT NULL DEFAULT 0.00,
  selling_price REAL NOT NULL DEFAULT 0.00,
  mrp REAL NOT NULL DEFAULT 0.00,
  wholesale_price REAL NOT NULL DEFAULT 0.00,
  barcode TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'active',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT DEFAULT NULL,
  UNIQUE (medicine_id, batch_number)
);

CREATE TABLE IF NOT EXISTS suppliers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  company_name TEXT NOT NULL,
  contact_person TEXT DEFAULT NULL,
  phone TEXT NOT NULL,
  email TEXT DEFAULT NULL,
  address TEXT NOT NULL,
  city TEXT NOT NULL DEFAULT 'Mumbai',
  state TEXT NOT NULL DEFAULT 'Maharashtra',
  gstin TEXT DEFAULT NULL,
  drug_license_no TEXT DEFAULT NULL,
  payment_terms_days INTEGER NOT NULL DEFAULT 30,
  current_balance REAL NOT NULL DEFAULT 0.00,
  performance_rating REAL NOT NULL DEFAULT 4.50,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS doctors (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  registration_number TEXT NOT NULL,
  specialization TEXT NOT NULL DEFAULT 'General Physician',
  hospital_clinic TEXT NOT NULL,
  phone TEXT NOT NULL,
  email TEXT DEFAULT NULL,
  address TEXT DEFAULT NULL,
  commission_rate REAL NOT NULL DEFAULT 0.00,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS patients (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  patient_code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  age INTEGER DEFAULT NULL,
  gender TEXT DEFAULT 'Male',
  phone TEXT NOT NULL UNIQUE,
  email TEXT DEFAULT NULL,
  address TEXT DEFAULT NULL,
  allergies TEXT DEFAULT NULL,
  chronic_conditions TEXT DEFAULT NULL,
  blood_group TEXT DEFAULT NULL,
  loyalty_points INTEGER NOT NULL DEFAULT 0,
  loyalty_tier TEXT NOT NULL DEFAULT 'Bronze',
  outstanding_balance REAL NOT NULL DEFAULT 0.00,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS prescriptions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  prescription_number TEXT NOT NULL UNIQUE,
  patient_id INTEGER NOT NULL,
  doctor_id INTEGER DEFAULT NULL,
  prescription_date TEXT NOT NULL,
  file_path TEXT DEFAULT NULL,
  status TEXT NOT NULL DEFAULT 'pending',
  pharmacist_id INTEGER DEFAULT NULL,
  pharmacist_notes TEXT DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS prescription_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  prescription_id INTEGER NOT NULL,
  medicine_id INTEGER NOT NULL,
  dosage TEXT NOT NULL DEFAULT '1 Tablet',
  frequency TEXT NOT NULL DEFAULT '1-0-1 (After Food)',
  duration TEXT NOT NULL DEFAULT '5 Days',
  qty_prescribed INTEGER NOT NULL DEFAULT 10,
  qty_dispensed INTEGER NOT NULL DEFAULT 0,
  instructions TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS purchase_orders (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  po_number TEXT NOT NULL UNIQUE,
  supplier_id INTEGER NOT NULL,
  order_date TEXT NOT NULL,
  expected_date TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'draft',
  total_amount REAL NOT NULL DEFAULT 0.00,
  notes TEXT DEFAULT NULL,
  created_by INTEGER DEFAULT NULL,
  approved_by INTEGER DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS purchase_order_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  po_id INTEGER NOT NULL,
  medicine_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL,
  expected_rate REAL NOT NULL DEFAULT 0.00,
  total_amount REAL NOT NULL DEFAULT 0.00
);

CREATE TABLE IF NOT EXISTS purchases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  invoice_number TEXT NOT NULL,
  po_id INTEGER DEFAULT NULL,
  supplier_id INTEGER NOT NULL,
  invoice_date TEXT NOT NULL,
  subtotal REAL NOT NULL DEFAULT 0.00,
  tax_amount REAL NOT NULL DEFAULT 0.00,
  discount_amount REAL NOT NULL DEFAULT 0.00,
  grand_total REAL NOT NULL DEFAULT 0.00,
  paid_amount REAL NOT NULL DEFAULT 0.00,
  payment_status TEXT NOT NULL DEFAULT 'unpaid',
  payment_mode TEXT NOT NULL DEFAULT 'credit',
  notes TEXT DEFAULT NULL,
  created_by INTEGER DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS purchase_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  purchase_id INTEGER NOT NULL,
  medicine_id INTEGER NOT NULL,
  batch_id INTEGER DEFAULT NULL,
  batch_number TEXT NOT NULL,
  mfg_date TEXT NOT NULL,
  expiry_date TEXT NOT NULL,
  quantity INTEGER NOT NULL,
  free_qty INTEGER NOT NULL DEFAULT 0,
  purchase_rate REAL NOT NULL,
  mrp REAL NOT NULL,
  selling_price REAL NOT NULL,
  discount_percent REAL NOT NULL DEFAULT 0.00,
  gst_rate REAL NOT NULL DEFAULT 12.00,
  cgst_amount REAL NOT NULL DEFAULT 0.00,
  sgst_amount REAL NOT NULL DEFAULT 0.00,
  igst_amount REAL NOT NULL DEFAULT 0.00,
  total_amount REAL NOT NULL
);

CREATE TABLE IF NOT EXISTS purchase_returns (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  return_number TEXT NOT NULL UNIQUE,
  purchase_id INTEGER DEFAULT NULL,
  supplier_id INTEGER NOT NULL,
  return_date TEXT NOT NULL,
  total_amount REAL NOT NULL DEFAULT 0.00,
  return_reason TEXT NOT NULL DEFAULT 'expired',
  refund_type TEXT NOT NULL DEFAULT 'supplier_credit',
  status TEXT NOT NULL DEFAULT 'completed',
  notes TEXT DEFAULT NULL,
  created_by INTEGER DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS purchase_return_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  return_id INTEGER NOT NULL,
  medicine_id INTEGER NOT NULL,
  batch_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL,
  rate REAL NOT NULL,
  total_amount REAL NOT NULL,
  reason TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS sales (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  invoice_number TEXT NOT NULL UNIQUE,
  prescription_id INTEGER DEFAULT NULL,
  patient_id INTEGER DEFAULT NULL,
  customer_name TEXT DEFAULT 'Walk-in Customer',
  customer_phone TEXT DEFAULT NULL,
  doctor_id INTEGER DEFAULT NULL,
  doctor_name TEXT DEFAULT NULL,
  sale_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  subtotal REAL NOT NULL DEFAULT 0.00,
  tax_amount REAL NOT NULL DEFAULT 0.00,
  cgst_amount REAL NOT NULL DEFAULT 0.00,
  sgst_amount REAL NOT NULL DEFAULT 0.00,
  igst_amount REAL NOT NULL DEFAULT 0.00,
  discount_amount REAL NOT NULL DEFAULT 0.00,
  loyalty_points_used INTEGER NOT NULL DEFAULT 0,
  loyalty_discount REAL NOT NULL DEFAULT 0.00,
  round_off REAL NOT NULL DEFAULT 0.00,
  grand_total REAL NOT NULL DEFAULT 0.00,
  paid_amount REAL NOT NULL DEFAULT 0.00,
  change_amount REAL NOT NULL DEFAULT 0.00,
  payment_mode TEXT NOT NULL DEFAULT 'cash',
  payment_status TEXT NOT NULL DEFAULT 'paid',
  is_credit_sale INTEGER NOT NULL DEFAULT 0,
  cashier_id INTEGER DEFAULT NULL,
  print_count INTEGER NOT NULL DEFAULT 0,
  notes TEXT DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sale_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  sale_id INTEGER NOT NULL,
  medicine_id INTEGER NOT NULL,
  batch_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL,
  unit_price REAL NOT NULL,
  mrp REAL NOT NULL,
  discount_percent REAL NOT NULL DEFAULT 0.00,
  gst_rate REAL NOT NULL DEFAULT 12.00,
  cgst_amount REAL NOT NULL DEFAULT 0.00,
  sgst_amount REAL NOT NULL DEFAULT 0.00,
  igst_amount REAL NOT NULL DEFAULT 0.00,
  total_amount REAL NOT NULL
);

CREATE TABLE IF NOT EXISTS sale_returns (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  return_number TEXT NOT NULL UNIQUE,
  sale_id INTEGER NOT NULL,
  patient_id INTEGER DEFAULT NULL,
  return_date TEXT NOT NULL,
  total_amount REAL NOT NULL DEFAULT 0.00,
  refund_type TEXT NOT NULL DEFAULT 'cash_refund',
  refund_status TEXT NOT NULL DEFAULT 'completed',
  created_by INTEGER DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sale_return_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  return_id INTEGER NOT NULL,
  sale_item_id INTEGER NOT NULL,
  medicine_id INTEGER NOT NULL,
  batch_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL,
  unit_price REAL NOT NULL,
  total_amount REAL NOT NULL,
  return_reason TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS stock_movements (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  medicine_id INTEGER NOT NULL,
  batch_id INTEGER NOT NULL,
  movement_type TEXT NOT NULL,
  quantity INTEGER NOT NULL,
  previous_qty INTEGER NOT NULL,
  new_qty INTEGER NOT NULL,
  reference_type TEXT DEFAULT NULL,
  reference_id INTEGER DEFAULT NULL,
  user_id INTEGER DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS financial_transactions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  trans_code TEXT NOT NULL UNIQUE,
  trans_type TEXT NOT NULL,
  entity_type TEXT NOT NULL DEFAULT 'general',
  entity_id INTEGER DEFAULT NULL,
  entity_name TEXT DEFAULT NULL,
  reference_type TEXT DEFAULT NULL,
  reference_id INTEGER DEFAULT NULL,
  amount REAL NOT NULL,
  payment_mode TEXT NOT NULL DEFAULT 'cash',
  trans_date TEXT NOT NULL,
  description TEXT NOT NULL,
  created_by INTEGER DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS expenses (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  expense_date TEXT NOT NULL,
  category TEXT NOT NULL DEFAULT 'Miscellaneous',
  amount REAL NOT NULL,
  payment_mode TEXT NOT NULL DEFAULT 'cash',
  payee TEXT NOT NULL,
  reference_no TEXT DEFAULT NULL,
  receipt_file TEXT DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_by INTEGER DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS loyalty_coupons (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT NOT NULL UNIQUE,
  description TEXT NOT NULL,
  discount_type TEXT NOT NULL DEFAULT 'percentage',
  discount_value REAL NOT NULL,
  min_order_amount REAL NOT NULL DEFAULT 0.00,
  max_discount REAL NOT NULL DEFAULT 500.00,
  expiry_date TEXT NOT NULL,
  usage_count INTEGER NOT NULL DEFAULT 0,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS loyalty_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  patient_id INTEGER NOT NULL,
  sale_id INTEGER DEFAULT NULL,
  points_change INTEGER NOT NULL,
  reason TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  type TEXT NOT NULL,
  title TEXT NOT NULL,
  message TEXT NOT NULL,
  link TEXT DEFAULT NULL,
  severity TEXT NOT NULL DEFAULT 'info',
  is_read INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS online_orders (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  order_number TEXT NOT NULL UNIQUE,
  customer_name TEXT NOT NULL,
  customer_phone TEXT NOT NULL,
  customer_email TEXT DEFAULT NULL,
  delivery_address TEXT NOT NULL,
  prescription_file TEXT DEFAULT NULL,
  status TEXT NOT NULL DEFAULT 'pending',
  total_amount REAL NOT NULL DEFAULT 0.00,
  payment_status TEXT NOT NULL DEFAULT 'cod',
  notes TEXT DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS online_order_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  online_order_id INTEGER NOT NULL,
  medicine_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 1,
  price REAL NOT NULL
);

CREATE TABLE IF NOT EXISTS deliveries (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  tracking_number TEXT NOT NULL UNIQUE,
  sale_id INTEGER DEFAULT NULL,
  online_order_id INTEGER DEFAULT NULL,
  customer_name TEXT NOT NULL,
  customer_phone TEXT NOT NULL,
  delivery_address TEXT NOT NULL,
  delivery_executive_name TEXT NOT NULL,
  delivery_executive_phone TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'assigned',
  delivery_fee REAL NOT NULL DEFAULT 40.00,
  estimated_delivery TEXT DEFAULT NULL,
  delivered_at TEXT DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS documents (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  category TEXT NOT NULL DEFAULT 'Other',
  file_path TEXT NOT NULL,
  file_size_kb INTEGER NOT NULL DEFAULT 0,
  file_type TEXT DEFAULT 'image/jpeg',
  entity_type TEXT DEFAULT NULL,
  entity_id INTEGER DEFAULT NULL,
  uploaded_by INTEGER DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER DEFAULT NULL,
  username TEXT DEFAULT NULL,
  action TEXT NOT NULL,
  module TEXT NOT NULL,
  record_id INTEGER DEFAULT NULL,
  ip_address TEXT DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  details TEXT DEFAULT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  setting_key TEXT NOT NULL UNIQUE,
  setting_value TEXT DEFAULT NULL,
  setting_group TEXT NOT NULL DEFAULT 'general',
  description TEXT DEFAULT NULL,
  updated_at TEXT DEFAULT NULL
);
