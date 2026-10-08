<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= htmlspecialchars($sale['invoice_number']) ?> - INFOSOF Pharmacy</title>
    <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/print.css">
    <style>
        .invoice-action-bar {
            max-width: 860px;
            margin: 20px auto 10px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }
    </style>
</head>
<body class="<?= ($format === 'thermal') ? 'print-thermal' : '' ?>">

<!-- Floating Action Bar for Cashier -->
<div class="invoice-action-bar no-print">
    <div>
        <strong>Invoice #<?= htmlspecialchars($sale['invoice_number']) ?></strong>
        <span style="font-size:12px;color:var(--text-muted);margin-left:8px;"><?= $sale['sale_date'] ?></span>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="btn btn-sm btn-primary" onclick="printStandardInvoice()">📄 Print A4 Invoice</button>
        <button class="btn btn-sm btn-secondary" onclick="printThermalReceipt()">🧾 Print 80mm Thermal</button>
        <button class="btn btn-sm btn-secondary" style="background:#25d366;color:#fff;border-color:#25d366;" onclick="shareOnWhatsApp('<?= htmlspecialchars($sale['customer_phone'] ?? '') ?>', '<?= htmlspecialchars($sale['invoice_number']) ?>', '<?= htmlspecialchars(addslashes($sale['customer_name'] ?? '')) ?>', <?= $sale['grand_total'] ?>, [])">
            💬 WhatsApp
        </button>
        <a href="<?= $baseURL ?>/sales" class="btn btn-sm btn-secondary">Close</a>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. STANDARD A4 / A5 TAX INVOICE FORMAT         -->
<!-- ============================================== -->
<div class="standard-invoice-container">
    <div class="invoice-header-box">
        <div>
            <h1 style="color:#0d9488;font-size:1.6rem;font-weight:800;margin-bottom:4px;"><?= htmlspecialchars($pharmacy['pharmacy_name']) ?></h1>
            <div style="font-size:11px;color:#475569;line-height:1.4;">
                <div><?= htmlspecialchars($pharmacy['legal_name']) ?></div>
                <div><?= nl2br(htmlspecialchars($pharmacy['address'])) ?></div>
                <div><strong>GSTIN:</strong> <?= htmlspecialchars($pharmacy['gstin']) ?> | <strong>DL No:</strong> <?= htmlspecialchars($pharmacy['drug_license_no']) ?></div>
                <div><strong>Reg Pharmacist:</strong> <?= htmlspecialchars($pharmacy['pharmacist_name']) ?> (<?= htmlspecialchars($pharmacy['pharmacist_reg_no']) ?>)</div>
                <div>Phone: <?= htmlspecialchars($pharmacy['phone']) ?> | Email: <?= htmlspecialchars($pharmacy['email']) ?></div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="display:inline-block;background:#0d9488;color:#fff;padding:4px 12px;border-radius:4px;font-size:12px;font-weight:bold;letter-spacing:1px;margin-bottom:6px;">
                TAX INVOICE
            </div>
            <div style="font-size:13px;font-weight:bold;">Invoice: <?= htmlspecialchars($sale['invoice_number']) ?></div>
            <div style="font-size:11px;color:#64748b;">Date: <?= date('d-M-Y H:i', strtotime($sale['sale_date'])) ?></div>
            <div style="font-size:11px;color:#64748b;">Cashier: <?= htmlspecialchars($sale['cashier_name'] ?? 'Counter') ?></div>
        </div>
    </div>

    <!-- Bill To / Patient Details -->
    <div class="invoice-bill-to">
        <div>
            <div style="font-size:11px;font-weight:bold;color:#64748b;text-transform:uppercase;">Patient / Customer Info:</div>
            <div style="font-size:13px;font-weight:bold;color:#0f172a;margin-top:2px;"><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Cash Customer') ?></div>
            <?php if (!empty($sale['customer_phone'])): ?>
                <div style="font-size:11px;color:#475569;">Mobile: <?= htmlspecialchars($sale['customer_phone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($sale['patient_address'])): ?>
                <div style="font-size:11px;color:#475569;"><?= htmlspecialchars($sale['patient_address']) ?></div>
            <?php endif; ?>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;font-weight:bold;color:#64748b;text-transform:uppercase;">Doctor & Payment:</div>
            <div style="font-size:12px;color:#0f172a;">Doctor: <strong><?= htmlspecialchars($sale['doctor_name'] ?? 'Self / OTC') ?></strong></div>
            <?php if (!empty($sale['doctor_clinic'])): ?>
                <div style="font-size:11px;color:#475569;">Clinic: <?= htmlspecialchars($sale['doctor_clinic']) ?></div>
            <?php endif; ?>
            <?php if (!empty($sale['doctor_reg'])): ?>
                <div style="font-size:10px;color:#64748b;">Reg: <?= htmlspecialchars($sale['doctor_reg']) ?></div>
            <?php endif; ?>
            <div style="font-size:11px;color:#475569;">Payment Mode: <strong style="text-transform:uppercase;"><?= htmlspecialchars($sale['payment_mode']) ?></strong> (<?= strtoupper($sale['payment_status']) ?>)</div>
        </div>
    </div>

    <!-- Itemized Medicine Table -->
    <table class="table-custom" style="width:100%;margin-bottom:20px;font-size:12px;border-collapse:collapse;">
        <thead>
            <tr style="background:#f1f5f9;border-top:1px solid #cbd5e1;border-bottom:1px solid #cbd5e1;">
                <th style="width:4%;text-align:center;padding:8px 6px;">#</th>
                <th style="text-align:left;padding:8px 8px;">Medicine Description</th>
                <th style="white-space:nowrap;text-align:left;padding:8px 6px;">Batch No</th>
                <th style="white-space:nowrap;text-align:center;padding:8px 6px;">Exp Date</th>
                <th style="white-space:nowrap;text-align:center;padding:8px 6px;">Qty</th>
                <th style="white-space:nowrap;text-align:right;padding:8px 6px;">MRP</th>
                <th style="white-space:nowrap;text-align:right;padding:8px 6px;">Rate</th>
                <th style="white-space:nowrap;text-align:center;padding:8px 6px;">Disc%</th>
                <th style="white-space:nowrap;text-align:center;padding:8px 6px;">Tax%</th>
                <th style="white-space:nowrap;text-align:right;padding:8px 8px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($items as $item): ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="text-align:center;padding:8px 6px;"><?= $i++ ?></td>
                    <td style="padding:8px 8px;">
                        <strong><?= htmlspecialchars($item['brand_name'] ?? $item['medicine_name'] ?? '') ?></strong>
                        <div style="font-size:10px;color:#64748b;">
                            <?= htmlspecialchars($item['medicine_name'] ?? '') ?><?= !empty($item['pack_size']) ? ' (' . htmlspecialchars($item['pack_size']) . ')' : (!empty($item['strength']) ? ' (' . htmlspecialchars($item['strength']) . ')' : '') ?>
                        </div>
                    </td>
                    <td style="white-space:nowrap;padding:8px 6px;"><code><?= htmlspecialchars($item['batch_number'] ?? '') ?></code></td>
                    <td style="white-space:nowrap;text-align:center;padding:8px 6px;"><?= htmlspecialchars($item['expiry_date'] ?? '') ?></td>
                    <td style="white-space:nowrap;text-align:center;padding:8px 6px;"><strong><?= $item['quantity'] ?? 0 ?></strong> <?= htmlspecialchars($item['unit'] ?? '') ?></td>
                    <td style="white-space:nowrap;text-align:right;padding:8px 6px;"><?= $pharmacy['currency_symbol'] ?><?= number_format($item['mrp'] ?? 0, 2) ?></td>
                    <td style="white-space:nowrap;text-align:right;padding:8px 6px;"><?= $pharmacy['currency_symbol'] ?><?= number_format($item['unit_price'] ?? 0, 2) ?></td>
                    <td style="white-space:nowrap;text-align:center;padding:8px 6px;"><?= $item['discount_percent'] ?? 0 ?>%</td>
                    <td style="white-space:nowrap;text-align:center;padding:8px 6px;"><?= $item['gst_rate'] ?? 0 ?>%</td>
                    <td style="white-space:nowrap;text-align:right;font-weight:bold;padding:8px 8px;"><?= $pharmacy['currency_symbol'] ?><?= number_format($item['total_amount'] ?? 0, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals & GST Summary Box -->
    <div style="display:grid;grid-template-columns: 1.2fr 1fr; gap:20px; margin-bottom: 25px;">
        <!-- GST Tax Split -->
        <div style="background:#f8fafc;padding:12px;border-radius:8px;border:1px solid #e2e8f0;font-size:11px;">
            <div style="font-weight:bold;margin-bottom:6px;color:#475569;">TAX (GST) BREAKDOWN SUMMARY:</div>
            <div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                <span>Total Taxable Subtotal:</span>
                <strong><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['subtotal'] - $sale['discount_amount'], 2) ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                <span>CGST (Central GST):</span>
                <span><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['cgst_amount'], 2) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                <span>SGST (State GST):</span>
                <span><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['sgst_amount'], 2) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;border-top:1px dashed #cbd5e1;padding-top:4px;font-weight:bold;">
                <span>Total GST Component:</span>
                <span><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['tax_amount'], 2) ?></span>
            </div>
        </div>

        <!-- Grand Total Summary -->
        <div style="background:#f0fdfa;padding:12px;border-radius:8px;border:1px solid #99f6e4;font-size:12px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span>Subtotal:</span>
                <span><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['subtotal'], 2) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;color:#ef4444;">
                <span>Discount:</span>
                <span>- <?= $pharmacy['currency_symbol'] ?><?= number_format($sale['discount_amount'], 2) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span>Taxes:</span>
                <span>+ <?= $pharmacy['currency_symbol'] ?><?= number_format($sale['tax_amount'], 2) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:10px;color:#64748b;">
                <span>Round-off:</span>
                <span><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['round_off'], 2) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;border-top:2px solid #0d9488;padding-top:6px;font-size:1.2rem;font-weight:bold;color:#0f766e;">
                <span>Grand Total:</span>
                <span><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['grand_total'], 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Terms & Registered Pharmacist Signature -->
    <div style="display:flex;justify-content:space-between;align-items:flex-end;border-top:1px dashed #cbd5e1;padding-top:15px;font-size:11px;color:#64748b;">
        <div style="max-width:55%;">
            <strong>Terms & Conditions:</strong>
            <div><?= nl2br(htmlspecialchars($pharmacy['terms_conditions'] ?? 'Goods once sold are not returnable without bill.')) ?></div>
        </div>
        <div style="text-align:center;">
            <div style="height:45px;"></div>
            <div style="border-top:1px solid #475569;width:180px;padding-top:4px;">Authorized Pharmacist Signature</div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 2. 80MM / 58MM THERMAL RECEIPT FORMAT          -->
<!-- ============================================== -->
<div class="thermal-receipt-container">
    <div class="thermal-header">
        <div class="thermal-title"><?= htmlspecialchars($pharmacy['pharmacy_name']) ?></div>
        <div style="font-size:10px;"><?= htmlspecialchars($pharmacy['city']) ?> | Tel: <?= htmlspecialchars($pharmacy['phone']) ?></div>
        <div style="font-size:10px;">GSTIN: <?= htmlspecialchars($pharmacy['gstin']) ?></div>
        <div style="font-size:10px;">DL: <?= htmlspecialchars($pharmacy['drug_license_no']) ?></div>
    </div>

    <div style="font-size:11px;margin-bottom:6px;">
        <div class="thermal-row">
            <span>Bill: #<?= htmlspecialchars($sale['invoice_number']) ?></span>
            <span><?= date('d/m/y H:i', strtotime($sale['sale_date'])) ?></span>
        </div>
        <div class="thermal-row">
            <span>Cust: <?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in') ?></span>
            <span>Mode: <?= strtoupper($sale['payment_mode']) ?></span>
        </div>
    </div>

    <table class="thermal-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Batch</th>
                <th>Qty</th>
                <th style="text-align:right;">Amt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item['brand_name'] ?? $item['medicine_name'] ?? '') ?></strong>
                    </td>
                    <td><?= htmlspecialchars($item['batch_number'] ?? '') ?></td>
                    <td><?= $item['quantity'] ?? 0 ?></td>
                    <td style="text-align:right;"><?= number_format($item['total_amount'] ?? 0, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="thermal-totals">
        <div class="thermal-row">
            <span>Subtotal:</span>
            <span><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['subtotal'], 2) ?></span>
        </div>
        <?php if ($sale['discount_amount'] > 0): ?>
            <div class="thermal-row" style="color:#ef4444;">
                <span>Discount:</span>
                <span>- <?= $pharmacy['currency_symbol'] ?><?= number_format($sale['discount_amount'], 2) ?></span>
            </div>
        <?php endif; ?>
        <div class="thermal-row">
            <span>Taxes (GST):</span>
            <span><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['tax_amount'], 2) ?></span>
        </div>
        <div class="thermal-row" style="font-weight:bold;font-size:14px;border-top:1px dashed #000;padding-top:4px;margin-top:4px;">
            <span>TOTAL:</span>
            <span><?= $pharmacy['currency_symbol'] ?><?= number_format($sale['grand_total'], 2) ?></span>
        </div>
    </div>

    <div class="thermal-barcode-box" style="text-align:center;margin-top:12px;padding-top:8px;border-top:1px dashed #cbd5e1;">
        <div style="display:flex;justify-content:center;margin-bottom:4px;">
            <?= \App\Core\Barcode::renderSvg($sale['invoice_number'], 32, 1.15, false) ?>
        </div>
        <div style="font-size:10px;font-family:monospace;font-weight:700;letter-spacing:1px;"><?= htmlspecialchars($sale['invoice_number']) ?></div>
        <div style="font-size:10px;margin-top:6px;color:#475569;">Thank You! Get Well Soon!</div>
    </div>
</div>

<script src="<?= $baseURL ?>/assets/js/app.js"></script>
</body>
</html>
