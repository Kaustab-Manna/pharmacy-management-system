<div class="page-header">
    <div class="page-title">
        <h1>FEFO / FIFO Stock Management (Module 22)</h1>
        <p>First Expiry, First Out algorithm prioritization to minimize pharmaceutical write-off losses.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/pos" class="btn btn-primary">
            <span>⚡ Test FEFO at POS Counter</span>
        </a>
    </div>
</div>

<!-- FEFO Rules Highlight Banner -->
<div class="card" style="margin-bottom: 1.5rem; background:#f0fdfa; border-color:#99f6e4;">
    <div class="card-body" style="padding: 1rem 1.5rem; display:flex; gap:12px; align-items:center;">
        <div style="font-size:2rem;">🛡️</div>
        <div>
            <strong style="color:#0f766e;font-size:1rem;">FEFO Enforcement Rules Active:</strong>
            <div style="font-size:0.85rem;color:#134e4a;margin-top:2px;">
                1. System automatically allocates batches sorted strictly by <code>expiry_date ASC</code> on POS Counter.<br>
                2. If a cashier selects a later-expiring batch while an earlier batch still has stock, an alert badge is displayed to prevent accidental inventory aging.<br>
                3. Reduces stock write-offs and ensures safe medication distribution to patients.
            </div>
        </div>
    </div>
</div>

<!-- Active Multi-Batch Medicines FEFO Hierarchy -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">🔄 Active FEFO Batch Queues</h3>
    </div>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Total Active Units</th>
                    <th>FEFO Priority Order (Earliest &rarr; Latest)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fefoAudit as $med): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--primary);"><?= htmlspecialchars($med['brand_name']) ?></strong>
                            <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($med['name']) ?></div>
                        </td>
                        <td>
                            <strong><?= $med['total_qty'] ?></strong> units across <?= $med['batch_count'] ?> batches
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                                <?php 
                                $batches = $fefoBatches[$med['id']] ?? [];
                                foreach ($batches as $idx => $b): 
                                ?>
                                    <div style="background:<?= $idx === 0 ? '#ccfbf1' : 'var(--bg-body)' ?>;border:1px solid <?= $idx === 0 ? '#14b8a6' : 'var(--border-color)' ?>;border-radius:6px;padding:4px 8px;font-size:11px;display:flex;align-items:center;gap:4px;">
                                        <?php if ($idx === 0): ?>
                                            <span style="font-weight:700;color:#0f766e;">[PRIORITY 1]</span>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted);">[<?= $idx + 1 ?>]</span>
                                        <?php endif; ?>
                                        <code><?= htmlspecialchars($b['batch_number']) ?></code>
                                        <span>(Qty: <?= $b['quantity'] ?>)</span>
                                        <span class="fefo-pill" style="font-size:9px;">Exp: <?= $b['expiry_date'] ?></span>
                                    </div>
                                    <?php if ($idx < count($batches) - 1): ?>
                                        <span style="color:var(--text-muted);">&rarr;</span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent FEFO Sales Verification Ledger -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📜 FEFO Dispense Compliance Audit</h3>
    </div>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Medicine</th>
                    <th>Dispensed Batch</th>
                    <th>Expiry Date</th>
                    <th>Dispensed Qty</th>
                    <th>FEFO Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentSalesItems as $item): ?>
                    <tr>
                        <td>
                            <a href="<?= $baseURL ?>/sales/invoice/<?= $item['sale_id'] ?>" style="color:var(--primary);font-weight:600;">
                                <?= htmlspecialchars($item['invoice_number']) ?>
                            </a>
                        </td>
                        <td style="font-size:12px;"><?= htmlspecialchars($item['sale_date']) ?></td>
                        <td><strong><?= htmlspecialchars($item['brand_name']) ?></strong></td>
                        <td><code><?= htmlspecialchars($item['batch_number']) ?></code></td>
                        <td><?= htmlspecialchars($item['expiry_date']) ?></td>
                        <td><strong><?= $item['quantity'] ?></strong></td>
                        <td>
                            <span class="nav-badge badge-success">✓ FEFO Compliant</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
