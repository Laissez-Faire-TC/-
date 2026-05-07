<?php
/**
 * 物販注文モデル
 */
class MerchandiseOrder
{
    /**
     * 全注文を取得（明細・購入者情報付き）
     */
    public static function findAll(?string $paymentStatus = null): array
    {
        $where  = '';
        $params = [];
        if ($paymentStatus !== null) {
            $where    = ' WHERE o.payment_status = ?';
            $params[] = $paymentStatus;
        }

        $orders = Database::getInstance()->fetchAll(
            "SELECT o.*, m.name_kanji as member_name_kanji, m.student_id as member_student_id
             FROM merchandise_orders o
             LEFT JOIN members m ON m.id = o.member_id
             {$where}
             ORDER BY o.created_at DESC",
            $params
        );

        foreach ($orders as &$o) {
            $o['items'] = self::getItems((int)$o['id']);
        }
        return $orders;
    }

    public static function findById(int $id): ?array
    {
        $order = Database::getInstance()->fetch(
            "SELECT o.*, m.name_kanji as member_name_kanji, m.student_id as member_student_id
             FROM merchandise_orders o
             LEFT JOIN members m ON m.id = o.member_id
             WHERE o.id = ?",
            [$id]
        );
        if (!$order) return null;
        $order['items'] = self::getItems($id);
        return $order;
    }

    public static function getItems(int $order_id): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM merchandise_order_items WHERE order_id = ? ORDER BY id ASC",
            [$order_id]
        );
    }

    /**
     * 商品別の注文集計（管理画面の「商品別売上」用）
     */
    public static function summaryByMerchandise(int $merchandise_id): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT moi.color_name, moi.size_name,
                    SUM(moi.quantity) as total_quantity,
                    SUM(moi.subtotal) as total_amount,
                    COUNT(DISTINCT moi.order_id) as order_count
             FROM merchandise_order_items moi
             JOIN merchandise_orders o ON o.id = moi.order_id
             WHERE moi.merchandise_id = ?
               AND o.payment_status != 'cancelled'
             GROUP BY moi.color_name, moi.size_name
             ORDER BY moi.color_name, moi.size_name",
            [$merchandise_id]
        );
    }

    /**
     * 注文を作成（明細含む）
     * cart: [{merchandise_id, color_id, size_id, quantity}, ...]
     * buyer: {name, kana, contact, member_id, notes}
     */
    public static function create(array $cart, array $buyer): ?array
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 各商品の現在価格・色名・サイズ名を取得しつつ合計を計算
            $items = [];
            $total = 0;
            foreach ($cart as $line) {
                $mid = (int)($line['merchandise_id'] ?? 0);
                $qty = max(1, (int)($line['quantity'] ?? 1));
                if ($mid <= 0 || $qty <= 0) continue;

                $merch = $db->fetch(
                    "SELECT id, name, price FROM merchandise WHERE id = ? AND is_active = 1",
                    [$mid]
                );
                if (!$merch) {
                    throw new Exception('商品が見つかりません');
                }

                $colorName = null;
                $colorId   = null;
                if (!empty($line['color_id'])) {
                    $color = $db->fetch(
                        "SELECT id, color_name FROM merchandise_colors WHERE id = ? AND merchandise_id = ?",
                        [(int)$line['color_id'], $mid]
                    );
                    if ($color) {
                        $colorId   = (int)$color['id'];
                        $colorName = $color['color_name'];
                    }
                }

                $sizeName = null;
                $sizeId   = null;
                if (!empty($line['size_id'])) {
                    $size = $db->fetch(
                        "SELECT id, size_name FROM merchandise_sizes WHERE id = ? AND merchandise_id = ?",
                        [(int)$line['size_id'], $mid]
                    );
                    if ($size) {
                        $sizeId   = (int)$size['id'];
                        $sizeName = $size['size_name'];
                    }
                }

                $unit     = (int)$merch['price'];
                $subtotal = $unit * $qty;
                $total   += $subtotal;

                $items[] = [
                    'merchandise_id'   => $mid,
                    'merchandise_name' => $merch['name'],
                    'color_id'         => $colorId,
                    'color_name'       => $colorName,
                    'size_id'          => $sizeId,
                    'size_name'        => $sizeName,
                    'quantity'         => $qty,
                    'unit_price'       => $unit,
                    'subtotal'         => $subtotal,
                ];
            }

            if (empty($items)) {
                throw new Exception('カートが空です');
            }

            $orderId = $db->insert(
                "INSERT INTO merchandise_orders
                 (member_id, pending_student_id, buyer_name, buyer_kana, pending_line_name, pending_phone, buyer_contact, total_amount, payment_status, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'unpaid', ?)",
                [
                    !empty($buyer['member_id']) ? (int)$buyer['member_id'] : null,
                    !empty($buyer['pending_student_id']) ? trim($buyer['pending_student_id']) : null,
                    trim($buyer['name'] ?? ''),
                    trim($buyer['kana']               ?? '') ?: null,
                    trim($buyer['pending_line_name']  ?? '') ?: null,
                    trim($buyer['pending_phone']      ?? '') ?: null,
                    trim($buyer['contact']            ?? '') ?: null,
                    $total,
                    trim($buyer['notes']              ?? '') ?: null,
                ]
            );

            foreach ($items as $it) {
                $db->insert(
                    "INSERT INTO merchandise_order_items
                     (order_id, merchandise_id, color_id, size_id, color_name, size_name, merchandise_name, quantity, unit_price, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $orderId,
                        $it['merchandise_id'],
                        $it['color_id'],
                        $it['size_id'],
                        $it['color_name'],
                        $it['size_name'],
                        $it['merchandise_name'],
                        $it['quantity'],
                        $it['unit_price'],
                        $it['subtotal'],
                    ]
                );
            }

            $db->commit();
            return self::findById($orderId);

        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 振込確認状態を切り替え（paid <-> unpaid）
     */
    public static function togglePaid(int $id): ?array
    {
        $db = Database::getInstance();
        $row = $db->fetch("SELECT payment_status FROM merchandise_orders WHERE id = ?", [$id]);
        if (!$row) return null;

        $newStatus = $row['payment_status'] === 'paid' ? 'unpaid' : 'paid';
        $paidAt    = $newStatus === 'paid' ? date('Y-m-d H:i:s') : null;

        $db->execute(
            "UPDATE merchandise_orders SET payment_status = ?, paid_at = ? WHERE id = ?",
            [$newStatus, $paidAt, $id]
        );
        return self::findById($id);
    }

    public static function updateStatus(int $id, string $status): ?array
    {
        $allowed = ['unpaid', 'paid', 'cancelled'];
        if (!in_array($status, $allowed)) return null;

        $paidAt = $status === 'paid' ? date('Y-m-d H:i:s') : null;
        Database::getInstance()->execute(
            "UPDATE merchandise_orders SET payment_status = ?, paid_at = ? WHERE id = ?",
            [$status, $paidAt, $id]
        );
        return self::findById($id);
    }

    public static function delete(int $id): bool
    {
        return Database::getInstance()->execute(
            "DELETE FROM merchandise_orders WHERE id = ?",
            [$id]
        ) > 0;
    }

    /**
     * 未マッチ注文一覧（pending_student_id があり member_id が NULL）
     */
    public static function findPending(): array
    {
        $orders = Database::getInstance()->fetchAll(
            "SELECT o.*,
                    m.id           AS matched_member_id,
                    m.name_kanji   AS matched_member_name
             FROM merchandise_orders o
             LEFT JOIN members m ON m.student_id = o.pending_student_id
             WHERE o.member_id IS NULL
               AND o.pending_student_id IS NOT NULL
             ORDER BY o.created_at DESC"
        );
        foreach ($orders as &$o) {
            $o['items'] = self::getItems((int)$o['id']);
        }
        return $orders;
    }

    /**
     * 未マッチ注文を会員DBと突合し、見つかれば紐付け
     * @return array ['matched' => int, 'unmatched' => int]
     */
    public static function matchAllPending(): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT o.id, m.id AS member_id
             FROM merchandise_orders o
             JOIN members m ON m.student_id = o.pending_student_id
             WHERE o.member_id IS NULL
               AND o.pending_student_id IS NOT NULL"
        );
        $matched = 0;
        foreach ($rows as $r) {
            $db->execute(
                "UPDATE merchandise_orders SET member_id = ? WHERE id = ?",
                [(int)$r['member_id'], (int)$r['id']]
            );
            $matched++;
        }
        $unmatched = (int)($db->fetch(
            "SELECT COUNT(*) AS c FROM merchandise_orders
             WHERE member_id IS NULL AND pending_student_id IS NOT NULL"
        )['c'] ?? 0);

        return ['matched' => $matched, 'unmatched' => $unmatched];
    }

    /**
     * 単一の会員IDに対し、同じ学籍番号の未マッチ注文を紐付け
     * 会員追加時のフックから呼ぶ
     */
    public static function matchByStudentId(string $studentId, int $memberId): int
    {
        if ($studentId === '') return 0;
        return Database::getInstance()->execute(
            "UPDATE merchandise_orders
             SET member_id = ?
             WHERE member_id IS NULL
               AND pending_student_id = ?",
            [$memberId, $studentId]
        );
    }

    /**
     * 注文を手動で会員に紐付ける
     */
    public static function linkToMember(int $orderId, int $memberId): bool
    {
        return Database::getInstance()->execute(
            "UPDATE merchandise_orders SET member_id = ? WHERE id = ?",
            [$memberId, $orderId]
        ) > 0;
    }
}
