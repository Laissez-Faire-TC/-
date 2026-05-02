<?php
/**
 * 遠征チームメンバーモデル
 */
class ExpeditionTeamMember
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * チームにメンバーを追加して作成した行を返す
     */
    public function add(int $team_id, int $member_id): ?array
    {
        $id = $this->db->insert(
            "INSERT INTO expedition_team_members (team_id, member_id) VALUES (?, ?)",
            [$team_id, $member_id]
        );

        return $this->db->fetch(
            "SELECT * FROM expedition_team_members WHERE id = ?",
            [$id]
        );
    }

    /**
     * チームメンバーを削除
     */
    public function remove(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM expedition_team_members WHERE id = ?",
            [$id]
        ) > 0;
    }

    /**
     * メンバーの並び順とチームを一括更新（チーム移動にも対応）
     * $items: [{team_id: X, members: [{id: Y, sort_order: Z}, ...]}]
     */
    public function updateOrder(array $items): void
    {
        foreach ($items as $item) {
            $team_id = $item['team_id'];
            $members = $item['members'] ?? [];

            foreach ($members as $member) {
                $this->db->execute(
                    "UPDATE expedition_team_members SET team_id = ?, sort_order = ? WHERE id = ?",
                    [$team_id, $member['sort_order'], $member['id']]
                );
            }
        }
    }

    /**
     * メンバーを別チームへ移動
     */
    public function moveMember(int $member_id, int $from_team_id, int $to_team_id): bool
    {
        return $this->db->execute(
            "UPDATE expedition_team_members SET team_id = ? WHERE member_id = ? AND team_id = ?",
            [$to_team_id, $member_id, $from_team_id]
        ) > 0;
    }
}
