<?php
/**
 * 遠征車両モデル
 */
class ExpeditionCar
{
    /**
     * 遠征IDに紐づく全車両を取得（乗車メンバー・立替者付き）
     */
    public static function findByExpedition(int $expedition_id): array
    {
        $db = Database::getInstance();
        $cars = $db->fetchAll(
            "SELECT * FROM expedition_cars WHERE expedition_id = ? ORDER BY sort_order",
            [$expedition_id]
        );

        foreach ($cars as &$car) {
            // 乗車メンバーを取得
            $car['car_members'] = $db->fetchAll(
                "SELECT ecm.*, m.name_kanji, m.name_kana,
                        ep.friday_last_class, ep.timescar_number
                 FROM expedition_car_members ecm
                 JOIN members m ON m.id = ecm.member_id
                 LEFT JOIN expedition_participants ep
                        ON ep.member_id = ecm.member_id AND ep.expedition_id = ?
                 WHERE ecm.car_id = ?
                 ORDER BY ecm.sort_order",
                [$expedition_id, $car['id']]
            );
            // 立替者を取得
            $car['car_payers'] = $db->fetchAll(
                "SELECT ecp.*, m.name_kanji
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
    public static function create(int $expedition_id, array $data): ?array
    {
        $db        = Database::getInstance();
        $tripType  = in_array($data['trip_type'] ?? '', ['outbound','return','both']) ? $data['trip_type'] : 'both';
        $depClass  = ($tripType === 'outbound' && isset($data['departure_class']) && $data['departure_class'] !== '')
                     ? (int)$data['departure_class'] : null;

        $id = $db->insert(
            "INSERT INTO expedition_cars (expedition_id, name, capacity, rental_fee, highway_fee, trip_type, departure_class)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $expedition_id,
                $data['name'] ?? '',
                $data['capacity'] ?? 5,
                $data['rental_fee'] ?? 0,
                $data['highway_fee'] ?? 0,
                $tripType,
                $depClass,
            ]
        );

        return $db->fetch("SELECT * FROM expedition_cars WHERE id = ?", [$id]);
    }

    /**
     * 車両情報を更新して更新後の行を返す
     */
    public static function update(int $id, array $data): ?array
    {
        $db = Database::getInstance();
        $allowedFields = ['name', 'capacity', 'rental_fee', 'highway_fee', 'sort_order', 'trip_type', 'departure_class'];
        $fields = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return $db->fetch("SELECT * FROM expedition_cars WHERE id = ?", [$id]);
        }

        $values[] = $id;
        $db->execute(
            "UPDATE expedition_cars SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        );

        return $db->fetch("SELECT * FROM expedition_cars WHERE id = ?", [$id]);
    }

    /**
     * 車両を関連データごと削除（トランザクション内で実行）
     */
    public static function delete(int $id): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $db->execute("DELETE FROM expedition_car_payers WHERE car_id = ?", [$id]);
            $db->execute("DELETE FROM expedition_car_members WHERE car_id = ?", [$id]);
            $result = $db->execute("DELETE FROM expedition_cars WHERE id = ?", [$id]) > 0;
            $db->commit();
            return $result;
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 遠征の車両費用精算を計算する
     * 返り値: ['total_cost' => int, 'per_person' => int, 'settlement' => [...]]
     * amount が正 = 支払い、負 = 返金
     */
    public static function calculateSettlement(int $expedition_id): array
    {
        $cars = self::findByExpedition($expedition_id);

        // 総費用を計算
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
        $per_person = ($total_members > 0) ? intval(ceil($total_cost / $total_members)) : 0;

        // 立替者マップ: member_id => 立替合計額
        $payer_map = [];
        $payer_names = [];
        foreach ($cars as $car) {
            foreach ($car['car_payers'] as $payer) {
                $mid = (int)$payer['member_id'];
                $payer_map[$mid] = ($payer_map[$mid] ?? 0) + (int)$payer['amount'];
                $payer_names[$mid] = $payer['name_kanji'];
            }
        }

        // 対象乗車者マップ: member_id => name（is_excluded=0）
        $member_map = [];
        foreach ($cars as $car) {
            foreach ($car['car_members'] as $member) {
                if ((int)$member['is_excluded'] === 0) {
                    $mid = (int)$member['member_id'];
                    if (!isset($member_map[$mid])) {
                        $member_map[$mid] = $member['name_kanji'];
                    }
                }
            }
        }

        $settlement = [];

        // 立替者の精算: 返金額 = 立替額 - 1人あたり負担額
        foreach ($payer_map as $member_id => $paid_amount) {
            $settlement[] = [
                'member_id' => $member_id,
                'name'      => $payer_names[$member_id],
                'amount'    => $paid_amount - $per_person,
            ];
        }

        // 未立替の乗車者: 支払い額 = 1人あたり負担額
        foreach ($member_map as $member_id => $name) {
            if (!isset($payer_map[$member_id])) {
                $settlement[] = [
                    'member_id' => $member_id,
                    'name'      => $name,
                    'amount'    => $per_person,
                ];
            }
        }

        return [
            'total_cost' => $total_cost,
            'per_person' => $per_person,
            'settlement' => $settlement,
        ];
    }

    /**
     * 往路の車を自動作成し、乗客を割り当てる
     *
     * 動作:
     *   1. 既存の往路・両路の車を削除してリセット
     *   2. can_book_car=1 の参加者ごとに車を作成（車名: 名前+車、出発時限: その人の friday_last_class）
     *      → その人をドライバーとして登録
     *   3. 残りの参加者を、friday_last_class <= car.departure_class を満たす最も早い車に割り当て
     *      → 空席なし・対応車なしの場合は最後の車に割り当て（警告あり）
     *
     * 戻り値: ['cars' => [...], 'warnings' => [...]]
     */
    public static function autoAssignOutbound(int $expedition_id, array $capacities = [], array $soloBookers = []): array
    {
        $db = Database::getInstance();

        // 既存の往路・両路の車を削除（復路専用は残す）
        $existingCars = $db->fetchAll(
            "SELECT id FROM expedition_cars WHERE expedition_id = ? AND trip_type IN ('outbound', 'both')",
            [$expedition_id]
        );
        foreach ($existingCars as $ec) {
            $db->execute("DELETE FROM expedition_car_payers  WHERE car_id = ?", [$ec['id']]);
            $db->execute("DELETE FROM expedition_car_members WHERE car_id = ?", [$ec['id']]);
            $db->execute("DELETE FROM expedition_cars        WHERE id = ?",     [$ec['id']]);
        }

        // 車に乗る参加者を全員取得（授業終了時限順）
        $participants = $db->fetchAll(
            "SELECT ep.*, m.name_kanji, m.name_kana
             FROM expedition_participants ep
             JOIN members m ON m.id = ep.member_id
             WHERE ep.expedition_id = ? AND ep.is_joining_car = 1
             ORDER BY COALESCE(ep.friday_last_class, 0) ASC, m.name_kana ASC",
            [$expedition_id]
        );

        if (empty($participants)) {
            return ['cars' => [], 'warnings' => ['車に乗る参加者がいません']];
        }

        // can_book_car=1 の人を抽出（授業終了時限順）
        $bookers = array_values(array_filter($participants, fn($p) => (int)($p['can_book_car'] ?? 0) === 1));

        if (empty($bookers)) {
            return ['cars' => [], 'warnings' => ['車を予約できる人が登録されていません（申し込み時に「車の予約をする」を選択した人が必要です）']];
        }

        $warnings  = [];
        $cars      = []; // [['id' => ..., 'name' => ..., 'departure_class' => ..., 'capacity' => 5], ...]
        $sortOrder = 1;

        // booker ごとに車を作成してドライバー登録
        foreach ($bookers as $booker) {
            $carName  = $booker['name_kanji'] . '車';
            $depClass = $booker['friday_last_class'] !== null ? (int)$booker['friday_last_class'] : null;

            $capacity = isset($capacities[$booker['member_id']]) ? (int)$capacities[$booker['member_id']] : 5;
            $capacity = max(1, $capacity);

            $carId = $db->insert(
                "INSERT INTO expedition_cars
                 (expedition_id, name, capacity, rental_fee, highway_fee, trip_type, departure_class, sort_order)
                 VALUES (?, ?, ?, 0, 0, 'outbound', ?, ?)",
                [$expedition_id, $carName, $capacity, $depClass, $sortOrder++]
            );

            // booker をドライバーとして登録
            $driverRole = ($booker['driver_type'] === 'sub_driver') ? 'sub_driver' : 'driver';
            $db->insert(
                "INSERT INTO expedition_car_members (car_id, member_id, role, is_excluded, sort_order)
                 VALUES (?, ?, ?, 0, 0)",
                [$carId, $booker['member_id'], $driverRole]
            );

            $cars[] = [
                'id'              => $carId,
                'name'            => $carName,
                'departure_class' => $depClass,
                'capacity'        => $capacity,
                'booker_id'       => (int)$booker['member_id'],
            ];
        }

        // booker 以外のドライバー能力がある参加者をサブドライバー候補プールに
        $bookerIds     = array_map(fn($b) => (int)$b['member_id'], $bookers);
        $subDriverPool = array_values(array_filter($participants, function ($p) use ($bookerIds) {
            return !in_array((int)$p['member_id'], $bookerIds)
                && in_array($p['driver_type'] ?? 'none', ['driver', 'sub_driver']);
        }));
        $assignedSubIds = [];

        // 各車にサブドライバーを自動割り当て（solo_bookers 指定の車はスキップ）
        foreach ($cars as &$car) {
            if (in_array($car['booker_id'], $soloBookers)) continue;
            foreach ($subDriverPool as $sd) {
                if (in_array((int)$sd['member_id'], $assignedSubIds)) continue;
                $db->insert(
                    "INSERT INTO expedition_car_members (car_id, member_id, role, is_excluded, sort_order)
                     VALUES (?, ?, 'sub_driver', 0, 1)",
                    [$car['id'], $sd['member_id']]
                );
                $assignedSubIds[] = (int)$sd['member_id'];
                break;
            }
        }
        unset($car);

        // booker + 割り当て済みサブドライバーを除いた参加者を乗客として配置
        $preAssigned = array_unique(array_merge($bookerIds, $assignedSubIds));
        $unassigned  = array_values(array_filter($participants, fn($p) => !in_array((int)$p['member_id'], $preAssigned)));
        $lastCar    = end($cars);

        foreach ($unassigned as $participant) {
            $personClass = (int)($participant['friday_last_class'] ?? 0);
            $targetCar   = null;

            // departure_class >= personClass の最も早い車（空席あり）を探す
            foreach ($cars as $car) {
                $carDep = $car['departure_class'] !== null ? (int)$car['departure_class'] : 99;
                if ($carDep >= $personClass) {
                    $currentCount = (int)$db->fetch(
                        "SELECT COUNT(*) as cnt FROM expedition_car_members WHERE car_id = ?",
                        [$car['id']]
                    )['cnt'];
                    if ($currentCount < $car['capacity']) {
                        $targetCar = $car;
                        break;
                    }
                }
            }

            // 適合する車がない or 全て満席 → 最後の車に強制割り当て
            if (!$targetCar) {
                $targetCar  = $lastCar;
                $classLabel = $personClass === 0 ? '授業なし' : "{$personClass}限終わり";
                $warnings[] = "【{$participant['name_kanji']}（{$classLabel}）】適切な空席の車が見つからないため「{$targetCar['name']}」に割り当てました";
            }

            $so = (int)$db->fetch(
                "SELECT COUNT(*) as cnt FROM expedition_car_members WHERE car_id = ?",
                [$targetCar['id']]
            )['cnt'];

            // driver_type に基づいてロールを決定
            $role = 'passenger';
            if (($participant['driver_type'] ?? 'none') === 'driver')     $role = 'driver';
            if (($participant['driver_type'] ?? 'none') === 'sub_driver') $role = 'sub_driver';

            $db->insert(
                "INSERT INTO expedition_car_members (car_id, member_id, role, is_excluded, sort_order)
                 VALUES (?, ?, ?, 0, ?)",
                [$targetCar['id'], $participant['member_id'], $role, $so]
            );
        }

        // 各車のサブドライバー不足チェック
        foreach ($cars as $car) {
            $members = $db->fetchAll(
                "SELECT role FROM expedition_car_members WHERE car_id = ?",
                [$car['id']]
            );
            $hasDriver    = false;
            $hasSubDriver = false;
            foreach ($members as $m) {
                if ($m['role'] === 'driver')     $hasDriver    = true;
                if ($m['role'] === 'sub_driver') $hasSubDriver = true;
            }
            $depLabel = $car['departure_class'] !== null
                ? ($car['departure_class'] === 0 ? '早出' : "{$car['departure_class']}限後出発")
                : '';
            $carLabel = $car['name'] . ($depLabel ? "（{$depLabel}）" : '');

            // booker が solo 指定の場合はサブドライバー不在でも警告しない
            $bookerIdForCar = null;
            foreach ($cars as $c) {
                if ($c['id'] === $car['id']) { $bookerIdForCar = $c['booker_id'] ?? null; break; }
            }
            $isSolo = $bookerIdForCar !== null && in_array($bookerIdForCar, $soloBookers);

            if (!$hasSubDriver && !$hasDriver) {
                $warnings[] = "【{$carLabel}】ドライバーがいません";
            } elseif (!$hasSubDriver && !$isSolo) {
                $warnings[] = "【{$carLabel}】サブドライバーがいません（ドライバー1人のみ）";
            }
        }

        return [
            'cars'     => self::findByExpedition($expedition_id),
            'warnings' => $warnings,
        ];
    }

    /**
     * 復路の車を自動作成し、乗客を住所ベースで割り当てる
     *
     * モード:
     *   by_station    - 参加者の住所から最適な下車主要駅を解決し、同じ駅グループを同じ車に乗せる
     *   by_driver_home - ドライバーの家の近くに住む参加者を同じ車に乗せる
     *
     * 動作:
     *   1. 既存の復路専用車を削除してリセット
     *   2. can_book_car=1 の参加者ごとに復路車を作成してドライバー登録
     *   3. 各参加者の住所をジオコーディング → 下車駅/距離で車に割り当て
     *
     * 戻り値: ['cars' => [...], 'warnings' => [...], 'station_summary' => [...]]
     */
    public static function autoAssignReturn(int $expedition_id, string $mode = 'by_station', array $capacities = [], array $preferredStations = []): array
    {
        $db        = Database::getInstance();
        $warnings  = [];
        $sortOrder = 1;

        // 既存の復路専用車を削除
        $existingCars = $db->fetchAll(
            "SELECT id FROM expedition_cars WHERE expedition_id = ? AND trip_type = 'return'",
            [$expedition_id]
        );
        foreach ($existingCars as $ec) {
            $db->execute("DELETE FROM expedition_car_payers  WHERE car_id = ?", [$ec['id']]);
            $db->execute("DELETE FROM expedition_car_members WHERE car_id = ?", [$ec['id']]);
            $db->execute("DELETE FROM expedition_cars        WHERE id = ?",     [$ec['id']]);
        }

        // 車に乗る参加者を全員取得（住所付き）
        $participants = $db->fetchAll(
            "SELECT ep.*, m.name_kanji, m.name_kana, m.address
             FROM expedition_participants ep
             JOIN members m ON m.id = ep.member_id
             WHERE ep.expedition_id = ? AND ep.is_joining_car = 1
             ORDER BY m.name_kana ASC",
            [$expedition_id]
        );

        if (empty($participants)) {
            return ['cars' => [], 'warnings' => ['車に乗る参加者がいません'], 'station_summary' => []];
        }

        // ドライバー（can_book_car=1）を抽出
        $drivers = array_values(array_filter($participants, fn($p) => (int)($p['can_book_car'] ?? 0) === 1));

        if (empty($drivers)) {
            return ['cars' => [], 'warnings' => ['車を予約できる人が登録されていません'], 'station_summary' => []];
        }

        // ドライバーごとに復路車を作成し、住所をジオコーディング
        $cars = []; // [['car_id', 'car_name', 'lat', 'lng', 'drop_station', 'capacity'], ...]

        $validStations = StationResolverService::MAJOR_STATIONS;

        foreach ($drivers as $driver) {
            // 希望駅が指定されていればそれを優先、なければ住所から解決
            $mid         = $driver['member_id'];
            $coords      = null;
            $dropStation = '高田馬場'; // デフォルト

            if (!empty($preferredStations[$mid]) && in_array($preferredStations[$mid], $validStations, true)) {
                $dropStation = $preferredStations[$mid];
            } elseif (!empty($driver['address'])) {
                $coords = StationResolverService::geocodeAddress($driver['address']);
                if ($coords) {
                    $dropStation = StationResolverService::resolveDropStationByLatLng($coords['lat'], $coords['lng']);
                } else {
                    $warnings[] = "【{$driver['name_kanji']}（ドライバー）】住所のジオコーディングに失敗しました（デフォルト: 高田馬場）";
                }
            } else {
                $warnings[] = "【{$driver['name_kanji']}（ドライバー）】住所が登録されていません（デフォルト: 高田馬場）";
            }

            // 車名: "ドライバー名車（下車駅方面）"
            $carName = $driver['name_kanji'] . '車（' . $dropStation . '方面）';

            $capacity = isset($capacities[$driver['member_id']]) ? (int)$capacities[$driver['member_id']] : 5;
            $capacity = max(1, $capacity);

            $carId = $db->insert(
                "INSERT INTO expedition_cars
                 (expedition_id, name, capacity, rental_fee, highway_fee, trip_type, departure_class, sort_order)
                 VALUES (?, ?, ?, 0, 0, 'return', NULL, ?)",
                [$expedition_id, $carName, $capacity, $sortOrder++]
            );

            // ドライバーを登録
            $driverRole = ($driver['driver_type'] === 'sub_driver') ? 'sub_driver' : 'driver';
            $db->insert(
                "INSERT INTO expedition_car_members (car_id, member_id, role, is_excluded, sort_order)
                 VALUES (?, ?, ?, 0, 0)",
                [$carId, $driver['member_id'], $driverRole]
            );

            $cars[] = [
                'car_id'       => $carId,
                'car_name'     => $carName,
                'lat'          => $coords['lat'] ?? null,
                'lng'          => $coords['lng'] ?? null,
                'drop_station' => $dropStation,
                'capacity'     => $capacity,
            ];
        }

        // ドライバー以外の参加者を事前にジオコーディング
        $driverIds  = array_map(fn($d) => (int)$d['member_id'], $drivers);
        $passengers = array_values(array_filter($participants, fn($p) => !in_array((int)$p['member_id'], $driverIds)));

        foreach ($passengers as &$p) {
            $p['coords']       = null;
            $p['drop_station'] = '高田馬場';

            if (!empty($p['address'])) {
                $coords = StationResolverService::geocodeAddress($p['address']);
                if ($coords) {
                    $p['coords']       = $coords;
                    $p['drop_station'] = StationResolverService::resolveDropStationByLatLng($coords['lat'], $coords['lng']);
                } else {
                    $warnings[] = "【{$p['name_kanji']}】住所のジオコーディングに失敗しました（デフォルト: 高田馬場）";
                }
            }
        }
        unset($p);

        // 各参加者を車に割り当て
        foreach ($passengers as $passenger) {
            $targetCarId = null;

            if ($mode === 'by_station') {
                // 下車駅が一致する車（空席あり）を探す
                foreach ($cars as $car) {
                    if ($car['drop_station'] === $passenger['drop_station']) {
                        $cnt = (int)$db->fetch(
                            "SELECT COUNT(*) as cnt FROM expedition_car_members WHERE car_id = ?",
                            [$car['car_id']]
                        )['cnt'];
                        if ($cnt < $car['capacity']) {
                            $targetCarId = $car['car_id'];
                            break;
                        }
                    }
                }

                // 一致する車がない場合は最も空いている車
                if (!$targetCarId) {
                    $minCount = PHP_INT_MAX;
                    foreach ($cars as $car) {
                        $cnt = (int)$db->fetch(
                            "SELECT COUNT(*) as cnt FROM expedition_car_members WHERE car_id = ?",
                            [$car['car_id']]
                        )['cnt'];
                        if ($cnt < $car['capacity'] && $cnt < $minCount) {
                            $minCount    = $cnt;
                            $targetCarId = $car['car_id'];
                        }
                    }
                    if ($targetCarId) {
                        $warnings[] = "【{$passenger['name_kanji']}（{$passenger['drop_station']}方面）】一致する車がないため別の車に割り当てました";
                    }
                }
            } else {
                // by_driver_home: ドライバーの家に最も近い車（空席あり）を探す
                if ($passenger['coords'] !== null) {
                    $minDist = PHP_FLOAT_MAX;
                    foreach ($cars as $car) {
                        if ($car['lat'] === null) continue;
                        $cnt = (int)$db->fetch(
                            "SELECT COUNT(*) as cnt FROM expedition_car_members WHERE car_id = ?",
                            [$car['car_id']]
                        )['cnt'];
                        if ($cnt < $car['capacity']) {
                            $dist = StationResolverService::calcDistance(
                                $passenger['coords']['lat'], $passenger['coords']['lng'],
                                $car['lat'], $car['lng']
                            );
                            if ($dist < $minDist) {
                                $minDist     = $dist;
                                $targetCarId = $car['car_id'];
                            }
                        }
                    }
                }

                // 座標なし or 全ドライバー座標なし → 最も空いている車
                if (!$targetCarId) {
                    $minCount = PHP_INT_MAX;
                    foreach ($cars as $car) {
                        $cnt = (int)$db->fetch(
                            "SELECT COUNT(*) as cnt FROM expedition_car_members WHERE car_id = ?",
                            [$car['car_id']]
                        )['cnt'];
                        if ($cnt < $car['capacity'] && $cnt < $minCount) {
                            $minCount    = $cnt;
                            $targetCarId = $car['car_id'];
                        }
                    }
                }
            }

            // 全車満席 → 最後の車に強制割り当て
            if (!$targetCarId) {
                $lastCar     = end($cars);
                $targetCarId = $lastCar['car_id'];
                $warnings[]  = "【{$passenger['name_kanji']}】全車満席のため「{$lastCar['car_name']}」に強制割り当てしました";
            }

            $so = (int)$db->fetch(
                "SELECT COUNT(*) as cnt FROM expedition_car_members WHERE car_id = ?",
                [$targetCarId]
            )['cnt'];

            // driver_type に基づいてロールを決定
            $passRole = 'passenger';
            if (($passenger['driver_type'] ?? 'none') === 'driver')     $passRole = 'driver';
            if (($passenger['driver_type'] ?? 'none') === 'sub_driver') $passRole = 'sub_driver';

            $db->insert(
                "INSERT INTO expedition_car_members (car_id, member_id, role, is_excluded, sort_order)
                 VALUES (?, ?, ?, 0, ?)",
                [$targetCarId, $passenger['member_id'], $passRole, $so]
            );
        }

        // サブドライバー不足チェック
        foreach ($cars as $car) {
            $members      = $db->fetchAll(
                "SELECT role FROM expedition_car_members WHERE car_id = ?",
                [$car['car_id']]
            );
            $hasDriver    = false;
            $hasSubDriver = false;
            foreach ($members as $m) {
                if ($m['role'] === 'driver')     $hasDriver    = true;
                if ($m['role'] === 'sub_driver') $hasSubDriver = true;
            }
            if (!$hasDriver) {
                $warnings[] = "【{$car['car_name']}】ドライバーがいません";
            } elseif (!$hasSubDriver) {
                $warnings[] = "【{$car['car_name']}】サブドライバーがいません（ドライバー1人のみ）";
            }
        }

        // 下車駅サマリー（car_id => drop_station）
        $stationSummary = [];
        foreach ($cars as $car) {
            $stationSummary[$car['car_id']] = $car['drop_station'];
        }

        return [
            'cars'            => self::findByExpedition($expedition_id),
            'warnings'        => $warnings,
            'station_summary' => $stationSummary,
        ];
    }
}
