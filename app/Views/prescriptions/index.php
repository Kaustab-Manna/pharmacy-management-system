<div class="page-header">
    <div class="page-title">
        <h1>Prescription Management & Digital Archive</h1>
        <p>Digitize doctor prescriptions, verify Schedule H/X compliance, and track dispensed medications.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/prescription-billing" class="btn btn-secondary">
            <span>🩺 Prescription Billing</span>
        </a>
        <button class="btn btn-primary" onclick="openModal('addPrescriptionModal')">
            <span>➕ Upload / New Prescription</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Prescription #</th>
                    <th>Date</th>
                    <th>Patient Name</th>
                    <th>Doctor</th>
                    <th>Allergies Alert</th>
                    <th>Verification</th>
                    <th>Approved By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($prescriptions)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No prescriptions on file.</td></tr>
                <?php else: ?>
                    <?php foreach ($prescriptions as $rx): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);font-size:0.95rem;"><?= htmlspecialchars($rx['prescription_number']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($rx['prescription_date']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($rx['patient_name']) ?></strong>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($rx['patient_phone']) ?></div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($rx['doctor_name'] ?? 'Not Specified') ?></div>
                                <div style="font-size:10px;color:var(--text-muted);"><?= htmlspecialchars($rx['doctor_spec'] ?? '') ?></div>
                            </td>
                            <td>
                                <?php if (!empty($rx['allergies']) && strtolower($rx['allergies']) !== 'none'): ?>
                                    <span class="nav-badge badge-danger" title="<?= htmlspecialchars($rx['allergies']) ?>">⚠️ <?= htmlspecialchars($rx['allergies']) ?></span>
                                <?php else: ?>
                                    <span style="font-size:11px;color:var(--text-muted);">None reported</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($rx['status'] === 'verified'): ?>
                                    <span class="nav-badge badge-success">Verified</span>
                                <?php elseif ($rx['status'] === 'completed'): ?>
                                    <span class="nav-badge badge-info">Dispensed</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-warning">Pending Review</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size:12px;"><?= htmlspecialchars($rx['pharmacist_name'] ?? 'Pending') ?></span>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    <?php if ($rx['status'] === 'pending'): ?>
                                        <form method="POST" action="<?= $baseURL ?>/prescriptions/verify/<?= $rx['id'] ?>" style="display:inline;">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve as Pharmacist">✓ Approve</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="<?= $baseURL ?>/prescription-billing?patient_id=<?= $rx['patient_id'] ?>&prescription_id=<?= $rx['id'] ?>" class="btn btn-sm btn-primary" title="Bill this Prescription">⚡ Bill</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Upload / Create Prescription -->
<div id="addPrescriptionModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title">📝 Register New Prescription</h3>
            <button class="modal-close" onclick="closeModal('addPrescriptionModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/prescriptions/create" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Patient *</label>
                        <select name="patient_id" class="form-control" required>
                            <option value="">Select Registered Patient</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['phone']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Prescribing Doctor</label>
                        <select name="doctor_id" class="form-control">
                            <option value="">Select Doctor</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['hospital_clinic']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Prescription Date</label>
                        <input type="date" name="prescription_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Prescription Document / Scan Attachment (Image / PDF)</label>
                    <input type="file" name="prescription_file" class="form-control" accept="image/*,application/pdf">
                </div>

                <!-- Prescribed Items Dynamic Block -->
                <div style="margin-top:1.25rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                        <span style="font-weight:700;font-size:0.85rem;">💊 Prescribed Medications:</span>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addRxItemRow()">+ Add Medicine</button>
                    </div>
                    <div id="rxItemsContainer" style="display:flex;flex-direction:column;gap:8px;">
                        <div class="form-row" style="background:var(--bg-body);padding:8px;border-radius:6px;align-items:center;">
                            <div style="flex:2;">
                                <select name="med_id[]" class="form-control" required>
                                    <option value="">Select Medicine</option>
                                    <?php foreach ($medicines as $m): ?>
                                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['brand_name']) ?> (<?= htmlspecialchars($m['name']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex:1;">
                                <input type="text" name="dosage[]" class="form-control" placeholder="Dosage (e.g. 1 Tab)" value="1 Tablet">
                            </div>
                            <div style="flex:1;">
                                <input type="text" name="frequency[]" class="form-control" placeholder="Frequency" value="1-0-1 (After Food)">
                            </div>
                            <div style="flex:1;">
                                <input type="text" name="duration[]" class="form-control" placeholder="Duration" value="5 Days">
                            </div>
                            <div style="flex:0.8;">
                                <input type="number" name="qty[]" class="form-control" placeholder="Qty" value="10">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Pharmacist Clinical Notes & Verification Remarks</label>
                    <textarea name="pharmacist_notes" class="form-control" rows="2" placeholder="e.g. Dosage verified, explained intake instructions to patient..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addPrescriptionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save & Verify Prescription</button>
            </div>
        </form>
    </div>
</div>

<script>
function addRxItemRow() {
    var container = document.getElementById('rxItemsContainer');
    var firstRow = container.querySelector('.form-row');
    var clone = firstRow.cloneNode(true);
    container.appendChild(clone);
}

document.addEventListener('DOMContentLoaded', function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('open') === 'upload' || params.get('upload') === '1') {
        openModal('addPrescriptionModal');
    }
});
</script>
