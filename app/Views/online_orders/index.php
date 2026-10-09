<div class="page-header">
    <div class="page-title">
        <h1>Online / E-Pharmacy Order Management</h1>
        <p>Customer web portal ordering, digital prescription uploads, verification workflow, and dispatch packing.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addWebOrderModal')">
            <span>🌐 Simulate Web Order</span>
        </button>
    </div>
</div>

<!-- Quick Statistics Summary -->
<?php
$totalOrders = count($orders);
$pendingOrders = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$processingOrders = count(array_filter($orders, fn($o) => $o['status'] === 'processing'));
$deliveredOrders = count(array_filter($orders, fn($o) => $o['status'] === 'delivered'));
?>
<div class="stats-grid">
    <div class="stat-card stat-success">
        <div class="stat-info">
            <h3>Total Online Orders</h3>
            <div class="stat-number"><?= $totalOrders ?></div>
            <div class="stat-subtext">Received via Web Portal</div>
        </div>
        <div class="stat-icon icon-teal">🌐</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Pending Verification</h3>
            <div class="stat-number"><?= $pendingOrders ?></div>
            <div class="stat-subtext">Awaiting Pharmacist Review</div>
        </div>
        <div class="stat-icon icon-amber">⏳</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Processing & Packed</h3>
            <div class="stat-number"><?= $processingOrders ?></div>
            <div class="stat-subtext">Ready for Dispatch / Delivery</div>
        </div>
        <div class="stat-icon icon-blue">📦</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Delivered</h3>
            <div class="stat-number"><?= $deliveredOrders ?></div>
            <div class="stat-subtext">Fulfilled Orders</div>
        </div>
        <div class="stat-icon icon-teal">✅</div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer Name & Contact</th>
                    <th>Delivery Address</th>
                    <th>Ordered Medicines</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2.5rem;color:var(--text-muted);">No online orders yet. Click <strong>'Simulate Web Order'</strong> to test customer ordering.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <a href="javascript:void(0)" onclick="viewOrderDetails(<?= $o['id'] ?>)" title="Click to view full order details" style="color:var(--primary);font-weight:700;text-decoration:none;display:inline-block;">
                                    <?= htmlspecialchars($o['order_number']) ?>
                                </a>
                                <div style="margin-top:2px;">
                                    <span class="badge badge-secondary" style="font-size:10px;">
                                        <?= count($o['items']) ?> <?= count($o['items']) === 1 ? 'item' : 'items' ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($o['customer_name']) ?></strong>
                                <div style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:4px;margin-top:2px;">
                                    📞 <?= htmlspecialchars($o['customer_phone']) ?>
                                </div>
                                <?php if (!empty($o['customer_email'])): ?>
                                    <div style="font-size:11px;color:var(--text-muted);">✉️ <?= htmlspecialchars($o['customer_email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size:12px;max-width:200px;line-height:1.4;"><?= htmlspecialchars($o['delivery_address']) ?></div>
                                <?php if (!empty($o['notes'])): ?>
                                    <div style="font-size:11px;color:var(--warning);margin-top:4px;" title="Customer note">
                                        📝 <?= htmlspecialchars($o['notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (empty($o['items'])): ?>
                                    <span style="font-size:12px;color:var(--text-muted);font-style:italic;">No medicine items listed</span>
                                <?php else: ?>
                                    <div style="display:flex;flex-direction:column;gap:4px;min-width:210px;">
                                        <?php foreach (array_slice($o['items'], 0, 3) as $item): ?>
                                            <div style="display:flex;align-items:center;justify-content:space-between;background:var(--bg-body);padding:3px 8px;border-radius:6px;font-size:12px;border:1px solid var(--border-color);">
                                                <span style="font-weight:600;color:var(--text-main);">
                                                    💊 <?= htmlspecialchars($item['brand_name'] ?: $item['medicine_name']) ?>
                                                    <?php if (!empty($item['strength'])): ?>
                                                        <span style="font-weight:normal;color:var(--text-muted);font-size:11px;">(<?= htmlspecialchars($item['strength']) ?>)</span>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="badge badge-info" style="font-size:10px;margin-left:6px;white-space:nowrap;">
                                                    <?= $item['quantity'] ?> <?= htmlspecialchars($item['unit'] ?? 'Qty') ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($o['items']) > 3): ?>
                                            <a href="javascript:void(0)" onclick="viewOrderDetails(<?= $o['id'] ?>)" style="font-size:11px;color:var(--primary);font-weight:600;text-decoration:none;">
                                                +<?= count($o['items']) - 3 ?> more medicines...
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;white-space:nowrap;">
                                <?= htmlspecialchars(date('d M Y, h:i A', strtotime($o['created_at']))) ?>
                            </td>
                            <td>
                                <div><strong><?= $pharmacy['currency_symbol'] ?><?= number_format($o['total_amount'], 2) ?></strong></div>
                                <div style="margin-top:2px;">
                                    <span class="badge badge-<?= ($o['payment_status'] === 'prepaid' || $o['payment_status'] === 'paid') ? 'success' : 'secondary' ?>" style="font-size:10px;">
                                        <?= strtoupper($o['payment_status'] ?? 'COD') ?>
                                    </span>
                                </div>
                            </td>
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
                                <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;">
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="viewOrderDetails(<?= $o['id'] ?>)" title="View Medicines & Full Order Details">
                                        👁️ View Order
                                    </button>
                                    <form method="POST" action="<?= $baseURL ?>/online-orders/update-status/<?= $o['id'] ?>" style="display:inline;">
                                        <?php if ($o['status'] === 'pending'): ?>
                                            <input type="hidden" name="status" value="processing">
                                            <button type="submit" class="btn btn-sm btn-primary" title="Verify prescription & pack medicines">Verify & Pack</button>
                                        <?php elseif ($o['status'] === 'processing'): ?>
                                            <input type="hidden" name="status" value="delivered">
                                            <button type="submit" class="btn btn-sm btn-success" title="Mark order as delivered">Mark Delivered</button>
                                        <?php else: ?>
                                            <span class="nav-badge badge-secondary">Completed</span>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: View Order Details & Ordered Medicines -->
<div id="viewOrderModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 760px;">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" style="display:flex;align-items:center;gap:8px;">
                    <span>📦 Online Order:</span>
                    <span id="viewModalOrderNumber" style="color:var(--primary);"></span>
                    <span id="viewModalStatusBadge"></span>
                </h3>
            </div>
            <button class="modal-close" onclick="closeModal('viewOrderModal')">&times;</button>
        </div>
        <div class="modal-body" id="printableOrderSlip">
            <!-- Order Meta Grid -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:12px;background:var(--bg-body);padding:14px;border-radius:8px;border:1px solid var(--border-color);margin-bottom:1.25rem;">
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Customer Details</div>
                    <div style="font-weight:700;font-size:14px;color:var(--text-main);margin-top:2px;" id="viewModalCustomerName"></div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">📞 <span id="viewModalCustomerPhone"></span></div>
                    <div style="font-size:12px;color:var(--text-muted);" id="viewModalCustomerEmail"></div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Delivery Address</div>
                    <div style="font-size:13px;color:var(--text-main);line-height:1.4;margin-top:2px;" id="viewModalDeliveryAddress"></div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Order Info</div>
                    <div style="font-size:12px;color:var(--text-main);margin-top:2px;">📅 Placed: <strong id="viewModalOrderDate"></strong></div>
                    <div style="font-size:12px;color:var(--text-main);margin-top:2px;">💳 Payment: <strong id="viewModalPaymentStatus"></strong></div>
                </div>
            </div>

            <!-- Notes / Prescription details -->
            <div id="viewModalNotesContainer" style="display:none;margin-bottom:1.25rem;background:rgba(245, 158, 11, 0.1);border-left:4px solid var(--warning);padding:10px 12px;border-radius:4px;">
                <strong style="color:var(--text-main);font-size:12px;">📝 Patient Note / Prescription Remark:</strong>
                <div style="font-size:13px;color:var(--text-main);margin-top:3px;" id="viewModalNotes"></div>
            </div>

            <!-- Itemized Medicines Ordered Section -->
            <div style="margin-top:0.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.6rem;">
                    <h4 style="margin:0;font-size:15px;display:flex;align-items:center;gap:6px;">
                        <span>💊 Medicines Ordered by Patient</span>
                        <span id="viewModalItemsCount" class="badge badge-secondary" style="font-size:11px;">0 items</span>
                    </h4>
                </div>
                <div class="table-responsive" style="border:1px solid var(--border-color);border-radius:6px;overflow:hidden;">
                    <table class="table-custom" style="margin:0;">
                        <thead>
                            <tr style="background:var(--bg-body);">
                                <th style="width:40px;">#</th>
                                <th>Medicine & Brand Name</th>
                                <th>Dosage / Strength</th>
                                <th>Unit</th>
                                <th style="text-align:center;">Qty</th>
                                <th style="text-align:right;">Unit Price</th>
                                <th style="text-align:right;">Total</th>
                            </tr>
                        </thead>
                        <tbody id="viewModalItemsTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                        <tfoot>
                            <tr style="background:var(--bg-body);font-weight:700;">
                                <td colspan="6" style="text-align:right;padding:10px 12px;">Grand Total:</td>
                                <td style="text-align:right;padding:10px 12px;color:var(--primary);font-size:15px;" id="viewModalGrandTotal"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <button type="button" class="btn btn-secondary" onclick="printOrderSlip()">🖨️ Print Order Slip</button>
            </div>
            <div style="display:flex;gap:8px;">
                <span id="viewModalActionContainer"></span>
                <button type="button" class="btn btn-secondary" onclick="closeModal('viewOrderModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Simulate Web Order -->
<div id="addWebOrderModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 680px;">
        <div class="modal-header">
            <h3 class="modal-title">🌐 Simulate Incoming Online Web Order</h3>
            <button class="modal-close" onclick="closeModal('addWebOrderModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/online-orders/create">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col" style="flex:1.5;">
                        <label class="form-label">Customer Name *</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" value="Aakash Narang" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Mobile Number *</label>
                        <input type="text" name="customer_phone" class="form-control" value="+91 98200 77112" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Email</label>
                        <input type="email" name="customer_email" class="form-control" value="aakash@gmail.com">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_status" class="form-control">
                            <option value="cod" selected>Cash on Delivery (COD)</option>
                            <option value="prepaid">Online Prepaid (UPI / NetBanking)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Address *</label>
                    <textarea name="delivery_address" class="form-control" rows="2" required>Flat 304, Tower B, Palm Beach Road, Vashi, Navi Mumbai</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Customer Order Notes / Prescription Remarks</label>
                    <input type="text" name="notes" class="form-control" value="Deliver between 5-7 PM please. Prescription attached.">
                </div>

                <!-- Select Medicines for Web Order -->
                <div style="margin-top:1.25rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                        <label class="form-label" style="margin:0;font-weight:700;">💊 Select Medicines to Order *</label>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addWebOrderItemRow()">+ Add Medicine</button>
                    </div>
                    <div id="webOrderItemsContainer" style="display:flex;flex-direction:column;gap:8px;">
                        <!-- Row 1 -->
                        <div class="web-order-item-row" style="display:flex;gap:8px;align-items:center;background:var(--bg-body);padding:8px 10px;border-radius:6px;border:1px solid var(--border-color);">
                            <div style="flex:2.5;">
                                <select name="medicine_ids[]" class="form-control web-med-select" onchange="onMedicineSelectChanged(this)" required>
                                    <option value="">Select Medicine</option>
                                    <?php foreach ($medicines as $m): ?>
                                        <option value="<?= $m['id'] ?>" data-price="<?= $m['selling_price'] ?>" data-unit="<?= htmlspecialchars($m['unit']) ?>" data-strength="<?= htmlspecialchars($m['strength']) ?>">
                                            <?= htmlspecialchars($m['brand_name'] ?: $m['name']) ?> - <?= htmlspecialchars($m['name']) ?> (₹<?= number_format($m['selling_price'], 2) ?>/<?= htmlspecialchars($m['unit']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex:0.8;">
                                <input type="number" name="quantities[]" class="form-control web-qty-input" min="1" value="2" placeholder="Qty" onchange="updateSimulateTotal()" oninput="updateSimulateTotal()" required>
                            </div>
                            <div style="flex:1;">
                                <input type="number" step="0.01" name="prices[]" class="form-control web-price-input" placeholder="Price" onchange="updateSimulateTotal()" oninput="updateSimulateTotal()" required>
                            </div>
                            <div style="flex:0.3;text-align:center;">
                                <button type="button" class="btn btn-sm btn-subtle-danger" onclick="removeWebOrderItemRow(this)" title="Remove item" style="padding:4px 8px;">✕</button>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;align-items:center;margin-top:8px;padding-right:10px;">
                        <span style="font-weight:600;font-size:13px;color:var(--text-muted);margin-right:8px;">Estimated Total:</span>
                        <strong style="color:var(--primary);font-size:16px;" id="simulateTotalDisplay"><?= $pharmacy['currency_symbol'] ?>0.00</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addWebOrderModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Web Order</button>
            </div>
        </form>
    </div>
</div>

<script>
var ordersData = <?= json_encode($orders, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var currencySymbol = <?= json_encode($pharmacy['currency_symbol']) ?>;
var baseURL = <?= json_encode($baseURL) ?>;

function viewOrderDetails(orderId) {
    var order = ordersData.find(function(o) { return Number(o.id) === Number(orderId); });
    if (!order) return;

    // Populate Order Information
    document.getElementById('viewModalOrderNumber').innerText = '#' + order.order_number;
    document.getElementById('viewModalCustomerName').innerText = order.customer_name;
    document.getElementById('viewModalCustomerPhone').innerText = order.customer_phone;
    document.getElementById('viewModalCustomerEmail').innerText = order.customer_email || 'None';
    document.getElementById('viewModalDeliveryAddress').innerText = order.delivery_address;
    document.getElementById('viewModalOrderDate').innerText = order.created_at;

    var paymentMode = (order.payment_status || 'cod').toUpperCase();
    document.getElementById('viewModalPaymentStatus').innerText = paymentMode === 'PREPAID' ? 'Online (Prepaid)' : 'Cash on Delivery (COD)';

    // Status Badge
    var statusBadge = document.getElementById('viewModalStatusBadge');
    if (order.status === 'delivered') {
        statusBadge.className = 'nav-badge badge-success';
        statusBadge.innerText = 'Delivered';
    } else if (order.status === 'processing') {
        statusBadge.className = 'nav-badge badge-info';
        statusBadge.innerText = 'Processing / Packed';
    } else {
        statusBadge.className = 'nav-badge badge-warning';
        statusBadge.innerText = 'Pending Verification';
    }

    // Notes
    var notesBox = document.getElementById('viewModalNotesContainer');
    if (order.notes && order.notes.trim() !== '') {
        notesBox.style.display = 'block';
        document.getElementById('viewModalNotes').innerText = order.notes;
    } else {
        notesBox.style.display = 'none';
    }

    // Populate Ordered Medicines Table
    var tbody = document.getElementById('viewModalItemsTableBody');
    tbody.innerHTML = '';
    var items = order.items || [];
    document.getElementById('viewModalItemsCount').innerText = items.length + (items.length === 1 ? ' medicine' : ' medicines');

    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:1.5rem;color:var(--text-muted);">No detailed medicine records found for this order.</td></tr>';
    } else {
        items.forEach(function(item, index) {
            var tr = document.createElement('tr');
            var lineTotal = (Number(item.quantity) * Number(item.price)).toFixed(2);
            tr.innerHTML = 
                '<td>' + (index + 1) + '</td>' +
                '<td><strong>' + escapeHtml(item.brand_name || item.medicine_name) + '</strong>' +
                    (item.brand_name && item.medicine_name ? '<div style="font-size:11px;color:var(--text-muted);">' + escapeHtml(item.medicine_name) + '</div>' : '') +
                '</td>' +
                '<td>' + escapeHtml(item.dosage_form || 'Tablet') + ' ' + (item.strength ? '(' + escapeHtml(item.strength) + ')' : '') + '</td>' +
                '<td>' + escapeHtml(item.unit || 'Strip') + '</td>' +
                '<td style="text-align:center;"><strong>' + item.quantity + '</strong></td>' +
                '<td style="text-align:right;">' + currencySymbol + Number(item.price).toFixed(2) + '</td>' +
                '<td style="text-align:right;"><strong>' + currencySymbol + lineTotal + '</strong></td>';
            tbody.appendChild(tr);
        });
    }

    document.getElementById('viewModalGrandTotal').innerText = currencySymbol + Number(order.total_amount).toFixed(2);

    // Modal Action button
    var actionContainer = document.getElementById('viewModalActionContainer');
    actionContainer.innerHTML = '';
    if (order.status === 'pending') {
        actionContainer.innerHTML = 
            '<form method="POST" action="' + baseURL + '/online-orders/update-status/' + order.id + '" style="display:inline;">' +
            '<input type="hidden" name="status" value="processing">' +
            '<button type="submit" class="btn btn-primary">✓ Verify & Pack Order</button>' +
            '</form>';
    } else if (order.status === 'processing') {
        actionContainer.innerHTML = 
            '<form method="POST" action="' + baseURL + '/online-orders/update-status/' + order.id + '" style="display:inline;">' +
            '<input type="hidden" name="status" value="delivered">' +
            '<button type="submit" class="btn btn-success">✓ Mark Delivered</button>' +
            '</form>';
    }

    openModal('viewOrderModal');
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

function addWebOrderItemRow() {
    var container = document.getElementById('webOrderItemsContainer');
    var firstRow = container.querySelector('.web-order-item-row');
    if (!firstRow) return;
    var clone = firstRow.cloneNode(true);
    // Reset values in clone
    var select = clone.querySelector('.web-med-select');
    select.selectedIndex = 0;
    var qty = clone.querySelector('.web-qty-input');
    qty.value = 1;
    var price = clone.querySelector('.web-price-input');
    price.value = '';
    container.appendChild(clone);
    updateSimulateTotal();
}

function removeWebOrderItemRow(btn) {
    var container = document.getElementById('webOrderItemsContainer');
    var rows = container.querySelectorAll('.web-order-item-row');
    if (rows.length > 1) {
        btn.closest('.web-order-item-row').remove();
        updateSimulateTotal();
    } else {
        alert('An order must have at least one medicine item.');
    }
}

function onMedicineSelectChanged(select) {
    var row = select.closest('.web-order-item-row');
    var selectedOption = select.options[select.selectedIndex];
    var price = selectedOption.getAttribute('data-price') || 50.00;
    var priceInput = row.querySelector('.web-price-input');
    priceInput.value = Number(price).toFixed(2);
    updateSimulateTotal();
}

function updateSimulateTotal() {
    var rows = document.querySelectorAll('#webOrderItemsContainer .web-order-item-row');
    var total = 0;
    rows.forEach(function(row) {
        var qty = parseFloat(row.querySelector('.web-qty-input').value) || 0;
        var price = parseFloat(row.querySelector('.web-price-input').value) || 0;
        total += (qty * price);
    });
    document.getElementById('simulateTotalDisplay').innerText = currencySymbol + total.toFixed(2);
}

function printOrderSlip() {
    var printContent = document.getElementById('printableOrderSlip').innerHTML;
    var printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write('<html><head><title>Online Order Slip</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; padding: 20px; color: #111; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 15px; }');
    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
    printWindow.document.write('th { background: #f4f4f4; }');
    printWindow.document.write('.badge, .nav-badge { padding: 3px 6px; border-radius: 4px; font-size: 11px; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2>Pharmacy Online Order Slip</h2>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// Initialize pre-selection on first modal open
document.addEventListener('DOMContentLoaded', function() {
    var firstSelect = document.querySelector('.web-med-select');
    if (firstSelect && firstSelect.options.length > 1) {
        firstSelect.selectedIndex = 1;
        onMedicineSelectChanged(firstSelect);
    }
});
</script>
