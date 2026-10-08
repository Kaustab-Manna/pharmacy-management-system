<div class="page-header">
    <div class="page-title">
        <h1>Medicine Pricing Management (Module 23)</h1>
        <p>Batch-wise pricing tiers: Cost Price, Maximum Retail Price (MRP), Retail Selling Price, and Wholesale Price.</p>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Medicine / Brand</th>
                    <th>Batch Number</th>
                    <th>Purchase Rate (Cost)</th>
                    <th>MRP</th>
                    <th>Retail Selling Price</th>
                    <th>Wholesale Price</th>
                    <th>Retail Margin (%)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pricingList as $p): 
                    $cost = (float)$p['purchase_price'];
                    $sell = (float)$p['selling_price'];
                    $margin = ($sell > 0 && $cost > 0) ? round((($sell - $cost) / $sell) * 100, 1) : 0;
                ?>
                    <tr>
                        <td>
                            <strong style="color:var(--primary);"><?= htmlspecialchars($p['brand_name']) ?></strong>
                            <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($p['medicine_name']) ?> (<?= htmlspecialchars($p['pack_size']) ?>)</div>
                        </td>
                        <td><code><?= htmlspecialchars($p['batch_number']) ?></code></td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($cost, 2) ?></td>
                        <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($p['mrp'], 2) ?></strong></td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($sell, 2) ?></td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($p['wholesale_price'] ?: ($cost * 1.1), 2) ?></td>
                        <td>
                            <span class="nav-badge badge-success"><?= $margin ?>% Margin</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="openPriceModal(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['brand_name'])) ?>', '<?= htmlspecialchars($p['batch_number']) ?>', <?= $p['mrp'] ?>, <?= $p['selling_price'] ?>, <?= $p['wholesale_price'] ?: ($cost * 1.1) ?>)">
                                ✏️ Edit Prices
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Edit Price -->
<div id="editPriceModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h3 class="modal-title">✏️ Edit Batch Pricing</h3>
            <button class="modal-close" onclick="closeModal('editPriceModal')">&times;</button>
        </div>
        <form id="priceEditForm" method="POST">
            <div class="modal-body">
                <div style="margin-bottom:1rem;">
                    Medicine: <strong id="price_brand_name" style="color:var(--primary);"></strong><br>
                    Batch: <code id="price_batch_number"></code>
                </div>

                <div class="form-group">
                    <label class="form-label">Maximum Retail Price (MRP) *</label>
                    <input type="number" step="0.01" id="inp_mrp" name="mrp" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Retail Selling Price *</label>
                    <input type="number" step="0.01" id="inp_selling_price" name="selling_price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Wholesale Price (for institutional buyers)</label>
                    <input type="number" step="0.01" id="inp_wholesale_price" name="wholesale_price" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editPriceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Pricing</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPriceModal(id, brand, batch, mrp, sell, wholesale) {
    document.getElementById('priceEditForm').action = '<?= $baseURL ?>/pricing/update/' + id;
    document.getElementById('price_brand_name').innerText = brand;
    document.getElementById('price_batch_number').innerText = batch;
    document.getElementById('inp_mrp').value = mrp;
    document.getElementById('inp_selling_price').value = sell;
    document.getElementById('inp_wholesale_price').value = wholesale;
    openModal('editPriceModal');
}
</script>
