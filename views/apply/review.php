<?php
$pageTitle = htmlspecialchars($camp['name']) . ' - 最終確認';
$appName = '合宿申し込み';

// 参加日程の表示用テキスト（管理システムと同じ）
$joinTimingLabels = [
    'outbound_bus' => '往路バス',
    'breakfast' => '朝食',
    'morning' => '午前イベント',
    'lunch' => '昼食',
    'afternoon' => '午後イベント',
    'dinner' => '夕食',
    'night' => '夜',
    'lodging' => '宿泊',
];

$leaveTimingLabels = [
    'before_breakfast' => '朝食前',
    'breakfast' => '朝食',
    'morning' => '午前イベント',
    'lunch' => '昼食',
    'afternoon' => '午後イベント',
    'dinner' => '夕食',
    'night' => '夜',
    'return_bus' => '復路バス',
];

$nights = max(0, $leaveDay - $joinDay);

ob_start();
?>

<div class="card shadow">
    <div class="card-body p-4">
        <h2 class="text-center mb-4"><?= htmlspecialchars($camp['name']) ?></h2>

        <h5 class="mb-3">以下の内容で申し込みます。よろしいですか？</h5>

        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <strong>申し込み者</strong>
                <?php if ($infoEdited): ?>
                <span class="badge bg-warning text-dark"><i class="bi bi-pencil"></i> 情報修正あり</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th width="120">名前:</th>
                        <td>
                            <?= htmlspecialchars($displayMember['name_kanji']) ?>
                            <?php if ($infoEdited && $displayMember['name_kanji'] !== $member['name_kanji']): ?>
                            <small class="text-muted ms-2"><s><?= htmlspecialchars($member['name_kanji']) ?></s></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>学年:</th>
                        <td>
                            <?= htmlspecialchars($displayMember['grade']) ?>年
                            <?php if ($infoEdited && $displayMember['grade'] !== $member['grade']): ?>
                            <small class="text-muted ms-2"><s><?= htmlspecialchars($member['grade']) ?>年</s></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>住所:</th>
                        <td>
                            <?= htmlspecialchars($displayMember['address']) ?>
                            <?php if ($infoEdited && $displayMember['address'] !== $member['address']): ?>
                            <small class="text-muted ms-2"><s><?= htmlspecialchars($member['address']) ?></s></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>アレルギー:</th>
                        <td>
                            <?= htmlspecialchars($displayMember['allergy'] ?? '') ?: '<span class="text-muted">なし</span>' ?>
                            <?php if ($infoEdited && ($displayMember['allergy'] ?? '') !== ($member['allergy'] ?? '')): ?>
                            <small class="text-muted ms-2"><s><?= htmlspecialchars($member['allergy'] ?? 'なし') ?></s></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>LINE名:</th>
                        <td>
                            <?= htmlspecialchars($displayMember['line_name']) ?>
                            <?php if ($infoEdited && $displayMember['line_name'] !== $member['line_name']): ?>
                            <small class="text-muted ms-2"><s><?= htmlspecialchars($member['line_name']) ?></s></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <?php if ($infoEdited): ?>
                <small class="text-muted"><i class="bi bi-info-circle"></i> 修正内容は参加者名簿に反映されます。会員名簿は幹事が確認後に更新します。</small>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>参加日程</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th width="120">参加期間:</th>
                        <td>
                            <?= $joinDay ?>日目 <?= $joinTimingLabels[$joinTiming] ?? $joinTiming ?>
                            〜
                            <?= $leaveDay ?>日目 <?= $leaveTimingLabels[$leaveTiming] ?? $leaveTiming ?>
                        </td>
                    </tr>
                    <tr>
                        <th>宿泊数:</th>
                        <td><?= $nights ?>泊</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <strong>交通手段</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th width="120">往路バス:</th>
                        <td>
                            <?= $useOutboundBus ? '<span class="badge bg-success">利用する</span>' : '<span class="badge bg-secondary">利用しない</span>' ?>
                        </td>
                    </tr>
                    <tr>
                        <th>復路バス:</th>
                        <td>
                            <?= $useReturnBus ? '<span class="badge bg-success">利用する</span>' : '<span class="badge bg-secondary">利用しない</span>' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <strong>備考</strong>
            </div>
            <div class="card-body">
                <textarea id="noteInput" class="form-control" rows="3"
                          placeholder="幹事への連絡事項があれば入力してください（任意）"></textarea>
            </div>
        </div>

        <div id="errorMessage" class="alert alert-danger d-none"></div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> 戻る
            </button>
            <button type="button" class="btn btn-primary flex-grow-1" onclick="submitApplication()" id="submitButton">
                <i class="bi bi-check-circle"></i> 申し込む
            </button>
        </div>
    </div>
</div>

<script>
async function submitApplication() {
    const button = document.getElementById('submitButton');
    const errorDiv = document.getElementById('errorMessage');

    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>送信中...';

    const data = {
        member_id: <?= $memberId ?>,
        join_day: <?= $joinDay ?>,
        join_timing: '<?= $joinTiming ?>',
        leave_day: <?= $leaveDay ?>,
        leave_timing: '<?= $leaveTiming ?>',
        use_outbound_bus: <?= $useOutboundBus ? 1 : 0 ?>,
        use_return_bus: <?= $useReturnBus ? 1 : 0 ?>,
        info_edited: <?= $infoEdited ? 1 : 0 ?>,
        edited_name_kanji:  <?= json_encode($editedNameKanji) ?>,
        edited_grade:       <?= json_encode($editedGrade) ?>,
        edited_gender:      <?= json_encode($editedGender) ?>,
        edited_faculty:     <?= json_encode($editedFaculty) ?>,
        edited_department:  <?= json_encode($editedDepartment) ?>,
        edited_address:     <?= json_encode($editedAddress) ?>,
        edited_allergy:     <?= json_encode($editedAllergy) ?>,
        edited_line_name:   <?= json_encode($editedLineName) ?>,
        note:               document.getElementById('noteInput').value.trim() || null,
    };

    try {
        const response = await fetch('/apply/<?= $token ?>/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            window.location.href = `/apply/<?= $token ?>/complete?member_id=<?= $memberId ?>`;
        } else {
            errorDiv.textContent = result.error?.message || '申し込みに失敗しました';
            errorDiv.classList.remove('d-none');
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-check-circle"></i> 申し込む';
        }
    } catch (error) {
        console.error('Submit error:', error);
        errorDiv.textContent = '通信エラーが発生しました';
        errorDiv.classList.remove('d-none');
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-check-circle"></i> 申し込む';
    }
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/public.php';
?>
