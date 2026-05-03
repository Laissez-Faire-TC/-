<?php
/**
 * 遠征車両立替者モデル
 */
class ExpeditionCarPayer
{
    /**
     * 車両IDに紐づく立替者一覧を取得
     */
    public static function findByCar(int $car_id): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT ecp.*, m.name_kanji
             FROM expedition_car_payers ecp
             JOIN members m ON m.id = ecp.member_id
             WHERE ecp.car_id = ?",
            [$car_id]
        );
    }

    /**
     * 立替者を追加して作成行を返す
     */
    public static function add(int $car_id, int $member_id, int $amount): ?array
    {
        $db = Database::getInstance();
        $id = $db->insert(
            "INSERT INTO expedition_car_payers (car_id, member_id, amount) VALUES (?, ?, ?)",
            [$car_id, $member_id, $amount]
        );

        return $db->fetch("SELECT * FROM expedition_car_payers WHERE id = ?", [$id]);
    }

    /**
     * 立替者を削除
     */
    public static function remove(int $id): bool
    {
        return Database::getInstance()->execute(
            "DELETE FROM expedition_car_payers WHERE id = ?",
            [$id]
        ) > 0;
    }
}
