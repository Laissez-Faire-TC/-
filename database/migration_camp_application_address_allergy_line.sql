-- 合宿申込時の情報修正フィールドを追加（migration_camp_application_edits.sql が未実行の場合はこちらを実行）
ALTER TABLE camp_applications
    ADD COLUMN edited_name_kanji  VARCHAR(100) NULL DEFAULT NULL,
    ADD COLUMN edited_grade       VARCHAR(10)  NULL DEFAULT NULL,
    ADD COLUMN edited_gender      VARCHAR(10)  NULL DEFAULT NULL,
    ADD COLUMN edited_faculty     VARCHAR(100) NULL DEFAULT NULL,
    ADD COLUMN edited_department  VARCHAR(100) NULL DEFAULT NULL,
    ADD COLUMN edited_address     TEXT         NULL DEFAULT NULL,
    ADD COLUMN edited_allergy     TEXT         NULL DEFAULT NULL,
    ADD COLUMN edited_line_name   VARCHAR(100) NULL DEFAULT NULL,
    ADD COLUMN info_edited        TINYINT(1)   NOT NULL DEFAULT 0,
    ADD COLUMN member_updated     TINYINT(1)   NOT NULL DEFAULT 0;
