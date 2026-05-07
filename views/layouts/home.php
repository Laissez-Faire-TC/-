<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? '早稲田大学 Laissez-Faire T.C. | 公式サイト') ?></title>
  <meta name="description" content="早稲田大学公認テニスサークル Laissez-Faire T.C. の公式ホームページです。活動内容や新歓情報を掲載しています。">
  <meta name="google-site-verification" content="HgQ8mW8b2QZYtcNvrt0W9DnAColbd8t52PCl7meKhbw" />
  <!-- OGP -->
  <meta property="og:title" content="早稲田大学 Laissez-Faire T.C. 公式サイト">
  <meta property="og:description" content="早大公認テニスサークル、レッセフェールの活動情報はこちら！">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://laissez-faire-tc.com/">
  <meta property="og:image" content="https://laissez-faire-tc.com/public/images/top.jpg">
  <meta name="twitter:card" content="summary_large_image">
  <link rel="canonical" href="https://laissez-faire-tc.com/">
  <link rel="icon" type="image/jpeg" href="/public/images/favicon.jpg">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Noto+Serif+JP:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/home.css">
</head>
<body>
<?= $content ?>
<script src="/public/js/home.js"></script>
</body>
</html>
