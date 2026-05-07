-- 補助金カラムを追加（全体額、参加費から人数割りで減額）
ALTER TABLE expeditions
    ADD COLUMN subsidy INT NOT NULL DEFAULT 0 AFTER expense_deadline;
