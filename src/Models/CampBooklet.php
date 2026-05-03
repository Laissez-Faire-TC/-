<?php
/**
 * 合宿しおりモデル
 */
class CampBooklet
{
    private Database $db;

    private const JSON_FIELDS = [
        'items_to_bring',
        'schedules',
        'team_battle_teams',
        'team_battle_schedule',
        'kohaku_teams',
        'kohaku_matches',
        'night_rec_groups',
        'room_assignments',
        'meal_duty',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByCampId(int $campId): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM camp_booklets WHERE camp_id = ?",
            [$campId]
        );

        if (!$row) {
            return null;
        }

        return $this->decode($row);
    }

    public function findByToken(string $token): ?array
    {
        $row = $this->db->fetch(
            "SELECT b.*, c.name as camp_name, c.start_date, c.end_date
             FROM camp_booklets b
             JOIN camps c ON b.camp_id = c.id
             WHERE b.public_token = ?",
            [$token]
        );

        if (!$row) {
            return null;
        }

        return $this->decode($row);
    }

    public function upsert(int $campId, array $data): bool
    {
        $existing = $this->db->fetch(
            "SELECT id FROM camp_booklets WHERE camp_id = ?",
            [$campId]
        );

        $allowedFields = [
            'meeting_time', 'meeting_place', 'meeting_note', 'return_place',
            'items_to_bring', 'schedules',
            'team_battle_teams', 'team_battle_rules', 'team_battle_schedule',
            'kohaku_teams', 'kohaku_rules', 'kohaku_matches',
            'night_rec_groups', 'room_assignments',
            'floor_plan_image', 'meal_duty',
            'is_public',
        ];

        $filtered = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $val = $data[$field];
                if (in_array($field, self::JSON_FIELDS) && is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                $filtered[$field] = $val;
            }
        }

        if (empty($filtered)) {
            return false;
        }

        if ($existing) {
            $sets = implode(', ', array_map(fn($f) => "{$f} = ?", array_keys($filtered)));
            $values = array_values($filtered);
            $values[] = $campId;
            return $this->db->execute(
                "UPDATE camp_booklets SET {$sets} WHERE camp_id = ?",
                $values
            ) >= 0;
        } else {
            // 新規作成時にトークン生成
            $filtered['camp_id'] = $campId;
            $filtered['public_token'] = bin2hex(random_bytes(16));

            $cols = implode(', ', array_keys($filtered));
            $placeholders = implode(', ', array_fill(0, count($filtered), '?'));
            $this->db->insert(
                "INSERT INTO camp_booklets ({$cols}) VALUES ({$placeholders})",
                array_values($filtered)
            );
            return true;
        }
    }

    /**
     * 公開中のしおりがある合宿一覧を取得
     */
    public function getPublicCamps(): array
    {
        return $this->db->fetchAll(
            "SELECT c.id, c.name, c.start_date, c.end_date
             FROM camp_booklets b
             JOIN camps c ON b.camp_id = c.id
             WHERE b.is_public = 1
             ORDER BY c.start_date DESC"
        );
    }

    public function generateToken(int $campId): string
    {
        $token = bin2hex(random_bytes(16));
        $existing = $this->db->fetch(
            "SELECT id FROM camp_booklets WHERE camp_id = ?",
            [$campId]
        );

        if ($existing) {
            // トークン再発行と同時に is_public=1 にする
            $this->db->execute(
                "UPDATE camp_booklets SET public_token = ?, is_public = 1 WHERE camp_id = ?",
                [$token, $campId]
            );
        } else {
            $this->db->insert(
                "INSERT INTO camp_booklets (camp_id, public_token, is_public) VALUES (?, ?, 1)",
                [$campId, $token]
            );
        }

        return $token;
    }

    private function decode(array $row): array
    {
        foreach (self::JSON_FIELDS as $field) {
            if (isset($row[$field]) && is_string($row[$field])) {
                $row[$field] = json_decode($row[$field], true) ?? [];
            } elseif (!isset($row[$field])) {
                $row[$field] = [];
            }
        }
        return $row;
    }
}
