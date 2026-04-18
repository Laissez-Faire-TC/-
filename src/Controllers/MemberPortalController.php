<?php
/**
 * 会員ポータルコントローラー（一般会員向け）
 */
class MemberPortalController
{
    private const SESSION_KEY = 'member_authenticated';

    /**
     * 会員ログインページ
     */
    public function loginPage(array $params): void
    {
        if ($this->checkAuth()) {
            Response::redirect('/member/home');
            return;
        }
        $this->render('member/login', []);
    }

    /**
     * 会員ログイン処理（API）
     */
    public function login(array $params): void
    {
        $studentId = trim(Request::get('student_id') ?? '');

        // 正規化：全角→半角、小文字→大文字
        $studentId = mb_convert_kana($studentId, 'a');
        $studentId = strtoupper($studentId);

        if (empty($studentId)) {
            Response::error('学籍番号を入力してください', 400, 'VALIDATION_ERROR');
            return;
        }

        $memberModel = new Member();
        $member = $memberModel->findByStudentId($studentId);

        if ($member && in_array($member['status'], [Member::STATUS_ACTIVE, Member::STATUS_OB_OG])) {
            $_SESSION[self::SESSION_KEY] = true;
            $_SESSION['member_id']   = $member['id'];
            $_SESSION['member_name'] = $member['name_kanji'];
            $_SESSION['member_login_time'] = time();
            Response::success([], 'ログインしました');
        } else {
            $debugMsg = '会員が見つかりません';
            if ($member) {
                $debugMsg = '会員は存在しますが、ステータスが対象外です: ' . $member['status'];
            }
            Response::error($debugMsg, 401, 'INVALID_STUDENT_ID');
        }
    }

    /**
     * 会員ホームページ
     */
    public function home(array $params): void
    {
        if (!$this->checkAuth()) {
            Response::redirect('/member/login');
            return;
        }

        $campTokenModel = new CampToken();
        $activeCamps = $campTokenModel->getActiveCampsWithTokens();

        $memberId     = (int)($_SESSION['member_id'] ?? 0);
        $eventModel   = new Event();
        $activeEvents = $eventModel->getActiveWithMemberStatus($memberId);

        $collItemModel      = new CampCollectionItem();
        $pendingCollections = $collItemModel->getPendingByMemberId($memberId);

        $feeItemModel      = new MembershipFeeItem();
        $pendingFees       = $feeItemModel->getPendingByMemberId($memberId);

        $this->render('member/home', [
            'activeCamps'        => $activeCamps,
            'activeEvents'       => $activeEvents,
            'memberName'         => $_SESSION['member_name'] ?? '',
            'memberId'           => $memberId,
            'pendingCollections' => $pendingCollections,
            'pendingFees'        => $pendingFees,
        ]);
    }

    /**
     * 企画申し込み（API）
     */
    public function applyEvent(array $params): void
    {
        if (!$this->checkAuth()) {
            Response::error('ログインが必要です', 401, 'UNAUTHORIZED');
            return;
        }

        $eventId  = (int)$params['id'];
        $memberId = (int)($_SESSION['member_id'] ?? 0);

        $eventModel = new Event();
        $event      = $eventModel->find($eventId);

        if (!$event || !$event['is_active']) {
            Response::error('企画が見つかりません', 404, 'NOT_FOUND');
            return;
        }

        // 締め切りチェック
        if ($event['deadline'] !== null && $event['deadline'] < date('Y-m-d')) {
            Response::error('申込期限を過ぎています', 400, 'DEADLINE_PASSED');
            return;
        }

        $appModel = new EventApplication();
        $existing = $appModel->findByEventAndMember($eventId, $memberId);

        // すでに参加確定 or キャンセル待ちなら何もしない
        if ($existing && in_array($existing['status'], ['submitted', 'waitlisted'])) {
            Response::error('すでに申し込み済みです', 400, 'ALREADY_APPLIED');
            return;
        }

        // 定員チェック
        $isFull = $event['capacity'] !== null
               && (int)$event['application_count'] >= (int)$event['capacity'];

        if ($isFull) {
            if ($event['allow_waitlist']) {
                $appModel->apply($eventId, $memberId, 'waitlisted');
                Response::success(['status' => 'waitlisted'], 'キャンセル待ちに登録しました');
            } else {
                Response::error('定員に達しているため申し込みできません', 400, 'CAPACITY_FULL');
            }
            return;
        }

        $appModel->apply($eventId, $memberId, 'submitted');
        Response::success(['status' => 'submitted'], '申し込みが完了しました');
    }

    /**
     * 企画申し込みキャンセル（API）
     */
    public function cancelEvent(array $params): void
    {
        if (!$this->checkAuth()) {
            Response::error('ログインが必要です', 401, 'UNAUTHORIZED');
            return;
        }

        $eventId  = (int)$params['id'];
        $memberId = (int)($_SESSION['member_id'] ?? 0);

        $appModel   = new EventApplication();
        $prevStatus = $appModel->cancel($eventId, $memberId);

        // 参加確定者がキャンセルした場合のみ繰り上げ
        if ($prevStatus === 'submitted') {
            $appModel->promoteFromWaitlist($eventId);
        }

        Response::success([], 'キャンセルしました');
    }

    /**
     * 未提出の集金一覧を返す API
     * GET /api/member/collections
     */
    public function myCollections(array $params): void
    {
        if (!$this->checkAuth()) {
            Response::error('ログインが必要です', 401, 'UNAUTHORIZED');
            return;
        }

        $memberId  = (int)($_SESSION['member_id'] ?? 0);
        $itemModel = new CampCollectionItem();
        Response::success($itemModel->getPendingByMemberId($memberId));
    }

    /**
     * 集金フォームページ
     * GET /member/collection/{id}  (id = collection_id)
     */
    public function collectionForm(array $params): void
    {
        if (!$this->checkAuth()) {
            Response::redirect('/member/login');
            return;
        }

        $collectionId    = (int)$params['id'];
        $memberId        = (int)($_SESSION['member_id'] ?? 0);
        $itemModel       = new CampCollectionItem();
        $collectionModel = new CampCollection();

        $item = $itemModel->findByMemberAndCollection($memberId, $collectionId);
        if (!$item) {
            http_response_code(404);
            $this->render('error', ['message' => '集金情報が見つかりません']);
            return;
        }

        $collection = $collectionModel->findById($collectionId);
        if (!$collection) {
            http_response_code(404);
            $this->render('error', ['message' => '集金情報が見つかりません']);
            return;
        }

        $campModel = new Camp();
        $camp      = $campModel->find((int)$collection['camp_id']);

        $this->render('member/collection', [
            'item'       => $item,
            'collection' => $collection,
            'camp'       => $camp,
            'memberName' => $_SESSION['member_name'] ?? '',
            'memberId'   => $memberId,
        ]);
    }

    /**
     * 集金提出 API
     * POST /api/member/collection-items/{id}/submit
     */
    public function submitCollection(array $params): void
    {
        if (!$this->checkAuth()) {
            Response::error('ログインが必要です', 401, 'UNAUTHORIZED');
            return;
        }

        $itemId    = (int)$params['id'];
        $memberId  = (int)($_SESSION['member_id'] ?? 0);
        $itemModel = new CampCollectionItem();

        $item = $itemModel->find($itemId);
        if (!$item || (int)$item['member_id'] !== $memberId) {
            Response::error('Not found', 404, 'NOT_FOUND');
            return;
        }

        if ((int)$item['submitted'] === 1) {
            Response::error('すでに提出済みです', 400, 'ALREADY_SUBMITTED');
            return;
        }

        $transferred = Request::get('transferred');
        if (!$transferred || $transferred === '0' || $transferred === 'false') {
            Response::error('振り込み完了のチェックが必要です', 400, 'VALIDATION_ERROR');
            return;
        }

        // 期限超過チェック：期限が過ぎている場合は late_reason が必須
        $collectionModel = new CampCollection();
        $collection      = $collectionModel->findById((int)$item['collection_id']);
        $lateReason      = Request::get('late_reason') ?? null;

        if ($collection && $collection['deadline'] < date('Y-m-d')) {
            if (empty($lateReason)) {
                Response::error('入金遅れの理由を入力してください', 400, 'VALIDATION_ERROR');
                return;
            }
        }

        $itemModel->submit($itemId, $lateReason ?: null);
        Response::success([], '提出しました');
    }

    /**
     * 未提出の入会金一覧を返す API
     * GET /api/member/membership-fees
     */
    public function myFees(array $params): void
    {
        if (!$this->checkAuth()) {
            Response::error('ログインが必要です', 401, 'UNAUTHORIZED');
            return;
        }

        $memberId  = (int)($_SESSION['member_id'] ?? 0);
        $feeItemModel = new MembershipFeeItem();
        Response::success($feeItemModel->getPendingByMemberId($memberId));
    }

    /**
     * 入会金支払い確認フォームページ
     * GET /member/membership-fee/{id}  (id = membership_fee_id)
     */
    public function membershipFeeForm(array $params): void
    {
        if (!$this->checkAuth()) {
            Response::redirect('/member/login');
            return;
        }

        $feeId    = (int)$params['id'];
        $memberId = (int)($_SESSION['member_id'] ?? 0);

        $feeItemModel = new MembershipFeeItem();
        $feeModel     = new MembershipFee();

        $item = $feeItemModel->findByMemberAndFee($memberId, $feeId);
        if (!$item) {
            http_response_code(404);
            $this->render('error', ['message' => '入会金情報が見つかりません']);
            return;
        }

        $fee = $feeModel->findById($feeId);
        if (!$fee) {
            http_response_code(404);
            $this->render('error', ['message' => '入会金情報が見つかりません']);
            return;
        }

        $grades = $feeModel->getGrades($feeId);

        // 会員の学年に対応する金額を取得
        $memberModel = new Member();
        $member      = $memberModel->find($memberId);
        $memberGrade = $member['grade'] ?? '';
        $gradeAmount = $grades[$memberGrade] ?? null;

        $effectiveAmount = $item['custom_amount'] ?? $gradeAmount;

        $this->render('member/membership_fee', [
            'item'            => $item,
            'fee'             => $fee,
            'grades'          => $grades,
            'memberGrade'     => $memberGrade,
            'effectiveAmount' => $effectiveAmount,
            'memberName'      => $_SESSION['member_name'] ?? '',
            'memberId'        => $memberId,
        ]);
    }

    /**
     * 入会金支払い提出 API
     * POST /api/member/membership-fee-items/{id}/submit
     */
    public function submitFee(array $params): void
    {
        if (!$this->checkAuth()) {
            Response::error('ログインが必要です', 401, 'UNAUTHORIZED');
            return;
        }

        $itemId       = (int)$params['id'];
        $memberId     = (int)($_SESSION['member_id'] ?? 0);
        $feeItemModel = new MembershipFeeItem();

        $item = $feeItemModel->find($itemId);
        if (!$item || (int)$item['member_id'] !== $memberId) {
            Response::error('Not found', 404, 'NOT_FOUND');
            return;
        }

        if ((int)$item['submitted'] === 1) {
            Response::error('すでに提出済みです', 400, 'ALREADY_SUBMITTED');
            return;
        }

        $transferred = Request::get('transferred');
        if (!$transferred || $transferred === '0' || $transferred === 'false') {
            Response::error('振り込み完了のチェックが必要です', 400, 'VALIDATION_ERROR');
            return;
        }

        // 期限超過チェック
        $feeModel  = new MembershipFee();
        $fee       = $feeModel->findById((int)$item['membership_fee_id']);
        $lateReason = Request::get('late_reason') ?? null;

        if ($fee && $fee['deadline'] < date('Y-m-d')) {
            if (empty($lateReason)) {
                Response::error('入金遅れの理由を入力してください', 400, 'VALIDATION_ERROR');
                return;
            }
        }

        $feeItemModel->submit($itemId, $lateReason ?: null);
        Response::success([], '提出しました');
    }

    /**
     * デバッグ用（確認後削除）
     */
    public function debugMember(array $params): void
    {
        $studentId = strtoupper(trim($_GET['student_id'] ?? '1Y23F158-5'));
        $memberModel = new Member();
        $member = $memberModel->findByStudentId($studentId);
        header('Content-Type: application/json');
        echo json_encode([
            'searched_id' => $studentId,
            'found' => $member ? true : false,
            'status' => $member['status'] ?? null,
            'STATUS_ACTIVE' => Member::STATUS_ACTIVE,
            'STATUS_OB_OG' => Member::STATUS_OB_OG,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * 会員ログアウト処理（API）
     */
    public function logout(array $params): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        unset($_SESSION['member_id']);
        unset($_SESSION['member_name']);
        unset($_SESSION['member_login_time']);
        Response::success([], 'ログアウトしました');
    }

    /**
     * 認証チェック
     */
    private function checkAuth(): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        // セッション有効期限：24時間
        if (isset($_SESSION['member_login_time'])) {
            if (time() - $_SESSION['member_login_time'] > 86400) {
                unset($_SESSION[self::SESSION_KEY]);
                return false;
            }
        }
        return true;
    }

    /**
     * ビューのレンダリング
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        $config  = require CONFIG_PATH . '/app.php';
        $appName = $config['name'];

        ob_start();
        include VIEWS_PATH . '/' . $view . '.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/member.php';
    }
}
