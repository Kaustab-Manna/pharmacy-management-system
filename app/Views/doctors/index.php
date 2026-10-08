<div class="page-header">
    <div class="page-title">
        <h1>Doctor Management (Module 10)</h1>
        <p>Medical council registrations, medical specialties, hospital clinics, and prescriber referral analytics.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addDoctorModal')">
            <span>➕ Add Doctor</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Doctor Name</th>
                    <th>Medical Reg No.</th>
                    <th>Specialization</th>
                    <th>Hospital / Clinic</th>
                    <th>Contact</th>
                    <th>Prescriptions</th>
                    <th>Referred Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $d): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--primary);font-size:0.95rem;"><?= htmlspecialchars($d['name']) ?></strong>
                        </td>
                        <td><code><?= htmlspecialchars($d['registration_number']) ?></code></td>
                        <td><?= htmlspecialchars($d['specialization']) ?></td>
                        <td><?= htmlspecialchars($d['hospital_clinic']) ?></td>
                        <td>
                            <div><?= htmlspecialchars($d['phone']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($d['email'] ?? '') ?></div>
                        </td>
                        <td>
                            <span class="nav-badge badge-info"><?= $d['total_prescriptions'] ?> Rx records</span>
                        </td>
                        <td>
                            <span class="nav-badge badge-success"><?= $d['total_sales_referred'] ?> bills</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Doctor -->
<div id="addDoctorModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">➕ Register Doctor Profile</h3>
            <button class="modal-close" onclick="closeModal('addDoctorModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/doctors/create">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Doctor Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Dr. First Last, MD" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Medical Registration No *</label>
                        <input type="text" name="registration_number" class="form-control" placeholder="e.g. MCI-29182" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Specialization</label>
                        <input type="text" name="specialization" class="form-control" placeholder="e.g. Cardiology, Pediatrics, General" value="General Physician">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Hospital / Clinic Name</label>
                        <input type="text" name="hospital_clinic" class="form-control" placeholder="e.g. Lilavati Clinic">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 ...">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="doctor@clinic.com">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Clinic Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Clinic location details"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addDoctorModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Doctor Profile</button>
            </div>
        </form>
    </div>
</div>
