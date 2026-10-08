<div class="page-header">
    <div class="page-title">
        <h1>Patient & Customer Management (Module 11)</h1>
        <p>Patient medical profiles, known drug allergies, loyalty tiers, lifetime spend, and ledger balances.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportTableToCSV('patientsTable', 'patient_directory.csv')">
            <span>📥 Export CSV</span>
        </button>
        <button class="btn btn-primary" onclick="openModal('addPatientModal')">
            <span>➕ Register Patient</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">👥 Patient Master Directory</h3>
        <form method="GET" action="<?= $baseURL ?>/patients" style="display:flex;gap:6px;">
            <input type="text" name="search" class="form-control" placeholder="Search patient..." value="<?= htmlspecialchars($search ?? '') ?>" style="font-size:12px;padding:4px 8px;width:200px;">
            <button type="submit" class="btn btn-sm btn-secondary">Search</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table-custom" id="patientsTable">
            <thead>
                <tr>
                    <th>Patient ID / Name</th>
                    <th>Age / Gender</th>
                    <th>Phone / Email</th>
                    <th>Known Drug Allergies</th>
                    <th>Loyalty Points</th>
                    <th>Tier</th>
                    <th>Outstanding Dues</th>
                    <th>Visits / Spend</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patients)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No patients found.</td></tr>
                <?php else: ?>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);font-size:0.95rem;"><?= htmlspecialchars($p['name']) ?></strong>
                                <div style="font-size:11px;color:var(--text-muted);font-family:var(--font-mono);"><?= htmlspecialchars($p['patient_code']) ?></div>
                            </td>
                            <td><?= $p['age'] ?> yrs / <?= htmlspecialchars($p['gender']) ?></td>
                            <td>
                                <div><?= htmlspecialchars($p['phone']) ?></div>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($p['email'] ?? '') ?></div>
                            </td>
                            <td>
                                <?php if (!empty($p['allergies']) && strtolower($p['allergies']) !== 'none'): ?>
                                    <span class="nav-badge badge-danger">⚠️ <?= htmlspecialchars($p['allergies']) ?></span>
                                <?php else: ?>
                                    <span style="font-size:11px;color:var(--text-muted);">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color:#0d9488;font-size:1rem;"><?= $p['loyalty_points'] ?></strong> pts
                            </td>
                            <td>
                                <span class="nav-badge badge-info"><?= htmlspecialchars($p['loyalty_tier']) ?></span>
                            </td>
                            <td>
                                <?php if ($p['outstanding_balance'] > 0): ?>
                                    <strong style="color:#ef4444;"><?= $pharmacy['currency_symbol'] ?><?= number_format($p['outstanding_balance'], 2) ?></strong>
                                <?php else: ?>
                                    <span class="nav-badge badge-success">Nil</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= $p['total_visits'] ?> visits</div>
                                <div style="font-size:11px;color:var(--text-muted);"><?= $pharmacy['currency_symbol'] ?><?= number_format($p['total_spend'], 2) ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Register Patient -->
<div id="addPatientModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">➕ Register Patient Profile</h3>
            <button class="modal-close" onclick="closeModal('addPatientModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/patients/create">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Patient Name" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Mobile Phone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 ..." required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Age</label>
                        <input type="number" name="age" class="form-control" placeholder="Age" value="30">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-control">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Blood Group</label>
                        <input type="text" name="blood_group" class="form-control" placeholder="e.g. B+, O+">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="color:#ef4444;">Known Drug Allergies (CRITICAL CLINICAL ALERT)</label>
                    <input type="text" name="allergies" class="form-control" placeholder="e.g. Penicillin, Sulfa, Aspirin (or None)" value="None">
                </div>

                <div class="form-group">
                    <label class="form-label">Chronic Conditions / Health Notes</label>
                    <input type="text" name="chronic_conditions" class="form-control" placeholder="e.g. Diabetic, Hypertension, Asthma" value="None">
                </div>

                <div class="form-group">
                    <label class="form-label">Address / Locality</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Residential delivery address"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addPatientModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Patient</button>
            </div>
        </form>
    </div>
</div>
