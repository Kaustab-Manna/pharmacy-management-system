<div class="page-header">
    <div class="page-title">
        <h1>Loyalty & Rewards Management (Module 28)</h1>
        <p>Customer retention tiers (Bronze, Silver, Gold, Platinum), points accumulation, and discount coupons.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addCouponModal')">
            <span>➕ Create Coupon Code</span>
        </button>
    </div>
</div>

<!-- Loyalty Tiers Hierarchy -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Bronze Tier</h3>
            <div class="stat-number" style="font-size:1.4rem;">Standard</div>
            <div class="stat-subtext">Base Tier • 1 pt per ₹100</div>
        </div>
        <div class="stat-icon icon-teal">🥉</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Silver Tier</h3>
            <div class="stat-number" style="font-size:1.4rem;">Spend &gt; ₹5,000</div>
            <div class="stat-subtext">5% Auto-discount</div>
        </div>
        <div class="stat-icon icon-blue">🥈</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Gold Tier</h3>
            <div class="stat-number" style="font-size:1.4rem;">Spend &gt; ₹15,000</div>
            <div class="stat-subtext">8% Auto-discount + Free Home Delivery</div>
        </div>
        <div class="stat-icon icon-amber">🥇</div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-info">
            <h3>Platinum Tier</h3>
            <div class="stat-number" style="font-size:1.4rem;">Spend &gt; ₹30,000</div>
            <div class="stat-subtext">10% VIP discount + Priority Clinical Support</div>
        </div>
        <div class="stat-icon icon-purple">💎</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">
    
    <!-- Active Coupons Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🎁 Promotional Coupons & Discount Vouchers</h3>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount Value</th>
                        <th>Min Order</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $c): ?>
                        <tr>
                            <td>
                                <strong style="font-family:var(--font-mono);color:var(--primary);"><?= htmlspecialchars($c['code']) ?></strong>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($c['description']) ?></div>
                            </td>
                            <td>
                                <strong><?= $c['discount_type'] === 'percentage' ? $c['discount_value'] . '%' : $pharmacy['currency_symbol'] . $c['discount_value'] ?> OFF</strong>
                            </td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($c['min_order_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($c['expiry_date']) ?></td>
                            <td>
                                <?php if ($c['is_active']): ?>
                                    <span class="nav-badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="nav-badge badge-secondary">Expired</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Loyalty Point Earners -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">⭐ Top Customer Loyalty Balances</h3>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Mobile</th>
                        <th>Current Points</th>
                        <th>Cashback Value</th>
                        <th>Tier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $pat): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($pat['name']) ?></strong></td>
                            <td><?= htmlspecialchars($pat['phone']) ?></td>
                            <td><strong style="color:var(--primary);font-size:1.05rem;"><?= $pat['loyalty_points'] ?></strong> pts</td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($pat['loyalty_points'] * 1.0, 2) ?></td>
                            <td><span class="nav-badge badge-info"><?= htmlspecialchars($pat['loyalty_tier']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Add Coupon -->
<div id="addCouponModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h3 class="modal-title">🎁 Create Discount Coupon</h3>
            <button class="modal-close" onclick="closeModal('addCouponModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/loyalty/create-coupon">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Coupon Code (e.g. WELLNESS15) *</label>
                    <input type="text" name="code" class="form-control" style="text-transform:uppercase;font-weight:bold;" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g. Festive discount on all medicines">
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Discount Type</label>
                        <select name="discount_type" class="form-control">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₹)</option>
                        </select>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Discount Value *</label>
                        <input type="number" step="0.01" name="discount_value" class="form-control" placeholder="10" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Min Order Value (₹)</label>
                        <input type="number" step="0.01" name="min_order_amount" class="form-control" value="0.00">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Valid Until</label>
                        <input type="date" name="expiry_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 months')) ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCouponModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Coupon</button>
            </div>
        </form>
    </div>
</div>
