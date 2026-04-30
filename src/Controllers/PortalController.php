<?php
/**
 * 会員ポータルコントローラー（公開ページ）
 */
class PortalController
{
    /**
     * ポータルトップページ表示
     */
    public function index(array $params): void
    {
        // 募集中の合宿を取得
        $campTokenModel = new CampToken();
        $activeCamps = $campTokenModel->getActiveCampsWithTokens();

        // 新規入会・継続入会の受付状態を独立して取得
        $ayModel      = new AcademicYear();
        $enrollOpen   = $ayModel->getEnrollOpenYear();
        $renewOpen    = $ayModel->getRenewOpenYear();
        $today        = date('Y-m-d');

        // 期限切れチェック
        $enrollActive = $enrollOpen !== null
            && (empty($enrollOpen['enrollment_deadline']) || $enrollOpen['enrollment_deadline'] >= $today);
        $renewActive  = $renewOpen  !== null
            && (empty($renewOpen['renew_deadline'])       || $renewOpen['renew_deadline']       >= $today);

        $this->render('portal/index', [
            'activeCamps'  => $activeCamps,
            'enrollActive' => $enrollActive,
            'renewActive'  => $renewActive,
        ]);
    }

    /**
     * ビューのレンダリング
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        $config = require CONFIG_PATH . '/app.php';
        $appName = $config['name'];

        ob_start();
        include VIEWS_PATH . '/' . $view . '.php';
        $content = ob_get_clean();

        // 公開ページなので認証不要のレイアウト
        include VIEWS_PATH . '/layouts/public.php';
    }
}
