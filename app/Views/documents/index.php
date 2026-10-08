<div class="page-header">
    <div class="page-title">
        <h1>Central Document & License Archive (Module 34)</h1>
        <p>Safely archive prescription scans, drug licenses (20B/21B), supplier contracts, and regulatory certificates.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('uploadDocModal')">
            <span>📁 Upload Document</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Document Title</th>
                    <th>Category</th>
                    <th>File Type</th>
                    <th>Size</th>
                    <th>Uploaded Date</th>
                    <th>Archived By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No documents archived yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($doc['title']) ?></strong>
                            </td>
                            <td><span class="nav-badge badge-info"><?= htmlspecialchars($doc['category']) ?></span></td>
                            <td><?= htmlspecialchars($doc['file_type']) ?></td>
                            <td><?= $doc['file_size_kb'] ?> KB</td>
                            <td><?= htmlspecialchars($doc['created_at']) ?></td>
                            <td><?= htmlspecialchars($doc['uploaded_by_name'] ?? 'Admin') ?></td>
                            <td>
                                <a href="<?= $baseURL ?>/<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-secondary">
                                    👁️ View / Download
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Upload Document -->
<div id="uploadDocModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">📁 Archive New Document</h3>
            <button class="modal-close" onclick="closeModal('uploadDocModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/documents/upload" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Document Title *</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Drug License 20B Renewal 2026" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-control">
                        <option value="Drug License">Drug License (20B / 21B)</option>
                        <option value="Registration">Pharmacy Registration & GST Cert</option>
                        <option value="Prescription">Prescription Scan</option>
                        <option value="Supplier Invoice">Supplier Bill / LR Copy</option>
                        <option value="Product Sheet">Product Monograph / COA</option>
                        <option value="Customer Record">Patient Medical History Document</option>
                        <option value="Other">Other Certificate</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Select File (PDF / Image) *</label>
                    <input type="file" name="doc_file" class="form-control" required accept=".pdf,image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('uploadDocModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Archive File</button>
            </div>
        </form>
    </div>
</div>
