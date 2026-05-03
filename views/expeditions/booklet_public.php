<?php
/**
 * 遠征しおり公開ビュー
 * $booklet: ExpeditionBooklet::findByPublicToken() の結果（デコード済み）
 */

$b = $booklet;

function fmtDateExp(?string $d): string {
    if (!$d) return '';
    $t = strtotime($d);
    return $t ? date('Y年n月j日', $t) : $d;
}
function weekDayExp(?string $d): string {
    if (!$d) return '';
    $t = strtotime($d);
    return $t ? ['日','月','火','水','木','金','土'][date('w', $t)] : '';
}

$startDate = $b['start_date'] ?? null;
$endDate   = $b['end_date']   ?? null;

// チームメンバーのソート関数: OB/OG→4年→3年→2年→1年、OB/OGは入学年昇順
function sortBookletMembers(array $members): array {
    usort($members, function($a, $b) {
        $ga = (int)($a['grade'] ?? 1);
        $gb = (int)($b['grade'] ?? 1);
        // OB/OG(0)を先頭、現役は学年降順
        $rankA = $ga === 0 ? -1 : (5 - $ga);
        $rankB = $gb === 0 ? -1 : (5 - $gb);
        if ($rankA !== $rankB) return $rankA - $rankB;
        // OB/OG同士は入学年昇順（古い先輩が先）
        if ($ga === 0) {
            return ((int)($a['enrollment_year'] ?? 9999)) - ((int)($b['enrollment_year'] ?? 9999));
        }
        return 0;
    });
    return $members;
}

$hasMeeting  = !empty($b['venue']) || !empty($b['meeting_note']);
$hasItems    = !empty($b['items_to_bring']);
$hasSchedule = !empty($b['schedules']);
$hasCar      = !empty($dbCars)  || !empty($b['car_assignment']);
$hasTeam     = !empty($dbTeams) || !empty($b['team_assignment']);
$hasRoom     = !empty($b['room_assignments']);
$hasNotes    = !empty($b['notes']);

// ログイン済み会員の強調表示用（コントローラーから渡される、未ログイン時は 0 / ''）
$myMemberId = (int)($myMemberId ?? 0);
$myName     = (string)($myName ?? '');
$isLoggedIn = (bool)($isLoggedIn ?? false);
?>
<style>
.booklet-hero {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.75rem 1.5rem;
    margin-bottom: 1.5rem;
}
.booklet-hero .hero-label {
    font-size: .75rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: .4rem;
}
.booklet-hero h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: .25rem;
}
.booklet-hero .hero-date {
    font-size: .95rem;
    color: #6b7280;
}
.booklet-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin-bottom: 1rem;
    overflow: hidden;
}
.booklet-section-header {
    padding: .65rem 1rem;
    font-weight: 600;
    font-size: .9rem;
    color: #111827;
    border-bottom: 1px solid #e5e7eb;
    border-left: 3px solid #111827;
    display: flex;
    align-items: center;
    gap: .4rem;
    background: #f9fafb;
}
.booklet-section-header.collapsible {
    cursor: pointer;
    user-select: none;
}
.booklet-section-header.collapsible:hover { background: #f3f4f6; }
.collapse-chevron {
    margin-left: auto;
    font-size: .7rem;
    color: #9ca3af;
    transition: transform .2s;
}
.booklet-section-header.collapsible.collapsed .collapse-chevron {
    transform: rotate(-90deg);
}
.booklet-section-body { padding: 1rem; }
.schedule-table th {
    background: #f3f4f6;
    color: #374151;
    font-weight: 600;
}
.schedule-table td, .schedule-table th {
    padding: .4rem .75rem;
    border: 1px solid #e5e7eb;
    vertical-align: middle;
}
.items-list li {
    padding: .35rem 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: .93rem;
    color: #374151;
}
.items-list li.highlight { font-weight: 700; color: #b91c1c; }
.team-card {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
    height: 100%;
}
.team-card-header {
    background: #f3f4f6;
    padding: .4rem .75rem;
    font-weight: 600;
    font-size: .85rem;
    color: #374151;
}
.team-member {
    padding: .25rem .75rem;
    font-size: .88rem;
    border-top: 1px solid #f3f4f6;
    color: #374151;
}
.car-card {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
    height: 100%;
}
.car-card-header {
    background: #f3f4f6;
    padding: .4rem .75rem;
    font-weight: 600;
    font-size: .85rem;
    color: #374151;
    display: flex;
    align-items: center;
    gap: .35rem;
}
.room-table th {
    background: #f3f4f6;
    color: #374151;
    font-weight: 600;
    text-align: center;
}
.room-table td, .room-table th {
    padding: .35rem .6rem;
    border: 1px solid #e5e7eb;
    font-size: .88rem;
}
.pre-wrap { white-space: pre-wrap; word-break: break-word; font-family: inherit; font-size: .9rem; }
.nav-tabs .nav-link { color: #6b7280; font-size: .9rem; }
.nav-tabs .nav-link.active { font-weight: 600; color: #111827; }
.nav-tabs .nav-link:hover { color: #374151; }
/* 強調表示 */
.my-car .car-card-header,
.my-team-header { background: #eff6ff !important; border-left: 3px solid #3b82f6; }
.my-car .booklet-section-header { background: #eff6ff; border-left: 3px solid #3b82f6; }
.team-member.me,
.car-member-me td { background: #eff6ff; font-weight: 700; }
</style>

<!-- ヒーローヘッダー -->
<div class="booklet-hero mt-3">
    <div class="hero-label">Laissez-Faire T.C. 遠征しおり</div>
    <h2><?= htmlspecialchars($b['expedition_name'] ?? '遠征しおり') ?></h2>
    <?php if ($startDate && $endDate): ?>
    <div class="hero-date">
        <?= fmtDateExp($startDate) ?>（<?= weekDayExp($startDate) ?>）
        &ndash;
        <?= fmtDateExp($endDate) ?>（<?= weekDayExp($endDate) ?>）
    </div>
    <?php elseif ($startDate): ?>
    <div class="hero-date"><?= fmtDateExp($startDate) ?>（<?= weekDayExp($startDate) ?>）</div>
    <?php endif; ?>
</div>

<?php if (!$hasMeeting && !$hasItems && !$hasSchedule && !$hasCar && !$hasTeam && !$hasRoom && !$hasNotes): ?>
<div class="alert alert-info">しおりの内容はまだ登録されていません。</div>
<?php else: ?>

<!-- タブナビゲーション -->
<ul class="nav nav-tabs mb-3" id="bookletTabs" role="tablist">
    <?php if ($hasMeeting):  ?><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-meeting">集合</a></li><?php endif; ?>
    <?php if ($hasItems):    ?><li class="nav-item"><a class="nav-link <?= !$hasMeeting  ? 'active' : '' ?>" data-bs-toggle="tab" href="#tab-items">持ち物</a></li><?php endif; ?>
    <?php if ($hasSchedule): ?><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-schedule">日程</a></li><?php endif; ?>
    <?php if ($hasCar):      ?><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-car">車割</a></li><?php endif; ?>
    <?php if ($hasTeam):     ?><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-team">チーム</a></li><?php endif; ?>
    <?php if ($hasRoom):     ?><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-room">部屋割り</a></li><?php endif; ?>
    <?php if ($hasNotes):    ?><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-notes">備考</a></li><?php endif; ?>
</ul>

<div class="tab-content">

<!-- ========== 集合情報 ========== -->
<?php if ($hasMeeting): ?>
<div class="tab-pane fade show active" id="tab-meeting">
    <div class="booklet-section">
        <div class="booklet-section-header"><i class="bi bi-geo-alt"></i> 集合情報</div>
        <div class="booklet-section-body">
            <?php if (!empty($b['venue'])): ?>
            <div class="mb-3">
                <div class="text-muted small mb-1">開催場所</div>
                <div class="fs-6 fw-bold"><?= htmlspecialchars($b['venue']) ?></div>
                <!-- Google マップ -->
                <div class="ratio ratio-16x9 mt-2" style="border-radius:8px; overflow:hidden;">
                    <iframe src="https://maps.google.com/maps?q=<?= urlencode($b['venue']) ?>&output=embed"
                        style="border:0;" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
            <?php endif; ?>

            <div class="p-3 mb-2 rounded d-flex align-items-center gap-2"
                 style="background:#f0f9ff; border:1px solid #bae6fd; font-size:.9rem; color:#0369a1;">
                <i class="bi bi-car-front-fill fs-5"></i>
                <span>集合場所・集合時間は各車のドライバーに直接確認してください</span>
            </div>

            <?php if (!empty($b['meeting_note'])): ?>
            <div class="p-3 mt-2 rounded" style="background:#f9fafb; border:1px solid #e5e7eb; font-size:.88rem; color:#374151;">
                <?= nl2br(htmlspecialchars($b['meeting_note'])) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========== 持ち物 ========== -->
<?php if ($hasItems): ?>
<div class="tab-pane fade <?= !$hasMeeting ? 'show active' : '' ?>" id="tab-items">
    <div class="booklet-section">
        <div class="booklet-section-header"><i class="bi bi-bag-check"></i> 持ち物</div>
        <div class="booklet-section-body">
            <ul class="items-list list-unstyled mb-0">
                <?php foreach ($b['items_to_bring'] as $item): ?>
                <li class="<?= !empty($item['highlight']) ? 'highlight' : '' ?>">
                    <?= !empty($item['highlight'])
                        ? '<i class="bi bi-exclamation-circle-fill text-danger me-1"></i>'
                        : '・' ?>
                    <?= htmlspecialchars($item['text'] ?? '') ?>
                    <?php if (!empty($item['note'])): ?>
                    <span class="text-muted small">（<?= htmlspecialchars($item['note']) ?>）</span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========== タイムスケジュール ========== -->
<?php if ($hasSchedule): ?>
<div class="tab-pane fade" id="tab-schedule">
    <?php foreach ($b['schedules'] as $dayIdx => $day):
        $colId = 'scheduleDay' . $dayIdx; ?>
    <div class="booklet-section">
        <div class="booklet-section-header collapsible"
             data-bs-toggle="collapse"
             data-bs-target="#<?= $colId ?>"
             aria-expanded="true">
            <i class="bi bi-calendar3"></i>
            <?= htmlspecialchars($day['label'] ?? ($dayIdx + 1) . '日目の予定') ?>
            <span class="collapse-chevron">▼</span>
        </div>
        <div class="collapse show" id="<?= $colId ?>">
            <div class="booklet-section-body p-0">
                <table class="table table-sm schedule-table mb-0">
                    <thead>
                        <tr><th style="width:80px;">時間</th><th>予定</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($day['rows'] ?? [] as $row): ?>
                        <tr class="<?= !empty($row['highlight']) ? 'table-warning' : '' ?>">
                            <td class="fw-bold text-nowrap"><?= htmlspecialchars($row['time'] ?? '') ?></td>
                            <td>
                                <?= htmlspecialchars($row['activity'] ?? '') ?>
                                <?php if (!empty($row['note'])): ?>
                                <div class="small text-danger">（<?= htmlspecialchars($row['note']) ?>）</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ========== 車割 ========== -->
<?php if ($hasCar): ?>
<div class="tab-pane fade" id="tab-car">
    <?php if (!empty($dbCars)): ?>
    <?php
    $roleLabel    = ['driver' => 'ドライバー', 'sub_driver' => 'サブドライバー', 'passenger' => '乗客'];
    $outboundCars = array_values(array_filter($dbCars, fn($c) => ($c['trip_type'] ?? 'both') !== 'return'));
    $returnCars   = array_values(array_filter($dbCars, fn($c) => ($c['trip_type'] ?? 'both') === 'return'));

    // 車リスト描画クロージャ
    $renderCarGroup = function(array $cars) use ($roleLabel, $myMemberId): void {
        foreach ($cars as $car):
            $members  = $car['car_members'] ?? [];
            $isMyCar  = $myMemberId > 0 && count(array_filter($members, fn($m) => (int)$m['member_id'] === $myMemberId)) > 0;
    ?>
    <div class="booklet-section mb-2 <?= $isMyCar ? 'my-car' : '' ?>">
        <div class="booklet-section-header">
            <i class="bi bi-car-front-fill"></i>
            <?= htmlspecialchars($car['name']) ?>
            <span class="text-muted fw-normal ms-2" style="font-size:.8rem;">定員<?= (int)$car['capacity'] ?>名</span>
            <?php if ($isMyCar): ?><span class="badge bg-primary ms-2" style="font-size:.7rem;">あなたの車</span><?php endif; ?>
        </div>
        <?php if (empty($members)): ?>
        <div class="booklet-section-body text-muted" style="font-size:.88rem;">乗員未登録</div>
        <?php else: ?>
        <div class="booklet-section-body p-0">
            <table class="table table-sm mb-0" style="font-size:.9rem;">
                <tbody>
                    <?php foreach ($members as $m):
                        $isMe = $myMemberId > 0 && (int)$m['member_id'] === $myMemberId;
                    ?>
                    <tr class="<?= $isMe ? 'car-member-me' : '' ?>">
                        <td style="width:120px;" class="text-muted ps-3">
                            <?= htmlspecialchars($roleLabel[$m['role']] ?? $m['role']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($m['name_kanji'] ?? '') ?>
                            <?php if ($isMe): ?><i class="bi bi-star-fill text-warning ms-1" style="font-size:.75rem;"></i><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php
        endforeach;
    };
    ?>

    <?php if (!empty($outboundCars)): ?>
    <div class="fw-bold text-secondary mb-2" style="font-size:.85rem; border-bottom:1px solid #e5e7eb; padding-bottom:.25rem;">
        <i class="bi bi-arrow-right-circle"></i> 往路
    </div>
    <?php $renderCarGroup($outboundCars); ?>
    <?php endif; ?>

    <?php if (!empty($returnCars)): ?>
    <div class="fw-bold text-secondary mb-2 mt-3" style="font-size:.85rem; border-bottom:1px solid #e5e7eb; padding-bottom:.25rem;">
        <i class="bi bi-arrow-left-circle"></i> 復路
    </div>
    <?php $renderCarGroup($returnCars); ?>
    <?php endif; ?>

    <?php else: ?>
    <div class="booklet-section">
        <div class="booklet-section-header"><i class="bi bi-car-front"></i> 車割</div>
        <div class="booklet-section-body">
            <div class="pre-wrap"><?= htmlspecialchars($b['car_assignment'] ?? '') ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ========== チーム分け ========== -->
<?php if ($hasTeam): ?>
<div class="tab-pane fade" id="tab-team">
    <?php if (!empty($dbTeams)): ?>
    <?php foreach ($dbTeams as $team):
        $members   = $team['members'] ?? [];
        $females   = sortBookletMembers(array_values(array_filter($members, fn($m) => ($m['gender'] ?? '') === 'female')));
        $males     = sortBookletMembers(array_values(array_filter($members, fn($m) => ($m['gender'] ?? '') !== 'female')));
        $isMyTeam  = $myMemberId > 0 && count(array_filter($members, fn($m) => (int)$m['member_id'] === $myMemberId)) > 0;
    ?>
    <div class="booklet-section mb-2">
        <div class="booklet-section-header <?= $isMyTeam ? 'my-team-header' : '' ?>">
            <i class="bi bi-people-fill"></i>
            <?= htmlspecialchars($team['name']) ?>
            <span class="text-muted fw-normal ms-2" style="font-size:.8rem;"><?= count($members) ?>名</span>
            <?php if ($isMyTeam): ?><span class="badge bg-primary ms-2" style="font-size:.7rem;">あなたのチーム</span><?php endif; ?>
        </div>
        <?php if (empty($members)): ?>
        <div class="booklet-section-body text-muted" style="font-size:.88rem;">メンバー未登録</div>
        <?php else: ?>
        <div class="row g-0" style="border-top:1px solid #e5e7eb;">
            <!-- 女子（左） -->
            <div class="col-6" style="border-right:1px solid #e5e7eb;">
                <div class="px-3 py-1" style="font-size:.75rem; font-weight:600; color:#be185d; background:#fdf2f8; border-bottom:1px solid #fce7f3;">
                    <i class="bi bi-gender-female"></i> 女子（<?= count($females) ?>名）
                </div>
                <?php if (empty($females)): ?>
                <div class="px-3 py-2 text-muted" style="font-size:.85rem;">—</div>
                <?php else: ?>
                <?php foreach ($females as $m):
                    $isMe = $myMemberId > 0 && (int)$m['member_id'] === $myMemberId;
                ?>
                <div class="team-member <?= $isMe ? 'me' : '' ?>">
                    <?= htmlspecialchars($m['name_kanji'] ?? '') ?>
                    <?php if ($isMe): ?><i class="bi bi-star-fill text-warning ms-1" style="font-size:.75rem;"></i><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- 男子（右） -->
            <div class="col-6">
                <div class="px-3 py-1" style="font-size:.75rem; font-weight:600; color:#1d4ed8; background:#eff6ff; border-bottom:1px solid #dbeafe;">
                    <i class="bi bi-gender-male"></i> 男子（<?= count($males) ?>名）
                </div>
                <?php if (empty($males)): ?>
                <div class="px-3 py-2 text-muted" style="font-size:.85rem;">—</div>
                <?php else: ?>
                <?php foreach ($males as $m):
                    $isMe = $myMemberId > 0 && (int)$m['member_id'] === $myMemberId;
                ?>
                <div class="team-member <?= $isMe ? 'me' : '' ?>">
                    <?= htmlspecialchars($m['name_kanji'] ?? '') ?>
                    <?php if ($isMe): ?><i class="bi bi-star-fill text-warning ms-1" style="font-size:.75rem;"></i><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="booklet-section">
        <div class="booklet-section-header"><i class="bi bi-people"></i> チーム分け</div>
        <div class="booklet-section-body">
            <div class="pre-wrap"><?= htmlspecialchars($b['team_assignment'] ?? '') ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ========== 部屋割り ========== -->
<?php if ($hasRoom): ?>
<div class="tab-pane fade" id="tab-room">
    <div class="booklet-section">
        <div class="booklet-section-header"><i class="bi bi-door-open"></i> 部屋割り</div>
        <div class="booklet-section-body p-0">
            <table class="table table-sm room-table mb-0">
                <thead>
                    <tr>
                        <th>カテゴリ</th>
                        <th>部屋番号</th>
                        <th>目安人数</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($b['room_assignments'] as $cat):
                        $rooms  = $cat['rooms'] ?? [];
                        $rCount = max(1, count($rooms));
                    ?>
                    <?php if (empty($rooms)): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($cat['category'] ?? '') ?></td>
                        <td colspan="2" class="text-muted">—</td>
                    </tr>
                    <?php else:
                        foreach ($rooms as $ri => $room): ?>
                    <tr>
                        <?php if ($ri === 0): ?>
                        <td rowspan="<?= $rCount ?>" class="fw-bold align-middle"><?= htmlspecialchars($cat['category'] ?? '') ?></td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($room['room_no'] ?? '') ?></td>
                        <td class="text-center"><?= !empty($room['capacity']) ? $room['capacity'] . '人' : '—' ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========== 備考 ========== -->
<?php if ($hasNotes): ?>
<div class="tab-pane fade" id="tab-notes">
    <div class="booklet-section">
        <div class="booklet-section-header"><i class="bi bi-sticky"></i> 備考</div>
        <div class="booklet-section-body">
            <div class="pre-wrap"><?= htmlspecialchars($b['notes']) ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

</div><!-- /.tab-content -->
<?php endif; ?>

<div class="mt-4 pb-3" style="font-size:.8rem; color:#9ca3af; text-align:center;">
    ご不明な点は幹事長までご連絡ください
</div>

<script>
// 折りたたみ開閉でシェブロン回転
document.querySelectorAll('.booklet-section-header.collapsible').forEach(header => {
    const targetId = header.getAttribute('data-bs-target');
    if (!targetId) return;
    const pane = document.querySelector(targetId);
    if (!pane) return;
    pane.addEventListener('hide.bs.collapse', () => header.classList.add('collapsed'));
    pane.addEventListener('show.bs.collapse', () => header.classList.remove('collapsed'));
});
</script>
