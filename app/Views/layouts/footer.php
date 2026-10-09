    </div> <!-- End .app-content -->
</main> <!-- End .app-main -->
</div> <!-- End .app-container -->

<!-- Global Keyboard Shortcuts Modal -->
<div id="shortcutsModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width:500px;">
        <div class="modal-header">
            <h3 class="modal-title">⚡ Keyboard Shortcuts & Quick Keys</h3>
            <button class="modal-close" onclick="closeModal('shortcutsModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px;">
                <div style="background:var(--bg-body);padding:8px 12px;border-radius:6px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Shortcuts Guide</span>
                    <kbd style="background:var(--bg-card);padding:2px 6px;border-radius:4px;border:1px solid var(--border-color);font-weight:700;">F1</kbd>
                </div>
                <div style="background:var(--bg-body);padding:8px 12px;border-radius:6px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Focus Search / Scanner</span>
                    <kbd style="background:var(--bg-card);padding:2px 6px;border-radius:4px;border:1px solid var(--border-color);font-weight:700;">F2</kbd>
                </div>
                <div style="background:var(--bg-body);padding:8px 12px;border-radius:6px;display:flex;justify-content:space-between;align-items:center;">
                    <span>New POS Sale / Cart</span>
                    <kbd style="background:var(--bg-card);padding:2px 6px;border-radius:4px;border:1px solid var(--border-color);font-weight:700;">F4</kbd>
                </div>
                <div style="background:var(--bg-body);padding:8px 12px;border-radius:6px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Focus Discount Field</span>
                    <kbd style="background:var(--bg-card);padding:2px 6px;border-radius:4px;border:1px solid var(--border-color);font-weight:700;">F8</kbd>
                </div>
                <div style="background:var(--bg-body);padding:8px 12px;border-radius:6px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Complete Bill & Print</span>
                    <kbd style="background:var(--bg-card);padding:2px 6px;border-radius:4px;border:1px solid var(--border-color);font-weight:700;">F9</kbd>
                </div>
                <div style="background:var(--bg-body);padding:8px 12px;border-radius:6px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Close Active Popup</span>
                    <kbd style="background:var(--bg-card);padding:2px 6px;border-radius:4px;border:1px solid var(--border-color);font-weight:700;">Esc</kbd>
                </div>
                <div style="background:var(--bg-body);padding:8px 12px;border-radius:6px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Hardware Barcode Gun</span>
                    <span style="font-size:11px;color:var(--success);font-weight:600;">Auto-Detect</span>
                </div>
                <div style="background:var(--bg-body);padding:8px 12px;border-radius:6px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Dark / Light Mode</span>
                    <span style="font-size:11px;color:var(--text-muted);font-weight:600;">🌙 in Topbar</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('shortcutsModal')">Close</button>
        </div>
    </div>
</div>

<!-- Floating Shortcuts Guide Trigger -->
<div class="shortcuts-floating-trigger no-print" style="position:fixed;bottom:20px;right:20px;z-index:95;">
    <button class="btn btn-sm btn-secondary" onclick="openModal('shortcutsModal')" style="box-shadow:var(--shadow-lg);border-radius:20px;padding:6px 14px;background:var(--bg-card);display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
        <span>⌨️</span>
        <span>Shortcuts</span>
        <kbd style="font-size:10px;background:var(--border-light);padding:1px 5px;border-radius:4px;border:1px solid var(--border-color);font-weight:700;">F1</kbd>
    </button>
</div>

<!-- Core Frontend Scripts -->
<script src="<?= $baseURL ?>/assets/js/app.js"></script>
</body>
</html>
