<?php
/**
 * 遠征車両モデル
 */
class ExpeditionCar
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 遠征IDに紐づく全車両を取得（乗車メンバー・立替者付き）
     */
    public function findByExpedition(int $expedition_id): array
    {
        $cars = $this->db->fetchAll(
            "SELECT * FROM expedition_cars WHERE expedition_id = ? ORDER BY sort_order",
            [$expedition_id]
        );

        foreach ($cars as &$car) {
            // 乗車メンバーを取得
            $car['car_members'] = $this->db->fetchAll(
                "SELECT ecm.*, m.name, m.furigana
                 FROM expedition_car_members ecm
                 JOIN members m ON m.id = ecm.member_id
                 WHERE ecm.car_id = ?
                 ORDER BY ecm.sort_order",
                [$car['id']]
            );

            // 立替者を取得
            $car['car_payers'] = $this->db->fetchAll(
                "SELECT ecp.*, m.name
                 FROM expedition_car_payers ecp
                 JOIN members m ON m.id = ecp.member_id
                 WHERE ecp.car_id = ?",
                [$car['id']]
            );
        }

        return $cars;
    }

    /**
     * 車両を新規作成して作成行を返す
     */
    public function create(int $expedition_id, array $data): ?array
    {
        $id = $this->db->insert(
            "INSERT INTO expedition_cars (expedition_id, name, capacity, rental_fee, highway_fee)
             VALUES (?, ?, ?, ?, ?)",
            [
                $expedition_id,
                $data['name'] ?? '',
                $data['capacity'] ?? null,
                $data['rental_fee'] ?? 0,
                $data['highway_fee'] ?? 0,
            ]
        );

        return $this->db->fetch(
            "SELECT * FROM expedition_cars WHERE id = ?",
            [$id]
        );
    }

    /**
     * 車両情報を更新して更新後の行を返す
     */
    public function update(int $id, array $data): ?array
    {
        $allowedFields = ['name', 'capacity', 'rental_fee', 'highway_fee', 'sort_order'];
        $fields = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return $this->db->fetch("SELECT * FROM expedition_cars WHERE id = ?", [$id]);
        }

        $values[] = $id;
        $this->db->execute(
            "UPDATE expedition_cars SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        );

        return $this->db->fetch("SELECT * FROM expedition_cars WHERE id = ?", [$id]);
    }

    /**
     * 車両を関連データごと削除（トランザクション内で実行）
     */
    public function delete(int $id): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. 立替者を削除
            $this->db->execute(
                "DELETE FROM expedition_car_payers WHERE car_id = ?",
                [$id]
            );

            // 2. 乗車メンバーを削除
            $this->db->execute(
                "DELETE FROM expedition_car_members WHERE car_id = ?",
                [$id]
            );

            // 3. 車両本体を削除
            $result = $this->db->execute(
                "DELETE FROM expedition_cars WHERE id = ?",
                [$id]
            ) > 0;

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * 遠征の車両費用精算を計算する
     * 返り値: ['total_cost' => int, 'per_person' => int, 'settlement' => [['member_id' => int, 'name' => string, 'amount' => int], ...]]
     * amount が正 = 支払い、負 = 返金
     */
    public function calculateSettlement(int $expedition_id): array
    {
        $cars = $this->findByExpedition($expedition_id);

        // 総費用を計算（全車両の rental_fee + highway_fee の合算）
        $total_cost = 0;
        foreach ($cars as $car) {
            $total_cost += (int)$car['rental_fee'] + (int)$car['highway_fee'];
        }

        // is_excluded=0 の乗員数を全車合算
        $total_members = 0;
        foreach ($cars as $car) {
            foreach ($car['car_members'] as $member) {
                if ((int)$member['is_excluded'] === 0) {
                    $total_members++;
                }
            }
        }

        // 1人あたり負担額（切り上げ）
        $per_person = ($total_members > 0)
            ? intval(ceil($total_cost / $total_members))
            : 0;

        // 精算計算
        // 立替者マップを構築: member_id => 立替合計額
        $payer_map = [];
        $payer_names = [];
        foreach ($cars as $car) {
            foreach ($car['car_payers'] as $payer) {
                $mid = (int)$payer['member_id'];
                $payer_map[$mid] = ($payer_map[$mid] ?? 0) + (int)$payer['amount'];
                $payer_names[$mid] = $payer['name'];
            }
        }

        // 対象乗車者マップを構築: member_id => name（is_excluded=0）
        $member_map = [];
        foreach ($cars as $car) {
            foreach ($car['car_members'] as $member) {
                if ((int)$member['is_excluded'] === 0) {
                    $mid = (int)$member['member_id'];
                    if (!isset($member_map[$mid])) {
                        $member_map[$mid] = $member['name'];
                    }
                }
            }
        }

        $settlement = [];

        // 立替者の精算: 返金額 = 立替額 - 1人あたり負担額
        foreach ($payer_map as $member_id => $paid_amount) {
            $amount = $paid_amount - $per_person;
            $settlement[] = [
                'member_id' => $member_id,
                'name'      => $payer_names[$member_id],
                'amount'    => $amount, // 正なら返金、負なら追加支払い
            ];
        }

        // 未立替の乗車者: 支払い額 = 1人あたり負担額
        foreach ($member_map as $member_id => $name) {
            if (!isset($payer_map[$member_id])) {
                $settlement[] = [
                    'member_id' => $member_id,
                    'name'      => $name,
                    'amount'    => $per_person, // 正 = 支払い
                ];
            }
        }

        return [
            'total_cost' => $total_cost,
            'per_person' => $per_person,
            'settlement' => $settlement,
        ];
    }
}
