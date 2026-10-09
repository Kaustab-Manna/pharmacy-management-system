<div class="page-header">
    <div class="page-title">
        <h1>Purchase Management & Invoices</h1>
        <p>Record supplier invoices, receive medicine shipments, create batches, and manage accounts payable.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/purchase-orders" class="btn btn-secondary">
            <span>📋 Purchase Orders (PO)</span>
        </a>
        <a href="<?= $baseURL ?>/purchases/create" class="btn btn-primary">
            <span>➕ New Purchase Entry</span>
        </a>
        <button class="btn btn-secondary" onclick="openModal('addPurchaseModal')">
            <span>⚡ Quick Entry</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Supplier / Distributor</th>
                    <th>Invoice Date</th>
                    <th>Subtotal</th>
                    <th>Taxes</th>
                    <th>Grand Total</th>
                    <th>Paid Amount</th>
                    <th>Status</th>
                    <th>Mode</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($purchases)): ?>
                    <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--text-muted);">No purchase records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($purchases as $p): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($p['invoice_number']) ?></strong>
                                <?php if (!empty($p['po_number'])): ?>
                                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                        <a href="<?= $baseURL ?>/purchase-orders" style="color:var(--primary);text-decoration:none;" title="Linked Purchase Order">📦 <?= htmlspecialchars($p['po_number']) ?></a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['supplier_name']) ?></strong>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($p['supplier_phone']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($p['invoice_date']) ?></td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($p['subtotal'], 2) ?></td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($p['tax_amount'], 2) ?></td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($p['grand_total'], 2) ?></strong></td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($p['paid_amount'], 2) ?></td>
                            <td>
                                <?php if ($p['payment_status'] === 'paid'): ?>
                                    <span class="nav-badge badge-success">Paid</span>
                                <?php elseif ($p['payment_status'] === 'partial'): ?>
                                    <span class="nav-badge badge-warning">Partial</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-danger">Unpaid / Dues</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="nav-badge badge-secondary" style="text-transform:uppercase;"><?= htmlspecialchars($p['payment_mode']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Purchase Entry -->
<div id="addPurchaseModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h3 class="modal-title">📥 New Purchase Entry & Batch Receiving</h3>
            <button class="modal-close" onclick="closeModal('addPurchaseModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/purchases/create">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Distributor / Supplier *</label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['company_name']) ?> (<?= htmlspecialchars($s['name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Supplier Bill / Invoice # *</label>
                        <input type="text" name="invoice_number" class="form-control" placeholder="e.g. CI-99210" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Invoice Date *</label>
                        <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-control">
                            <option value="credit">Credit (Pay Later)</option>
                            <option value="cash">Cash Paid</option>
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                </div>

                <!-- Multiple Line Items -->
                <div style="margin-top: 1.25rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                        <span style="font-weight:700;font-size:0.85rem;">📦 Received Medicines & Batches:</span>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addPurchaseRow()">+ Add Another Item</button>
                    </div>
                    
                    <div id="purchaseItemsBox" style="display:flex;flex-direction:column;gap:10px;">
                        <div class="form-row purchase-item-row" style="background:var(--bg-body);padding:10px;border-radius:8px;border:1px solid var(--border-color);align-items:flex-end;">
                            <div style="flex:2;min-width:180px;">
                                <label class="form-label">Medicine *</label>
                                <select name="medicine_id[]" class="form-control" required>
                                    <option value="">Select Medicine</option>
                                    <?php foreach ($medicines as $m): ?>
                                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['brand_name']) ?> (<?= htmlspecialchars($m['name']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex:1.2;min-width:110px;">
                                <label class="form-label">Batch No *</label>
                                <input type="text" name="batch_number[]" class="form-control" placeholder="Batch #" required>
                            </div>
                            <div style="flex:1;min-width:110px;">
                                <label class="form-label">MFG Date</label>
                                <input type="date" name="mfg_date[]" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div style="flex:1;min-width:110px;">
                                <label class="form-label">EXP Date *</label>
                                <input type="date" name="expiry_date[]" class="form-control" value="<?= date('Y-m-d', strtotime('+2 years')) ?>" required>
                            </div>
                            <div style="flex:0.8;min-width:70px;">
                                <label class="form-label">Qty *</label>
                                <input type="number" name="quantity[]" class="form-control" placeholder="Qty" required>
                            </div>
                            <div style="flex:1;min-width:90px;">
                                <label class="form-label">Purchase Rate</label>
                                <input type="number" step="0.01" name="purchase_rate[]" class="form-control" placeholder="₹ Rate" required>
                            </div>
                            <div style="flex:1;min-width:90px;">
                                <label class="form-label">MRP (₹)</label>
                                <input type="number" step="0.01" name="mrp[]" class="form-control" placeholder="₹ MRP" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Purchase Notes & Verification</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Shipment received intact, cold chain verified..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addPurchaseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save & Add To Inventory</button>
            </div>
        </form>
    </div>
</div>

<script>
function addPurchaseRow() {
    var box = document.getElementById('purchaseItemsBox');
    var firstRow = box.querySelector('.purchase-item-row');
    var clone = firstRow.cloneNode(true);
    // clear inputs
    var inputs = clone.querySelectorAll('input');
    inputs.forEach(function(inp) {
        if (inp.name !== 'mfg_date[]' && inp.name !== 'expiry_date[]') {
            inp.value = '';
        }
    });
    box.appendChild(clone);
}

document.addEventListener('DOMContentLoaded', function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('open') === 'modal') {
        openModal('addPurchaseModal');
    }
});
</script>
