-- レンタカー清算機能追加
-- expeditions に費用申請期限カラムを追加
ALTER TABLE expeditions
  ADD COLUMN expense_deadline DATE DEFAULT NULL COMMENT '費用申請期限' AFTER capacity_female;

-- レンタカー費用申請テーブル
CREATE TABLE IF NOT EXISTS expedition_car_expenses (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  expedition_id     INT NOT NULL,
  member_id         INT NOT NULL,
  rental_fee        INT NOT NULL DEFAULT 0 COMMENT 'レンタカー代',
  gas_fee           INT NOT NULL DEFAULT 0 COMMENT 'ガソリン代',
  highway_fee       INT NOT NULL DEFAULT 0 COMMENT '高速料金',
  other_fee         INT NOT NULL DEFAULT 0 COMMENT 'その他',
  other_description VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'その他の内訳',
  note              TEXT DEFAULT NULL COMMENT '備考',
  submitted_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_member_expedition (member_id, expedition_id),
  INDEX idx_expedition_id (expedition_id)
);
