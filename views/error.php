<div class="card shadow">
    <div class="card-body p-4 text-center">
        <div class="mb-4">
            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
        </div>

        <h2 class="mb-3">エラー</h2>

        <div class="alert alert-danger">
            <?= htmlspecialchars($message ?? 'エラーが発生しました') ?>
        </div>

        <p class="text-muted">
            問題が解決しない場合は、幹事に連絡してください。
        </p>

        <a href="/member" class="btn btn-outline-secondary btn-sm mt-2">
            <i class="bi bi-house"></i> ホームに戻る
        </a>
    </div>
</div>
