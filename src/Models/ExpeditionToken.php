<?php
if (!class_exists('ExpeditionToken')) {
    /**
     * 遠征申し込みURLトークンモデル
     */
    class ExpeditionToken
    {
        private Database $db;

        public function __construct()
        {
            $this->db = Database::getInstance();
        }

        /**
         * 遠征IDでトークンを取得
         */
        public function findByExpedition(int $expedition_id): ?array
        {
            return $this->db->fetch(
                "SELECT * FROM expedition_tokens WHERE expedition_id = ?",
                [$expedition_id]
            );
        }

        /**
         * トークン文字列で取得
         * ※expires_at の有効期限チェックは呼び出し元で行う
         */
        public function findByToken(string $token): ?array
        {
            return $this->db->fetch(
                "SELECT * FROM expedition_tokens WHERE token = ?",
                [$token]
            );
        }

        /**
         * トークンを生成する
         * 既存トークンを削除してから新しいトークンをINSERTし、作成した行を返す
         */
        public function generate(int $expedition_id): ?array
        {
            // 既存トークンを削除
            $this->db->execute(
                "DELETE FROM expedition_tokens WHERE expedition_id = ?",
                [$expedition_id]
            );

            // 64文字の16進数トークンを生成
            $token = bin2hex(random_bytes(32));

            // 有効期限は現在時刻から30日後
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

            $this->db->insert(
                "INSERT INTO expedition_tokens (expedition_id, token, expires_at) VALUES (?, ?, ?)",
                [$expedition_id, $token, $expiresAt]
            );

            // 作成した行を返す
            return $this->findByExpedition($expedition_id);
        }

        /**
         * 遠征IDに紐づくトークンを削除
         */
        public function delete(int $expedition_id): bool
        {
            return $this->db->execute(
                "DELETE FROM expedition_tokens WHERE expedition_id = ?",
                [$expedition_id]
            ) > 0;
        }
    }
}
