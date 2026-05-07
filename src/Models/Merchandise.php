<?php
/**
 * 物販商品モデル
 */
class Merchandise
{
    public static function findAll(): array
    {
        $rows = Database::getInstance()->fetchAll(
            "SELECT m.*, COUNT(DISTINCT moi.order_id) as order_count
             FROM merchandise m
             LEFT JOIN merchandise_order_items moi ON moi.merchandise_id = m.id
             GROUP BY m.id
             ORDER BY m.sort_order ASC, m.id DESC"
        );
        return $rows;
    }

    public static function findById(int $id): ?array
    {
        $row = Database::getInstance()->fetch(
            "SELECT * FROM merchandise WHERE id = ?",
            [$id]
        );
        if (!$row) return null;

        $row['colors'] = self::getColors($id);
        $row['sizes']  = self::getSizes($id);
        return $row;
    }

    public static function getColors(int $merchandise_id): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM merchandise_colors WHERE merchandise_id = ? ORDER BY sort_order ASC, id ASC",
            [$merchandise_id]
        );
    }

    public static function getSizes(int $merchandise_id): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM merchandise_sizes WHERE merchandise_id = ? ORDER BY sort_order ASC, id ASC",
            [$merchandise_id]
        );
    }

    public static function create(array $data): ?array
    {
        $id = Database::getInstance()->insert(
            "INSERT INTO merchandise (name, description, price, sale_start, sale_end, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['description'] ?? null,
                (int)($data['price'] ?? 0),
                $data['sale_start'] ?: null,
                $data['sale_end']   ?: null,
                isset($data['is_active']) ? (int)$data['is_active'] : 1,
                (int)($data['sort_order'] ?? 0),
            ]
        );
        return self::findById($id);
    }

    public static function update(int $id, array $data): ?array
    {
        $allowed = ['name', 'description', 'price', 'sale_start', 'sale_end', 'is_active', 'sort_order'];
        $fields  = [];
        $values  = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                if (in_array($f, ['sale_start', 'sale_end'])) {
                    $values[] = $data[$f] !== '' ? $data[$f] : null;
                } elseif (in_array($f, ['price', 'is_active', 'sort_order'])) {
                    $values[] = (int)$data[$f];
                } else {
                    $values[] = $data[$f];
                }
            }
        }
        if (empty($fields)) return self::findById($id);

        $values[] = $id;
        Database::getInstance()->execute(
            "UPDATE merchandise SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        );
        return self::findById($id);
    }

    public static function delete(int $id): bool
    {
        return Database::getInstance()->execute(
            "DELETE FROM merchandise WHERE id = ?",
            [$id]
        ) > 0;
    }

    /**
     * 色を保存（送られた配列で完全置換）
     * 各要素: ['color_name' => string, 'image_path' => string|null, 'sort_order' => int]
     */
    public static function saveColors(int $merchandise_id, array $colors): void
    {
        $db = Database::getInstance();
        $db->execute("DELETE FROM merchandise_colors WHERE merchandise_id = ?", [$merchandise_id]);
        foreach ($colors as $i => $c) {
            $name = trim($c['color_name'] ?? '');
            if ($name === '') continue;
            $db->insert(
                "INSERT INTO merchandise_colors (merchandise_id, color_name, image_path, sort_order)
                 VALUES (?, ?, ?, ?)",
                [
                    $merchandise_id,
                    $name,
                    $c['image_path'] ?? null,
                    (int)($c['sort_order'] ?? $i),
                ]
            );
        }
    }

    /**
     * サイズを保存（完全置換）
     */
    public static function saveSizes(int $merchandise_id, array $sizes): void
    {
        $db = Database::getInstance();
        $db->execute("DELETE FROM merchandise_sizes WHERE merchandise_id = ?", [$merchandise_id]);
        foreach ($sizes as $i => $s) {
            $name = trim($s['size_name'] ?? '');
            if ($name === '') continue;
            $db->insert(
                "INSERT INTO merchandise_sizes (merchandise_id, size_name, sort_order)
                 VALUES (?, ?, ?)",
                [
                    $merchandise_id,
                    $name,
                    (int)($s['sort_order'] ?? $i),
                ]
            );
        }
    }

    /**
     * 現在販売可能な商品を取得（is_active=1 かつ 販売期間内）
     */
    public static function findAvailable(): array
    {
        $now  = date('Y-m-d H:i:s');
        $rows = Database::getInstance()->fetchAll(
            "SELECT * FROM merchandise
             WHERE is_active = 1
               AND (sale_start IS NULL OR sale_start <= ?)
               AND (sale_end   IS NULL OR sale_end   >= ?)
             ORDER BY sort_order ASC, id DESC",
            [$now, $now]
        );
        foreach ($rows as &$r) {
            $r['colors'] = self::getColors((int)$r['id']);
            $r['sizes']  = self::getSizes((int)$r['id']);
        }
        return $rows;
    }
}
