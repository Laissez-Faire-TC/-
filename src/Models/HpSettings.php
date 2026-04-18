<?php
/**
 * HP 設定モデル（キーバリューストア）
 */
class HpSettings
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function get(string $key): ?string
    {
        $row = $this->db->fetch('SELECT setting_value FROM hp_settings WHERE setting_key = ?', [$key]);
        return $row ? $row['setting_value'] : null;
    }

    public function getJson(string $key): mixed
    {
        $val = $this->get($key);
        return $val !== null ? json_decode($val, true) : null;
    }

    public function set(string $key, string $value): void
    {
        $this->db->execute(
            'INSERT INTO hp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
    }

    public function setJson(string $key, mixed $value): void
    {
        $this->set($key, json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 全設定を連想配列で返す
     */
    public function all(): array
    {
        $rows = $this->db->fetchAll('SELECT setting_key, setting_value FROM hp_settings');
        $map = [];
        foreach ($rows as $row) {
            $map[$row['setting_key']] = $row['setting_value'];
        }
        return $map;
    }
}
