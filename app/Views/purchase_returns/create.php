<div class="page-header">
    <div class="page-title">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
            <a href="<?= $baseURL ?>/expiry" style="color:var(--text-muted);text-decoration:none;font-size:0.9rem;">&larr; Expiry Management</a>
            <span style="color:var(--text-muted);">&bull;</span>
            <span style="color:var(--primary);font-size:0.9rem;font-weight:600;">Module 16: Supplier Returns</span>
        </div>
        <h1>↩️ Process Return to Supplier & Debit Note</h1>
        <p>Return expired, near-expiry, or damaged stock to distributors. Deducts inventory immediately and generates a supplier credit debit note.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/expiry" class="btn btn-secondary">
            <span>⏳ Expiry Monitor</span>
        </a>
        <a href="<?= $baseURL ?>/purchase-returns" class="btn btn-secondary">
            <span>📋 All Supplier Returns</span>
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:1.5rem;align-items:start;">
    
    <!-- Left Column: Return Details Form -->
    <div class="card">
        <div class="card-header" style="background:var(--bg-body);border-bottom:1px solid var(--border-color);">
            <h3 class="card-title" style="font-size:1rem;color:var(--text-main);">
                📦 Return Particulars
            </h3>
            <span style="font-size:0.8rem;color:var(--text-muted);">Stock will be deducted from active batch</span>
        </div>

        <form id="purchaseReturnForm" method="POST" action="<?= $baseURL ?>/purchase-returns/create">
            <div class="card-body">
                
                <?php if (!empty($batch)): ?>
                    <!-- Pre-selected Batch Info Card -->
                    <input type="hidden" name="batch_id" id="batchIdInput" value="<?= $batch['id'] ?>">
                    
                    <div style="background:var(--bg-body);border:1px solid var(--border-color);border-radius:10px;padding:1rem;margin-bottom:1.5rem;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                            <div>
                                <h3 style="margin:0 0 4px 0;font-size:1.1rem;color:var(--text-main);">
                                    <?= htmlspecialchars($batch['brand_name'] ?? $batch['medicine_name']) ?>
                                </h3>
                                <div style="font-size:12px;color:var(--text-muted);">
                                    <?= htmlspecialchars($batch['medicine_name']) ?> &bull; 
                                    <?= htmlspecialchars($batch['dosage_form'] ?? 'Medicine') ?> &bull; 
                                    <?= htmlspecialchars($batch['strength'] ?? '') ?>
                                </div>
                            </div>
                            <?php 
                                $daysDiff = (int)ceil((strtotime($batch['expiry_date']) - time()) / 86400);
                            ?>
                            <?php if ($daysDiff < 0): ?>
                                <span class="nav-badge badge-danger">EXPIRED (<?= abs($daysDiff) ?> days ago)</span>
                            <?php elseif ($daysDiff <= 30): ?>
                                <span class="nav-badge badge-warning">Near Expiry (<?= $daysDiff ?> days left)</span>
                            <?php else: ?>
                                <span class="nav-badge badge-info"><?= $daysDiff ?> days left</span>
                            <?php endif; ?>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:10px;padding-top:10px;border-top:1px dashed var(--border-color);font-size:12px;">
                            <div>
                                <span style="color:var(--text-muted);display:block;">Batch Number:</span>
                                <strong style="font-family:var(--font-mono);font-size:13px;"><?= htmlspecialchars($batch['batch_number']) ?></strong>
                            </div>
                            <div>
                                <span style="color:var(--text-muted);display:block;">Expiry Date:</span>
                                <strong><?= htmlspecialchars($batch['expiry_date']) ?></strong>
                            </div>
                            <div>
                                <span style="color:var(--text-muted);display:block;">Available Stock:</span>
                                <strong style="color:var(--primary);"><?= $batch['quantity'] ?> <?= htmlspecialchars($batch['unit'] ?? 'units') ?></strong>
                            </div>
                            <div>
                                <span style="color:var(--text-muted);display:block;">Purchase Rate:</span>
                                <strong><?= $pharmacy['currency_symbol'] ?><span id="unitRateDisplay"><?= number_format($batch['purchase_price'], 2) ?></span></strong>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Batch Selection Dropdown if no batch is pre-selected -->
                    <div class="form-group">
                        <label class="form-label">Select Medicine Batch to Return *</label>
                        <select name="batch_id" id="batchSelect" class="form-control" required onchange="handleBatchChange()">
                            <option value="">-- Choose Medicine Batch --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>" 
                                    data-rate="<?= $b['purchase_price'] ?>" 
                                    data-qty="<?= $b['quantity'] ?>" 
                                    data-unit="<?= htmlspecialchars($b['unit'] ?? 'units') ?>" 
                                    data-expiry="<?= $b['expiry_date'] ?>">
                                    <?= htmlspecialchars($b['brand_name'] ?? $b['medicine_name']) ?> (Batch: <?= htmlspecialchars($b['batch_number']) ?>) - Stock: <?= $b['quantity'] ?> @ <?= $pharmacy['currency_symbol'] ?><?= number_format($b['purchase_price'], 2) ?> [Exp: <?= $b['expiry_date'] ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Distributor / Supplier Selection -->
                <div class="form-group">
                    <label class="form-label">Distributor / Supplier *</label>
                    <select name="supplier_id" id="supplierSelect" class="form-control" required>
                        <option value="">-- Select Distributor / Supplier --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (!empty($suggestedSupplierId) && $suggestedSupplierId == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['company_name']) ?> (<?= htmlspecialchars($s['name']) ?>) - Current Balance: <?= $pharmacy['currency_symbol'] ?><?= number_format($s['current_balance'] ?? 0, 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($suggestedSupplierId)): ?>
                        <div style="font-size:11px;color:var(--primary);margin-top:4px;">
                            ✓ Supplier auto-detected from original inward purchase invoice.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Return Quantity & Reason Row -->
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">
                            Quantity to Return *
                            <span id="maxQtyNote" style="font-size:11px;color:var(--text-muted);font-weight:400;">
                                (Max: <?= $batch['quantity'] ?? 0 ?>)
                            </span>
                        </label>
                        <?php 
                            $defaultQty = !empty($prefillQty) ? min((int)$prefillQty, (int)($batch['quantity'] ?? 1)) : ($batch['quantity'] ?? 1);
                        ?>
                        <input type="number" name="quantity" id="quantityInput" class="form-control" 
                            min="1" max="<?= $batch['quantity'] ?? 9999 ?>" 
                            value="<?= $defaultQty ?>" required oninput="calculateReturnTotal()">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Reason for Return *</label>
                        <select name="return_reason" class="form-control">
                            <option value="expired" <?= (!empty($daysDiff) && $daysDiff < 0) ? 'selected' : '' ?>>Expired Stock (Supplier Agreement)</option>
                            <option value="near_expiry" <?= (!empty($daysDiff) && $daysDiff >= 0 && $daysDiff <= 60) ? 'selected' : '' ?>>Near Expiry (< 60 Days Notice)</option>
                            <option value="damaged">Damaged / Broken / Leaked</option>
                            <option value="recall">Manufacturer Quality Recall</option>
                            <option value="excess">Excess Overstock</option>
                        </select>
                    </div>
                </div>

                <!-- Notes / Courier docket -->
                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Return Notes & Dispatch Tracking</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Returned with Distributor Representative; Credit Note expected in next billing cycle; Gate pass #..."></textarea>
                </div>
            </div>

            <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;background:var(--bg-body);border-top:1px solid var(--border-color);padding:1rem;">
                <a href="<?= $baseURL ?>/expiry" class="btn btn-secondary">Cancel & Back to Expiry</a>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;font-weight:700;display:flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(13,148,136,0.3);">
                    <span>↩️ Confirm Return & Deduct Stock</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Right Column: Financial Impact & Debit Note Summary -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        
        <div class="card" style="box-shadow:var(--shadow-md);">
            <div class="card-header" style="background:var(--bg-body);border-bottom:1px solid var(--border-color);">
                <h3 class="card-title" style="font-size:0.95rem;">🧾 Debit Note Valuation</h3>
            </div>
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                    <span style="color:var(--text-muted);">Units Returning:</span>
                    <strong id="summaryUnits">0</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                    <span style="color:var(--text-muted);">Purchase Cost / Unit:</span>
                    <strong id="summaryUnitCost">₹0.00</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:2px solid var(--border-color);font-size:1.15rem;">
                    <span style="font-weight:700;color:var(--text-main);">Debit Note Total:</span>
                    <strong id="summaryTotal" style="color:var(--primary);font-size:1.35rem;">₹0.00</strong>
                </div>
                <div style="margin-top:1rem;background:var(--primary-light);color:var(--primary-dark);padding:10px;border-radius:8px;font-size:12px;line-height:1.4;">
                    💼 <strong>Supplier Credit:</strong> Generating this debit note immediately credits your pharmacy account and reduces payable balance to the selected distributor.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="background:#fff1f2;border-bottom:1px solid #fecdd3;">
                <h3 class="card-title" style="color:#9f1239;font-size:0.9rem;">
                    ⚠️ Immediate Stock Impact
                </h3>
            </div>
            <div class="card-body" style="font-size:12px;color:var(--text-muted);line-height:1.5;">
                <ul style="padding-left:1.2rem;margin:0;">
                    <li style="margin-bottom:6px;">Stock quantity in batch will be reduced by returned units.</li>
                    <li style="margin-bottom:6px;">If returned completely, the batch status changes to <strong>Depleted</strong>.</li>
                    <li style="margin-bottom:6px;">A permanent <code>purchase_return</code> movement is written to stock ledger audit.</li>
                </ul>
            </div>
        </div>

    </div>

</div>

<script>
var currentUnitRate = <?= !empty($batch['purchase_price']) ? (float)$batch['purchase_price'] : 0 ?>;
var currentMaxQty = <?= !empty($batch['quantity']) ? (int)$batch['quantity'] : 0 ?>;

function handleBatchChange() {
    var select = document.getElementById('batchSelect');
    if (!select) return;
    var selectedOpt = select.options[select.selectedIndex];
    if (selectedOpt && selectedOpt.value) {
        currentUnitRate = parseFloat(selectedOpt.dataset.rate) || 0;
        currentMaxQty = parseInt(selectedOpt.dataset.qty) || 0;
        
        var qtyInput = document.getElementById('quantityInput');
        qtyInput.max = currentMaxQty;
        qtyInput.value = currentMaxQty;

        var maxNote = document.getElementById('maxQtyNote');
        if (maxNote) {
            maxNote.textContent = '(Max: ' + currentMaxQty + ')';
        }
    } else {
        currentUnitRate = 0;
        currentMaxQty = 0;
    }
    calculateReturnTotal();
}

function calculateReturnTotal() {
    var qtyInput = document.getElementById('quantityInput');
    var qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;
    
    if (currentMaxQty > 0 && qty > currentMaxQty) {
        qty = currentMaxQty;
        qtyInput.value = currentMaxQty;
    }

    var total = qty * currentUnitRate;

    var summaryUnits = document.getElementById('summaryUnits');
    if (summaryUnits) summaryUnits.textContent = qty;

    var summaryUnitCost = document.getElementById('summaryUnitCost');
    if (summaryUnitCost) summaryUnitCost.textContent = '₹' + currentUnitRate.toFixed(2);

    var summaryTotal = document.getElementById('summaryTotal');
    if (summaryTotal) summaryTotal.textContent = '₹' + total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    calculateReturnTotal();
});
</script>
