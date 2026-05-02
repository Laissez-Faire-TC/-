<?php
/**
 * 遠征しおりコントローラー
 */
class ExpeditionBookletController
{
    /**
     * しおり取得
     * GET /api/expeditions/{id}/booklet
     */
    public function getBooklet(array $params): void
    {
        Auth::requireAuth();

        $booklet = ExpeditionBooklet::findByExpedition($params['id']);
        if (!$booklet) {
            // しおりが存在しない場合は空データを返す
            Response::success([
                'items' => [
                    'packing_list'   => '',
                    'car_assignment' => '',
                    'team_assignment' => '',
                    'room_assignment' => ''
                ],
                'published' => false
            ]);
            return;
        }
        $booklet['items'] = json_decode($booklet['items'], true);
        Response::success($booklet);
    }

    /**
     * しおり保存（新規作成 or 更新）
     * POST /api/expeditions/{id}/booklet
     */
    public function saveBooklet(array $params): void
    {
        Auth::requireAuth();

        $data    = Request::only(['items']);
        $booklet = ExpeditionBooklet::save($params['id'], $data['items']);
        Response::success($booklet);
    }

    /**
     * しおり公開
     * POST /api/expeditions/{id}/booklet/publish
     */
    public function publishBooklet(array $params): void
    {
        Auth::requireAuth();

        $booklet = ExpeditionBooklet::publish($params['id']);
        Response::success($booklet);
    }

    /**
     * 公開しおり閲覧（認証不要）
     * GET /public/expedition-booklet/{token}
     */
    public function viewPublicBooklet(array $params): void
    {
        $booklet = ExpeditionBooklet::findByPublicToken($params['token']);
        if (!$booklet) Response::error('しおりが見つかりません', 404);
        $booklet['items'] = json_decode($booklet['items'], true);
        // 公開しおりビューをレンダリング（views/expeditions/booklet_public.php）
        $this->render('expeditions/booklet_public', ['booklet' => $booklet]);
    }

    /**
     * ビューのレンダリング
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        ob_start();
        include VIEWS_PATH . '/' . $view . '.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/public.php';
    }
}
