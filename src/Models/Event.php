<?php
/**
 * 企画モデル
 */
class Event
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 全件取得（申込数・キャンセル待ち数付き）
     */
    public function all(): array
    {
        return $this->db->fetchAll(
            "SELECT e.*,
                    COUNT(CASE WHEN ea.status = 'submitted'  THEN 1 END) AS application_count,
                    COUNT(CASE WHEN ea.status = 'waitlisted' THEN 1 END) AS waitlist_count
             FROM events e
             LEFT JOIN event_applications ea ON ea.event_id = e.id
             GROUP BY e.id
             ORDER BY e.event_date DESC",
            []
        );
    }

    /**
     * ID指定で取得
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT e.*,
                    COUNT(CASE WHEN ea.status = 'submitted'  THEN 1 END) AS application_count,
                    COUNT(CASE WHEN ea.status = 'waitlisted' THEN 1 END) AS waitlist_count
             FROM events e
             LEFT JOIN event_applications ea ON ea.event_id = e.id
             WHERE e.id = ?
             GROUP BY e.id",
            [$id]
        );
    }

    /**
     * 公開中の企画を申込状況付きで取得（会員ホーム用）
     * ・is_active = 1
     * ・deadline が NULL または今日以降
     */
    public function getActiveWithMemberStatus(int $memberId): array
    {
        return $this->db->fetchAll(
            "SELECT e.*,
                    COUNT(CASE WHEN ea.status = 'submitted'  THEN 1 END) AS application_count,
                    COUNT(CASE WHEN ea.status = 'waitlisted' THEN 1 END) AS waitlist_count,
                    my_app.status AS my_status,
                    (SELECT COUNT(*) FROM event_applications
                     WHERE event_id = e.id AND status = 'waitlisted'
                       AND created_at <= my_app.created_at) AS my_waitlist_position
             FROM events e
             LEFT JOIN event_applications ea ON ea.event_id = e.id
             LEFT JOIN event_applications my_app
                    ON my_app.event_id = e.id AND my_app.member_id = ?
             WHERE e.is_active = 1
               AND (e.deadline IS NULL OR e.deadline >= CURDATE())
             GROUP BY e.id
             ORDER BY e.event_date ASC",
            [$memberId]
        );
    }

    /**
     * 新規作成
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO events
                    (title, event_date, event_time, description, location, participation_fee, capacity, deadline, allow_waitlist, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['title'],
            $data['event_date'],
            $data['event_time']   ?? null,
            $data['description']  ?? null,
            $data['location']     ?? null,
            $data['participation_fee'] ?? 0,
            isset($data['capacity']) && $data['capacity'] !== '' ? (int)$data['capacity'] : null,
            isset($data['deadline']) && $data['deadline'] !== '' ? $data['deadline'] : null,
            $data['allow_waitlist'] ?? 0,
            $data['is_active'] ?? 0,
        ]);
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): bool
    {
        $allowed = ['title', 'event_date', 'event_time', 'description', 'location',
                    'participation_fee', 'capacity', 'deadline', 'allow_waitlist', 'is_active'];
        $fields = [];
        $values = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                if ($field === 'capacity' || $field === 'deadline') {
                    $values[] = ($data[$field] !== '' && $data[$field] !== null) ? $data[$field] : null;
                    if ($field === 'capacity' && $values[count($values)-1] !== null) {
                        $values[count($values)-1] = (int)$values[count($values)-1];
                    }
                } else {
                    $values[] = $data[$field];
                }
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        return $this->db->execute(
            "UPDATE events SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        ) >= 0;
    }

    /**
     * 削除（CASCADE で applications/expenses も削除）
     */
    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM events WHERE id = ?", [$id]) > 0;
    }
}
