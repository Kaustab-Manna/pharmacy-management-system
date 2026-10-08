<div class="page-header">
    <div class="page-title">
        <h1>Notifications & Alert System (Module 29)</h1>
        <p>Real-time automated warnings for low stocks, expired batches, near-expiry items, and pending prescriptions.</p>
    </div>
    <div class="page-actions">
        <form method="POST" action="<?= $baseURL ?>/notifications/mark-read">
            <button type="submit" class="btn btn-secondary">✓ Mark All as Read</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">🔔 Active System Alerts & Clinical Notifications</h3>
    </div>
    <div class="card-body" style="padding:1rem;">
        <?php if (empty($notifications)): ?>
            <p style="text-align:center;padding:2rem;color:var(--text-muted);">No notifications or warnings at this time.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($notifications as $n): ?>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:12px 16px;border-radius:8px;border:1px solid var(--border-color);background:<?= $n['is_read'] ? 'var(--bg-card)' : 'var(--bg-body)' ?>;">
                        <div style="display:flex;gap:12px;align-items:flex-start;">
                            <div style="font-size:1.5rem;">
                                <?php if ($n['severity'] === 'danger'): ?>
                                    🚫
                                <?php elseif ($n['severity'] === 'warning'): ?>
                                    ⚠️
                                <?php elseif ($n['severity'] === 'success'): ?>
                                    ✅
                                <?php else: ?>
                                    ℹ️
                                <?php endif; ?>
                            </div>
                            <div>
                                <strong style="font-size:0.95rem;color:var(--text-main);"><?= htmlspecialchars($n['title']) ?></strong>
                                <div style="font-size:12px;color:var(--text-muted);margin:3px 0 6px 0;"><?= htmlspecialchars($n['message']) ?></div>
                                <div style="font-size:10px;color:var(--text-light);"><?= htmlspecialchars($n['created_at']) ?></div>
                            </div>
                        </div>
                        <?php if (!empty($n['link'])): ?>
                            <a href="<?= $baseURL ?>/<?= ltrim($n['link'], '/') ?>" class="btn btn-sm btn-primary">Take Action &rarr;</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
