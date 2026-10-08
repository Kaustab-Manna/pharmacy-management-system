<div class="page-header">
    <div class="page-title">
        <h1>Reports & Business Analytics (Module 33)</h1>
        <p>Daily/monthly sales, gross profit analysis, medicine-wise velocity, and manufacturer reports.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportTableToCSV('analyticsTable', 'pharmacy_report_<?= htmlspecialchars($reportType) ?>.csv')">
            <span>📥 Export Report CSV</span>
        </button>
    </div>
</div>

<!-- Report Navigation Tabs & Date Filters -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <form method="GET" action="<?= $baseURL ?>/reports" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1.5;min-width:200px;">
                <label class="form-label">Report Type</label>
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="daily_sales" <?= $reportType === 'daily_sales' ? 'selected' : '' ?>>📅 Daily Sales & Revenue</option>
                    <option value="monthly_sales" <?= $reportType === 'monthly_sales' ? 'selected' : '' ?>>📊 Monthly Sales Trends</option>
                    <option value="profit_report" <?= $reportType === 'profit_report' ? 'selected' : '' ?>>💵 Profit & Loss (COGS Analysis)</option>
                    <option value="medicine_sales" <?= $reportType === 'medicine_sales' ? 'selected' : '' ?>>💊 Top Medicine Sales Volume</option>
                    <option value="manufacturer_sales" <?= $reportType === 'manufacturer_sales' ? 'selected' : '' ?>>🏭 Manufacturer / Brand Sales</option>
                    <option value="doctor_prescriptions" <?= $reportType === 'doctor_prescriptions' ? 'selected' : '' ?>>👨‍⚕️ Doctor-wise Prescription Sales</option>
                    <option value="supplier_purchases" <?= $reportType === 'supplier_purchases' ? 'selected' : '' ?>>🚚 Supplier Procurement Report</option>
                </select>
            </div>
            <div style="flex:1;min-width:130px;">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div style="flex:1;min-width:130px;">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Generate</button>
            </div>
        </form>
    </div>
</div>

<!-- Report Data Rendering Container -->
<div class="card">
    <div class="table-responsive">
        <table class="table-custom" id="analyticsTable">
            <?php if ($reportType === 'daily_sales'): ?>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoices Count</th>
                        <th>Taxable Subtotal</th>
                        <th>Taxes Collected</th>
                        <th>Discounts</th>
                        <th>Total Net Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:2rem;">No data for selected date range.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['report_date']) ?></strong></td>
                                <td><?= $row['invoice_count'] ?> sales</td>
                                <td><?= $pharmacy['currency_symbol'] ?><?= number_format($row['subtotal'], 2) ?></td>
                                <td><?= $pharmacy['currency_symbol'] ?><?= number_format($row['tax_amount'], 2) ?></td>
                                <td>- <?= $pharmacy['currency_symbol'] ?><?= number_format($row['discount'], 2) ?></td>
                                <td><strong style="color:var(--primary);"><?= $pharmacy['currency_symbol'] ?><?= number_format($row['grand_total'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($reportType === 'profit_report'): ?>
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Sale Revenue</th>
                        <th>Cost of Goods (COGS)</th>
                        <th>Gross Profit</th>
                        <th>Margin (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:2rem;">No sales to calculate profit.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $row): 
                            $margin = ($row['revenue'] > 0) ? round(($row['gross_profit'] / $row['revenue']) * 100, 1) : 0;
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['invoice_number']) ?></strong></td>
                                <td><?= htmlspecialchars($row['sale_date']) ?></td>
                                <td><?= $pharmacy['currency_symbol'] ?><?= number_format($row['revenue'], 2) ?></td>
                                <td><?= $pharmacy['currency_symbol'] ?><?= number_format($row['cogs'], 2) ?></td>
                                <td><strong style="color:var(--success);"><?= $pharmacy['currency_symbol'] ?><?= number_format($row['gross_profit'], 2) ?></strong></td>
                                <td><span class="nav-badge badge-success"><?= $margin ?>% Margin</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($reportType === 'medicine_sales'): ?>
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Manufacturer</th>
                        <th>Units Sold</th>
                        <th>Gross Revenue Generated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $row): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($row['brand_name']) ?></strong>
                                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($row['medicine_name']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['manufacturer']) ?></td>
                            <td><strong><?= $row['total_units_sold'] ?></strong> units</td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($row['total_revenue'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            <?php elseif ($reportType === 'doctor_prescriptions'): ?>
                <thead>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Specialization</th>
                        <th>Hospital / Clinic</th>
                        <th>Prescriptions Fulfilled</th>
                        <th>Total Generated Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $row): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($row['doctor_name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['specialization']) ?></td>
                            <td><?= htmlspecialchars($row['hospital_clinic']) ?></td>
                            <td><?= $row['prescription_sales_count'] ?> fulfilled</td>
                            <td><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($row['total_generated_sales'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            <?php else: ?>
                <!-- Fallback Table -->
                <thead>
                    <tr>
                        <?php if (!empty($reportData[0])): ?>
                            <?php foreach (array_keys($reportData[0]) as $col): ?>
                                <th><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $col))) ?></th>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <th>No Records</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $row): ?>
                        <tr>
                            <?php foreach ($row as $val): ?>
                                <td><?= htmlspecialchars((string)$val) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php endif; ?>
        </table>
    </div>
</div>
