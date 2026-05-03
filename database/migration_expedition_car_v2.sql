-- 遠征車割機能拡張 v2
-- expedition_participants に車関連カラムを追加

ALTER TABLE expedition_participants
  ADD COLUMN is_joining_car TINYINT DEFAULT 1 COMMENT '車に乗るか (1=乗る, 0=乗らない)',
  ADD COLUMN driver_type ENUM('driver','sub_driver','none') DEFAULT 'none' COMMENT 'ドライバー種別',
  ADD COLUMN timescar_number VARCHAR(50) DEFAULT '' COMMENT 'タイムズカーシェア利用者番号',
  ADD COLUMN can_book_car TINYINT DEFAULT 0 COMMENT '車の予約をするか (1=する, 0=しない)',
  ADD COLUMN friday_last_class TINYINT NULL COMMENT '金曜授業終了時限 (0=授業なし, 1〜6=何限まで, NULL=車不参加)';

-- expedition_cars に往路/復路区分・出発時限を追加

ALTER TABLE expedition_cars
  ADD COLUMN trip_type ENUM('outbound','return','both') DEFAULT 'both' COMMENT '運行区分 (outbound=往路, return=復路, both=両路)',
  ADD COLUMN departure_class TINYINT NULL COMMENT '往路の出発時限 (0=早出, 1〜6=何限後出発, NULL=制限なし)';
