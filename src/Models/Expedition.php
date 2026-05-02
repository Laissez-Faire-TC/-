<?php
/**
 * 遠征モデル
 */
class Expedition
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 全件取得（参加者数付き）
     */
    public function findAll(): array
    {
        return $this->db->fetchAll(
            "SELECT e.*, COUNT(ep.id) as participant_count
             FROM expeditions e
             LEFT JOIN expedition_participants ep ON ep.expedition_id = e.id
             GROUP BY e.id
             ORDER BY e.start_date DESC"
        );
    }

    /**
     * ID指定で取得
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM expeditions WHERE id = ?",
            [$id]
        );
    }

    /**
     * 新規作成
     */
    public function create(array $data): ?array
    {
        $sql = "INSERT INTO expeditions (
            name, start_date, end_date, base_fee, pre_night_fee, lunch_fee
        ) VALUES (?, ?, ?, ?, ?, ?)";

        $id = $this->db->insert($sql, [
            $data['name'],
            $data['start_date'],
            $data['end_date'],
            $data['base_fee'] ?? 0,
            $data['pre_night_fee'] ?? 0,
            $data['lunch_fee'] ?? 0,
        ]);

        return $this->findById($id);
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): ?array
    {
        $fields = [];
        $values = [];

        // 更新可能なフィールド
        $allowedFields = [
            'name', 'start_date', 'end_date', 'base_fee', 'pre_night_fee', 'lunch_fee',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return $this->findById($id);
        }

        $values[] = $id;
        $sql = "UPDATE expeditions SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->execute($sql, $values);

        return $this->findById($id);
    }

    /**
     * 削除（関連テーブルをトランザクション内で順次削除）
     */
    public function delete(int $id): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. 収集品明細を削除
            $this->db->execute(
                "DELETE ec FROM expedition_collection_items ec
                 INNER JOIN expedition_collections ecl ON ec.collection_id = ecl.id
                 WHERE ecl.expedition_id = ?",
                [$id]
            );

            // 2. 収集品を削除
            $this->db->execute(
                "DELETE FROM expedition_collections WHERE expedition_id = ?",
                [$id]
            );

            // 3. チームメンバーを削除
            $this->db->execute(
                "DELETE etm FROM expedition_team_members etm
                 INNER JOIN expedition_teams et ON etm.team_id = et.id
                 WHERE et.expedition_id = ?",
                [$id]
            );

            // 4. チームを削除
            $this->db->execute(
                "DELETE FROM expedition_teams WHERE expedition_id = ?",
                [$id]
            );

            // 5. 車両支払者を削除
            $this->db->execute(
                "DELETE ecp FROM expedition_car_payers ecp
                 INNER JOIN expedition_cars ec ON ecp.car_id = ec.id
                 WHERE ec.expedition_id = ?",
                [$id]
            );

            // 6. 車両メンバーを削除
            $this->db->execute(
                "DELETE ecm FROM expedition_car_members ecm
                 INNER JOIN expedition_cars ec ON ecm.car_id = ec.id
                 WHERE ec.expedition_id = ?",
                [$id]
            );

            // 7. 車両を削除
            $this->db->execute(
                "DELETE FROM expedition_cars WHERE expedition_id = ?",
                [$id]
            );

            // 8. 参加者を削除
            $this->db->execute(
                "DELETE FROM expedition_participants WHERE expedition_id = ?",
                [$id]
            );

            // 9. トークンを削除
            $this->db->execute(
                "DELETE FROM expedition_tokens WHERE expedition_id = ?",
                [$id]
            );

            // 10. 冊子を削除
            $this->db->execute(
                "DELETE FROM expedition_booklets WHERE expedition_id = ?",
                [$id]
            );

            // 11. 遠征本体を削除
            $result = $this->db->execute(
                "DELETE FROM expeditions WHERE id = ?",
                [$id]
            ) > 0;

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
