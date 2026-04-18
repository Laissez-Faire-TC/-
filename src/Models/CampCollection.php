<?php
/**
 * 合宿集金モデル
 */
class CampCollection
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * camp_id で集金情報を取得（提出数・合計数付き）
     */
    public function findByCampId(int $campId): ?array
    {
        return $this->db->fetch(
            "SELECT cc.*, c.name as camp_name,
                    COUNT(CASE WHEN ci.submitted=1 THEN 1 END) as submitted_count,
                    COUNT(ci.id) as total_count
             FROM camp_collections cc
             JOIN camps c ON c.id = cc.camp_id
             LEFT JOIN camp_collection_items ci ON ci.collection_id = cc.id
             WHERE cc.camp_id = ?
             GROUP BY cc.id",
            [$campId]
        );
    }

    /**
     * id で集金情報を取得（提出数・合計数付き）
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT cc.*, c.name as camp_name,
                    COUNT(CASE WHEN ci.submitted=1 THEN 1 END) as submitted_count,
                    COUNT(ci.id) as total_count
             FROM camp_collections cc
             JOIN camps c ON c.id = cc.camp_id
             LEFT JOIN camp_collection_items ci ON ci.collection_id = cc.id
             WHERE cc.id = ?
             GROUP BY cc.id",
            [$id]
        );
    }

    /**
     * 集金レコードを作成し、対象会員のアイテムを初期化する
     */
    public function create(array $data): int
    {
        $id = $this->db->insert(
            "INSERT INTO camp_collections (camp_id, default_amount, deadline, is_active) VALUES (?, ?, ?, 1)",
            [$data['camp_id'], (int)$data['default_amount'], $data['deadline']]
        );

        $this->initializeItems($id, (int)$data['camp_id']);

        return $id;
    }

    /**
     * deadline / default_amount / is_active を更新する
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowed = ['default_amount', 'deadline', 'is_active'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        return $this->db->execute(
            "UPDATE camp_collections SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        ) > 0;
    }

    /**
     * 集金レコードを削除する
     */
    public function delete(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM camp_collections WHERE id = ?",
            [$id]
        ) > 0;
    }

    /**
     * 申し込み済み会員のアイテムを一括初期化する（重複は無視）
     */
    public function initializeItems(int $collectionId, int $campId): void
    {
        $this->db->execute(
            "INSERT IGNORE INTO camp_collection_items (collection_id, member_id)
             SELECT ?, member_id FROM camp_applications
             WHERE camp_id = ? AND status = 'submitted' AND member_id IS NOT NULL",
            [$collectionId, $campId]
        );
    }
}
