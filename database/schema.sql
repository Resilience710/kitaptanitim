-- =====================================================================
--  Kitap Tanıtım Sitesi - Veritabanı Şeması ve Başlangıç İçeriği
-- =====================================================================
--  Bu dosyayı iki şekilde kullanabilirsiniz:
--   1) Önerilen: install.php sihirbazını çalıştırın (bu dosyayı sizin için işler).
--   2) Elle: cPanel > phpMyAdmin > Içe Aktar (Import) ile bu dosyayı yükleyin.
--      Elle yüklerseniz varsayılan giriş bilgisi: kullanıcı "admin", şifre "admin123".
--      Giriş yaptıktan sonra şifreyi MUTLAKA değiştirin.
-- =====================================================================

CREATE TABLE IF NOT EXISTS settings (
    setting_key   VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(160) NOT NULL UNIQUE,
    title       VARCHAR(255) NOT NULL,
    subtitle    VARCHAR(255) NULL,
    description MEDIUMTEXT NULL,
    price       DECIMAL(10,2) NULL,
    currency    VARCHAR(8) NOT NULL DEFAULT 'TL',
    etsy_url    VARCHAR(512) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order  INT NOT NULL DEFAULT 0,
    status      ENUM('draft','published') NOT NULL DEFAULT 'published',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status_sort (status, sort_order),
    KEY idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    file_path  VARCHAR(512) NOT NULL,
    alt_text   VARCHAR(255) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_product (product_id, sort_order),
    CONSTRAINT fk_pi_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name  VARCHAR(120) NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------- Başlangıç Ayarları (İçerik) ------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
    ('site_title',          'Kitap Adı'),
    ('logo_text',           'Kitap Adı'),
    ('logo_image',          ''),
    ('favicon',             'assets/img/favicon.svg'),
    ('hero_eyebrow',        'YENİ KİTAP'),
    ('hero_heading',        'Yeni Kitabınızla Tanışın'),
    ('hero_subheading',     'Buraya kitabınızı anlatan kısa, etkileyici bir tanıtım metni gelecek. Bu metni yönetim panelinden dilediğiniz gibi değiştirebilirsiniz.'),
    ('btn_buy_label',       'Etsy''de Satın Al'),
    ('btn_detail_label',    'İncele'),
    ('collection_title',    'Diğer Kitaplar'),
    ('collection_subtitle', 'Koleksiyondaki diğer eserlere göz atın.'),
    ('about_title',         'Kitap Hakkında'),
    ('about_text',          'Kitabınız hakkında daha uzun bir tanıtım metnini buraya yazabilirsiniz. Hikâyesi, sizi yazmaya iten ilham ve okurların neler bulacağı gibi detaylar bu bölümde yer alabilir.'),
    ('footer_text',         '© 2026 Tüm hakları saklıdır.'),
    ('social_instagram',    ''),
    ('social_etsy',         ''),
    ('theme_bg_color',          '#fbfaf8'),
    ('theme_text_color',        '#1b1b1a'),
    ('theme_accent_color',      '#1b1b1a'),
    ('theme_muted_color',       '#7a756e'),
    ('theme_border_color',      '#e7e2da'),
    ('theme_card_bg',           '#ffffff'),
    ('theme_btn_text_color',    '#ffffff'),
    ('theme_font_heading_name', 'Playfair Display'),
    ('theme_font_body_name',    'Inter'),
    ('theme_font_size_base',    '17px'),
    ('theme_font_size_h1',      '3.4rem'),
    ('theme_header_height',     '64px'),
    ('theme_radius',            '10px'),
    ('theme_btn_radius',        '100px'),
    ('theme_max_width',         '1180px'),
    ('theme_section_gap',       '110px')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- ----------------------- Örnek Kitap ------------------------
INSERT INTO products (id, slug, title, subtitle, description, price, currency, etsy_url, is_featured, sort_order, status)
VALUES (
    1,
    'ornek-kitap',
    'Örnek Kitap',
    'Kısa bir alt başlık',
    'Bu örnek bir kitap açıklamasıdır. Yönetim panelinden (/admin) bu kitabı düzenleyebilir, kendi kitabınızın bilgilerini, görsellerini ve Etsy bağlantısını ekleyebilirsiniz. Açıklama bölümünde kitabınızın konusunu, kimler için yazıldığını ve okuru nelerin beklediğini anlatabilirsiniz.',
    0.00,
    'TL',
    '',
    1,
    0,
    'published'
) ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO product_images (product_id, file_path, alt_text, is_primary, sort_order) VALUES
    (1, 'assets/img/placeholder-cover.svg', 'Örnek kitap kapağı', 1, 0),
    (1, 'assets/img/placeholder-1.svg',     'Örnek görsel 1',     0, 1),
    (1, 'assets/img/placeholder-2.svg',     'Örnek görsel 2',     0, 2)
ON DUPLICATE KEY UPDATE file_path = file_path;

-- ----------------------- Varsayılan Yönetici ------------------------
-- Yalnızca elle (phpMyAdmin) kurulum için. install.php bu satırı kendi
-- bilgilerinizle günceller. Şifre: admin123  (giriş sonrası değiştirin!)
INSERT INTO admin_users (id, username, password_hash, display_name) VALUES
    (1, 'admin', '$2y$12$9d4jgWmonIZcBaBGyRMwuuiXa7yCRlfwAeXYVuQ3suloUu3G69VPi', 'Yönetici')
ON DUPLICATE KEY UPDATE username = username;
