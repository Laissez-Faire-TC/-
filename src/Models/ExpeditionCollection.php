<?php
/**
 * 遠征徴収モデル
 */
class ExpeditionCollection
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 遠征IDで徴収一覧を取得（各徴収に明細をセットして返す）
     */
    public function findByExpedition(int $expedition_id): array
    {
        $collections = $this->db->fetchAll(
            "SELECT * FROM expedition_collections WHERE expedition_id = ? ORDER BY round",
            [$expedition_id]
        );

        foreach ($collections as &$collection) {
            $collection['items'] = $this->db->fetchAll(
                "SELECT eci.*, m.name FROM expedition_collection_items eci
                 JOIN members m ON m.id = eci.member_id
                 WHERE eci.collection_id = ? ORDER BY m.furigana",
                [$collection['id']]
            );
        }
        unset($collection);

        return $collections;
    }

    /**
     * 徴収を新規作成（status='pending'で作成）
     */
    public function create(int $expedition_id, int $round, string $title): ?array
    {
        $id = $this->db->insert(
            "INSERT INTO expedition_collections (expedition_id, round, title, status) VALUES (?, ?, ?, 'pending')",
            [$expedition_id, $round, $title]
        );

        return $this->db->fetch(
            "SELECT * FROM expedition_collections WHERE id = ?",
            [$id]
        );
    }

    /**
     * 徴収を更新
     */
    public function update(int $id, array $data): ?array
    {
        $fields = [];
        $values = [];

        // 更新可能なフィールド
        $allowedFields = ['round', 'title', 'status'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $values[] = $id;
            $sql = "UPDATE expedition_collections SET " . implode(', ', $fields) . " WHERE id = ?";
            $this->db->execute($sql, $values);
        }

        return $this->db->fetch(
            "SELECT * FROM expedition_collections WHERE id = ?",
            [$id]
        );
    }

    /**
     * 徴収をトランザクション内で削除（明細→徴収の順に削除）
     */
    public function delete(int $id): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. 徴収明細を削除
            $this->db->execute(
                "DELETE FROM expedition_collection_items WHERE collection_id = ?",
                [$id]
            );

            // 2. 徴収本体を削除
            $result = $this->db->execute(
                "DELETE FROM expedition_collections WHERE id = ?",
                [$id]
            ) > 0;

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * 徴収明細を自動生成する
     * round=1: 参加費（base_fee + オプション）
     * round=2: 車両費精算（ExpeditionCar::calculateSettlement を使用）
     */
    public function generateItems(int $collection_id): bool
    {
        // 徴収と遠征の料金情報を取得
        $collection = $this->db->fetch(
            "SELECT ec.*, e.base_fee, e.pre_night_fee, e.lunch_fee
             FROM expedition_collections ec
             JOIN expeditions e ON e.id = ec.expedition_id
             WHERE ec.id = ?",
            [$collection_id]
        );

        if (!$collection) {
            return false;
        }

        $expedition_id = (int)$collection['expedition_id'];

        if ((int)$collection['round'] === 1) {
            // 第1回: 参加費を計算して明細を生成
            $participants = $this->db->fetchAll(
                "SELECT * FROM expedition_participants WHERE expedition_id = ?",
                [$expedition_id]
            );

            foreach ($participants as $participant) {
                // 基本料金 + 前泊オプション + 昼食オプション
                $amount = (int)$collection['base_fee'];
                if ((int)$participant['pre_night'] === 1) {
                    $amount += (int)$collection['pre_night_fee'];
                }
                if ((int)$participant['lunch'] === 1) {
                    $amount += (int)$collection['lunch_fee'];
                }

                $this->db->insert(
                    "INSERT INTO expedition_collection_items (collection_id, member_id, amount) VALUES (?, ?, ?)",
                    [$collection_id, $participant['member_id'], $amount]
                );
            }

        } elseif ((int)$collection['round'] === 2) {
            // 第2回: 車両費精算を計算して明細を生成
            $carModel = new ExpeditionCar();
            $result = $carModel->calculateSettlement($expedition_id);

            foreach ($result['settlement'] as $entry) {
                // amount は正（支払い）または負（返金）をそのまま保存
                $this->db->insert(
                    "INSERT INTO expedition_collection_items (collection_id, member_id, amount) VALUES (?, ?, ?)",
                    [$collection_id, $entry['member_id'], $entry['amount']]
                );
            }
        }

        return true;
    }
}
