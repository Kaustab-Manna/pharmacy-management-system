<div class="pos-layout">
    
    <!-- LEFT PANEL: Medicine Search & Quick Add -->
    <div>
        <div class="card" style="margin-bottom: 1rem;">
            <div class="card-body" style="padding: 1rem;">
                <div class="pos-search-header">
                    <div style="flex:1;position:relative;">
                        <input type="text" id="posSearchInput" class="form-control" placeholder="🔍 Search medicine by brand name, salt composition, barcode (F2)..." autocomplete="off" autofocus>
                    </div>
                    <button class="btn btn-secondary" onclick="openModal('loadPrescriptionModal')" title="Load Verified Prescription">
                        🩺 Load Rx
                    </button>
                </div>
            </div>
        </div>

        <!-- Available Medicines Cards Grid -->
        <div id="medicinesCatalogGrid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:0.85rem;max-height:calc(100vh - 230px);overflow-y:auto;padding-right:4px;">
            <?php foreach ($medicines as $med): ?>
                <div class="card med-catalog-card" style="padding:0.85rem;cursor:pointer;transition:var(--transition);" onclick="selectMedicineForCart(<?= $med['id'] ?>, '<?= htmlspecialchars(addslashes($med['brand_name'])) ?>')">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <strong style="color:var(--primary);font-size:0.95rem;"><?= htmlspecialchars($med['brand_name']) ?></strong>
                            <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($med['name']) ?></div>
                        </div>
                        <?php if ($med['requires_prescription']): ?>
                            <span class="nav-badge badge-danger" style="font-size:9px;">Rx</span>
                        <?php endif; ?>
                    </div>

                    <div style="margin: 0.5rem 0; font-size:11px; color:var(--text-muted); line-height:1.3;">
                        <div><?= htmlspecialchars($med['composition']) ?></div>
                        <div><?= htmlspecialchars($med['pack_size']) ?> • <?= htmlspecialchars($med['unit']) ?></div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.4rem;padding-top:0.4rem;border-top:1px dashed var(--border-color);">
                        <div>
                            <span style="font-size:11px;color:var(--text-muted);">Stock: </span>
                            <strong style="color:var(--success);"><?= $med['total_stock'] ?></strong>
                        </div>
                        <button class="btn btn-sm btn-primary" style="padding:2px 8px;font-size:11px;">+ Add</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT PANEL: Live Counter Cart & Checkout -->
    <div>
        <div class="pos-cart-card">
            <!-- Customer / Patient Selector -->
            <div style="padding:0.85rem;border-bottom:1px solid var(--border-color);background:var(--bg-body);display:flex;gap:0.5rem;align-items:center;">
                <div style="flex:1;">
                    <select id="posPatientSelect" class="form-control" style="font-size:0.85rem;">
                        <option value="">👤 Walk-in Cash Customer</option>
                        <?php foreach ($patients as $p): ?>
                            <option value="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-phone="<?= htmlspecialchars($p['phone']) ?>" data-points="<?= $p['loyalty_points'] ?>">
                                <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['phone']) ?>) - <?= $p['loyalty_points'] ?> pts
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-sm btn-secondary" onclick="openModal('quickAddPatientModal')" title="Add Patient">+</button>
            </div>

            <!-- Prescribing Doctor Selector & Quick Register -->
            <div style="padding:0.5rem 0.85rem;border-bottom:1px solid var(--border-color);background:var(--bg-body);display:flex;gap:0.5rem;align-items:center;">
                <div style="flex:1;">
                    <select id="posDoctorSelect" class="form-control" style="font-size:0.85rem;" onchange="toggleCustomDoctorField(this.value)">
                        <option value="">👨‍⚕️ Prescribing Doctor: Self / OTC (None)</option>
                        <option value="custom" style="font-weight:600;color:var(--primary);">✏️ Type Unregistered Doctor...</option>
                        <?php foreach ($doctors as $d): ?>
                            <option value="<?= $d['id'] ?>" data-name="<?= htmlspecialchars($d['name']) ?>" data-clinic="<?= htmlspecialchars($d['hospital_clinic'] ?? '') ?>" data-reg="<?= htmlspecialchars($d['registration_number'] ?? '') ?>">
                                <?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['hospital_clinic'] ?: 'Clinic') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="openModal('quickAddDoctorModal')" title="Register New Doctor Profile">+</button>
            </div>

            <!-- Unregistered Doctor Inputs (Shown if 'Type Unregistered Doctor' is selected) -->
            <div id="unregisteredDoctorFields" style="display:none;padding:0.5rem 0.85rem;background:#fffbeb;border-bottom:1px solid #fde68a;">
                <div style="font-size:11px;font-weight:600;color:#92400e;margin-bottom:4px;">Unregistered Doctor Details (Prints on Bill):</div>
                <div style="display:flex;gap:4px;">
                    <input type="text" id="customDoctorName" class="form-control" placeholder="Doctor Name (e.g. Dr. A. Sen)" style="font-size:0.8rem;padding:4px 8px;flex:1;">
                    <input type="text" id="customDoctorClinic" class="form-control" placeholder="Clinic / Hospital Name" style="font-size:0.8rem;padding:4px 8px;flex:1;">
                </div>
            </div>

            <!-- Cart Items Table Header -->
            <div style="padding:0.6rem 0.85rem;background:var(--border-light);border-bottom:1px solid var(--border-color);font-size:0.75rem;font-weight:700;color:var(--text-muted);display:grid;grid-template-columns: 2fr 1fr 1fr 1fr 30px;gap:0.5rem;">
                <div>MEDICINE / BATCH</div>
                <div>QTY</div>
                <div>RATE</div>
                <div>TOTAL</div>
                <div></div>
            </div>

            <!-- Cart Items Container -->
            <div id="cartItemsList" class="pos-cart-items">
                <div id="emptyCartMessage" style="text-align:center;color:var(--text-muted);padding:2rem 1rem;">
                    <div style="font-size:2rem;margin-bottom:0.5rem;">🛒</div>
                    <div>Cart is empty</div>
                    <div style="font-size:11px;">Scan barcode or click medicines on left to bill</div>
                </div>
            </div>

            <!-- Cart Summary & Checkout -->
            <div class="pos-cart-summary">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:var(--text-muted);">Subtotal:</span>
                    <strong id="summarySubtotal"><?= $pharmacy['currency_symbol'] ?>0.00</strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:var(--text-muted);">GST Tax (CGST + SGST):</span>
                    <strong id="summaryTax"><?= $pharmacy['currency_symbol'] ?>0.00</strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:var(--text-muted);">Discount:</span>
                    <span style="color:var(--danger);" id="summaryDiscount">- <?= $pharmacy['currency_symbol'] ?>0.00</span>
                </div>
                
                <hr style="border:none;border-top:1px dashed var(--border-color);margin:6px 0;">

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:0.95rem;font-weight:700;">Grand Total:</span>
                    <span style="font-size:1.6rem;font-weight:800;color:var(--primary);" id="summaryGrandTotal"><?= $pharmacy['currency_symbol'] ?>0.00</span>
                </div>

                <!-- Payment Mode Buttons -->
                <div style="display:grid;grid-template-columns: repeat(4, 1fr); gap: 4px; margin-bottom: 8px;">
                    <button type="button" class="btn btn-sm btn-secondary active-mode" id="btnModeCash" onclick="setPaymentMode('cash')">💵 Cash</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="btnModeUpi" onclick="setPaymentMode('upi')">📱 UPI</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="btnModeCard" onclick="setPaymentMode('card')">💳 Card</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="btnModeCredit" onclick="setPaymentMode('credit')">⚖️ Credit</button>
                </div>

                <!-- Hidden input to keep paidAmount in sync for complete checkout -->
                <input type="hidden" id="paidAmount" value="0">

                <!-- Bill Discount Section (Replaced Cash Received) -->
                <div id="discountBox" style="margin-bottom:8px;background:var(--bg-body);padding:8px 10px;border-radius:8px;border:1px solid var(--border-color);">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="font-size:11px;font-weight:700;color:var(--text-main);display:flex;align-items:center;gap:4px;">
                            🏷️ Discount (F8)
                        </span>
                        <div style="display:flex;gap:2px;background:var(--border-light);padding:2px;border-radius:5px;">
                            <button type="button" id="btnDiscountPercent" class="btn btn-sm" style="padding:2px 8px;font-size:11px;font-weight:700;border-radius:4px;background:var(--primary);color:#fff;" onclick="setDiscountType('percent')">%</button>
                            <button type="button" id="btnDiscountFlat" class="btn btn-sm" style="padding:2px 8px;font-size:11px;font-weight:700;border-radius:4px;background:transparent;color:var(--text-main);" onclick="setDiscountType('flat')"><?= $pharmacy['currency_symbol'] ?></button>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <div style="flex:1.2;position:relative;">
                            <input type="number" step="any" min="0" id="billDiscountValue" class="form-control" placeholder="Discount % (F8)" style="font-size:12px;padding:5px 8px;font-weight:600;" oninput="applyBillDiscount()" onchange="applyBillDiscount()">
                        </div>
                        <div style="display:flex;gap:3px;">
                            <button type="button" class="btn btn-sm btn-secondary" style="padding:4px 6px;font-size:10px;font-weight:600;" onclick="setDiscountPreset(0)">0%</button>
                            <button type="button" class="btn btn-sm btn-secondary" style="padding:4px 6px;font-size:10px;font-weight:600;" onclick="setDiscountPreset(5)">5%</button>
                            <button type="button" class="btn btn-sm btn-secondary" style="padding:4px 6px;font-size:10px;font-weight:600;" onclick="setDiscountPreset(10)">10%</button>
                            <button type="button" class="btn btn-sm btn-secondary" style="padding:4px 6px;font-size:10px;font-weight:600;" onclick="setDiscountPreset(15)">15%</button>
                        </div>
                    </div>
                </div>

                <!-- Complete Sale Trigger -->
                <button type="button" id="completeSaleBtn" class="btn btn-primary" style="width:100%;padding:0.75rem;font-size:1rem;font-weight:700;" onclick="submitPosCheckout()">
                    ⚡ Complete & Print (F9)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Invoice Success & Dual Print Center -->
<div id="saleSuccessModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 480px; text-align: center;">
        <div class="modal-body" style="padding: 2rem 1.5rem;">
            <div style="width:64px;height:64px;background:#d1fae5;color:#059669;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1rem;">
                ✓
            </div>
            <h2 style="color:var(--text-main);font-size:1.4rem;margin-bottom:4px;">Sale Completed!</h2>
            <p style="color:var(--text-muted);font-size:13px;margin-bottom:1rem;">Invoice Number: <strong id="successInvoiceNum" style="color:var(--primary);"></strong></p>
            
            <div style="background:var(--bg-body);padding:1rem;border-radius:8px;margin-bottom:1.5rem;">
                <div style="font-size:12px;color:var(--text-muted);">Total Paid:</div>
                <div style="font-size:1.8rem;font-weight:800;color:var(--text-main);" id="successGrandTotal"></div>
                <div style="font-size:12px;color:var(--success);" id="successChangeDue"></div>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;">
                <button class="btn btn-primary" onclick="triggerThermalPrint()" style="justify-content:center;">
                    🖨️ Print 80mm Thermal Receipt
                </button>
                <button class="btn btn-secondary" onclick="triggerStandardA4Print()" style="justify-content:center;">
                    📄 Print A4 / A5 Tax Invoice
                </button>
                <button class="btn btn-secondary" onclick="triggerWhatsAppShare()" style="justify-content:center;background:#25d366;color:#fff;border-color:#25d366;">
                    💬 Share Invoice on WhatsApp
                </button>
                <button class="btn btn-secondary" onclick="closeSuccessAndReset()" style="justify-content:center;margin-top:6px;">
                    ➕ Next Sale (F4)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Load Active Prescription (Module 13) -->
<div id="loadPrescriptionModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">🩺 Load Verified Doctor Prescription</h3>
            <button class="modal-close" onclick="closeModal('loadPrescriptionModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php if (empty($prescriptions)): ?>
                    <p style="color:var(--text-muted);text-align:center;">No pending verified prescriptions found.</p>
                <?php else: ?>
                    <?php foreach ($prescriptions as $rx): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-body);">
                            <div>
                                <strong style="color:var(--primary);"><?= htmlspecialchars($rx['prescription_number']) ?></strong>
                                <div style="font-size:12px;font-weight:600;"><?= htmlspecialchars($rx['patient_name']) ?> (<?= htmlspecialchars($rx['patient_phone']) ?>)</div>
                                <div style="font-size:11px;color:var(--text-muted);">Doctor: <?= htmlspecialchars($rx['doctor_name'] ?? 'Prescribing Physician') ?> • Date: <?= $rx['prescription_date'] ?></div>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="loadPrescriptionIntoCart(<?= $rx['id'] ?>, <?= $rx['patient_id'] ?>, '<?= htmlspecialchars(addslashes($rx['patient_name'])) ?>', '<?= htmlspecialchars(addslashes($rx['patient_phone'])) ?>')">
                                Load Rx
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('loadPrescriptionModal')">Close</button>
        </div>
    </div>
</div>

<!-- Modal: UPI QR Payment Popup -->
<div id="upiQrModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 360px; text-align: center;">
        <div class="modal-header">
            <h3 class="modal-title">📱 Scan UPI QR Code</h3>
            <button class="modal-close" onclick="closeModal('upiQrModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="upiQrContainer"></div>
            <p style="font-size:12px;color:var(--text-muted);margin-top:10px;">Supports GPay, PhonePe, Paytm, BHIM</p>
        </div>
        <div class="modal-footer" style="justify-content:center;">
            <button class="btn btn-primary" onclick="closeModal('upiQrModal'); submitPosCheckout();">Payment Confirmed</button>
        </div>
    </div>
</div>

<!-- Modal: Quick Add Patient -->
<div id="quickAddPatientModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h3 class="modal-title">➕ Quick Add Patient / Customer</h3>
            <button class="modal-close" onclick="closeModal('quickAddPatientModal')">&times;</button>
        </div>
        <form id="quickAddPatientForm" onsubmit="submitQuickAddPatient(event)">
            <div class="modal-body">
                <div style="margin-bottom: 0.85rem;">
                    <label class="form-label">Full Name *</label>
                    <input type="text" id="quickPatientName" class="form-control" required placeholder="e.g. Ramesh Patel" autocomplete="off">
                </div>
                <div class="form-row" style="margin-bottom: 0.85rem;">
                    <div class="form-col">
                        <label class="form-label">Mobile Number *</label>
                        <input type="tel" id="quickPatientPhone" class="form-control" required placeholder="10-digit mobile number" pattern="[0-9]{10}" title="10-digit mobile number" autocomplete="off">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Gender</label>
                        <select id="quickPatientGender" class="form-control">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row" style="margin-bottom: 0.85rem;">
                    <div class="form-col">
                        <label class="form-label">Age</label>
                        <input type="number" id="quickPatientAge" class="form-control" value="30" min="1" max="120">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Email (Optional)</label>
                        <input type="email" id="quickPatientEmail" class="form-control" placeholder="name@email.com">
                    </div>
                </div>
                <div style="margin-bottom: 0.85rem;">
                    <label class="form-label">Address / City (Optional)</label>
                    <input type="text" id="quickPatientAddress" class="form-control" placeholder="Street, Area, City">
                </div>
                <div id="quickPatientMsg" style="display:none;padding:8px 12px;border-radius:6px;font-size:12px;margin-top:6px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('quickAddPatientModal')">Cancel</button>
                <button type="submit" id="savePatientBtn" class="btn btn-primary">Save & Select Patient</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Quick Add Doctor -->
<div id="quickAddDoctorModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h3 class="modal-title">➕ Quick Register Doctor Profile</h3>
            <button class="modal-close" onclick="closeModal('quickAddDoctorModal')">&times;</button>
        </div>
        <form id="quickAddDoctorForm" onsubmit="submitQuickAddDoctor(event)">
            <div class="modal-body">
                <div style="margin-bottom: 0.85rem;">
                    <label class="form-label">Doctor Full Name *</label>
                    <input type="text" id="quickDocName" class="form-control" required placeholder="e.g. Dr. Rajesh Verma, MD" autocomplete="off">
                </div>
                <div class="form-row" style="margin-bottom: 0.85rem;">
                    <div class="form-col">
                        <label class="form-label">Hospital / Clinic Name</label>
                        <input type="text" id="quickDocClinic" class="form-control" placeholder="e.g. Lilavati Clinic">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Medical Reg No. (Optional)</label>
                        <input type="text" id="quickDocReg" class="form-control" placeholder="e.g. MCI-29182">
                    </div>
                </div>
                <div class="form-row" style="margin-bottom: 0.85rem;">
                    <div class="form-col">
                        <label class="form-label">Specialization</label>
                        <input type="text" id="quickDocSpec" class="form-control" value="General Physician" placeholder="Specialty">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Phone (Optional)</label>
                        <input type="text" id="quickDocPhone" class="form-control" placeholder="+91 ...">
                    </div>
                </div>
                <div id="quickDocMsg" style="display:none;padding:8px 12px;border-radius:6px;font-size:12px;margin-top:6px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('quickAddDoctorModal')">Cancel</button>
                <button type="submit" id="saveDoctorBtn" class="btn btn-primary">Save & Select Doctor</button>
            </div>
        </form>
    </div>
</div>


<script>
var posCart = [];
var currentPaymentMode = 'cash';
var activePrescriptionId = null;
var lastSaleResponse = null;

// Search live filter
document.getElementById('posSearchInput').addEventListener('input', function(e) {
    var val = e.target.value.toLowerCase().trim();
    var cards = document.querySelectorAll('.med-catalog-card');
    cards.forEach(function(card) {
        var text = card.innerText.toLowerCase();
        card.style.display = text.indexOf(val) !== -1 ? 'block' : 'none';
    });
});

// Barcode hardware scan callback
window.onBarcodeScanned = function(code) {
    fetch('<?= $baseURL ?>/api/barcode/lookup?code=' + encodeURIComponent(code))
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.batch) {
                addItemToCart(data.batch);
            }
        });
};

function selectMedicineForCart(medId, brandName) {
    fetch('<?= $baseURL ?>/api/pos/batches/' + medId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.batches && data.batches.length > 0) {
                // Select earliest expiry batch by default (FEFO principle)
                addItemToCart(data.batches[0]);
            } else {
                alert('No active stock available for ' + brandName);
            }
        });
}

function addItemToCart(batch) {
    var existingIndex = posCart.findIndex(function(item) {
        return item.batch_id === batch.id;
    });

    if (existingIndex > -1) {
        if (posCart[existingIndex].quantity < batch.quantity) {
            posCart[existingIndex].quantity += 1;
        } else {
            alert('Cannot add more than available batch stock (' + batch.quantity + ')');
        }
    } else {
        posCart.push({
            batch_id: batch.id,
            medicine_id: batch.medicine_id,
            brand_name: batch.brand_name,
            batch_number: batch.batch_number,
            expiry_date: batch.expiry_date,
            unit_price: parseFloat(batch.selling_price || batch.mrp),
            mrp: parseFloat(batch.mrp),
            gst_rate: parseFloat(batch.gst_rate || 12),
            quantity: 1,
            max_stock: parseInt(batch.quantity),
            discount_percent: 0
        });
    }
    renderCart();
}

function updateCartQty(index, delta) {
    var item = posCart[index];
    var newQty = item.quantity + delta;
    if (newQty <= 0) {
        posCart.splice(index, 1);
    } else if (newQty > item.max_stock) {
        alert('Max available batch stock is ' + item.max_stock);
    } else {
        item.quantity = newQty;
    }
    renderCart();
}

function removeCartItem(index) {
    posCart.splice(index, 1);
    renderCart();
}

function renderCart() {
    var list = document.getElementById('cartItemsList');
    if (posCart.length === 0) {
        list.innerHTML = '<div id="emptyCartMessage" style="text-align:center;color:var(--text-muted);padding:3rem 1rem;"><div style="font-size:2rem;margin-bottom:0.5rem;">🛒</div><div>Cart is empty</div><div style="font-size:11px;">Scan barcode or click medicines on left to bill</div></div>';
        updateTotals(0, 0, 0, 0);
        return;
    }

    var html = '';
    var subtotal = 0;
    var totalTax = 0;
    var totalDiscount = 0;

    posCart.forEach(function(item, idx) {
        var lineBase = item.unit_price * item.quantity;
        var lineDisc = (lineBase * item.discount_percent) / 100;
        var lineNet = lineBase - lineDisc;
        var lineTax = (lineNet * item.gst_rate) / 100;
        var lineTotal = lineNet + lineTax;

        subtotal += lineBase;
        totalDiscount += lineDisc;
        totalTax += lineTax;

        html += '<div class="cart-item-row">' +
                '<div>' +
                    '<strong style="color:var(--primary);">' + escapeHtml(item.brand_name) + '</strong>' +
                    '<div style="font-size:10px;color:var(--text-muted);">' +
                        'B: ' + escapeHtml(item.batch_number) + ' | Exp: ' + escapeHtml(item.expiry_date) +
                    '</div>' +
                '</div>' +
                '<div style="display:flex;align-items:center;gap:4px;">' +
                    '<button class="btn btn-sm btn-secondary" style="padding:1px 6px;" onclick="updateCartQty(' + idx + ', -1)">-</button>' +
                    '<strong>' + item.quantity + '</strong>' +
                    '<button class="btn btn-sm btn-secondary" style="padding:1px 6px;" onclick="updateCartQty(' + idx + ', 1)">+</button>' +
                '</div>' +
                '<div><?= $pharmacy['currency_symbol'] ?>' + item.unit_price.toFixed(2) + '</div>' +
                '<div><strong><?= $pharmacy['currency_symbol'] ?>' + lineTotal.toFixed(2) + '</strong></div>' +
                '<div><button class="btn btn-sm btn-danger" style="padding:1px 5px;" onclick="removeCartItem(' + idx + ')">&times;</button></div>' +
                '</div>';
    });

    list.innerHTML = html;

    // Calculate bill discount
    if (currentDiscountType === 'percent') {
        currentBillDiscountAmount = (subtotal * currentBillDiscountValue) / 100;
    } else {
        currentBillDiscountAmount = currentBillDiscountValue;
    }
    currentBillDiscountAmount = Math.min(currentBillDiscountAmount, subtotal);

    var totalDiscount = totalItemDiscount + currentBillDiscountAmount;
    var grandTotal = Math.max(0, Math.round(subtotal - totalDiscount + totalTax));
    updateTotals(subtotal, totalTax, totalDiscount, grandTotal);
}

function updateTotals(subtotal, tax, discount, grandTotal) {
    document.getElementById('summarySubtotal').innerText = '<?= $pharmacy['currency_symbol'] ?>' + subtotal.toFixed(2);
    document.getElementById('summaryTax').innerText = '<?= $pharmacy['currency_symbol'] ?>' + tax.toFixed(2);
    document.getElementById('summaryDiscount').innerText = '- <?= $pharmacy['currency_symbol'] ?>' + discount.toFixed(2);
    document.getElementById('summaryGrandTotal').innerText = '<?= $pharmacy['currency_symbol'] ?>' + grandTotal.toFixed(2);
    
    var paidInput = document.getElementById('paidAmount');
    if (paidInput) {
        paidInput.value = grandTotal;
    }
}

var currentDiscountType = 'percent'; // 'percent' or 'flat'
var currentBillDiscountValue = 0;
var currentBillDiscountAmount = 0;

function setDiscountType(type) {
    currentDiscountType = type;
    var btnPct = document.getElementById('btnDiscountPercent');
    var btnFlat = document.getElementById('btnDiscountFlat');
    var input = document.getElementById('billDiscountValue');

    if (type === 'percent') {
        if (btnPct) { btnPct.style.background = 'var(--primary)'; btnPct.style.color = '#fff'; }
        if (btnFlat) { btnFlat.style.background = 'transparent'; btnFlat.style.color = 'var(--text-main)'; }
        if (input) input.placeholder = 'Discount % (F8)';
    } else {
        if (btnFlat) { btnFlat.style.background = 'var(--primary)'; btnFlat.style.color = '#fff'; }
        if (btnPct) { btnPct.style.background = 'transparent'; btnPct.style.color = 'var(--text-main)'; }
        if (input) input.placeholder = 'Discount Amount (F8)';
    }
    applyBillDiscount();
}

function setDiscountPreset(pct) {
    setDiscountType('percent');
    var input = document.getElementById('billDiscountValue');
    if (input) {
        input.value = pct > 0 ? pct : '';
    }
    applyBillDiscount();
}

function applyBillDiscount() {
    var input = document.getElementById('billDiscountValue');
    var val = parseFloat(input ? input.value : 0) || 0;
    currentBillDiscountValue = Math.max(0, val);
    renderCart();
}

function setPaymentMode(mode) {
    currentPaymentMode = mode;
    ['btnModeCash', 'btnModeUpi', 'btnModeCard', 'btnModeCredit'].forEach(function(id) {
        document.getElementById(id).style.background = 'var(--bg-card)';
        document.getElementById(id).style.color = 'var(--text-main)';
    });
    
    var activeBtnId = 'btnMode' + mode.charAt(0).toUpperCase() + mode.slice(1);
    var activeBtn = document.getElementById(activeBtnId);
    if (activeBtn) {
        activeBtn.style.background = 'var(--primary)';
        activeBtn.style.color = '#fff';
    }

    if (mode === 'upi') {
        var totalText = document.getElementById('summaryGrandTotal').innerText.replace(/[^0-9.]/g, '');
        var total = parseFloat(totalText) || 0;
        if (total > 0) {
            renderUpiQR('upiQrContainer', 'infosofcare@icici', 'INFOSOF Care Pharmacy', total, 'Pharmacy Counter Bill');
            openModal('upiQrModal');
        }
    }
}

function calculateChange() {
    var totalText = document.getElementById('summaryGrandTotal').innerText.replace(/[^0-9.]/g, '');
    var total = parseFloat(totalText) || 0;
    var paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    var change = Math.max(0, paid - total);
    document.getElementById('changeDueText').innerText = '<?= $pharmacy['currency_symbol'] ?>' + change.toFixed(2);
}

function loadPrescriptionIntoCart(rxId, patId, patName, patPhone) {
    activePrescriptionId = rxId;
    var select = document.getElementById('posPatientSelect');
    select.value = patId;
    closeModal('loadPrescriptionModal');

    // Auto-load items from prescription
    fetch('<?= $baseURL ?>/api/prescriptions/' + rxId + '/items')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.items) {
                data.items.forEach(function(item) {
                    selectMedicineForCart(item.medicine_id, item.medicine_name);
                });
            }
        });
}

function submitPosCheckout() {
    if (posCart.length === 0) {
        alert('Cart is empty.');
        return;
    }

    var patientSelect = document.getElementById('posPatientSelect');
    var patientId = patientSelect.value || null;
    var customerName = patientId ? patientSelect.options[patientSelect.selectedIndex].getAttribute('data-name') : 'Walk-in Cash Customer';
    var customerPhone = patientId ? patientSelect.options[patientSelect.selectedIndex].getAttribute('data-phone') : '';
    var paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;

    var docSelect = document.getElementById('posDoctorSelect');
    var docId = null;
    var docName = null;
    if (docSelect) {
        if (docSelect.value === 'custom') {
            var customName = (document.getElementById('customDoctorName').value || '').trim();
            var customClinic = (document.getElementById('customDoctorClinic').value || '').trim();
            if (customName) {
                docName = customName + (customClinic ? ' (' + customClinic + ')' : '');
            }
        } else if (docSelect.value) {
            docId = parseInt(docSelect.value);
            var selectedOption = docSelect.options[docSelect.selectedIndex];
            var dName = selectedOption.getAttribute('data-name');
            var dClinic = selectedOption.getAttribute('data-clinic');
            docName = dName + (dClinic ? ' (' + dClinic + ')' : '');
        }
    }

    var payload = {
        patient_id: patientId,
        customer_name: customerName,
        customer_phone: customerPhone,
        doctor_id: docId,
        doctor_name: docName,
        prescription_id: activePrescriptionId,
        payment_mode: currentPaymentMode,
        paid_amount: paidAmount,
        bill_discount: currentBillDiscountAmount || 0,
        discount_amount: currentBillDiscountAmount || 0,
        items: posCart
    };

    fetch('<?= $baseURL ?>/pos/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            lastSaleResponse = data;
            lastSaleResponse.patient_phone = customerPhone;
            lastSaleResponse.customer_name = customerName;
            lastSaleResponse.cart_items = [].concat(posCart);

            document.getElementById('successInvoiceNum').innerText = data.invoice_number;
            document.getElementById('successGrandTotal').innerText = '<?= $pharmacy['currency_symbol'] ?>' + parseFloat(data.grand_total).toFixed(2);
            document.getElementById('successChangeDue').innerText = 'Change Given: <?= $pharmacy['currency_symbol'] ?>' + parseFloat(data.change_amount).toFixed(2);
            
            openModal('saleSuccessModal');
        } else {
            alert(data.message || 'Error completing checkout');
        }
    })
    .catch(function(err) {
        alert('Checkout error: ' + err);
    });
}

function triggerThermalPrint() {
    if (lastSaleResponse && lastSaleResponse.invoice_id) {
        window.open('<?= $baseURL ?>/sales/invoice/' + lastSaleResponse.invoice_id + '?format=thermal', '_blank');
    }
}

function triggerStandardA4Print() {
    if (lastSaleResponse && lastSaleResponse.invoice_id) {
        window.open('<?= $baseURL ?>/sales/invoice/' + lastSaleResponse.invoice_id + '?format=standard', '_blank');
    }
}

function triggerWhatsAppShare() {
    if (lastSaleResponse) {
        var phone = lastSaleResponse.patient_phone || prompt("Enter customer WhatsApp mobile number (10 digits):");
        if (phone) {
            shareOnWhatsApp(phone, lastSaleResponse.invoice_number, lastSaleResponse.customer_name, lastSaleResponse.grand_total, lastSaleResponse.cart_items);
        }
    }
}

function closeSuccessAndReset() {
    closeModal('saleSuccessModal');
    resetPosCart();
}

function resetPosCart() {
    posCart = [];
    activePrescriptionId = null;
    document.getElementById('posPatientSelect').value = '';
    var discInput = document.getElementById('billDiscountValue');
    if (discInput) discInput.value = '';
    currentBillDiscountValue = 0;
    currentBillDiscountAmount = 0;
    renderCart();
    document.getElementById('posSearchInput').focus();
}

function submitQuickAddPatient(e) {
    e.preventDefault();
    var name = document.getElementById('quickPatientName').value.trim();
    var phone = document.getElementById('quickPatientPhone').value.trim();
    var gender = document.getElementById('quickPatientGender').value;
    var age = document.getElementById('quickPatientAge').value;
    var email = document.getElementById('quickPatientEmail').value.trim();
    var address = document.getElementById('quickPatientAddress').value.trim();
    var msgBox = document.getElementById('quickPatientMsg');

    if (!name || !phone) {
        msgBox.style.display = 'block';
        msgBox.style.background = '#fee2e2';
        msgBox.style.color = '#991b1b';
        msgBox.innerText = 'Patient Name and Phone number are required.';
        return;
    }

    var saveBtn = document.getElementById('savePatientBtn');
    saveBtn.disabled = true;
    saveBtn.innerText = 'Saving...';
    msgBox.style.display = 'none';

    var formData = new URLSearchParams();
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('gender', gender);
    formData.append('age', age);
    formData.append('email', email);
    formData.append('address', address);

    fetch('<?= $baseURL ?>/patients/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData.toString()
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save & Select Patient';
        if (data.success) {
            var select = document.getElementById('posPatientSelect');
            var existingOption = null;
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].value == data.id) {
                    existingOption = select.options[i];
                    break;
                }
            }

            if (!existingOption) {
                var opt = document.createElement('option');
                opt.value = data.id;
                opt.setAttribute('data-name', data.name);
                opt.setAttribute('data-phone', data.phone);
                opt.setAttribute('data-points', data.loyalty_points || 50);
                opt.textContent = data.name + ' (' + data.phone + ') - ' + (data.loyalty_points || 50) + ' pts';
                select.appendChild(opt);
                select.value = data.id;
            } else {
                select.value = data.id;
            }

            closeModal('quickAddPatientModal');
            document.getElementById('quickAddPatientForm').reset();
        } else {
            msgBox.style.display = 'block';
            msgBox.style.background = '#fee2e2';
            msgBox.style.color = '#991b1b';
            msgBox.innerText = data.message || 'Error saving patient.';
        }
    })
    .catch(function(err) {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save & Select Patient';
        msgBox.style.display = 'block';
        msgBox.style.background = '#fee2e2';
        msgBox.style.color = '#991b1b';
        msgBox.innerText = 'Network error saving patient: ' + err;
    });
}

function toggleCustomDoctorField(val) {
    var customBox = document.getElementById('unregisteredDoctorFields');
    if (val === 'custom') {
        customBox.style.display = 'block';
        document.getElementById('customDoctorName').focus();
    } else {
        customBox.style.display = 'none';
    }
}

function submitQuickAddDoctor(e) {
    e.preventDefault();
    var name = document.getElementById('quickDocName').value.trim();
    var clinic = document.getElementById('quickDocClinic').value.trim();
    var reg = document.getElementById('quickDocReg').value.trim();
    var spec = document.getElementById('quickDocSpec').value.trim();
    var phone = document.getElementById('quickDocPhone').value.trim();
    var msgBox = document.getElementById('quickDocMsg');

    if (!name) {
        msgBox.style.display = 'block';
        msgBox.style.background = '#fee2e2';
        msgBox.style.color = '#991b1b';
        msgBox.innerText = 'Doctor Name is required.';
        return;
    }

    var saveBtn = document.getElementById('saveDoctorBtn');
    saveBtn.disabled = true;
    saveBtn.innerText = 'Saving...';
    msgBox.style.display = 'none';

    var formData = new URLSearchParams();
    formData.append('name', name);
    formData.append('hospital_clinic', clinic);
    formData.append('registration_number', reg);
    formData.append('specialization', spec);
    formData.append('phone', phone);

    fetch('<?= $baseURL ?>/doctors/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData.toString()
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save & Select Doctor';
        if (data.success) {
            var select = document.getElementById('posDoctorSelect');
            var opt = document.createElement('option');
            opt.value = data.id;
            opt.setAttribute('data-name', data.name);
            opt.setAttribute('data-clinic', data.hospital_clinic || '');
            opt.setAttribute('data-reg', data.registration_number || '');
            opt.textContent = data.name + (data.hospital_clinic ? ' (' + data.hospital_clinic + ')' : '');
            select.appendChild(opt);
            select.value = data.id;

            toggleCustomDoctorField(data.id);
            closeModal('quickAddDoctorModal');
            document.getElementById('quickAddDoctorForm').reset();
        } else {
            msgBox.style.display = 'block';
            msgBox.style.background = '#fee2e2';
            msgBox.style.color = '#991b1b';
            msgBox.innerText = data.message || 'Error saving doctor.';
        }
    })
    .catch(function(err) {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save & Select Doctor';
        msgBox.style.display = 'block';
        msgBox.style.background = '#fee2e2';
        msgBox.style.color = '#991b1b';
        msgBox.innerText = 'Network error saving doctor: ' + err;
    });
}

window.resetPosCart = resetPosCart;
</script>
