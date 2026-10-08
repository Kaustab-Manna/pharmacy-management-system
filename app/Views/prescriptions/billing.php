<div class="page-header">
    <div class="page-title">
        <h1>Prescription-Based Billing (Module 13)</h1>
        <p>Seamlessly bridge verified doctor prescriptions directly to billing with FEFO stock allocation.</p>
    </div>
</div>

<!-- Step 1: Select Patient & Prescription -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">🔍 Step 1: Select Patient & Active Prescription</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= $baseURL ?>/prescription-billing" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:250px;">
                <label class="form-label">Select Patient</label>
                <select name="patient_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Choose Registered Patient --</option>
                    <?php foreach ($patients as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($patientId == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['phone']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!empty($activePrescriptions)): ?>
                <div style="flex:1;min-width:250px;">
                    <label class="form-label">Select Active Prescription</label>
                    <select name="prescription_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Choose Prescription --</option>
                        <?php foreach ($activePrescriptions as $rx): ?>
                            <option value="<?= $rx['id'] ?>" <?= ($prescriptionId == $rx['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rx['prescription_number']) ?> (Date: <?= $rx['prescription_date'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Step 2: Prescribed Medicines & Stock Verification -->
<?php if (!empty($prescriptionId)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">💊 Step 2: Prescribed Items & Stock Availability Check</h3>
            <span class="nav-badge badge-success">Verified By Pharmacist</span>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Prescribed Medicine</th>
                        <th>Dosage & Frequency</th>
                        <th>Duration</th>
                        <th>Prescribed Qty</th>
                        <th>In-Stock Status</th>
                        <th>Suggested Batch (FEFO)</th>
                        <th>Dispense Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $canFulfillAll = true;
                    foreach ($prescribedItems as $item): 
                        $inStock = ($item['available_stock'] >= $item['qty_prescribed']);
                        if (!$inStock) $canFulfillAll = false;
                    ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($item['brand_name']) ?></strong>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($item['medicine_name']) ?> (<?= htmlspecialchars($item['pack_size']) ?>)</div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($item['dosage']) ?></div>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($item['frequency']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($item['duration']) ?></td>
                            <td><strong><?= $item['qty_prescribed'] ?></strong> <?= htmlspecialchars($item['unit']) ?></td>
                            <td>
                                <?php if ($item['available_stock'] <= 0): ?>
                                    <span class="nav-badge badge-danger">Out of Stock</span>
                                <?php elseif ($item['available_stock'] < $item['qty_prescribed']): ?>
                                    <span class="nav-badge badge-warning">Partial (<?= $item['available_stock'] ?> avail)</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-success">In Stock (<?= $item['available_stock'] ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['suggested_batch_number'])): ?>
                                    <code><?= htmlspecialchars($item['suggested_batch_number']) ?></code>
                                    <span class="fefo-pill" style="margin-left:4px;">FEFO Earliest</span>
                                <?php else: ?>
                                    <span style="color:#ef4444;font-size:11px;">No active batch</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="number" class="form-control" style="width:80px;padding:4px 8px;font-weight:bold;" value="<?= min($item['qty_prescribed'], $item['available_stock']) ?>" max="<?= $item['available_stock'] ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;color:var(--text-muted);">
                Pharmacist verification complete. Items ready to transfer to POS counter.
            </div>
            <a href="<?= $baseURL ?>/pos" class="btn btn-primary">
                ⚡ Transfer to POS Counter for Billing &rarr;
            </a>
        </div>
    </div>
<?php endif; ?>
