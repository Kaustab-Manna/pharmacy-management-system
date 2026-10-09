<div class="page-header">
    <div class="page-title">
        <h1>Purchase Order Management (PO)</h1>
        <p>Draft purchase orders, submit for approval, track pending delivery dates, and convert directly to purchase invoices.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addPoModal')">
            <span>➕ Create Purchase Order</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Expected Date</th>
                    <th>Estimated Amount</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No purchase orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $po): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($po['po_number']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($po['supplier_name']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($po['order_date']) ?></td>
                            <td><?= htmlspecialchars($po['expected_date']) ?></td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($po['total_amount'], 2) ?></strong></td>
                            <td>
                                <?php if ($po['status'] === 'approved'): ?>
                                    <span class="nav-badge badge-success">Approved</span>
                                <?php elseif ($po['status'] === 'converted_to_invoice'): ?>
                                    <span class="nav-badge badge-info">Converted to Invoice</span>
                                    <?php if (!empty($po['purchase_invoice_number'])): ?>
                                        <div style="font-size:11px;margin-top:2px;">
                                            <a href="<?= $baseURL ?>/purchases" style="color:var(--primary);font-weight:600;text-decoration:none;">Inv: <?= htmlspecialchars($po['purchase_invoice_number']) ?></a>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($po['status'] === 'pending_approval'): ?>
                                    <span class="nav-badge badge-warning">Pending Approval</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-secondary"><?= htmlspecialchars($po['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($po['created_by_name'] ?? 'Staff') ?></td>
                            <td>
                                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                    <?php if ($po['status'] === 'pending_approval'): ?>
                                        <form method="POST" action="<?= $baseURL ?>/purchase-orders/approve/<?= $po['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="action" value="approve_and_convert">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve PO and immediately generate Purchase Invoice">✓ Approve & Invoice</button>
                                        </form>
                                        <form method="POST" action="<?= $baseURL ?>/purchase-orders/approve/<?= $po['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="action" value="only_approve">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Approve only without invoice generation" style="padding:2px 6px;font-size:11px;">Approve Only</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($po['status'] === 'approved'): ?>
                                        <form method="POST" action="<?= $baseURL ?>/purchase-orders/convert/<?= $po['id'] ?>" style="display:inline;">
                                            <button type="submit" class="btn btn-sm btn-primary" title="Receive stock and create Purchase Invoice">📥 Receive / Invoice</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($po['status'] === 'converted_to_invoice'): ?>
                                        <?php if (!empty($po['purchase_invoice_number'])): ?>
                                            <a href="<?= $baseURL ?>/purchases" class="btn btn-sm btn-outline-primary" style="display:inline-flex;align-items:center;gap:4px;" title="View in Purchase Invoices">
                                                🧾 <?= htmlspecialchars($po['purchase_invoice_number']) ?>
                                            </a>
                                        <?php else: ?>
                                            <form method="POST" action="<?= $baseURL ?>/purchase-orders/convert/<?= $po['id'] ?>" style="display:inline;">
                                                <button type="submit" class="btn btn-sm btn-warning" title="Generate Missing Purchase Invoice">📥 Generate Invoice</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create PO -->
<div id="addPoModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 750px;">
        <div class="modal-header">
            <h3 class="modal-title">📋 Create New Purchase Order</h3>
            <button class="modal-close" onclick="closeModal('addPoModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/purchase-orders/create">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Supplier *</label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Expected Delivery Date</label>
                        <input type="date" name="expected_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                </div>

                <!-- PO Item Rows -->
                <div style="margin-top:1rem;">
                    <div style="font-weight:700;font-size:0.85rem;margin-bottom:0.5rem;">Ordered Items:</div>
                    <div id="poItemsBox" style="display:flex;flex-direction:column;gap:8px;">
                        <div class="form-row po-row" style="background:var(--bg-body);padding:8px;border-radius:6px;align-items:center;">
                            <div style="flex:2;">
                                <select name="medicine_id[]" class="form-control" required>
                                    <option value="">Select Medicine</option>
                                    <?php foreach ($medicines as $m): ?>
                                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['brand_name']) ?> (<?= htmlspecialchars($m['name']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex:1;">
                                <input type="number" name="quantity[]" class="form-control" placeholder="Qty" required>
                            </div>
                            <div style="flex:1;">
                                <input type="number" step="0.01" name="expected_rate[]" class="form-control" placeholder="Est Rate (₹)">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Order Notes / Terms</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Deliver to warehouse dock before 4 PM..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addPoModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Purchase Order</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('open') === 'create' || params.has('medicine_id')) {
        openModal('addPoModal');
        var medId = params.get('medicine_id');
        if (medId) {
            var medSelect = document.querySelector('#addPoModal select[name="medicine_id[]"]');
            if (medSelect) {
                medSelect.value = medId;
            }
        }
    }
});
</script>
