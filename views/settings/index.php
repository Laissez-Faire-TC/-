<div class="container-fluid py-4">
    <h1><i class="bi bi-gear"></i> システム設定</h1>
    <hr class="mb-4">

    <div class="row g-4">

        <!-- AI設定 -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-robot"></i> AIチャットボット設定</strong>
                    <button class="btn btn-primary btn-sm" id="saveAiBtn" onclick="saveAiSettings()">保存</button>
                </div>
                <div class="card-body">
                    <div id="aiAlert" class="alert d-none mb-3"></div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">使用モデル</label>
                        <input type="text" class="form-control font-monospace" id="aiModel" placeholder="例: claude-haiku-4-5-20251001">
                        <div class="form-text">Anthropicのモデル名をそのまま入力してください。現在利用可能なモデルは <a href="https://docs.anthropic.com/ja/docs/about-claude/models" target="_blank">Anthropicの公式ドキュメント</a> で確認できます。</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">最大トークン数</label>
                        <input type="number" class="form-control" id="aiMaxTokens" min="256" max="8192" step="256">
                        <div class="form-text">回答の最大長さです（256〜8192）。通常は1024で十分です。</div>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="aiEnabled">
                        <label class="form-check-label" for="aiEnabled">チャットボットを有効にする</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- パスワード変更 -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong><i class="bi bi-key"></i> 幹部パスワード変更</strong>
                </div>
                <div class="card-body">
                    <div id="passwordAlert" class="alert d-none mb-3"></div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">現在のパスワード</label>
                        <input type="password" class="form-control" id="currentPassword" placeholder="現在のパスワード">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">新しいパスワード</label>
                        <input type="password" class="form-control" id="newPassword" placeholder="6文字以上">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">新しいパスワード（確認）</label>
                        <input type="password" class="form-control" id="confirmPassword" placeholder="もう一度入力">
                    </div>
                    <button class="btn btn-danger" id="changePasswordBtn" onclick="changePassword()">
                        <i class="bi bi-shield-lock"></i> パスワードを変更する
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadAiSettings);

async function loadAiSettings() {
    try {
        const res  = await fetch('/api/system-settings');
        const json = await res.json();
        if (!json.success) return;
        const d = json.data;
        document.getElementById('aiModel').value     = d.ai_model;
        document.getElementById('aiMaxTokens').value = d.ai_max_tokens;
        document.getElementById('aiEnabled').checked = d.ai_enabled;
    } catch (e) { console.error(e); }
}

async function saveAiSettings() {
    const btn = document.getElementById('saveAiBtn');
    btn.disabled = true;
    try {
        const res  = await fetch('/api/system-settings', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ai_model:      document.getElementById('aiModel').value,
                ai_max_tokens: parseInt(document.getElementById('aiMaxTokens').value),
                ai_enabled:    document.getElementById('aiEnabled').checked ? 1 : 0,
            })
        });
        const json = await res.json();
        showAlert('aiAlert', json.success ? 'success' : 'danger', json.message || json.error?.message || 'エラー');
    } catch (e) {
        showAlert('aiAlert', 'danger', '通信エラーが発生しました');
    } finally {
        btn.disabled = false;
    }
}

async function changePassword() {
    const btn     = document.getElementById('changePasswordBtn');
    const current = document.getElementById('currentPassword').value;
    const newPw   = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;

    if (!current || !newPw || !confirm) {
        showAlert('passwordAlert', 'danger', 'すべての項目を入力してください');
        return;
    }

    btn.disabled = true;
    try {
        const res  = await fetch('/api/auth/change-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ current_password: current, new_password: newPw, confirm_password: confirm })
        });
        const json = await res.json();
        if (json.success) {
            showAlert('passwordAlert', 'success', 'パスワードを変更しました');
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value     = '';
            document.getElementById('confirmPassword').value = '';
        } else {
            showAlert('passwordAlert', 'danger', json.error?.message || 'エラーが発生しました');
        }
    } catch (e) {
        showAlert('passwordAlert', 'danger', '通信エラーが発生しました');
    } finally {
        btn.disabled = false;
    }
}

function showAlert(id, type, msg) {
    const el = document.getElementById(id);
    el.className = `alert alert-${type}`;
    el.textContent = msg;
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), 4000);
}
</script>
