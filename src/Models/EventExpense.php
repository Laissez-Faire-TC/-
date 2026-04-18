<?php
/**
 * 企画雑費モデル
 */
class EventExpense
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 企画IDで取得
     */
    public function getByEventId(int $eventId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM event_expenses WHERE event_id = ? ORDER BY id",
            [$eventId]
        );
    }

    /**
     * ID指定で取得
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM event_expenses WHERE id = ?",
            [$id]
        );
    }

    /**
     * 合計金額取得
     */
    public function sumByEventId(int $eventId): int
    {
        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM event_expenses WHERE event_id = ?",
            [$eventId]
        );
        return (int)($row['total'] ?? 0);
    }

    /**
     * 新規作成
     */
    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO event_expenses (event_id, name, amount) VALUES (?, ?, ?)",
            [$data['event_id'], $data['name'], $data['amount']]
        );
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach (['name', 'amount'] as $field) {
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
            "UPDATE event_expenses SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        ) > 0;
    }

    /**
     * 削除
     */
    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM event_expenses WHERE id = ?", [$id]) > 0;
    }
}
