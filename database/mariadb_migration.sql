-- Bandland Shoes MariaDB Migration Script
-- Bu script Supabase'deki tablo yapısını MariaDB/MySQL'de oluşturur

SET FOREIGN_KEY_CHECKS = 0;

-- Database oluştur (eğer yoksa)
CREATE DATABASE IF NOT EXISTS `bandland_shoes` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bandland_shoes`;

-- Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `parent_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_categories_slug` (`slug`),
    INDEX `idx_categories_parent` (`parent_id`),
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Genders Table
CREATE TABLE IF NOT EXISTS `genders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_genders_slug` (`slug`)
) ENGINE=InnoDB;

-- Colors Table
CREATE TABLE IF NOT EXISTS `colors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `hex_code` VARCHAR(7),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_colors_name` (`name`)
) ENGINE=InnoDB;

-- Sizes Table
CREATE TABLE IF NOT EXISTS `sizes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `size_value` VARCHAR(10) NOT NULL,
    `size_type` ENUM('EU', 'US', 'UK') DEFAULT 'EU',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sizes_value_type` (`size_value`, `size_type`)
) ENGINE=InnoDB;

-- Product Models Table
CREATE TABLE IF NOT EXISTS `product_models` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `features` TEXT,
    `base_price` DECIMAL(10,2) NOT NULL,
    `is_featured` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_product_models_name` (`name`),
    INDEX `idx_product_models_featured` (`is_featured`),
    INDEX `idx_product_models_price` (`base_price`)
) ENGINE=InnoDB;

-- Product Categories (Many-to-Many)
CREATE TABLE IF NOT EXISTS `product_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_product_category` (`product_id`, `category_id`),
    INDEX `idx_product_categories_product` (`product_id`),
    INDEX `idx_product_categories_category` (`category_id`),
    FOREIGN KEY (`product_id`) REFERENCES `product_models`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Product Genders (Many-to-Many)
CREATE TABLE IF NOT EXISTS `product_genders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `gender_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_product_gender` (`product_id`, `gender_id`),
    INDEX `idx_product_genders_product` (`product_id`),
    INDEX `idx_product_genders_gender` (`gender_id`),
    FOREIGN KEY (`product_id`) REFERENCES `product_models`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`gender_id`) REFERENCES `genders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Product Variants Table
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `model_id` INT NOT NULL,
    `color_id` INT NOT NULL,
    `size_id` INT NOT NULL,
    `sku` VARCHAR(100) NOT NULL UNIQUE,
    `price` DECIMAL(10,2) NOT NULL,
    `original_price` DECIMAL(10,2),
    `stock_quantity` INT DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_product_variants_model` (`model_id`),
    INDEX `idx_product_variants_color` (`color_id`),
    INDEX `idx_product_variants_size` (`size_id`),
    INDEX `idx_product_variants_sku` (`sku`),
    INDEX `idx_product_variants_stock` (`stock_quantity`),
    UNIQUE KEY `unique_model_color_size` (`model_id`, `color_id`, `size_id`),
    FOREIGN KEY (`model_id`) REFERENCES `product_models`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`color_id`) REFERENCES `colors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`size_id`) REFERENCES `sizes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Product Images Table
CREATE TABLE IF NOT EXISTS `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `model_id` INT NOT NULL,
    `color_id` INT,
    `image_url` TEXT NOT NULL,
    `alt_text` VARCHAR(255),
    `is_primary` BOOLEAN DEFAULT FALSE,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_product_images_model` (`model_id`),
    INDEX `idx_product_images_color` (`color_id`),
    INDEX `idx_product_images_primary` (`is_primary`),
    FOREIGN KEY (`model_id`) REFERENCES `product_models`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`color_id`) REFERENCES `colors`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Blogs Table
CREATE TABLE IF NOT EXISTS `blogs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `title` TEXT NOT NULL,
    `excerpt` TEXT,
    `content` LONGTEXT,
    `image_url` TEXT,
    `category` VARCHAR(100),
    `tags` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_blogs_category` (`category`),
    FULLTEXT `idx_blogs_search` (`title`, `excerpt`)
) ENGINE=InnoDB;

-- About Settings Table
CREATE TABLE IF NOT EXISTS `about_settings` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `meta_key` VARCHAR(255) NOT NULL UNIQUE,
    `meta_value` LONGTEXT,
    `section` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_about_settings_key` (`meta_key`),
    INDEX `idx_about_settings_section` (`section`)
) ENGINE=InnoDB;

-- About Content Blocks Table
CREATE TABLE IF NOT EXISTS `about_content_blocks` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `section` VARCHAR(100) NOT NULL,
    `title` TEXT,
    `subtitle` TEXT,
    `content` LONGTEXT,
    `image_url` TEXT,
    `icon` VARCHAR(100),
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_about_content_section` (`section`),
    INDEX `idx_about_content_order` (`sort_order`)
) ENGINE=InnoDB;

-- Slider Items Table
CREATE TABLE IF NOT EXISTS `slider_items` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `title` TEXT NOT NULL,
    `description` TEXT,
    `image_url` TEXT,
    `bg_color` VARCHAR(7) DEFAULT '#f0f0f0',
    `button_text` VARCHAR(100) NOT NULL,
    `button_url` VARCHAR(255) NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slider_active` (`is_active`),
    INDEX `idx_slider_order` (`sort_order`)
) ENGINE=InnoDB;

-- Seasonal Collections Table
CREATE TABLE IF NOT EXISTS `seasonal_collections` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `title` TEXT NOT NULL,
    `description` TEXT,
    `image_url` TEXT,
    `button_url` TEXT,
    `sort_order` INT DEFAULT 0,
    `layout_type` ENUM('left', 'right') DEFAULT 'left',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_seasonal_order` (`sort_order`)
) ENGINE=InnoDB;

-- Contact Info Table
CREATE TABLE IF NOT EXISTS `contact_info` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `section` VARCHAR(100) NOT NULL,
    `field` VARCHAR(100) NOT NULL,
    `value` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_contact_section` (`section`),
    INDEX `idx_contact_field` (`field`)
) ENGINE=InnoDB;

-- Social Media Links Table
CREATE TABLE IF NOT EXISTS `social_media_links` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `platform` VARCHAR(50) NOT NULL,
    `url` TEXT NOT NULL,
    `icon_class` VARCHAR(100),
    `order_index` INT DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_social_platform` (`platform`),
    INDEX `idx_social_active` (`is_active`),
    INDEX `idx_social_order` (`order_index`)
) ENGINE=InnoDB;

-- Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_contact_messages_email` (`email`),
    INDEX `idx_contact_messages_date` (`created_at`)
) ENGINE=InnoDB;

-- Site Settings Table
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(255) NOT NULL UNIQUE,
    `setting_value` LONGTEXT NOT NULL,
    `setting_group` VARCHAR(100) DEFAULT 'general',
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_site_settings_key` (`setting_key`),
    INDEX `idx_site_settings_group` (`setting_group`)
) ENGINE=InnoDB;

-- SEO Settings Table
CREATE TABLE IF NOT EXISTS `seo_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(255) NOT NULL UNIQUE,
    `setting_value` LONGTEXT NOT NULL,
    `setting_type` VARCHAR(50) DEFAULT 'meta',
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_seo_settings_key` (`setting_key`),
    INDEX `idx_seo_settings_type` (`setting_type`),
    INDEX `idx_seo_settings_active` (`is_active`)
) ENGINE=InnoDB;

-- Admin Users Table (Yeni)
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(255),
    `email` VARCHAR(255),
    `is_active` BOOLEAN DEFAULT TRUE,
    `last_login` TIMESTAMP NULL,
    `login_time` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_admin_username` (`username`),
    INDEX `idx_admin_active` (`is_active`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- Örnek veri ekleyelim

-- Kategoriler
INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Kadın Ayakkabı', 'kadin-ayakkabi', 'Kadın ayakkabı koleksiyonu'),
(2, 'Erkek Ayakkabı', 'erkek-ayakkabi', 'Erkek ayakkabı koleksiyonu'),
(3, 'Çocuk Ayakkabı', 'cocuk-ayakkabi', 'Çocuk ayakkabı koleksiyonu'),
(4, 'Spor Ayakkabı', 'spor-ayakkabi', 'Spor ayakkabı koleksiyonu'),
(5, 'Klasik Ayakkabı', 'klasik-ayakkabi', 'Klasik ayakkabı koleksiyonu'),
(6, 'Bot & Çizme', 'bot-cizme', 'Bot ve çizme koleksiyonu');

-- Cinsiyetler
INSERT IGNORE INTO `genders` (`id`, `name`, `slug`) VALUES
(1, 'Kadın', 'kadin'),
(2, 'Erkek', 'erkek'),
(3, 'Çocuk', 'cocuk'),
(4, 'Unisex', 'unisex');

-- Renkler
INSERT IGNORE INTO `colors` (`id`, `name`, `hex_code`) VALUES
(1, 'Siyah', '#000000'),
(2, 'Beyaz', '#FFFFFF'),
(3, 'Kahverengi', '#8B4513'),
(4, 'Lacivert', '#000080'),
(5, 'Gri', '#808080'),
(6, 'Kırmızı', '#FF0000'),
(7, 'Mavi', '#0000FF'),
(8, 'Yeşil', '#008000'),
(9, 'Sarı', '#FFFF00'),
(10, 'Pembe', '#FFC0CB');

-- Bedenler
INSERT IGNORE INTO `sizes` (`id`, `size_value`, `size_type`) VALUES
(1, '35', 'EU'),
(2, '36', 'EU'),
(3, '37', 'EU'),
(4, '38', 'EU'),
(5, '39', 'EU'),
(6, '40', 'EU'),
(7, '41', 'EU'),
(8, '42', 'EU'),
(9, '43', 'EU'),
(10, '44', 'EU'),
(11, '45', 'EU'),
(12, '46', 'EU');

-- Varsayılan admin kullanıcısı (şifre: admin123)
INSERT IGNORE INTO `admin_users` (`username`, `password_hash`, `full_name`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Site Yöneticisi', 'admin@bandlandshoes.com');

-- İletişim bilgileri
INSERT IGNORE INTO `contact_info` (`section`, `field`, `value`) VALUES
('contact', 'address', 'İstanbul, Türkiye'),
('contact', 'phone', '+90 555 123 4567'),
('contact', 'email', 'info@bandlandshoes.com'),
('contact', 'working_hours', 'Pazartesi - Cumartesi: 09:00 - 18:00'),
('footer', 'company_name', 'Bandland Shoes'),
('footer', 'copyright', '© 2025 Bandland Shoes. Tüm hakları saklıdır.');

-- Sosyal medya linkleri
INSERT IGNORE INTO `social_media_links` (`platform`, `url`, `icon_class`, `order_index`) VALUES
('Facebook', 'https://facebook.com/bandlandshoes', 'fab fa-facebook', 1),
('Instagram', 'https://instagram.com/bandlandshoes', 'fab fa-instagram', 2),
('Twitter', 'https://twitter.com/bandlandshoes', 'fab fa-twitter', 3),
('YouTube', 'https://youtube.com/bandlandshoes', 'fab fa-youtube', 4),
('WhatsApp', 'https://wa.me/905551234567', 'fab fa-whatsapp', 5);

-- Slider örneği
INSERT IGNORE INTO `slider_items` (`title`, `description`, `button_text`, `button_url`, `bg_color`, `sort_order`) VALUES
('Yeni Sezon Koleksiyonu', 'En trend ayakkabı modelleri şimdi mağazalarımızda!', 'Koleksiyonu İncele', '/products.php', '#1a365d', 1),
('Ücretsiz Kargo', '250 TL ve üzeri alışverişlerinizde kargo bedava!', 'Alışverişe Başla', '/products.php', '#2d543d', 2),
('İndirim Fırsatları', 'Seçili ürünlerde %50\'ye varan indirimler!', 'Fırsatları Gör', '/products.php', '#7c2d12', 3);

-- Sezonluk koleksiyonlar
INSERT IGNORE INTO `seasonal_collections` (`title`, `description`, `button_url`, `layout_type`, `sort_order`) VALUES
('İlkbahar Koleksiyonu', 'Baharın renkli dünyasında stilinizi yansıtacak özel tasarımlar.', '/products.php?category=ilkbahar', 'left', 1),
('Yaz Koleksiyonu', 'Sıcak yaz günleri için rahat ve şık ayakkabı modelleri.', '/products.php?category=yaz', 'right', 2);

COMMIT;
