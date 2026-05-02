<?php
/**
 * 遠征コントローラー
 */
class ExpeditionController
{
    public function __construct()
    {
        Auth::requireAuth();
    }

    // ==================== 一覧・詳細・CRUD ====================

    /**
     * 遠征一覧取得
     * GET /api/expeditions
     */
    public function index(): void
    {
        $expeditions = Expedition::findAll();
        Response::success($expeditions);
    }

    /**
     * 遠征詳細取得
     * GET /api/expeditions/{id}
     */
    public function show(array $params): void
    {
        $expedition = Expedition::findById($params['id']);
        if (!$expedition) Response::error('遠征が見つかりません', 404);
        $expedition['participants'] = ExpeditionParticipant::findByExpedition($params['id']);
        $expedition['cars']        = ExpeditionCar::findByExpedition($params['id']);
        $expedition['teams']       = ExpeditionTeam::findByExpedition($params['id']);
        Response::success($expedition);
    }

    /**
     * 遠征作成
     * POST /api/expeditions
     */
    public function store(): void
    {
        Request::validate(['name' => 'required', 'start_date' => 'required', 'end_date' => 'required']);
        $data       = Request::only(['name', 'start_date', 'end_date', 'base_fee', 'pre_night_fee', 'lunch_fee']);
        $expedition = Expedition::create($data);
        Response::success($expedition, 201);
    }

    /**
     * 遠征更新
     * PUT /api/expeditions/{id}
     */
    public function update(array $params): void
    {
        $data       = Request::only(['name', 'start_date', 'end_date', 'base_fee', 'pre_night_fee', 'lunch_fee']);
        $expedition = Expedition::update($params['id'], $data);
        Response::success($expedition);
    }

    /**
     * 遠征削除
     * DELETE /api/expeditions/{id}
     */
    public function destroy(array $params): void
    {
        Expedition::delete($params['id']);
        Response::success(['message' => '遠征を削除しました']);
    }

    // ==================== 参加者管理 ====================

    /**
     * 参加者一覧取得
     * GET /api/expeditions/{id}/participants
     */
    public function getParticipants(array $params): void
    {
        $participants = ExpeditionParticipant::findByExpedition($params['id']);
        Response::success($participants);
    }

    /**
     * 参加者追加
     * POST /api/expeditions/{id}/participants
     */
    public function addParticipant(array $params): void
    {
        $data        = Request::body();
        $participant = ExpeditionParticipant::add($params['id'], $data['member_id']);
        Response::success($participant, 201);
    }

    /**
     * 参加者更新
     * PUT /api/expeditions/{id}/participants/{pid}
     */
    public function updateParticipant(array $params): void
    {
        $data        = Request::only(['pre_night', 'lunch', 'status']);
        $participant = ExpeditionParticipant::update($params['pid'], $data);
        Response::success($participant);
    }

    /**
     * 参加者削除
     * DELETE /api/expeditions/{id}/participants/{pid}
     */
    public function removeParticipant(array $params): void
    {
        ExpeditionParticipant::remove($params['pid']);
        Response::success(['message' => '参加者を削除しました']);
    }

    // ==================== 車割管理 ====================

    /**
     * 車一覧取得
     * GET /api/expeditions/{id}/cars
     */
    public function getCars(array $params): void
    {
        $cars = ExpeditionCar::findByExpedition($params['id']);
        Response::success($cars);
    }

    /**
     * 車追加
     * POST /api/expeditions/{id}/cars
     */
    public function addCar(array $params): void
    {
        $data = Request::only(['name', 'capacity', 'rental_fee', 'highway_fee']);
        $car  = ExpeditionCar::create($params['id'], $data);
        Response::success($car, 201);
    }

    /**
     * 車更新
     * PUT /api/expeditions/{id}/cars/{cid}
     */
    public function updateCar(array $params): void
    {
        $data = Request::only(['name', 'capacity', 'rental_fee', 'highway_fee', 'sort_order']);
        $car  = ExpeditionCar::update($params['cid'], $data);
        Response::success($car);
    }

    /**
     * 車削除
     * DELETE /api/expeditions/{id}/cars/{cid}
     */
    public function removeCar(array $params): void
    {
        ExpeditionCar::delete($params['cid']);
        Response::success(['message' => '車を削除しました']);
    }

    /**
     * 乗員追加
     * POST /api/expeditions/{id}/cars/{cid}/members
     */
    public function addCarMember(array $params): void
    {
        $data   = Request::body();
        $member = ExpeditionCarMember::add($params['cid'], $data['member_id'], $data['role'] ?? 'passenger');
        Response::success($member, 201);
    }

    /**
     * 乗員更新
     * PUT /api/expeditions/{id}/cars/{cid}/members/{mid}
     */
    public function updateCarMember(array $params): void
    {
        $data   = Request::only(['role', 'is_excluded', 'sort_order']);
        $member = ExpeditionCarMember::update($params['mid'], $data);
        Response::success($member);
    }

    /**
     * 乗員削除
     * DELETE /api/expeditions/{id}/cars/{cid}/members/{mid}
     */
    public function removeCarMember(array $params): void
    {
        ExpeditionCarMember::remove($params['mid']);
        Response::success(['message' => '乗員を削除しました']);
    }

    /**
     * 立替者追加
     * POST /api/expeditions/{id}/cars/{cid}/payers
     */
    public function addCarPayer(array $params): void
    {
        $data  = Request::body();
        $payer = ExpeditionCarPayer::add($params['cid'], $data['member_id'], $data['amount'] ?? 0);
        Response::success($payer, 201);
    }

    /**
     * 立替者削除
     * DELETE /api/expeditions/{id}/cars/{cid}/payers/{pid}
     */
    public function removeCarPayer(array $params): void
    {
        ExpeditionCarPayer::remove($params['pid']);
        Response::success(['message' => '立替者を削除しました']);
    }

    /**
     * 車代清算計算
     * GET /api/expeditions/{id}/cars/settlement
     */
    public function getSettlement(array $params): void
    {
        $settlement = ExpeditionCar::calculateSettlement($params['id']);
        Response::success($settlement);
    }

    // ==================== チーム管理 ====================

    /**
     * チーム一覧取得
     * GET /api/expeditions/{id}/teams
     */
    public function getTeams(array $params): void
    {
        $teams = ExpeditionTeam::findByExpedition($params['id']);
        Response::success($teams);
    }

    /**
     * チーム追加
     * POST /api/expeditions/{id}/teams
     */
    public function addTeam(array $params): void
    {
        $data = Request::body();
        $team = ExpeditionTeam::create($params['id'], $data['name']);
        Response::success($team, 201);
    }

    /**
     * チーム更新
     * PUT /api/expeditions/{id}/teams/{tid}
     */
    public function updateTeam(array $params): void
    {
        $data = Request::only(['name', 'sort_order']);
        $team = ExpeditionTeam::update($params['tid'], $data);
        Response::success($team);
    }

    /**
     * チーム削除
     * DELETE /api/expeditions/{id}/teams/{tid}
     */
    public function removeTeam(array $params): void
    {
        ExpeditionTeam::delete($params['tid']);
        Response::success(['message' => 'チームを削除しました']);
    }

    /**
     * チームメンバー追加
     * POST /api/expeditions/{id}/teams/{tid}/members
     */
    public function addTeamMember(array $params): void
    {
        $data   = Request::body();
        $member = ExpeditionTeamMember::add($params['tid'], $data['member_id']);
        Response::success($member, 201);
    }

    /**
     * チームメンバー削除
     * DELETE /api/expeditions/{id}/teams/{tid}/members/{mid}
     */
    public function removeTeamMember(array $params): void
    {
        ExpeditionTeamMember::remove($params['mid']);
        Response::success(['message' => 'メンバーを削除しました']);
    }

    /**
     * チーム並び順更新
     * PUT /api/expeditions/{id}/teams/order
     */
    public function updateTeamOrder(array $params): void
    {
        $data = Request::body();
        ExpeditionTeamMember::updateOrder($data);
        Response::success(['message' => '並び順を更新しました']);
    }

    // ==================== 集金管理 ====================

    /**
     * 集金一覧取得
     * GET /api/expeditions/{id}/collection
     */
    public function getCollection(array $params): void
    {
        $collections = ExpeditionCollection::findByExpedition($params['id']);
        Response::success($collections);
    }

    /**
     * 集金生成
     * POST /api/expeditions/{id}/collection/generate
     */
    public function generateCollection(array $params): void
    {
        $body       = Request::body();
        $round      = $body['round'] ?? 1;
        $round      = intval($round);
        $title      = $round === 1 ? '遠征前集金（参加費）' : '遠征後集金（車代清算）';
        $collection = ExpeditionCollection::create($params['id'], $round, $title);
        ExpeditionCollection::generateItems($collection['id']);
        Response::success($collection);
    }

    /**
     * 集金明細更新
     * PUT /api/expeditions/{id}/collection/{cid}/items/{iid}
     */
    public function updateCollectionItem(array $params): void
    {
        $data = Request::only(['paid', 'memo', 'amount']);
        $item = ExpeditionCollectionItem::update($params['iid'], $data);
        Response::success($item);
    }

    // ==================== 申し込みURL ====================

    /**
     * 申し込みURL取得
     * GET /api/expeditions/{id}/application-url
     */
    public function getApplicationUrl(array $params): void
    {
        $token = ExpeditionToken::findByExpedition($params['id']);
        Response::success($token ?: null);
    }

    /**
     * 申し込みURL生成
     * POST /api/expeditions/{id}/application-url
     */
    public function generateApplicationUrl(array $params): void
    {
        $token = ExpeditionToken::generate($params['id']);
        Response::success($token);
    }
}
