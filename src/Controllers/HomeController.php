<?php
/**
 * 公開トップページコントローラー
 */
class HomeController
{
    public function index(array $params): void
    {
        $this->render('home', [
            'pageTitle' => '早稲田大学 Laissez-Faire T.C.（レッセフェール）| 公式サイト'
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);

        ob_start();
        include VIEWS_PATH . '/' . $view . '.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/home.php';
    }
}
