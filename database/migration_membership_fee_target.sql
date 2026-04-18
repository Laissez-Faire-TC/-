-- 入会金設定に対象区分カラムを追加
-- new:  新規入会者のみ
-- renew: 継続入会者のみ
-- both: 両方（デフォルト）
ALTER TABLE membership_fees
    ADD COLUMN target_type ENUM('new','renew','both') NOT NULL DEFAULT 'both'
        COMMENT '対象区分: new=新規入会, renew=継続入会, both=両方'
    AFTER deadline;
