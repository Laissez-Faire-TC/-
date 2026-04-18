<!-- ヘッダー -->
<div class="text-center mb-4 pt-3">
    <h2 class="fw-normal text-dark">Laissez-Faire T.C.</h2>
    <p class="text-muted mb-0">会員ポータル</p>
</div>

<!-- メニュー -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <a href="/member/login" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                        <i class="bi bi-box-arrow-in-right text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark">会員ログイン</h6>
                        <small class="text-muted">合宿申込・管理はこちら</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php if ($enrollActive ?? false): ?>
    <div class="col-md-6">
        <a href="/enroll" class="text-decoration-none">
            <div class="card h-100 border-success shadow-sm">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3">
                        <i class="bi bi-person-plus text-success" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark">新規入会</h6>
                        <small class="text-success fw-bold">受付中</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php else: ?>
    <div class="col-md-6">
        <div class="card h-100 border-0 bg-light opacity-75">
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-circle bg-secondary bg-opacity-10 p-2 me-3">
                    <i class="bi bi-person-plus text-secondary" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-muted">新規入会</h6>
                    <small class="text-muted">現在受付していません</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($renewActive ?? false): ?>
    <div class="col-md-6">
        <a href="/renew" class="text-decoration-none">
            <div class="card h-100 border-info shadow-sm">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                        <i class="bi bi-arrow-repeat text-info" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark">継続入会</h6>
                        <small class="text-info fw-bold">受付中</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php else: ?>
    <div class="col-md-6">
        <div class="card h-100 border-0 bg-light opacity-75">
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-circle bg-secondary bg-opacity-10 p-2 me-3">
                    <i class="bi bi-arrow-repeat text-secondary" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-muted">継続入会</h6>
                    <small class="text-muted">現在受付していません</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- 案内 -->
<div class="text-muted small text-center">
    <p class="mb-0">ご不明な点は幹事長までご連絡ください</p>
</div>
