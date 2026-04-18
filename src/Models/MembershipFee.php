<?php
/**
 * 入会金設定モデル
 */
class MembershipFee
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 全入会金設定を取得（提出数・合計数付き）
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT mf.*,
                    COUNT(CASE WHEN mi.submitted=1 THEN 1 END) as submitted_count,
                    COUNT(mi.id) as total_count
             FROM membership_fees mf
             LEFT JOIN membership_fee_items mi ON mi.membership_fee_id = mf.id
             GROUP BY mf.id
             ORDER BY mf.academic_year DESC, mf.id DESC"
        );
    }

    /**
     * id で取得（提出数・合計数付き）
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT mf.*,
                    COUNT(CASE WHEN mi.submitted=1 THEN 1 END) as submitted_count,
                    COUNT(mi.id) as total_count
             FROM membership_fees mf
             LEFT JOIN membership_fee_items mi ON mi.membership_fee_id = mf.id
             WHERE mf.id = ?
             GROUP BY mf.id",
            [$id]
        );
    }

    /**
     * 学年別金額を取得
     */
    public function getGrades(int $membershipFeeId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT grade, amount FROM membership_fee_grades WHERE membership_fee_id = ?",
            [$membershipFeeId]
        );
        // grade => amount のマップに変換
        $map = [];
        foreach ($rows as $row) {
            $map[$row['grade']] = (int)$row['amount'];
        }
        return $map;
    }

    /**
     * 入会金設定を作成し、対象会員のアイテムを初期化する
     */
    public function create(array $data): int
    {
        $targetType = $data['target_type'] ?? 'both';
        $id = $this->db->insert(
            "INSERT INTO membership_fees (academic_year, name, deadline, target_type, is_active) VALUES (?, ?, ?, ?, 1)",
            [(int)$data['academic_year'], $data['name'], $data['deadline'], $targetType]
        );

        // 学年別金額を保存
        if (!empty($data['grades']) && is_array($data['grades'])) {
            $this->saveGrades($id, $data['grades']);
        }

        // 対象会員のアイテムを初期化
        $this->initializeItems($id, (int)$data['academic_year']);

        return $id;
    }

    /**
     * 入会金設定を更新する
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowed = ['name', 'deadline', 'target_type', 'is_active', 'academic_year'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        $updated = false;
        if (!empty($fields)) {
            $values[] = $id;
            $updated = $this->db->execute(
                "UPDATE membership_fees SET " . implode(', ', $fields) . " WHERE id = ?",
                $values
            ) > 0;
        }

        // 学年別金額を更新
        if (array_key_exists('grades', $data) && is_array($data['grades'])) {
            $this->saveGrades($id, $data['grades']);
            $updated = true;
        }

        return $updated;
    }

    /**
     * 学年別金額を保存（REPLACE INTO でupsert）
     */
    public function saveGrades(int $membershipFeeId, array $grades): void
    {
        foreach ($grades as $grade => $amount) {
            $this->db->execute(
                "INSERT INTO membership_fee_grades (membership_fee_id, grade, amount)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE amount = VALUES(amount)",
                [$membershipFeeId, $grade, (int)$amount]
            );
        }
    }

    /**
     * 入会金設定を削除する
     */
    public function delete(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM membership_fees WHERE id = ?",
            [$id]
        ) > 0;
    }

    /**
     * 対象年度の active/ob_og 会員のアイテムを一括初期化する
     */
    public function initializeItems(int $membershipFeeId, int $academicYear): void
    {
        // 該当年度に在籍していた active / ob_og 会員を対象にする
        // academic_years テーブルで年度を管理しているため、membersテーブルから直接対象を取る
        $this->db->execute(
            "INSERT IGNORE INTO membership_fee_items (membership_fee_id, member_id)
             SELECT ?, m.id FROM members m
             WHERE m.status IN ('active', 'ob_og')",
            [$membershipFeeId]
        );
    }

    /**
     * 特定会員をアイテムに追加する（新規入会者向け）
     */
    public function addMember(int $membershipFeeId, int $memberId): void
    {
        $this->db->execute(
            "INSERT IGNORE INTO membership_fee_items (membership_fee_id, member_id) VALUES (?, ?)",
            [$membershipFeeId, $memberId]
        );
    }

    /**
     * 有効な入会金設定を取得する
     * @param string|null $targetType 'new'|'renew'|null(全て)
     */
    public function getActive(?string $targetType = null): array
    {
        if ($targetType !== null) {
            return $this->db->fetchAll(
                "SELECT * FROM membership_fees
                 WHERE is_active = 1
                   AND (target_type = ? OR target_type = 'both')
                 ORDER BY deadline ASC",
                [$targetType]
            );
        }
        return $this->db->fetchAll(
            "SELECT * FROM membership_fees WHERE is_active = 1 ORDER BY deadline ASC"
        );
    }
}
