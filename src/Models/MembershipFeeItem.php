<?php
/**
 * 入会金支払いアイテムモデル
 */
class MembershipFeeItem
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * membership_fee_id に紐づく全アイテムを会員情報・学年別金額付きで取得
     */
    public function getByFeeId(int $membershipFeeId): array
    {
        return $this->db->fetchAll(
            "SELECT mi.*,
                    m.name_kanji, m.name_kana, m.grade, m.gender,
                    COALESCE(mi.custom_amount, fg.amount) as effective_amount
             FROM membership_fee_items mi
             JOIN members m ON m.id = mi.member_id
             LEFT JOIN membership_fee_grades fg
                ON fg.membership_fee_id = mi.membership_fee_id AND fg.grade = m.grade
             WHERE mi.membership_fee_id = ?
             ORDER BY m.name_kana ASC",
            [$membershipFeeId]
        );
    }

    /**
     * ID 指定で取得
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM membership_fee_items WHERE id = ?",
            [$id]
        );
    }

    /**
     * 会員ID と membership_fee_id で取得
     */
    public function findByMemberAndFee(int $memberId, int $membershipFeeId): ?array
    {
        return $this->db->fetch(
            "SELECT mi.*,
                    COALESCE(mi.custom_amount, fg.amount) as effective_amount
             FROM membership_fee_items mi
             LEFT JOIN membership_fee_grades fg
                ON fg.membership_fee_id = mi.membership_fee_id
                AND fg.grade = (SELECT grade FROM members WHERE id = mi.member_id)
             WHERE mi.member_id = ? AND mi.membership_fee_id = ?",
            [$memberId, $membershipFeeId]
        );
    }

    /**
     * 未提出の入会金アイテムを会員ID で取得（入会金・学年別金額付き）
     */
    public function getPendingByMemberId(int $memberId): array
    {
        return $this->db->fetchAll(
            "SELECT mi.*,
                    mf.name as fee_name, mf.deadline, mf.academic_year,
                    COALESCE(mi.custom_amount, fg.amount) as effective_amount
             FROM membership_fee_items mi
             JOIN membership_fees mf ON mf.id = mi.membership_fee_id
             LEFT JOIN membership_fee_grades fg
                ON fg.membership_fee_id = mi.membership_fee_id
                AND fg.grade = (SELECT grade FROM members WHERE id = mi.member_id)
             WHERE mi.member_id = ? AND mi.submitted = 0 AND mf.is_active = 1
             ORDER BY mf.deadline ASC",
            [$memberId]
        );
    }

    /**
     * 会員ID で全アイテムを取得（提出済み含む）
     */
    public function getAllByMemberId(int $memberId): array
    {
        return $this->db->fetchAll(
            "SELECT mi.*,
                    mf.name as fee_name, mf.deadline, mf.academic_year,
                    COALESCE(mi.custom_amount, fg.amount) as effective_amount
             FROM membership_fee_items mi
             JOIN membership_fees mf ON mf.id = mi.membership_fee_id
             LEFT JOIN membership_fee_grades fg
                ON fg.membership_fee_id = mi.membership_fee_id
                AND fg.grade = (SELECT grade FROM members WHERE id = mi.member_id)
             WHERE mi.member_id = ?
             ORDER BY mf.deadline DESC",
            [$memberId]
        );
    }

    /**
     * custom_amount / admin_confirmed を更新する
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowed = ['custom_amount', 'admin_confirmed'];
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
            "UPDATE membership_fee_items SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        ) > 0;
    }

    /**
     * 提出済みにする（submitted=1, submitted_at=NOW(), late_reason を設定）
     */
    public function submit(int $id, ?string $lateReason): bool
    {
        return $this->db->execute(
            "UPDATE membership_fee_items
             SET submitted = 1, submitted_at = NOW(), late_reason = ?
             WHERE id = ?",
            [$lateReason, $id]
        ) > 0;
    }
}
