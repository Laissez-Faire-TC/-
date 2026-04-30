<div class="pt-3 mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/member/home">ホーム</a></li>
            <li class="breadcrumb-item active">登録情報の変更</li>
        </ol>
    </nav>
    <h4 class="fw-normal mb-1">登録情報の変更</h4>
    <p class="text-muted small mb-0">変更できる項目のみ編集できます。氏名・学籍番号等の変更は幹部にご連絡ください。</p>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> 情報を更新しました。変更内容は幹部に通知されます。
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div id="alertArea"></div>

<!-- 変更不可の情報（読み取り専用表示） -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-person"></i> 基本情報（変更不可）
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label text-muted small">名前（漢字）</label>
                <div class="form-control-plaintext ps-2 border rounded bg-light"><?= htmlspecialchars($member['name_kanji']) ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small">名前（カナ）</label>
                <div class="form-control-plaintext ps-2 border rounded bg-light"><?= htmlspecialchars($member['name_kana']) ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">学籍番号</label>
                <div class="form-control-plaintext ps-2 border rounded bg-light"><?= htmlspecialchars($member['student_id']) ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">学年</label>
                <div class="form-control-plaintext ps-2 border rounded bg-light"><?= htmlspecialchars($member['grade']) ?>年</div>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">性別</label>
                <div class="form-control-plaintext ps-2 border rounded bg-light"><?= $member['gender'] === 'male' ? '男性' : '女性' ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small">学部</label>
                <div class="form-control-plaintext ps-2 border rounded bg-light"><?= htmlspecialchars($member['faculty'] ?? '未設定') ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small">学科</label>
                <div class="form-control-plaintext ps-2 border rounded bg-light"><?= htmlspecialchars($member['department'] ?? '未設定') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- 変更可能な情報フォーム -->
<form id="profileForm">
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-pencil"></i> 変更可能な情報
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="phone" class="form-label">電話番号 <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           value="<?= htmlspecialchars($member['phone'] ?? '') ?>"
                           placeholder="例: 090-1234-5678" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">メールアドレス</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($member['email'] ?? '') ?>"
                           placeholder="例: taro@example.com">
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">住所 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="address" name="address"
                           value="<?= htmlspecialchars($member['address'] ?? '') ?>"
                           placeholder="例: 東京都新宿区西早稲田1-1-1" required>
                </div>
                <div class="col-12">
                    <label for="emergency_contact" class="form-label">緊急連絡先 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="emergency_contact" name="emergency_contact"
                           value="<?= htmlspecialchars($member['emergency_contact'] ?? '') ?>"
                           placeholder="例: 父 090-0000-0000" required>
                    <div class="form-text">続柄と電話番号を入力してください</div>
                </div>
                <div class="col-md-6">
                    <label for="line_name" class="form-label">LINE名</label>
                    <input type="text" class="form-control" id="line_name" name="line_name"
                           value="<?= htmlspecialchars($member['line_name'] ?? '') ?>"
                           placeholder="例: 山田太郎">
                </div>
                <div class="col-12">
                    <label for="allergy" class="form-label">アレルギー</label>
                    <input type="text" class="form-control" id="allergy" name="allergy"
                           value="<?= htmlspecialchars($member['allergy'] ?? '') ?>"
                           placeholder="例: 卵・乳製品（なければ空欄）">
                </div>
                <div class="col-12">
                    <label class="form-label">SNS掲載同意</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sns_allowed" id="sns_yes" value="1"
                                   <?= (int)($member['sns_allowed'] ?? 1) === 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sns_yes">同意する</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sns_allowed" id="sns_no" value="0"
                                   <?= (int)($member['sns_allowed'] ?? 1) === 0 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sns_no">同意しない</label>
                        </div>
                    </div>
                    <div class="form-text">活動記録や合宿写真等をSNSに掲載することへの同意</div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <a href="/member/home" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> ホームに戻る
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="bi bi-check-lg"></i> 変更を保存する
            </button>
        </div>
    </div>
</form>

<script>
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>保存中...';

    const form = e.target;
    const data = {
        phone:             form.phone.value.trim(),
        address:           form.address.value.trim(),
        emergency_contact: form.emergency_contact.value.trim(),
        email:             form.email.value.trim(),
        allergy:           form.allergy.value.trim(),
        line_name:         form.line_name.value.trim(),
        sns_allowed:       form.sns_allowed.value,
    };

    const alertArea = document.getElementById('alertArea');
    alertArea.innerHTML = '';

    try {
        const res  = await fetch('/api/member/profile', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        const json = await res.json();

        if (json.success) {
            window.location.href = '/member/profile?success=1';
        } else {
            alertArea.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${json.error?.message || '更新に失敗しました'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> 変更を保存する';
        }
    } catch (err) {
        alertArea.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> 通信エラーが発生しました</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> 変更を保存する';
    }
});
</script>
