<?php
/**
 * 遠征チームモデル
 */
class ExpeditionTeam
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 遠征IDに紐づくチーム一覧を取得（メンバー情報を含む）
     */
    public function findByExpedition(int $expedition_id): array
    {
        $teams = $this->db->fetchAll(
            "SELECT * FROM expedition_teams WHERE expedition_id = ? ORDER BY sort_order",
            [$expedition_id]
        );

        // 各チームのメンバーを取得してセット
        foreach ($teams as &$team) {
            $team['members'] = $this->db->fetchAll(
                "SELECT etm.*, m.name, m.furigana, m.gender
                 FROM expedition_team_members etm
                 JOIN members m ON m.id = etm.member_id
                 WHERE etm.team_id = ?
                 ORDER BY etm.sort_order",
                [$team['id']]
            );
        }

        return $teams;
    }

    /**
     * チームを新規作成して作成した行を返す
     */
    public function create(int $expedition_id, string $name): ?array
    {
        $id = $this->db->insert(
            "INSERT INTO expedition_teams (expedition_id, name, sort_order) VALUES (?, ?, 0)",
            [$expedition_id, $name]
        );

        return $this->db->fetch(
            "SELECT * FROM expedition_teams WHERE id = ?",
            [$id]
        );
    }

    /**
     * チーム情報を更新して更新後の行を返す
     */
    public function update(int $id, array $data): ?array
    {
        $fields = [];
        $values = [];

        // 許可フィールド
        $allowedFields = ['name', 'sort_order'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return $this->db->fetch("SELECT * FROM expedition_teams WHERE id = ?", [$id]);
        }

        $values[] = $id;
        $this->db->execute(
            "UPDATE expedition_teams SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        );

        return $this->db->fetch("SELECT * FROM expedition_teams WHERE id = ?", [$id]);
    }

    /**
     * チームを削除（メンバー情報も含めてトランザクション内で削除）
     */
    public function delete(int $id): bool
    {
        $this->db->beginTransaction();

        try {
            // 先にチームメンバーを削除
            $this->db->execute(
                "DELETE FROM expedition_team_members WHERE team_id = ?",
                [$id]
            );

            // チーム本体を削除
            $result = $this->db->execute(
                "DELETE FROM expedition_teams WHERE id = ?",
                [$id]
            ) > 0;

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
