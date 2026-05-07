-- 遠征集金 振り込み確認フォーム対応
-- expedition_collections に振込期限カラムを追加
ALTER TABLE expedition_collections
  ADD COLUMN deadline DATE DEFAULT NULL COMMENT '振込期限' AFTER title;

-- expedition_collection_items に会員提出・管理者確認カラムを追加
ALTER TABLE expedition_collection_items
  ADD COLUMN submitted      TINYINT(1)  NOT NULL DEFAULT 0    COMMENT '会員提出済みフラグ' AFTER memo,
  ADD COLUMN submitted_at   DATETIME    DEFAULT NULL           COMMENT '提出日時'           AFTER submitted,
  ADD COLUMN late_reason    TEXT        DEFAULT NULL           COMMENT '遅延理由'           AFTER submitted_at;
