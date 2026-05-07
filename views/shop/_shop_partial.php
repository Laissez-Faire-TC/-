<?php
/**
 * 物販ショップ共通パーシャル
 * 必須変数:
 *   $items     : Merchandise::findAvailable() の結果
 *   $mode      : 'member' | 'public'
 *   $token     : 公開URL用トークン（modeがpublicの場合のみ）
 *   $myOrders  : 会員の過去注文（modeがmemberの場合のみ）
 */
?>
<div class="pt-3 mb-3">
    <h4 class="fw-normal mb-1"><i class="bi bi-bag-heart"></i> Laissez-Faire ショップ</h4>
    <p class="text-muted small mb-0">サークルオリジナルアイテム販売</p>
</div>

<?php if (empty($items)): ?>
<div class="card mb-4">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-bag-x" style="font-size: 2rem;"></i>
        <p class="mt-2 mb-0">現在販売中の商品はありません</p>
    </div>
</div>
<?php else: ?>

<style>
    /* フローティングカートボタン */
    .floating-cart-btn {
        position: fixed;
        right: 1.25rem;
        bottom: 1.25rem;
        z-index: 1040;
        border-radius: 50rem;
        padding: 0.75rem 1.25rem;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
        display: none;
    }
    .floating-cart-btn.is-visible { display: inline-flex; align-items: center; gap: .5rem; }
    .floating-cart-btn .badge {
        background: white;
        color: var(--bs-primary);
        font-weight: 700;
    }
    /* 商品ページの最終余白（カートボタンと被らないように） */
    .shop-bottom-spacer { height: 5rem; }
</style>

<!-- フローティングカートボタン -->
<button type="button" id="floatingCartBtn" class="btn btn-primary floating-cart-btn"
        data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
    <i class="bi bi-cart-fill fs-5"></i>
    <span><span id="cartCount">0</span> 点</span>
    <span class="badge rounded-pill" id="cartTotalBadge">¥0</span>
</button>

<!-- カートオフキャンバス（右からスライドイン） -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas">
    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title"><i class="bi bi-cart"></i> カート</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <div id="cartItems" class="flex-grow-1 overflow-auto">
            <div class="text-center text-muted py-5">
                <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">カートは空です</p>
            </div>
        </div>
        <div class="p-3 border-top d-flex justify-content-between align-items-center bg-light">
            <strong>合計</strong>
            <strong class="fs-5 text-primary" id="cartTotal">¥0</strong>
        </div>
        <div class="p-3 border-top">
            <button type="button" class="btn btn-primary w-100" id="checkoutBtn" disabled>
                <i class="bi bi-credit-card"></i> 注文に進む
            </button>
        </div>
    </div>
</div>

<h6 class="text-uppercase text-muted fw-bold mb-3 small">商品一覧</h6>
<?php foreach ($items as $item): ?>
<?php
    $colors = $item['colors'] ?? [];
    $sizes  = $item['sizes']  ?? [];
    $firstImage = $colors[0]['image_path'] ?? null;
?>
<div class="card mb-3 shadow-sm">
    <?php if ($firstImage): ?>
    <img src="<?= htmlspecialchars($firstImage) ?>"
         id="img_<?= (int)$item['id'] ?>"
         class="card-img-top" style="max-height: 320px; object-fit: contain; background: #f1f3f5;"
         alt="<?= htmlspecialchars($item['name']) ?>">
    <?php endif; ?>
    <div class="card-body">
        <h5 class="card-title mb-1"><?= htmlspecialchars($item['name']) ?></h5>
        <p class="text-primary fw-bold mb-2">¥<?= number_format((int)$item['price']) ?></p>
        <?php if (!empty($item['description'])): ?>
        <p class="small text-muted"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($colors)): ?>
        <div class="mb-2">
            <label class="form-label small mb-1">色</label>
            <select class="form-select form-select-sm color-select"
                    data-merch="<?= (int)$item['id'] ?>">
                <?php foreach ($colors as $c): ?>
                <option value="<?= (int)$c['id'] ?>" data-image="<?= htmlspecialchars($c['image_path'] ?? '') ?>">
                    <?= htmlspecialchars($c['color_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <?php if (!empty($sizes)): ?>
        <div class="mb-2">
            <label class="form-label small mb-1">サイズ</label>
            <select class="form-select form-select-sm size-select" data-merch="<?= (int)$item['id'] ?>">
                <?php foreach ($sizes as $s): ?>
                <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['size_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="d-flex gap-2 align-items-end">
            <div>
                <label class="form-label small mb-1">数量</label>
                <input type="number" class="form-control form-control-sm qty-input"
                       data-merch="<?= (int)$item['id'] ?>"
                       value="1" min="1" max="20" style="width: 80px;">
            </div>
            <button type="button" class="btn btn-primary flex-grow-1 add-to-cart-btn"
                    data-merch="<?= (int)$item['id'] ?>"
                    data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>"
                    data-price="<?= (int)$item['price'] ?>">
                <i class="bi bi-cart-plus"></i> カートに追加
            </button>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (($mode ?? '') === 'member' && !empty($myOrders)): ?>
<h6 class="text-uppercase text-muted fw-bold mb-3 small mt-5">あなたの注文履歴</h6>
<?php foreach ($myOrders as $o): ?>
<div class="card mb-2">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted"><?= htmlspecialchars($o['created_at']) ?></small>
            <?php if ($o['payment_status'] === 'paid'): ?>
            <span class="badge bg-success">入金済</span>
            <?php elseif ($o['payment_status'] === 'cancelled'): ?>
            <span class="badge bg-secondary">キャンセル</span>
            <?php else: ?>
            <span class="badge bg-warning text-dark">未入金</span>
            <?php endif; ?>
        </div>
        <ul class="mt-1 mb-1 small">
            <?php foreach ($o['items'] as $it): ?>
            <li>
                <?= htmlspecialchars($it['merchandise_name']) ?>
                <?= !empty($it['color_name']) ? '／' . htmlspecialchars($it['color_name']) : '' ?>
                <?= !empty($it['size_name'])  ? '／' . htmlspecialchars($it['size_name'])  : '' ?>
                × <?= (int)$it['quantity'] ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="text-end small text-muted">合計 ¥<?= number_format((int)$o['total_amount']) ?></div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<div class="shop-bottom-spacer"></div>

<!-- チェックアウトモーダル -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ご注文確認</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="checkoutSummary" class="mb-3"></div>

                <?php if (!empty($memberName)): ?>
                <div class="mb-2 small text-muted">
                    <i class="bi bi-person-circle"></i> ご注文者: <strong><?= htmlspecialchars($memberName) ?></strong> さん
                </div>
                <?php endif; ?>

                <?php if (($mode ?? '') === 'pending'): ?>
                <div class="alert alert-warning small mb-3">
                    <i class="bi bi-info-circle"></i>
                    DBに学籍番号がまだ登録されていない方向けの購入フォームです。<br>
                    入力された学籍番号で、後日会員登録された際に自動で紐付けられます。
                </div>
                <div class="mb-2">
                    <label class="form-label">学籍番号 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="pendingStudentId" placeholder="例: 1Y25F158-5">
                    <div class="form-text small">半角・大文字で入力（自動変換されます）</div>
                </div>
                <div class="mb-2">
                    <label class="form-label">氏名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="pendingName" placeholder="例: 山田太郎">
                </div>
                <div class="mb-2">
                    <label class="form-label">LINE名</label>
                    <input type="text" class="form-control" id="pendingLineName" placeholder="LINE登録名">
                </div>
                <div class="mb-2">
                    <label class="form-label">電話番号</label>
                    <input type="tel" class="form-control" id="pendingPhone" placeholder="例: 090-1234-5678">
                    <div class="form-text small">LINE名・電話番号のいずれかは必須です</div>
                </div>
                <?php endif; ?>

                <div class="mb-2">
                    <label class="form-label">備考（任意）</label>
                    <textarea class="form-control" id="orderNotes" rows="2"></textarea>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle"></i>
                    お支払い方法（振込・手渡しなど）と商品のお渡し方法は、担当者の指示に従ってください。
                </div>
                <div id="checkoutErr" class="alert alert-danger d-none mt-2"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">戻る</button>
                <button class="btn btn-primary" id="submitBtn" onclick="submitCheckout()">注文を確定する</button>
            </div>
        </div>
    </div>
</div>

<!-- 注文完了モーダル -->
<div class="modal fade" id="completeModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-2">ご注文を承りました</h5>
                <p class="text-muted small mb-3">担当者から支払い方法・お渡し方法のご案内が届きます。</p>
                <div id="completeSummary" class="text-start small mb-3"></div>
                <button class="btn btn-primary" data-bs-dismiss="modal" onclick="location.reload()">閉じる</button>
            </div>
        </div>
    </div>
</div>

<script>
const SHOP_MODE = <?= json_encode($mode ?? 'member') ?>;
const SHOP_TOKEN = <?= json_encode($token ?? null) ?>;

let cart = []; // [{merchandise_id, color_id, size_id, color_name, size_name, name, unit_price, quantity}]

function cartKey(merchandise_id, color_id, size_id) {
    return `${merchandise_id}|${color_id ?? ''}|${size_id ?? ''}`;
}

document.addEventListener('DOMContentLoaded', () => {
    // 暫定購入フォーム: 学籍番号の自動変換（全角→半角・小文字→大文字）
    const studentInput = document.getElementById('pendingStudentId');
    if (studentInput) {
        studentInput.addEventListener('input', function () {
            const pos = this.selectionStart;
            this.value = this.value
                .replace(/[！-～]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
                .replace(/　/g, ' ')
                .toUpperCase();
            this.setSelectionRange(pos, pos);
        });
    }

    // 色変更で画像を切り替え
    document.querySelectorAll('.color-select').forEach(sel => {
        sel.addEventListener('change', () => {
            const merchId = sel.dataset.merch;
            const opt = sel.options[sel.selectedIndex];
            const img = document.getElementById('img_' + merchId);
            if (img && opt.dataset.image) img.src = opt.dataset.image;
        });
    });

    // カート追加ボタン
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            addToCart(
                parseInt(btn.dataset.merch),
                btn.dataset.name,
                parseInt(btn.dataset.price)
            );
            showAddedToast();
        });
    });

    // 「注文に進む」ボタン
    document.getElementById('checkoutBtn').addEventListener('click', () => {
        const oc = bootstrap.Offcanvas.getInstance(document.getElementById('cartOffcanvas'));
        if (oc) oc.hide();
        openCheckout();
    });
});

function showAddedToast() {
    let toast = document.getElementById('shopToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'shopToast';
        toast.className = 'position-fixed top-0 start-50 translate-middle-x mt-3 px-3 py-2 bg-dark text-white rounded shadow';
        toast.style.zIndex = '1080';
        toast.style.transition = 'opacity 0.3s';
        toast.innerHTML = '<i class="bi bi-check-circle"></i> カートに追加しました';
        document.body.appendChild(toast);
    }
    toast.style.opacity = '1';
    clearTimeout(toast._t);
    toast._t = setTimeout(() => { toast.style.opacity = '0'; }, 1500);
}

function addToCart(merchId, name, price) {
    const colorSel = document.querySelector(`.color-select[data-merch="${merchId}"]`);
    const sizeSel  = document.querySelector(`.size-select[data-merch="${merchId}"]`);
    const qtyInput = document.querySelector(`.qty-input[data-merch="${merchId}"]`);

    const colorId   = colorSel ? parseInt(colorSel.value) : null;
    const colorName = colorSel ? colorSel.options[colorSel.selectedIndex].textContent.trim() : null;
    const sizeId    = sizeSel  ? parseInt(sizeSel.value)  : null;
    const sizeName  = sizeSel  ? sizeSel.options[sizeSel.selectedIndex].textContent.trim()  : null;
    const qty       = Math.max(1, parseInt(qtyInput.value) || 1);

    const key = cartKey(merchId, colorId, sizeId);
    const existing = cart.find(c => cartKey(c.merchandise_id, c.color_id, c.size_id) === key);

    if (existing) {
        existing.quantity += qty;
    } else {
        cart.push({
            merchandise_id: merchId,
            color_id:       colorId,
            size_id:        sizeId,
            color_name:     colorName,
            size_name:      sizeName,
            name:           name,
            unit_price:     price,
            quantity:       qty,
        });
    }
    renderCart();
}

function removeFromCart(key) {
    cart = cart.filter(c => cartKey(c.merchandise_id, c.color_id, c.size_id) !== key);
    renderCart();
}

function changeQty(key, delta) {
    const c = cart.find(c => cartKey(c.merchandise_id, c.color_id, c.size_id) === key);
    if (!c) return;
    c.quantity = Math.max(1, c.quantity + delta);
    renderCart();
}

function renderCart() {
    const floatBtn   = document.getElementById('floatingCartBtn');
    const itemsEl    = document.getElementById('cartItems');
    const totalEl    = document.getElementById('cartTotal');
    const totalBadge = document.getElementById('cartTotalBadge');
    const countEl    = document.getElementById('cartCount');
    const checkoutBtn = document.getElementById('checkoutBtn');

    const totalQty = cart.reduce((a, c) => a + c.quantity, 0);
    let total = 0;

    if (cart.length === 0) {
        itemsEl.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">カートは空です</p>
            </div>`;
        totalEl.textContent    = '¥0';
        totalBadge.textContent = '¥0';
        countEl.textContent    = '0';
        checkoutBtn.disabled   = true;
        floatBtn.classList.remove('is-visible');
        return;
    }

    itemsEl.innerHTML = cart.map(c => {
        const sub = c.unit_price * c.quantity;
        total += sub;
        const key = cartKey(c.merchandise_id, c.color_id, c.size_id);
        return `
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <div class="flex-grow-1 me-2">
                <div class="small fw-bold">${escapeHtml(c.name)}</div>
                <div class="small text-muted">
                    ${c.color_name ? escapeHtml(c.color_name) : ''}${c.color_name && c.size_name ? '／' : ''}${c.size_name ? escapeHtml(c.size_name) : ''}
                </div>
                <div class="d-flex align-items-center gap-1 mt-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 cart-qty-minus" data-key="${key}">−</button>
                    <span class="px-2">${c.quantity}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 cart-qty-plus" data-key="${key}">＋</button>
                </div>
            </div>
            <div class="text-end">
                <div class="small">¥${Number(sub).toLocaleString()}</div>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 cart-remove" data-key="${key}">削除</button>
            </div>
        </div>`;
    }).join('');

    totalEl.textContent    = '¥' + total.toLocaleString();
    totalBadge.textContent = '¥' + total.toLocaleString();
    countEl.textContent    = totalQty;
    checkoutBtn.disabled   = false;
    floatBtn.classList.add('is-visible');

    document.querySelectorAll('.cart-qty-minus').forEach(b => b.addEventListener('click', () => changeQty(b.dataset.key, -1)));
    document.querySelectorAll('.cart-qty-plus').forEach(b => b.addEventListener('click', () => changeQty(b.dataset.key, 1)));
    document.querySelectorAll('.cart-remove').forEach(b => b.addEventListener('click', () => removeFromCart(b.dataset.key)));
}

function openCheckout() {
    if (cart.length === 0) return;
    let total = 0;
    document.getElementById('checkoutSummary').innerHTML = `
        <ul class="list-unstyled small">
            ${cart.map(c => {
                const sub = c.unit_price * c.quantity;
                total += sub;
                return `<li class="d-flex justify-content-between border-bottom py-1">
                    <span>${escapeHtml(c.name)}${c.color_name ? '／' + escapeHtml(c.color_name) : ''}${c.size_name ? '／' + escapeHtml(c.size_name) : ''} × ${c.quantity}</span>
                    <span>¥${Number(sub).toLocaleString()}</span>
                </li>`;
            }).join('')}
            <li class="d-flex justify-content-between pt-2 fw-bold">
                <span>合計</span>
                <span class="text-primary fs-5">¥${Number(total).toLocaleString()}</span>
            </li>
        </ul>
    `;
    document.getElementById('checkoutErr').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('checkoutModal')).show();
}

async function submitCheckout() {
    const btn = document.getElementById('submitBtn');
    const err = document.getElementById('checkoutErr');
    err.classList.add('d-none');
    btn.disabled = true;

    const body = {
        cart: cart.map(c => ({
            merchandise_id: c.merchandise_id,
            color_id:       c.color_id,
            size_id:        c.size_id,
            quantity:       c.quantity,
        })),
        notes: document.getElementById('orderNotes').value.trim(),
    };

    let url;
    if (SHOP_MODE === 'pending') {
        const studentId = document.getElementById('pendingStudentId').value.trim();
        const pname     = document.getElementById('pendingName').value.trim();
        const lineName  = document.getElementById('pendingLineName').value.trim();
        const phone     = document.getElementById('pendingPhone').value.trim();
        if (!studentId) {
            err.textContent = '学籍番号を入力してください';
            err.classList.remove('d-none');
            btn.disabled = false;
            return;
        }
        if (!pname) {
            err.textContent = '氏名を入力してください';
            err.classList.remove('d-none');
            btn.disabled = false;
            return;
        }
        if (!lineName && !phone) {
            err.textContent = 'LINE名または電話番号を入力してください';
            err.classList.remove('d-none');
            btn.disabled = false;
            return;
        }
        body.student_id = studentId;
        body.name       = pname;
        body.line_name  = lineName;
        body.phone      = phone;
        url = '/api/store/pending/checkout';
    } else if (SHOP_TOKEN) {
        url = `/api/store/${SHOP_TOKEN}/checkout`;
    } else {
        url = '/api/member/store/checkout';
    }

    try {
        const res  = await fetch(url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(body),
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
            document.getElementById('completeSummary').innerHTML = `
                <div>注文番号: #${data.data.id}</div>
                <div>合計: ¥${Number(data.data.total_amount).toLocaleString()}</div>
            `;
            new bootstrap.Modal(document.getElementById('completeModal')).show();
            cart = [];
            renderCart();
        } else {
            err.textContent = data.error?.message || '注文に失敗しました';
            err.classList.remove('d-none');
            btn.disabled = false;
        }
    } catch (e) {
        err.textContent = '通信エラーが発生しました';
        err.classList.remove('d-none');
        btn.disabled = false;
    }
}

function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s ?? '';
    return div.innerHTML;
}
</script>
