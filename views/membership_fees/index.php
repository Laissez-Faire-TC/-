<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">入会金管理</h1>
    <button class="btn btn-primary" onclick="showCreateModal()">
        <i class="bi bi-plus-circle"></i> 新規作成
    </button>
</div>

<div id="feeList">読み込み中...</div>

<!-- 詳細パネル -->
<div id="feeDetail" class="d-none mt-4"></div>

<!-- 新規作成/編集モーダル -->
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
                        <div class="col-md-4">
                            <label class="form-label">年度 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="academicYear" placeholder="例: 2026" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="feeName" placeholder="例: 2026年度入会金" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">振込期限 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="feeDeadline" required>
                        </div>
                    </div>

                    <h6 class="mb-2">学年別金額</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-3">
                            <thead class="table-light">
                                <tr>
                                    <?php
                                    $gradeLabels = ['1' => '1年', '2' => '2年', '3' => '3年', '4' => '4年', 'M1' => 'M1', 'M2' => 'M2', 'OB' => 'OB', 'OG' => 'OG'];
                                    foreach ($gradeLabels as $g => $label): ?>
                                    <th class="text-center"><?= $label ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($gradeLabels as $g => $label): ?>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-end grade-amount"
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
const GRADE_KEYS = ['1', '2', '3', '4', 'M1', 'M2', 'OB', 'OG'];
let currentFeeId = null;
let allFees = [];

// ページ読み込み時
loadFees();

async function loadFees() {
    const res = await fetch('/api/membership-fees');
    const data = await res.json();
    allFees = data.data || [];
    renderFeeList();
}

function renderFeeList() {
    const container = document.getElementById('feeList');
    if (allFees.length === 0) {
        container.innerHTML = '<div class="text-muted">入会金設定がありません</div>';
        return;
    }

    let html = '<div class="list-group">';
    for (const fee of allFees) {
        const activeLabel = fee.is_active == 1
            ? '<span class="badge bg-success ms-2">受付中</span>'
            : '<span class="badge bg-secondary ms-2">非公開</span>';

        html += `
        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-start" style="cursor:pointer" onclick="loadDetail(${fee.id})">
            <div>
                <strong>${escHtml(fee.name)}</strong>${activeLabel}
                <div class="text-muted small">
                    ${fee.academic_year}年度 &nbsp;|&nbsp; 振込期限: ${escHtml(fee.deadline)}
                    &nbsp;|&nbsp; 提出: ${fee.submitted_count}/${fee.total_count}
                </div>
            </div>
            <div class="btn-group btn-group-sm ms-2" onclick="event.stopPropagation()">
                <button class="btn btn-outline-primary" onclick="showEditModal(${fee.id})"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-outline-danger" onclick="deleteFee(${fee.id})"><i class="bi bi-trash"></i></button>
            </div>
        </div>`;
    }
    html += '</div>';
    container.innerHTML = html;
}

async function loadDetail(feeId) {
    currentFeeId = feeId;
    const detail = document.getElementById('feeDetail');
    detail.innerHTML = '<div class="text-muted">読み込み中...</div>';
    detail.classList.remove('d-none');

    const res = await fetch(`/api/membership-fees/${feeId}`);
    const data = await res.json();
    if (!data.success) {
        detail.innerHTML = '<div class="alert alert-danger">読み込みに失敗しました</div>';
        return;
    }

    const { fee, grades, submitted, unsubmitted } = data.data;
    renderDetail(fee, grades, submitted, unsubmitted);
}

function renderDetail(fee, grades, submitted, unsubmitted) {
    const detail = document.getElementById('feeDetail');

    const gradeLabels = { '1': '1年', '2': '2年', '3': '3年', '4': '4年', 'M1': 'M1', 'M2': 'M2', 'OB': 'OB', 'OG': 'OG' };
    let gradesHtml = '';
    for (const [g, label] of Object.entries(gradeLabels)) {
        const amt = grades[g] ?? '-';
        gradesHtml += `<span class="badge bg-light text-dark border me-1">${label}: ${amt !== '-' ? Number(amt).toLocaleString() + '円' : '-'}</span>`;
    }

    const renderItems = (items, isSubmitted) => {
        if (items.length === 0) return '<div class="text-muted small">なし</div>';
        let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
        html += '<thead class="table-light"><tr><th>名前</th><th>学年</th><th>金額</th><th>状態</th><th>操作</th></tr></thead><tbody>';
        for (const item of items) {
            const amt = item.effective_amount !== null ? Number(item.effective_amount).toLocaleString() + '円' : '-';
            const confirmed = isSubmitted
                ? (item.admin_confirmed == 1
                    ? '<span class="badge bg-info">通帳確認済</span>'
                    : '<span class="badge bg-warning text-dark">確認待ち</span>')
                : '';
            html += `<tr>
                <td>${escHtml(item.name_kanji)}</td>
                <td>${escHtml(item.grade)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm" style="width:100px"
                           value="${item.custom_amount ?? ''}"
                           placeholder="${item.effective_amount ?? 0}"
                           onchange="updateItemAmount(${item.id}, this.value)">
                </td>
                <td>${isSubmitted ? `<span class="badge bg-success">提出済</span> ${confirmed}` : '<span class="badge bg-secondary">未提出</span>'}</td>
                <td>
                    ${isSubmitted ? `<button class="btn btn-outline-info btn-sm" onclick="toggleConfirm(${item.id})">${item.admin_confirmed == 1 ? '確認取消' : '通帳確認'}</button>` : ''}
                </td>
            </tr>`;
        }
        html += '</tbody></table></div>';
        return html;
    };

    detail.innerHTML = `
    <hr>
    <h5>${escHtml(fee.name)}</h5>
    <div class="mb-3">${gradesHtml}</div>

    <ul class="nav nav-tabs mb-3" id="detailTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabUnsubmitted">
                未提出 <span class="badge bg-secondary">${unsubmitted.length}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSubmitted">
                提出済み <span class="badge bg-success">${submitted.length}</span>
            </button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="tabUnsubmitted">
            ${renderItems(unsubmitted, false)}
        </div>
        <div class="tab-pane fade" id="tabSubmitted">
            ${renderItems(submitted, true)}
        </div>
    </div>`;
}

async function updateItemAmount(itemId, value) {
    const body = { custom_amount: value === '' ? '' : value };
    await fetch(`/api/membership-fee-items/${itemId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
    });
}

async function toggleConfirm(itemId) {
    const res = await fetch(`/api/membership-fee-items/${itemId}/confirm`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({}),
    });
    const data = await res.json();
    if (data.success) {
        loadDetail(currentFeeId);
    }
}

function showCreateModal() {
    document.getElementById('feeModalTitle').textContent = '入会金設定を新規作成';
    document.getElementById('feeId').value = '';
    document.getElementById('feeForm').reset();
    document.getElementById('feeFormError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('feeModal')).show();
}

async function showEditModal(feeId) {
    const res = await fetch(`/api/membership-fees/${feeId}`);
    const data = await res.json();
    if (!data.success) return;

    const { fee, grades } = data.data;
    document.getElementById('feeModalTitle').textContent = '入会金設定を編集';
    document.getElementById('feeId').value = fee.id;
    document.getElementById('academicYear').value = fee.academic_year;
    document.getElementById('feeName').value = fee.name;
    document.getElementById('feeDeadline').value = fee.deadline;
    document.getElementById('feeFormError').classList.add('d-none');

    for (const g of GRADE_KEYS) {
        const el = document.getElementById(`grade_${g}`);
        if (el) el.value = grades[g] ?? '';
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
        if (val !== '') grades[g] = parseInt(val, 10);
    }

    const body = { academic_year: parseInt(academicYear), name, deadline, grades };
    const url = feeId ? `/api/membership-fees/${feeId}` : '/api/membership-fees';
    const method = feeId ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
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

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
