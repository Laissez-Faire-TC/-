CREATE TABLE IF NOT EXISTS camp_collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    default_amount INT NOT NULL DEFAULT 0,
    deadline DATE NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_camp (camp_id),
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS camp_collection_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_id INT NOT NULL,
    member_id INT NOT NULL,
    custom_amount INT NULL COMMENT 'NULLならdefault_amountを使用',
    submitted TINYINT(1) NOT NULL DEFAULT 0,
    late_reason TEXT NULL,
    admin_confirmed TINYINT(1) NOT NULL DEFAULT 0,
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_member (collection_id, member_id),
    FOREIGN KEY (collection_id) REFERENCES camp_collections(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
