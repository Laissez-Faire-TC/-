-- 入会期限カラムを academic_years テーブルに追加
-- enrollment_deadline: 新規入会フォームの提出期限
-- renew_deadline: 継続入会フォームの提出期限
ALTER TABLE academic_years
    ADD COLUMN enrollment_deadline DATE NULL COMMENT '新規入会フォーム提出期限',
    ADD COLUMN renew_deadline      DATE NULL COMMENT '継続入会フォーム提出期限';
