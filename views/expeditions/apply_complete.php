<?php
// 申し込み完了ページ
// 変数: $expedition, $member, $participant
?>
<div class="card shadow">
    <div class="card-body p-4 text-center">
        <div class="mb-4">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        </div>
        <h2 class="mb-3">申し込みを受け付けました</h2>
        <div class="alert alert-success">
            <strong><?= htmlspecialchars($member['name_kanji'] ?? '') ?></strong> さんの申し込みを受け付けました。
        </div>
    </div>
</div>

<?php if ($expedition && $participant): ?>
<div class="card shadow mt-3">
    <div class="card-header bg-light">
        <strong>申し込み内容</strong>
    </div>
    <div class="card-body">
        <?php if ((int)($participant['status'] === 'waitlisted')): ?>
        <div class="alert alert-warning mb-3">
            <i class="bi bi-clock"></i>
            <strong>キャンセル待ち</strong>として登録されました。空きが出た場合に確定となります。
        </div>
        <?php else: ?>
        <div class="alert alert-success mb-3">
            <i class="bi bi-check-circle"></i>
            参加が<strong>確定</strong>しました。
        </div>
        <?php endif; ?>
        <h6 class="mb-3"><?= htmlspecialchars($expedition['name']) ?></h6>
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <th width="100">期間</th>
                <td>
                    <?= date('Y年n月j日', strtotime($expedition['start_date'])) ?> 〜
                    <?= date('Y年n月j日', strtotime($expedition['end_date'])) ?>
                </td>
            </tr>
            <tr>
                <th>前泊</th>
                <td><?= (int)$participant['pre_night'] ? 'あり' : 'なし' ?></td>
            </tr>
            <tr>
                <th>昼食</th>
                <td><?= (int)$participant['lunch'] ? '注文する' : '注文しない' ?></td>
            </tr>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="alert alert-warning mt-3">
    <h6><i class="bi bi-exclamation-triangle"></i> 変更・キャンセルについて</h6>
    <p class="mb-0">申し込み後の変更・キャンセルは幹事に連絡してください。</p>
</div>

<div class="text-center mt-3">
    <a href="/member/home" class="btn btn-outline-secondary">
        <i class="bi bi-house"></i> 会員ページへ戻る
    </a>
</div>
