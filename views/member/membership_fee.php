<?php
$isPastDeadline = $fee['deadline'] < date('Y-m-d');
$isSubmitted    = (int)$item['submitted'] === 1;
$displayAmount  = $effectiveAmount !== null ? number_format((int)$effectiveAmount) . '円' : '金額未設定';
?>
<div class="pt-3 mb-4">
    <a href="/member/home" class="text-decoration-none">&larr; ホームに戻る</a>
    <h4 class="mt-2 fw-normal">入会金振込確認フォーム</h4>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong><?= htmlspecialchars($fee['name'] ?? '') ?></strong>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-5 col-sm-4 text-muted">対象年度</dt>
            <dd class="col-7 col-sm-8"><?= htmlspecialchars($fee['academic_year'] ?? '') ?>年度</dd>

            <dt class="col-5 col-sm-4 text-muted">学年</dt>
            <dd class="col-7 col-sm-8"><?= htmlspecialchars($memberGrade) ?></dd>

            <dt class="col-5 col-sm-4 text-muted">振込金額</dt>
            <dd class="col-7 col-sm-8 fw-bold fs-5"><?= $displayAmount ?></dd>

            <dt class="col-5 col-sm-4 text-muted">入金期限</dt>
            <dd class="col-7 col-sm-8">
                <?= htmlspecialchars($fee['deadline']) ?>
                <?php if ($isPastDeadline && !$isSubmitted): ?>
                <span class="badge bg-danger ms-1">期限超過</span>
                <?php endif; ?>
            </dd>
        </dl>
    </div>
</div>

<?php if ($isSubmitted): ?>
<!-- 提出済み -->
<div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill fs-5"></i>
    <div>
        <strong>提出済みです</strong><br>
        <small class="text-muted">
            提出日時: <?= htmlspecialchars($item['submitted_at'] ?? '') ?>
            <?php if ($item['late_reason']): ?>
            &nbsp;・&nbsp;遅延理由あり
            <?php endif; ?>
        </small>
    </div>
</div>

<?php if ((int)$item['admin_confirmed'] === 1): ?>
<div class="alert alert-info">
    <i class="bi bi-bank"></i> 通帳確認済みです
</div>
<?php else: ?>
<div class="alert alert-warning">
    <i class="bi bi-clock"></i> 幹事による通帳確認待ちです
</div>
<?php endif; ?>

<?php else: ?>
<!-- 未提出フォーム -->
<?php if ($isPastDeadline): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>入金期限が過ぎています</strong><br>
    振り込みを完了後、下記フォームに遅延理由を入力して提出してください。
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form id="feeForm">

            <?php if ($isPastDeadline): ?>
            <div class="mb-3">
                <label for="lateReason" class="form-label">
                    入金遅れの理由 <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="lateReason" rows="3"
                          placeholder="遅延の理由を入力してください" required></textarea>
            </div>
            <?php endif; ?>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="transferredCheck">
                <label class="form-check-label fw-bold" for="transferredCheck">
                    振り込みを完了しました
                </label>
            </div>

            <div id="formError" class="alert alert-danger d-none"></div>

            <button type="submit" class="btn btn-danger w-100" id="submitBtn">
                提出する
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('feeForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const transferred = document.getElementById('transferredCheck').checked;
    const lateReasonEl = document.getElementById('lateReason');
    const lateReason = lateReasonEl ? lateReasonEl.value.trim() : '';
    const errorDiv = document.getElementById('formError');
    const submitBtn = document.getElementById('submitBtn');

    if (!transferred) {
        errorDiv.textContent = '振り込み完了のチェックが必要です';
        errorDiv.classList.remove('d-none');
        return;
    }

    if (lateReasonEl && !lateReason) {
        errorDiv.textContent = '入金遅れの理由を入力してください';
        errorDiv.classList.remove('d-none');
        return;
    }

    errorDiv.classList.add('d-none');
    submitBtn.disabled = true;
    submitBtn.textContent = '送信中...';

    try {
        const body = { transferred: '1' };
        if (lateReasonEl) {
            body.late_reason = lateReason;
        }

        const res = await fetch('/api/member/membership-fee-items/<?= (int)$item['id'] ?>/submit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });

        const data = await res.json();

        if (data.success) {
            location.reload();
        } else {
            errorDiv.textContent = data.error?.message || '送信に失敗しました';
            errorDiv.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.textContent = '提出する';
        }
    } catch (err) {
        errorDiv.textContent = '通信エラーが発生しました';
        errorDiv.classList.remove('d-none');
        submitBtn.disabled = false;
        submitBtn.textContent = '提出する';
    }
});
</script>
