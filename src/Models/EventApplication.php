<?php
/**
 * 企画申し込みモデル
 */
class EventApplication
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 参加確定者一覧（status = submitted）
     */
    public function getByEventId(int $eventId): array
    {
        return $this->db->fetchAll(
            "SELECT ea.*, m.name_kanji, m.name_kana, m.student_id, m.grade, m.gender,
                    m.faculty, m.department, m.line_name
             FROM event_applications ea
             JOIN members m ON m.id = ea.member_id
             WHERE ea.event_id = ? AND ea.status = 'submitted'
             ORDER BY ea.created_at ASC",
            [$eventId]
        );
    }

    /**
     * キャンセル待ち一覧（status = waitlisted、登録順）
     */
    public function getWaitlistByEventId(int $eventId): array
    {
        return $this->db->fetchAll(
            "SELECT ea.*, m.name_kanji, m.name_kana, m.student_id, m.grade, m.gender,
                    m.faculty, m.department, m.line_name
             FROM event_applications ea
             JOIN members m ON m.id = ea.member_id
             WHERE ea.event_id = ? AND ea.status = 'waitlisted'
             ORDER BY ea.created_at ASC",
            [$eventId]
        );
    }

    /**
     * 参加確定人数（submitted のみ）
     */
    public function countByEventId(int $eventId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM event_applications WHERE event_id = ? AND status = 'submitted'",
            [$eventId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * キャンセル待ち人数
     */
    public function countWaitlistByEventId(int $eventId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM event_applications WHERE event_id = ? AND status = 'waitlisted'",
            [$eventId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * 会員の申込状況確認
     */
    public function findByEventAndMember(int $eventId, int $memberId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM event_applications WHERE event_id = ? AND member_id = ?",
            [$eventId, $memberId]
        );
    }

    /**
     * 申し込み（UPSERT）
     * $status = 'submitted' or 'waitlisted'
     */
    public function apply(int $eventId, int $memberId, string $status = 'submitted'): bool
    {
        $existing = $this->findByEventAndMember($eventId, $memberId);

        if ($existing) {
            return $this->db->execute(
                "UPDATE event_applications SET status = ?, promoted = 0, updated_at = NOW()
                 WHERE event_id = ? AND member_id = ?",
                [$status, $eventId, $memberId]
            ) >= 0;
        }

        $this->db->insert(
            "INSERT INTO event_applications (event_id, member_id, status, promoted) VALUES (?, ?, ?, 0)",
            [$eventId, $memberId, $status]
        );
        return true;
    }

    /**
     * キャンセル
     * 戻り値: キャンセル前のstatus（繰り上げ判定用）
     */
    public function cancel(int $eventId, int $memberId): string
    {
        $existing = $this->findByEventAndMember($eventId, $memberId);
        $prevStatus = $existing ? $existing['status'] : 'cancelled';

        $this->db->execute(
            "UPDATE event_applications SET status = 'cancelled', updated_at = NOW()
             WHERE event_id = ? AND member_id = ?",
            [$eventId, $memberId]
        );

        return $prevStatus;
    }

    /**
     * 管理者によるキャンセル（ID指定）
     * 戻り値: キャンセル前のstatus
     */
    public function cancelById(int $id): string
    {
        $existing = $this->db->fetch(
            "SELECT * FROM event_applications WHERE id = ?", [$id]
        );
        $prevStatus = $existing ? $existing['status'] : 'cancelled';

        $this->db->execute(
            "UPDATE event_applications SET status = 'cancelled', updated_at = NOW() WHERE id = ?",
            [$id]
        );

        return $prevStatus;
    }

    /**
     * キャンセル待ちの先頭を繰り上げ
     * 繰り上げた場合: その申込レコードを返す。待ち無し: null
     */
    public function promoteFromWaitlist(int $eventId): ?array
    {
        $next = $this->db->fetch(
            "SELECT * FROM event_applications
             WHERE event_id = ? AND status = 'waitlisted'
             ORDER BY created_at ASC LIMIT 1",
            [$eventId]
        );

        if (!$next) {
            return null;
        }

        $this->db->execute(
            "UPDATE event_applications
             SET status = 'submitted', promoted = 1, updated_at = NOW()
             WHERE id = ?",
            [$next['id']]
        );

        return $next;
    }
}
