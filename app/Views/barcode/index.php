<div class="page-header">
    <div class="page-title">
        <h1>Barcode & Medicine Scanning Station</h1>
        <p>Real-time barcode generation, USB laser scanner detection, and printable pharmacy sticker labels.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns: 1.2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; align-items: start;">
    
    <!-- Live Barcode Scanner & Lookup Station -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">▌│█║ Live Hardware / Manual Barcode Scanner</h3>
            <span class="nav-badge badge-success">READY</span>
        </div>
        <div class="card-body">
            <div style="margin-bottom:1rem;">
                <label class="form-label">Scan Barcode with USB Scanner or Type & Press Enter:</label>
                <div style="display:flex;gap:0.5rem;">
                    <input type="text" id="liveBarcodeInput" class="form-control" placeholder="Scan barcode gun here or type code..." autofocus autocomplete="off">
                    <button class="btn btn-primary" onclick="triggerBarcodeLookup()">Lookup</button>
                </div>
            </div>

            <div id="barcodeResultBox" style="display:none;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;padding:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h4 id="resBrandName" style="color:var(--primary);font-size:1.1rem;margin-bottom:2px;"></h4>
                        <div id="resMedName" style="font-size:12px;color:var(--text-muted);margin-bottom:6px;"></div>
                        <div style="font-size:12px;display:flex;gap:12px;">
                            <span>Batch: <strong id="resBatchNo"></strong></span>
                            <span>Exp: <strong id="resExpDate"></strong></span>
                            <span>Stock: <strong id="resStock"></strong></span>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:10px;color:var(--text-muted);">MRP</div>
                        <div id="resMrp" style="font-size:1.25rem;font-weight:800;color:var(--text-main);"></div>
                    </div>
                </div>
                <div style="margin-top:1rem;display:flex;gap:0.5rem;">
                    <a id="resAddPosBtn" href="<?= $baseURL ?>/pos" class="btn btn-sm btn-primary">⚡ Send to POS Counter</a>
                    <a id="resPrintBtn" href="#" target="_blank" class="btn btn-sm btn-secondary">🖨️ Print Label</a>
                </div>
            </div>

            <!-- Quick sample barcode buttons for test evaluation -->
            <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px dashed var(--border-color);">
                <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">
                    Demo Barcodes (Click to test instant scan):
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="btn btn-sm btn-secondary" onclick="simulateScan('890123456002')">Paracetamol: 890123456002</button>
                    <button class="btn btn-sm btn-secondary" onclick="simulateScan('890123456001')">Amoxil: 890123456001</button>
                    <button class="btn btn-sm btn-secondary" onclick="simulateScan('890123456004')">Glycomet: 890123456004</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Sticker Generator -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🏷️ Print Barcode Labels</h3>
        </div>
        <form method="GET" action="<?= $baseURL ?>/barcode/print" target="_blank">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Select Medicine Batch *</label>
                    <select name="batch_id" class="form-control" required>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>">
                                <?= htmlspecialchars($b['brand_name']) ?> (Batch: <?= htmlspecialchars($b['batch_number']) ?> - Exp: <?= $b['expiry_date'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Number of Stickers</label>
                        <select name="copies" class="form-control">
                            <option value="1">Single Sticker (50x25mm)</option>
                            <option value="12">12 Stickers</option>
                            <option value="24" selected>24 Stickers (A4 Sheet)</option>
                            <option value="30">30 Stickers (A4 Sheet)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn-primary">Generate Printable Sheet</button>
            </div>
        </form>
    </div>
</div>

<!-- Active Barcodes Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 Active Batch Barcodes Registry</h3>
    </div>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Brand / Medicine</th>
                    <th>Batch No</th>
                    <th>Barcode Representation</th>
                    <th>Expiry</th>
                    <th>MRP</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($batches as $b): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--primary);"><?= htmlspecialchars($b['brand_name']) ?></strong>
                            <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($b['medicine_name']) ?> (<?= htmlspecialchars($b['pack_size']) ?>)</div>
                        </td>
                        <td>
                            <strong style="font-family:var(--font-mono);"><?= htmlspecialchars($b['batch_number']) ?></strong>
                        </td>
                        <td>
                            <div class="barcode-preview" style="display:inline-block;padding:6px 10px;background:#fff;border:1px solid var(--border-color);border-radius:6px;text-align:center;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                                <?= \App\Core\Barcode::renderSvg($b['barcode'], 24, 0.95, false) ?>
                                <div style="font-size:10px;font-family:var(--font-mono);font-weight:700;color:var(--text-main);margin-top:3px;letter-spacing:0.5px;"><?= htmlspecialchars($b['barcode']) ?></div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($b['expiry_date']) ?></td>
                        <td><?= $pharmacy['currency_symbol'] ?><?= number_format($b['mrp'], 2) ?></td>
                        <td>
                            <a href="<?= $baseURL ?>/barcode/print?batch_id=<?= $b['id'] ?>&copies=24" target="_blank" class="btn btn-sm btn-secondary">
                                🖨️ Labels
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('liveBarcodeInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        triggerBarcodeLookup();
    }
});

function simulateScan(code) {
    document.getElementById('liveBarcodeInput').value = code;
    triggerBarcodeLookup();
}

function triggerBarcodeLookup() {
    var code = document.getElementById('liveBarcodeInput').value.trim();
    if (!code) return;

    fetch('<?= $baseURL ?>/api/barcode/lookup?code=' + encodeURIComponent(code))
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.batch) {
                var b = data.batch;
                document.getElementById('resBrandName').innerText = b.brand_name;
                document.getElementById('resMedName').innerText = b.medicine_name + ' (' + b.strength + ') - ' + b.dosage_form;
                document.getElementById('resBatchNo').innerText = b.batch_number;
                document.getElementById('resExpDate').innerText = b.expiry_date;
                document.getElementById('resStock').innerText = b.quantity + ' ' + b.unit;
                document.getElementById('resMrp').innerText = '<?= $pharmacy['currency_symbol'] ?>' + parseFloat(b.mrp).toFixed(2);
                document.getElementById('resPrintBtn').href = '<?= $baseURL ?>/barcode/print?batch_id=' + b.id;
                document.getElementById('barcodeResultBox').style.display = 'block';
            } else {
                alert(data.message || 'Barcode not found.');
                document.getElementById('barcodeResultBox').style.display = 'none';
            }
        })
        .catch(function(err) {
            alert('Lookup error occurred: ' + err);
        });
}
</script>
