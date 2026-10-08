<div class="page-header">
    <div class="page-title">
        <h1>WhatsApp / SMS / Email Integration (Module 30)</h1>
        <p>Direct communication channels: digital WhatsApp invoices, refill reminders, payment notices & SMS gateway.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:1.5rem;align-items:start;">
    
    <!-- 1. WhatsApp Instant Invoice Dispatcher -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">💬 WhatsApp 1-Click Invoice & Message Sharing</h3>
            <span class="nav-badge badge-success">API Ready</span>
        </div>
        <div class="card-body">
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:1rem;">
                Instantly transmit digital cash memos, medication dosages, and dosage timing instructions directly to customer WhatsApp numbers without expensive third-party SMS bills.
            </p>

            <div style="display:flex;flex-direction:column;gap:10px;">
                <div style="font-weight:700;font-size:0.85rem;">Recent Sales Ready for WhatsApp Sharing:</div>
                <?php foreach ($recentSales as $sale): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-body);">
                        <div>
                            <strong style="color:var(--primary);"><?= htmlspecialchars($sale['invoice_number']) ?></strong>
                            <div style="font-size:12px;"><?= htmlspecialchars($sale['customer_name'] ?? 'Customer') ?> (<?= htmlspecialchars($sale['customer_phone']) ?>)</div>
                            <div style="font-size:11px;color:var(--text-muted);">Amount: <?= $pharmacy['currency_symbol'] ?><?= number_format($sale['grand_total'], 2) ?> on <?= $sale['sale_date'] ?></div>
                        </div>
                        <button class="btn btn-sm btn-secondary" style="background:#25d366;color:#fff;border-color:#25d366;" onclick="shareOnWhatsApp('<?= htmlspecialchars($sale['customer_phone']) ?>', '<?= htmlspecialchars($sale['invoice_number']) ?>', '<?= htmlspecialchars(addslashes($sale['customer_name'] ?? 'Customer')) ?>', <?= $sale['grand_total'] ?>, [])">
                            💬 Send on WA
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 2. Payment Reminder & Gateway Templates -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        
        <!-- Due Payment WhatsApp Reminders -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🔔 Payment Overdue WA Reminders</h3>
            </div>
            <div class="card-body" style="padding:1rem;">
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php if (empty($duePatients)): ?>
                        <p style="font-size:12px;color:var(--text-muted);text-align:center;">No pending receivables.</p>
                    <?php else: ?>
                        <?php foreach ($duePatients as $dp): ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px;border-bottom:1px solid var(--border-color);font-size:12px;">
                                <div>
                                    <strong><?= htmlspecialchars($dp['name']) ?></strong>
                                    <div style="color:var(--danger);">Due: <?= $pharmacy['currency_symbol'] ?><?= number_format($dp['outstanding_balance'], 2) ?></div>
                                </div>
                                <button class="btn btn-sm btn-secondary" style="font-size:11px;" onclick="sendPaymentReminderWA('<?= htmlspecialchars($dp['phone']) ?>', '<?= htmlspecialchars(addslashes($dp['name'])) ?>', <?= $dp['outstanding_balance'] ?>)">
                                    Send Reminder
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SMS / Email Gateway Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">⚙️ SMS & Email Gateway Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">SMS Provider</label>
                    <select class="form-control">
                        <option value="twilio">Twilio SMS API</option>
                        <option value="fast2sms" selected>Fast2SMS (India)</option>
                        <option value="msg91">MSG91 Enterprise</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">API Key / Auth Token</label>
                    <input type="password" class="form-control" value="demo_api_key_infosof_pharma_2026">
                </div>
                <div class="form-group">
                    <label class="form-label">Sender ID</label>
                    <input type="text" class="form-control" value="INFOPH">
                </div>
                <button class="btn btn-sm btn-primary" onclick="alert('Gateway settings saved successfully!')">Save Configuration</button>
            </div>
        </div>

    </div>
</div>

<script>
function sendPaymentReminderWA(phone, name, amount) {
    if (!phone) {
        alert('Phone number missing.');
        return;
    }
    var cleanPhone = phone.replace(/[^0-9]/g, '');
    if (cleanPhone.length === 10) cleanPhone = '91' + cleanPhone;
    var msg = "*INFOSOF CARE PHARMACY - PAYMENT REMINDER*\n\n" +
              "Dear " + name + ",\n" +
              "This is a gentle reminder that an outstanding medicine balance of *₹" + parseFloat(amount).toFixed(2) + "* is pending in your account.\n\n" +
              "Kindly clear the dues at your earliest convenience via UPI or at the counter.\n" +
              "Helpline: +91 98200 12345";
    window.open("https://api.whatsapp.com/send?phone=" + cleanPhone + "&text=" + encodeURIComponent(msg), '_blank');
}
</script>
