-- 入会金設定テーブル
CREATE TABLE IF NOT EXISTS membership_fees (
    id           INT NOT NULL AUTO_INCREMENT,
    academic_year INT NOT NULL,              -- 対象年度
    name         VARCHAR(255) NOT NULL,      -- 例: 2026年度入会金
    deadline     DATE NOT NULL,              -- 振込期限
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 学年別金額テーブル
CREATE TABLE IF NOT EXISTS membership_fee_grades (
    id                  INT NOT NULL AUTO_INCREMENT,
    membership_fee_id   INT NOT NULL,
    grade               VARCHAR(10) NOT NULL,   -- 1, 2, 3, 4, M1, M2, OB, OG
    amount              INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY unique_fee_grade (membership_fee_id, grade),
    FOREIGN KEY (membership_fee_id) REFERENCES membership_fees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 会員ごとの支払い状況テーブル
CREATE TABLE IF NOT EXISTS membership_fee_items (
    id                  INT NOT NULL AUTO_INCREMENT,
    membership_fee_id   INT NOT NULL,
    member_id           INT NOT NULL,
    custom_amount       INT NULL,              -- NULLなら学年別金額を使用
    submitted           TINYINT(1) NOT NULL DEFAULT 0,
    submitted_at        TIMESTAMP NULL,
    late_reason         TEXT NULL,
    admin_confirmed     TINYINT(1) NOT NULL DEFAULT 0,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_member (membership_fee_id, member_id),
    FOREIGN KEY (membership_fee_id) REFERENCES membership_fees(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
