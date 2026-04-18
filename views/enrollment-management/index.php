<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">入会管理</h1>
</div>

<!-- タブナビゲーション -->
<ul class="nav nav-tabs mb-4" id="enrollTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabYears">
            <i class="bi bi-calendar3"></i> 入会フォーム
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabFees">
            <i class="bi bi-bank"></i> 入会金管理
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabNewMembers">
            <i class="bi bi-person-plus"></i> 新規入会者リスト
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- 入会フォームタブ -->
    <div class="tab-pane fade show active" id="tabYears">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div>
            <button class="btn btn-outline-secondary btn-sm" onclick="showCreateYearModal()">
                <i class="bi bi-plus-circle"></i> 新年度を追加
            </button>
        </div>
        <div id="yearCards">
            <div class="text-center py-5"><div class="spinner-border spinner-border-sm" role="status"></div></div>
        </div>
    </div>

    <!-- 入会金管理タブ -->
    <div class="tab-pane fade" id="tabFees">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div>
            <button class="btn btn-primary btn-sm" onclick="showCreateFeeModal()">
                <i class="bi bi-plus-circle"></i> 新規作成
            </button>
        </div>

        <div id="feeList">読み込み中...</div>
        <div id="feeDetail" class="d-none mt-4"></div>
    </div>

    <!-- 新規入会者リストタブ -->
    <div class="tab-pane fade" id="tabNewMembers">
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
        <div id="newMemberList">
            <div class="text-center py-5">
                <div class="spinner-border" role="status"></div>
            </div>
        </div>
    </div>

</div>

<!-- 新年度作成モーダル -->
<div class="modal fade" id="createYearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新年度を追加</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">年度 <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="newYear" min="2020" max="2100" required>
                    <small class="text-muted">例: 2026</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="createYear()">作成</button>
            </div>
        </div>
    </div>
</div>

<!-- 新規入会 開始モーダル -->
<div class="modal fade" id="enrollStartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enrollStartModalTitle">新規入会の受付を開始</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="enrollStartYear">
                <p class="text-muted small mb-3">新規入会フォームの受付期限を設定してください。</p>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person-plus text-success"></i> 新規入会フォーム 受付期限</label>
                    <input type="date" class="form-control" id="enrollDeadlineInput">
                    <small class="text-muted">未設定の場合は期限なし（手動停止まで受付）</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-success" onclick="saveEnrollStart()">受付開始</button>
            </div>
        </div>
    </div>
</div>

<!-- 新規入会 停止確認モーダル -->
<div class="modal fade" id="enrollStopModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新規入会の受付を停止</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="enrollStopYear">
                <p>新規入会フォームの受付を停止します。ポータルページのボタンが非表示になります。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-danger" onclick="saveEnrollStop()">停止する</button>
            </div>
        </div>
    </div>
</div>

<!-- 継続入会 開始モーダル -->
<div class="modal fade" id="renewStartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renewStartModalTitle">継続入会の受付を開始</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="renewStartYear">
                <p class="text-muted small mb-3">継続入会フォームの受付期限を設定してください。</p>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-arrow-repeat text-info"></i> 継続入会フォーム 受付期限</label>
                    <input type="date" class="form-control" id="renewDeadlineInput">
                    <small class="text-muted">未設定の場合は期限なし（手動停止まで受付）</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-info text-white" onclick="saveRenewStart()">受付開始</button>
            </div>
        </div>
    </div>
</div>

<!-- 継続入会 停止確認モーダル -->
<div class="modal fade" id="renewStopModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">継続入会の受付を停止</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="renewStopYear">
                <p>継続入会フォームの受付を停止します。ポータルページのボタンが非表示になります。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-danger" onclick="saveRenewStop()">停止する</button>
            </div>
        </div>
    </div>
</div>

<!-- 入会金設定モーダル -->
<div class="modal fade" id="feeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feeModalTitle">入会金設定</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="feeForm">
                    <input type="hidden" id="feeId">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">年度 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="academicYear" placeholder="例: 2026" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="feeName" placeholder="例: 2026年度入会金" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">振込期限 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="feeDeadline" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">対象区分 <span class="text-danger">*</span></label>
                            <select class="form-select" id="feeTargetType">
                                <option value="both">新規・継続 両方</option>
                                <option value="new">新規入会のみ</option>
                                <option value="renew">継続入会のみ</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="mb-2">学年別金額</h6>
                    <p class="text-muted small mb-2">4年・M1・M2・OB・OGは同額です。</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-3">
                            <thead class="table-light">
                                <tr>
                                    <?php
                                    $gradeLabels = ['1'=>'1年','2'=>'2年','3'=>'3年','OB_OG'=>'OB/OG'];
                                    foreach ($gradeLabels as $g => $label): ?>
                                    <th class="text-center"><?= $label ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($gradeLabels as $g => $label): ?>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-end"
                                               data-grade="<?= $g ?>" id="grade_<?= $g ?>"
                                               placeholder="0" min="0" step="100">
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="feeFormError" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="saveFee()">保存</button>
            </div>
        </div>
    </div>
</div>

<script>
// 入力欄のキー（OB/OGはまとめて1つ）
const GRADE_KEYS = ['1','2','3','OB_OG'];
// OB_OG として入力された値をDBに保存する際に展開するグレード
const OB_OG_GRADES = ['4','M1','M2','OB','OG'];
let years = [];
let allFees = [];
let currentFeeId = null;

// ページ読み込み時
document.addEventListener('DOMContentLoaded', () => {
    loadYears();

    // タブ切り替えで遅延ロード
    document.querySelector('[data-bs-target="#tabFees"]').addEventListener('shown.bs.tab', () => {
        if (allFees.length === 0) loadFees();
    });
    document.querySelector('[data-bs-target="#tabNewMembers"]').addEventListener('shown.bs.tab', () => {
        loadNewMembers();
    });
});

// ====== 入会フォームタブ ======

async function loadYears() {
    const res = await fetch('/api/academic-years');
    const data = await res.json();
    if (data.success) {
        years = data.data.years || data.data || [];
        renderYearCards();
        loadMemberCounts();
    }
}

async function loadMemberCounts() {
    for (const year of years) {
        const res = await fetch(`/api/members?academic_year=${year.year}&per_page=1`);
        const data = await res.json();
        if (data.success) {
            const el = document.getElementById(`memberCount-${year.year}`);
            if (el) el.textContent = (data.data.pagination?.total ?? 0) + '名';
        }
    }
}

function renderYearCards() {
    const container = document.getElementById('yearCards');
    if (!years.length) {
        container.innerHTML = '<div class="text-muted text-center py-5">年度データがありません</div>';
        return;
    }

    const base  = window.location.origin;
    const today = new Date().toISOString().slice(0, 10);

    container.innerHTML = years.map(year => {
        const enrollOpen    = year.enroll_open == 1;
        const renewOpen     = year.renew_open  == 1;
        const enrollDeadline = year.enrollment_deadline || null;
        const renewDeadline  = year.renew_deadline      || null;
        const enrollPast    = enrollDeadline && enrollDeadline < today;
        const renewPast     = renewDeadline  && renewDeadline  < today;

        // 実質的な受付状態（フラグON かつ 期限内）
        const enrollActive  = enrollOpen && !enrollPast;
        const renewActive   = renewOpen  && !renewPast;

        const enrollUrl = `${base}/enroll`;
        const renewUrl  = `${base}/renew`;

        const deadlineBadge = (d, past) => {
            if (!d) return '<span class="text-muted small">期限なし</span>';
            return past
                ? `<span class="badge bg-danger">期限切れ ${d}</span>`
                : `<span class="badge bg-success">${d} まで</span>`;
        };

        const enrollStatusBadge = enrollActive
            ? '<span class="badge bg-success">受付中</span>'
            : (enrollOpen && enrollPast
                ? '<span class="badge bg-warning text-dark">期限切れ</span>'
                : '<span class="badge bg-secondary">停止中</span>');

        const renewStatusBadge  = renewActive
            ? '<span class="badge bg-info text-white">受付中</span>'
            : (renewOpen && renewPast
                ? '<span class="badge bg-warning text-dark">期限切れ</span>'
                : '<span class="badge bg-secondary">停止中</span>');

        const enrollActionBtn = (enrollOpen && !enrollPast)
            ? `<button class="btn btn-outline-danger btn-xs btn-sm" onclick="showEnrollStop(${year.year})"><i class="bi bi-stop-circle"></i> 停止</button>`
            : `<button class="btn btn-success btn-sm" onclick="showEnrollStart(${year.year}, '${enrollDeadline||''}')"><i class="bi bi-play-circle"></i> 受付開始</button>`;

        const renewActionBtn  = (renewOpen && !renewPast)
            ? `<button class="btn btn-outline-danger btn-xs btn-sm" onclick="showRenewStop(${year.year})"><i class="bi bi-stop-circle"></i> 停止</button>`
            : `<button class="btn btn-info text-white btn-sm" onclick="showRenewStart(${year.year}, '${renewDeadline||''}')"><i class="bi bi-play-circle"></i> 受付開始</button>`;

        const cardBorder = (enrollActive || renewActive) ? 'border-success' : '';

        return `
        <div class="card mb-3 ${cardBorder}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">${year.year}年度</h5>
                    <span class="text-muted small" id="memberCount-${year.year}">
                        <span class="spinner-border spinner-border-sm"></span>
                    </span>
                </div>

                <div class="row g-3">
                    <!-- 新規入会 -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 ${enrollActive ? 'border-success bg-success bg-opacity-10' : ''}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold"><i class="bi bi-person-plus text-success"></i> 新規入会 ${enrollStatusBadge}</span>
                                ${enrollActionBtn}
                            </div>
                            <div class="mb-2 small">${deadlineBadge(enrollDeadline, enrollPast)}</div>
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm url-input" value="${enrollUrl}" readonly style="font-size:0.75rem;">
                                <button class="btn btn-sm btn-outline-secondary copy-btn flex-shrink-0" data-url="${enrollUrl}" title="コピー"><i class="bi bi-clipboard"></i></button>
                            </div>
                        </div>
                    </div>
                    <!-- 継続入会 -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 ${renewActive ? 'border-info bg-info bg-opacity-10' : ''}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold"><i class="bi bi-arrow-repeat text-info"></i> 継続入会 ${renewStatusBadge}</span>
                                ${renewActionBtn}
                            </div>
                            <div class="mb-2 small">${deadlineBadge(renewDeadline, renewPast)}</div>
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm url-input" value="${renewUrl}" readonly style="font-size:0.75rem;">
                                <button class="btn btn-sm btn-outline-secondary copy-btn flex-shrink-0" data-url="${renewUrl}" title="コピー"><i class="bi bi-clipboard"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
}

function showCreateYearModal() {
    const maxYear = years.length ? Math.max(...years.map(y => y.year)) : new Date().getFullYear();
    document.getElementById('newYear').value = maxYear + 1;
    new bootstrap.Modal(document.getElementById('createYearModal')).show();
}

async function createYear() {
    const year = document.getElementById('newYear').value;
    if (!year) { alert('年度を入力してください'); return; }
    const res = await fetch('/api/academic-years', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ year: parseInt(year) }),
    });
    const data = await res.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('createYearModal')).hide();
        loadYears();
    } else { alert('エラー: ' + (data.error?.message || data.error)); }
}

// ====== 新規入会 開始/停止 ======

function showEnrollStart(year, currentDeadline) {
    document.getElementById('enrollStartYear').value = year;
    document.getElementById('enrollStartModalTitle').textContent = `${year}年度 新規入会の受付を開始`;
    document.getElementById('enrollDeadlineInput').value = currentDeadline || '';
    new bootstrap.Modal(document.getElementById('enrollStartModal')).show();
}

async function saveEnrollStart() {
    const year     = document.getElementById('enrollStartYear').value;
    const deadline = document.getElementById('enrollDeadlineInput').value;

    const res = await fetch('/api/academic-years/set-enroll-open', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ year: parseInt(year), open: true, enrollment_deadline: deadline }),
    });
    const data = await res.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('enrollStartModal')).hide();
        loadYears();
    } else { alert('エラー: ' + (data.error?.message || data.error)); }
}

function showEnrollStop(year) {
    document.getElementById('enrollStopYear').value = year;
    new bootstrap.Modal(document.getElementById('enrollStopModal')).show();
}

async function saveEnrollStop() {
    const year = document.getElementById('enrollStopYear').value;
    const res = await fetch('/api/academic-years/set-enroll-open', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ year: parseInt(year), open: false }),
    });
    const data = await res.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('enrollStopModal')).hide();
        loadYears();
    } else { alert('エラー: ' + (data.error?.message || data.error)); }
}

// ====== 継続入会 開始/停止 ======

function showRenewStart(year, currentDeadline) {
    document.getElementById('renewStartYear').value = year;
    document.getElementById('renewStartModalTitle').textContent = `${year}年度 継続入会の受付を開始`;
    document.getElementById('renewDeadlineInput').value = currentDeadline || '';
    new bootstrap.Modal(document.getElementById('renewStartModal')).show();
}

async function saveRenewStart() {
    const year     = document.getElementById('renewStartYear').value;
    const deadline = document.getElementById('renewDeadlineInput').value;

    const res = await fetch('/api/academic-years/set-renew-open', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ year: parseInt(year), open: true, renew_deadline: deadline }),
    });
    const data = await res.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('renewStartModal')).hide();
        loadYears();
    } else { alert('エラー: ' + (data.error?.message || data.error)); }
}

function showRenewStop(year) {
    document.getElementById('renewStopYear').value = year;
    new bootstrap.Modal(document.getElementById('renewStopModal')).show();
}

async function saveRenewStop() {
    const year = document.getElementById('renewStopYear').value;
    const res = await fetch('/api/academic-years/set-renew-open', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ year: parseInt(year), open: false }),
    });
    const data = await res.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('renewStopModal')).hide();
        loadYears();
    } else { alert('エラー: ' + (data.error?.message || data.error)); }
}

// コピーボタン
document.addEventListener('click', async e => {
    const btn = e.target.closest('.copy-btn');
    if (!btn || btn.disabled) return;
    const url = btn.dataset.url;
    const origHtml = btn.innerHTML;
    try { await navigator.clipboard.writeText(url); } catch {
        const input = btn.previousElementSibling;
        if (input) { const d = input.disabled; input.disabled=false; input.select(); document.execCommand('copy'); input.disabled=d; }
    }
    btn.innerHTML = '<i class="bi bi-check-lg"></i>';
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-success');
    setTimeout(() => {
        btn.innerHTML = origHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 1500);
});

// ====== 入会金管理タブ ======

async function loadFees() {
    const res = await fetch('/api/membership-fees');
    const data = await res.json();
    allFees = data.data || [];
    renderFeeList();
}

function renderFeeList() {
    const container = document.getElementById('feeList');
    if (!allFees.length) {
        container.innerHTML = '<div class="text-muted">入会金設定がありません。「新規作成」から作成してください。</div>';
        return;
    }

    let html = '<div class="list-group">';
    for (const fee of allFees) {
        const activeLabel = fee.is_active == 1
            ? '<span class="badge bg-success ms-2">受付中</span>'
            : '<span class="badge bg-secondary ms-2">非公開</span>';
        const targetLabels = { new: '新規のみ', renew: '継続のみ', both: '新規・継続' };
        const targetColors = { new: 'bg-primary', renew: 'bg-info text-dark', both: 'bg-light text-dark border' };
        const t = fee.target_type || 'both';
        const targetBadge = `<span class="badge ${targetColors[t]} ms-2">${targetLabels[t]}</span>`;
        html += `
        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-start" style="cursor:pointer" onclick="loadFeeDetail(${fee.id})">
            <div>
                <strong>${escHtml(fee.name)}</strong>${activeLabel}${targetBadge}
                <div class="text-muted small">${fee.academic_year}年度 &nbsp;|&nbsp; 振込期限: ${escHtml(fee.deadline)} &nbsp;|&nbsp; 提出: ${fee.submitted_count}/${fee.total_count}</div>
            </div>
            <div class="btn-group btn-group-sm ms-2" onclick="event.stopPropagation()">
                <button class="btn btn-outline-primary" onclick="showEditFeeModal(${fee.id})"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-outline-danger" onclick="deleteFee(${fee.id})"><i class="bi bi-trash"></i></button>
            </div>
        </div>`;
    }
    html += '</div>';
    container.innerHTML = html;
}

async function loadFeeDetail(feeId) {
    currentFeeId = feeId;
    const detail = document.getElementById('feeDetail');
    detail.innerHTML = '<div class="text-muted">読み込み中...</div>';
    detail.classList.remove('d-none');

    const res = await fetch(`/api/membership-fees/${feeId}`);
    const data = await res.json();
    if (!data.success) { detail.innerHTML = '<div class="alert alert-danger">読み込みに失敗しました</div>'; return; }

    const { fee, grades, submitted, unsubmitted } = data.data;
    // OB/OGはOBの値を代表として表示
    const gradeLabels = {'1':'1年','2':'2年','3':'3年','OB_OG':'OB/OG'};

    let gradesHtml = '';
    for (const [g, label] of Object.entries(gradeLabels)) {
        const dbKey = (g === 'OB_OG') ? 'OB' : g;
        const amt = grades[dbKey];
        gradesHtml += `<span class="badge bg-light text-dark border me-1">${label}: ${amt !== undefined ? Number(amt).toLocaleString()+'円' : '-'}</span>`;
    }

    const renderItems = (items, isSubmitted) => {
        if (!items.length) return '<div class="text-muted small">なし</div>';
        let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
        html += '<thead class="table-light"><tr><th>名前</th><th>学年</th><th>金額</th><th>状態</th><th>操作</th></tr></thead><tbody>';
        for (const item of items) {
            const amt = item.effective_amount !== null ? Number(item.effective_amount).toLocaleString()+'円' : '-';
            const confirmed = isSubmitted
                ? (item.admin_confirmed==1 ? '<span class="badge bg-info">通帳確認済</span>' : '<span class="badge bg-warning text-dark">確認待ち</span>')
                : '';
            html += `<tr>
                <td>${escHtml(item.name_kanji)}</td>
                <td>${escHtml(item.grade)}</td>
                <td><input type="number" class="form-control form-control-sm" style="width:100px" value="${item.custom_amount??''}" placeholder="${item.effective_amount??0}" onchange="updateFeeItemAmount(${item.id}, this.value)"></td>
                <td>${isSubmitted ? '<span class="badge bg-success">提出済</span> '+confirmed : '<span class="badge bg-secondary">未提出</span>'}</td>
                <td>${isSubmitted ? `<button class="btn btn-outline-info btn-sm" onclick="toggleFeeConfirm(${item.id})">${item.admin_confirmed==1?'確認取消':'通帳確認'}</button>` : ''}</td>
            </tr>`;
        }
        html += '</tbody></table></div>';
        return html;
    };

    detail.innerHTML = `
    <hr>
    <h6>${escHtml(fee.name)}</h6>
    <div class="mb-3">${gradesHtml}</div>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#feeTabUnsub">未提出 <span class="badge bg-secondary">${unsubmitted.length}</span></button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#feeTabSub">提出済み <span class="badge bg-success">${submitted.length}</span></button></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="feeTabUnsub">${renderItems(unsubmitted, false)}</div>
        <div class="tab-pane fade" id="feeTabSub">${renderItems(submitted, true)}</div>
    </div>`;
}

async function updateFeeItemAmount(itemId, value) {
    await fetch(`/api/membership-fee-items/${itemId}`, {
        method: 'PUT', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({custom_amount: value}),
    });
}

async function toggleFeeConfirm(itemId) {
    const res = await fetch(`/api/membership-fee-items/${itemId}/confirm`, {
        method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({}),
    });
    const data = await res.json();
    if (data.success) loadFeeDetail(currentFeeId);
}

function showCreateFeeModal() {
    document.getElementById('feeModalTitle').textContent = '入会金設定を新規作成';
    document.getElementById('feeId').value = '';
    document.getElementById('feeForm').reset();
    document.getElementById('feeTargetType').value = 'both';
    document.getElementById('feeFormError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('feeModal')).show();
}

async function showEditFeeModal(feeId) {
    const res = await fetch(`/api/membership-fees/${feeId}`);
    const data = await res.json();
    if (!data.success) return;
    const { fee, grades } = data.data;
    document.getElementById('feeModalTitle').textContent = '入会金設定を編集';
    document.getElementById('feeId').value = fee.id;
    document.getElementById('academicYear').value = fee.academic_year;
    document.getElementById('feeName').value = fee.name;
    document.getElementById('feeDeadline').value = fee.deadline;
    document.getElementById('feeTargetType').value = fee.target_type || 'both';
    document.getElementById('feeFormError').classList.add('d-none');
    for (const g of GRADE_KEYS) {
        const el = document.getElementById(`grade_${g}`);
        if (!el) continue;
        // OB_OG 欄はDBの OB の値を代表として表示
        const dbKey = (g === 'OB_OG') ? 'OB' : g;
        el.value = grades[dbKey] ?? '';
    }
    new bootstrap.Modal(document.getElementById('feeModal')).show();
}

async function saveFee() {
    const feeId = document.getElementById('feeId').value;
    const academicYear = document.getElementById('academicYear').value;
    const name = document.getElementById('feeName').value.trim();
    const deadline = document.getElementById('feeDeadline').value;
    const errorDiv = document.getElementById('feeFormError');
    if (!academicYear || !name || !deadline) {
        errorDiv.textContent = '年度、名称、振込期限は必須です';
        errorDiv.classList.remove('d-none');
        return;
    }
    const grades = {};
    for (const g of GRADE_KEYS) {
        const val = document.getElementById(`grade_${g}`).value;
        if (val === '') continue;
        const amount = parseInt(val, 10);
        if (g === 'OB_OG') {
            // OB/OG 欄の値を 4・M1・M2・OB・OG すべてに適用
            for (const og of OB_OG_GRADES) {
                grades[og] = amount;
            }
        } else {
            grades[g] = amount;
        }
    }
    const targetType = document.getElementById('feeTargetType').value;
    const body = { academic_year: parseInt(academicYear), name, deadline, target_type: targetType, grades };
    const url = feeId ? `/api/membership-fees/${feeId}` : '/api/membership-fees';
    const res = await fetch(url, {
        method: feeId ? 'PUT' : 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(body),
    });
    const data = await res.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('feeModal')).hide();
        loadFees();
    } else {
        errorDiv.textContent = data.error?.message || '保存に失敗しました';
        errorDiv.classList.remove('d-none');
    }
}

async function deleteFee(feeId) {
    if (!confirm('この入会金設定を削除しますか？\n（支払い状況も全て削除されます）')) return;
    const res = await fetch(`/api/membership-fees/${feeId}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
        currentFeeId = null;
        document.getElementById('feeDetail').classList.add('d-none');
        loadFees();
    }
}

// ====== 新規入会者リストタブ ======

async function loadNewMembers() {
    const container = document.getElementById('newMemberList');
    try {
        const res = await fetch('/api/members/pending');
        const data = await res.json();
        if (!data.success) throw new Error();
        const members = data.data.members || [];
        document.getElementById('pendingCount').textContent = data.data.count ?? members.length;
        renderNewMembers(members, container);
    } catch (e) {
        container.innerHTML = '<div class="alert alert-danger">データの取得に失敗しました</div>';
    }
}

function renderNewMembers(members, container) {
    if (!members.length) {
        container.innerHTML = `
            <div class="card"><div class="card-body text-center py-5 text-muted">
                <i class="bi bi-person-check fs-1 mb-3 d-block"></i>
                <p class="mb-0">新規入会者はいません</p>
            </div></div>`;
        return;
    }

    container.innerHTML = members.map(m => `
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-2">${escHtml(m.name_kanji)} <small class="text-muted">(${escHtml(m.name_kana)})</small></h5>
                <div class="row g-2">
                    <div class="col-md-6">
                        <small class="text-muted d-block">学籍番号</small>
                        <code>${escHtml(m.student_id)}</code>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">学部・学科</small>
                        ${escHtml(m.faculty)} ${escHtml(m.department)}
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">学年・性別</small>
                        ${escHtml(m.grade)}年 / ${m.gender === 'male' ? '男性' : '女性'}
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">登録日</small>
                        ${fmtDateTime(m.created_at)}
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-info" onclick="toggleNewMemberDetail(${m.id})">
                        <i class="bi bi-info-circle"></i> 詳細
                    </button>
                </div>
                <div class="collapse mt-3 pt-3 border-top" id="nmDetail-${m.id}">
                    <div class="row g-2 small">
                        <div class="col-md-4"><strong>電話番号:</strong> ${escHtml(m.phone)}</div>
                        <div class="col-md-4"><strong>緊急連絡先:</strong> ${escHtml(m.emergency_contact)}</div>
                        <div class="col-md-4"><strong>生年月日:</strong> ${escHtml(m.birthdate)}</div>
                        <div class="col-md-6"><strong>住所:</strong> ${escHtml(m.address)}</div>
                        <div class="col-md-6"><strong>メール:</strong> ${escHtml(m.email || '-')}</div>
                        <div class="col-md-4"><strong>LINE名:</strong> ${escHtml(m.line_name)}</div>
                        <div class="col-md-4"><strong>アレルギー:</strong> ${escHtml(m.allergy || 'なし')}</div>
                        <div class="col-md-4"><strong>SNS投稿:</strong> ${m.sns_allowed == 1 ? '可' : '不可'}</div>
                        <div class="col-md-6"><strong>コート予約番号:</strong> ${escHtml(m.sports_registration_no || '-')}</div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function toggleNewMemberDetail(id) {
    const el = document.getElementById(`nmDetail-${id}`);
    new bootstrap.Collapse(el, { toggle: true });
}

function fmtDateTime(str) {
    if (!str) return '-';
    const d = new Date(str);
    return `${d.getFullYear()}/${String(d.getMonth()+1).padStart(2,'0')}/${String(d.getDate()).padStart(2,'0')} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
