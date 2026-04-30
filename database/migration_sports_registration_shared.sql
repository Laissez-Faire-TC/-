-- コート予約番号の他サークル共用フラグを追加
ALTER TABLE members
    ADD COLUMN sports_registration_shared TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '他サークルでもコート予約番号を使用するか（1=使用する）'
        AFTER sports_registration_no;
