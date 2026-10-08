<div class="page-header">
    <div class="page-title">
        <h1>Medicine Sales Register</h1>
        <p>Comprehensive record of all retail POS and credit billing transactions.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/pos" class="btn btn-primary">
            <span>⚡ New POS Sale (F2)</span>
        </a>
        <button class="btn btn-secondary" onclick="exportTableToCSV('salesTable', 'sales_register.csv')">
            <span>📥 Export CSV</span>
        </button>
    </div>
</div>

<!-- Sales Statistics Summary -->
<div class="stats-grid">
    <div class="stat-card stat-success">
        <div class="stat-info">
            <h3>Period Total Sales</h3>
            <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($summary['total'], 2) ?></div>
            <div class="stat-subtext"><?= $summary['count'] ?> Completed Invoices</div>
        </div>
        <div class="stat-icon icon-teal">🧾</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Tax Collected</h3>
            <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($summary['tax'], 2) ?></div>
            <div class="stat-subtext">CGST + SGST Breakdown</div>
        </div>
        <div class="stat-icon icon-blue">📑</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Discounts Given</h3>
            <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($summary['discount'], 2) ?></div>
            <div class="stat-subtext">Customer Savings</div>
        </div>
        <div class="stat-icon icon-amber">🎁</div>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <form method="GET" action="<?= $baseURL ?>/sales" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
            <div style="flex:2;min-width:200px;">
                <input type="text" name="search" class="form-control" placeholder="Search by invoice #, customer name, phone..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div style="flex:1;min-width:130px;">
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div style="flex:1;min-width:130px;">
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div style="flex:1;min-width:130px;">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="paid" <?= ($paymentStatus === 'paid') ? 'selected' : '' ?>>Paid</option>
                    <option value="partial" <?= ($paymentStatus === 'partial') ? 'selected' : '' ?>>Partial</option>
                    <option value="unpaid" <?= ($paymentStatus === 'unpaid') ? 'selected' : '' ?>>Unpaid (Credit)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="<?= $baseURL ?>/sales" class="btn btn-sm btn-secondary" style="margin-left:4px;">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Sales Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table-custom" id="salesTable">
            <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Date & Time</th>
                    <th>Customer Name</th>
                    <th>Items</th>
                    <th>Subtotal</th>
                    <th>GST</th>
                    <th>Grand Total</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="10" style="text-align:center;padding:2rem;color:var(--text-muted);">No sales records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($sales as $s): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($s['invoice_number']) ?></strong>
                            </td>
                            <td style="font-size:12px;"><?= htmlspecialchars($s['sale_date']) ?></td>
                            <td>
                                <div><?= htmlspecialchars($s['customer_name'] ?? 'Walk-in') ?></div>
                                <?php if (!empty($s['customer_phone'])): ?>
                                    <div style="font-size:10px;color:var(--text-muted);"><?= htmlspecialchars($s['customer_phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($s['item_count'] ?? 1) ?> items
                                </span>
                            </td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($s['subtotal'], 2) ?></td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($s['tax_amount'], 2) ?></td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($s['grand_total'], 2) ?></strong></td>
                            <td>
                                <span class="badge badge-info" style="text-transform:uppercase;"><?= htmlspecialchars($s['payment_mode']) ?></span>
                            </td>
                            <td>
                                <?php if ($s['payment_status'] === 'paid'): ?>
                                    <span class="badge badge-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Due</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    <a href="<?= $baseURL ?>/sales/invoice/<?= $s['id'] ?>" class="btn btn-sm btn-secondary" title="View & Print">🖨️</a>
                                    <a href="<?= $baseURL ?>/sales-returns/create?sale_id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary" title="Sales Return (Credit Note)">↩️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
