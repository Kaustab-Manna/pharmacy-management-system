<div class="page-header">
    <div class="page-title">
        <h1>Smart Reorder Management (Module 21)</h1>
        <p>AI-driven predictive stock replenishment calculating sales velocity, lead times, and suggested purchase quantities.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportTableToCSV('reorderTable', 'smart_reorder_recommendations.csv')">
            <span>📥 Export Recommendations</span>
        </button>
    </div>
</div>

<!-- Formula & Concept Explainer Card as in PDF -->
<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, rgba(13,148,136,0.06), rgba(99,102,241,0.06)); border-color: rgba(13,148,136,0.3);">
    <div class="card-body" style="padding: 1.25rem 1.5rem;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="font-size:2rem;">💡</div>
            <div>
                <strong style="color:var(--primary);font-size:1.05rem;">Predictive Reorder Algorithm (PDF Specification):</strong>
                <div style="font-size:0.85rem;color:var(--text-muted);margin-top:2px;">
                    Algorithm Formula: <code>Suggested Order = (Avg Daily Sales &times; Lead Time [10d]) + Reorder Buffer - Current Stock</code>
                </div>
                <div style="font-size:0.82rem;color:var(--text-main);margin-top:4px;background:#ffffff;padding:4px 10px;border-radius:4px;display:inline-block;border:1px solid var(--border-color);">
                    <strong>Example from Spec:</strong> Paracetamol &rarr; Current Stock: <strong>18</strong> &rarr; Avg Daily Sales: <strong>10</strong> &rarr; Suggested Order: <strong>100 Units</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📈 Automated Reorder Recommendations</h3>
        <span class="nav-badge badge-warning">Priority Ranked</span>
    </div>
    <div class="table-responsive">
        <table class="table-custom" id="reorderTable">
            <thead>
                <tr>
                    <th>Medicine / Brand</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Min Level</th>
                    <th>Reorder Level</th>
                    <th>Avg Daily Sales</th>
                    <th>Suggested Order Qty</th>
                    <th>Urgency Status</th>
                    <th>Procurement Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reorderList as $item): ?>
                    <tr style="<?= $item['urgency'] === 'danger' ? 'background:#fff1f2;' : ($item['urgency'] === 'warning' ? 'background:#fffbeb;' : '') ?>">
                        <td>
                            <strong style="color:var(--primary);"><?= htmlspecialchars($item['brand_name']) ?></strong>
                            <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['pack_size']) ?>)</div>
                        </td>
                        <td><?= htmlspecialchars($item['category_name'] ?? 'General') ?></td>
                        <td>
                            <strong style="font-size:1.1rem;<?= $item['current_stock'] <= $item['min_stock_level'] ? 'color:#ef4444;' : '' ?>">
                                <?= $item['current_stock'] ?>
                            </strong> <?= htmlspecialchars($item['unit']) ?>
                        </td>
                        <td><?= $item['min_stock_level'] ?></td>
                        <td><?= $item['reorder_level'] ?></td>
                        <td>
                            <strong><?= $item['daily_sales'] ?></strong> units/day
                        </td>
                        <td>
                            <strong style="font-size:1.15rem;color:var(--primary);">
                                <?= $item['suggested_order'] ?>
                            </strong> <?= htmlspecialchars($item['unit']) ?>
                        </td>
                        <td>
                            <?php if ($item['status_label'] === 'OUT_OF_STOCK'): ?>
                                <span class="nav-badge badge-danger">OUT OF STOCK</span>
                            <?php elseif ($item['status_label'] === 'CRITICAL'): ?>
                                <span class="nav-badge badge-danger">CRITICAL LOW</span>
                            <?php elseif ($item['status_label'] === 'REORDER_NOW'): ?>
                                <span class="nav-badge badge-warning">REORDER NOW</span>
                            <?php else: ?>
                                <span class="nav-badge badge-success">SUFFICIENT</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['suggested_order'] > 0): ?>
                                <button class="btn btn-sm btn-primary" onclick="openPoPrefillModal(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['brand_name'])) ?>', <?= $item['suggested_order'] ?>, <?= $item['latest_purchase_rate'] ?? 25 ?>)">
                                    📋 Create PO
                                </button>
                            <?php else: ?>
                                <span style="font-size:11px;color:var(--text-muted);">Adequate</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Instant PO Generation from Smart Reorder Suggestion -->
<div id="quickPoModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">📋 1-Click Purchase Order Generation</h3>
            <button class="modal-close" onclick="closeModal('quickPoModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/purchase-orders/create">
            <div class="modal-body">
                <input type="hidden" id="po_medicine_id" name="medicine_id[]">
                <div style="margin-bottom:1rem;">
                    Medicine: <strong id="po_medicine_name" style="color:var(--primary);"></strong>
                </div>
                <div class="form-group">
                    <label class="form-label">Select Preferred Supplier *</label>
                    <select name="supplier_id" class="form-control" required>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Suggested Order Qty *</label>
                        <input type="number" id="po_quantity" name="quantity[]" class="form-control" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Estimated Rate (₹)</label>
                        <input type="number" step="0.01" id="po_expected_rate" name="expected_rate[]" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Expected Delivery Date</label>
                    <input type="date" name="expected_date" class="form-control" value="<?= date('Y-m-d', strtotime('+5 days')) ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('quickPoModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate Purchase Order</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPoPrefillModal(medId, medName, qty, rate) {
    document.getElementById('po_medicine_id').value = medId;
    document.getElementById('po_medicine_name').innerText = medName;
    document.getElementById('po_quantity').value = qty;
    document.getElementById('po_expected_rate').value = rate;
    openModal('quickPoModal');
}
</script>
