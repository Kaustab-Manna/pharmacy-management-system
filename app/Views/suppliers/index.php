<div class="page-header">
    <div class="page-title">
        <h1>Suppliers & Distributors Management (Module 9)</h1>
        <p>Maintain pharmaceutical distributor profiles, GST & drug licenses, outstanding dues, and ledger records.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addSupplierModal')">
            <span>➕ Add New Supplier</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Distributor / Company</th>
                    <th>Contact Person</th>
                    <th>Phone / Email</th>
                    <th>GSTIN & Drug License</th>
                    <th>Payment Terms</th>
                    <th>Total Invoiced</th>
                    <th>Current Balance (Dues)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--primary);font-size:0.95rem;"><?= htmlspecialchars($s['company_name']) ?></strong>
                            <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($s['city']) ?>, <?= htmlspecialchars($s['state']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($s['contact_person'] ?? $s['name']) ?></td>
                        <td>
                            <div><?= htmlspecialchars($s['phone']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($s['email'] ?? '') ?></div>
                        </td>
                        <td style="font-size:11px;">
                            <div>GST: <code><?= htmlspecialchars($s['gstin'] ?? 'Pending') ?></code></div>
                            <div>DL: <?= htmlspecialchars($s['drug_license_no'] ?? '-') ?></div>
                        </td>
                        <td><?= $s['payment_terms_days'] ?> Days Net</td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($s['total_purchase_volume'], 2) ?> (<?= $s['total_invoices'] ?> bills)</td>
                        <td>
                            <strong style="<?= $s['current_balance'] > 0 ? 'color:#f59e0b;' : 'color:var(--success);' ?>">
                                <?= $pharmacy['currency_symbol'] ?><?= number_format($s['current_balance'], 2) ?>
                            </strong>
                        </td>
                        <td>
                            <a href="<?= $baseURL ?>/purchases?supplier_id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary" title="View Invoices">📋 Orders</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Supplier -->
<div id="addSupplierModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-header">
            <h3 class="modal-title">➕ Add New Distributor</h3>
            <button class="modal-close" onclick="closeModal('addSupplierModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/suppliers/create">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Distributor / Company Name *</label>
                        <input type="text" name="company_name" class="form-control" placeholder="e.g. Cipla Healthcare Dist" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="Key Account Rep">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 ..." required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="orders@supplier.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">GSTIN</label>
                        <input type="text" name="gstin" class="form-control" placeholder="27AAAAA0000A1Z5">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Drug License No (DL 20B/21B)</label>
                        <input type="text" name="drug_license_no" class="form-control" placeholder="MH-20B-...">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Payment Terms (Days)</label>
                        <input type="number" name="payment_terms_days" class="form-control" value="30">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Warehouse Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Full depot / dispatch address"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addSupplierModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Supplier</button>
            </div>
        </form>
    </div>
</div>
