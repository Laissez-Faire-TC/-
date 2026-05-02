<?php
/**
 * 遠征徴収明細モデル
 */
class ExpeditionCollectionItem
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 徴収IDで明細一覧を取得（フリガナ順）
     */
    public function findByCollection(int $collection_id): array
    {
        return $this->db->fetchAll(
            "SELECT eci.*, m.name, m.furigana
             FROM expedition_collection_items eci
             JOIN members m ON m.id = eci.member_id
             WHERE eci.collection_id = ? ORDER BY m.furigana",
            [$collection_id]
        );
    }

    /**
     * 明細を更新（paid / memo / amount を対象フィールドとする）
     */
    public function update(int $id, array $data): ?array
    {
        $fields = [];
        $values = [];

        // 更新可能なフィールド
        $allowedFields = ['paid', 'memo', 'amount'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $values[] = $id;
            $sql = "UPDATE expedition_collection_items SET " . implode(', ', $fields) . " WHERE id = ?";
            $this->db->execute($sql, $values);
        }

        return $this->db->fetch(
            "SELECT * FROM expedition_collection_items WHERE id = ?",
            [$id]
        );
    }
}
