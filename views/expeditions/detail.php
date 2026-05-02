<?php
// 遠征詳細ページ - 7タブ構成
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('/expeditions') ?>" class="text-decoration-none">&larr; 戻る</a>
        <h1 class="mt-2" id="expeditionTitle">読み込み中...</h1>
    </div>
</div>

<!-- タブナビゲーション -->
<ul class="nav nav-tabs mb-4" id="expeditionTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabBasic">基本情報</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabParticipants" onclick="loadParticipants()">参加者管理</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCars" onclick="loadCars()">車割</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabTeams" onclick="loadTeams()">チーム分け</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabApplication" onclick="loadApplicationUrl()">申し込みURL</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCollection" onclick="loadCollection()">集金管理</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBooklet" onclick="loadBooklet()">しおり</button>
    </li>
</ul>

<!-- タブコンテンツ -->
<div class="tab-content">

    <!-- ===== タブ1: 基本情報 ===== -->
    <div class="tab-pane fade show active" id="tabBasic">
        <div id="basicInfo">読み込み中...</div>
    </div>

    <!-- ===== タブ2: 参加者管理 ===== -->
    <div class="tab-pane fade" id="tabParticipants">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>参加者一覧 <span class="badge bg-secondary" id="participantCount">0</span></h4>
            <button class="btn btn-primary btn-sm" onclick="showAddParticipantModal()">+ 参加者を追加</button>
        </div>
        <div id="participantList">読み込み中...</div>
    </div>

    <!-- ===== タブ3: 車割 ===== -->
    <div class="tab-pane fade" id="tabCars">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>車割</h4>
            <button class="btn btn-primary btn-sm" onclick="showAddCarModal()">+ 車を追加</button>
        </div>
        <div id="carList">読み込み中...</div>
        <!-- 清算サマリー -->
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5>清算サマリー</h5>
                <button class="btn btn-outline-primary btn-sm" onclick="calcSettlement()">清算を計算</button>
            </div>
            <div id="settlementResult"></div>
        </div>
    </div>

    <!-- ===== タブ4: チーム分け ===== -->
    <div class="tab-pane fade" id="tabTeams">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>チーム分け</h4>
            <button class="btn btn-primary btn-sm" onclick="showAddTeamModal()">+ チームを追加</button>
        </div>
        <div id="teamBoard" class="d-flex flex-wrap gap-3">読み込み中...</div>
    </div>

    <!-- ===== タブ5: 申し込みURL ===== -->
    <div class="tab-pane fade" id="tabApplication">
        <div id="applicationUrlContent">読み込み中...</div>
    </div>

    <!-- ===== タブ6: 集金管理 ===== -->
    <div class="tab-pane fade" id="tabCollection">
        <div id="collectionContent">読み込み中...</div>
    </div>

    <!-- ===== タブ7: しおり ===== -->
    <div class="tab-pane fade" id="tabBooklet">
        <div id="bookletContent">読み込み中...</div>
    </div>

</div>

<!-- ===== 参加者追加モーダル ===== -->
<div class="modal fade" id="addParticipantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">参加者を追加</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">会員ID <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="newParticipantMemberId" placeholder="会員IDを入力">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="addParticipant()">追加</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== 車追加モーダル ===== -->
<div class="modal fade" id="addCarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">車を追加</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">車名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newCarName" placeholder="例: 田中号">
                </div>
                <div class="mb-3">
                    <label class="form-label">定員</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="newCarCapacity" min="1" value="5">
                        <span class="input-group-text">人</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">レンタカー代</label>
                    <div class="input-group">
                        <span class="input-group-text">¥</span>
                        <input type="number" class="form-control" id="newCarRentalFee" min="0" value="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">高速代</label>
                    <div class="input-group">
                        <span class="input-group-text">¥</span>
                        <input type="number" class="form-control" id="newCarHighwayFee" min="0" value="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="addCar()">追加</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== 乗員追加モーダル ===== -->
<div class="modal fade" id="addCarMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">乗員を追加</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">参加者</label>
                    <select class="form-select" id="carMemberSelect"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">役割</label>
                    <select class="form-select" id="carMemberRole">
                        <option value="passenger">乗客</option>
                        <option value="driver">ドライバー</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="addCarMember()">追加</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== 立替者追加モーダル ===== -->
<div class="modal fade" id="addCarPayerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">立替者を追加</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">参加者</label>
                    <select class="form-select" id="carPayerSelect"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">立替金額</label>
                    <div class="input-group">
                        <span class="input-group-text">¥</span>
                        <input type="number" class="form-control" id="carPayerAmount" min="0" value="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="addCarPayer()">追加</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== チーム追加モーダル ===== -->
<div class="modal fade" id="addTeamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">チームを追加</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">チーム名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newTeamName" placeholder="例: Aチーム">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="addTeam()">追加</button>
            </div>
        </div>
    </div>
</div>

<!-- SortableJS CDN（チーム分けD&D用） -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
// PHP から遠征IDを埋め込む
const expeditionId = <?= $data['id'] ?>;

// 各タブのロード済みフラグ（二重ロード防止）
let participantsLoaded = false;
let carsLoaded = false;
let teamsLoaded = false;
let applicationLoaded = false;
let collectionLoaded = false;
let bookletLoaded = false;

// モーダルインスタンス
let addParticipantModal, addCarModal, addCarMemberModal, addCarPayerModal, addTeamModal;

// 現在操作中の車ID（乗員・立替者追加時に使用）
let currentCarId = null;

// 参加者キャッシュ（各モーダルのセレクト生成に使用）
let participantsCache = [];

document.addEventListener('DOMContentLoaded', () => {
    // モーダルを初期化
    addParticipantModal = new bootstrap.Modal(document.getElementById('addParticipantModal'));
    addCarModal         = new bootstrap.Modal(document.getElementById('addCarModal'));
    addCarMemberModal   = new bootstrap.Modal(document.getElementById('addCarMemberModal'));
    addCarPayerModal    = new bootstrap.Modal(document.getElementById('addCarPayerModal'));
    addTeamModal        = new bootstrap.Modal(document.getElementById('addTeamModal'));

    // ページロード時に基本情報を取得
    loadBasicInfo();
});

// ==============================
// ユーティリティ
// ==============================
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '11';
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-body">${escapeHtml(message)}</div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), duration);
}

// ==============================
// タブ1: 基本情報
// ==============================
async function loadBasicInfo() {
    try {
        const res  = await fetch(`/api/expeditions/${expeditionId}`);
        const data = await res.json();
        if (data.success) {
            renderBasicInfo(data.data);
            document.getElementById('expeditionTitle').textContent = data.data.name || '遠征詳細';
        } else {
            document.getElementById('basicInfo').innerHTML = '<div class="alert alert-danger">読み込みに失敗しました</div>';
        }
    } catch (err) {
        console.error(err);
        document.getElementById('basicInfo').innerHTML = '<div class="alert alert-danger">通信エラーが発生しました</div>';
    }
}

function renderBasicInfo(e) {
    document.getElementById('basicInfo').innerHTML = `
        <div class="card">
            <div class="card-header">基本情報を編集</div>
            <div class="card-body">
                <form id="basicInfoForm">
                    <div class="mb-3">
                        <label class="form-label">イベント名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editName" value="${escapeHtml(e.name)}" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">開始日</label>
                            <input type="date" class="form-control" id="editStartDate" value="${e.start_date || ''}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">終了日</label>
                            <input type="date" class="form-control" id="editEndDate" value="${e.end_date || ''}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">参加費</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editParticipationFee" min="0" value="${e.participation_fee || 0}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">前泊費</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editPreNightFee" min="0" value="${e.pre_night_fee || 0}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">昼食費</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editLunchFee" min="0" value="${e.lunch_fee || 0}">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="saveBasicInfo()">保存</button>
                </form>
            </div>
        </div>
    `;
}

async function saveBasicInfo() {
    const name = document.getElementById('editName').value.trim();
    if (!name) {
        alert('イベント名を入力してください');
        return;
    }

    const data = {
        name:               name,
        start_date:         document.getElementById('editStartDate').value || null,
        end_date:           document.getElementById('editEndDate').value || null,
        participation_fee:  parseInt(document.getElementById('editParticipationFee').value) || 0,
        pre_night_fee:      parseInt(document.getElementById('editPreNightFee').value) || 0,
        lunch_fee:          parseInt(document.getElementById('editLunchFee').value) || 0,
    };

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}`, {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(data),
        });
        const result = await res.json();
        if (result.success) {
            showToast('保存しました');
            document.getElementById('expeditionTitle').textContent = name;
        } else {
            alert(result.error?.message || '保存に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

// ==============================
// タブ2: 参加者管理
// ==============================
async function loadParticipants() {
    // 既にロード済みの場合はスキップ
    if (participantsLoaded) return;
    participantsLoaded = true;

    try {
        const res  = await fetch(`/api/expeditions/${expeditionId}/participants`);
        const data = await res.json();
        if (data.success) {
            participantsCache = data.data || [];
            renderParticipants(participantsCache);
        } else {
            document.getElementById('participantList').innerHTML = '<div class="alert alert-danger">読み込みに失敗しました</div>';
        }
    } catch (err) {
        console.error(err);
        document.getElementById('participantList').innerHTML = '<div class="alert alert-danger">通信エラーが発生しました</div>';
    }
}

function renderParticipants(participants) {
    document.getElementById('participantCount').textContent = participants.length;

    if (participants.length === 0) {
        document.getElementById('participantList').innerHTML = '<div class="alert alert-info">参加者はまだいません</div>';
        return;
    }

    const rows = participants.map(p => `
        <tr>
            <td>${escapeHtml(p.name)}</td>
            <td class="text-center">
                <input type="checkbox" ${p.pre_night ? 'checked' : ''}
                    onchange="updateParticipant(${p.id}, 'pre_night', this.checked)">
            </td>
            <td class="text-center">
                <input type="checkbox" ${p.lunch ? 'checked' : ''}
                    onchange="updateParticipant(${p.id}, 'lunch', this.checked)">
            </td>
            <td>
                <button class="btn btn-outline-danger btn-sm" onclick="deleteParticipant(${p.id})">削除</button>
            </td>
        </tr>
    `).join('');

    document.getElementById('participantList').innerHTML = `
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>名前</th>
                        <th class="text-center">前泊</th>
                        <th class="text-center">昼食</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function showAddParticipantModal() {
    document.getElementById('newParticipantMemberId').value = '';
    addParticipantModal.show();
}

async function addParticipant() {
    const memberId = document.getElementById('newParticipantMemberId').value;
    if (!memberId) {
        alert('会員IDを入力してください');
        return;
    }

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/participants`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ member_id: parseInt(memberId) }),
        });
        const result = await res.json();
        if (result.success) {
            addParticipantModal.hide();
            participantsLoaded = false;
            await loadParticipants();
            showToast('参加者を追加しました');
        } else {
            alert(result.error?.message || '追加に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function updateParticipant(pid, field, value) {
    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/participants/${pid}`, {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ [field]: value }),
        });
        const result = await res.json();
        if (!result.success) {
            alert(result.error?.message || '更新に失敗しました');
            // 再ロードして状態を元に戻す
            participantsLoaded = false;
            loadParticipants();
        } else {
            showToast('更新しました', 'success', 1500);
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function deleteParticipant(pid) {
    if (!confirm('この参加者を削除しますか？')) return;

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/participants/${pid}`, { method: 'DELETE' });
        const result = await res.json();
        if (result.success) {
            participantsLoaded = false;
            await loadParticipants();
            showToast('削除しました');
        } else {
            alert(result.error?.message || '削除に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

// ==============================
// タブ3: 車割
// ==============================
async function loadCars() {
    if (carsLoaded) return;
    carsLoaded = true;

    // 参加者キャッシュが空の場合は取得する
    if (participantsCache.length === 0) {
        try {
            const res  = await fetch(`/api/expeditions/${expeditionId}/participants`);
            const data = await res.json();
            if (data.success) participantsCache = data.data || [];
        } catch (err) {
            console.error(err);
        }
    }

    try {
        const res  = await fetch(`/api/expeditions/${expeditionId}/cars`);
        const data = await res.json();
        if (data.success) {
            renderCars(data.data || []);
        } else {
            document.getElementById('carList').innerHTML = '<div class="alert alert-danger">読み込みに失敗しました</div>';
        }
    } catch (err) {
        console.error(err);
        document.getElementById('carList').innerHTML = '<div class="alert alert-danger">通信エラーが発生しました</div>';
    }
}

function renderCars(cars) {
    if (cars.length === 0) {
        document.getElementById('carList').innerHTML = '<div class="alert alert-info">車がまだ登録されていません</div>';
        return;
    }

    const html = cars.map(car => {
        // 乗員リスト
        const membersHtml = (car.members || []).map(m => `
            <tr>
                <td>${escapeHtml(m.name)}</td>
                <td>
                    <select class="form-select form-select-sm" onchange="updateCarMember(${car.id}, ${m.id}, 'role', this.value)">
                        <option value="passenger" ${m.role === 'passenger' ? 'selected' : ''}>乗客</option>
                        <option value="driver"    ${m.role === 'driver'    ? 'selected' : ''}>ドライバー</option>
                    </select>
                </td>
                <td class="text-center">
                    <input type="checkbox" ${m.exclude_settlement ? 'checked' : ''}
                        onchange="updateCarMember(${car.id}, ${m.id}, 'exclude_settlement', this.checked)"
                        title="清算対象外">
                </td>
                <td>
                    <button class="btn btn-outline-danger btn-sm py-0" onclick="deleteCarMember(${car.id}, ${m.id})">削除</button>
                </td>
            </tr>
        `).join('');

        // 立替者リスト
        const payersHtml = (car.payers || []).map(p => `
            <tr>
                <td>${escapeHtml(p.name)}</td>
                <td>¥${Number(p.amount).toLocaleString()}</td>
                <td>
                    <button class="btn btn-outline-danger btn-sm py-0" onclick="deleteCarPayer(${car.id}, ${p.id})">削除</button>
                </td>
            </tr>
        `).join('');

        return `
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>${escapeHtml(car.name)}</strong>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteCar(${car.id})">車を削除</button>
                </div>
                <div class="card-body">
                    <p class="mb-1">
                        定員: ${car.capacity}人
                        レンタカー代: ¥${Number(car.rental_fee || 0).toLocaleString()}
                        高速代: ¥${Number(car.highway_fee || 0).toLocaleString()}
                    </p>

                    <h6 class="mt-3">乗員</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>名前</th>
                                    <th>役割</th>
                                    <th class="text-center" title="清算対象外">対象外</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>${membersHtml || '<tr><td colspan="4" class="text-muted">乗員なし</td></tr>'}</tbody>
                        </table>
                    </div>
                    <button class="btn btn-outline-primary btn-sm mb-3" onclick="showAddCarMemberModal(${car.id})">+ 乗員を追加</button>

                    <h6 class="mt-2">立替者</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>名前</th>
                                    <th>金額</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>${payersHtml || '<tr><td colspan="3" class="text-muted">立替者なし</td></tr>'}</tbody>
                        </table>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="showAddCarPayerModal(${car.id})">+ 立替者を追加</button>
                </div>
            </div>
        `;
    }).join('');

    document.getElementById('carList').innerHTML = html;
}

function showAddCarModal() {
    document.getElementById('newCarName').value        = '';
    document.getElementById('newCarCapacity').value    = '5';
    document.getElementById('newCarRentalFee').value   = '0';
    document.getElementById('newCarHighwayFee').value  = '0';
    addCarModal.show();
}

async function addCar() {
    const name = document.getElementById('newCarName').value.trim();
    if (!name) {
        alert('車名を入力してください');
        return;
    }

    const data = {
        name:        name,
        capacity:    parseInt(document.getElementById('newCarCapacity').value)   || 5,
        rental_fee:  parseInt(document.getElementById('newCarRentalFee').value)  || 0,
        highway_fee: parseInt(document.getElementById('newCarHighwayFee').value) || 0,
    };

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/cars`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(data),
        });
        const result = await res.json();
        if (result.success) {
            addCarModal.hide();
            carsLoaded = false;
            await loadCars();
            showToast('車を追加しました');
        } else {
            alert(result.error?.message || '追加に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function deleteCar(cid) {
    if (!confirm('この車を削除しますか？')) return;

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/cars/${cid}`, { method: 'DELETE' });
        const result = await res.json();
        if (result.success) {
            carsLoaded = false;
            await loadCars();
            showToast('削除しました');
        } else {
            alert(result.error?.message || '削除に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function showAddCarMemberModal(cid) {
    currentCarId = cid;
    // 参加者リストをセレクトに反映
    const sel = document.getElementById('carMemberSelect');
    sel.innerHTML = participantsCache.map(p =>
        `<option value="${p.id}">${escapeHtml(p.name)}</option>`
    ).join('');
    document.getElementById('carMemberRole').value = 'passenger';
    addCarMemberModal.show();
}

async function addCarMember() {
    const pid  = document.getElementById('carMemberSelect').value;
    const role = document.getElementById('carMemberRole').value;
    if (!pid) { alert('参加者を選択してください'); return; }

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/cars/${currentCarId}/members`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ participant_id: parseInt(pid), role }),
        });
        const result = await res.json();
        if (result.success) {
            addCarMemberModal.hide();
            carsLoaded = false;
            await loadCars();
            showToast('乗員を追加しました');
        } else {
            alert(result.error?.message || '追加に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function updateCarMember(cid, mid, field, value) {
    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/cars/${cid}/members/${mid}`, {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ [field]: value }),
        });
        const result = await res.json();
        if (!result.success) {
            alert(result.error?.message || '更新に失敗しました');
            carsLoaded = false;
            loadCars();
        } else {
            showToast('更新しました', 'success', 1500);
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function deleteCarMember(cid, mid) {
    if (!confirm('乗員を削除しますか？')) return;

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/cars/${cid}/members/${mid}`, { method: 'DELETE' });
        const result = await res.json();
        if (result.success) {
            carsLoaded = false;
            await loadCars();
            showToast('削除しました');
        } else {
            alert(result.error?.message || '削除に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function showAddCarPayerModal(cid) {
    currentCarId = cid;
    const sel = document.getElementById('carPayerSelect');
    sel.innerHTML = participantsCache.map(p =>
        `<option value="${p.id}">${escapeHtml(p.name)}</option>`
    ).join('');
    document.getElementById('carPayerAmount').value = '0';
    addCarPayerModal.show();
}

async function addCarPayer() {
    const pid    = document.getElementById('carPayerSelect').value;
    const amount = parseInt(document.getElementById('carPayerAmount').value) || 0;
    if (!pid) { alert('参加者を選択してください'); return; }

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/cars/${currentCarId}/payers`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ participant_id: parseInt(pid), amount }),
        });
        const result = await res.json();
        if (result.success) {
            addCarPayerModal.hide();
            carsLoaded = false;
            await loadCars();
            showToast('立替者を追加しました');
        } else {
            alert(result.error?.message || '追加に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function deleteCarPayer(cid, pid) {
    if (!confirm('立替者を削除しますか？')) return;

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/cars/${cid}/payers/${pid}`, { method: 'DELETE' });
        const result = await res.json();
        if (result.success) {
            carsLoaded = false;
            await loadCars();
            showToast('削除しました');
        } else {
            alert(result.error?.message || '削除に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

// 清算計算
async function calcSettlement() {
    const el = document.getElementById('settlementResult');
    el.innerHTML = '<div class="text-muted">計算中...</div>';

    try {
        const res  = await fetch(`/api/expeditions/${expeditionId}/cars/settlement`);
        const data = await res.json();
        if (data.success) {
            renderSettlement(data.data);
        } else {
            el.innerHTML = '<div class="alert alert-danger">計算に失敗しました</div>';
        }
    } catch (err) {
        el.innerHTML = '<div class="alert alert-danger">通信エラーが発生しました</div>';
    }
}

function renderSettlement(settlement) {
    const el = document.getElementById('settlementResult');

    const detailRows = (settlement.details || []).map(d => {
        const amount = d.amount;
        const badge  = amount >= 0
            ? `<span class="badge bg-danger">支払い ¥${Number(amount).toLocaleString()}</span>`
            : `<span class="badge bg-success">返金 ¥${Number(-amount).toLocaleString()}</span>`;
        return `<tr><td>${escapeHtml(d.name)}</td><td>${badge}</td></tr>`;
    }).join('');

    el.innerHTML = `
        <div class="card">
            <div class="card-header">清算結果</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>総費用:</strong> ¥${Number(settlement.total_cost || 0).toLocaleString()}</div>
                    <div class="col-md-4"><strong>対象人数:</strong> ${settlement.target_count || 0}人</div>
                    <div class="col-md-4"><strong>1人あたり:</strong> ¥${Number(settlement.per_person || 0).toLocaleString()}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr><th>名前</th><th>支払い/返金</th></tr>
                        </thead>
                        <tbody>${detailRows || '<tr><td colspan="2" class="text-muted">データなし</td></tr>'}</tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

// ==============================
// タブ4: チーム分け
// ==============================
async function loadTeams() {
    if (teamsLoaded) return;
    teamsLoaded = true;

    try {
        const res  = await fetch(`/api/expeditions/${expeditionId}/teams`);
        const data = await res.json();
        if (data.success) {
            renderTeams(data.data);
        } else {
            document.getElementById('teamBoard').innerHTML = '<div class="alert alert-danger">読み込みに失敗しました</div>';
        }
    } catch (err) {
        console.error(err);
        document.getElementById('teamBoard').innerHTML = '<div class="alert alert-danger">通信エラーが発生しました</div>';
    }
}

function renderTeams(teamsData) {
    const board = document.getElementById('teamBoard');

    // 未割り当てエリア + 各チームエリアをカードで横並び
    const unassigned = teamsData.unassigned || [];
    const teams      = teamsData.teams      || [];

    let html = '';

    // 未割り当てカード
    html += `
        <div class="card" style="min-width:200px;">
            <div class="card-header fw-bold">未割り当て</div>
            <ul class="list-group list-group-flush sortable-team" id="team-unassigned" data-team-id="">
                ${unassigned.map(p => `
                    <li class="list-group-item py-1 px-2" data-participant-id="${p.id}">${escapeHtml(p.name)}</li>
                `).join('')}
            </ul>
        </div>
    `;

    // 各チームカード
    teams.forEach(team => {
        html += `
            <div class="card" style="min-width:200px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="team-name" onclick="startEditTeamName(${team.id}, this)"
                          style="cursor:pointer;" title="クリックで編集">${escapeHtml(team.name)}</span>
                </div>
                <ul class="list-group list-group-flush sortable-team" id="team-${team.id}" data-team-id="${team.id}">
                    ${(team.participants || []).map(p => `
                        <li class="list-group-item py-1 px-2" data-participant-id="${p.id}">${escapeHtml(p.name)}</li>
                    `).join('')}
                </ul>
            </div>
        `;
    });

    board.innerHTML = html;

    // SortableJS で各エリアをD&D有効化
    document.querySelectorAll('.sortable-team').forEach(el => {
        Sortable.create(el, {
            group:     'teams',
            animation: 150,
            onEnd:     saveTeamOrder,
        });
    });
}

// D&D完了時に並び順をAPIへ送信
async function saveTeamOrder() {
    const order = [];

    document.querySelectorAll('.sortable-team').forEach(el => {
        const teamId = el.dataset.teamId || null;
        el.querySelectorAll('li[data-participant-id]').forEach(li => {
            order.push({
                participant_id: parseInt(li.dataset.participantId),
                team_id:        teamId ? parseInt(teamId) : null,
            });
        });
    });

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/teams/order`, {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ order }),
        });
        const result = await res.json();
        if (!result.success) {
            console.error('並び順の保存に失敗:', result.error?.message);
        }
    } catch (err) {
        console.error('並び順保存エラー:', err);
    }
}

function showAddTeamModal() {
    document.getElementById('newTeamName').value = '';
    addTeamModal.show();
}

async function addTeam() {
    const name = document.getElementById('newTeamName').value.trim();
    if (!name) { alert('チーム名を入力してください'); return; }

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/teams`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ name }),
        });
        const result = await res.json();
        if (result.success) {
            addTeamModal.hide();
            teamsLoaded = false;
            await loadTeams();
            showToast('チームを追加しました');
        } else {
            alert(result.error?.message || '追加に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

// チーム名のインライン編集
function startEditTeamName(teamId, el) {
    const currentName = el.textContent.trim();
    el.innerHTML = `
        <input type="text" class="form-control form-control-sm" value="${escapeHtml(currentName)}"
               onblur="saveTeamName(${teamId}, this)"
               onkeydown="if(event.key==='Enter') this.blur(); if(event.key==='Escape') cancelTeamName(${teamId}, this, '${escapeHtml(currentName)}')">
    `;
    el.querySelector('input').focus();
}

async function saveTeamName(teamId, input) {
    const name = input.value.trim();
    if (!name) {
        input.value = input.dataset.original || '';
        return;
    }

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/teams/${teamId}`, {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ name }),
        });
        const result = await res.json();
        if (result.success) {
            // インプットをテキストに戻す
            const span = input.parentElement;
            span.innerHTML = escapeHtml(name);
            span.onclick = () => startEditTeamName(teamId, span);
            showToast('チーム名を更新しました', 'success', 1500);
        } else {
            alert(result.error?.message || '更新に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function cancelTeamName(teamId, input, original) {
    const span = input.parentElement;
    span.innerHTML = original;
    span.onclick = () => startEditTeamName(teamId, span);
}

// ==============================
// タブ5: 申し込みURL
// ==============================
async function loadApplicationUrl() {
    if (applicationLoaded) return;
    applicationLoaded = true;

    const content = document.getElementById('applicationUrlContent');

    try {
        const res  = await fetch(`/api/expeditions/${expeditionId}/application-url`);
        const data = await res.json();
        if (data.success) {
            renderApplicationUrl(data.data);
        } else {
            content.innerHTML = '<div class="alert alert-danger">読み込みに失敗しました</div>';
        }
    } catch (err) {
        console.error(err);
        content.innerHTML = '<div class="alert alert-danger">通信エラーが発生しました</div>';
    }
}

function renderApplicationUrl(data) {
    const content = document.getElementById('applicationUrlContent');

    let html = '<h4 class="mb-3">申し込みURL管理</h4>';

    if (data.has_token) {
        const token     = data.token;
        const isActive  = token.is_active == 1;
        const hasDeadline = token.deadline !== null;
        const isExpired = hasDeadline && new Date(token.deadline) < new Date();

        html += `
            <div class="card mb-3">
                <div class="card-header bg-light"><strong>現在の申し込みURL</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">URL:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="${escapeHtml(data.url)}" id="applicationUrlInput" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyApplicationUrl()">
                                <i class="bi bi-clipboard"></i> コピー
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ステータス:</label>
                        <div>
                            ${isActive && !isExpired
                                ? '<span class="badge bg-success">有効</span>'
                                : '<span class="badge bg-secondary">無効</span>'}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">有効期限:</label>
                        <p class="mb-0">${token.deadline ? escapeHtml(token.deadline) : '無期限'}</p>
                    </div>
                    <button class="btn btn-outline-warning" onclick="reissueApplicationUrl()">
                        <i class="bi bi-arrow-clockwise"></i> URLを再発行
                    </button>
                </div>
            </div>
            <div class="alert alert-warning">
                <strong><i class="bi bi-exclamation-triangle"></i> 注意</strong><br>
                新しいURLを発行すると、古いURLは無効になります。
            </div>
        `;
    } else {
        html += `
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-link-45deg" style="font-size: 3rem; color: #6c757d;"></i>
                    <h5 class="mt-3">申し込みURLが未発行です</h5>
                    <p class="text-muted">URLを発行すると、会員が遠征に申し込めるようになります。</p>
                    <button class="btn btn-primary" onclick="reissueApplicationUrl()">
                        <i class="bi bi-plus-circle"></i> 申し込みURLを発行
                    </button>
                </div>
            </div>
        `;
    }

    content.innerHTML = html;
}

function copyApplicationUrl() {
    const input = document.getElementById('applicationUrlInput');
    input.select();
    document.execCommand('copy');
    showToast('URLをコピーしました');
}

async function reissueApplicationUrl() {
    if (!confirm('URLを発行/再発行しますか？\n既存のURLがある場合は無効になります。')) return;

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/application-url`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({}),
        });
        const result = await res.json();
        if (result.success) {
            showToast('URLを発行しました');
            applicationLoaded = false;
            await loadApplicationUrl();
        } else {
            alert(result.error?.message || '発行に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

// ==============================
// タブ6: 集金管理
// ==============================
async function loadCollection() {
    if (collectionLoaded) return;
    collectionLoaded = true;

    const content = document.getElementById('collectionContent');

    try {
        const res  = await fetch(`/api/expeditions/${expeditionId}/collection`);
        const data = await res.json();
        if (data.success) {
            renderCollection(data.data);
        } else {
            content.innerHTML = '<div class="alert alert-danger">読み込みに失敗しました</div>';
        }
    } catch (err) {
        console.error(err);
        content.innerHTML = '<div class="alert alert-danger">通信エラーが発生しました</div>';
    }
}

function renderCollection(data) {
    const content = document.getElementById('collectionContent');

    // 第1回・第2回をアコーディオン形式で表示
    const rounds = [
        { id: 1, label: '第1回集金（遠征前）',  items: data.round1 || [] },
        { id: 2, label: '第2回集金（遠征後）', items: data.round2 || [] },
    ];

    let accordionHtml = rounds.map(round => {
        const tableRows = round.items.map(item => {
            const amountDisplay = item.amount < 0
                ? `<span class="text-success">返金 ¥${Number(-item.amount).toLocaleString()}</span>`
                : `¥${Number(item.amount).toLocaleString()}`;

            return `
                <tr>
                    <td>${escapeHtml(item.name)}</td>
                    <td>${amountDisplay}</td>
                    <td class="text-center">
                        <input type="checkbox" ${item.paid ? 'checked' : ''}
                            onchange="updateCollectionItem(${item.id}, this.checked)">
                    </td>
                </tr>
            `;
        }).join('');

        const tableHtml = round.items.length > 0 ? `
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>名前</th>
                            <th>金額</th>
                            <th class="text-center">支払い済み</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                </table>
            </div>
        ` : '<p class="text-muted mb-2">データがありません。「データ生成」ボタンで生成してください。</p>';

        return `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button ${round.id === 1 ? '' : 'collapsed'}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseRound${round.id}">
                        ${escapeHtml(round.label)}
                        <span class="badge bg-secondary ms-2">${round.items.length}件</span>
                    </button>
                </h2>
                <div id="collapseRound${round.id}" class="accordion-collapse collapse ${round.id === 1 ? 'show' : ''}">
                    <div class="accordion-body">
                        ${tableHtml}
                        <button class="btn btn-outline-primary btn-sm" onclick="generateCollection(${round.id})">
                            <i class="bi bi-arrow-clockwise"></i> データ生成
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    content.innerHTML = `
        <h4 class="mb-3">集金管理</h4>
        <div class="accordion">${accordionHtml}</div>
    `;
}

async function generateCollection(round) {
    if (!confirm(`第${round}回集金のデータを生成しますか？\n既存のデータは上書きされます。`)) return;

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/collection/generate`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ round }),
        });
        const result = await res.json();
        if (result.success) {
            showToast('データを生成しました');
            collectionLoaded = false;
            await loadCollection();
        } else {
            alert(result.error?.message || '生成に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function updateCollectionItem(iid, paid) {
    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/collection/items/${iid}`, {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ paid }),
        });
        const result = await res.json();
        if (!result.success) {
            alert(result.error?.message || '更新に失敗しました');
            collectionLoaded = false;
            loadCollection();
        } else {
            showToast('更新しました', 'success', 1500);
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

// ==============================
// タブ7: しおり
// ==============================
async function loadBooklet() {
    if (bookletLoaded) return;
    bookletLoaded = true;

    try {
        const res  = await fetch(`/api/expeditions/${expeditionId}/booklet`);
        const data = await res.json();
        if (data.success) {
            renderBooklet(data.data);
        } else {
            document.getElementById('bookletContent').innerHTML = '<div class="alert alert-danger">読み込みに失敗しました</div>';
        }
    } catch (err) {
        console.error(err);
        document.getElementById('bookletContent').innerHTML = '<div class="alert alert-danger">通信エラーが発生しました</div>';
    }
}

function renderBooklet(b) {
    const publicToken = b.public_token || '';
    const publicUrl   = publicToken ? `/public/expedition-booklet/${publicToken}` : '';

    document.getElementById('bookletContent').innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">しおり編集</h4>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="saveBooklet()">
                    <i class="bi bi-save"></i> 保存
                </button>
                <button class="btn btn-primary btn-sm" onclick="publishBooklet()">
                    <i class="bi bi-globe2"></i> 公開
                </button>
            </div>
        </div>

        ${publicUrl ? `
        <div class="alert alert-success mb-3">
            <strong>公開URL:</strong>
            <div class="input-group input-group-sm mt-1">
                <input type="text" class="form-control" value="${escapeHtml(window.location.origin + publicUrl)}" readonly id="bookletPublicUrlInput">
                <button class="btn btn-outline-secondary" onclick="copyBookletUrl()">
                    <i class="bi bi-clipboard"></i> コピー
                </button>
            </div>
        </div>
        ` : ''}

        <div class="mb-3">
            <label class="form-label fw-bold">持ち物</label>
            <textarea class="form-control" id="bookletItemsToBring" rows="5">${escapeHtml(b.items_to_bring || '')}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">車割</label>
            <textarea class="form-control" id="bookletCarAssignment" rows="5">${escapeHtml(b.car_assignment || '')}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">チーム割</label>
            <textarea class="form-control" id="bookletTeamAssignment" rows="5">${escapeHtml(b.team_assignment || '')}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">部屋割</label>
            <textarea class="form-control" id="bookletRoomAssignment" rows="5">${escapeHtml(b.room_assignment || '')}</textarea>
        </div>
    `;
}

async function saveBooklet() {
    const data = {
        items_to_bring:  document.getElementById('bookletItemsToBring')?.value  || '',
        car_assignment:  document.getElementById('bookletCarAssignment')?.value  || '',
        team_assignment: document.getElementById('bookletTeamAssignment')?.value || '',
        room_assignment: document.getElementById('bookletRoomAssignment')?.value || '',
    };

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/booklet`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(data),
        });
        const result = await res.json();
        if (result.success) {
            showToast('保存しました');
        } else {
            alert(result.error?.message || '保存に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function publishBooklet() {
    if (!confirm('しおりを公開しますか？')) return;

    try {
        const res    = await fetch(`/api/expeditions/${expeditionId}/booklet/publish`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({}),
        });
        const result = await res.json();
        if (result.success) {
            showToast('公開しました');
            bookletLoaded = false;
            await loadBooklet();
        } else {
            alert(result.error?.message || '公開に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function copyBookletUrl() {
    const input = document.getElementById('bookletPublicUrlInput');
    if (!input) return;
    input.select();
    document.execCommand('copy');
    showToast('URLをコピーしました');
}
</script>
