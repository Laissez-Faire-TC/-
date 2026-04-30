<?php
class MemberChangeNotification
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $memberId, string $memberName, string $studentId, array $changes): int
    {
        return $this->db->insert(
            "INSERT INTO member_change_notifications (member_id, member_name, student_id, changes_json)
             VALUES (?, ?, ?, ?)",
            [$memberId, $memberName, $studentId, json_encode($changes, JSON_UNESCAPED_UNICODE)]
        );
    }

    public function getUnread(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM member_change_notifications WHERE read_at IS NULL ORDER BY created_at DESC"
        );
    }

    public function markAsRead(int $id): void
    {
        $this->db->execute(
            "UPDATE member_change_notifications SET read_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    public function countUnread(): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM member_change_notifications WHERE read_at IS NULL"
        );
        return (int)($row['cnt'] ?? 0);
    }
}
