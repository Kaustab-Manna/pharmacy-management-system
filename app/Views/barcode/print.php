<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Barcode Labels - INFOSOF Pharmacy</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 15px;
            background: #f8fafc;
        }
        .labels-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            max-width: 900px;
            margin: 0 auto;
        }
        .barcode-sticker {
            background: #ffffff;
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        .sticker-pharmacy {
            font-size: 9px;
            font-weight: 700;
            color: #0f766e;
            text-transform: uppercase;
        }
        .sticker-name {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 2px 0;
        }
        .sticker-details {
            font-size: 9px;
            color: #475569;
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .barcode-graphic {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 4px 0 2px 0;
            overflow: hidden;
            min-height: 36px;
        }
        .barcode-graphic svg {
            max-width: 100%;
            height: 36px;
            display: block;
        }
        .barcode-number {
            font-family: monospace;
            font-size: 10px;
            color: #000;
            letter-spacing: 1px;
            font-weight: 600;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .barcode-sticker {
                border: 1px dashed #cbd5e1;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .barcode-graphic svg rect {
                fill: #000000 !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print" style="max-width:900px;margin:0 auto 15px auto;display:flex;justify-content:space-between;align-items:center;background:#fff;padding:12px 20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
    <div>
        <strong>Printing Barcode Sheet: <?= htmlspecialchars($batch['brand_name'] ?? 'Medicine') ?></strong>
        <span style="font-size:12px;color:#64748b;margin-left:10px;">(<?= $copies ?> Labels)</span>
    </div>
    <div style="display:flex;gap:10px;">
        <button onclick="window.print()" style="background:#0d9488;color:#fff;border:none;padding:8px 16px;border-radius:6px;font-weight:bold;cursor:pointer;">🖨️ Print Now</button>
        <button onclick="window.close()" style="background:#e2e8f0;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;">Close</button>
    </div>
</div>

<div class="labels-grid">
    <?php if ($batch): ?>
        <?php 
            $barcodeVal = !empty($batch['barcode']) ? $batch['barcode'] : ($batch['batch_number'] ?? '000000');
            $barcodeSvg = \App\Core\Barcode::renderSvg($barcodeVal, 36, 1.35, false);
            $pharmacyTitle = !empty($pharmacy['pharmacy_name']) ? $pharmacy['pharmacy_name'] : 'INFOSOF PHARMACY';
        ?>
        <?php for ($i = 0; $i < $copies; $i++): ?>
            <div class="barcode-sticker">
                <div class="sticker-pharmacy"><?= htmlspecialchars($pharmacyTitle) ?></div>
                <div class="sticker-name"><?= htmlspecialchars($batch['brand_name']) ?> (<?= htmlspecialchars($batch['pack_size']) ?>)</div>
                <div class="sticker-details">
                    <span>B: <?= htmlspecialchars($batch['batch_number']) ?></span>
                    <span>EXP: <?= htmlspecialchars($batch['expiry_date']) ?></span>
                </div>
                <div class="barcode-graphic">
                    <?= $barcodeSvg ?>
                </div>
                <div class="barcode-number"><?= htmlspecialchars($barcodeVal) ?></div>
                <div style="font-size:10px;font-weight:bold;margin-top:2px;">MRP: ₹<?= number_format($batch['mrp'], 2) ?></div>
            </div>
        <?php endfor; ?>
    <?php else: ?>
        <p>No batch specified.</p>
    <?php endif; ?>
</div>

</body>
</html>
