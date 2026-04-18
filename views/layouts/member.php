<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員ページ | Laissez-Faire T.C.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="/public/css/style.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .member-container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-white border-bottom shadow-sm">
        <div class="container" style="max-width: 800px;">
            <a class="navbar-brand fw-normal" href="/">
                Laissez-Faire T.C.
            </a>
            <?php if (!empty($memberName)): ?>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small"><?= htmlspecialchars($memberName) ?> さん</span>
                <button class="btn btn-outline-secondary btn-sm" onclick="memberLogout()">
                    <i class="bi bi-box-arrow-right"></i> ログアウト
                </button>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <main class="member-container">
        <?= $content ?>
    </main>

    <footer class="text-center text-muted py-4 mt-5 border-top">
        <small>&copy; <?= date('Y') ?> 早稲田大学 Laissez-Faire T.C.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    async function memberLogout() {
        await fetch('/index.php?route=api/member/logout', { method: 'POST' });
        window.location.href = '/portal';
    }
    </script>
</body>
</html>
