<div class="page-header">
    <div class="page-title">
        <h1>Delivery Management & Dispatch Desk (Module 32)</h1>
        <p>Assign home delivery orders to riders, track real-time fulfillment status, and monitor courier delivery charges.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addDeliveryModal')">
            <span>🛵 New Delivery Dispatch</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Tracking #</th>
                    <th>Customer Name</th>
                    <th>Contact Phone</th>
                    <th>Delivery Address</th>
                    <th>Delivery Executive (Rider)</th>
                    <th>Delivery Fee</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deliveries)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No active deliveries in transit.</td></tr>
                <?php else: ?>
                    <?php foreach ($deliveries as $d): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($d['tracking_number']) ?></strong></td>
                            <td><?= htmlspecialchars($d['customer_name']) ?></td>
                            <td><?= htmlspecialchars($d['customer_phone']) ?></td>
                            <td style="font-size:12px;"><?= htmlspecialchars($d['delivery_address']) ?></td>
                            <td>
                                <div><strong><?= htmlspecialchars($d['delivery_executive_name']) ?></strong></div>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($d['delivery_executive_phone']) ?></div>
                            </td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($d['delivery_fee'], 2) ?></td>
                            <td>
                                <?php if ($d['status'] === 'delivered'): ?>
                                    <span class="nav-badge badge-success">Delivered</span>
                                <?php elseif ($d['status'] === 'out_for_delivery'): ?>
                                    <span class="nav-badge badge-warning">Out For Delivery</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-info">Assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="<?= $baseURL ?>/delivery/update-status/<?= $d['id'] ?>" style="display:inline;">
                                    <?php if ($d['status'] === 'assigned'): ?>
                                        <input type="hidden" name="status" value="out_for_delivery">
                                        <button type="submit" class="btn btn-sm btn-primary">Dispatch</button>
                                    <?php elseif ($d['status'] === 'out_for_delivery'): ?>
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="btn btn-sm btn-success">Mark Delivered</button>
                                    <?php else: ?>
                                        <span class="nav-badge badge-secondary">Delivered</span>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Delivery Dispatch -->
<div id="addDeliveryModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">🛵 Assign Home Delivery Dispatch</h3>
            <button class="modal-close" onclick="closeModal('addDeliveryModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/delivery/create">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="Patient / Customer">
                </div>
                <div class="form-group">
                    <label class="form-label">Mobile Phone Number *</label>
                    <input type="text" name="customer_phone" class="form-control" required placeholder="+91 ...">
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Street Address *</label>
                    <textarea name="delivery_address" class="form-control" rows="2" required placeholder="Full residential drop-off address"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Delivery Executive / Rider</label>
                        <input type="text" name="delivery_executive_name" class="form-control" value="Suresh (Pharmacy Rider #1)">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Rider Mobile</label>
                        <input type="text" name="delivery_executive_phone" class="form-control" value="+91 98200 88221">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Fee (₹)</label>
                    <input type="number" step="0.01" name="delivery_fee" class="form-control" value="40.00">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addDeliveryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Dispatch Delivery</button>
            </div>
        </form>
    </div>
</div>
