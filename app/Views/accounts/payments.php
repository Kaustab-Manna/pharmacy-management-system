<div class="page-header">
    <div class="page-title">
        <h1>Payment & Accounts Register (Module 25)</h1>
        <p>Live cash drawer reconciliation, transaction ledgers, UPI/Card settlements, and cashier register audit.</p>
    </div>
</div>

<!-- Cash Drawer Reconciliation Widgets -->
<div class="stats-grid">
    <div class="stat-card stat-success">
        <div class="stat-info">
            <h3>Expected Cash in Drawer</h3>
            <div class="stat-number" style="color:var(--success);"><?= $pharmacy['currency_symbol'] ?><?= number_format($netCashInDrawer, 2) ?></div>
            <div class="stat-subtext">Cash Sales - Cash Expenses</div>
        </div>
        <div class="stat-icon icon-teal">💵</div>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-info">
            <h3>Today UPI / Digital</h3>
            <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($upiSales, 2) ?></div>
            <div class="stat-subtext">Direct Bank Settlements</div>
        </div>
        <div class="stat-icon icon-blue">📱</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Today Card Swipes</h3>
            <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($cardSales, 2) ?></div>
            <div class="stat-subtext">POS Terminal Batches</div>
        </div>
        <div class="stat-icon icon-purple">💳</div>
    </div>
</div>

<!-- Financial Transactions Ledger Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📜 Payment & Cash Inflow / Outflow Register</h3>
        <button class="btn btn-sm btn-secondary" onclick="exportTableToCSV('paymentsTable', 'payment_register.csv')">Export CSV</button>
    </div>
    <div class="table-responsive">
        <table class="table-custom" id="paymentsTable">
            <thead>
                <tr>
                    <th>Trans Code</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Party / Entity</th>
                    <th>Mode</th>
                    <th>Amount</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No financial transactions logged yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($t['trans_code']) ?></code></td>
                            <td><?= htmlspecialchars($t['trans_date']) ?></td>
                            <td>
                                <?php if ($t['trans_type'] === 'customer_collection'): ?>
                                    <span class="nav-badge badge-success">Collection IN</span>
                                <?php elseif ($t['trans_type'] === 'supplier_payment'): ?>
                                    <span class="nav-badge badge-danger">Supplier OUT</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-secondary"><?= htmlspecialchars($t['trans_type']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($t['entity_name'] ?? 'General') ?></td>
                            <td><span class="nav-badge badge-info" style="text-transform:uppercase;"><?= htmlspecialchars($t['payment_mode']) ?></span></td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($t['amount'], 2) ?></strong></td>
                            <td style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($t['description']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
