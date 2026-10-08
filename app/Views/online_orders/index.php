<div class="page-header">
    <div class="page-title">
        <h1>Online / E-Pharmacy Order Management (Module 31)</h1>
        <p>Customer web portal ordering, digital prescription uploads, verification workflow, and dispatch packing.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addWebOrderModal')">
            <span>🌐 Simulate Web Order</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer Name</th>
                    <th>Contact Phone</th>
                    <th>Delivery Address</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No online orders yet. Click 'Simulate Web Order' to test customer ordering.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($o['order_number']) ?></strong></td>
                            <td><?= htmlspecialchars($o['customer_name']) ?></td>
                            <td><?= htmlspecialchars($o['customer_phone']) ?></td>
                            <td style="font-size:12px;"><?= htmlspecialchars($o['delivery_address']) ?></td>
                            <td><?= htmlspecialchars($o['created_at']) ?></td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($o['total_amount'], 2) ?></strong></td>
                            <td>
                                <?php if ($o['status'] === 'delivered'): ?>
                                    <span class="nav-badge badge-success">Delivered</span>
                                <?php elseif ($o['status'] === 'processing'): ?>
                                    <span class="nav-badge badge-info">Processing / Packed</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-warning">Pending Verification</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="<?= $baseURL ?>/online-orders/update-status/<?= $o['id'] ?>" style="display:inline;">
                                    <?php if ($o['status'] === 'pending'): ?>
                                        <input type="hidden" name="status" value="processing">
                                        <button type="submit" class="btn btn-sm btn-primary">Verify & Pack</button>
                                    <?php elseif ($o['status'] === 'processing'): ?>
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="btn btn-sm btn-success">Mark Delivered</button>
                                    <?php else: ?>
                                        <span class="nav-badge badge-secondary">Completed</span>
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

<!-- Modal: Simulate Web Order -->
<div id="addWebOrderModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">🌐 Simulate Incoming Online Web Order</h3>
            <button class="modal-close" onclick="closeModal('addWebOrderModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/online-orders/create">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" value="Aakash Narang" required>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Mobile Number *</label>
                        <input type="text" name="customer_phone" class="form-control" value="+91 98200 77112" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Email</label>
                        <input type="email" name="customer_email" class="form-control" value="aakash@gmail.com">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Address *</label>
                    <textarea name="delivery_address" class="form-control" rows="2" required>Flat 304, Tower B, Palm Beach Road, Vashi</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Order Notes / Prescription Info</label>
                    <input type="text" name="notes" class="form-control" value="Delivery between 5-7 PM please">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addWebOrderModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Web Order</button>
            </div>
        </form>
    </div>
</div>
