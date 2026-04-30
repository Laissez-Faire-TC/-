-- 合宿申し込みと企画申し込みに備考欄を追加
ALTER TABLE camp_applications
    ADD COLUMN note TEXT NULL DEFAULT NULL COMMENT '申し込み時の備考';

ALTER TABLE event_applications
    ADD COLUMN note TEXT NULL DEFAULT NULL COMMENT '申し込み時の備考';
