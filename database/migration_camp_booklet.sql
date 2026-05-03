-- 合宿しおりテーブル
CREATE TABLE IF NOT EXISTS `camp_booklets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `camp_id` INT NOT NULL,
  -- 集合情報
  `meeting_time` VARCHAR(10) NOT NULL DEFAULT '8:40',
  `meeting_place` VARCHAR(200) NOT NULL DEFAULT '新宿センタービル（地上）',
  `meeting_note` TEXT,
  `return_place` VARCHAR(200) DEFAULT NULL,
  -- 持ち物（JSON配列: [{text, highlight}]）
  `items_to_bring` JSON,
  -- タイムスケジュール（JSON: [{day, label, rows: [{time, activity, note, highlight}]}]）
  `schedules` JSON,
  -- 団体戦チーム分け（JSON: [{team_name, is_leader_row, members:[{name}]}]）
  `team_battle_teams` JSON,
  -- 団体戦ルール（テキスト or HTML）
  `team_battle_rules` TEXT,
  -- 団体戦タイムスケジュール（JSON: [{time, courts}]）
  `team_battle_schedule` JSON,
  -- 紅白戦チーム分け（JSON: {red:[{name}], white:[{name}]}）
  `kohaku_teams` JSON,
  -- 紅白戦ルール
  `kohaku_rules` TEXT,
  -- 紅白戦対戦表（JSON: [{round, courts:[{court, match1, match2}]}]）
  `kohaku_matches` JSON,
  -- 夜レク班分け（JSON: [{group_name, members:[{name}]}]）
  `night_rec_groups` JSON,
  -- 部屋割り（JSON: [{category, rooms:[{room_no, capacity}]}]）
  `room_assignments` JSON,
  -- 宿内平面図（画像URL）
  `floor_plan_image` VARCHAR(500) DEFAULT NULL,
  -- 配膳当番（JSON: [{meal, days:[{day, group}]}]）
  `meal_duty` JSON,
  -- 公開設定
  `is_public` TINYINT(1) NOT NULL DEFAULT 0,
  `public_token` VARCHAR(64) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_camp_id` (`camp_id`),
  UNIQUE KEY `uk_public_token` (`public_token`),
  FOREIGN KEY (`camp_id`) REFERENCES `camps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
