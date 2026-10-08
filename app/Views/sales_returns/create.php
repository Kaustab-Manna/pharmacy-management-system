<div class="page-header">
    <div class="page-title">
        <h1>Process Customer Return - Invoice #<?= htmlspecialchars($sale['invoice_number'] ?? '') ?></h1>
        <p>Select items and quantities to accept back into active pharmacy stock.</p>
    </div>
</div>

<?php if (!$sale): ?>
    <div class="alert alert-error">Please select a valid invoice from the returns register.</div>
<?php else: ?>
    <div class="card" style="max-width: 800px;">
        <div class="card-header">
            <h3 class="card-title">🧾 Return Items from Invoice #<?= htmlspecialchars($sale['invoice_number']) ?></h3>
            <span class="nav-badge badge-info"><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in') ?></span>
        </div>
        <form method="POST" action="<?= $baseURL ?>/sales-returns/create">
            <input type="hidden" name="sale_id" value="<?= $sale['id'] ?>">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Select Sold Item to Return *</label>
                    <select name="sale_item_id" class="form-control" required>
                        <?php foreach ($items as $it): ?>
                            <option value="<?= $it['id'] ?>">
                                <?= htmlspecialchars($it['brand_name']) ?> (Batch: <?= htmlspecialchars($it['batch_number']) ?>) - Sold Qty: <?= $it['quantity'] ?> @ <?= $pharmacy['currency_symbol'] ?><?= number_format($it['unit_price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Quantity Returned *</label>
                        <input type="number" name="quantity" class="form-control" placeholder="1" min="1" value="1" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Refund Mode</label>
                        <select name="refund_type" class="form-control">
                            <option value="cash_refund">Cash Refund</option>
                            <option value="credit_note">Store Credit Note (Customer Balance)</option>
                            <option value="upi_refund">UPI Refund</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Reason for Return *</label>
                    <select name="reason" class="form-control" required>
                        <option value="Customer changed mind / unused">Customer changed mind / unused package</option>
                        <option value="Doctor changed prescription">Doctor changed prescription</option>
                        <option value="Adverse reaction / allergy">Adverse reaction / allergy reported</option>
                        <option value="Damaged seal upon opening">Damaged seal upon opening</option>
                    </select>
                </div>
            </div>
            <div class="card-footer" style="display:flex;justify-content:flex-end;gap:10px;">
                <a href="<?= $baseURL ?>/sales-returns" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Process Return & Restock</button>
            </div>
        </form>
    </div>
<?php endif; ?>
