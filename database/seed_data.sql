-- =======================================================
-- INFOSOF TECHNOLOGIES - Pharmacy Management Software
-- Comprehensive Sample Seed Data
-- =======================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------
-- Users (Password for all accounts is: admin123)
-- -------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `phone`, `is_active`, `created_at`) VALUES
(1, 'superadmin', 'superadmin@infosofpharmacy.com', '$2y$10$nqN/o0ExLMvWhU4e4aaKuerasJDAc70JTUGkGL9.IobNjURrAvTkq', 'System Super Admin', 'superadmin', '+91 98200 00001', 1, NOW()),
(2, 'admin', 'admin@infosofpharmacy.com', '$2y$10$nqN/o0ExLMvWhU4e4aaKuerasJDAc70JTUGkGL9.IobNjURrAvTkq', 'Ramesh Iyer (Pharmacy Admin)', 'pharmacy_admin', '+91 98200 00002', 1, NOW()),
(3, 'pharmacist', 'pharmacist@infosofpharmacy.com', '$2y$10$nqN/o0ExLMvWhU4e4aaKuerasJDAc70JTUGkGL9.IobNjURrAvTkq', 'Dr. Rajesh Sharma (Pharmacist)', 'pharmacist', '+91 98200 00003', 1, NOW()),
(4, 'store', 'store@infosofpharmacy.com', '$2y$10$nqN/o0ExLMvWhU4e4aaKuerasJDAc70JTUGkGL9.IobNjURrAvTkq', 'Vikram Rathore (Warehouse Mgr)', 'store_manager', '+91 98200 00004', 1, NOW()),
(5, 'purchase', 'purchase@infosofpharmacy.com', '$2y$10$nqN/o0ExLMvWhU4e4aaKuerasJDAc70JTUGkGL9.IobNjURrAvTkq', 'Sanjay Gupta (Purchase Mgr)', 'purchase_manager', '+91 98200 00005', 1, NOW()),
(6, 'billing', 'billing@infosofpharmacy.com', '$2y$10$nqN/o0ExLMvWhU4e4aaKuerasJDAc70JTUGkGL9.IobNjURrAvTkq', 'Neha Joshi (Billing Exec)', 'billing_executive', '+91 98200 00006', 1, NOW()),
(7, 'cashier', 'cashier@infosofpharmacy.com', '$2y$10$nqN/o0ExLMvWhU4e4aaKuerasJDAc70JTUGkGL9.IobNjURrAvTkq', 'Arjun Kapoor (Cashier)', 'cashier', '+91 98200 00007', 1, NOW()),
(8, 'accountant', 'accountant@infosofpharmacy.com', '$2y$10$nqN/o0ExLMvWhU4e4aaKuerasJDAc70JTUGkGL9.IobNjURrAvTkq', 'Manish Agarwal (Accountant)', 'accountant', '+91 98200 00008', 1, NOW()),
(9, 'sales', 'sales@infosofpharmacy.com', '$2y$10$nqN/o0ExLMvWhU4e4aaKuerasJDAc70JTUGkGL9.IobNjURrAvTkq', 'Sunita Rao (Sales Staff)', 'sales_staff', '+91 98200 00009', 1, NOW())
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- -------------------------------------------------------
-- Pharmacy Profile
-- -------------------------------------------------------
INSERT INTO `pharmacy_profile` (`id`, `pharmacy_name`, `legal_name`, `gstin`, `drug_license_no`, `pharmacy_reg_no`, `pharmacist_name`, `pharmacist_reg_no`, `address`, `city`, `state`, `country`, `pincode`, `phone`, `mobile`, `email`, `website`, `invoice_prefix`, `po_prefix`, `currency_symbol`, `tax_inclusive`, `thermal_header`, `thermal_footer`, `terms_conditions`)
VALUES (
  1,
  'INFOSOF Care Pharmacy & Medico',
  'INFOSOF Healthcare Solutions Private Limited',
  '27AAACI1234F1Z9',
  'MH-MZ1-20B-184920 / MH-MZ1-21B-184921',
  'PHARM-MH-2023-90812',
  'Dr. Rajesh Sharma (Reg. Pharmacist)',
  'PCI-MAH-882910',
  'Shop 12-14, Health Plaza, M.G. Road, Bandra West',
  'Mumbai',
  'Maharashtra',
  'India',
  '400050',
  '+91 22 2640 1199',
  '+91 98200 12345',
  'care@infosofpharmacy.com',
  'https://pharmacy.infosof.com',
  'INV-',
  'PO-',
  '₹',
  1,
  'INFOSOF CARE PHARMACY\nShop 12-14 Health Plaza, Bandra West, Mumbai\nGSTIN: 27AAACI1234F1Z9 | DL: 20B/21B-184920',
  'Thank you for your purchase!\nMedicines once sold cannot be returned without original cash memo.\nKeep medicines out of reach of children.',
  '1. Goods once sold will be accepted for return within 7 days with original bill.\n2. Refrigerated & cold-chain items will not be taken back.\n3. Prescriptions are mandatory for Schedule H & X medications.'
) ON DUPLICATE KEY UPDATE `pharmacy_name` = VALUES(`pharmacy_name`);

-- -------------------------------------------------------
-- Categories / Therapeutic Classification
-- -------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Antibiotics', 'antibiotics', 'Antimicrobial medicines fighting bacterial infections'),
(2, 'Antacids & Gastrointestinal', 'antacids', 'Acidity, GERD, and stomach ulcer relief medicines'),
(3, 'Vitamins & Supplements', 'vitamins', 'Nutritional health boosters, minerals & multivitamin supplements'),
(4, 'Cardiovascular Medicines', 'cardiovascular', 'Hypertension, cardiac care & blood pressure regulators'),
(5, 'Diabetic Medicines', 'diabetic', 'Oral anti-diabetic formulations & insulin analogues'),
(6, 'Dermatological Medicines', 'dermatological', 'Topical ointments, antibacterial gels & skin solutions'),
(7, 'Surgical & First Aid', 'surgical', 'Bandages, antiseptic swabs, surgical cotton & dressings'),
(8, 'Medical Devices', 'medical-devices', 'Blood glucose meters, BP monitors, nebulizers & thermometers'),
(9, 'Analgesics & Anti-Inflammatory', 'analgesics', 'Pain killers, fever reducers & muscle relaxants'),
(10, 'Respiratory & Antiallergic', 'respiratory', 'Cough syrups, anti-histamines, inhalers & bronchodilators')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- -------------------------------------------------------
-- Medicines Master
-- -------------------------------------------------------
INSERT INTO `medicines` (`id`, `code`, `name`, `brand_name`, `category_id`, `dosage_form`, `strength`, `composition`, `manufacturer`, `pack_size`, `unit`, `hsn_code`, `gst_rate`, `requires_prescription`, `min_stock_level`, `reorder_level`) VALUES
(1, 'MED1001', 'Amoxicillin 500mg', 'Amoxil 500', 1, 'Capsule', '500 mg', 'Amoxicillin Trihydrate IP 500mg', 'GlaxoSmithKline (GSK)', '1x10 Caps', 'Strip', '300410', 12.00, 1, 30, 80),
(2, 'MED1002', 'Paracetamol 650mg', 'Dolo 650', 9, 'Tablet', '650 mg', 'Paracetamol IP 650mg', 'Micro Labs Ltd', '1x15 Tabs', 'Strip', '300490', 12.00, 0, 50, 150),
(3, 'MED1003', 'Azithromycin 500mg', 'Azee 500', 1, 'Tablet', '500 mg', 'Azithromycin Dihydrate IP 500mg', 'Cipla Ltd', '1x5 Tabs', 'Strip', '300420', 12.00, 1, 25, 60),
(4, 'MED1004', 'Metformin 500mg SR', 'Glycomet 500 SR', 5, 'Tablet', '500 mg', 'Metformin Hydrochloride IP 500mg Extended Release', 'USV Private Limited', '1x20 Tabs', 'Strip', '300490', 5.00, 1, 40, 100),
(5, 'MED1005', 'Pantoprazole & Domperidone', 'Pan-D', 2, 'Capsule', '40mg + 30mg', 'Pantoprazole Sodium 40mg + Domperidone 30mg SR', 'Alkem Laboratories', '1x15 Caps', 'Strip', '300490', 12.00, 1, 35, 90),
(6, 'MED1006', 'Atorvastatin 10mg', 'Atorva 10', 4, 'Tablet', '10 mg', 'Atorvastatin Calcium IP 10mg', 'Zydus Cadila', '1x15 Tabs', 'Strip', '300490', 12.00, 1, 20, 50),
(7, 'MED1007', 'Amlodipine 5mg', 'Amlong 5', 4, 'Tablet', '5 mg', 'Amlodipine Besylate IP 5mg', 'Micro Labs Ltd', '1x15 Tabs', 'Strip', '300490', 12.00, 1, 25, 60),
(8, 'MED1008', 'Cetirizine 10mg', 'Cetzine 10', 10, 'Tablet', '10 mg', 'Cetirizine Hydrochloride IP 10mg', 'Dr. Reddy Laboratories', '1x10 Tabs', 'Strip', '300490', 12.00, 0, 30, 80),
(9, 'MED1009', 'Vitamin C 500mg Chewable', 'Limcee 500', 3, 'Tablet', '500 mg', 'Ascorbic Acid IP 100mg + Sodium Ascorbate 450mg', 'Abbott India', '1x15 Tabs', 'Strip', '300450', 12.00, 0, 40, 100),
(10, 'MED1010', 'Betadine Ointment 5%', 'Betadine 5% 20g', 6, 'Ointment', '5% w/w', 'Povidone Iodine IP 5% w/w', 'Win-Medicare', '20g Tube', 'Tube', '300490', 12.00, 0, 15, 40),
(11, 'MED1011', 'Sterile Roller Bandage', 'SafeGauze 10cm', 7, 'Bandage', '10cm x 3m', 'Pure absorbent surgical cotton gauze roll', 'Johnson & Johnson Medical', '1 Roll', 'Piece', '300590', 5.00, 0, 20, 60),
(12, 'MED1012', 'Digital Flex Thermometer', 'Omron MC-246', 8, 'Device', 'Standard', 'Rapid digital temperature measurement sensor', 'Omron Healthcare', '1 Unit', 'Piece', '902519', 18.00, 0, 10, 25)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- -------------------------------------------------------
-- Batches (FEFO: Earliest Expiry First, Some Near-Expiry & Expired)
-- -------------------------------------------------------
INSERT INTO `batches` (`id`, `medicine_id`, `batch_number`, `mfg_date`, `expiry_date`, `quantity`, `initial_quantity`, `purchase_price`, `selling_price`, `mrp`, `barcode`, `status`) VALUES
-- Paracetamol Dolo 650 (Safe batch)
(1, 2, 'DOLO24A', '2024-01-10', '2026-12-31', 120, 200, 24.50, 31.50, 33.60, '890123456002', 'active'),
-- Paracetamol Dolo 650 (Earlier batch - FEFO test)
(2, 2, 'DOLO23Z', '2023-11-05', '2025-05-15', 35, 100, 22.00, 30.00, 33.60, '890123456022', 'active'),
-- Amoxicillin (Near expiry batch: ~20 days from now for alert testing)
(3, 1, 'AMX23K', '2023-04-10', '2026-10-25', 18, 100, 62.00, 78.00, 85.00, '890123456001', 'active'),
-- Amoxicillin (Fresh batch)
(4, 1, 'AMX24C', '2024-03-01', '2027-02-28', 95, 150, 65.00, 80.00, 88.00, '890123456011', 'active'),
-- Azithromycin (Expired batch for testing expiry module)
(5, 3, 'AZI22M', '2022-09-01', '2024-08-31', 12, 50, 95.00, 115.00, 128.00, '890123456003', 'expired'),
-- Azithromycin (Good batch)
(6, 3, 'AZI24P', '2024-02-15', '2026-08-31', 65, 80, 102.00, 122.00, 134.00, '890123456033', 'active'),
-- Glycomet 500 SR
(7, 4, 'GLY24D', '2024-01-20', '2026-11-30', 85, 120, 38.00, 48.00, 52.00, '890123456004', 'active'),
-- Pan-D
(8, 5, 'PAND24H', '2024-02-01', '2026-10-15', 72, 100, 140.00, 175.00, 195.00, '890123456005', 'active'),
-- Atorva 10
(9, 6, 'ATV24J', '2024-03-10', '2027-01-31', 45, 60, 88.00, 110.00, 122.00, '890123456006', 'active'),
-- Amlong 5
(10, 7, 'AML24A', '2024-02-18', '2026-12-31', 50, 75, 42.00, 54.00, 60.00, '890123456007', 'active'),
-- Cetzine 10 (Low stock test)
(11, 8, 'CTZ23Q', '2023-10-01', '2026-09-30', 14, 80, 18.00, 24.00, 26.50, '890123456008', 'active'),
-- Limcee 500
(12, 9, 'LIM24E', '2024-04-01', '2026-10-31', 110, 150, 19.50, 26.00, 28.50, '890123456009', 'active'),
-- Betadine 5%
(13, 10, 'BET24R', '2024-01-15', '2027-01-15', 30, 40, 62.00, 78.00, 85.00, '890123456010', 'active'),
-- SafeGauze
(14, 11, 'GAU24A', '2024-01-01', '2028-12-31', 48, 60, 15.00, 22.00, 25.00, '890123456011', 'active'),
-- Omron MC-246
(15, 12, 'OMR24X', '2024-01-01', '2029-12-31', 18, 25, 190.00, 260.00, 290.00, '890123456012', 'active')
ON DUPLICATE KEY UPDATE `batch_number` = VALUES(`batch_number`);

-- -------------------------------------------------------
-- Suppliers / Distributors
-- -------------------------------------------------------
INSERT INTO `suppliers` (`id`, `name`, `company_name`, `contact_person`, `phone`, `email`, `address`, `city`, `state`, `gstin`, `drug_license_no`, `payment_terms_days`, `current_balance`) VALUES
(1, 'Cipla Pharma Dist', 'Cipla Health Distributors Pvt Ltd', 'Amit Saxena', '+91 98211 44551', 'orders@cipladist.com', 'Plot 45, MIDC Industrial Area, Andheri East', 'Mumbai', 'Maharashtra', '27AAACC4910D1ZX', 'MH-20B-77182', 30, 42500.00),
(2, 'Sun Care Wholesalers', 'Sun Pharma Wholesale Agencies', 'Rajesh Singhania', '+91 98211 88992', 'sales@suncareagencies.com', 'Warehouse 10, Bhiwandi Logistics Park', 'Thane', 'Maharashtra', '27AAACS8912M1Z4', 'MH-20B-88291', 21, 18200.00),
(3, 'Micro Medico Agency', 'Micro Labs Regional Depot', 'Kishore Joshi', '+91 98211 22334', 'info@micromedico.in', 'Shop 4, Commercial Chambers, Dadar West', 'Mumbai', 'Maharashtra', '27AAACM3312K1ZY', 'MH-20B-99120', 15, 8750.00),
(4, 'Abbott Healthcare Supply', 'Abbott India Authorized Logistics', 'Deepak Verma', '+91 98211 66778', 'supply@abbottlogistics.com', 'Godown 18, Vashi APMC Market', 'Navi Mumbai', 'Maharashtra', '27AAACA9921E1Z8', 'MH-20B-66512', 30, 0.00)
ON DUPLICATE KEY UPDATE `company_name` = VALUES(`company_name`);

-- -------------------------------------------------------
-- Doctors
-- -------------------------------------------------------
INSERT INTO `doctors` (`id`, `name`, `registration_number`, `specialization`, `hospital_clinic`, `phone`, `email`, `address`) VALUES
(1, 'Dr. Arvind Patel, MD', 'MCI-MAH-29182', 'Cardiology & Internal Medicine', 'Lilavati Hospital & Research Centre', '+91 98201 11223', 'arvind.patel@lilavati.org', 'Bandra West, Mumbai'),
(2, 'Dr. Sneha Kulkarni, MBBS, DNB', 'MCI-MAH-44912', 'General Physician & Diabetologist', 'Apollo Clinic', '+91 98201 33445', 'sneha.kulkarni@apollo.com', 'Santacruz West, Mumbai'),
(3, 'Dr. Rohan Mehta, MD, DCH', 'MCI-MAH-55109', 'Pediatrician & Child Health', 'Holy Family Hospital', '+91 98201 77889', 'rohan.mehta@pediatriccare.in', 'Bandra, Mumbai')
ON DUPLICATE KEY UPDATE `registration_number` = VALUES(`registration_number`);

-- -------------------------------------------------------
-- Patients / Customers
-- -------------------------------------------------------
INSERT INTO `patients` (`id`, `patient_code`, `name`, `age`, `gender`, `phone`, `email`, `address`, `allergies`, `chronic_conditions`, `blood_group`, `loyalty_points`, `loyalty_tier`, `outstanding_balance`) VALUES
(1, 'PAT-1001', 'Rahul Sharma', 42, 'Male', '+91 98202 11111', 'rahul.sharma@gmail.com', 'Flat 402, Sea View Apts, Bandra', 'Penicillin Allergy', 'Type 2 Diabetes, Mild Hypertension', 'B+', 145, 'Silver', 0.00),
(2, 'PAT-1002', 'Priya Verma', 34, 'Female', '+91 98202 22222', 'priya.verma@yahoo.com', 'B-12, Greenfield Society, Khar', 'Sulfa drugs', 'Asthma', 'O+', 320, 'Gold', 450.00),
(3, 'PAT-1003', 'Anita Desai', 68, 'Female', '+91 98202 33333', 'anita.desai@gmail.com', '701, Sunshine Heights, Santacruz', 'None reported', 'Hypertension, Osteoarthritis', 'A+', 85, 'Bronze', 0.00),
(4, 'PAT-1004', 'Vikram Singh', 28, 'Male', '+91 98202 44444', 'vikram.singh@outlook.com', 'Room 15, Chawl 3, Bandra East', 'None', 'None', 'AB+', 210, 'Silver', 120.00)
ON DUPLICATE KEY UPDATE `patient_code` = VALUES(`patient_code`);

-- -------------------------------------------------------
-- Prescriptions
-- -------------------------------------------------------
INSERT INTO `prescriptions` (`id`, `prescription_number`, `patient_id`, `doctor_id`, `prescription_date`, `status`, `pharmacist_id`, `pharmacist_notes`) VALUES
(1, 'RX-2026-001', 1, 2, '2026-09-28', 'verified', 3, 'Patient verified. Instructions explained for Metformin after dinner.'),
(2, 'RX-2026-002', 2, 1, '2026-09-30', 'verified', 3, 'Cardio maintenance therapy. Monitored BP reading normal.'),
(3, 'RX-2026-003', 3, 2, '2026-10-01', 'pending', NULL, 'Awaiting pharmacist review.')
ON DUPLICATE KEY UPDATE `prescription_number` = VALUES(`prescription_number`);

INSERT INTO `prescription_items` (`id`, `prescription_id`, `medicine_id`, `dosage`, `frequency`, `duration`, `qty_prescribed`, `qty_dispensed`, `instructions`) VALUES
(1, 1, 4, '1 Tablet', '1-0-1 (After Meals)', '15 Days', 30, 30, 'Take with food to avoid gastric irritation'),
(2, 1, 5, '1 Capsule', '1-0-0 (Empty Stomach)', '15 Days', 15, 15, 'Take 30 mins before morning breakfast'),
(3, 2, 6, '1 Tablet', '0-0-1 (At Bedtime)', '30 Days', 30, 30, 'Regular cholesterol monitoring'),
(4, 2, 7, '1 Tablet', '1-0-0 (Morning)', '30 Days', 30, 30, 'BP control medication'),
(5, 3, 2, '1 Tablet', '1-0-1 (SOS / Fever)', '5 Days', 10, 0, 'Take when temperature exceeds 100 F')
ON DUPLICATE KEY UPDATE `dosage` = VALUES(`dosage`);

-- -------------------------------------------------------
-- Sales & POS Transactions
-- -------------------------------------------------------
INSERT INTO `sales` (`id`, `invoice_number`, `prescription_id`, `patient_id`, `customer_name`, `customer_phone`, `doctor_id`, `doctor_name`, `sale_date`, `subtotal`, `tax_amount`, `cgst_amount`, `sgst_amount`, `discount_amount`, `round_off`, `grand_total`, `paid_amount`, `payment_mode`, `payment_status`, `cashier_id`) VALUES
(1, 'INV-2026-0001', 1, 1, 'Rahul Sharma', '+91 98202 11111', 2, 'Dr. Sneha Kulkarni', '2026-09-28 11:30:00', 319.00, 28.50, 14.25, 14.25, 15.00, 0.50, 333.00, 333.00, 'upi', 'paid', 7),
(2, 'INV-2026-0002', 2, 2, 'Priya Verma', '+91 98202 22222', 1, 'Dr. Arvind Patel', '2026-09-30 17:15:00', 328.00, 39.36, 19.68, 19.68, 20.00, 0.64, 348.00, 348.00, 'card', 'paid', 7),
(3, 'INV-2026-0003', NULL, NULL, 'Walk-in Cash Customer', NULL, NULL, NULL, '2026-10-01 10:15:00', 63.00, 7.56, 3.78, 3.78, 0.00, 0.44, 71.00, 71.00, 'cash', 'paid', 6)
ON DUPLICATE KEY UPDATE `invoice_number` = VALUES(`invoice_number`);

INSERT INTO `sale_items` (`id`, `sale_id`, `medicine_id`, `batch_id`, `quantity`, `unit_price`, `mrp`, `discount_percent`, `gst_rate`, `cgst_amount`, `sgst_amount`, `total_amount`) VALUES
(1, 1, 4, 7, 2, 48.00, 52.00, 0.00, 5.00, 2.40, 2.40, 100.80),
(2, 1, 5, 8, 1, 175.00, 195.00, 0.00, 12.00, 10.50, 10.50, 196.00),
(3, 2, 6, 9, 2, 110.00, 122.00, 5.00, 12.00, 12.54, 12.54, 234.08),
(4, 2, 7, 10, 2, 54.00, 60.00, 5.00, 12.00, 6.16, 6.16, 114.92),
(5, 3, 2, 1, 2, 31.50, 33.60, 0.00, 12.00, 3.78, 3.78, 70.56)
ON DUPLICATE KEY UPDATE `unit_price` = VALUES(`unit_price`);

-- -------------------------------------------------------
-- Expenses
-- -------------------------------------------------------
INSERT INTO `expenses` (`id`, `expense_date`, `category`, `amount`, `payment_mode`, `payee`, `reference_no`, `notes`, `created_by`) VALUES
(1, '2026-09-05', 'Rent', 35000.00, 'bank_transfer', 'Health Plaza Management Ltd', 'CHQ-981203', 'Pharmacy premises rent for Sept 2026', 8),
(2, '2026-09-10', 'Electricity', 8450.00, 'upi', 'Adani Electricity Mumbai Ltd', 'BILL-8891024', 'Power bill for AC and medicine cold storage', 8),
(3, '2026-09-15', 'Maintenance', 2200.00, 'cash', 'CoolCare Services', 'CASH-REC-11', 'Quarterly servicing of vaccine refrigerator', 8)
ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);

-- -------------------------------------------------------
-- Loyalty Coupons
-- -------------------------------------------------------
INSERT INTO `loyalty_coupons` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `expiry_date`, `is_active`) VALUES
(1, 'WELCOME10', 'Flat 10% discount for first time registered customers', 'percentage', 10.00, 200.00, 100.00, '2026-12-31', 1),
(2, 'SENIORCARE', 'Special 15% discount for senior citizens', 'percentage', 15.00, 500.00, 250.00, '2026-12-31', 1),
(3, 'HEALTH50', 'Flat ₹50 off on orders above ₹600', 'fixed', 50.00, 600.00, 50.00, '2026-12-31', 1)
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`);

-- -------------------------------------------------------
-- Notifications
-- -------------------------------------------------------
INSERT INTO `notifications` (`id`, `type`, `title`, `message`, `link`, `severity`, `is_read`, `created_at`) VALUES
(1, 'near_expiry', 'Near-Expiry Warning: Amoxil 500', 'Batch AMX23K expires on 2026-10-25 (within 24 days). Consider supplier return or priority sale.', 'batches?filter=near_expiry', 'warning', 0, NOW()),
(2, 'expired', 'Expired Stock Alert: Azee 500', 'Batch AZI22M has expired on 2024-08-31. Stock must be quarantined or returned.', 'expiry', 'danger', 0, NOW()),
(3, 'low_stock', 'Low Stock Alert: Cetzine 10', 'Current stock (14 units) is below minimum threshold (30 units). Recommended reorder: 80 units.', 'reorder', 'warning', 0, NOW()),
(4, 'prescription_pending', 'New Prescription Awaiting Verification', 'Prescription RX-2026-003 for Anita Desai requires pharmacist review.', 'prescriptions', 'info', 0, NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- -------------------------------------------------------
-- System Settings
-- -------------------------------------------------------
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `description`) VALUES
(1, 'theme_mode', 'light', 'appearance', 'Default interface theme (light or dark)'),
(2, 'thermal_paper_size', '80mm', 'printing', 'Thermal printer paper width (80mm or 58mm)'),
(3, 'enable_fefo_warning', '1', 'billing', 'Prompt warning when selecting non-earliest batch'),
(4, 'auto_backup_enabled', '1', 'backup', 'Enable automatic database dump scheduling'),
(5, 'loyalty_earn_rate', '1', 'loyalty', 'Loyalty points earned per 100 currency units spent'),
(6, 'loyalty_redeem_value', '1.00', 'loyalty', 'Cash value of 1 loyalty point'),
(7, 'barcode_symbology', 'CODE128', 'barcode', 'Default barcode symbology'),
(8, 'whatsapp_enabled', '1', 'integration', 'Enable WhatsApp click-to-chat sharing')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

SET FOREIGN_KEY_CHECKS = 1;
