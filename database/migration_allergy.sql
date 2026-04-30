-- アレルギー情報カラムを participants テーブルに追加するマイグレーション
ALTER TABLE participants
ADD COLUMN allergy TEXT NULL DEFAULT NULL COMMENT 'アレルギー情報（自由記述）' AFTER gender;

SELECT 'アレルギーカラムの追加が完了しました（participants テーブル）' AS message;
