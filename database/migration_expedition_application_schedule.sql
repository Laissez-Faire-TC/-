-- 申込み開始日時カラムを追加（申込期限 deadline は既存）
ALTER TABLE expeditions
    ADD COLUMN application_start DATETIME DEFAULT NULL AFTER deadline;
