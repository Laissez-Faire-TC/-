<?php
/**
 * 遠征車両乗車メンバーモデル
 */
class ExpeditionCarMember
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 乗車メンバーを追加して作成行を返す
     */
    public function add(int $car_id, int $member_id, string $role = 'passenger'): ?array
    {
        $id = $this->db->insert(
            "INSERT INTO expedition_car_members (car_id, member_id, role) VALUES (?, ?, ?)",
            [$car_id, $member_id, $role]
        );

        return $this->db->fetch(
            "SELECT * FROM expedition_car_members WHERE id = ?",
            [$id]
        );
    }

    /**
     * 乗車メンバー情報を更新して更新後の行を返す
     */
    public function update(int $id, array $data): ?array
    {
        $allowedFields = ['role', 'is_excluded', 'sort_order'];
        $fields = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return $this->db->fetch("SELECT * FROM expedition_car_members WHERE id = ?", [$id]);
        }

        $values[] = $id;
        $this->db->execute(
            "UPDATE expedition_car_members SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        );

        return $this->db->fetch("SELECT * FROM expedition_car_members WHERE id = ?", [$id]);
    }

    /**
     * 乗車メンバーを削除
     */
    public function remove(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM expedition_car_members WHERE id = ?",
            [$id]
        ) > 0;
    }

    /**
     * 並び順を一括更新
     * $items: [['id' => int, 'sort_order' => int], ...]
     */
    public function updateOrder(array $items): void
    {
        foreach ($items as $item) {
            $this->db->execute(
                "UPDATE expedition_car_members SET sort_order = ? WHERE id = ?",
                [$item['sort_order'], $item['id']]
            );
        }
    }
}
