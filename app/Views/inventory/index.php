<div class="page-header">
    <div class="page-title">
        <h1>Inventory & Real-Time Stock Management (Module 20)</h1>
        <p>Comprehensive overview of warehouse stock, valuations at Cost & MRP, and real-time movement ledgers.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/reorder" class="btn btn-secondary">
            <span>📈 Smart Reorders</span>
        </a>
        <a href="<?= $baseURL ?>/fefo" class="btn btn-secondary">
            <span>🔄 FEFO Engine</span>
        </a>
        <button class="btn btn-primary" onclick="exportTableToCSV('stockTable', 'inventory_valuation.csv')">
            <span>📥 Export Stock CSV</span>
        </button>
    </div>
</div>

<!-- Stock Valuation KPIs -->
<div class="stats-grid">
    <div class="stat-card stat-success">
        <div class="stat-info">
            <h3>Total Stock Valuation (Cost)</h3>
            <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($totals['total_cost'], 2) ?></div>
            <div class="stat-subtext">Total Purchase Asset Value</div>
        </div>
        <div class="stat-icon icon-teal">🏢</div>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-info">
            <h3>Retail Potential Value (MRP)</h3>
            <div class="stat-number"><?= $pharmacy['currency_symbol'] ?><?= number_format($totals['total_mrp'], 2) ?></div>
            <div class="stat-subtext">Projected Gross Realization</div>
        </div>
        <div class="stat-icon icon-blue">🏷️</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Units in Stock</h3>
            <div class="stat-number"><?= number_format($totals['total_units']) ?></div>
            <div class="stat-subtext">Across All Active Batches</div>
        </div>
        <div class="stat-icon icon-purple">📦</div>
    </div>
</div>

<!-- Search & Live Inventory Table -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">📦 Real-Time Medicine Stock Levels</h3>
        <form method="GET" action="<?= $baseURL ?>/inventory" style="display:flex;gap:6px;">
            <input type="text" name="search" class="form-control" placeholder="Search stock..." value="<?= htmlspecialchars($search ?? '') ?>" style="font-size:12px;padding:4px 8px;width:200px;">
            <button type="submit" class="btn btn-sm btn-secondary">Search</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table-custom" id="stockTable">
            <thead>
                <tr>
                    <th>Medicine / Brand</th>
                    <th>Category</th>
                    <th>Pack Size</th>
                    <th>Available Stock</th>
                    <th>Active Batches</th>
                    <th>Earliest Expiry (FEFO)</th>
                    <th>Cost Valuation</th>
                    <th>MRP Valuation</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--primary);"><?= htmlspecialchars($item['brand_name']) ?></strong>
                            <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['code']) ?>)</div>
                        </td>
                        <td>
                            <span class="nav-badge badge-secondary" style="font-size:10px;"><?= htmlspecialchars($item['category_name'] ?? 'General') ?></span>
                        </td>
                        <td><?= htmlspecialchars($item['pack_size']) ?> • <?= htmlspecialchars($item['unit']) ?></td>
                        <td>
                            <strong style="font-size:1.05rem;"><?= $item['current_stock'] ?></strong> <?= htmlspecialchars($item['unit']) ?>
                        </td>
                        <td>
                            <a href="<?= $baseURL ?>/batches?medicine_id=<?= $item['id'] ?>" class="btn btn-sm btn-secondary" style="font-size:11px;padding:2px 6px;">
                                <?= $item['batch_count'] ?> batches
                            </a>
                        </td>
                        <td>
                            <?php if (!empty($item['earliest_expiry'])): ?>
                                <span class="fefo-pill"><?= $item['earliest_expiry'] ?></span>
                            <?php else: ?>
                                <span style="font-size:11px;color:var(--text-muted);">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($item['stock_valuation_cost'], 2) ?></td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($item['stock_valuation_mrp'], 2) ?></td>
                        <td>
                            <?php if ($item['current_stock'] <= 0): ?>
                                <span class="nav-badge badge-danger">Out of Stock</span>
                            <?php elseif ($item['current_stock'] <= $item['min_stock_level']): ?>
                                <span class="nav-badge badge-warning">Low Stock</span>
                            <?php else: ?>
                                <span class="nav-badge badge-success">Optimal</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Stock Movement Audit Ledger (Last 20 Movements) -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📜 Stock Movement & Audit Trail Ledger</h3>
        <span style="font-size:11px;color:var(--text-muted);">Real-time tracking of every in/out quantity modification</span>
    </div>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Medicine</th>
                    <th>Batch #</th>
                    <th>Movement Type</th>
                    <th>Qty Change</th>
                    <th>Previous &rarr; New</th>
                    <th>User</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movements as $m): ?>
                    <tr>
                        <td style="font-size:12px;"><?= htmlspecialchars($m['created_at']) ?></td>
                        <td><strong><?= htmlspecialchars($m['brand_name']) ?></strong></td>
                        <td><code><?= htmlspecialchars($m['batch_number']) ?></code></td>
                        <td>
                            <?php if ($m['movement_type'] === 'purchase'): ?>
                                <span class="nav-badge badge-success">Purchase IN</span>
                            <?php elseif ($m['movement_type'] === 'sale'): ?>
                                <span class="nav-badge badge-info">POS Sale OUT</span>
                            <?php elseif ($m['movement_type'] === 'sale_return'): ?>
                                <span class="nav-badge badge-warning">Return IN</span>
                            <?php elseif ($m['movement_type'] === 'purchase_return'): ?>
                                <span class="nav-badge badge-danger">Supplier Return OUT</span>
                            <?php elseif ($m['movement_type'] === 'expiry_writeoff'): ?>
                                <span class="nav-badge badge-danger">Expiry Write-Off</span>
                            <?php else: ?>
                                <span class="nav-badge badge-secondary"><?= htmlspecialchars($m['movement_type']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="<?= $m['quantity'] > 0 ? 'color:var(--success);' : 'color:var(--danger);' ?>">
                                <?= $m['quantity'] > 0 ? '+' : '' ?><?= $m['quantity'] ?>
                            </strong>
                        </td>
                        <td><?= $m['previous_qty'] ?> &rarr; <strong><?= $m['new_qty'] ?></strong></td>
                        <td><?= htmlspecialchars($m['user_name'] ?? 'System') ?></td>
                        <td style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($m['notes'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
