<div class="page-header">
    <div class="page-title">
        <h1>GST & Tax Compliance Management (Module 24)</h1>
        <p>GSTR-1 Outward Supply (Sales), GSTR-2 Input Tax Credit (ITC), and HSN item summaries.</p>
    </div>
    <div class="page-actions">
        <form method="GET" action="<?= $baseURL ?>/gst" style="display:flex;gap:6px;align-items:center;">
            <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>" onchange="this.form.submit()">
        </form>
        <button class="btn btn-secondary" onclick="exportTableToCSV('salesGstTable', 'gstr1_sales_tax.csv')">
            <span>📥 Export GSTR-1 CSV</span>
        </button>
    </div>
</div>

<!-- GSTR-1 Outward Taxable Supplies (Sales) -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">🧾 GSTR-1: Outward Supplies & Output Tax Liability</h3>
        <span class="nav-badge badge-info">Period: <?= htmlspecialchars($month) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table-custom" id="salesGstTable">
            <thead>
                <tr>
                    <th>GST Slab Rate</th>
                    <th>Invoice Count</th>
                    <th>Taxable Turnover</th>
                    <th>CGST (Central)</th>
                    <th>SGST (State)</th>
                    <th>Total Output Tax</th>
                    <th>Total Invoiced Value</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($salesTaxSummary)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:1.5rem;color:var(--text-muted);">No sales recorded in this month.</td></tr>
                <?php else: ?>
                    <?php 
                    $totTaxable = 0; $totCgst = 0; $totSgst = 0; $totTax = 0; $totInv = 0;
                    foreach ($salesTaxSummary as $row): 
                        $tax = $row['total_cgst'] + $row['total_sgst'] + $row['total_igst'];
                        $totTaxable += $row['taxable_amount'];
                        $totCgst += $row['total_cgst'];
                        $totSgst += $row['total_sgst'];
                        $totTax += $tax;
                        $totInv += $row['total_amount'];
                    ?>
                        <tr>
                            <td><strong><?= $row['gst_rate'] ?>% Slab</strong></td>
                            <td><?= $row['invoice_count'] ?> invoices</td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($row['taxable_amount'], 2) ?></td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($row['total_cgst'], 2) ?></td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($row['total_sgst'], 2) ?></td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($tax, 2) ?></strong></td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($row['total_amount'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background:var(--border-light);font-weight:bold;">
                        <td colspan="2">TOTAL TAX LIABILITY:</td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($totTaxable, 2) ?></td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($totCgst, 2) ?></td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($totSgst, 2) ?></td>
                        <td style="color:#ef4444;"><?= $pharmacy['currency_symbol'] ?><?= number_format($totTax, 2) ?></td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($totInv, 2) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- GSTR-2 Input Tax Credit (Purchases) -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">📥 GSTR-2: Inward Supplies & Input Tax Credit (ITC)</h3>
    </div>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>GST Rate</th>
                    <th>Purchase Taxable Value</th>
                    <th>Input CGST</th>
                    <th>Input SGST</th>
                    <th>Total ITC Available</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($purchaseTaxSummary)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:1.5rem;color:var(--text-muted);">No purchases recorded in this month.</td></tr>
                <?php else: ?>
                    <?php foreach ($purchaseTaxSummary as $p): ?>
                        <tr>
                            <td><strong><?= $p['gst_rate'] ?>% Slab</strong></td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($p['taxable_amount'], 2) ?></td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($p['itc_cgst'], 2) ?></td>
                            <td><?= $pharmacy['currency_symbol'] ?><?= number_format($p['itc_sgst'], 2) ?></td>
                            <td><strong style="color:var(--success);"><?= $pharmacy['currency_symbol'] ?><?= number_format($p['itc_cgst'] + $p['itc_sgst'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- HSN Summary Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📑 HSN Wise Sales Summary</h3>
    </div>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>HSN Code</th>
                    <th>GST Rate</th>
                    <th>Units Sold</th>
                    <th>Taxable Value</th>
                    <th>Total Tax Component</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hsnSummary as $hsn): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($hsn['hsn_code']) ?></code></td>
                        <td><?= $hsn['gst_rate'] ?>%</td>
                        <td><?= $hsn['total_qty'] ?> units</td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($hsn['taxable_val'], 2) ?></td>
                        <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($hsn['total_tax'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
