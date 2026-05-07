<?php
/**
 * 住所から最適な下車主要駅を解決するサービス
 *
 * 使用API:
 *   - 国土地理院 住所検索API（ジオコーディング）
 *   - HeartRails Express API（近隣駅・路線情報取得）
 */
class StationResolverService
{
    /** 対象主要駅 */
    const MAJOR_STATIONS = ['高田馬場', '新宿', '渋谷', '東京'];

    /**
     * 各主要駅に接続する路線キーワード（部分一致）
     * 優先度順に並べる（先にマッチしたほうがスコア高）
     */
    private const STATION_LINE_KEYWORDS = [
        '高田馬場' => ['東西線', '副都心線'],
        '新宿'    => ['中央線', '小田急', '京王', '新宿線', '埼京線'],
        '渋谷'    => ['東横線', '田園都市線', '井の頭線', '半蔵門線'],
        '東京'    => ['東海道', '横須賀線', '総武', '京浜東北'],
    ];

    /** 主要駅の緯度経度（API失敗時のフォールバック用） */
    private const MAJOR_STATION_COORDS = [
        '高田馬場' => ['lat' => 35.7126, 'lng' => 139.7037],
        '新宿'    => ['lat' => 35.6896, 'lng' => 139.7006],
        '渋谷'    => ['lat' => 35.6580, 'lng' => 139.7016],
        '東京'    => ['lat' => 35.6812, 'lng' => 139.7671],
    ];

    /** HTTP タイムアウト秒数 */
    private const HTTP_TIMEOUT = 5;

    /**
     * 住所を緯度経度に変換（国土地理院 住所検索API）
     *
     * @return array{lat: float, lng: float}|null 失敗時は null
     */
    public static function geocodeAddress(string $address): ?array
    {
        if (empty(trim($address))) {
            return null;
        }

        $url  = 'https://msearch.gsi.go.jp/address-search/AddressSearch?q=' . urlencode($address);
        $json = self::httpGet($url);
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);
        if (
            !is_array($data) ||
            empty($data[0]['geometry']['coordinates'])
        ) {
            return null;
        }

        return [
            'lng' => (float)$data[0]['geometry']['coordinates'][0],
            'lat' => (float)$data[0]['geometry']['coordinates'][1],
        ];
    }

    /**
     * 緯度経度から近隣駅一覧を取得（HeartRails Express API）
     *
     * @return array 駅情報の配列。各要素: ['name' => '駅名', 'line' => '路線名', 'x' => lng, 'y' => lat, ...]
     */
    public static function getNearbyStations(float $lat, float $lng): array
    {
        $url  = "http://express.heartrails.com/api/json?method=getStations&x={$lng}&y={$lat}";
        $json = self::httpGet($url);
        if ($json === null) {
            return [];
        }

        $data = json_decode($json, true);
        return $data['response']['station'] ?? [];
    }

    /**
     * 住所から最適な下車主要駅を返す
     *
     * @param string $address 住所文字列
     * @return string 主要駅名（高田馬場 / 新宿 / 渋谷 / 東京）
     */
    public static function resolveDropStation(string $address): string
    {
        $coords = self::geocodeAddress($address);
        if ($coords === null) {
            return '高田馬場'; // デフォルト
        }
        return self::resolveDropStationByLatLng($coords['lat'], $coords['lng']);
    }

    /**
     * 緯度経度から推奨下車主要駅と最寄り駅を両方返す
     *
     * @return array{drop_station: string, nearest_station: string|null}
     */
    public static function resolveAllByLatLng(float $lat, float $lng): array
    {
        $stations = self::getNearbyStations($lat, $lng);

        // 最寄り駅: HeartRails の1件目（距離最小）
        $nearestStation = !empty($stations) ? ($stations[0]['name'] ?? null) : null;

        // 推奨下車駅: スコアリング
        $dropStation = self::scoreDropStation($stations, $lat, $lng);

        return [
            'drop_station'    => $dropStation,
            'nearest_station' => $nearestStation,
        ];
    }

    /**
     * 緯度経度から最適な下車主要駅を返す
     *
     * 判定ロジック:
     *   1. HeartRails で近隣駅（最大5件）を取得
     *   2. 各駅の路線名をキーワードマッチして主要駅スコアを加算
     *      （近い駅ほど重みが大きい: 1位=5点, 2位=4点, ...）
     *   3. スコア最大の主要駅を返す
     *   4. 全スコア0（路線不明）の場合は直線距離で最近傍主要駅を返す
     */
    public static function resolveDropStationByLatLng(float $lat, float $lng): string
    {
        $stations = self::getNearbyStations($lat, $lng);
        return self::scoreDropStation($stations, $lat, $lng);
    }

    /**
     * 近隣駅リストから推奨下車主要駅をスコアリングして返す（内部共通処理）
     */
    private static function scoreDropStation(array $stations, float $lat, float $lng): string
    {
        if (empty($stations)) {
            return self::nearestMajorByDistance($lat, $lng);
        }

        // 主要駅スコアを集計
        $scores = array_fill_keys(self::MAJOR_STATIONS, 0);

        $limit = min(count($stations), 5);
        for ($i = 0; $i < $limit; $i++) {
            $line   = $stations[$i]['line'] ?? '';
            $weight = 5 - $i; // 1番近い駅=5点, 2番目=4点...

            foreach (self::STATION_LINE_KEYWORDS as $major => $keywords) {
                foreach ($keywords as $keyword) {
                    if (mb_strpos($line, $keyword) !== false) {
                        $scores[$major] += $weight;
                        break; // 1路線で複数キーワードが一致しても1回だけ加算
                    }
                }
            }
        }

        arsort($scores);
        $topStation = (string)array_key_first($scores);

        // 全スコアが 0 なら距離フォールバック
        if ($scores[$topStation] === 0) {
            return self::nearestMajorByDistance($lat, $lng);
        }

        return $topStation;
    }

    /**
     * 主要駅との直線距離で最近傍を返す（フォールバック）
     */
    public static function nearestMajorByDistance(float $lat, float $lng): string
    {
        $minDist = PHP_FLOAT_MAX;
        $nearest = '高田馬場';

        foreach (self::MAJOR_STATION_COORDS as $station => $coords) {
            $dist = self::calcDistance($lat, $lng, $coords['lat'], $coords['lng']);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $station;
            }
        }

        return $nearest;
    }

    /**
     * Haversine 公式で2点間の距離 (km) を計算
     */
    public static function calcDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371.0; // 地球半径 (km)
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * HTTP GET リクエスト（タイムアウト付き）
     *
     * @return string|null レスポンスボディ。失敗時は null
     */
    private static function httpGet(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => self::HTTP_TIMEOUT,
                'method'  => 'GET',
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);
        return ($result === false) ? null : $result;
    }
}
