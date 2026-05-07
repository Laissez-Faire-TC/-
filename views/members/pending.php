<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>新規入会者リスト</h1>
        <p class="text-muted mb-0">入会フォームから登録された新規会員の一覧です</p>
    </div>
    <a href="/members" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> 会員一覧に戻る
    </a>
</div>

<!-- 新規入会者件数 -->
<div class="card mb-4 border-info">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class="bi bi-person-plus-fill text-info fs-3 me-3"></i>
            <div>
                <h5 class="mb-1">新規入会者: <span id="pendingCount">0</span>件</h5>
                <p class="text-muted mb-0 small">入会フォームから登録された会員です</p>
            </div>
        </div>
    </div>
</div>

<!-- 新規入会者一覧 -->
<div id="pendingList">
    <div class="text-center py-5">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">読み込み中...</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadPendingMembers();
});

async function loadPendingMembers() {
    try {
        const res = await fetch('/index.php?route=api/members/pending');
        const data = await res.json();

        if (data.success) {
            renderPendingMembers(data.data.members);
            document.getElementById('pendingCount').textContent = data.data.count;
        }
    } catch (err) {
        console.error(err);
        document.getElementById('pendingList').innerHTML = `
            <div class="alert alert-danger">
                データの取得に失敗しました
            </div>
        `;
    }
}

function renderPendingMembers(members) {
    const container = document.getElementById('pendingList');

    if (members.length === 0) {
        container.innerHTML = `
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-person-check fs-1 mb-3 d-block"></i>
                    <p class="mb-0">新規入会者はいません</p>
                </div>
            </div>
        `;
        return;
    }

    container.innerHTML = members.map(m => `
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="card-title mb-2">${escapeHtml(m.name_kanji)} <small class="text-muted">(${escapeHtml(m.name_kana)})</small></h5>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <small class="text-muted d-block">学籍番号</small>
                                <code>${escapeHtml(m.student_id)}</code>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">学部・学科</small>
                                ${escapeHtml(m.faculty)} ${escapeHtml(m.department)}
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">学年・性別</small>
                                ${escapeHtml(m.grade)}年 / ${m.gender === 'male' ? '男性' : '女性'}
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">登録日</small>
                                ${formatDate(m.created_at)}
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-info" onclick="showDetail(${m.id})">
                                <i class="bi bi-info-circle"></i> 詳細
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 詳細情報（折りたたみ） -->
                <div class="collapse mt-3 pt-3 border-top" id="detail-${m.id}">
                    <div class="row g-2 small">
                        <div class="col-md-4">
                            <strong>電話番号:</strong> ${escapeHtml(m.phone)}
                        </div>
                        <div class="col-md-4">
                            <strong>緊急連絡先:</strong> ${escapeHtml(m.emergency_contact)}
                        </div>
                        <div class="col-md-4">
                            <strong>生年月日:</strong> ${escapeHtml(m.birthdate)}
                        </div>
                        <div class="col-md-6">
                            <strong>住所:</strong> ${escapeHtml(m.address)}
                        </div>
                        <div class="col-md-6">
                            <strong>メールアドレス:</strong> ${escapeHtml(m.email || '-')}
                        </div>
                        <div class="col-md-4">
                            <strong>LINE名:</strong> ${escapeHtml(m.line_name)}
                        </div>
                        <div class="col-md-4">
                            <strong>アレルギー:</strong> ${escapeHtml(m.allergy || 'なし')}
                        </div>
                        <div class="col-md-4">
                            <strong>SNS投稿:</strong> ${m.sns_allowed == 1 ? '可' : '不可'}
                        </div>
                        <div class="col-md-6">
                            <strong>コート予約番号:</strong> ${escapeHtml(m.sports_registration_no || '-')}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function showDetail(id) {
    const detailEl = document.getElementById(`detail-${id}`);
    const collapse = new bootstrap.Collapse(detailEl, { toggle: true });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = ('0' + (date.getMonth() + 1)).slice(-2);
    const day = ('0' + date.getDate()).slice(-2);
    const hour = ('0' + date.getHours()).slice(-2);
    const min = ('0' + date.getMinutes()).slice(-2);
    return `${year}/${month}/${day} ${hour}:${min}`;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
