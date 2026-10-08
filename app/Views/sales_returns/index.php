<div class="page-header">
    <div class="page-title">
        <h1>Sales Return Management & Credit Notes (Module 19)</h1>
        <p>Customer medicine returns, automated stock reinstatement to batches, and refund issuance.</p>
    </div>
</div>

<!-- Select Invoice to Return -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">🔍 Process Return by Original Sales Invoice</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= $baseURL ?>/sales-returns/create" style="display:flex;gap:1rem;align-items:flex-end;">
            <div style="flex:1;">
                <label class="form-label">Select Original Sale Invoice</label>
                <select name="sale_id" class="form-control" required>
                    <option value="">-- Choose Sales Invoice --</option>
                    <?php foreach ($recentSales as $s): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= htmlspecialchars($s['invoice_number']) ?> - <?= htmlspecialchars($s['customer_name'] ?? 'Walk-in') ?> (<?= $pharmacy['currency_symbol'] ?><?= number_format($s['grand_total'], 2) ?> on <?= $s['sale_date'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Load Invoice Items &rarr;</button>
        </form>
    </div>
</div>

<!-- Returns History -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📜 Processed Customer Returns (Credit Notes)</h3>
    </div>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Credit Note #</th>
                    <th>Original Invoice #</th>
                    <th>Customer</th>
                    <th>Return Date</th>
                    <th>Refund Amount</th>
                    <th>Refund Mode</th>
                    <th>Processed By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($returns)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No sales returns recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($returns as $r): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($r['return_number']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($r['invoice_number']) ?></td>
                            <td><?= htmlspecialchars($r['patient_name'] ?? 'Walk-in') ?></td>
                            <td><?= htmlspecialchars($r['return_date']) ?></td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($r['total_amount'], 2) ?></strong></td>
                            <td>
                                <span class="nav-badge badge-info" style="text-transform:uppercase;"><?= htmlspecialchars($r['refund_type']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($r['created_by_name'] ?? 'Cashier') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
