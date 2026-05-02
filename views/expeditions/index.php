<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>遠征管理</h1>
    <button class="btn btn-primary" onclick="showCreateModal()">
        + 新規遠征作成
    </button>
</div>

<!-- 遠征一覧 -->
<div id="expeditionList" class="row">
    <div class="col-12 text-center py-5">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">読み込み中...</span>
        </div>
    </div>
</div>

<!-- 新規遠征作成モーダル -->
<div class="modal fade" id="expeditionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expeditionModalTitle">新規遠征作成</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expeditionForm">
                    <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">遠征名（イベント名） <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="expeditionName" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">開始日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expeditionStartDate" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">終了日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expeditionEndDate" required>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">費用設定</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">参加費（base_fee）</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="expeditionBaseFee" value="0" min="0">
                                <span class="input-group-text">円</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">前泊費用（pre_night_fee）</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="expeditionPreNightFee" value="0" min="0">
                                <span class="input-group-text">円</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">昼食費用（lunch_fee）</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="expeditionLunchFee" value="0" min="0">
                                <span class="input-group-text">円</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="createExpedition()">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- 削除確認モーダル -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">削除確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMessage">この遠征を削除してもよろしいですか？</p>
                <p class="text-danger small">※関連データもすべて削除されます。この操作は取り消せません。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-danger" id="deleteConfirmBtn">削除する</button>
            </div>
        </div>
    </div>
</div>

<script>
// モーダルインスタンス
let expeditionModal;
let deleteModal;
// 削除対象ID（削除確認モーダル用）
let pendingDeleteId = null;

document.addEventListener('DOMContentLoaded', () => {
    expeditionModal = new bootstrap.Modal(document.getElementById('expeditionModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    // 削除確認ボタンのイベント設定
    document.getElementById('deleteConfirmBtn').addEventListener('click', () => {
        if (pendingDeleteId !== null) {
            confirmDelete(pendingDeleteId);
        }
    });

    // 遠征一覧を読み込む
    loadExpeditions();
});

// 遠征一覧を取得して表示
async function loadExpeditions() {
    try {
        const res = await fetch('/index.php?route=api/expeditions');
        const data = await res.json();

        if (data.success) {
            renderExpeditions(data.data);
        } else {
            showExpeditionError('遠征一覧の取得に失敗しました');
        }
    } catch (err) {
        console.error(err);
        showExpeditionError('通信エラーが発生しました');
    }
}

// 遠征一覧をレンダリング
function renderExpeditions(expeditions) {
    const container = document.getElementById('expeditionList');

    if (expeditions.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5 text-muted">
                <p>まだ遠征がありません</p>
                <button class="btn btn-primary" onclick="showCreateModal()">最初の遠征を作成</button>
            </div>
        `;
        return;
    }

    container.innerHTML = expeditions.map(expedition => `
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">${escapeHtml(expedition.name)}</h5>
                        <p class="card-text text-muted mb-0">
                            ${expedition.start_date} ～ ${expedition.end_date}
                            <span class="badge bg-secondary ms-2">${expedition.participant_count}名</span>
                        </p>
                    </div>
                    <div>
                        <button class="btn btn-outline-danger btn-sm me-2"
                            onclick="deleteExpedition(${expedition.id}, '${escapeHtml(expedition.name).replace(/'/g, "\\'")}')">削除</button>
                        <a href="/index.php?route=expeditions/${expedition.id}" class="btn btn-primary btn-sm">詳細</a>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// エラーメッセージを表示
function showExpeditionError(message) {
    const container = document.getElementById('expeditionList');
    container.innerHTML = `
        <div class="col-12 text-center py-5 text-danger">
            <p>${escapeHtml(message)}</p>
        </div>
    `;
}

// 新規作成モーダルを表示
function showCreateModal() {
    document.getElementById('expeditionModalTitle').textContent = '新規遠征作成';
    document.getElementById('expeditionForm').reset();
    expeditionModal.show();
}

// 新規作成フォーム送信
async function createExpedition(e) {
    const data = {
        name: document.getElementById('expeditionName').value,
        start_date: document.getElementById('expeditionStartDate').value,
        end_date: document.getElementById('expeditionEndDate').value,
        base_fee: parseInt(document.getElementById('expeditionBaseFee').value) || 0,
        pre_night_fee: parseInt(document.getElementById('expeditionPreNightFee').value) || 0,
        lunch_fee: parseInt(document.getElementById('expeditionLunchFee').value) || 0,
    };

    try {
        const res = await fetch('/index.php?route=api/expeditions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (result.success) {
            expeditionModal.hide();
            loadExpeditions();
            showToast('遠征を作成しました');
        } else {
            alert(result.error?.message || '保存に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

// 削除確認→API呼び出し
async function deleteExpedition(id, name) {
    // 削除確認モーダルを表示
    pendingDeleteId = id;
    document.getElementById('deleteConfirmMessage').textContent = `「${name}」を削除してもよろしいですか？`;
    deleteModal.show();
}

// 削除APIを呼び出す（削除確認モーダルの「削除する」ボタンから呼ばれる）
async function confirmDelete(id) {
    try {
        const res = await fetch(`/index.php?route=api/expeditions/${id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();

        if (data.success) {
            deleteModal.hide();
            pendingDeleteId = null;
            loadExpeditions();
            showToast('遠征を削除しました');
        } else {
            alert(data.error?.message || '削除に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

// HTMLエスケープ
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
