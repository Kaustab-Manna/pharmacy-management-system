<div class="page-header">
    <div class="page-title">
        <h1>Expense Management (Module 27)</h1>
        <p>Track store operational expenses including shop rent, electricity, salaries, equipment servicing, and bills.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addExpenseModal')">
            <span>➕ Record Expense</span>
        </button>
    </div>
</div>

<!-- Expense Categories KPI Overview -->
<div class="stats-grid">
    <div class="stat-card stat-danger">
        <div class="stat-info">
            <h3>Total Period Expenses</h3>
            <div class="stat-number" style="color:var(--danger);"><?= $pharmacy['currency_symbol'] ?><?= number_format($totalExpenses, 2) ?></div>
            <div class="stat-subtext">All Categories Outflow</div>
        </div>
        <div class="stat-icon icon-red">💸</div>
    </div>
    <?php foreach ($categoryBreakdown as $cat): ?>
        <div class="stat-card">
            <div class="stat-info">
                <h3><?= htmlspecialchars($cat['category']) ?></h3>
                <div class="stat-number" style="font-size:1.4rem;"><?= $pharmacy['currency_symbol'] ?><?= number_format($cat['total'], 2) ?></div>
                <div class="stat-subtext"><?= $cat['count'] ?> entries</div>
            </div>
            <div class="stat-icon icon-blue">📋</div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📜 Expense Ledger</h3>
        <button class="btn btn-sm btn-secondary" onclick="exportTableToCSV('expensesTable', 'expenses_ledger.csv')">Export CSV</button>
    </div>
    <div class="table-responsive">
        <table class="table-custom" id="expensesTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Payee</th>
                    <th>Payment Mode</th>
                    <th>Bill / Ref No</th>
                    <th>Amount</th>
                    <th>Logged By</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No expenses recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($expenses as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['expense_date']) ?></td>
                            <td><span class="nav-badge badge-secondary"><?= htmlspecialchars($e['category']) ?></span></td>
                            <td><strong><?= htmlspecialchars($e['payee']) ?></strong></td>
                            <td><span class="nav-badge badge-info" style="text-transform:uppercase;"><?= htmlspecialchars($e['payment_mode']) ?></span></td>
                            <td><code><?= htmlspecialchars($e['reference_no'] ?? '-') ?></code></td>
                            <td><strong style="color:var(--danger);"><?= $pharmacy['currency_symbol'] ?><?= number_format($e['amount'], 2) ?></strong></td>
                            <td><?= htmlspecialchars($e['created_by_name'] ?? 'Staff') ?></td>
                            <td style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($e['notes'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Record Expense -->
<div id="addExpenseModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">💸 Record Store Expense</h3>
            <button class="modal-close" onclick="closeModal('addExpenseModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/expenses/create" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-control" required>
                            <option value="Electricity">Electricity</option>
                            <option value="Rent">Shop / Premises Rent</option>
                            <option value="Staff Salary">Staff Salary</option>
                            <option value="Transportation">Transportation & Delivery</option>
                            <option value="Maintenance">Maintenance & Servicing</option>
                            <option value="Internet & Software">Internet & Software</option>
                            <option value="Packaging">Packaging Materials</option>
                            <option value="Miscellaneous" selected>Miscellaneous</option>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Expense Date *</label>
                        <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-control">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Payee / Vendor Name *</label>
                    <input type="text" name="payee" class="form-control" placeholder="e.g. Electric Board, Landlord, Service Vendor" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Receipt / Bill Reference No</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="e.g. BILL-891024">
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Receipt Voucher Scan</label>
                    <input type="file" name="receipt_file" class="form-control" accept="image/*,application/pdf">
                </div>

                <div class="form-group">
                    <label class="form-label">Description / Remarks</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Brief reason for expense"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addExpenseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Expense</button>
            </div>
        </form>
    </div>
</div>
