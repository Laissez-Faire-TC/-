<?php
/**
 * 遠征参加者モデル
 */
class ExpeditionParticipant
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 遠征IDで参加者一覧を取得（ふりがな順）
     */
    public function findByExpedition(int $expedition_id): array
    {
        return $this->db->fetchAll(
            "SELECT ep.*, m.name, m.furigana, m.gender
             FROM expedition_participants ep
             JOIN members m ON m.id = ep.member_id
             WHERE ep.expedition_id = ?
             ORDER BY m.furigana",
            [$expedition_id]
        );
    }

    /**
     * 参加者を追加してレコードを返す
     */
    public function add(int $expedition_id, int $member_id): ?array
    {
        $id = $this->db->insert(
            "INSERT INTO expedition_participants (expedition_id, member_id, pre_night, lunch, status)
             VALUES (?, ?, 0, 0, 'confirmed')",
            [$expedition_id, $member_id]
        );

        return $this->db->fetch(
            "SELECT * FROM expedition_participants WHERE id = ?",
            [$id]
        );
    }

    /**
     * 参加者情報を更新して更新後レコードを返す
     */
    public function update(int $id, array $data): ?array
    {
        // 更新可能なフィールド
        $allowedFields = ['pre_night', 'lunch', 'status'];

        $fields = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $values[] = $id;
            $sql = "UPDATE expedition_participants SET " . implode(', ', $fields) . " WHERE id = ?";
            $this->db->execute($sql, $values);
        }

        return $this->db->fetch(
            "SELECT * FROM expedition_participants WHERE id = ?",
            [$id]
        );
    }

    /**
     * 参加者を削除する
     */
    public function remove(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM expedition_participants WHERE id = ?",
            [$id]
        ) > 0;
    }
}
