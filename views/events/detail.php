<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/events" class="text-muted small text-decoration-none">← 企画一覧</a>
        <h1 class="mb-0 mt-1"><?= htmlspecialchars($event['title']) ?></h1>
    </div>
    <div>
        <button id="toggleActiveBtn" class="btn btn-outline-secondary me-2" onclick="toggleActive()">
            <?= $event['is_active'] ? '非公開にする' : '会員ページに公開する' ?>
        </button>
    </div>
</div>

<!-- タブナビゲーション -->
<ul class="nav nav-tabs mb-4" id="eventTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tabInfo">基本情報</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tabApplications">
            申込者 <span class="badge bg-secondary" id="appCountBadge"><?= $event['application_count'] ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tabExpenses">雑費</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tabCalc">費用計算</a>
    </li>
</ul>

<div class="tab-content">

    <!-- ── 基本情報タブ ── -->
    <div class="tab-pane fade show active" id="tabInfo">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">タイトル</label>
                        <input type="text" class="form-control" id="infoTitle" value="<?= htmlspecialchars($event['title']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">開催日</label>
                        <input type="date" class="form-control" id="infoDate" value="<?= htmlspecialchars($event['event_date']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">開始時刻</label>
                        <input type="time" class="form-control" id="infoTime" value="<?= htmlspecialchars(substr($event['event_time'] ?? '', 0, 5)) ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">場所</label>
                        <input type="text" class="form-control" id="infoLocation" value="<?= htmlspecialchars($event['location'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">概要</label>
                        <textarea class="form-control" id="infoDescription" rows="4"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">参加費（円）</label>
                        <input type="number" class="form-control" id="infoFee" value="<?= (int)$event['participation_fee'] ?>" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">定員</label>
                        <input type="number" class="form-control" id="infoCapacity"
                               value="<?= $event['capacity'] !== null ? (int)$event['capacity'] : '' ?>"
                               placeholder="空欄 = 制限なし" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">申込期限</label>
                        <input type="date" class="form-control" id="infoDeadline"
                               value="<?= htmlspecialchars($event['deadline'] ?? '') ?>">
                        <div class="form-text">期限を過ぎると会員ページから非表示</div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="infoAllowWaitlist"
                                   <?= $event['allow_waitlist'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="infoAllowWaitlist">キャンセル待ちを受け付ける</label>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="infoIsActive"
                                   <?= $event['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="infoIsActive">公開中</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-primary" onclick="saveInfo()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 申込者タブ ── -->
    <div class="tab-pane fade" id="tabApplications">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>申込者一覧</span>
                <span id="capacityDisplay" class="fw-semibold"></span>
            </div>
            <div class="card-body p-0">
                <div id="applicationsContainer">
                    <div class="text-center p-4 text-muted">読み込み中...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 雑費タブ ── -->
    <div class="tab-pane fade" id="tabExpenses">
        <div class="card shadow-sm mb-3">
            <div class="card-header">雑費追加</div>
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">項目名</label>
                        <input type="text" class="form-control" id="expenseName" placeholder="例: コート代">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">金額（円）</label>
                        <input type="number" class="form-control" id="expenseAmount" value="0" min="0">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" onclick="addExpense()">追加</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div id="expensesContainer">
                    <div class="text-center p-4 text-muted">読み込み中...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 費用計算タブ ── -->
    <div class="tab-pane fade" id="tabCalc">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" onclick="loadCalc()">計算する</button>
                </div>
                <div id="calcResult"></div>
            </div>
        </div>
    </div>

</div>

<script>
const EVENT_ID = <?= (int)$eventId ?>;
let isActive   = <?= $event['is_active'] ? 'true' : 'false' ?>;
let capacity   = <?= $event['capacity'] !== null ? (int)$event['capacity'] : 'null' ?>;

// ──────────────────────────────────
// 初期ロード
// ──────────────────────────────────
document.querySelector('a[href="#tabApplications"]').addEventListener('shown.bs.tab', loadApplications);
document.querySelector('a[href="#tabExpenses"]').addEventListener('shown.bs.tab', loadExpenses);
document.querySelector('a[href="#tabCalc"]').addEventListener('shown.bs.tab', loadCalc);

// ──────────────────────────────────
// 基本情報保存
// ──────────────────────────────────
async function saveInfo() {
    const cap = document.getElementById('infoCapacity').value;
    const payload = {
        title:             document.getElementById('infoTitle').value.trim(),
        event_date:        document.getElementById('infoDate').value,
        event_time:        document.getElementById('infoTime').value        || null,
        location:          document.getElementById('infoLocation').value.trim() || null,
        description:       document.getElementById('infoDescription').value.trim() || null,
        participation_fee: parseInt(document.getElementById('infoFee').value) || 0,
        capacity:          cap !== '' ? parseInt(cap) : null,
        deadline:          document.getElementById('infoDeadline').value || null,
        allow_waitlist:    document.getElementById('infoAllowWaitlist').checked ? 1 : 0,
        is_active:         document.getElementById('infoIsActive').checked ? 1 : 0,
    };

    try {
        const res  = await fetch(`/api/events/${EVENT_ID}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            isActive = !!payload.is_active;
            capacity = payload.capacity;
            document.getElementById('toggleActiveBtn').textContent =
                isActive ? '非公開にする' : '会員ページに公開する';
            showToast('保存しました', 'success');
        } else {
            alert(data.error?.message || '保存に失敗しました');
        }
    } catch (e) {
        alert('通信エラーが発生しました');
    }
}

// ──────────────────────────────────
// 公開切り替え
// ──────────────────────────────────
async function toggleActive() {
    try {
        const res  = await fetch(`/api/events/${EVENT_ID}/toggle-active`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            isActive = !!data.data.is_active;
            document.getElementById('toggleActiveBtn').textContent =
                isActive ? '非公開にする' : '会員ページに公開する';
            document.getElementById('infoIsActive').checked = isActive;
            showToast(data.message, 'success');
        }
    } catch (e) {
        alert('通信エラーが発生しました');
    }
}

// ──────────────────────────────────
// 申込者一覧
// ──────────────────────────────────
async function loadApplications() {
    const container = document.getElementById('applicationsContainer');
    try {
        const res  = await fetch(`/api/events/${EVENT_ID}/applications`);
        const data = await res.json();
        const submitted = data.data?.submitted || [];
        const waitlist  = data.data?.waitlist  || [];

        document.getElementById('appCountBadge').textContent = submitted.length;

        // 定員表示
        const capDisplay = document.getElementById('capacityDisplay');
        if (capacity !== null) {
            const isFull = submitted.length >= capacity;
            const cls    = isFull ? 'bg-danger' : (submitted.length / capacity >= 0.8 ? 'bg-warning text-dark' : 'bg-primary');
            capDisplay.innerHTML = `<span class="badge ${cls} fs-6">${submitted.length} / ${capacity}</span>`;
        } else {
            capDisplay.innerHTML = `<span class="text-muted">${submitted.length}人申込中</span>`;
        }

        let html = '';

        // ── 参加確定一覧 ──
        if (submitted.length === 0) {
            html += '<div class="text-center p-4 text-muted">申込者はまだいません</div>';
        } else {
            const rows = submitted.map((a, i) => {
                const promoted = parseInt(a.promoted) === 1;
                const rowCls   = promoted ? 'table-warning' : '';
                const badge    = promoted
                    ? '<span class="badge bg-warning text-dark ms-1" title="キャンセル待ちから繰り上げ">繰り上げ</span>'
                    : '';
                return `
                <tr class="${rowCls}">
                    <td>${i + 1}</td>
                    <td class="fw-semibold">${esc(a.name_kanji)}${badge}</td>
                    <td>${esc(gradeLabel(a.grade))}</td>
                    <td>${esc(a.gender === 'male' ? '男' : '女')}</td>
                    <td class="small text-muted">${esc(a.department || '—')}</td>
                    <td class="small text-muted">${esc(a.line_name || '—')}</td>
                    <td class="small text-muted">${formatDateTime(a.created_at)}</td>
                    <td><button class="btn btn-sm btn-outline-danger" onclick="cancelApplication(${a.id})">取消</button></td>
                </tr>`;
            }).join('');

            html += `
            <div class="px-3 pt-3 pb-1">
                <span class="fw-semibold">参加確定</span>
                <span class="badge bg-primary ms-1">${submitted.length}人</span>
            </div>
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>#</th><th>氏名</th><th>学年</th><th>性別</th><th>学科</th><th>LINE名</th><th>申込日時</th><th></th></tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`;
        }

        // ── キャンセル待ち一覧 ──
        if (waitlist.length > 0) {
            const wRows = waitlist.map((a, i) => `
                <tr class="table-secondary">
                    <td class="text-muted">${i + 1}</td>
                    <td class="fw-semibold">${esc(a.name_kanji)}</td>
                    <td>${esc(gradeLabel(a.grade))}</td>
                    <td>${esc(a.gender === 'male' ? '男' : '女')}</td>
                    <td class="small text-muted">${esc(a.department || '—')}</td>
                    <td class="small text-muted">${esc(a.line_name || '—')}</td>
                    <td class="small text-muted">${formatDateTime(a.created_at)}</td>
                    <td><button class="btn btn-sm btn-outline-danger" onclick="cancelApplication(${a.id})">取消</button></td>
                </tr>`).join('');

            html += `
            <div class="px-3 pt-3 pb-1 border-top">
                <span class="fw-semibold text-secondary">キャンセル待ち</span>
                <span class="badge bg-secondary ms-1">${waitlist.length}人</span>
                <small class="text-muted ms-2">参加確定者がキャンセルすると自動で繰り上がります</small>
            </div>
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>順番</th><th>氏名</th><th>学年</th><th>性別</th><th>学科</th><th>LINE名</th><th>登録日時</th><th></th></tr>
                </thead>
                <tbody>${wRows}</tbody>
            </table>`;
        }

        container.innerHTML = html;
    } catch (e) {
        container.innerHTML = '<div class="text-center p-4 text-danger">読み込みに失敗しました</div>';
    }
}

async function cancelApplication(id) {
    if (!confirm('この申込をキャンセルしますか？')) return;
    try {
        const res  = await fetch(`/api/event-applications/${id}/cancel`, { method: 'POST' });
        const data = await res.json();
        if (data.success) { loadApplications(); }
        else { alert(data.error?.message || 'キャンセルに失敗しました'); }
    } catch (e) {
        alert('通信エラーが発生しました');
    }
}

// ──────────────────────────────────
// 雑費
// ──────────────────────────────────
async function loadExpenses() {
    const container = document.getElementById('expensesContainer');
    try {
        const res  = await fetch(`/api/events/${EVENT_ID}`);
        const data = await res.json();
        const expenses = data.data?.expenses || [];

        if (expenses.length === 0) {
            container.innerHTML = '<div class="text-center p-4 text-muted">雑費はありません</div>';
            return;
        }

        const total = expenses.reduce((s, e) => s + parseInt(e.amount), 0);
        const rows  = expenses.map(e => `
            <tr>
                <td>${esc(e.name)}</td>
                <td class="text-end">${Number(e.amount).toLocaleString()}円</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteExpense(${e.id})">削除</button>
                </td>
            </tr>`).join('');

        container.innerHTML = `
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>項目名</th><th class="text-end">金額</th><th></th></tr>
                </thead>
                <tbody>${rows}</tbody>
                <tfoot class="table-light fw-semibold">
                    <tr><td>合計</td><td class="text-end">${total.toLocaleString()}円</td><td></td></tr>
                </tfoot>
            </table>`;
    } catch (e) {
        container.innerHTML = '<div class="text-center p-4 text-danger">読み込みに失敗しました</div>';
    }
}

async function addExpense() {
    const name   = document.getElementById('expenseName').value.trim();
    const amount = parseInt(document.getElementById('expenseAmount').value) || 0;
    if (!name) { alert('項目名を入力してください'); return; }

    try {
        const res  = await fetch(`/api/events/${EVENT_ID}/expenses`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, amount }),
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('expenseName').value   = '';
            document.getElementById('expenseAmount').value = '0';
            loadExpenses();
        } else {
            alert(data.error?.message || '追加に失敗しました');
        }
    } catch (e) {
        alert('通信エラーが発生しました');
    }
}

async function deleteExpense(id) {
    if (!confirm('この雑費を削除しますか？')) return;
    try {
        const res  = await fetch(`/api/event-expenses/${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) { loadExpenses(); }
        else { alert(data.error?.message || '削除に失敗しました'); }
    } catch (e) {
        alert('通信エラーが発生しました');
    }
}

// ──────────────────────────────────
// 費用計算
// ──────────────────────────────────
async function loadCalc() {
    const container = document.getElementById('calcResult');
    container.innerHTML = '<div class="text-center text-muted">計算中...</div>';
    try {
        const res  = await fetch(`/api/events/${EVENT_ID}/calculate`);
        const data = await res.json();
        const d    = data.data;

        if (!data.success) {
            container.innerHTML = '<div class="text-danger">計算に失敗しました</div>';
            return;
        }

        const expenseRows = (d.expenses || []).map(e => `
            <tr><td>${esc(e.name)}</td><td class="text-end">${Number(e.amount).toLocaleString()}円</td></tr>
        `).join('');

        container.innerHTML = `
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body py-3">
                            <div class="fs-4 fw-bold text-primary">${d.applicant_count}人</div>
                            <div class="small text-muted">申込人数</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <div class="fs-4 fw-bold">${Number(d.participation_fee).toLocaleString()}円</div>
                            <div class="small text-muted">参加費</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <div class="fs-4 fw-bold">${Number(d.expense_per_person).toLocaleString()}円</div>
                            <div class="small text-muted">雑費負担（1人）</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body py-3">
                            <div class="fs-4 fw-bold text-success">${Number(d.total_per_person).toLocaleString()}円</div>
                            <div class="small text-muted">1人あたり合計</div>
                        </div>
                    </div>
                </div>
            </div>
            ${d.expenses.length > 0 ? `
            <h6 class="mb-2">雑費内訳</h6>
            <table class="table table-sm">
                <thead class="table-light"><tr><th>項目</th><th class="text-end">金額</th></tr></thead>
                <tbody>${expenseRows}</tbody>
                <tfoot class="fw-semibold"><tr><td>合計</td><td class="text-end">${Number(d.total_expenses).toLocaleString()}円</td></tr></tfoot>
            </table>` : '<p class="text-muted">雑費は登録されていません</p>'}
            ${d.applicant_count === 0 ? '<div class="alert alert-warning mt-3">申込者がいないため1人あたりの費用は計算できません</div>' : ''}`;
    } catch (e) {
        container.innerHTML = '<div class="text-danger">通信エラーが発生しました</div>';
    }
}

// ──────────────────────────────────
// ユーティリティ
// ──────────────────────────────────
function esc(str) {
    return String(str ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function gradeLabel(g) {
    if (g === 'OB' || g === 'OG') return g;
    return g + '年';
}

function formatDateTime(str) {
    if (!str) return '—';
    const d = new Date(str);
    return `${d.getFullYear()}/${d.getMonth()+1}/${d.getDate()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

function showToast(message, type = 'success') {
    const t = document.createElement('div');
    t.className = `toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
    t.setAttribute('role', 'alert');
    t.innerHTML = `<div class="d-flex"><div class="toast-body">${esc(message)}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.body.appendChild(t);
    const toast = new bootstrap.Toast(t, { delay: 3000 });
    toast.show();
    t.addEventListener('hidden.bs.toast', () => t.remove());
}
</script>
