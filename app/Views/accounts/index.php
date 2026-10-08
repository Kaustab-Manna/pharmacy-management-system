<div class="page-header">
    <div class="page-title">
        <h1>Accounts Receivable & Payable (Module 26)</h1>
        <p>Monitor patient credit balances and supplier procurement invoices with instant settlement tracking.</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-info">
        <div class="stat-info">
            <h3>Total Receivables (Customer Dues)</h3>
            <div class="stat-number" style="color:var(--info);"><?= $pharmacy['currency_symbol'] ?><?= number_format($totalReceivables, 2) ?></div>
            <div class="stat-subtext">Pending Inflow From Credit Patients</div>
        </div>
        <div class="stat-icon icon-blue">👥</div>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-info">
            <h3>Total Payables (Supplier Dues)</h3>
            <div class="stat-number" style="color:var(--warning);"><?= $pharmacy['currency_symbol'] ?><?= number_format($totalPayables, 2) ?></div>
            <div class="stat-subtext">Pending Outflow to Distributors</div>
        </div>
        <div class="stat-icon icon-amber">🚚</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">
    
    <!-- 1. Customer Receivables -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">👥 Customer Outstanding Balances</h3>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Phone</th>
                        <th>Outstanding Due</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($receivables)): ?>
                        <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:1.5rem;">No customer dues pending.</td></tr>
                    <?php else: ?>
                        <?php foreach ($receivables as $cust): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($cust['name']) ?></strong></td>
                                <td><?= htmlspecialchars($cust['phone']) ?></td>
                                <td><strong style="color:#ef4444;"><?= $pharmacy['currency_symbol'] ?><?= number_format($cust['outstanding_balance'], 2) ?></strong></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="openCollectModal(<?= $cust['id'] ?>, '<?= htmlspecialchars(addslashes($cust['name'])) ?>', <?= $cust['outstanding_balance'] ?>)">
                                        Collect
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Supplier Payables -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🚚 Supplier Outstanding Balances</h3>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Supplier Company</th>
                        <th>Phone</th>
                        <th>Payable Due</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payables)): ?>
                        <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:1.5rem;">No supplier balances due.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payables as $supp): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($supp['company_name']) ?></strong></td>
                                <td><?= htmlspecialchars($supp['phone']) ?></td>
                                <td><strong style="color:#f59e0b;"><?= $pharmacy['currency_symbol'] ?><?= number_format($supp['current_balance'], 2) ?></strong></td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="openPaySupplierModal(<?= $supp['id'] ?>, '<?= htmlspecialchars(addslashes($supp['company_name'])) ?>', <?= $supp['current_balance'] ?>)">
                                        Record Payment
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Collect Payment from Customer -->
<div id="collectModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 450px;">
        <div class="modal-header">
            <h3 class="modal-title">💵 Collect Due Payment</h3>
            <button class="modal-close" onclick="closeModal('collectModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/accounts/collect">
            <input type="hidden" id="col_patient_id" name="patient_id">
            <div class="modal-body">
                <div style="margin-bottom:1rem;">
                    Patient: <strong id="col_patient_name" style="color:var(--primary);"></strong><br>
                    Current Due: <strong id="col_due_amount" style="color:#ef4444;"></strong>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Amount Collected *</label>
                    <input type="number" step="0.01" id="col_amount" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" class="form-control">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Receipt Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g. Cleared pending invoice balance">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('collectModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Collection</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Pay Supplier -->
<div id="paySupplierModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 450px;">
        <div class="modal-header">
            <h3 class="modal-title">🚚 Record Supplier Payment</h3>
            <button class="modal-close" onclick="closeModal('paySupplierModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/accounts/pay-supplier">
            <input type="hidden" id="pay_supplier_id" name="supplier_id">
            <div class="modal-body">
                <div style="margin-bottom:1rem;">
                    Supplier: <strong id="pay_supplier_name" style="color:var(--primary);"></strong><br>
                    Current Due: <strong id="pay_due_amount" style="color:#f59e0b;"></strong>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Amount *</label>
                    <input type="number" step="0.01" id="pay_amount" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" class="form-control">
                        <option value="bank_transfer">Bank Transfer / NEFT / RTGS</option>
                        <option value="cheque">Cheque</option>
                        <option value="upi">UPI</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Transaction / Cheque / UTR Ref No</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="e.g. UTR-99120489">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('paySupplierModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCollectModal(id, name, due) {
    document.getElementById('col_patient_id').value = id;
    document.getElementById('col_patient_name').innerText = name;
    document.getElementById('col_due_amount').innerText = '<?= $pharmacy['currency_symbol'] ?>' + parseFloat(due).toFixed(2);
    document.getElementById('col_amount').value = due;
    openModal('collectModal');
}

function openPaySupplierModal(id, name, due) {
    document.getElementById('pay_supplier_id').value = id;
    document.getElementById('pay_supplier_name').innerText = name;
    document.getElementById('pay_due_amount').innerText = '<?= $pharmacy['currency_symbol'] ?>' + parseFloat(due).toFixed(2);
    document.getElementById('pay_amount').value = due;
    openModal('paySupplierModal');
}
</script>
