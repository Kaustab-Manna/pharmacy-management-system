<div class="page-header">
    <div class="page-title">
        <h1>Medicine Master Management</h1>
        <p>Comprehensive pharmaceutical product database with strength, salts, GST rates, and packaging.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportTableToCSV('medicinesTable', 'medicines_master.csv')">
            <span>📥 Export CSV</span>
        </button>
        <button class="btn btn-primary" onclick="openModal('addMedicineModal')">
            <span>➕ Add New Medicine</span>
        </button>
    </div>
</div>

<!-- Search & Filtering Bar -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <form method="GET" action="<?= $baseURL ?>/medicines" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
            <div style="flex:2;min-width:250px;">
                <input type="text" name="search" class="form-control" placeholder="Search by brand name, generic composition, manufacturer, code..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div style="flex:1;min-width:180px;">
                <select name="category_id" class="form-control">
                    <option value="">All Therapeutic Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($categoryId == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <?php if (!empty($search) || !empty($categoryId)): ?>
                    <a href="<?= $baseURL ?>/medicines" class="btn btn-sm btn-secondary" style="margin-left:4px;">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Medicines List Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table-custom" id="medicinesTable">
            <thead>
                <tr>
                    <th style="min-width:140px;">Code / Brand</th>
                    <th style="min-width:160px;">Generic Composition</th>
                    <th style="min-width:115px;">Category</th>
                    <th style="min-width:125px;">Dosage & Pack</th>
                    <th style="min-width:145px;">Stock / FEFO</th>
                    <th style="min-width:90px;">MRP</th>
                    <th style="min-width:85px;">Tax</th>
                    <th style="min-width:115px;">Rx Category</th>
                    <th style="min-width:90px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($medicines)): ?>
                    <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--text-muted);">No medicines found matching criteria.</td></tr>
                <?php else: ?>
                    <?php foreach ($medicines as $med): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);font-size:0.95rem;"><?= htmlspecialchars($med['brand_name']) ?></strong>
                                <div style="font-size:11px;color:var(--text-muted);font-family:var(--font-mono);"><?= htmlspecialchars($med['code']) ?> • <?= htmlspecialchars($med['manufacturer']) ?></div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($med['name']) ?></div>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($med['composition']) ?></div>
                            </td>
                            <td style="white-space:nowrap;">
                                <span class="badge badge-secondary" style="font-size:10.5px;"><?= htmlspecialchars($med['category_name'] ?? 'General') ?></span>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($med['dosage_form']) ?> (<?= htmlspecialchars($med['strength']) ?>)</div>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($med['pack_size']) ?> • <?= htmlspecialchars($med['unit']) ?></div>
                            </td>
                            <td style="white-space:nowrap;">
                                <div style="display:flex;flex-direction:column;gap:3px;align-items:flex-start;">
                                    <div>
                                        <?php if ($med['current_stock'] <= 0): ?>
                                            <span class="badge badge-danger">Out of Stock</span>
                                        <?php elseif ($med['current_stock'] <= $med['min_stock_level']): ?>
                                            <span class="badge badge-warning">
                                                <strong style="font-size:0.85rem;"><?= $med['current_stock'] ?></strong>&nbsp;units (Low)
                                            </span>
                                        <?php else: ?>
                                            <strong style="color:var(--success);font-size:0.95rem;"><?= $med['current_stock'] ?></strong> <span style="font-size:11px;color:var(--text-muted);">units</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($med['earliest_expiry'])): ?>
                                        <div style="font-size:10.5px;color:var(--text-muted);display:flex;align-items:center;gap:4px;margin-top:2px;">
                                            <span>FEFO:</span>
                                            <span class="fefo-pill"><?= $med['earliest_expiry'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="white-space:nowrap;">
                                <strong style="font-size:0.95rem;color:var(--text-main);"><?= $pharmacy['currency_symbol'] ?><?= number_format($med['current_mrp'], 2) ?></strong>
                            </td>
                            <td style="white-space:nowrap;">
                                <span style="font-size:11.5px;font-weight:600;"><?= $med['gst_rate'] ?>%</span>
                                <div style="font-size:9.5px;color:var(--text-muted);">HSN: <?= htmlspecialchars($med['hsn_code']) ?></div>
                            </td>
                            <td style="white-space:nowrap;">
                                <?php if ($med['requires_prescription']): ?>
                                    <span class="badge badge-danger" title="Prescription Mandatory (Schedule H)">⚕️ Rx Req</span>
                                <?php else: ?>
                                    <span class="badge badge-success" title="Over The Counter">OTC</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;white-space:nowrap;">
                                <div style="display:inline-flex;gap:6px;">
                                    <a href="<?= $baseURL ?>/batches?medicine_id=<?= $med['id'] ?>" class="btn btn-sm btn-secondary" title="View Batches" style="padding:4px 8px;">📦</a>
                                    <button class="btn btn-sm btn-secondary" onclick="editMedicine(<?= htmlspecialchars(json_encode($med)) ?>)" title="Edit Medicine" style="padding:4px 8px;">✏️</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add New Medicine (Module 4) -->
<div id="addMedicineModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 750px;">
        <div class="modal-header">
            <h3 class="modal-title">➕ Add New Medicine to Catalog</h3>
            <button class="modal-close" onclick="closeModal('addMedicineModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/medicines/create">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Brand Name *</label>
                        <input type="text" name="brand_name" class="form-control" placeholder="e.g. Dolo 650, Augmentin 625" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Medicine Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Paracetamol 650mg" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Therapeutic Category *</label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Dosage Form</label>
                        <select name="dosage_form" class="form-control">
                            <option value="Tablet">Tablet</option>
                            <option value="Capsule">Capsule</option>
                            <option value="Syrup">Syrup / Suspension</option>
                            <option value="Injection">Injection / Vial</option>
                            <option value="Ointment">Ointment / Gel</option>
                            <option value="Drops">Eye / Ear Drops</option>
                            <option value="Inhaler">Inhaler / Respule</option>
                            <option value="Device">Medical Device</option>
                            <option value="Surgical">Surgical / Dressing</option>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Strength</label>
                        <input type="text" name="strength" class="form-control" placeholder="e.g. 500 mg, 100 ml">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Generic Composition / Salt Name *</label>
                    <input type="text" name="composition" class="form-control" placeholder="e.g. Paracetamol IP 650mg" required>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Manufacturer</label>
                        <input type="text" name="manufacturer" class="form-control" placeholder="e.g. Cipla, Sun Pharma, Micro Labs">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Pack Size</label>
                        <input type="text" name="pack_size" class="form-control" placeholder="e.g. 1x15 Tablets, 100ml Bottle" value="1x10">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Dispense Unit</label>
                        <select name="unit" class="form-control">
                            <option value="Strip">Strip</option>
                            <option value="Bottle">Bottle</option>
                            <option value="Piece">Piece</option>
                            <option value="Tube">Tube</option>
                            <option value="Vial">Vial</option>
                            <option value="Box">Box</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">HSN Code</label>
                        <input type="text" name="hsn_code" class="form-control" value="300490">
                    </div>
                    <div class="form-col">
                        <label class="form-label">GST Tax Rate (%)</label>
                        <select name="gst_rate" class="form-control">
                            <option value="0.00">0% (Nil)</option>
                            <option value="5.00">5% (Life Saving)</option>
                            <option value="12.00" selected>12% (Standard Medicines)</option>
                            <option value="18.00">18% (Devices / Cosmetics)</option>
                            <option value="28.00">28% (Luxury)</option>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Prescription Required?</label>
                        <select name="requires_prescription" class="form-control">
                            <option value="0">No (OTC)</option>
                            <option value="1">Yes (Schedule H / Rx Mandatory)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Min Stock Level (Alert)</label>
                        <input type="number" name="min_stock_level" class="form-control" value="20">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Reorder Level (Suggested)</label>
                        <input type="number" name="reorder_level" class="form-control" value="50">
                    </div>
                </div>

                <!-- Optional Opening Stock -->
                <div style="background:var(--bg-body);padding:1rem;border-radius:8px;margin-top:1rem;border:1px dashed var(--border-color);">
                    <div style="font-weight:700;font-size:0.85rem;color:var(--text-main);margin-bottom:0.5rem;">📦 Initial Opening Stock (Optional)</div>
                    <div class="form-row">
                        <div class="form-col">
                            <label class="form-label">Batch No.</label>
                            <input type="text" name="initial_batch" class="form-control" placeholder="e.g. BT24A">
                        </div>
                        <div class="form-col">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="initial_qty" class="form-control" placeholder="0">
                        </div>
                        <div class="form-col">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="initial_expiry" class="form-control">
                        </div>
                        <div class="form-col">
                            <label class="form-label">MRP (₹)</label>
                            <input type="number" step="0.01" name="initial_mrp" class="form-control" placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addMedicineModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Medicine</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Medicine -->
<div id="editMedicineModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 750px;">
        <div class="modal-header">
            <h3 class="modal-title">✏️ Edit Medicine Details</h3>
            <button class="modal-close" onclick="closeModal('editMedicineModal')">&times;</button>
        </div>
        <form id="editMedicineForm" method="POST">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Brand Name</label>
                        <input type="text" id="edit_brand_name" name="brand_name" class="form-control" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Medicine Full Name</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Category</label>
                        <select id="edit_category_id" name="category_id" class="form-control">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Dosage Form</label>
                        <input type="text" id="edit_dosage_form" name="dosage_form" class="form-control">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Strength</label>
                        <input type="text" id="edit_strength" name="strength" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Generic Composition</label>
                    <input type="text" id="edit_composition" name="composition" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Manufacturer</label>
                        <input type="text" id="edit_manufacturer" name="manufacturer" class="form-control">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Pack Size</label>
                        <input type="text" id="edit_pack_size" name="pack_size" class="form-control">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Dispense Unit</label>
                        <input type="text" id="edit_unit" name="unit" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">HSN Code</label>
                        <input type="text" id="edit_hsn_code" name="hsn_code" class="form-control">
                    </div>
                    <div class="form-col">
                        <label class="form-label">GST Rate (%)</label>
                        <input type="number" step="0.01" id="edit_gst_rate" name="gst_rate" class="form-control">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Rx Required?</label>
                        <select id="edit_requires_prescription" name="requires_prescription" class="form-control">
                            <option value="0">No (OTC)</option>
                            <option value="1">Yes (Schedule H / Rx)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editMedicineModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Medicine</button>
            </div>
        </form>
    </div>
</div>

<script>
function editMedicine(med) {
    document.getElementById('editMedicineForm').action = '<?= $baseURL ?>/medicines/edit/' + med.id;
    document.getElementById('edit_brand_name').value = med.brand_name || '';
    document.getElementById('edit_name').value = med.name || '';
    document.getElementById('edit_category_id').value = med.category_id || '';
    document.getElementById('edit_dosage_form').value = med.dosage_form || '';
    document.getElementById('edit_strength').value = med.strength || '';
    document.getElementById('edit_composition').value = med.composition || '';
    document.getElementById('edit_manufacturer').value = med.manufacturer || '';
    document.getElementById('edit_pack_size').value = med.pack_size || '';
    document.getElementById('edit_unit').value = med.unit || '';
    document.getElementById('edit_hsn_code').value = med.hsn_code || '';
    document.getElementById('edit_gst_rate').value = med.gst_rate || '12.00';
    document.getElementById('edit_requires_prescription').value = med.requires_prescription || '0';
    openModal('editMedicineModal');
}
</script>
