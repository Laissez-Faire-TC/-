-- 新規入会・継続入会の受付フラグを独立させる
-- enroll_open: 新規入会フォームの受付フラグ
-- renew_open:  継続入会フォームの受付フラグ
-- (既存の enrollment_open は削除せず残す。今後は使用しない)
ALTER TABLE academic_years
    ADD COLUMN enroll_open TINYINT(1) NOT NULL DEFAULT 0 COMMENT '新規入会フォーム受付中フラグ',
    ADD COLUMN renew_open  TINYINT(1) NOT NULL DEFAULT 0 COMMENT '継続入会フォーム受付中フラグ';
