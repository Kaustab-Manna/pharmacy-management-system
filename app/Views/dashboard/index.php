<div class="page-header">
    <div class="page-title">
        <h1>Pharmacy Central Dashboard</h1>
        <p>Real-time clinical metrics, inventory valuations, sales performance, and expiry monitoring.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/pos" class="btn btn-primary" style="box-shadow: 0 4px 14px rgba(13,148,136,0.4);">
            <span>⚡ Counter POS (F2)</span>
        </a>
        <a href="<?= $baseURL ?>/purchases/create" class="btn btn-secondary">
            <span>📥 Add Purchase</span>
        </a>
        <a href="<?= $baseURL ?>/prescriptions/upload" class="btn btn-secondary">
            <span>📝 Upload Rx</span>
        </a>
    </div>
</div>

<!-- Primary Dashboard Stat Cards (All 12 PDF Indicators) -->
<div class="stats-grid">
    <!-- 1. Today's Sales -->
    <div class="stat-card stat-success">
        <div class="stat-info">
            <h3>Today's Sales</h3>
            <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($todaySales['total'], 2) ?></div>
            <div class="stat-subtext">
                <span class="badge-success" style="padding:2px 6px;border-radius:4px;"><?= $todaySales['count'] ?> Invoices</span>
                <span>Rx Sales: <strong><?= $prescriptionSalesCount ?></strong></span>
            </div>
        </div>
        <div class="stat-icon icon-teal">💵</div>
    </div>

    <!-- 2. Today's Purchases -->
    <a href="<?= $baseURL ?>/purchases" style="text-decoration:none;color:inherit;display:block;">
        <div class="stat-card stat-info" style="cursor:pointer;transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="stat-info">
                <h3>Today's Purchases</h3>
                <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($todayPurchases['total'], 2) ?></div>
                <div class="stat-subtext">
                    <span><?= $todayPurchases['count'] ?> Invoices</span>
                    <span style="color:var(--text-muted);">• Pending POs: <?= $pendingPoCount ?></span>
                </div>
            </div>
            <div class="stat-icon icon-blue">📥</div>
        </div>
    </a>

    <!-- 3. Stock Valuation -->
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Stock Value</h3>
            <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($stockValuation['cost_val'], 2) ?></div>
            <div class="stat-subtext">
                <span>MRP Val: <?= $pharmacy['currency_symbol'] ?><?= number_format($stockValuation['mrp_val'], 2) ?></span>
                <span>(<?= $stockValuation['total_units'] ?> Units)</span>
            </div>
        </div>
        <div class="stat-icon icon-purple">🏢</div>
    </div>

    <!-- 4. Daily Profit Indicators -->
    <div class="stat-card stat-success">
        <div class="stat-info">
            <h3>Daily Gross Profit</h3>
            <div class="stat-number" style="color:var(--success);"><?= $pharmacy['currency_symbol'] ?><?= number_format($todayProfit, 2) ?></div>
            <div class="stat-subtext">
                <span class="badge-success" style="padding:2px 6px;border-radius:4px;">Margin: <?= $profitMargin ?>%</span>
                <span>Net of COGS</span>
            </div>
        </div>
        <div class="stat-icon icon-green">📈</div>
    </div>

    <!-- 5. Low-Stock Medicines -->
    <div class="stat-card <?= $lowStockCount > 0 ? 'stat-warning' : '' ?>">
        <div class="stat-info">
            <h3>Low-Stock Alert</h3>
            <div class="stat-number" style="<?= $lowStockCount > 0 ? 'color:var(--warning);' : '' ?>"><?= $lowStockCount ?></div>
            <div class="stat-subtext">
                <a href="<?= $baseURL ?>/reorder" style="text-decoration:underline;">View Smart Reorders &rarr;</a>
            </div>
        </div>
        <div class="stat-icon icon-amber">📉</div>
    </div>

    <!-- 6. Near-Expiry Medicines -->
    <div class="stat-card <?= $nearExpiryCount > 0 ? 'stat-danger' : '' ?>">
        <div class="stat-info">
            <h3>Near-Expiry (30d)</h3>
            <div class="stat-number" style="<?= $nearExpiryCount > 0 ? 'color:var(--danger);' : '' ?>"><?= $nearExpiryCount ?></div>
            <div class="stat-subtext">
                <a href="<?= $baseURL ?>/expiry" style="text-decoration:underline;color:var(--danger);">FEFO / Supplier Return &rarr;</a>
            </div>
        </div>
        <div class="stat-icon icon-red">⏳</div>
    </div>

    <!-- 7. Expired Medicines -->
    <div class="stat-card stat-danger">
        <div class="stat-info">
            <h3>Expired Medicines</h3>
            <div class="stat-number" style="color:var(--danger);"><?= $expiredCount ?></div>
            <div class="stat-subtext">
                <span>Quarantine / Write-Off Required</span>
            </div>
        </div>
        <div class="stat-icon icon-red">🚫</div>
    </div>

    <!-- 8. Receivables & Payables -->
    <div class="stat-card">
        <div class="stat-info">
            <h3>Dues & Payables</h3>
            <div class="stat-number" style="font-size:1.35rem;">
                <span title="Customer Receivables" style="color:var(--info);">Rec: <?= $pharmacy['currency_symbol'] ?><?= number_format($customerReceivables, 0) ?></span>
            </div>
            <div class="stat-subtext">
                <span title="Supplier Payables" style="color:var(--warning);">Payable: <?= $pharmacy['currency_symbol'] ?><?= number_format($supplierOutstanding, 0) ?></span>
            </div>
        </div>
        <div class="stat-icon icon-teal">⚖️</div>
    </div>
</div>

<!-- Middle Section: Recent Transactions & Priority Stock Action Alerts -->
<div class="dashboard-main-grid">
    
    <!-- 12. Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🧾 Recent Sales Transactions</h3>
            <a href="<?= $baseURL ?>/sales" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTransactions)): ?>
                        <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:2rem;">No sales recorded today yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--primary);"><?= htmlspecialchars($tx['invoice_number']) ?></strong>
                                    <?php if (!empty($tx['prescription_id'])): ?>
                                        <span class="fefo-pill" style="font-size:9px;">Rx</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($tx['customer_name'] ?? 'Walk-in') ?></td>
                                <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($tx['grand_total'], 2) ?></strong></td>
                                <td>
                                    <span class="nav-badge badge-info" style="text-transform:uppercase;"><?= htmlspecialchars($tx['payment_mode']) ?></span>
                                </td>
                                <td style="font-size:11px;color:var(--text-muted);"><?= date('H:i', strtotime($tx['sale_date'])) ?></td>
                                <td>
                                    <a href="<?= $baseURL ?>/sales/invoice/<?= $tx['id'] ?>" class="btn btn-sm btn-secondary" title="View & Print">🖨️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Priority Alerts: Low Stock & Expiring Batches -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        
        <!-- Near-Expiry Priority Box -->
        <div class="card">
            <div class="card-header" style="background:#fff1f2;">
                <h3 class="card-title" style="color:#9f1239;font-size:0.95rem;">
                    <span>⏳</span> Urgent Expiry Warnings
                </h3>
                <a href="<?= $baseURL ?>/expiry" class="btn btn-sm btn-secondary" style="font-size:11px;">Manage</a>
            </div>
            <div class="card-body" style="padding:0.75rem 1rem;">
                <?php if (empty($urgentExpiringBatches)): ?>
                    <p style="font-size:12px;color:var(--text-muted);text-align:center;padding:1rem;">All active stocks are within safe expiry windows (>30 days).</p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <?php foreach ($urgentExpiringBatches as $exp): ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 8px;border-bottom:1px solid var(--border-color);font-size:12px;">
                                <div>
                                    <strong><?= htmlspecialchars($exp['medicine_name']) ?></strong>
                                    <div style="color:var(--text-muted);font-size:11px;">Batch: <?= htmlspecialchars($exp['batch_number']) ?> (<?= $exp['quantity'] ?> units left)</div>
                                </div>
                                <div style="text-align:right;">
                                    <span class="nav-badge badge-danger"><?= htmlspecialchars($exp['expiry_date']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock / Reorder Suggestions -->
        <div class="card">
            <div class="card-header" style="background:#fffbeb;">
                <h3 class="card-title" style="color:#92400e;font-size:0.95rem;">
                    <span>📉</span> Critical Low-Stock (Reorder)
                </h3>
                <a href="<?= $baseURL ?>/reorder" class="btn btn-sm btn-secondary" style="font-size:11px;">Smart Reorder</a>
            </div>
            <div class="card-body" style="padding:0.75rem 1rem;">
                <?php if (empty($criticalReorders)): ?>
                    <p style="font-size:12px;color:var(--text-muted);text-align:center;padding:1rem;">Inventory levels healthy across all medicines.</p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <?php foreach ($criticalReorders as $ro): ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 8px;border-bottom:1px solid var(--border-color);font-size:12px;">
                                <div>
                                    <strong><?= htmlspecialchars($ro['name']) ?></strong>
                                    <div style="color:var(--text-muted);font-size:11px;">Min Req: <?= $ro['min_stock_level'] ?> | Current: <span style="color:#ef4444;font-weight:700;"><?= $ro['current_stock'] ?></span></div>
                                </div>
                                <div>
                                    <a href="<?= $baseURL ?>/purchase-orders/create?medicine_id=<?= $ro['id'] ?? 1 ?>" class="btn btn-sm btn-primary" style="font-size:11px;padding:2px 8px;">Order</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
