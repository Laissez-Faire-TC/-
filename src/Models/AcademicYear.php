<?php
/**
 * 年度管理モデル
 */

if (!class_exists('AcademicYear')) {

class AcademicYear
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * ID指定で取得
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM academic_years WHERE id = ?",
            [$id]
        );
    }

    /**
     * 年度指定で取得
     */
    public function findByYear(int $year): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM academic_years WHERE year = ?",
            [$year]
        );
    }

    /**
     * 現在年度を取得
     */
    public function getCurrent(): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM academic_years WHERE is_current = 1 LIMIT 1"
        );
    }

    /**
     * 全年度を取得（新しい順）
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM academic_years ORDER BY year DESC"
        );
    }

    /**
     * 新規作成
     *
     * @param array $data ['year', 'start_date', 'end_date', 'enrollment_open']
     * @return int 挿入されたID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO academic_years (year, start_date, end_date, is_current, enrollment_open)
                VALUES (?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['year'],
            $data['start_date'],
            $data['end_date'],
            $data['is_current'] ?? 0,
            $data['enrollment_open'] ?? 0,
        ]);
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = ['year', 'start_date', 'end_date', 'is_current', 'enrollment_open', 'enrollment_deadline', 'renew_deadline', 'enroll_open', 'renew_open'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE academic_years SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->execute($sql, $values) > 0;
    }

    /**
     * 現在年度を切り替え
     *
     * @param int $year 新しい現在年度
     * @return bool 成功/失敗
     */
    public function setCurrentYear(int $year): bool
    {
        $this->db->beginTransaction();

        try {
            // 全ての年度の is_current を 0 にする
            $this->db->execute("UPDATE academic_years SET is_current = 0");

            // 指定年度の is_current を 1 にする
            $result = $this->db->execute(
                "UPDATE academic_years SET is_current = 1 WHERE year = ?",
                [$year]
            );

            $this->db->commit();
            return $result > 0;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * 年度が存在するかチェック
     */
    public function exists(int $year): bool
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM academic_years WHERE year = ?",
            [$year]
        );
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * 次の年度を自動作成（現在年度+1）
     *
     * @return int 作成された年度のID
     */
    public function createNextYear(): int
    {
        $current = $this->getCurrent();
        if (!$current) {
            throw new Exception('現在年度が設定されていません');
        }

        $nextYear = (int)$current['year'] + 1;

        // 既に存在する場合はエラー
        if ($this->exists($nextYear)) {
            throw new Exception("{$nextYear}年度は既に存在します");
        }

        // 次年度の日付を計算（例: 2026年4月1日 ～ 2027年3月31日）
        $startDate = $nextYear . '-04-01';
        $endDate = ($nextYear + 1) . '-03-31';

        return $this->create([
            'year' => $nextYear,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_current' => 0,
            'enrollment_open' => 0,
        ]);
    }

    /**
     * 新規入会受付中の年度を取得（enroll_open = 1）
     */
    public function getEnrollOpenYear(): ?array
    {
        $result = $this->db->fetch(
            "SELECT * FROM academic_years WHERE enroll_open = 1 LIMIT 1"
        );
        return $result ?: null;
    }

    /**
     * 継続入会受付中の年度を取得（renew_open = 1）
     */
    public function getRenewOpenYear(): ?array
    {
        $result = $this->db->fetch(
            "SELECT * FROM academic_years WHERE renew_open = 1 LIMIT 1"
        );
        return $result ?: null;
    }

    /**
     * @deprecated getEnrollOpenYear() / getRenewOpenYear() を使用してください
     * 後方互換のため残存（enroll_open または renew_open が 1 の年度を返す）
     */
    public function getEnrollmentOpenYear(): ?array
    {
        $result = $this->db->fetch(
            "SELECT * FROM academic_years WHERE enroll_open = 1 OR renew_open = 1 LIMIT 1"
        );
        return $result ?: null;
    }

    /**
     * 新規入会フォームの受付開始/停止
     * ONにする場合は他の年度をすべてOFFにする
     */
    public function setEnrollOpen(int $year, bool $open): bool
    {
        if ($open) {
            $this->db->execute("UPDATE academic_years SET enroll_open = 0");
        }
        return $this->db->execute(
            "UPDATE academic_years SET enroll_open = ? WHERE year = ?",
            [$open ? 1 : 0, $year]
        ) > 0;
    }

    /**
     * 継続入会フォームの受付開始/停止
     * ONにする場合は他の年度をすべてOFFにする
     */
    public function setRenewOpen(int $year, bool $open): bool
    {
        if ($open) {
            $this->db->execute("UPDATE academic_years SET renew_open = 0");
        }
        return $this->db->execute(
            "UPDATE academic_years SET renew_open = ? WHERE year = ?",
            [$open ? 1 : 0, $year]
        ) > 0;
    }

    /**
     * @deprecated setEnrollOpen / setRenewOpen を使用してください
     */
    public function setEnrollmentOpen(int $year, bool $open): bool
    {
        return $this->setEnrollOpen($year, $open);
    }
}

}
