<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>年度管理</h1>
    <button class="btn btn-primary" onclick="showCreateYearModal()">
        + 新年度を追加
    </button>
</div>

<!-- 年度一覧 -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">年度一覧</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>年度</th>
                    <th>期間</th>
                    <th>会員数</th>
                    <th width="120">操作</th>
                </tr>
            </thead>
            <tbody id="yearTableBody">
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">読み込み中...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
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
                <form id="createYearForm">
                    <div class="mb-3">
                        <label class="form-label">年度 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="newYear" min="2020" max="2100" required>
                        <small class="text-muted">例: 2026</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">開始日</label>
                        <input type="date" class="form-control" id="newStartDate">
                        <small class="text-muted">未入力の場合は4月1日</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">終了日</label>
                        <input type="date" class="form-control" id="newEndDate">
                        <small class="text-muted">未入力の場合は翌年3月31日</small>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="newEnrollmentOpen">
                        <label class="form-check-label" for="newEnrollmentOpen">
                            入会受付を開始
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="createYear()">作成</button>
            </div>
        </div>
    </div>
</div>

<script>
let years = [];

document.addEventListener('DOMContentLoaded', function() {
    loadYears();
});

async function loadYears() {
    try {
        const response = await fetch('/index.php?route=api/academic-years');
        const data = await response.json();

        if (data.success) {
            years = data.data.years;
            renderYearTable();
            // 会員数を取得
            loadMemberCounts();
        } else {
            showError('年度の取得に失敗しました: ' + data.error);
        }
    } catch (error) {
        showError('通信エラー: ' + error.message);
    }
}

async function loadMemberCounts() {
    for (const year of years) {
        try {
            const response = await fetch(`/index.php?route=api/members&academic_year=${year.year}&per_page=1`);
            const data = await response.json();
            if (data.success) {
                const countEl = document.getElementById(`memberCount-${year.year}`);
                if (countEl) {
                    countEl.textContent = data.data.pagination.total + '名';
                }
            }
        } catch (error) {
            console.error('会員数取得エラー:', error);
        }
    }
}

function renderYearTable() {
    const tbody = document.getElementById('yearTableBody');

    if (years.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4 text-muted">
                    年度データがありません
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = years.map(year => `
        <tr>
            <td><strong>${year.year}年度</strong></td>
            <td class="text-nowrap">${formatDate(year.start_date)} 〜 ${formatDate(year.end_date)}</td>
            <td id="memberCount-${year.year}">
                <span class="spinner-border spinner-border-sm" role="status"></span>
            </td>
            <td>
                <a href="/members?year=${year.year}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-people"></i> 会員一覧
                </a>
            </td>
        </tr>
    `).join('');
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return `${date.getFullYear()}/${String(date.getMonth() + 1).padStart(2, '0')}/${String(date.getDate()).padStart(2, '0')}`;
}

function showCreateYearModal() {
    // 次の年度を自動入力
    const currentYear = years.length > 0 ? Math.max(...years.map(y => y.year)) : new Date().getFullYear();
    document.getElementById('newYear').value = currentYear + 1;
    document.getElementById('newStartDate').value = '';
    document.getElementById('newEndDate').value = '';
    document.getElementById('newEnrollmentOpen').checked = false;

    new bootstrap.Modal(document.getElementById('createYearModal')).show();
}

async function createYear() {
    const year = document.getElementById('newYear').value;
    const startDate = document.getElementById('newStartDate').value;
    const endDate = document.getElementById('newEndDate').value;
    const enrollmentOpen = document.getElementById('newEnrollmentOpen').checked ? 1 : 0;

    if (!year) {
        showError('年度を入力してください');
        return;
    }

    try {
        const response = await fetch('/index.php?route=api/academic-years', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                year: parseInt(year),
                start_date: startDate,
                end_date: endDate,
                enrollment_open: enrollmentOpen
            })
        });

        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('createYearModal')).hide();
            showSuccess(data.data.message);
            loadYears();
        } else {
            showError(data.error);
        }
    } catch (error) {
        showError('通信エラー: ' + error.message);
    }
}

function showError(message) {
    alert('エラー: ' + message);
}
</script>
