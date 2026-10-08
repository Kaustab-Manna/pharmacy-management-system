<div class="page-header">
    <div class="page-title">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
            <a href="<?= $baseURL ?>/purchases" style="color:var(--text-muted);text-decoration:none;font-size:0.9rem;">&larr; Purchases</a>
            <span style="color:var(--text-muted);">&bull;</span>
            <span style="color:var(--primary);font-size:0.9rem;font-weight:600;">Module 14</span>
        </div>
        <h1>📥 New Purchase Invoice & Stock Inwarding</h1>
        <p>Record supplier invoices, create verified medicine batches, compute GST input tax credits, and update live inventory.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/purchase-orders" class="btn btn-secondary">
            <span>📋 Purchase Orders (PO)</span>
        </a>
        <a href="<?= $baseURL ?>/purchases" class="btn btn-secondary">
            <span>📋 Purchase Register</span>
        </a>
    </div>
</div>

<?php if (!empty($po)): ?>
    <div class="alert alert-info" style="margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
        <div>
            <strong>📋 Inwarding from Purchase Order:</strong> <?= htmlspecialchars($po['po_number']) ?> 
            (Supplier: <?= htmlspecialchars($po['supplier_name'] ?? 'Assigned Supplier') ?>)
        </div>
        <span class="nav-badge badge-info">PO Converted</span>
    </div>
<?php endif; ?>

<form id="purchaseForm" method="POST" action="<?= $baseURL ?>/purchases/create">
    <?php if (!empty($po['id'])): ?>
        <input type="hidden" name="po_id" value="<?= (int)$po['id'] ?>">
    <?php endif; ?>

    <!-- Section 1: Supplier & Bill Metadata -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background:var(--bg-body);border-bottom:1px solid var(--border-color);">
            <h3 class="card-title" style="font-size:1rem;color:var(--text-main);">
                🏢 Supplier & Invoice Particulars
            </h3>
            <span style="font-size:0.8rem;color:var(--text-muted);">* Mandatory details required for GST compliance</span>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <label class="form-label">Distributor / Supplier *</label>
                    <select name="supplier_id" id="supplierSelect" class="form-control" required>
                        <option value="">-- Choose Supplier / Distributor --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (!empty($po) && $po['supplier_id'] == $s['id']) || (isset($_GET['supplier_id']) && $_GET['supplier_id'] == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['company_name']) ?> (<?= htmlspecialchars($s['name']) ?>) - Phone: <?= htmlspecialchars($s['phone'] ?? 'N/A') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-col" style="flex: 1.5;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <label class="form-label" style="margin-bottom:0;">Supplier Invoice # *</label>
                        <button type="button" onclick="generateInvoiceNo()" style="background:none;border:none;color:var(--primary);cursor:pointer;font-size:11px;font-weight:600;padding:0;">⚡ Auto-Gen</button>
                    </div>
                    <input type="text" name="invoice_number" id="invoiceNumberInput" class="form-control" placeholder="e.g. INV-90412" required value="<?= htmlspecialchars($_GET['invoice_no'] ?? '') ?>">
                </div>
                <div class="form-col" style="flex: 1;">
                    <label class="form-label">Invoice Date *</label>
                    <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-col" style="flex: 1.2;">
                    <label class="form-label">Payment Mode *</label>
                    <select name="payment_mode" id="paymentModeSelect" class="form-control" onchange="handlePaymentModeChange()">
                        <option value="credit">Credit (Pay Later)</option>
                        <option value="cash">Cash Paid</option>
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="upi">UPI / QR Code</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Received Medicines & Batches Table -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;background:var(--bg-body);border-bottom:1px solid var(--border-color);">
            <div>
                <h3 class="card-title" style="font-size:1rem;color:var(--text-main);">
                    📦 Inward Medicine Batches & Pricing
                </h3>
                <p style="font-size:12px;color:var(--text-muted);margin:2px 0 0 0;">Add medicines from supplier shipment. Each batch will be created or replenished in the active inventory.</p>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="addPurchaseRow()">
                <span>➕ Add Another Medicine</span>
            </button>
        </div>
        <div class="table-responsive">
            <table class="table-custom" id="purchaseItemsTable">
                <thead>
                    <tr style="background:var(--bg-card);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;">
                        <th style="width:25%;">Medicine / Formulation *</th>
                        <th style="width:13%;">Batch No *</th>
                        <th style="width:11%;">Mfg Date</th>
                        <th style="width:11%;">Exp Date *</th>
                        <th style="width:9%;">Qty *</th>
                        <th style="width:11%;">Purchase Rate (₹) *</th>
                        <th style="width:10%;">MRP (₹) *</th>
                        <th style="width:9%;">GST %</th>
                        <th style="width:11%;text-align:right;">Total (₹)</th>
                        <th style="width:5%;text-align:center;"></th>
                    </tr>
                </thead>
                <tbody id="purchaseItemsBody">
                    <?php if (!empty($poItems)): ?>
                        <?php foreach ($poItems as $idx => $item): ?>
                            <tr class="purchase-row">
                                <td>
                                    <select name="medicine_id[]" class="form-control medicine-select" required>
                                        <option value="">Select Medicine</option>
                                        <?php foreach ($medicines as $m): ?>
                                            <option value="<?= $m['id'] ?>" <?= $m['id'] == $item['medicine_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m['brand_name']) ?> (<?= htmlspecialchars($m['name']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="batch_number[]" class="form-control" placeholder="Batch #" value="B<?= date('ym') ?>-<?= str_pad((string)($idx+1), 3, '0', STR_PAD_LEFT) ?>" required>
                                </td>
                                <td>
                                    <input type="date" name="mfg_date[]" class="form-control" value="<?= date('Y-m-d') ?>">
                                </td>
                                <td>
                                    <input type="date" name="expiry_date[]" class="form-control" value="<?= date('Y-m-d', strtotime('+2 years')) ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="quantity[]" class="form-control item-qty" placeholder="Qty" min="1" value="<?= (int)($item['quantity'] ?? 10) ?>" required oninput="calculateTotals()">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="purchase_rate[]" class="form-control item-rate" placeholder="0.00" min="0" value="<?= number_format((float)($item['expected_rate'] ?? 0), 2, '.', '') ?>" required oninput="calculateTotals()">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="mrp[]" class="form-control item-mrp" placeholder="0.00" min="0" value="<?= number_format((float)(($item['expected_rate'] ?? 0) * 1.35), 2, '.', '') ?>" required>
                                </td>
                                <td>
                                    <select name="gst_rate[]" class="form-control item-gst" onchange="calculateTotals()">
                                        <option value="0">0%</option>
                                        <option value="5">5%</option>
                                        <option value="12" selected>12%</option>
                                        <option value="18">18%</option>
                                        <option value="28">28%</option>
                                    </select>
                                </td>
                                <td style="text-align:right;font-weight:700;color:var(--text-main);vertical-align:middle;">
                                    <span class="row-total">0.00</span>
                                </td>
                                <td style="text-align:center;vertical-align:middle;">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removePurchaseRow(this)" title="Remove item" style="padding:4px 8px;border-radius:6px;">✕</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="purchase-row">
                            <td>
                                <select name="medicine_id[]" class="form-control medicine-select" required>
                                    <option value="">Select Medicine</option>
                                    <?php foreach ($medicines as $m): ?>
                                        <option value="<?= $m['id'] ?>">
                                            <?= htmlspecialchars($m['brand_name']) ?> (<?= htmlspecialchars($m['name']) ?>) - <?= htmlspecialchars($m['dosage_form'] ?? 'Tablet') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="batch_number[]" class="form-control" placeholder="e.g. B26-101" required>
                            </td>
                            <td>
                                <input type="date" name="mfg_date[]" class="form-control" value="<?= date('Y-m-d') ?>">
                            </td>
                            <td>
                                <input type="date" name="expiry_date[]" class="form-control" value="<?= date('Y-m-d', strtotime('+2 years')) ?>" required>
                            </td>
                            <td>
                                <input type="number" name="quantity[]" class="form-control item-qty" placeholder="Qty" min="1" value="10" required oninput="calculateTotals()">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="purchase_rate[]" class="form-control item-rate" placeholder="0.00" min="0" value="50.00" required oninput="calculateTotals()">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="mrp[]" class="form-control item-mrp" placeholder="0.00" min="0" value="75.00" required>
                            </td>
                            <td>
                                <select name="gst_rate[]" class="form-control item-gst" onchange="calculateTotals()">
                                    <option value="0">0%</option>
                                    <option value="5">5%</option>
                                    <option value="12" selected>12%</option>
                                    <option value="18">18%</option>
                                    <option value="28">28%</option>
                                </select>
                            </td>
                            <td style="text-align:right;font-weight:700;color:var(--text-main);vertical-align:middle;">
                                <span class="row-total">0.00</span>
                            </td>
                            <td style="text-align:center;vertical-align:middle;">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removePurchaseRow(this)" title="Remove item" style="padding:4px 8px;border-radius:6px;">✕</button>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="padding:10px 16px;background:var(--bg-body);border-top:1px solid var(--border-color);display:flex;justify-content:flex-start;">
            <button type="button" class="btn btn-sm btn-secondary" onclick="addPurchaseRow()">
                <span>➕ Add Another Medicine Item</span>
            </button>
        </div>
    </div>

    <!-- Section 3: Summary, Notes & Settlement -->
    <div style="display:grid;grid-template-columns: 1.5fr 1fr;gap:1.5rem;align-items:start;">
        <div class="card">
            <div class="card-header" style="background:var(--bg-body);border-bottom:1px solid var(--border-color);">
                <h3 class="card-title" style="font-size:0.95rem;">📝 Verification & Cold Chain Notes</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Receiving Notes / Quality Inspection</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="e.g. Shipment delivered in good condition; cold chain intact; manufacturer batch test reports verified..."></textarea>
                </div>
                <div style="background:var(--bg-body);border-radius:8px;padding:12px;border:1px dashed var(--border-color);font-size:12px;color:var(--text-muted);line-height:1.5;">
                    💡 <strong>Smart Inventory Rule:</strong> Submitting this purchase entry automatically increases the stock quantity in the pharmacy batch registry and registers a permanent audit trail in stock movements.
                </div>
            </div>
        </div>

        <div class="card" style="box-shadow:var(--shadow-md);">
            <div class="card-header" style="background:var(--bg-body);border-bottom:1px solid var(--border-color);">
                <h3 class="card-title" style="font-size:0.95rem;">💰 Invoice Valuation & Settlement</h3>
            </div>
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                    <span style="color:var(--text-muted);">Taxable Subtotal:</span>
                    <strong id="subtotalDisplay">₹0.00</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                    <span style="color:var(--text-muted);">Total GST (ITC Input Credit):</span>
                    <strong id="taxDisplay" style="color:var(--info);">₹0.00</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:2px solid var(--border-color);font-size:1.15rem;">
                    <span style="font-weight:700;color:var(--text-main);">Grand Total:</span>
                    <strong id="grandTotalDisplay" style="color:var(--primary);font-size:1.25rem;">₹0.00</strong>
                </div>

                <div style="margin-top:1rem;">
                    <label class="form-label" style="font-size:13px;">Amount Paid Now (₹)</label>
                    <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" class="form-control" placeholder="0.00" value="0.00" oninput="updateBalanceDue()">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;font-size:12px;">
                        <span style="color:var(--text-muted);">Supplier Outstanding Due:</span>
                        <strong id="dueBalanceDisplay" style="color:var(--danger);">₹0.00</strong>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="display:flex;flex-direction:column;gap:10px;padding:1rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:1rem;font-weight:700;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 14px rgba(13,148,136,0.3);">
                    <span>📥 Save Purchase Invoice & Inward Stock</span>
                </button>
                <a href="<?= $baseURL ?>/purchases" class="btn btn-secondary" style="width:100%;text-align:center;">Cancel & Return</a>
            </div>
        </div>
    </div>
</form>

<script>
var grandTotalCalculated = 0;

function generateInvoiceNo() {
    var d = new Date();
    var ymd = d.getFullYear() + '' + ('0' + (d.getMonth()+1)).slice(-2) + ('0' + d.getDate()).slice(-2);
    var rand = Math.floor(1000 + Math.random() * 9000);
    document.getElementById('invoiceNumberInput').value = 'PINV-' + ymd + '-' + rand;
}

function calculateTotals() {
    var rows = document.querySelectorAll('#purchaseItemsBody tr.purchase-row');
    var subtotal = 0;
    var totalTax = 0;

    rows.forEach(function(row) {
        var qtyInput = row.querySelector('.item-qty');
        var rateInput = row.querySelector('.item-rate');
        var gstSelect = row.querySelector('.item-gst');
        var rowTotalSpan = row.querySelector('.row-total');

        var qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
        var rate = parseFloat(rateInput ? rateInput.value : 0) || 0;
        var gst = parseFloat(gstSelect ? gstSelect.value : 0) || 0;

        var lineBase = qty * rate;
        var lineTax = (lineBase * gst) / 100;
        var lineTotal = lineBase + lineTax;

        subtotal += lineBase;
        totalTax += lineTax;

        if (rowTotalSpan) {
            rowTotalSpan.textContent = lineTotal.toFixed(2);
        }
    });

    grandTotalCalculated = subtotal + totalTax;

    document.getElementById('subtotalDisplay').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('taxDisplay').textContent = '₹' + totalTax.toFixed(2);
    document.getElementById('grandTotalDisplay').textContent = '₹' + grandTotalCalculated.toFixed(2);

    handlePaymentModeChange();
}

function handlePaymentModeChange() {
    var mode = document.getElementById('paymentModeSelect').value;
    var paidInput = document.getElementById('paidAmountInput');
    
    if (mode === 'credit') {
        paidInput.value = '0.00';
    } else {
        paidInput.value = grandTotalCalculated.toFixed(2);
    }
    updateBalanceDue();
}

function updateBalanceDue() {
    var paid = parseFloat(document.getElementById('paidAmountInput').value) || 0;
    var due = grandTotalCalculated - paid;
    if (due < 0) due = 0;
    document.getElementById('dueBalanceDisplay').textContent = '₹' + due.toFixed(2);
}

function addPurchaseRow() {
    var tbody = document.getElementById('purchaseItemsBody');
    var firstRow = tbody.querySelector('tr.purchase-row');
    var clone = firstRow.cloneNode(true);

    // reset fields in the cloned row
    var medSelect = clone.querySelector('.medicine-select');
    if (medSelect) medSelect.selectedIndex = 0;

    var inputs = clone.querySelectorAll('input');
    inputs.forEach(function(inp) {
        if (inp.name === 'mfg_date[]') {
            var now = new Date().toISOString().split('T')[0];
            inp.value = now;
        } else if (inp.name === 'expiry_date[]') {
            var exp = new Date();
            exp.setFullYear(exp.getFullYear() + 2);
            inp.value = exp.toISOString().split('T')[0];
        } else if (inp.name === 'quantity[]') {
            inp.value = '10';
        } else if (inp.name === 'purchase_rate[]') {
            inp.value = '0.00';
        } else if (inp.name === 'mrp[]') {
            inp.value = '0.00';
        } else {
            inp.value = '';
        }
    });

    var rowTotalSpan = clone.querySelector('.row-total');
    if (rowTotalSpan) rowTotalSpan.textContent = '0.00';

    tbody.appendChild(clone);
    calculateTotals();
}

function removePurchaseRow(btn) {
    var rows = document.querySelectorAll('#purchaseItemsBody tr.purchase-row');
    if (rows.length <= 1) {
        alert('Purchase entry requires at least one medicine item.');
        return;
    }
    var tr = btn.closest('tr');
    if (tr) {
        tr.remove();
        calculateTotals();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
});
</script>
