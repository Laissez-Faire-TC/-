-- 遠征管理：申込期限・男女別定員カラム追加
ALTER TABLE expeditions
  ADD COLUMN deadline DATE DEFAULT NULL AFTER end_date,
  ADD COLUMN capacity_male INT DEFAULT NULL AFTER deadline,
  ADD COLUMN capacity_female INT DEFAULT NULL AFTER capacity_male;
