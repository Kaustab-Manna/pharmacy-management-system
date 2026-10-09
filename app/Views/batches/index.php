<div class="page-header">
    <div class="page-title">
        <h1>Batch Management & FEFO Tracking</h1>
        <p>Batch-wise inventory, manufacturing & expiry dates, cost vs MRP margins, and barcode assignments.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportTableToCSV('batchesTable', 'batches_inventory.csv')">
            <span>📥 Export CSV</span>
        </button>
        <button class="btn btn-primary" onclick="openModal('addBatchModal')">
            <span>➕ Add New Batch</span>
        </button>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <form method="GET" action="<?= $baseURL ?>/batches" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
            <div style="flex:2;min-width:220px;">
                <select name="medicine_id" class="form-control">
                    <option value="">All Medicines</option>
                    <?php foreach ($medicines as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= ($medicineId == $m['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['brand_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1;min-width:180px;">
                <select name="filter" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="near_expiry" <?= ($filter === 'near_expiry') ? 'selected' : '' ?>>⚠️ Near Expiry (&le; 30 Days)</option>
                    <option value="expired" <?= ($filter === 'expired') ? 'selected' : '' ?>>🚫 Expired Stock</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <?php if (!empty($medicineId) || !empty($filter)): ?>
                    <a href="<?= $baseURL ?>/batches" class="btn btn-sm btn-secondary" style="margin-left:4px;">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom" id="batchesTable">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Batch No / Barcode</th>
                    <th>MFG Date</th>
                    <th>Expiry Date</th>
                    <th>Available Qty</th>
                    <th>Purchase Rate</th>
                    <th>MRP / Sell</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--text-muted);">No batches found.</td></tr>
                <?php else: ?>
                    <?php 
                    $todayStr = date('Y-m-d');
                    $warn30Str = date('Y-m-d', strtotime('+30 days'));
                    foreach ($batches as $b): 
                        $isExpired = ($b['expiry_date'] < $todayStr || $b['status'] === 'expired');
                        $isNearExpiry = (!$isExpired && $b['expiry_date'] <= $warn30Str);
                    ?>
                        <tr style="<?= $isExpired ? 'background:#fff1f2;' : ($isNearExpiry ? 'background:#fffbeb;' : '') ?>">
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($b['brand_name']) ?></strong>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($b['medicine_name']) ?></div>
                            </td>
                            <td>
                                <strong style="font-family:var(--font-mono);font-size:0.95rem;"><?= htmlspecialchars($b['batch_number']) ?></strong>
                                <div style="font-size:10px;color:var(--text-muted);font-family:var(--font-mono);">
                                    🏷️ <?= htmlspecialchars($b['barcode']) ?>
                                </div>
                            </td>
                            <td style="font-size:12px;"><?= htmlspecialchars($b['mfg_date']) ?></td>
                            <td>
                                <strong style="<?= $isExpired ? 'color:#ef4444;' : ($isNearExpiry ? 'color:#f59e0b;' : 'color:var(--text-main);') ?>">
                                    <?= htmlspecialchars($b['expiry_date']) ?>
                                </strong>
                                <?php if ($isExpired): ?>
                                    <span class="nav-badge badge-danger" style="display:block;margin-top:2px;">EXPIRED</span>
                                <?php elseif ($isNearExpiry): ?>
                                    <span class="nav-badge badge-warning" style="display:block;margin-top:2px;">EXPIRING SOON</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="font-size:1rem;color:var(--text-main);"><?= $b['quantity'] ?></strong> <?= htmlspecialchars($b['unit']) ?>
                                <div style="font-size:10px;color:var(--text-muted);">Sold: <?= $b['total_sold'] ?></div>
                            </td>
                            <td>
                                <?= $pharmacy['currency_symbol'] ?><?= number_format($b['purchase_price'], 2) ?>
                            </td>
                            <td>
                                <div>MRP: <strong><?= $pharmacy['currency_symbol'] ?><?= number_format($b['mrp'], 2) ?></strong></div>
                                <div style="font-size:11px;color:var(--text-muted);">Sell: <?= $pharmacy['currency_symbol'] ?><?= number_format($b['selling_price'], 2) ?></div>
                            </td>
                            <td>
                                <?php if ($b['quantity'] <= 0): ?>
                                    <span class="nav-badge badge-secondary">Depleted</span>
                                <?php elseif ($isExpired): ?>
                                    <span class="nav-badge badge-danger">Quarantined</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    <button class="btn btn-sm btn-secondary" onclick="openAdjustModal(<?= $b['id'] ?>, '<?= htmlspecialchars($b['batch_number']) ?>', <?= $b['quantity'] ?>)" title="Adjust Physical Stock">⚖️</button>
                                    <button class="btn btn-sm btn-secondary" onclick="openBatchPriceModal(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['brand_name'])) ?>', '<?= htmlspecialchars($b['batch_number']) ?>', <?= $b['mrp'] ?>, <?= $b['selling_price'] ?>, <?= $b['wholesale_price'] ?: ($b['purchase_price'] * 1.1) ?>)" title="Edit Batch MRP & Selling Price">✏️</button>
                                    <a href="<?= $baseURL ?>/barcode/print?batch_id=<?= $b['id'] ?>" class="btn btn-sm btn-secondary" title="Print Barcode Label">🏷️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Batch -->
<div id="addBatchModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">➕ Add New Batch</h3>
            <button class="modal-close" onclick="closeModal('addBatchModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/batches/create">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Medicine *</label>
                    <select name="medicine_id" class="form-control" required>
                        <?php foreach ($medicines as $m): ?>
                            <option value="<?= $m['id'] ?>">
                                <?= htmlspecialchars($m['brand_name']) ?> (<?= htmlspecialchars($m['name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Batch Number *</label>
                        <input type="text" name="batch_number" class="form-control" placeholder="e.g. BATCH-2024-01" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Quantity Received *</label>
                        <input type="number" name="quantity" class="form-control" placeholder="100" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Manufacturing Date (MFG)</label>
                        <input type="date" name="mfg_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Expiry Date (EXP) *</label>
                        <input type="date" name="expiry_date" class="form-control" value="<?= date('Y-m-d', strtotime('+2 years')) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Purchase Rate (₹)</label>
                        <input type="number" step="0.01" name="purchase_price" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">MRP (₹)</label>
                        <input type="number" step="0.01" name="mrp" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Selling Price (₹)</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" placeholder="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Barcode / EAN (Leave blank to auto-generate)</label>
                    <input type="text" name="barcode" class="form-control" placeholder="Auto-generated barcode if empty">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addBatchModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Batch</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Adjust Stock -->
<div id="adjustModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h3 class="modal-title">⚖️ Physical Stock Adjustment</h3>
            <button class="modal-close" onclick="closeModal('adjustModal')">&times;</button>
        </div>
        <form id="adjustForm" method="POST">
            <div class="modal-body">
                <div style="margin-bottom:1rem;">
                    Batch: <strong id="adjust_batch_number" style="font-family:var(--font-mono);"></strong>
                </div>
                <div class="form-group">
                    <label class="form-label">New Verified Stock Quantity *</label>
                    <input type="number" id="adjust_quantity" name="quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Reason for Adjustment *</label>
                    <select name="reason" class="form-control" required>
                        <option value="Physical Audit Count">Physical Audit Count Verification</option>
                        <option value="Damaged / Broken Ampoule">Damaged / Broken Packaging</option>
                        <option value="Theft or Loss Discrepancy">Theft or Unaccounted Discrepancy</option>
                        <option value="Supplier Replacement">Supplier Bonus / Replacement</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('adjustModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply Adjustment</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Batch Pricing & MRP -->
<div id="batchPriceModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h3 class="modal-title">🏷️ Edit Batch MRP & Pricing</h3>
            <button class="modal-close" onclick="closeModal('batchPriceModal')">&times;</button>
        </div>
        <form id="batchPriceForm" method="POST">
            <input type="hidden" name="redirect_to" value="<?= $baseURL ?>/batches">
            <div class="modal-body">
                <div style="margin-bottom:1rem;background:var(--bg-body);padding:10px;border-radius:6px;border:1px solid var(--border-color);">
                    Medicine: <strong id="bp_brand_name" style="color:var(--primary);"></strong><br>
                    Batch No: <code id="bp_batch_number"></code>
                </div>

                <div class="form-group">
                    <label class="form-label">Maximum Retail Price (MRP ₹) *</label>
                    <input type="number" step="0.01" id="bp_mrp" name="mrp" class="form-control" required oninput="autoUpdateBatchSellPrice()">
                </div>
                <div class="form-group">
                    <label class="form-label">Retail Selling Price (₹) *</label>
                    <input type="number" step="0.01" id="bp_selling_price" name="selling_price" class="form-control" required>
                    <small style="color:var(--text-muted);font-size:11px;">Counter price charged to retail patients</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Wholesale Price (₹)</label>
                    <input type="number" step="0.01" id="bp_wholesale_price" name="wholesale_price" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('batchPriceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Pricing</button>
            </div>
        </form>
    </div>
</div>

<script>
function autoUpdateBatchSellPrice() {
    var mrp = parseFloat(document.getElementById('bp_mrp').value) || 0;
    var sellInput = document.getElementById('bp_selling_price');
    if (mrp > 0 && (!sellInput.value || parseFloat(sellInput.value) === 0)) {
        sellInput.value = (mrp * 0.95).toFixed(2);
    }
}

function openBatchPriceModal(id, brand, batch, mrp, sell, wholesale) {
    document.getElementById('batchPriceForm').action = '<?= $baseURL ?>/pricing/update/' + id;
    document.getElementById('bp_brand_name').innerText = brand;
    document.getElementById('bp_batch_number').innerText = batch;
    document.getElementById('bp_mrp').value = mrp ? parseFloat(mrp).toFixed(2) : '';
    document.getElementById('bp_selling_price').value = sell ? parseFloat(sell).toFixed(2) : (mrp ? (mrp * 0.95).toFixed(2) : '');
    document.getElementById('bp_wholesale_price').value = wholesale ? parseFloat(wholesale).toFixed(2) : '';
    openModal('batchPriceModal');
}

function openAdjustModal(id, batchNo, currentQty) {
    document.getElementById('adjustForm').action = '<?= $baseURL ?>/batches/adjust/' + id;
    document.getElementById('adjust_batch_number').innerText = batchNo;
    document.getElementById('adjust_quantity').value = currentQty;
    openModal('adjustModal');
}
</script>
