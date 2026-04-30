<?php
$pageTitle = htmlspecialchars($camp['name']) . ' - 情報確認';
$appName = '合宿申し込み';

ob_start();
?>

<div class="card shadow">
    <div class="card-body p-4">
        <h2 class="text-center mb-4"><?= htmlspecialchars($camp['name']) ?></h2>

        <h5 class="mb-3">以下の情報で申し込みを行います</h5>
        <p class="text-muted mb-4">内容に間違いがないか確認してください。</p>

        <?php if ($hasApplied): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            既にこの合宿に申し込み済みです。再度申し込むと、前の申し込み内容が上書きされます。
        </div>
        <?php endif; ?>

        <div class="card bg-light mb-4">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="bi bi-check-circle text-success"></i>
                    この情報で間違いありませんか？
                </h6>

                <table class="table table-borderless mb-0" id="infoDisplayTable">
                    <tbody>
                        <tr>
                            <th width="120">名前:</th>
                            <td id="dispName"><?= htmlspecialchars($member['name_kanji']) ?></td>
                        </tr>
                        <tr>
                            <th>学籍番号:</th>
                            <td><?= htmlspecialchars($member['student_id']) ?></td>
                        </tr>
                        <tr>
                            <th>学年:</th>
                            <td id="dispGrade"><?= htmlspecialchars($member['grade']) ?>年</td>
                        </tr>
                        <tr>
                            <th>性別:</th>
                            <td id="dispGender"><?= $member['gender'] === 'male' ? '男性' : '女性' ?></td>
                        </tr>
                        <tr>
                            <th>住所:</th>
                            <td id="dispAddress"><?= htmlspecialchars($member['address']) ?></td>
                        </tr>
                        <tr>
                            <th>アレルギー:</th>
                            <td id="dispAllergy"><?= htmlspecialchars($member['allergy'] ?? '') ?: '<span class="text-muted">なし</span>' ?></td>
                        </tr>
                        <tr>
                            <th>LINE名:</th>
                            <td id="dispLineName"><?= htmlspecialchars($member['line_name']) ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-warning mb-0 mt-3">
                    <small>
                        <i class="bi bi-exclamation-triangle"></i>
                        情報に誤りがある場合は、
                        <button type="button" class="btn btn-sm btn-warning py-0 px-2 ms-1" onclick="toggleEditForm()">
                            <i class="bi bi-pencil"></i> その場で修正する
                        </button>
                    </small>
                </div>

                <!-- 編集フォーム -->
                <div id="editFormSection" style="display:none;" class="mt-3 border-top pt-3">
                    <h6 class="mb-3 fw-bold"><i class="bi bi-pencil-square text-warning"></i> 情報を修正</h6>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">名前（漢字）</label>
                        <input type="text" class="form-control form-control-sm" id="editNameKanji">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">学年</label>
                            <select class="form-select form-select-sm" id="editGrade">
                                <option value="1">1年</option>
                                <option value="2">2年</option>
                                <option value="3">3年</option>
                                <option value="4">4年</option>
                                <option value="OB">OB</option>
                                <option value="OG">OG</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">性別</label>
                            <select class="form-select form-select-sm" id="editGender">
                                <option value="male">男性</option>
                                <option value="female">女性</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">住所</label>
                        <input type="text" class="form-control form-control-sm" id="editAddress">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">アレルギー</label>
                        <textarea class="form-control form-control-sm" id="editAllergy" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">LINE名</label>
                        <input type="text" class="form-control form-control-sm" id="editLineName">
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        修正内容はこの合宿の参加者名簿に反映されます。会員名簿は幹事が確認後に更新します。
                    </small>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <?php
            $backUrl = !empty($_SESSION['member_authenticated'])
                ? '/member/home'
                : "/apply/{$token}";
            ?>
            <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> 戻る
            </a>
            <button type="button" class="btn btn-primary flex-grow-1" onclick="goToSchedule()">
                情報に間違いなし <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

<script>
const origValues = {
    name_kanji: <?= json_encode($member['name_kanji']) ?>,
    grade:      <?= json_encode($member['grade']) ?>,
    gender:     <?= json_encode($member['gender']) ?>,
    address:    <?= json_encode($member['address']) ?>,
    allergy:    <?= json_encode($member['allergy'] ?? '') ?>,
    line_name:  <?= json_encode($member['line_name']) ?>,
};

function toggleEditForm() {
    const section = document.getElementById('editFormSection');
    if (section.style.display === 'none') {
        document.getElementById('editNameKanji').value = origValues.name_kanji;
        document.getElementById('editGrade').value     = origValues.grade;
        document.getElementById('editGender').value    = origValues.gender;
        document.getElementById('editAddress').value   = origValues.address;
        document.getElementById('editAllergy').value   = origValues.allergy;
        document.getElementById('editLineName').value  = origValues.line_name;
        section.style.display = 'block';
        section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        section.style.display = 'none';
    }
}

function goToSchedule() {
    const section = document.getElementById('editFormSection');
    const isEditOpen = section.style.display !== 'none';

    let url = `/apply/<?= $token ?>/schedule?member_id=<?= $member['id'] ?>`;

    if (isEditOpen) {
        const editedName     = document.getElementById('editNameKanji').value.trim();
        const editedGrade    = document.getElementById('editGrade').value;
        const editedGender   = document.getElementById('editGender').value;
        const editedAddress  = document.getElementById('editAddress').value.trim();
        const editedAllergy  = document.getElementById('editAllergy').value.trim();
        const editedLineName = document.getElementById('editLineName').value.trim();

        const changed = (
            editedName     !== origValues.name_kanji ||
            editedGrade    !== origValues.grade      ||
            editedGender   !== origValues.gender     ||
            editedAddress  !== origValues.address    ||
            editedAllergy  !== origValues.allergy    ||
            editedLineName !== origValues.line_name
        );

        if (changed) {
            const params = new URLSearchParams({
                info_edited:       '1',
                edited_name_kanji: editedName,
                edited_grade:      editedGrade,
                edited_gender:     editedGender,
                edited_address:    editedAddress,
                edited_allergy:    editedAllergy,
                edited_line_name:  editedLineName,
            });
            url += '&' + params.toString();
        }
    }

    window.location.href = url;
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/public.php';
?>
