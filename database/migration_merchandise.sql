-- 物販システム用マイグレーション
-- サークル制作のTシャツ・パーカー等を会員/OB・OG向けに販売する

-- 商品マスタ
CREATE TABLE merchandise (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price INT NOT NULL DEFAULT 0,
  sale_start DATETIME DEFAULT NULL,
  sale_end DATETIME DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 商品の色オプション（色ごとに画像を持つ）
CREATE TABLE merchandise_colors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  merchandise_id INT NOT NULL,
  color_name VARCHAR(64) NOT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (merchandise_id) REFERENCES merchandise(id) ON DELETE CASCADE
);

-- 商品のサイズオプション
CREATE TABLE merchandise_sizes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  merchandise_id INT NOT NULL,
  size_name VARCHAR(32) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (merchandise_id) REFERENCES merchandise(id) ON DELETE CASCADE
);

-- 注文ヘッダ
CREATE TABLE merchandise_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  member_id INT DEFAULT NULL,
  buyer_name VARCHAR(100) NOT NULL,
  buyer_kana VARCHAR(100) DEFAULT NULL,
  buyer_contact VARCHAR(255) DEFAULT NULL,
  total_amount INT NOT NULL DEFAULT 0,
  payment_status ENUM('unpaid','paid','cancelled') NOT NULL DEFAULT 'unpaid',
  paid_at DATETIME DEFAULT NULL,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 注文明細（商品×色×サイズ×数量）
CREATE TABLE merchandise_order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  merchandise_id INT NOT NULL,
  color_id INT DEFAULT NULL,
  size_id INT DEFAULT NULL,
  color_name VARCHAR(64) DEFAULT NULL,
  size_name VARCHAR(32) DEFAULT NULL,
  merchandise_name VARCHAR(255) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price INT NOT NULL DEFAULT 0,
  subtotal INT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES merchandise_orders(id) ON DELETE CASCADE
);

-- 公開ショップ用トークン（OB/OG向けLINE共有URL）
CREATE TABLE merchandise_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(64) NOT NULL UNIQUE,
  label VARCHAR(100) DEFAULT NULL,
  expires_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
