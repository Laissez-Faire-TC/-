-- eventsテーブルに申込期限カラムを追加
ALTER TABLE events ADD COLUMN deadline DATE DEFAULT NULL COMMENT '申込期限（NULL=期限なし）' AFTER capacity;
