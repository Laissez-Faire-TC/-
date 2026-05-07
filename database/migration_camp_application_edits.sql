-- 合宿申し込み時の情報修正を記録するカラムを追加するマイグレーション
ALTER TABLE camp_applications
  ADD COLUMN edited_name_kanji VARCHAR(100) NULL DEFAULT NULL COMMENT '申し込み時に修正した名前',
  ADD COLUMN edited_grade VARCHAR(10) NULL DEFAULT NULL COMMENT '申し込み時に修正した学年',
  ADD COLUMN edited_gender VARCHAR(10) NULL DEFAULT NULL COMMENT '申し込み時に修正した性別',
  ADD COLUMN edited_faculty VARCHAR(100) NULL DEFAULT NULL COMMENT '申し込み時に修正した学部',
  ADD COLUMN edited_department VARCHAR(100) NULL DEFAULT NULL COMMENT '申し込み時に修正した学科',
  ADD COLUMN info_edited TINYINT(1) NOT NULL DEFAULT 0 COMMENT '申し込み時に情報修正があったか',
  ADD COLUMN member_updated TINYINT(1) NOT NULL DEFAULT 0 COMMENT '幹事が会員名簿に反映したか';

SELECT '合宿申し込み情報修正カラムの追加が完了しました' AS message;
