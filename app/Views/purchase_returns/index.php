<div class="page-header">
    <div class="page-title">
        <h1>Purchase Return Management (Module 16)</h1>
        <p>Return damaged, recalled, or expired medicines to suppliers with automatic inventory and ledger adjustment.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/purchase-returns/create" class="btn btn-primary">
            <span>↩️ New Supplier Return</span>
        </a>
        <button class="btn btn-secondary" onclick="openModal('addPurchaseReturnModal')">
            <span>⚡ Quick Modal</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Debit Note #</th>
                    <th>Supplier</th>
                    <th>Return Date</th>
                    <th>Reason</th>
                    <th>Total Refund Credit</th>
                    <th>Status</th>
                    <th>Processed By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($returns)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No supplier returns recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($returns as $r): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($r['return_number']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($r['supplier_name']) ?></td>
                            <td><?= htmlspecialchars($r['return_date']) ?></td>
                            <td>
                                <span class="nav-badge badge-warning" style="text-transform:uppercase;"><?= htmlspecialchars($r['return_reason']) ?></span>
                            </td>
                            <td>
                                <strong><?= $pharmacy['currency_symbol'] ?><?= number_format($r['total_amount'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="nav-badge badge-success">Completed</span>
                            </td>
                            <td><?= htmlspecialchars($r['created_by_name'] ?? 'Staff') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Supplier Return -->
<div id="addPurchaseReturnModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">↩️ Return Stock to Supplier</h3>
            <button class="modal-close" onclick="closeModal('addPurchaseReturnModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/purchase-returns/create">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Supplier *</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">-- Choose Supplier --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Select Medicine Batch *</label>
                    <select name="batch_id" class="form-control" required>
                        <option value="">-- Choose Batch --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>">
                                <?= htmlspecialchars($b['brand_name']) ?> (Batch: <?= htmlspecialchars($b['batch_number']) ?> - Avail: <?= $b['quantity'] ?> units - Exp: <?= $b['expiry_date'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Return Quantity *</label>
                        <input type="number" name="quantity" class="form-control" placeholder="Qty" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Return Reason *</label>
                        <select name="return_reason" class="form-control">
                            <option value="expired">Expired Stock</option>
                            <option value="damaged">Damaged / Broken Packaging</option>
                            <option value="recall">Manufacturer Quality Recall</option>
                            <option value="excess">Excess Overstock</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes & Remarks</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Near-expiry batch returned as per distributor agreement..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addPurchaseReturnModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Process Return & Adjust Balance</button>
            </div>
        </form>
    </div>
</div>
