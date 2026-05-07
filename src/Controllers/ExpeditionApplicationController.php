<?php
/**
 * 遠征申し込みコントローラー（公開・会員ログイン使用）
 *
 * フロー:
 *   GET  /apply/expedition/{token}         → ログインページ（未ログイン）or confirm へリダイレクト
 *   GET  /apply/expedition/{token}/confirm → 情報確認・前泊・昼食オプション選択
 *   POST /api/apply/expedition/{token}     → 申し込み処理
 *   GET  /apply/expedition/{token}/complete → 完了ページ
 */
class ExpeditionApplicationController
{
    private const SESSION_KEY = 'member_authenticated';

    // ==================== Step 1: ログイン ====================

    /**
     * ログインページ（未ログインの場合）、ログイン済みなら confirm へ
     * GET /apply/expedition/{token}
     */
    public function form(array $params): void
    {
        $token     = $params['token'];
        $tokenData = ExpeditionToken::findByToken($token);

        if (!$this->isTokenValid($tokenData)) {
            $this->showError('無効な申し込みURLです。URLが正しいか、有効期限内かをご確認ください。');
            return;
        }

        if ($this->checkAuth()) {
            Response::redirect("/apply/expedition/{$token}/confirm");
            return;
        }

        $expedition = Expedition::findById((int)$tokenData['expedition_id']);

        $pageTitle = htmlspecialchars($expedition['name'] ?? '遠征') . ' - 申し込み';
        $appName   = '遠征申し込み';
        ob_start();
        include VIEWS_PATH . '/expeditions/apply.php';
        $content = ob_get_clean();
        include VIEWS_PATH . '/layouts/public.php';
    }

    // ==================== Step 2: 情報確認・オプション ====================

    /**
     * 情報確認 + 前泊・昼食オプション
     * GET /apply/expedition/{token}/confirm
     */
    public function confirm(array $params): void
    {
        $token     = $params['token'];
        $tokenData = ExpeditionToken::findByToken($token);

        if (!$this->isTokenValid($tokenData)) {
            $this->showError('無効な申し込みURLです。URLが正しいか、有効期限内かをご確認ください。');
            return;
        }

        if (!$this->checkAuth()) {
            Response::redirect("/apply/expedition/{$token}");
            return;
        }

        $expedition  = Expedition::findById((int)$tokenData['expedition_id']);
        $memberId    = (int)$_SESSION['member_id'];
        $memberModel = new Member();
        $member      = $memberModel->find($memberId);

        if (!$member || !in_array($member['status'], ['active', 'ob_og'])) {
            $this->showError('会員情報が見つかりません。');
            return;
        }

        // 既申し込みチェック
        $alreadyApplied = false;
        foreach (ExpeditionParticipant::findByExpedition((int)$tokenData['expedition_id']) as $p) {
            if ((int)$p['member_id'] === $memberId) {
                $alreadyApplied = true;
                break;
            }
        }

        // 定員状況を取得
        $counts = ExpeditionParticipant::countByGenderAndStatus((int)$tokenData['expedition_id']);
        $memberGender = $member['gender']; // 'male' or 'female'
        $isMale       = ($memberGender === 'male');
        $capacityForGender = $isMale ? ($expedition['capacity_male'] ?? null) : ($expedition['capacity_female'] ?? null);
        $confirmedCount    = $isMale ? $counts['confirmed_male'] : $counts['confirmed_female'];
        $waitlistCount     = $isMale ? $counts['waitlisted_male'] : $counts['waitlisted_female'];
        $remaining         = ($capacityForGender !== null) ? max(0, $capacityForGender - $confirmedCount) : null;
        $isFull            = ($capacityForGender !== null) && ($confirmedCount >= $capacityForGender);

        $pageTitle = htmlspecialchars($expedition['name'] ?? '遠征') . ' - 申し込み確認';
        $appName   = '遠征申し込み';
        ob_start();
        include VIEWS_PATH . '/expeditions/apply_confirm.php';
        $content = ob_get_clean();
        include VIEWS_PATH . '/layouts/public.php';
    }

    // ==================== Step 3: 申し込み処理（API） ====================

    /**
     * 申し込み処理
     * POST /api/apply/expedition/{token}
     */
    public function apply(array $params): void
    {
        $token     = $params['token'];
        $tokenData = ExpeditionToken::findByToken($token);

        if (!$this->isTokenValid($tokenData)) {
            Response::error('無効な申し込みURLです', 400, 'INVALID_TOKEN');
            return;
        }

        if (!$this->checkAuth()) {
            Response::error('ログインが必要です', 401, 'UNAUTHORIZED');
            return;
        }

        $memberId     = (int)$_SESSION['member_id'];
        $expeditionId = (int)$tokenData['expedition_id'];

        $expedition = Expedition::findById($expeditionId);
        if (!$expedition) {
            Response::error('遠征が見つかりません', 404, 'NOT_FOUND');
            return;
        }

        $body         = Request::json();
        $preNight     = isset($body['pre_night']) ? (int)(bool)$body['pre_night'] : 1;
        $lunch        = isset($body['lunch'])     ? (int)(bool)$body['lunch']     : 0;
        $isJoiningCar = isset($body['is_joining_car']) ? (int)(bool)$body['is_joining_car'] : 1;
        $driverType   = in_array($body['driver_type'] ?? '', ['driver','sub_driver','none']) ? $body['driver_type'] : 'none';
        $timescarNum  = trim($body['timescar_number'] ?? '');
        $canBookCar   = isset($body['can_book_car']) ? (int)(bool)$body['can_book_car'] : 0;
        $fridayClass  = ($isJoiningCar && isset($body['friday_last_class']) && $body['friday_last_class'] !== '')
                        ? (int)$body['friday_last_class'] : null;

        // 会員情報修正
        $infoEdited = !empty($body['info_edited']) ? 1 : 0;
        if ($infoEdited) {
            $memberModel = new Member();
            $updateData  = [];
            if (isset($body['edited_address'])  && $body['edited_address']  !== '') $updateData['address']   = $body['edited_address'];
            if (isset($body['edited_allergy']))                                      $updateData['allergy']   = $body['edited_allergy'];
            if (isset($body['edited_line_name']) && $body['edited_line_name'] !== '') $updateData['line_name'] = $body['edited_line_name'];
            if (!empty($updateData)) {
                $memberModel->update($memberId, $updateData);

                // ダッシュボード通知
                $memberModel2 = new Member();
                $memberCur    = $memberModel2->find($memberId);
                if ($memberCur) {
                    $notifModel = new MemberChangeNotification();
                    $changes = [];
                    if (isset($updateData['address'])   && $updateData['address']   !== ($memberCur['address']   ?? '')) $changes['address']   = ['before' => $memberCur['address'],   'after' => $updateData['address']];
                    if (isset($updateData['allergy'])   && $updateData['allergy']   !== ($memberCur['allergy']   ?? '')) $changes['allergy']   = ['before' => $memberCur['allergy'],   'after' => $updateData['allergy']];
                    if (isset($updateData['line_name']) && $updateData['line_name'] !== ($memberCur['line_name'] ?? '')) $changes['line_name'] = ['before' => $memberCur['line_name'], 'after' => $updateData['line_name']];
                    if (!empty($changes)) $notifModel->create($memberId, $memberCur['name_kanji'], $memberCur['student_id'], $changes);
                }
            }
        }

        // 申込み開始日時チェック
        if (!empty($expedition['application_start']) && strtotime($expedition['application_start']) > time()) {
            Response::error('申し込み受付がまだ始まっていません', 400, 'NOT_STARTED');
            return;
        }

        // 申込期限チェック
        if (!empty($expedition['deadline']) && $expedition['deadline'] < date('Y-m-d')) {
            Response::error('申し込み期限を過ぎています', 400, 'DEADLINE_PASSED');
            return;
        }

        // 定員チェック（性別ごと）
        $memberModel2  = new Member();
        $member2       = $memberModel2->find($memberId);
        $memberGender  = $member2['gender'] ?? '';
        $isMale        = ($memberGender === 'male');
        $counts        = ExpeditionParticipant::countByGenderAndStatus($expeditionId);
        $capKey        = $isMale ? 'capacity_male' : 'capacity_female';
        $confKey       = $isMale ? 'confirmed_male' : 'confirmed_female';
        $capacityLimit = isset($expedition[$capKey]) && $expedition[$capKey] !== null ? (int)$expedition[$capKey] : null;
        $confirmedCnt  = $counts[$confKey];
        $status        = 'confirmed';
        if ($capacityLimit !== null && $confirmedCnt >= $capacityLimit) {
            $status = 'waitlisted';
        }

        // 重複チェック
        foreach (ExpeditionParticipant::findByExpedition($expeditionId) as $p) {
            if ((int)$p['member_id'] === $memberId) {
                Response::error('すでに申し込み済みです', 400, 'ALREADY_APPLIED');
                return;
            }
        }

        $participant = ExpeditionParticipant::add(
            $expeditionId, $memberId, $preNight, $lunch, $status,
            $isJoiningCar, $driverType, $timescarNum, $canBookCar, $fridayClass
        );
        Response::success(array_merge($participant, ['is_waitlisted' => $status === 'waitlisted']), 201);
    }

    // ==================== Step 4: 完了 ====================

    /**
     * 申し込み完了ページ
     * GET /apply/expedition/{token}/complete
     */
    public function complete(array $params): void
    {
        $token     = $params['token'];
        $tokenData = ExpeditionToken::findByToken($token);

        if (!$tokenData) {
            $this->showError('URLが無効です。');
            return;
        }

        $expedition  = Expedition::findById((int)$tokenData['expedition_id']);
        $member      = null;
        $participant = null;

        if ($this->checkAuth()) {
            $memberId    = (int)$_SESSION['member_id'];
            $memberModel = new Member();
            $member      = $memberModel->find($memberId);

            // 申し込み内容取得
            foreach (ExpeditionParticipant::findByExpedition((int)$tokenData['expedition_id']) as $p) {
                if ((int)$p['member_id'] === $memberId) {
                    $participant = $p;
                    break;
                }
            }
        }

        $pageTitle = '申し込み完了';
        $appName   = '遠征申し込み';
        ob_start();
        include VIEWS_PATH . '/expeditions/apply_complete.php';
        $content = ob_get_clean();
        include VIEWS_PATH . '/layouts/public.php';
    }

    // ==================== ユーティリティ ====================

    private function isTokenValid(?array $tokenData): bool
    {
        if (!$tokenData) return false;
        if ($tokenData['expires_at'] && strtotime($tokenData['expires_at']) < time()) return false;
        return true;
    }

    private function checkAuth(): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY])) return false;
        if (isset($_SESSION['member_login_time'])) {
            if (time() - $_SESSION['member_login_time'] > 86400) {
                unset($_SESSION[self::SESSION_KEY]);
                return false;
            }
        }
        return true;
    }

    private function showError(string $message): void
    {
        $errorMessage = $message;
        include VIEWS_PATH . '/apply/error.php';
    }
}
