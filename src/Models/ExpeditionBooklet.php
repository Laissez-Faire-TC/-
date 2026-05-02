<?php
/**
 * 遠征しおりモデル
 */
class ExpeditionBooklet
{
    private Database $db;

    /** JSONとして保存するフィールド一覧 */
    private const JSON_FIELDS = [
        'packing_list',
        'car_assignment',
        'team_assignment',
        'room_assignment',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 遠征IDでしおりを取得する
     */
    public function findByExpedition(int $expedition_id): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM expedition_booklets WHERE expedition_id = ?",
            [$expedition_id]
        );

        if (!$row) {
            return null;
        }

        return $this->decode($row);
    }

    /**
     * しおりを保存する（UPSERTパターン）
     *
     * @param int   $expedition_id 遠征ID
     * @param array $items         保存内容（packing_list, car_assignment, team_assignment, room_assignment）
     * @return array|null 保存後の行
     */
    public function save(int $expedition_id, array $items): ?array
    {
        // JSONエンコード
        $encoded = [];
        foreach (self::JSON_FIELDS as $field) {
            if (array_key_exists($field, $items)) {
                $val = $items[$field];
                // 配列の場合はJSONにエンコード、文字列はそのまま保存
                if (is_array($val)) {
                    $encoded[$field] = json_encode($val, JSON_UNESCAPED_UNICODE);
                } else {
                    $encoded[$field] = $val;
                }
            }
        }

        $existing = $this->db->fetch(
            "SELECT id FROM expedition_booklets WHERE expedition_id = ?",
            [$expedition_id]
        );

        if ($existing) {
            // 既存レコードを更新
            if (!empty($encoded)) {
                $sets = implode(', ', array_map(fn($f) => "{$f} = ?", array_keys($encoded)));
                $values = array_values($encoded);
                $values[] = $expedition_id;
                $this->db->execute(
                    "UPDATE expedition_booklets SET {$sets} WHERE expedition_id = ?",
                    $values
                );
            }
        } else {
            // 新規レコードを挿入
            $encoded['expedition_id'] = $expedition_id;
            $cols = implode(', ', array_keys($encoded));
            $placeholders = implode(', ', array_fill(0, count($encoded), '?'));
            $this->db->insert(
                "INSERT INTO expedition_booklets ({$cols}) VALUES ({$placeholders})",
                array_values($encoded)
            );
        }

        return $this->findByExpedition($expedition_id);
    }

    /**
     * しおりを公開する
     * public_tokenを生成してpublished=1に更新する
     *
     * @param int $expedition_id 遠征ID
     * @return array|null 更新後の行
     */
    public function publish(int $expedition_id): ?array
    {
        // ランダムな公開トークンを生成
        $public_token = bin2hex(random_bytes(16));

        $this->db->execute(
            "UPDATE expedition_booklets SET published = 1, public_token = ? WHERE expedition_id = ?",
            [$public_token, $expedition_id]
        );

        return $this->findByExpedition($expedition_id);
    }

    /**
     * 公開トークンでしおりを取得する（公開中のみ）
     *
     * @param string $token 公開トークン
     * @return array|null しおりデータ
     */
    public function findByPublicToken(string $token): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM expedition_booklets WHERE public_token = ? AND published = 1",
            [$token]
        );

        if (!$row) {
            return null;
        }

        return $this->decode($row);
    }

    /**
     * JSONフィールドをデコードする
     */
    private function decode(array $row): array
    {
        foreach (self::JSON_FIELDS as $field) {
            if (isset($row[$field]) && is_string($row[$field])) {
                // JSON文字列の場合はデコード、失敗時はそのまま返す
                $decoded = json_decode($row[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $row[$field] = $decoded;
                }
            }
        }
        return $row;
    }
}
