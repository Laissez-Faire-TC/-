<?php
/**
 * 遠征レンタカー費用申請モデル
 */
class ExpeditionCarExpense
{
    /**
     * 遠征IDで申請一覧を取得
     */
    public static function findByExpedition(int $expedition_id): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT ece.*, m.name_kanji, m.name_kana, m.grade, m.gender
             FROM expedition_car_expenses ece
             JOIN members m ON m.id = ece.member_id
             WHERE ece.expedition_id = ?
             ORDER BY ece.submitted_at ASC",
            [$expedition_id]
        );
    }

    /**
     * 会員・遠征IDで申請を1件取得
     */
    public static function findByMemberAndExpedition(int $member_id, int $expedition_id): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM expedition_car_expenses WHERE member_id = ? AND expedition_id = ?",
            [$member_id, $expedition_id]
        );
    }

    /**
     * 申請を登録または更新（upsert）
     */
    public static function upsert(int $expedition_id, int $member_id, array $data): void
    {
        $db       = Database::getInstance();
        $existing = self::findByMemberAndExpedition($member_id, $expedition_id);

        $rental    = max(0, (int)($data['rental_fee']      ?? 0));
        $gas       = max(0, (int)($data['gas_fee']         ?? 0));
        $highway   = max(0, (int)($data['highway_fee']     ?? 0));
        $other     = max(0, (int)($data['other_fee']       ?? 0));
        $otherDesc = mb_substr(trim($data['other_description'] ?? ''), 0, 255);
        $note      = trim($data['note'] ?? '') ?: null;

        if ($existing) {
            $db->execute(
                "UPDATE expedition_car_expenses
                 SET rental_fee=?, gas_fee=?, highway_fee=?, other_fee=?,
                     other_description=?, note=?, submitted_at=NOW()
                 WHERE id=?",
                [$rental, $gas, $highway, $other, $otherDesc, $note, $existing['id']]
            );
        } else {
            $db->insert(
                "INSERT INTO expedition_car_expenses
                 (expedition_id, member_id, rental_fee, gas_fee, highway_fee, other_fee, other_description, note)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$expedition_id, $member_id, $rental, $gas, $highway, $other, $otherDesc, $note]
            );
        }
    }

    /**
     * 申請を削除
     */
    public static function delete(int $id): void
    {
        Database::getInstance()->execute("DELETE FROM expedition_car_expenses WHERE id = ?", [$id]);
    }
}
