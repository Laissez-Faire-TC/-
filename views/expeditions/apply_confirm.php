<?php
// Step 2: 情報確認 + 前泊・昼食オプション
// 変数: $expedition, $token, $member, $alreadyApplied
// 定員関連変数: $isFull, $remaining, $waitlistCount, $capacityForGender, $confirmedCount, $isMale
?>
<?php if ($alreadyApplied): ?>
<div class="card shadow">
    <div class="card-body p-4 text-center">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
        <h4 class="mt-3">すでに申し込み済みです</h4>
        <p class="text-muted"><?= htmlspecialchars($member['name_kanji']) ?> さんはこの遠征に申し込み済みです。</p>
        <a href="/member/home" class="btn btn-outline-secondary">
            <i class="bi bi-house"></i> 会員ページへ戻る
        </a>
    </div>
</div>
<?php return; endif; ?>

<div class="card shadow mb-3">
    <div class="card-body p-4">
        <h2 class="text-center mb-2"><?= htmlspecialchars($expedition['name']) ?></h2>
        <p class="text-center text-muted mb-0">
            <?= date('Y年n月j日', strtotime($expedition['start_date'])) ?> 〜
            <?= date('Y年n月j日', strtotime($expedition['end_date'])) ?>
        </p>
    </div>
</div>

<!-- 登録情報確認 -->
<div class="card shadow mb-3">
    <div class="card-body p-4">
        <h5 class="mb-3">
            <i class="bi bi-person-check text-success"></i> 登録情報の確認
        </h5>
        <p class="text-muted small mb-3">内容に間違いがないか確認してください。</p>

        <table class="table table-borderless mb-0" id="infoDisplayTable">
            <tbody>
                <tr>
                    <th width="110">名前</th>
                    <td><strong><?= htmlspecialchars($member['name_kanji']) ?></strong>
                        <span class="text-muted small ms-2"><?= htmlspecialchars($member['name_kana'] ?? '') ?></span>
                    </td>
                </tr>
                <tr>
                    <th>学籍番号</th>
                    <td><?= htmlspecialchars($member['student_id']) ?></td>
                </tr>
                <tr>
                    <th>学年</th>
                    <td id="dispGrade"><?= htmlspecialchars($member['grade']) ?>年</td>
                </tr>
                <tr>
                    <th>性別</th>
                    <td><?= ($member['gender'] === '男' || $member['gender'] === 'male') ? '男性' : '女性' ?></td>
                </tr>
                <tr>
                    <th>住所</th>
                    <td id="dispAddress"><?= htmlspecialchars($member['address'] ?? '') ?: '<span class="text-muted">未登録</span>' ?></td>
                </tr>
                <tr>
                    <th>アレルギー</th>
                    <td id="dispAllergy"><?= htmlspecialchars($member['allergy'] ?? '') ?: '<span class="text-muted">なし</span>' ?></td>
                </tr>
                <tr>
                    <th>LINE名</th>
                    <td id="dispLineName"><?= htmlspecialchars($member['line_name'] ?? '') ?: '<span class="text-muted">未登録</span>' ?></td>
                </tr>
            </tbody>
        </table>

        <div class="alert alert-warning mb-0 mt-3">
            <small>
                <i class="bi bi-exclamation-triangle"></i>
                情報に誤りがある場合は
                <button type="button" class="btn btn-sm btn-warning py-0 px-2 ms-1" onclick="toggleEditForm()">
                    <i class="bi bi-pencil"></i> その場で修正する
                </button>
            </small>
        </div>

        <!-- 編集フォーム -->
        <div id="editFormSection" style="display:none;" class="mt-3 border-top pt-3">
            <h6 class="mb-3 fw-bold"><i class="bi bi-pencil-square text-warning"></i> 情報を修正</h6>
            <div class="mb-2">
                <label class="form-label small fw-bold">住所</label>
                <input type="text" class="form-control form-control-sm" id="editAddress"
                       placeholder="現住所を入力">
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">アレルギー</label>
                <textarea class="form-control form-control-sm" id="editAllergy" rows="2"
                          placeholder="なければ空欄"></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">LINE名</label>
                <input type="text" class="form-control form-control-sm" id="editLineName">
            </div>
            <small class="text-muted">
                <i class="bi bi-info-circle"></i>
                修正内容は会員情報に反映されます。
            </small>
        </div>
    </div>
</div>

<!-- 定員・申込期限情報 -->
<?php if (!empty($expedition['deadline'])): ?>
<div class="alert alert-info mb-3">
    <i class="bi bi-clock"></i>
    申込期限: <strong><?= date('Y年n月j日', strtotime($expedition['deadline'])) ?></strong>
</div>
<?php endif; ?>

<?php if ($capacityForGender !== null): ?>
<div class="card shadow mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-bold">定員状況（<?= $isMale ? '男性' : '女性' ?>）</span>
            <span class="small"><?= $confirmedCount ?> / <?= $capacityForGender ?> 人</span>
        </div>
        <div class="progress mb-2" style="height:8px;">
            <div class="progress-bar <?= $isFull ? 'bg-danger' : 'bg-success' ?>"
                 style="width:<?= min(100, round($confirmedCount / $capacityForGender * 100)) ?>%"></div>
        </div>
        <?php if ($isFull): ?>
        <div class="alert alert-warning mb-0 py-2 small">
            <i class="bi bi-exclamation-triangle"></i>
            定員に達しています。申し込むと<strong>キャンセル待ち</strong>になります。
            （現在 <?= $waitlistCount ?> 人待ち）
        </div>
        <?php elseif ($remaining <= 3): ?>
        <div class="text-warning small"><i class="bi bi-exclamation-triangle"></i> 残り<?= $remaining ?>枠</div>
        <?php else: ?>
        <div class="text-success small"><i class="bi bi-check-circle"></i> 残り<?= $remaining ?>枠</div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- 前泊・昼食オプション -->
<div class="card shadow mb-3">
    <div class="card-body p-4">
        <h5 class="mb-3">
            <i class="bi bi-list-check text-primary"></i> オプション選択
        </h5>

        <?php if ((int)($expedition['pre_night_fee'] ?? 0) > 0): ?>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="optPreNight" checked>
            <label class="form-check-label" for="optPreNight">
                <strong>前泊する</strong>
                <span class="badge bg-secondary ms-2">¥<?= number_format((int)$expedition['pre_night_fee']) ?></span>
            </label>
            <div class="text-muted small">前日から宿泊します（基本的にあり）</div>
        </div>
        <?php else: ?>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="optPreNight" checked>
            <label class="form-check-label" for="optPreNight">
                <strong>前泊する</strong>
            </label>
            <div class="text-muted small">前日から宿泊します（基本的にあり）</div>
        </div>
        <?php endif; ?>

        <?php if ((int)($expedition['lunch_fee'] ?? 0) > 0): ?>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="optLunch">
            <label class="form-check-label" for="optLunch">
                <strong>昼食を注文する</strong>
                <span class="badge bg-secondary ms-2">¥<?= number_format((int)$expedition['lunch_fee']) ?></span>
            </label>
        </div>
        <?php else: ?>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="optLunch">
            <label class="form-check-label" for="optLunch">
                <strong>昼食を注文する</strong>
            </label>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 車関連オプション -->
<div class="card shadow mb-3">
    <div class="card-body p-4">
        <h5 class="mb-3">
            <i class="bi bi-car-front text-info"></i> 車について
        </h5>

        <div class="mb-3">
            <label class="form-label fw-bold">車に乗りますか？</label>
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="isJoiningCar" id="joinCarYes" value="1" checked onchange="updateCarOptions()">
                    <label class="form-check-label" for="joinCarYes">乗る</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="isJoiningCar" id="joinCarNo" value="0" onchange="updateCarOptions()">
                    <label class="form-check-label" for="joinCarNo">乗らない（現地集合など）</label>
                </div>
            </div>
        </div>

        <div id="carDetailOptions">
            <div class="mb-3">
                <label class="form-label fw-bold">運転できますか？</label>
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="driverType" id="driverMain" value="driver" onchange="updateDriverOptions()">
                        <label class="form-check-label" for="driverMain">
                            <strong>メインドライバー</strong>
                            <span class="text-muted small">（主に運転担当）</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="driverType" id="driverSub" value="sub_driver" onchange="updateDriverOptions()">
                        <label class="form-check-label" for="driverSub">
                            <strong>サブドライバー</strong>
                            <span class="text-muted small">（補助的に運転可能）</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="driverType" id="driverNone" value="none" checked onchange="updateDriverOptions()">
                        <label class="form-check-label" for="driverNone">運転不可</label>
                    </div>
                </div>
            </div>

            <div id="driverDetails" style="display:none;">
                <div class="mb-3">
                    <label class="form-label fw-bold">タイムズカーシェア 利用者番号</label>
                    <input type="text" class="form-control form-control-sm" id="timescarNumber"
                           placeholder="例: TW1234567">
                    <div class="form-text">タイムズカーシェアの会員番号を入力してください</div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="canBookCar">
                        <label class="form-check-label" for="canBookCar">
                            <strong>車の予約をする</strong>
                            <span class="text-muted small">（タイムズカーシェアで予約してもよい）</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label fw-bold">金曜日の授業は何限に終わりますか？</label>
                <select class="form-select form-select-sm" id="fridayLastClass">
                    <option value="0">授業なし（または早く終わる）</option>
                    <option value="1">1限まで</option>
                    <option value="2">2限まで</option>
                    <option value="3">3限まで</option>
                    <option value="4">4限まで</option>
                    <option value="5">5限まで</option>
                    <option value="6">6限まで</option>
                </select>
                <div class="form-text">車割の往路グループ分けに使用します</div>
            </div>
        </div>
    </div>
</div>

<!-- 送信ボタン -->
<div id="submitError" class="alert alert-danger d-none mb-3"></div>

<div class="d-flex gap-2">
    <a href="/apply/expedition/<?= htmlspecialchars($token) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> 戻る
    </a>
    <button type="button" class="btn btn-primary flex-grow-1" id="submitBtn" onclick="submitApply()">
        この内容で申し込む <i class="bi bi-check2-circle"></i>
    </button>
</div>

<script>
const applyToken = <?= json_encode($token) ?>;
const origValues = {
    address:   <?= json_encode($member['address']   ?? '') ?>,
    allergy:   <?= json_encode($member['allergy']   ?? '') ?>,
    line_name: <?= json_encode($member['line_name'] ?? '') ?>,
};

function updateCarOptions() {
    const joining = document.querySelector('input[name="isJoiningCar"]:checked').value === '1';
    document.getElementById('carDetailOptions').style.display = joining ? 'block' : 'none';
}

function updateDriverOptions() {
    const driverType = document.querySelector('input[name="driverType"]:checked').value;
    const isDriver   = driverType === 'driver' || driverType === 'sub_driver';
    document.getElementById('driverDetails').style.display = isDriver ? 'block' : 'none';
}

function toggleEditForm() {
    const section = document.getElementById('editFormSection');
    if (section.style.display === 'none') {
        document.getElementById('editAddress').value  = origValues.address;
        document.getElementById('editAllergy').value  = origValues.allergy;
        document.getElementById('editLineName').value = origValues.line_name;
        section.style.display = 'block';
        section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        section.style.display = 'none';
    }
}

async function submitApply() {
    const btn    = document.getElementById('submitBtn');
    const errEl  = document.getElementById('submitError');
    errEl.classList.add('d-none');

    const preNight = document.getElementById('optPreNight').checked;
    const lunch    = document.getElementById('optLunch').checked;

    const section     = document.getElementById('editFormSection');
    const isEditOpen  = section.style.display !== 'none';
    let infoEdited    = false;
    let editedAddress  = '';
    let editedAllergy  = '';
    let editedLineName = '';

    if (isEditOpen) {
        editedAddress  = document.getElementById('editAddress').value.trim();
        editedAllergy  = document.getElementById('editAllergy').value.trim();
        editedLineName = document.getElementById('editLineName').value.trim();

        infoEdited = (
            editedAddress  !== origValues.address   ||
            editedAllergy  !== origValues.allergy   ||
            editedLineName !== origValues.line_name
        );
    }

    // 車関連
    const isJoiningCar = document.querySelector('input[name="isJoiningCar"]:checked').value;
    const driverType   = document.querySelector('input[name="driverType"]:checked').value;
    const timescarNum  = document.getElementById('timescarNumber').value.trim();
    const canBookCar   = document.getElementById('canBookCar').checked;
    const fridayClass  = isJoiningCar === '1'
        ? parseInt(document.getElementById('fridayLastClass').value, 10)
        : null;

    const body = {
        pre_night:          preNight,
        lunch:              lunch,
        info_edited:        infoEdited ? 1 : 0,
        edited_address:     editedAddress,
        edited_allergy:     editedAllergy,
        edited_line_name:   editedLineName,
        is_joining_car:     parseInt(isJoiningCar, 10),
        driver_type:        driverType,
        timescar_number:    timescarNum,
        can_book_car:       canBookCar ? 1 : 0,
        friday_last_class:  fridayClass,
    };

    btn.disabled    = true;
    btn.textContent = '申し込み中...';

    try {
        const res    = await fetch(`/api/apply/expedition/${applyToken}`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(body),
        });
        const result = await res.json();

        if (result.success) {
            window.location.href = `/apply/expedition/${applyToken}/complete`;
        } else {
            errEl.textContent = result.error?.message || '申し込みに失敗しました';
            errEl.classList.remove('d-none');
            btn.disabled    = false;
            btn.innerHTML   = 'この内容で申し込む <i class="bi bi-check2-circle"></i>';
        }
    } catch (err) {
        errEl.textContent = '通信エラーが発生しました';
        errEl.classList.remove('d-none');
        btn.disabled    = false;
        btn.innerHTML   = 'この内容で申し込む <i class="bi bi-check2-circle"></i>';
    }
}
</script>
