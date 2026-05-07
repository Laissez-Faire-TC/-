-- 物販: 未登録購入者対応カラム追加
-- 入会期限前にTシャツ販売を開始するため、DB未登録の入会予定者が購入できるようにする
-- 後で会員DB登録された際、学籍番号で member_id に紐付け（手動 or 入会時自動）

ALTER TABLE merchandise_orders
    ADD COLUMN pending_student_id VARCHAR(20)  DEFAULT NULL AFTER member_id,
    ADD COLUMN pending_line_name  VARCHAR(100) DEFAULT NULL AFTER buyer_kana,
    ADD COLUMN pending_phone      VARCHAR(20)  DEFAULT NULL AFTER pending_line_name;

-- 学籍番号での検索を高速化（一括マッチング用）
CREATE INDEX idx_merchandise_orders_pending_student_id ON merchandise_orders(pending_student_id);
