<div class="page-header">
    <div class="page-title">
        <h1>Expiry Management & Monitoring</h1>
        <p>Proactive tracking of near-expiry inventory, supplier return window alerts, and quarantine write-offs.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportTableToCSV('expiryTable', 'expiry_risk_report.csv')">
            <span>📥 Export Expiry Report</span>
        </button>
    </div>
</div>

<!-- 4 Expiry Window Cards -->
<div class="stats-grid">
    <a href="<?= $baseURL ?>/expiry?status=expired" style="text-decoration:none;">
        <div class="stat-card stat-danger">
            <div class="stat-info">
                <h3>Expired Stocks</h3>
                <div class="stat-number" style="color:var(--danger);"><?= $expiredStats['count'] ?> <span style="font-size:14px;color:var(--text-muted);">(<?= $expiredStats['units'] ?> units)</span></div>
                <div class="stat-subtext">Value: <?= $pharmacy['currency_symbol'] ?><?= number_format($expiredStats['val'], 2) ?></div>
            </div>
            <div class="stat-icon icon-red">🚫</div>
        </div>
    </a>

    <a href="<?= $baseURL ?>/expiry?days=30" style="text-decoration:none;">
        <div class="stat-card stat-warning">
            <div class="stat-info">
                <h3>Expiring &le; 30 Days</h3>
                <div class="stat-number" style="color:var(--warning);"><?= $within30Stats['count'] ?> <span style="font-size:14px;color:var(--text-muted);">(<?= $within30Stats['units'] ?> units)</span></div>
                <div class="stat-subtext">Value: <?= $pharmacy['currency_symbol'] ?><?= number_format($within30Stats['val'], 2) ?></div>
            </div>
            <div class="stat-icon icon-amber">⏳</div>
        </div>
    </a>

    <a href="<?= $baseURL ?>/expiry?days=60" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Expiring in 31 - 60 Days</h3>
                <div class="stat-number"><?= $within60Stats['count'] ?> <span style="font-size:14px;color:var(--text-muted);">(<?= $within60Stats['units'] ?> units)</span></div>
                <div class="stat-subtext">Value: <?= $pharmacy['currency_symbol'] ?><?= number_format($within60Stats['val'], 2) ?></div>
            </div>
            <div class="stat-icon icon-blue">📅</div>
        </div>
    </a>

    <a href="<?= $baseURL ?>/expiry?days=90" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Expiring in 61 - 90 Days</h3>
                <div class="stat-number"><?= $within90Stats['count'] ?> <span style="font-size:14px;color:var(--text-muted);">(<?= $within90Stats['units'] ?> units)</span></div>
                <div class="stat-subtext">Value: <?= $pharmacy['currency_symbol'] ?><?= number_format($within90Stats['val'], 2) ?></div>
            </div>
            <div class="stat-icon icon-teal">🛡️</div>
        </div>
    </a>
</div>

<!-- Expiry Table with Return / Write-Off Actions -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <span>📋</span> At-Risk Inventory Batches
        </h3>
        <div style="font-size:12px;color:var(--text-muted);">
            Showing batches expiring within <?= $statusFilter === 'expired' ? 'past dates (EXPIRED)' : $days . ' days' ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table-custom" id="expiryTable">
            <thead>
                <tr>
                    <th>Medicine / Brand</th>
                    <th class="nowrap">Batch Number</th>
                    <th class="nowrap">Expiry Date</th>
                    <th class="nowrap">Days Remaining</th>
                    <th class="nowrap">Units At Risk</th>
                    <th class="nowrap">Cost Value</th>
                    <th class="nowrap" style="text-align:center;">Supplier Return</th>
                    <th class="nowrap" style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No batches found in this risk window.</td></tr>
                <?php else: ?>
                    <?php 
                    $todayTime = strtotime(date('Y-m-d'));
                    foreach ($batches as $b): 
                        $expTime = strtotime($b['expiry_date']);
                        $diffDays = round(($expTime - $todayTime) / 86400);
                    ?>
                        <tr style="<?= $diffDays < 0 ? 'background:#fff1f2;' : ($diffDays <= 30 ? 'background:#fffbeb;' : '') ?>">
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($b['brand_name']) ?></strong>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($b['medicine_name']) ?></div>
                            </td>
                            <td class="nowrap">
                                <code style="font-size:13px;"><?= htmlspecialchars($b['batch_number']) ?></code>
                            </td>
                            <td class="nowrap">
                                <strong><?= htmlspecialchars($b['expiry_date']) ?></strong>
                            </td>
                            <td class="nowrap">
                                <?php if ($diffDays < 0): ?>
                                    <span class="nav-badge badge-danger">EXPIRED <?= abs($diffDays) ?> days ago</span>
                                <?php elseif ($diffDays <= 30): ?>
                                    <span class="nav-badge badge-warning"><?= $diffDays ?> days left</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-info"><?= $diffDays ?> days left</span>
                                <?php endif; ?>
                            </td>
                            <td class="nowrap">
                                <strong><?= $b['quantity'] ?></strong> <?= htmlspecialchars($b['unit']) ?>
                            </td>
                            <td class="nowrap">
                                <strong><?= $pharmacy['currency_symbol'] ?><?= number_format($b['quantity'] * $b['purchase_price'], 2) ?></strong>
                            </td>
                            <td class="nowrap" style="text-align:center;">
                                <a href="<?= $baseURL ?>/purchase-returns/create?batch_id=<?= $b['id'] ?>&medicine_id=<?= $b['medicine_id'] ?>&qty=<?= $b['quantity'] ?>" class="btn btn-sm btn-secondary" title="Process Supplier Return">
                                    <span>↩️</span>
                                    <span>Return to Supplier</span>
                                </a>
                            </td>
                            <td class="nowrap" style="text-align:center;">
                                <form method="POST" action="<?= $baseURL ?>/expiry/write-off/<?= $b['id'] ?>" onsubmit="return confirm('Quarantine and write-off batch <?= htmlspecialchars($b['batch_number']) ?>? Stock will be reduced to 0.')" style="margin:0;display:inline;">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Quarantine & Write Off">
                                        <span>🗑️</span>
                                        <span>Write Off</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
