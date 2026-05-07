<?php
/**
 * 認証コントローラー
 */
class AuthController
{
    /**
     * ログイン
     */
    public function login(array $params): void
    {
        $password = Request::get('password');

        if (empty($password)) {
            Response::error('パスワードを入力してください', 400, 'VALIDATION_ERROR');
        }

        if (Auth::login($password)) {
            Response::success([], 'ログインしました');
        } else {
            Response::error('パスワードが正しくありません', 401, 'INVALID_PASSWORD');
        }
    }

    /**
     * ログアウト
     */
    public function logout(array $params): void
    {
        Auth::logout();
        Response::success([], 'ログアウトしました');
    }

    /**
     * 認証状態チェック
     */
    public function check(array $params): void
    {
        if (Auth::check()) {
            Response::success(['authenticated' => true]);
        } else {
            Response::success(['authenticated' => false]);
        }
    }

    /**
     * パスワード変更
     */
    public function changePassword(array $params): void
    {
        Auth::requireAuth();

        $current  = Request::get('current_password');
        $new      = Request::get('new_password');
        $confirm  = Request::get('confirm_password');

        if (empty($current) || empty($new) || empty($confirm)) {
            Response::error('すべての項目を入力してください', 400, 'VALIDATION_ERROR');
            return;
        }

        if (mb_strlen($new) < 6) {
            Response::error('新しいパスワードは6文字以上にしてください', 400, 'VALIDATION_ERROR');
            return;
        }

        if ($new !== $confirm) {
            Response::error('新しいパスワードと確認用パスワードが一致しません', 400, 'VALIDATION_ERROR');
            return;
        }

        // 現在のパスワードを確認
        $db = Database::getInstance();
        $setting = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = 'password'");

        if (!$setting || !password_verify($current, $setting['setting_value'])) {
            Response::error('現在のパスワードが正しくありません', 401, 'INVALID_PASSWORD');
            return;
        }

        Auth::setPassword($new);
        Response::success([], 'パスワードを変更しました');
    }
}
