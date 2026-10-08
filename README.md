# INFOSOF TECHNOLOGIES - Pharmacy Management Software (v3.0.0)

> Enterprise Pharmacy Management Software built in accordance with the 14-page INFOSOF Module Specification, featuring CodeIgniter MVC Architecture, PHP 8.x, MySQL 8.x / 5.x with automatic zero-config SQLite local dev fallback, Vanilla CSS & Modern JS with legacy IE fallback compatibility, and full Render cloud deployment readiness.

---

## ⚡ Instant Localhost Quickstart

You can run and test the complete application on your local machine in seconds!

### Method 1: 1-Click Batch Runner (Windows)
Simply double-click:
```bat
run-local.bat
```
This automatically starts the built-in development server on `http://localhost:8080`.

---

### Method 2: Standard Command Line
Run the PHP built-in web server from the project directory:
```bash
php -S localhost:8080 -t public
```
Open your browser and navigate to:
👉 **[http://localhost:8080](http://localhost:8080)**

---

## 🔑 Default Login Credentials

| Role | Username | Password | Notes |
|---|---|---|---|
| **Super Administrator** | `admin` | `admin123` | Full system access to all 37 modules |
| **Pharmacist** | `pharmacist` | `admin123` | Rx dispensing, POS counter & medicines |
| **Cashier** | `cashier` | `admin123` | Fast counter billing & payment drawer |
| **Stock Manager** | `stock` | `admin123` | Batches, expiry & inventory management |
| **Accountant** | `accountant` | `admin123` | GSTR-1, GSTR-2, ledgers & expenses |

> 💡 **Quick Test Tip:** On the login page (`/login`), click on **any 1-Click Fast Login badge** at the bottom to sign in as that role instantly without typing!

---

## 💾 Database Configuration

The application includes an intelligent database abstraction layer supporting **both** SQLite and MySQL:

### 1. Zero-Setup Local Development (Default)
In `.env`:
```ini
DB_DRIVER=sqlite
```
- Requires **zero external server setup** (no need to start MySQL or XAMPP).
- Schema and rich seed data with medicines, batches, categories, suppliers, and users are automatically initialized in `database/pharmacy.sqlite`.

### 2. Local MySQL / XAMPP / WAMP
If you prefer running against local MySQL:
1. Start MySQL in XAMPP or service manager.
2. In `.env`, change:
```ini
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=pharmacy_db
DB_USER=root
DB_PASS=
```
The database and tables will be created and seeded automatically on first run! You can also import `database/schema.sql` and `database/seed_data.sql` manually if needed.

---

## 🚀 Cloud Deployment on Render

This repository is pre-configured for seamless 1-click deployment on Render:

1. Push this project to your GitHub/GitLab repository.
2. Log in to [Render](https://render.com) and click **New > Blueprint** (or **New > Web Service**).
3. Connect your repository. Render will automatically detect `render.yaml` and `Dockerfile`.
4. Configure environment variables in the Render Dashboard:
   - `APP_ENV`: `production`
   - `APP_URL`: `https://your-app-name.onrender.com`
   - `DB_DRIVER`: `sqlite` (for instant zero-database deploy) OR `mysql`
   - `DATABASE_URL`: `mysql://user:pass@host:port/dbname` (if attaching a MySQL service)
5. Click **Deploy**. The Docker entrypoint automatically configures Apache to bind to Render's dynamic `$PORT` environment variable.

---

## 📋 Comprehensive 37 Modules Overview

All 37 modules from the INFOSOF specification are implemented and indexed in the sidebar navigation:

1. **User & Role Management (`/users`)** - 9 RBAC roles with granular permissions.
2. **Dashboard Overview (`/dashboard`)** - 12 real-time KPIs, daily revenue, profit margin, stock valuation, and fast alerts.
3. **Store & Pharmacy Profile (`/settings`)** - GSTIN, drug license, invoice header/footer, currency.
4. **Medicine Master (`/medicines`)** - Generic names, HSN codes, storage conditions, Schedule H1 narcotic flags.
5. **Categories & Classification (`/categories`)** - Therapeutic and dosage form classifications.
6. **Batches & Stock Reconciliation (`/batches`)** - Physical count adjustment and discrepancy reasons.
7. **Expiry Management (`/expiry`)** - Color-coded expiry tracking (30/60/90 days), write-offs, supplier returns.
8. **Barcode & Scanning (`/barcode`, `/barcode/print`)** - Real-time scanner lookup, 24/30 sheet printable barcodes.
9. **Suppliers / Distributors (`/suppliers`)** - Drug license numbers, GSTIN, credit limits, balances.
10. **Doctors Management (`/doctors`)** - Medical council registration numbers, specialization.
11. **Patients / Customers (`/patients`)** - Medical history, known drug allergies, chronic diseases, balances.
12. **Prescription Management (`/prescriptions`)** - Upload Rx, doctor verification, doctor signature check.
13. **Prescription-Based Billing (`/prescription-billing`)** - Direct 1-click conversion from Rx to POS counter cart.
14. **Purchases (`/purchases`)** - Supplier invoice entry, batch creation, GST tax credit calculation.
15. **Purchase Orders (`/purchase-orders`)** - Draft -> Approval -> Supplier Dispatch -> 1-click GRN invoice conversion.
16. **Purchase Returns (`/purchase-returns`)** - Damaged/near-expiry stock returns, automatic Debit Note generation.
17. **POS Counter Billing (`/pos`)** - Fast counter UI, FEFO auto-allocation, live cash change calculation, dynamic UPI QR.
18. **Sales Management & Invoicing (`/sales`, `/sales/invoice/(:num)`)** - Dual thermal (80mm/58mm) and standard A4 tax invoice printouts.
19. **Sales Returns (`/sales-returns`)** - Customer return handling, restock verification, Credit Note generation.
20. **Inventory Management (`/inventory`)** - Real-time stock levels, stock movement history ledger.
21. **Smart Reorder Management (`/reorder`)** - Automated safety stock algorithm: `Lead Time Demand + Safety Buffer`.
22. **FEFO / FIFO Stock Management (`/fefo`)** - Automated priority queues preventing near-expiry deadstock.
23. **Medicine Pricing (`/pricing`)** - Purchase vs MRP vs Counter Selling Price, profit margin thresholds.
24. **GST & Tax Management (`/gst`)** - GSTR-1 outward tax liability, GSTR-2 ITC summary, HSN-wise tax breakdown.
25. **Cash Drawer & Payment Register (`/payments`)** - Cash, UPI, Card, Net Banking reconciliation.
26. **Accounts Receivable & Payable (`/accounts`)** - Outstanding customer credit collection, supplier payment entries.
27. **Expense Management (`/expenses`)** - Operating expense categorization, payee tracking, receipts.
28. **Loyalty & Discount Coupons (`/loyalty`)** - Customer reward points calculation, promo codes, minimum spend rules.
29. **Notification & Alert System (`/notifications`)** - Expiry alerts, low stock, pending PO authorizations.
30. **WhatsApp / SMS Gateway (`/integration`)** - Direct click-to-WhatsApp bill link sending, refill reminders.
31. **Online / E-Pharmacy Orders (`/online-orders`)** - Web order intake, prescription review, dispatch status pipeline.
32. **Delivery Management (`/delivery`)** - Rider assignment, cash-on-delivery tracking, address geocodes.
33. **Reports & Analytics (`/reports`)** - Daily sales, fast-moving medicines, expiry loss analysis, export to CSV.
34. **Document Archive (`/documents`)** - Secure storage for supplier bills, drug licenses, inspector certificates.
35. **Audit Trail & Activity Log (`/audit`)** - Tamper-evident logging of every login, sale, price change, and stock write-off.
36. **Security & Backup (`/security`)** - RBAC permission matrix view, 1-click database SQL backup download.
37. **Print Center (`/settings#print-center`)** - Dual mode thermal POS & A4 laser printing configurations.

---

## ⌨️ POS Keyboard Shortcuts

| Shortcut | Function |
|---|---|
| **F2** | Focus search & hardware barcode scanner |
| **F4** | New sale / Clear POS cart |
| **F8** | Jump cursor to payment tender amount |
| **F9** | Complete sale and print tax invoice |
| **Esc** | Close open modal or dialog |

---

## 🛠️ Technology Stack

- **Backend**: PHP 8.x with CodeIgniter standard MVC architecture
- **Database**: MySQL 8.x / 5.x & SQLite 3 (auto-configured)
- **Frontend**: Vanilla CSS Design System with dark/light theme, modern responsive layouts, and legacy browser fallbacks
- **Deployment**: Docker, Apache mod_rewrite, Render Blueprint
