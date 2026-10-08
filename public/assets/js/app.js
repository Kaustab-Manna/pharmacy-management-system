/**
 * INFOSOF Pharmacy Management - Frontend JavaScript Engine
 * Compatible with modern browsers and legacy browsers (ES5/ES6 polyfill friendly)
 */

document.addEventListener('DOMContentLoaded', function () {
    initTheme();
    initSidebarToggle();
    initSidebarDropdowns();
    initBarcodeScannerListener();
    initShortcuts();
});

// ----------------------------------------------------
// 1. Theme Management (Dark / Light)
// ----------------------------------------------------
function initTheme() {
    var savedTheme = localStorage.getItem('pharmacy_theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);

    var themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.innerHTML = savedTheme === 'dark' ? '☀️' : '🌙';
        themeBtn.addEventListener('click', function () {
            var current = document.documentElement.getAttribute('data-theme');
            var nextTheme = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', nextTheme);
            localStorage.setItem('pharmacy_theme', nextTheme);
            themeBtn.innerHTML = nextTheme === 'dark' ? '☀️' : '🌙';
        });
    }
}

// ----------------------------------------------------
// 2. Sidebar Mobile & Desktop Toggle & Dropdowns
// ----------------------------------------------------
function initSidebarToggle() {
    var toggleBtn = document.getElementById('sidebarToggleBtn');
    var sidebar = document.querySelector('.app-sidebar');
    var container = document.querySelector('.app-container');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            if (window.innerWidth <= 900) {
                sidebar.classList.toggle('show');
            } else {
                if (container) container.classList.toggle('sidebar-collapsed');
            }
        });
    }
}

function initSidebarDropdowns() {
    var headers = document.querySelectorAll('.sidebar-dropdown-header');
    headers.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var parent = this.closest('.sidebar-dropdown');
            if (parent) {
                parent.classList.toggle('open');
            }
        });
    });
}

// ----------------------------------------------------
// 3. Modal Dialog Manager
// ----------------------------------------------------
function openModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Close on clicking backdrop
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

// ----------------------------------------------------
// 4. Hardware Barcode Scanner Listener
// ----------------------------------------------------
var barcodeBuffer = '';
var lastKeyTime = Date.now();

function initBarcodeScannerListener() {
    window.addEventListener('keydown', function (e) {
        // Barcode scanners type very rapidly (< 35ms between keystrokes)
        var currentTime = Date.now();
        var timeDiff = currentTime - lastKeyTime;
        lastKeyTime = currentTime;

        // Skip standard input fields unless active element is specific barcode field
        var activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        var isTextInput = (activeTag === 'input' || activeTag === 'textarea');

        if (e.key === 'Enter') {
            if (barcodeBuffer.length >= 4) {
                handleBarcodeScanned(barcodeBuffer.trim());
                barcodeBuffer = '';
                if (!isTextInput) {
                    e.preventDefault();
                }
            }
        } else if (e.key.length === 1) {
            if (timeDiff > 80 && !isTextInput) {
                barcodeBuffer = '';
            }
            barcodeBuffer += e.key;
        }
    });
}

function handleBarcodeScanned(code) {
    console.log("Barcode Detected:", code);
    var searchInput = document.getElementById('posBarcodeSearch') || document.getElementById('globalSearch');
    if (searchInput) {
        searchInput.value = code;
        searchInput.dispatchEvent(new Event('input', { bubbles: true }));
        searchInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (typeof window.onBarcodeScanned === 'function') {
        window.onBarcodeScanned(code);
    }
}

// ----------------------------------------------------
// 5. Global Keyboard Shortcuts (F1-F9, Esc)
// ----------------------------------------------------
function initShortcuts() {
    window.addEventListener('keydown', function (e) {
        var key = e.key;
        var keyCode = e.keyCode || e.which;

        // F1 (KeyCode 112): Open / Toggle Keyboard Shortcuts Dialog
        if (key === 'F1' || keyCode === 112) {
            e.preventDefault();
            e.stopPropagation();
            var modal = document.getElementById('shortcutsModal');
            if (modal) {
                if (modal.classList.contains('active')) {
                    closeModal('shortcutsModal');
                } else {
                    openModal('shortcutsModal');
                }
            }
            return false;
        }

        // F2 (KeyCode 113): Focus Search / Scanner
        if (key === 'F2' || keyCode === 113) {
            e.preventDefault();
            e.stopPropagation();
            var searchInput = document.getElementById('posSearchInput') || 
                              document.getElementById('posBarcodeSearch') || 
                              document.getElementById('liveBarcodeInput') ||
                              document.getElementById('globalSearch') ||
                              document.getElementById('posSearch');
            if (searchInput) {
                searchInput.focus();
                if (typeof searchInput.select === 'function') {
                    searchInput.select();
                }
            }
            return false;
        }

        // F4 (KeyCode 115): New POS Sale / Reset Cart
        if (key === 'F4' || keyCode === 115) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof window.resetPosCart === 'function') {
                window.resetPosCart();
            } else if (typeof resetPosCart === 'function') {
                resetPosCart();
            } else {
                // Navigate to POS if on another module
                window.location.href = '/pos';
            }
            return false;
        }

        // F8 (KeyCode 119): Focus Payment Cash Received Field
        if (key === 'F8' || keyCode === 119) {
            var paidInput = document.getElementById('paidAmount');
            if (paidInput) {
                e.preventDefault();
                e.stopPropagation();
                paidInput.focus();
                if (typeof paidInput.select === 'function') {
                    paidInput.select();
                }
                return false;
            }
        }

        // F9 (KeyCode 120): Complete Sale & Print
        if (key === 'F9' || keyCode === 120) {
            var checkoutBtn = document.getElementById('completeSaleBtn');
            if (checkoutBtn && !checkoutBtn.disabled) {
                e.preventDefault();
                e.stopPropagation();
                checkoutBtn.click();
                return false;
            } else if (typeof window.submitPosCheckout === 'function') {
                e.preventDefault();
                e.stopPropagation();
                window.submitPosCheckout();
                return false;
            }
        }

        // Escape (KeyCode 27): Close Modals & Popups
        if (key === 'Escape' || key === 'Esc' || keyCode === 27) {
            var activeModals = document.querySelectorAll('.modal-overlay.active');
            if (activeModals.length > 0) {
                e.preventDefault();
                activeModals.forEach(function (m) {
                    m.classList.remove('active');
                });
            }
            var userMenu = document.getElementById('userMenuDropdown');
            if (userMenu && userMenu.classList.contains('active')) {
                userMenu.classList.remove('active');
            }
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
        }
    }, true);
}

// ----------------------------------------------------
// 6. Barcode Generator (Code 128 Canvas / SVG Renderer)
// ----------------------------------------------------
function generateBarcodeSVG(code) {
    // Generate clean SVG pattern for Code 128
    var bars = '';
    var width = 2;
    var height = 50;
    var x = 10;
    
    // Simple robust pattern representation
    for (var i = 0; i < code.length; i++) {
        var charCode = code.charCodeAt(i);
        var pattern = (charCode % 2 === 0) ? "1101" : "1011";
        for (var j = 0; j < pattern.length; j++) {
            if (pattern[j] === '1') {
                bars += '<rect x="' + x + '" y="5" width="' + width + '" height="' + height + '" fill="#000" />';
            }
            x += width + 1;
        }
        x += 2;
    }
    
    var totalWidth = x + 15;
    return '<svg width="' + totalWidth + '" height="75" xmlns="http://www.w3.org/2000/svg">' +
           bars +
           '<text x="' + (totalWidth / 2) + '" y="' + (height + 18) + '" text-anchor="middle" font-family="monospace" font-size="12" fill="#000">' +
           escapeHtml(code) +
           '</text></svg>';
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
    });
}

// ----------------------------------------------------
// 7. Dynamic UPI QR Code Generator
// ----------------------------------------------------
function renderUpiQR(elementId, vpa, name, amount, note) {
    var el = document.getElementById(elementId);
    if (!el) return;
    
    var upiUri = "upi://pay?pa=" + encodeURIComponent(vpa) +
                 "&pn=" + encodeURIComponent(name) +
                 "&am=" + encodeURIComponent(parseFloat(amount).toFixed(2)) +
                 "&cu=INR&tn=" + encodeURIComponent(note || 'Medicine Purchase');

    // Use QuickChart / standard dynamic QR image
    var qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" + encodeURIComponent(upiUri);
    el.innerHTML = '<div style="text-align:center;">' +
                   '<img src="' + qrUrl + '" alt="UPI QR" style="width:160px;height:160px;border:1px solid #ccc;border-radius:8px;padding:6px;background:#fff;" />' +
                   '<p style="margin-top:6px;font-size:12px;color:#64748b;">Scan to pay <strong>₹' + parseFloat(amount).toFixed(2) + '</strong></p>' +
                   '</div>';
}

// ----------------------------------------------------
// 8. WhatsApp Sharing Link Generator
// ----------------------------------------------------
function shareOnWhatsApp(phone, invoiceNum, customerName, totalAmount, items) {
    if (!phone) {
        alert("Please enter customer's mobile number first.");
        return;
    }
    
    var cleanPhone = phone.replace(/[^0-9]/g, '');
    if (cleanPhone.length === 10) {
        cleanPhone = '91' + cleanPhone;
    }
    
    var msg = "*INFOSOF CARE PHARMACY*\n" +
              "Invoice: #" + invoiceNum + "\n" +
              "Dear " + (customerName || 'Valued Customer') + ",\n" +
              "Thank you for visiting us! Your bill amount is *₹" + parseFloat(totalAmount).toFixed(2) + "*.\n\n" +
              "Medication Reminders:\n";
              
    if (items && items.length) {
        items.forEach(function (item, idx) {
            msg += (idx + 1) + ". " + item.name + " (" + item.qty + " " + item.unit + ") - " + (item.dosage || 'As prescribed') + "\n";
        });
    }
    
    msg += "\nGet well soon!\nPharmacy Helpline: +91 98200 12345";
    
    var waUrl = "https://api.whatsapp.com/send?phone=" + cleanPhone + "&text=" + encodeURIComponent(msg);
    window.open(waUrl, '_blank');
}

// ----------------------------------------------------
// 9. Table Export to CSV
// ----------------------------------------------------
function exportTableToCSV(tableId, filename) {
    var table = document.getElementById(tableId);
    if (!table) return;

    var rows = table.querySelectorAll('tr');
    var csv = [];
    for (var i = 0; i < rows.length; i++) {
        var row = [];
        var cols = rows[i].querySelectorAll('th, td');
        for (var j = 0; j < cols.length; j++) {
            var text = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + text.trim() + '"');
        }
        csv.push(row.join(','));
    }

    var csvFile = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    var downloadLink = document.createElement('a');
    downloadLink.download = filename || 'pharmacy-export.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// ----------------------------------------------------
// 10. Thermal Printing Trigger
// ----------------------------------------------------
function printThermalReceipt() {
    document.body.classList.add('print-thermal');
    window.print();
    setTimeout(function () {
        document.body.classList.remove('print-thermal');
    }, 1000);
}

function printStandardInvoice() {
    document.body.classList.remove('print-thermal');
    window.print();
}
