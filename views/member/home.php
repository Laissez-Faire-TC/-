<div class="pt-3 mb-4">
    <h4 class="fw-normal mb-1">こんにちは、<?= htmlspecialchars($memberName) ?> さん</h4>
    <p class="text-muted small mb-0">Laissez-Faire T.C. 会員ページ</p>
</div>

<?php if (!empty($pendingCollections)): ?>
<h6 class="text-uppercase text-muted fw-bold mb-3 small">振込確認フォーム</h6>
<div class="card border-danger mb-4">
    <div class="card-header bg-danger bg-opacity-10 border-danger">
        <i class="bi bi-cash-coin"></i> 振込確認をお願いします
    </div>
    <div class="card-body p-0">
        <?php foreach ($pendingCollections as $i => $pc):
            $isPastDeadline = $pc['deadline'] < date('Y-m-d');
        ?>
        <div class="d-flex justify-content-between align-items-center p-3 <?= $i < count($pendingCollections) - 1 ? 'border-bottom' : '' ?>">
            <div>
                <h6 class="mb-1"><?= htmlspecialchars($pc['camp_name']) ?></h6>
                <small class="text-muted">
                    <?= number_format((int)($pc['custom_amount'] ?? $pc['default_amount'])) ?>円
                    <?php if ($isPastDeadline): ?>
                    <span class="badge bg-danger ms-1">期限超過</span>
                    <?php else: ?>
                    ・期限 <?= date('n/j', strtotime($pc['deadline'])) ?>
                    <?php endif; ?>
                </small>
            </div>
            <a href="/member/collection/<?= (int)$pc['collection_id'] ?>" class="btn btn-danger btn-sm">振込確認</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($pendingFees)): ?>
<h6 class="text-uppercase text-muted fw-bold mb-3 small">入会金振込確認</h6>
<div class="card border-danger mb-4">
    <div class="card-header bg-danger bg-opacity-10 border-danger">
        <i class="bi bi-bank"></i> 入会金の振込確認をお願いします
    </div>
    <div class="card-body p-0">
        <?php foreach ($pendingFees as $i => $pf):
            $isPastDeadline = $pf['deadline'] < date('Y-m-d');
            $amount = $pf['effective_amount'] !== null ? number_format((int)$pf['effective_amount']) . '円' : '金額未設定';
        ?>
        <div class="d-flex justify-content-between align-items-center p-3 <?= $i < count($pendingFees) - 1 ? 'border-bottom' : '' ?>">
            <div>
                <h6 class="mb-1"><?= htmlspecialchars($pf['fee_name']) ?></h6>
                <small class="text-muted">
                    <?= $amount ?>
                    <?php if ($isPastDeadline): ?>
                    <span class="badge bg-danger ms-1">期限超過</span>
                    <?php else: ?>
                    ・期限 <?= date('n/j', strtotime($pf['deadline'])) ?>
                    <?php endif; ?>
                </small>
            </div>
            <a href="/member/membership-fee/<?= (int)$pf['membership_fee_id'] ?>" class="btn btn-danger btn-sm">振込確認</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($activeCamps)): ?>
<!-- 募集中の合宿 -->
<h6 class="text-uppercase text-muted fw-bold mb-3 small">募集中の合宿</h6>
<div class="card border-warning mb-4">
    <div class="card-header bg-warning bg-opacity-25 border-warning">
        <i class="bi bi-megaphone"></i> 申込受付中
    </div>
    <div class="card-body p-0">
        <?php foreach ($activeCamps as $i => $camp): ?>
        <div class="d-flex justify-content-between align-items-center p-3 <?= $i < count($activeCamps) - 1 ? 'border-bottom' : '' ?>">
            <div>
                <h6 class="mb-1"><?= htmlspecialchars($camp['camp_name']) ?></h6>
                <small class="text-muted">
                    <?= date('Y/n/j', strtotime($camp['start_date'])) ?> 〜 <?= date('n/j', strtotime($camp['end_date'])) ?>
                    <?php if ($camp['deadline']): ?>
                    &nbsp;・&nbsp;締切 <?= date('n/j', strtotime($camp['deadline'])) ?>
                    <?php endif; ?>
                </small>
            </div>
            <a href="/apply/<?= htmlspecialchars($camp['token']) ?>" class="btn btn-warning btn-sm">
                申し込む
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($activeEvents)): ?>
<!-- 募集中の企画 -->
<h6 class="text-uppercase text-muted fw-bold mb-3 small">募集中の企画・イベント</h6>
<div id="eventsSection">
<?php foreach ($activeEvents as $i => $ev):
    $count    = (int)$ev['application_count'];
    $capacity = $ev['capacity'] !== null ? (int)$ev['capacity'] : null;
    $isFull   = $capacity !== null && $count >= $capacity;
    $applied  = ($ev['my_status'] === 'submitted');
?>
<div class="card border-primary mb-3 shadow-sm">
    <div class="card-header bg-primary bg-opacity-10 border-primary d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($ev['title']) ?></span>
        <?php if ($capacity !== null): ?>
        <span class="badge <?= $isFull ? 'bg-danger' : ($count / $capacity >= 0.8 ? 'bg-warning text-dark' : 'bg-primary') ?>">
            <?= $count ?>/<?= $capacity ?>
        </span>
        <?php else: ?>
        <span class="badge bg-light text-dark border"><?= $count ?>人申込中</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <?php if ($ev['event_date']): ?>
            <div class="col-auto">
                <small class="text-muted"><i class="bi bi-calendar3"></i>
                <?= date('Y年n月j日', strtotime($ev['event_date'])) ?>
                <?php if ($ev['event_time']): ?>
                <?= date('G:i', strtotime($ev['event_time'])) ?>〜
                <?php endif; ?>
                </small>
            </div>
            <?php endif; ?>
            <?php if ($ev['location']): ?>
            <div class="col-auto">
                <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($ev['location']) ?></small>
            </div>
            <?php endif; ?>
            <?php if ((int)$ev['participation_fee'] > 0): ?>
            <div class="col-auto">
                <small class="text-muted"><i class="bi bi-cash"></i> <?= number_format((int)$ev['participation_fee']) ?>円</small>
            </div>
            <?php endif; ?>
        </div>
        <?php if ($ev['description']): ?>
        <p class="small mb-3"><?= nl2br(htmlspecialchars($ev['description'])) ?></p>
        <?php endif; ?>
        <div class="d-flex justify-content-end align-items-center gap-2">
            <?php
            $waitlisted = ($ev['my_status'] === 'waitlisted');
            $myWaitlistPos = (int)($ev['my_waitlist_position'] ?? 0);
            ?>
            <?php if ($applied): ?>
                <span class="badge bg-success">申込済</span>
                <button class="btn btn-sm btn-outline-danger"
                        onclick="cancelEvent(<?= (int)$ev['id'] ?>, this)">キャンセル</button>
            <?php elseif ($waitlisted): ?>
                <span class="badge bg-secondary">キャンセル待ち <?= $myWaitlistPos ?>番目</span>
                <button class="btn btn-sm btn-outline-danger"
                        onclick="cancelEvent(<?= (int)$ev['id'] ?>, this)">取り消す</button>
            <?php elseif ($isFull && $ev['allow_waitlist']): ?>
                <span class="text-muted small me-1">定員満員</span>
                <button class="btn btn-sm btn-outline-secondary"
                        onclick="applyEvent(<?= (int)$ev['id'] ?>, this)">キャンセル待ちで申し込む</button>
            <?php elseif ($isFull): ?>
                <span class="badge bg-danger">定員締め切り</span>
            <?php else: ?>
                <button class="btn btn-sm btn-primary"
                        onclick="applyEvent(<?= (int)$ev['id'] ?>, this)">申し込む</button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($activeCamps) && empty($activeEvents) && empty($pendingCollections) && empty($pendingFees)): ?>
<div class="card mb-4">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
        <p class="mt-2 mb-0">現在募集中の合宿・イベントはありません</p>
    </div>
</div>
<?php endif; ?>

<div class="text-muted small text-center mt-4">
    <p class="mb-0">ご不明な点は幹事長までご連絡ください</p>
</div>

<script>
async function applyEvent(eventId, btn) {
    btn.disabled = true;
    try {
        const res  = await fetch(`/api/member/events/${eventId}/apply`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error?.message || '申し込みに失敗しました');
            btn.disabled = false;
        }
    } catch (e) {
        alert('通信エラーが発生しました');
        btn.disabled = false;
    }
}

async function cancelEvent(eventId, btn) {
    if (!confirm('申し込みをキャンセルしますか？')) return;
    btn.disabled = true;
    try {
        const res  = await fetch(`/api/member/events/${eventId}/apply`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error?.message || 'キャンセルに失敗しました');
            btn.disabled = false;
        }
    } catch (e) {
        alert('通信エラーが発生しました');
        btn.disabled = false;
    }
}
</script>
