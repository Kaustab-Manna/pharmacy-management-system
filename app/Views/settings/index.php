<div class="page-header">
    <div class="page-title">
        <h1>Store Profile, Settings & Print Center (Modules 3 & 37)</h1>
        <p>Configure legal business credentials, GST & Drug License, thermal receipt headers, and print layouts.</p>
    </div>
</div>

<form method="POST" action="<?= $baseURL ?>/settings/update">
    <div style="display:grid;grid-template-columns:1.3fr 1fr;gap:1.5rem;align-items:start;">
        
        <!-- Pharmacy Profile Information (Module 3) -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🏢 Pharmacy / Company Profile</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Pharmacy Trade Name *</label>
                        <input type="text" name="pharmacy_name" class="form-control" value="<?= htmlspecialchars($pharmacy['pharmacy_name']) ?>" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Legal Entity Name *</label>
                        <input type="text" name="legal_name" class="form-control" value="<?= htmlspecialchars($pharmacy['legal_name']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">GSTIN (Tax ID) *</label>
                        <input type="text" name="gstin" class="form-control" value="<?= htmlspecialchars($pharmacy['gstin']) ?>" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Drug License No (DL 20B / 21B) *</label>
                        <input type="text" name="drug_license_no" class="form-control" value="<?= htmlspecialchars($pharmacy['drug_license_no']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Pharmacy Reg Number</label>
                        <input type="text" name="pharmacy_reg_no" class="form-control" value="<?= htmlspecialchars($pharmacy['pharmacy_reg_no']) ?>">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Qualified Pharmacist Name *</label>
                        <input type="text" name="pharmacist_name" class="form-control" value="<?= htmlspecialchars($pharmacy['pharmacist_name']) ?>" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Pharmacist Reg No</label>
                        <input type="text" name="pharmacist_reg_no" class="form-control" value="<?= htmlspecialchars($pharmacy['pharmacist_reg_no']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Store Street Address *</label>
                    <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($pharmacy['address']) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($pharmacy['city']) ?>">
                    </div>
                    <div class="form-col">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($pharmacy['state']) ?>">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($pharmacy['pincode']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($pharmacy['phone']) ?>">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($pharmacy['mobile']) ?>">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($pharmacy['email']) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice & Print Center Configurations (Module 37) -->
        <div style="display:flex;flex-direction:column;gap:1.5rem;">
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🖨️ Invoice & Printer Settings</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-col">
                            <label class="form-label">Sales Invoice Prefix</label>
                            <input type="text" name="invoice_prefix" class="form-control" value="<?= htmlspecialchars($pharmacy['invoice_prefix']) ?>">
                        </div>
                        <div class="form-col">
                            <label class="form-label">PO Prefix</label>
                            <input type="text" name="po_prefix" class="form-control" value="<?= htmlspecialchars($pharmacy['po_prefix']) ?>">
                        </div>
                        <div class="form-col">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars($pharmacy['currency_symbol']) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Thermal Printer Paper Width</label>
                        <select name="thermal_paper_size" class="form-control">
                            <option value="80mm" <?= ($settings['thermal_paper_size'] ?? '') === '80mm' ? 'selected' : '' ?>>80mm Standard POS Thermal Paper</option>
                            <option value="58mm" <?= ($settings['thermal_paper_size'] ?? '') === '58mm' ? 'selected' : '' ?>>58mm Compact POS Thermal Paper</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Thermal Receipt Footer Message</label>
                        <textarea name="thermal_footer" class="form-control" rows="2"><?= htmlspecialchars($pharmacy['thermal_footer']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Standard Invoice Terms & Conditions</label>
                        <textarea name="terms_conditions" class="form-control" rows="3"><?= htmlspecialchars($pharmacy['terms_conditions']) ?></textarea>
                    </div>
                </div>
                <div class="card-footer" style="display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary">Save All Settings</button>
                </div>
            </div>

            <!-- Print Center Test Buttons -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🧪 Print Center Diagnostics</h3>
                </div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                    <a href="<?= $baseURL ?>/sales/invoice/1?format=standard" target="_blank" class="btn btn-secondary" style="justify-content:center;">
                        📄 Test Print Sample A4 Tax Invoice
                    </a>
                    <a href="<?= $baseURL ?>/sales/invoice/1?format=thermal" target="_blank" class="btn btn-secondary" style="justify-content:center;">
                        🧾 Test Print Sample 80mm Thermal Receipt
                    </a>
                    <a href="<?= $baseURL ?>/barcode/print?batch_id=1&copies=12" target="_blank" class="btn btn-secondary" style="justify-content:center;">
                        🏷️ Test Print Sample Barcode Sticker Sheet
                    </a>
                </div>
            </div>

        </div>

    </div>
</form>
