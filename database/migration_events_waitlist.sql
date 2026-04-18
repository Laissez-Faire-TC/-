-- eventsテーブルにキャンセル待ちフラグを追加
ALTER TABLE events ADD COLUMN allow_waitlist TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=キャンセル待ち有効' AFTER deadline;

-- event_applicationsのstatusにwaitlistedを追加、繰り上げフラグを追加
ALTER TABLE event_applications
    MODIFY COLUMN status ENUM('submitted', 'waitlisted', 'cancelled') NOT NULL DEFAULT 'submitted';

ALTER TABLE event_applications
    ADD COLUMN promoted TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=キャンセル待ちから繰り上げ' AFTER status;
