<div class="page-header">
    <div class="page-title">
        <h1>Medicine Categories & Therapeutic Classifications</h1>
        <p>Organize medicines into antibiotics, antacids, vitamins, cardiovascular, diabetic, dermatological & surgical supplies.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addCategoryModal')">
            <span>➕ Add Category</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Clinical Description</th>
                    <th>Medicines Count</th>
                    <th>Total Active Units</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--primary);font-size:0.95rem;"><?= htmlspecialchars($cat['name']) ?></strong>
                        </td>
                        <td>
                            <code style="background:var(--border-light);padding:2px 6px;border-radius:4px;"><?= htmlspecialchars($cat['slug']) ?></code>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.85rem;">
                            <?= htmlspecialchars($cat['description'] ?? 'No description provided') ?>
                        </td>
                        <td>
                            <span class="nav-badge badge-info"><?= $cat['total_medicines'] ?> products</span>
                        </td>
                        <td>
                            <strong><?= number_format($cat['total_stock']) ?></strong> units
                        </td>
                        <td>
                            <a href="<?= $baseURL ?>/medicines?category_id=<?= $cat['id'] ?>" class="btn btn-sm btn-secondary" title="View Medicines">🔍 View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Category -->
<div id="addCategoryModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">➕ Add New Category</h3>
            <button class="modal-close" onclick="closeModal('addCategoryModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/categories/create">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Category / Classification Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Antibiotics, Antacids, Cardiovascular" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Therapeutic Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief clinical summary or therapeutic indications"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCategoryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Category</button>
            </div>
        </form>
    </div>
</div>
