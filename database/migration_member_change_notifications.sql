-- 会員による名簿情報変更の通知ログ
CREATE TABLE IF NOT EXISTS member_change_notifications (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    member_id     INT UNSIGNED    NOT NULL,
    member_name   VARCHAR(100)    NOT NULL,
    student_id    VARCHAR(50)     NOT NULL,
    changes_json  TEXT            NOT NULL,  -- JSON: {"field": {"before": ..., "after": ...}}
    read_at       DATETIME        NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_read_at    (read_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
