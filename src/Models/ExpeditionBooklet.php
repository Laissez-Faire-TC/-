<?php
/**
 * 遠征しおりモデル
 * expedition_booklets.items に JSON として保存する
 */
class ExpeditionBooklet
{
    /** デフォルト値 */
    private static function defaults(): array
    {
        return [
            'venue'            => '',
            'meeting_note'     => '',
            'items_to_bring'   => [],
            'car_assignment'   => '',
            'team_assignment'  => '',
            'room_assignments' => [],
            'notes'            => '',
        ];
    }

    /**
     * 遠征IDでしおりを取得する
     */
    public static function findByExpedition(int $expedition_id): ?array
    {
        $row = Database::getInstance()->fetch(
            "SELECT * FROM expedition_booklets WHERE expedition_id = ?",
            [$expedition_id]
        );

        return $row ? self::decode($row) : null;
    }

    /**
     * しおりを保存する（UPSERTパターン）
     * $data: フラットな連想配列（meeting_time, items_to_bring[] など）
     */
    public static function save(int $expedition_id, array $data): ?array
    {
        $db       = Database::getInstance();
        $defaults = self::defaults();

        // 許可フィールドのみ抽出
        $filtered = [];
        foreach ($defaults as $field => $default) {
            $filtered[$field] = $data[$field] ?? $default;
        }

        // 配列フィールドは配列として保証
        foreach (['items_to_bring', 'schedules', 'room_assignments'] as $f) {
            if (!is_array($filtered[$f])) {
                $filtered[$f] = [];
            }
        }

        $encoded  = json_encode($filtered, JSON_UNESCAPED_UNICODE);
        $existing = $db->fetch(
            "SELECT id FROM expedition_booklets WHERE expedition_id = ?",
            [$expedition_id]
        );

        if ($existing) {
            $db->execute(
                "UPDATE expedition_booklets SET items = ? WHERE expedition_id = ?",
                [$encoded, $expedition_id]
            );
        } else {
            $db->insert(
                "INSERT INTO expedition_booklets (expedition_id, items) VALUES (?, ?)",
                [$expedition_id, $encoded]
            );
        }

        return self::findByExpedition($expedition_id);
    }

    /**
     * しおりを公開する（レコードが存在しない場合は新規作成）
     */
    public static function publish(int $expedition_id): ?array
    {
        $db           = Database::getInstance();
        $public_token = bin2hex(random_bytes(16));

        $existing = $db->fetch(
            "SELECT id FROM expedition_booklets WHERE expedition_id = ?",
            [$expedition_id]
        );

        if ($existing) {
            $db->execute(
                "UPDATE expedition_booklets SET published = 1, public_token = ? WHERE expedition_id = ?",
                [$public_token, $expedition_id]
            );
        } else {
            $db->insert(
                "INSERT INTO expedition_booklets (expedition_id, items, published, public_token) VALUES (?, ?, 1, ?)",
                [$expedition_id, json_encode(self::defaults(), JSON_UNESCAPED_UNICODE), $public_token]
            );
        }

        return self::findByExpedition($expedition_id);
    }

    /**
     * 公開トークンでしおりを取得する（公開中のみ、遠征情報込み）
     */
    public static function findByPublicToken(string $token): ?array
    {
        $row = Database::getInstance()->fetch(
            "SELECT eb.*, e.name AS expedition_name, e.start_date, e.end_date
             FROM expedition_booklets eb
             JOIN expeditions e ON e.id = eb.expedition_id
             WHERE eb.public_token = ? AND eb.published = 1",
            [$token]
        );

        return $row ? self::decode($row) : null;
    }

    /**
     * items JSONをデコードしてトップレベルにマージする
     */
    private static function decode(array $row): array
    {
        $defaults = self::defaults();

        if (isset($row['items']) && is_string($row['items'])) {
            $decoded = json_decode($row['items'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // 配列フィールドは配列として保証
                foreach (['items_to_bring', 'schedules', 'room_assignments'] as $f) {
                    if (isset($decoded[$f]) && !is_array($decoded[$f])) {
                        $decoded[$f] = [];
                    }
                }
                $defaults = array_merge($defaults, $decoded);
            }
        }

        foreach ($defaults as $k => $v) {
            $row[$k] = $v;
        }

        return $row;
    }
}
