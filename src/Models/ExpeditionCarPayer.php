<?php
/**
 * 遠征車両立替者モデル
 */
class ExpeditionCarPayer
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 車両IDに紐づく立替者一覧を取得
     */
    public function findByCar(int $car_id): array
    {
        return $this->db->fetchAll(
            "SELECT ecp.*, m.name
             FROM expedition_car_payers ecp
             JOIN members m ON m.id = ecp.member_id
             WHERE ecp.car_id = ?",
            [$car_id]
        );
    }

    /**
     * 立替者を追加して作成行を返す
     */
    public function add(int $car_id, int $member_id, int $amount): ?array
    {
        $id = $this->db->insert(
            "INSERT INTO expedition_car_payers (car_id, member_id, amount) VALUES (?, ?, ?)",
            [$car_id, $member_id, $amount]
        );

        return $this->db->fetch(
            "SELECT * FROM expedition_car_payers WHERE id = ?",
            [$id]
        );
    }

    /**
     * 立替者を削除
     */
    public function remove(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM expedition_car_payers WHERE id = ?",
            [$id]
        ) > 0;
    }
}
