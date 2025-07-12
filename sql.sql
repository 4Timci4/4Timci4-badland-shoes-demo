-- --------------------------------------------------------
-- Sunucu:                       127.0.0.1
-- Sunucu sürümü:                11.7.2-MariaDB - mariadb.org binary distribution
-- Sunucu İşletim Sistemi:       Win64
-- HeidiSQL Sürüm:               12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- bandland_shoes için veritabanı yapısı dökülüyor
CREATE DATABASE IF NOT EXISTS `bandland_shoes` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci */;
USE `bandland_shoes`;

-- tablo yapısı dökülüyor bandland_shoes.about_content_blocks
CREATE TABLE IF NOT EXISTS `about_content_blocks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `section` text NOT NULL,
  `title` text DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `icon` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.about_content_blocks: ~8 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `about_content_blocks` (`id`, `section`, `title`, `subtitle`, `content`, `image_url`, `icon`, `sort_order`, `created_at`, `updated_at`) VALUES
	(1, 'values', 'Kalite', NULL, 'En kaliteli malzemeler ve işçilik ile üretilen ürünlerimiz, uzun ömürlü kullanım için tasarlanmıştır.', NULL, 'fas fa-star', 1, '2025-07-04 10:45:35', '2025-07-04 10:45:35'),
	(2, 'values', 'Tasarım', NULL, 'Trendleri yakından takip eden tasarım ekibimiz, hem şık hem de fonksiyonel ayakkabılar tasarlar.', NULL, 'fas fa-tshirt', 2, '2025-07-04 10:45:35', '2025-07-04 10:45:35'),
	(3, 'values', 'Sürdürülebilirlik', NULL, 'Çevreye duyarlı üretim süreçleri ve malzemeler kullanarak doğayı korumaya özen gösteriyoruz.', NULL, 'fas fa-leaf', 3, '2025-07-04 10:45:35', '2025-07-04 10:45:35'),
	(4, 'values', 'Müşteri Memnuniyeti', NULL, 'Müşterilerimizin memnuniyeti bizim için en önemli değerdir. Her zaman en iyi hizmeti sunmaya çalışıyoruz.', NULL, 'fas fa-users', 4, '2025-07-04 10:45:35', '2025-07-04 10:45:35'),
	(5, 'team', 'Ahmet Yılmaz', 'Kurucu ve CEO', '20 yılı aşkın ayakkabı sektörü tecrübesi ile Schön\'u kurmuş ve global bir marka haline getirmiştir.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTJ8fHByb2ZpbGUlMjBwaG90b3xlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60', NULL, 1, '2025-07-04 10:45:35', '2025-07-04 10:45:35'),
	(6, 'team', 'Zeynep Kaya', 'Tasarım Direktörü', 'Moda sektöründe 15 yıllık deneyimi ile Schön\'un özgün ve trend tasarımlarının arkasındaki isimdir.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8M3x8cHJvZmlsZSUyMHBob3RvfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60', NULL, 2, '2025-07-04 10:45:35', '2025-07-04 10:45:35'),
	(7, 'team', 'Mehmet Öztürk', 'Üretim Müdürü', 'Ayakkabı üretiminde uzman olan Mehmet, Schön\'un kaliteli ürünlerinin üretim süreçlerini yönetmektedir.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTF8fHByb2ZpbGUlMjBwaG90b3xlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60', NULL, 3, '2025-07-04 10:45:35', '2025-07-04 10:45:35'),
	(8, 'team', 'Ayşe Demir', 'Pazarlama Direktörü', 'Dijital pazarlama konusunda uzman olan Ayşe, Schön markasının tanıtım ve pazarlama stratejilerini yönetmektedir.', 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MjN8fHByb2ZpbGUlMjBwaG90b3xlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60', NULL, 4, '2025-07-04 10:45:35', '2025-07-04 10:45:35');

-- tablo yapısı dökülüyor bandland_shoes.about_settings
CREATE TABLE IF NOT EXISTS `about_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `meta_key` text NOT NULL,
  `meta_value` text DEFAULT NULL,
  `section` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.about_settings: ~14 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `about_settings` (`id`, `meta_key`, `meta_value`, `section`, `created_at`, `updated_at`) VALUES
	(1, 'banner_title', 'Hakkımızda', 'banner', '2025-07-04 10:45:22', '2025-07-04 10:45:22'),
	(2, 'banner_subtitle', 'Schön markası hikayesi', 'banner', '2025-07-04 10:45:22', '2025-07-04 10:45:22'),
	(3, 'story_title', 'Hikayemiz', 'story', '2025-07-04 10:45:22', '2025-07-08 06:11:55'),
	(4, 'story_subtitle', 'Schön markasının doğuşu', 'story', '2025-07-04 10:45:22', '2025-07-08 06:11:55'),
	(5, 'story_image_url', 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8c2hvZSUyMHN0b3JlfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60', 'story', '2025-07-04 10:45:22', '2025-07-08 06:11:56'),
	(6, 'story_content_title', '2010\'dan Beri Kalite ve Zarafet', 'story', '2025-07-04 10:45:22', '2025-07-08 06:11:55'),
	(7, 'story_content_p1', 'Schön, 2010 yılında İstanbul\'da kurulmuş, Türkiye\'nin önde gelen ayakkabı markalarından biridir. Kurucumuz Ahmet Yılmaz, uzun yıllar ayakkabı sektöründe çalıştıktan sonra kendi markasını yaratma hayalini gerçekleştirmiştir.', 'story', '2025-07-04 10:45:22', '2025-07-08 06:11:55'),
	(8, 'story_content_p2', 'Almanca\'da "güzel" anlamına gelen "Schön" ismi, markamızın temel felsefesini yansıtmaktadır: Estetik tasarım ve kaliteli işçilik ile üretilen, her adımda rahatlık ve şıklık sunan ayakkabılar.', 'story', '2025-07-04 10:45:22', '2025-07-08 06:11:56'),
	(9, 'story_content_p3', 'İlk mağazamızı Nişantaşı\'nda açtık ve kısa sürede müşterilerimizin beğenisini kazandık. Bugün Türkiye genelinde 25 mağazamız ve güçlü online varlığımız ile müşterilerimize hizmet vermeye devam ediyoruz.', 'story', '2025-07-04 10:45:22', '2025-07-08 06:11:56'),
	(10, 'values_title', 'Değerlerimiz', 'values', '2025-07-04 10:45:22', '2025-07-08 06:11:56'),
	(11, 'values_subtitle', 'Schön markasını özel yapan değerler', 'values', '2025-07-04 10:45:22', '2025-07-08 06:11:56'),
	(12, 'team_title', 'Ekibimiz', 'team', '2025-07-04 10:45:22', '2025-07-08 06:11:56'),
	(13, 'team_subtitle', 'Schön\'un arkasındaki değerli insanlar', 'team', '2025-07-04 10:45:22', '2025-07-08 06:11:56'),
	(14, 'story_content_homepage', 'Bu ana sayfa için özel olarak oluşturulmuş bir hakkında metnidir. Buraya istediğiniz içeriği yazabilirsiniz.', 'homepage', '2025-07-04 10:54:30', '2025-07-08 06:11:56');

-- tablo yapısı dökülüyor bandland_shoes.admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.admins: ~1 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `email`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
	(1, 'admin', '$2y$10$dc3nCMXM1OIiFxr2mPEb6uAOZLvVKpKG7lfrY1VeGXetmWRUiQyuy', 'Sistem Yöneticisi', 'admin@example.com', 1, NULL, '2025-07-11 22:20:33', '2025-07-11 23:20:33');

-- tablo yapısı dökülüyor bandland_shoes.auth_tokens
CREATE TABLE IF NOT EXISTS `auth_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` char(36) NOT NULL,
  `selector` varchar(255) NOT NULL,
  `hashed_validator` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `auth_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.auth_tokens: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- tablo yapısı dökülüyor bandland_shoes.blogs
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `category` text DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.blogs: ~2 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `blogs` (`id`, `title`, `excerpt`, `content`, `image_url`, `category`, `tags`, `created_at`) VALUES
	(1, '2025 Yazının Ayakkabı Trendleri', 'Bu yaz sokak modasına yön verecek en popüler ayakkabı modellerini sizler için derledik.', 'Detaylı blog içeriği buraya gelecek...', 'https://picsum.photos/seed/blog1/800/600', 'Moda', '["yaz", "trend", "sneaker"]', '2025-07-12 00:15:03'),
	(2, 'Deri Ayakkabı Bakımının Püf Noktaları', 'Deri ayakkabılarınızın ömrünü uzatacak pratik bakım önerileri.', 'Detaylı blog içeriği buraya gelecek...', 'https://picsum.photos/seed/blog2/800/600', 'Bakım', '["deri", "ayakkabı", "bakım"]', '2025-07-12 00:15:03');

-- tablo yapısı dökülüyor bandland_shoes.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_type` varchar(255) DEFAULT 'product_type',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.categories: ~7 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `category_type`, `created_at`) VALUES
	(1, 'Sneaker', 'sneaker', 'Günlük ve spor kullanım için rahat ayakkabılar.', 'product_type', '2025-07-07 08:02:35'),
	(2, 'Koşu Ayakkabısı', 'kosu-ayakkabisi', 'Performans odaklı koşu ve antrenman ayakkabıları.', 'product_type', '2025-07-07 08:02:35'),
	(3, 'Basketbol Ayakkabısı', 'basketbol-ayakkabisi', 'Yüksek bilek ve zemin tutuşu sağlayan basketbol ayakkabıları.', 'product_type', '2025-07-07 08:02:35'),
	(4, 'Bot', 'bot', 'Soğuk ve zorlu hava koşulları için dayanıklı botlar.', 'product_type', '2025-07-07 08:02:35'),
	(5, 'Sandalet & Terlik', 'sandalet-terlik', 'Yaz ayları ve plaj kullanımı için.', 'product_type', '2025-07-07 08:02:35'),
	(6, 'Klasik Ayakkabı', 'klasik-ayakkabi', 'Resmi ve özel günler için şık ayakkabılar.', 'product_type', '2025-07-07 08:02:35'),
	(7, 'Outdoor & Trekking', 'outdoor-trekking', 'Doğa yürüyüşleri ve zorlu araziler için tasarlanmış ayakkabılar.', 'product_type', '2025-07-07 08:02:35');

-- görünüm yapısı dökülüyor bandland_shoes.category_product_counts
-- VIEW bağımlılık sorunlarını çözmek için geçici tablolar oluşturuluyor
CREATE TABLE `category_product_counts` (
	`category_id` INT(11) NOT NULL,
	`category_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`category_slug` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`product_count` BIGINT(21) NOT NULL
) ENGINE=MyISAM;

-- tablo yapısı dökülüyor bandland_shoes.colors
CREATE TABLE IF NOT EXISTS `colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `hex_code` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.colors: ~15 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `colors` (`id`, `name`, `hex_code`, `display_order`, `created_at`) VALUES
	(1, 'Siyah', '#000000', 1, '2025-07-07 08:02:50'),
	(2, 'Beyaz', '#FFFFFF', 2, '2025-07-07 08:02:50'),
	(3, 'Gri', '#808080', 3, '2025-07-07 08:02:50'),
	(4, 'Kırmızı', '#FF0000', 4, '2025-07-07 08:02:50'),
	(5, 'Mavi', '#0000FF', 5, '2025-07-07 08:02:50'),
	(6, 'Yeşil', '#008000', 6, '2025-07-07 08:02:50'),
	(7, 'Sarı', '#FFFF00', 7, '2025-07-07 08:02:50'),
	(8, 'Lacivert', '#000080', 8, '2025-07-07 08:02:50'),
	(9, 'Bordo', '#800000', 9, '2025-07-07 08:02:50'),
	(10, 'Bej', '#F5F5DC', 10, '2025-07-07 08:02:50'),
	(11, 'Haki', '#808000', 11, '2025-07-07 08:02:50'),
	(12, 'Turuncu', '#FFA500', 12, '2025-07-07 08:02:50'),
	(13, 'Pembe', '#FFC0CB', 13, '2025-07-07 08:02:50'),
	(14, 'Mor', '#800080', 14, '2025-07-07 08:02:50'),
	(15, 'Kahverengi', '#A52A2A', 15, '2025-07-07 08:02:50');

-- tablo yapısı dökülüyor bandland_shoes.contact_info
CREATE TABLE IF NOT EXISTS `contact_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL,
  `field` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.contact_info: ~30 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `contact_info` (`id`, `section`, `field`, `value`, `created_at`, `updated_at`) VALUES
	(1, 'main', 'phone', '+90 212 123 45 67', '2025-07-12 00:14:45', '2025-07-12 00:14:45'),
	(2, 'main', 'email', 'info@bandlandshoes.com', '2025-07-12 00:14:45', '2025-07-12 00:14:45'),
	(3, 'main', 'address', 'Ayakkabıcılar Sitesi, No: 123, İstanbul', '2025-07-12 00:14:45', '2025-07-12 00:14:45'),
	(4, 'contact', 'description', 'Herh321321321angi bir sorunuz, öneriniz veya geri bildiriminiz mi var? Aşağıdaki bilgiler aracılığıyla bize ulaşabilir veya iletişim formunu doldurabilirsiniz.', '2025-07-05 18:02:32', '2025-07-09 05:37:02'),
	(5, 'contact', 'address', 'Bağdat C2131addesi No:123&amp;amp;amp;amp;lt;br&amp;amp;amp;amp;gt;Kadıköy, İstanbul', '2025-07-05 18:02:32', '2025-07-09 05:37:02'),
	(6, 'contact', 'phone1', '+90 555 123 4567111', '2025-07-05 18:02:32', '2025-07-09 05:37:02'),
	(7, 'contact', 'phone2', '+90 216 123 4567', '2025-07-05 18:02:32', '2025-07-09 05:37:03'),
	(8, 'contact', 'email1', 'info@schon.commmm', '2025-07-05 18:02:32', '2025-07-09 05:37:03'),
	(9, 'contact', 'email2', 'support@schon.com', '2025-07-05 18:02:32', '2025-07-09 05:37:03'),
	(10, 'contact', 'working_hours1', 'Pazartesi - Cumartesi: 10:00 - 20:00', '2025-07-05 18:02:32', '2025-07-09 05:37:03'),
	(11, 'contact', 'working_hours2', 'Pazar: 12:00 - 18:00', '2025-07-05 18:02:32', '2025-07-09 05:37:03'),
	(12, 'form', 'title', 'Bize Mesaj Gönderin', '2025-07-05 18:02:32', '2025-07-09 05:37:04'),
	(13, 'form', 'success_title', 'Mesajınız Başarıyla Gönderildi!', '2025-07-05 18:02:32', '2025-07-09 05:37:04'),
	(14, 'form', 'success_message', 'En kısa sürede size geri dönüş yapacağız.', '2025-07-05 18:02:32', '2025-07-09 05:37:04'),
	(15, 'form', 'success_button', 'Yeni Mesaj Gönder', '2025-07-05 18:02:32', '2025-07-09 05:37:04'),
	(16, 'map', 'title', 'Bizi Ziyaret Edinnnn', '2025-07-05 18:02:32', '2025-07-09 05:37:04'),
	(17, 'map', 'embed_code', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d24086.521847965187!2d29.040827342981352!3d40.96982862395644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab617a4696a95%3A0xb08d84362e53c232!2zQmHEn2RhdCBDYWRkZXNpLCBLYWTEsWvDtnkvxLBzdGFuYnVs!5e0!3m2!1str!2str!4v1624529096157!5m2!1str!2str', '2025-07-05 18:02:32', '2025-07-09 05:37:05'),
	(18, 'footer', 'site_title', 'Schön', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(19, 'footer', 'site_description', 'Türkiye\'nin en kaliteli ayakkabı markası olarak müşterilerimize en iyi hizmeti sunmaktayız.', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(20, 'footer', 'copyright_text', '© 2025 Schön. Tüm hakları saklıdır.', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(21, 'footer_links', 'home_url', '/index.php', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(22, 'footer_links', 'home_text', 'Ana Sayfa', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(23, 'footer_links', 'products_url', '/products.php', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(24, 'footer_links', 'products_text', 'Ürünler', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(25, 'footer_links', 'about_url', '/about.php', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(26, 'footer_links', 'about_text', 'Hakkımızda', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(27, 'footer_links', 'blog_url', '/blog.php', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(28, 'footer_links', 'blog_text', 'Blog', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(29, 'footer_links', 'contact_url', '/contact.php', '2025-07-06 17:46:38', '2025-07-06 17:46:38'),
	(30, 'footer_links', 'contact_text', 'İletişim', '2025-07-06 17:46:38', '2025-07-06 17:46:38');

-- tablo yapısı dökülüyor bandland_shoes.contact_messages
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.contact_messages: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- tablo yapısı dökülüyor bandland_shoes.email_templates
CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL,
  `body_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- bandland_shoes.email_templates: ~1 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `email_templates` (`id`, `name`, `description`, `subject`, `body_html`, `body_text`, `created_at`, `updated_at`) VALUES
	(1, 'registration_confirmation', 'Yeni Kullanıcı Kayıt Onayı', 'Hoş Geldiniz: {{fullName}}', '<p>Merhaba {{fullName}},</p>\r\n<p>Badland Shoes ailesine hoş geldiniz!</p>\r\n<p>Hesabınızın başarıyla oluşturulduğunu bildirmekten mutluluk duyarız. Artık en yeni koleksiyonlarımızı keşfedebilir, favori &uuml;r&uuml;nlerinizi listenize ekleyebilir ve size &ouml;zel kampanyalardan anında haberdar olabilirsiniz.</p>\r\n<p>Stilinizi tamamlayacak o m&uuml;kemmel ayakkabıyı bulmak i&ccedil;in hemen alışverişe başlayın!</p>\r\n<p>[Alışverişe Başla] -&gt; {{site_url}}</p>\r\n<p>Herhangi bir sorunuz veya yardıma ihtiyacınız olursa, <a href="{{contact_url}}">Buradan</a> bizimle iletişime ge&ccedil;mekten &ccedil;ekinmeyin.</p>\r\n<p>Keyifli alışverişler dileriz!</p>\r\n<p>Saygılarımızla, <strong>Badland Shoes Ekibi</strong></p>', 'Merhaba {{fullName}},\r\n\r\n... (Düz metin içeriği buraya)', '2025-07-12 08:51:22', '2025-07-12 15:36:37'),
	(2, 'password_reset', 'Şifre Sıfırlama', 'Badland Shoes - Şifre Sıfırlama Talebi', '<p>Şifre sıfırlama içeriği gelecek</p>', 'Şifre sıfırlama düz metin içeriği', '2025-07-12 16:07:09', '2025-07-12 16:07:09');

-- tablo yapısı dökülüyor bandland_shoes.favorites
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` char(36) NOT NULL,
  `variant_id` bigint(20) NOT NULL,
  `color_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_variant` (`user_id`,`variant_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_variant_id` (`variant_id`),
  KEY `idx_color_id` (`color_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.favorites: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- görünüm yapısı dökülüyor bandland_shoes.favorites_view
-- VIEW bağımlılık sorunlarını çözmek için geçici tablolar oluşturuluyor
CREATE TABLE `favorites_view` (
	`favorite_id` BIGINT(20) NOT NULL,
	`user_id` CHAR(36) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`variant_id` BIGINT(20) NOT NULL,
	`favorite_color_id` INT(11) NULL,
	`favorite_added_at` TIMESTAMP NULL,
	`pv_id` INT(11) NOT NULL,
	`model_id` INT(11) NULL,
	`size_id` INT(11) NULL,
	`color_id` INT(11) NULL,
	`stock_quantity` INT(11) NULL,
	`product_id` INT(11) NOT NULL,
	`product_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`product_description` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`color_name` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`color_hex` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`size_value` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`variant_image_url` MEDIUMTEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`product_image_url` MEDIUMTEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci'
) ENGINE=MyISAM;

-- tablo yapısı dökülüyor bandland_shoes.genders
CREATE TABLE IF NOT EXISTS `genders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.genders: ~4 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `genders` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
	(1, 'Erkek', 'erkek', 'Erkekler için tasarlanmış ürünler.', '2025-07-07 08:02:35'),
	(2, 'Kadın', 'kadin', 'Kadınlar için tasarlanmış ürünler.', '2025-07-07 08:02:35'),
	(3, 'Çocuk', 'cocuk', 'Çocuklar için tasarlanmış ürünler.', '2025-07-07 08:02:35'),
	(4, 'Unisex', 'unisex', 'Hem erkekler hem de kadınlar için uygun ürünler.', '2025-07-07 08:02:35');

-- görünüm yapısı dökülüyor bandland_shoes.gender_product_counts
-- VIEW bağımlılık sorunlarını çözmek için geçici tablolar oluşturuluyor
CREATE TABLE `gender_product_counts` (
	`id` INT(11) NOT NULL,
	`name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`slug` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`product_count` BIGINT(21) NOT NULL
) ENGINE=MyISAM;

-- tablo yapısı dökülüyor bandland_shoes.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.password_resets: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- görünüm yapısı dökülüyor bandland_shoes.product_api_summary
-- VIEW bağımlılık sorunlarını çözmek için geçici tablolar oluşturuluyor
CREATE TABLE `product_api_summary` (
	`id` INT(11) NOT NULL,
	`name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`description` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`is_featured` TINYINT(1) NULL,
	`created_at` TIMESTAMP NULL,
	`updated_at` TIMESTAMP NULL,
	`primary_image` MEDIUMTEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`primary_category` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`min_price` DECIMAL(10,2) NULL,
	`max_price` DECIMAL(10,2) NULL,
	`total_stock` DECIMAL(32,0) NULL
) ENGINE=MyISAM;

-- tablo yapısı dökülüyor bandland_shoes.product_categories
CREATE TABLE IF NOT EXISTS `product_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_models` (`id`),
  CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.product_categories: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- görünüm yapısı dökülüyor bandland_shoes.product_details_view
-- VIEW bağımlılık sorunlarını çözmek için geçici tablolar oluşturuluyor
CREATE TABLE `product_details_view` (
	`id` INT(11) NOT NULL,
	`name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`description` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`features` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`is_featured` TINYINT(1) NULL,
	`created_at` TIMESTAMP NULL,
	`updated_at` TIMESTAMP NULL,
	`categories` MEDIUMTEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`genders` MEDIUMTEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`variants` MEDIUMTEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`images` MEDIUMTEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci'
) ENGINE=MyISAM;

-- tablo yapısı dökülüyor bandland_shoes.product_genders
CREATE TABLE IF NOT EXISTS `product_genders` (
  `product_id` int(11) NOT NULL,
  `gender_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`,`gender_id`),
  KEY `gender_id` (`gender_id`),
  CONSTRAINT `product_genders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_models` (`id`),
  CONSTRAINT `product_genders_ibfk_2` FOREIGN KEY (`gender_id`) REFERENCES `genders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.product_genders: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- tablo yapısı dökülüyor bandland_shoes.product_images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  `image_url` text NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `model_id` (`model_id`),
  KEY `color_id` (`color_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `product_models` (`id`),
  CONSTRAINT `product_images_ibfk_2` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.product_images: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- tablo yapısı dökülüyor bandland_shoes.product_models
CREATE TABLE IF NOT EXISTS `product_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `features` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.product_models: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- tablo yapısı dökülüyor bandland_shoes.product_variants
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  `size_id` int(11) DEFAULT NULL,
  `sku` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `model_id` (`model_id`),
  KEY `color_id` (`color_id`),
  KEY `size_id` (`size_id`),
  CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `product_models` (`id`),
  CONSTRAINT `product_variants_ibfk_2` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`),
  CONSTRAINT `product_variants_ibfk_3` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.product_variants: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- tablo yapısı dökülüyor bandland_shoes.seasonal_collections
CREATE TABLE IF NOT EXISTS `seasonal_collections` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `button_url` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `layout_type` varchar(255) DEFAULT 'left',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.seasonal_collections: ~2 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `seasonal_collections` (`id`, `title`, `description`, `image_url`, `button_url`, `sort_order`, `layout_type`, `created_at`, `updated_at`) VALUES
	(1, 'Yaz Koleksiyonu', 'En yeni ve en trend yazlık ayakkabılarımızı keşfedin.', 'https://picsum.photos/seed/summer/1200/400', '/products?collection=summer', 0, 'right', '2025-07-12 00:14:30', '2025-07-12 00:56:54'),
	(2, 'Kış İndirimleri', 'Sezon sonu indirimlerini kaçırmayın!', 'https://picsum.photos/seed/winter/1200/400', '/products?collection=winter', 0, 'left', '2025-07-12 00:14:30', '2025-07-12 00:14:30');

-- tablo yapısı dökülüyor bandland_shoes.seo_settings
CREATE TABLE IF NOT EXISTS `seo_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `setting_key` text NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` text DEFAULT 'meta',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.seo_settings: ~24 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `seo_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'meta_title', 'Bandland Shoes - En Trend Ayakkabılar', 'meta', 1, '2025-07-12 00:15:25', '2025-07-12 00:15:25'),
	(2, 'meta_description', 'En yeni sezon ayakkabı, bot, çizme ve sneaker modelleri Bandland Shoes\'da. Ücretsiz kargo ve iade fırsatıyla hemen keşfedin!', 'meta', 1, '2025-07-12 00:15:25', '2025-07-12 00:15:25'),
	(3, 'meta_keywords', 'ayakkabı, sneaker, bot, çizme, topuklu ayakkabı', 'meta', 1, '2025-07-12 00:15:25', '2025-07-12 00:15:25'),
	(4, 'default_keywords', 'ayakkabı, kadın ayakkabı, erkek ayakkabı, çocuk ayakkabı, spor ayakkabı, klasik ayakkabı', 'meta', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(5, 'author', 'Schön', 'meta', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(6, 'robots', 'index, follow', 'meta', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(7, 'og_site_name', 'Schön', 'social', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(8, 'og_type', 'website', 'social', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(9, 'og_image', 'assets/images/og-image.jpg', 'social', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(10, 'twitter_card', 'summary_large_image', 'social', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(11, 'twitter_site', '@schonshoes', 'social', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(12, 'facebook_app_id', '', 'social', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(13, 'linkedin_company', 'schon-shoes', 'social', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(14, 'google_analytics_id', '', 'analytics', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(15, 'google_tag_manager_id', '', 'analytics', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(16, 'facebook_pixel_id', '', 'analytics', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(17, 'google_search_console', '', 'analytics', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(18, 'bing_webmaster', '', 'analytics', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(19, 'yandex_verification', '', 'analytics', 1, '2025-07-06 06:54:51', '2025-07-06 06:54:51'),
	(20, 'canonical_enabled', 'true', 'technical', 1, '2025-07-06 06:54:51', '2025-07-08 07:33:17'),
	(21, 'sitemap_enabled', 'true', 'technical', 1, '2025-07-06 06:54:51', '2025-07-08 07:33:17'),
	(22, 'schema_enabled', 'true', 'technical', 1, '2025-07-06 06:54:51', '2025-07-08 07:33:17'),
	(23, 'breadcrumbs_enabled', 'true', 'technical', 1, '2025-07-06 06:54:51', '2025-07-08 07:33:18'),
	(24, 'amp_enabled', 'true', 'technical', 1, '2025-07-06 06:54:51', '2025-07-08 07:33:18');

-- tablo yapısı dökülüyor bandland_shoes.site_settings
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `setting_key` text NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` text DEFAULT 'general',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.site_settings: ~21 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'site_name', 'Bandland Shoes', 'general', NULL, '2025-07-12 00:15:34', '2025-07-12 00:15:34'),
	(2, 'site_logo', '/assets/images/mt-logo.png', 'general', NULL, '2025-07-12 00:15:34', '2025-07-12 00:15:34'),
	(3, 'site_description', 'En kaliteli ayakkabı modelleri ve uygun fiyatlarla Schön\'de', 'general', '', '2025-07-06 08:53:55', '2025-07-08 06:23:48'),
	(4, 'site_logo', 'assets/images/mt-logo.png', 'general', '', '2025-07-06 08:53:55', '2025-07-08 06:23:48'),
	(5, 'site_favicon', 'assets/images/favicon.ico', 'general', '', '2025-07-06 08:53:56', '2025-07-08 06:23:49'),
	(6, 'primary_color', '#e91e63', 'general', '', '2025-07-06 08:53:56', '2025-07-08 06:23:49'),
	(7, 'secondary_color', '#2c2c54', 'general', '', '2025-07-06 08:53:56', '2025-07-08 06:23:49'),
	(8, 'footer_copyright', '© 2024 Schön. Tüm hakları saklıdır.', 'general', '', '2025-07-06 08:53:56', '2025-07-08 06:23:49'),
	(25, 'products_per_page', '12', 'technical', '', '2025-07-06 06:54:32', '2025-07-11 23:27:31'),
	(26, 'blogs_per_page', '10', 'technical', '', '2025-07-06 06:54:32', '2025-07-11 23:27:31'),
	(27, 'maintenance_mode', 'false', 'technical', '', '2025-07-06 06:54:32', '2025-07-11 23:27:31'),
	(28, 'site_language', 'tr', 'technical', '', '2025-07-06 06:54:32', '2025-07-11 23:27:31'),
	(29, 'timezone', 'Europe/Istanbul', 'technical', '', '2025-07-06 06:54:32', '2025-07-11 23:27:31'),
	(30, 'comments_enabled', 'true', 'technical', '', '2025-07-06 06:54:32', '2025-07-11 23:27:31'),
	(31, 'mail_host', 'mail.badlandshoes.com.tr', 'email', '', '2025-07-12 07:47:20', '2025-07-12 07:47:20'),
	(32, 'mail_port', '465', 'email', '', '2025-07-12 07:47:20', '2025-07-12 07:47:20'),
	(33, 'mail_username', 'mail@badlandshoes.com.tr', 'email', '', '2025-07-12 07:47:20', '2025-07-12 07:47:20'),
	(34, 'mail_password', 'Parola28!', 'email', '', '2025-07-12 07:47:20', '2025-07-12 07:47:20'),
	(35, 'mail_encryption', 'ssl', 'email', '', '2025-07-12 07:47:20', '2025-07-12 07:47:20'),
	(36, 'mail_from_address', 'mail@badlandshoes.com.tr', 'email', '', '2025-07-12 07:47:20', '2025-07-12 07:47:20'),
	(37, 'mail_from_name', 'Bandland Shoes', 'email', '', '2025-07-12 07:47:20', '2025-07-12 07:47:20');

-- tablo yapısı dökülüyor bandland_shoes.sizes
CREATE TABLE IF NOT EXISTS `sizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `size_value` varchar(255) NOT NULL,
  `size_type` varchar(255) DEFAULT 'EU',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.sizes: ~26 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `sizes` (`id`, `size_value`, `size_type`, `display_order`, `created_at`) VALUES
	(2, '29', 'EU', 2, '2025-07-07 08:02:50'),
	(3, '30', 'EU', 3, '2025-07-07 08:02:50'),
	(4, '31', 'EU', 4, '2025-07-07 08:02:50'),
	(5, '32', 'EU', 5, '2025-07-07 08:02:50'),
	(6, '33', 'EU', 6, '2025-07-07 08:02:50'),
	(7, '34', 'EU', 7, '2025-07-07 08:02:50'),
	(8, '35', 'EU', 8, '2025-07-07 08:02:50'),
	(9, '36', 'EU', 9, '2025-07-07 08:02:50'),
	(10, '36.5', 'EU', 10, '2025-07-07 08:02:50'),
	(11, '37', 'EU', 11, '2025-07-07 08:02:50'),
	(12, '37.5', 'EU', 12, '2025-07-07 08:02:50'),
	(13, '38', 'EU', 13, '2025-07-07 08:02:50'),
	(14, '38.5', 'EU', 14, '2025-07-07 08:02:50'),
	(15, '39', 'EU', 15, '2025-07-07 08:02:50'),
	(16, '40', 'EU', 16, '2025-07-07 08:02:50'),
	(17, '40.5', 'EU', 17, '2025-07-07 08:02:50'),
	(18, '41', 'EU', 18, '2025-07-07 08:02:50'),
	(19, '42', 'EU', 19, '2025-07-07 08:02:50'),
	(20, '42.5', 'EU', 20, '2025-07-07 08:02:50'),
	(21, '43', 'EU', 21, '2025-07-07 08:02:50'),
	(22, '44', 'EU', 22, '2025-07-07 08:02:50'),
	(23, '44.5', 'EU', 23, '2025-07-07 08:02:50'),
	(24, '45', 'EU', 24, '2025-07-07 08:02:50'),
	(25, '46', 'EU', 25, '2025-07-07 08:02:50'),
	(26, '47', 'EU', 26, '2025-07-07 08:02:50'),
	(27, '28', 'EU', 0, '2025-07-08 06:05:14');

-- tablo yapısı dökülüyor bandland_shoes.slider_items
CREATE TABLE IF NOT EXISTS `slider_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `bg_color` varchar(255) DEFAULT '#f0f0f0',
  `button_text` varchar(255) NOT NULL,
  `button_url` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.slider_items: ~2 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `slider_items` (`id`, `title`, `description`, `image_url`, `bg_color`, `button_text`, `button_url`, `is_active`, `sort_order`, `created_at`) VALUES
	(1, 'Yeni Sezon Sneakerlar', 'Şıklığı ve konforu bir araya getiren yeni sezon sneaker modellerimizle tanışın.', 'https://picsum.photos/seed/sneaker/1920/1080', '#f0f0f0', 'İncele', '/products?category=sneakers', 1, 0, '2025-07-12 00:14:38'),
	(2, 'Okula Dönüş Fırsatları', 'Okul hazırlıkları için en iyi ayakkabılar burada!', 'https://picsum.photos/seed/backtoschool/1920/1080', '#f0f0f0', 'Alışverişe Başla', '/products?tag=back-to-school', 1, 0, '2025-07-12 00:14:38');

-- tablo yapısı dökülüyor bandland_shoes.social_media_links
CREATE TABLE IF NOT EXISTS `social_media_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `icon_class` varchar(255) DEFAULT NULL,
  `order_index` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.social_media_links: ~5 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `social_media_links` (`id`, `platform`, `url`, `icon_class`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Facebook', 'https://facebook.com/yourpage', 'fab fa-facebook-f', 1, 1, '2025-07-05 18:02:32', '2025-07-05 18:02:32'),
	(2, 'Twitter', 'https://twitter.com/yourpage', 'fab fa-twitter', 2, 1, '2025-07-05 18:02:32', '2025-07-05 18:02:32'),
	(3, 'Instagram', 'https://instagram.com/yourpage', 'fab fa-instagram', 3, 1, '2025-07-05 18:02:32', '2025-07-05 18:02:32'),
	(4, 'LinkedIn', 'https://linkedin.com/company/yourpage', 'fab fa-linkedin-in', 4, 1, '2025-07-05 18:02:32', '2025-07-05 18:02:32'),
	(5, 'YouTube', 'https://youtube.com/yourchannel', 'fab fa-youtube', 5, 1, '2025-07-05 18:02:32', '2025-07-05 18:02:32');

-- tablo yapısı dökülüyor bandland_shoes.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` char(36) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `first_name` text DEFAULT NULL,
  `last_name` text DEFAULT NULL,
  `phone_number` text DEFAULT NULL,
  `gender` enum('Kadın','Erkek','Belirtmek İstemiyorum') DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.users: ~1 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `users` (`id`, `email`, `first_name`, `last_name`, `phone_number`, `gender`, `password_hash`, `created_at`, `updated_at`) VALUES
	('46db66ad-b3af-4463-8a3d-dc37a9befb13', 'burakfehimsalis@gmail.com', 'Burak Fehim', 'Şalış', '+905384135272', 'Erkek', '$2y$10$GX.it9xBBPRNxxF3EIBhv.AwrwMdnwBosm/MRnyUYUrF1XJloqyP6', '2025-07-11 22:24:28', '2025-07-11 23:24:28');

-- tablo yapısı dökülüyor bandland_shoes.user_sessions
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` char(36) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- bandland_shoes.user_sessions: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- Geçici tablolar temizlenerek final VIEW oluşturuluyor
DROP TABLE IF EXISTS `category_product_counts`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `category_product_counts` AS SELECT c.id AS category_id, c.name AS category_name, c.slug AS category_slug, COUNT(pc.product_id) AS product_count FROM categories c LEFT JOIN product_categories pc ON c.id = pc.category_id GROUP BY c.id, c.name, c.slug 
;

-- Geçici tablolar temizlenerek final VIEW oluşturuluyor
DROP TABLE IF EXISTS `favorites_view`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `favorites_view` AS SELECT f.id AS favorite_id, f.user_id, f.variant_id, f.color_id AS favorite_color_id, f.created_at AS favorite_added_at, pv.id AS pv_id, pv.model_id, pv.size_id, pv.color_id, pv.stock_quantity, pm.id AS product_id, pm.name AS product_name, pm.description AS product_description, cl.name AS color_name, cl.hex_code AS color_hex, s.size_value, (SELECT pi.image_url FROM product_images pi WHERE (pi.model_id = pv.model_id) AND (pi.color_id = IFNULL(f.color_id, pv.color_id)) ORDER BY pi.sort_order LIMIT 1) AS variant_image_url, (SELECT pi.image_url FROM product_images pi WHERE pi.model_id = pv.model_id ORDER BY pi.sort_order LIMIT 1) AS product_image_url FROM favorites f JOIN product_variants pv ON (f.variant_id = pv.id) JOIN product_models pm ON (pv.model_id = pm.id) LEFT JOIN colors cl ON (IFNULL(f.color_id, pv.color_id) = cl.id) LEFT JOIN sizes s ON (pv.size_id = s.id) 
;

-- Geçici tablolar temizlenerek final VIEW oluşturuluyor
DROP TABLE IF EXISTS `gender_product_counts`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `gender_product_counts` AS SELECT
                    g.id,
                    g.name,
                    g.slug,
                    COUNT(pg.product_id) AS product_count
                FROM genders g
                LEFT JOIN product_genders pg ON g.id = pg.gender_id
                GROUP BY g.id, g.name, g.slug
                ORDER BY g.name 
;

-- Geçici tablolar temizlenerek final VIEW oluşturuluyor
DROP TABLE IF EXISTS `product_api_summary`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `product_api_summary` AS SELECT pm.id, pm.name, pm.description, pm.is_featured, pm.created_at, pm.updated_at, (SELECT pi.image_url FROM product_images pi WHERE pi.model_id = pm.id AND pi.is_primary = 1 LIMIT 1) AS primary_image, (SELECT c.name FROM product_categories pc JOIN categories c ON pc.category_id = c.id WHERE pc.product_id = pm.id LIMIT 1) AS primary_category, (SELECT MIN(pv.price) FROM product_variants pv WHERE pv.model_id = pm.id) AS min_price, (SELECT MAX(pv.price) FROM product_variants pv WHERE pv.model_id = pm.id) AS max_price, (SELECT SUM(pv.stock_quantity) FROM product_variants pv WHERE pv.model_id = pm.id) AS total_stock FROM product_models pm 
;

-- Geçici tablolar temizlenerek final VIEW oluşturuluyor
DROP TABLE IF EXISTS `product_details_view`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `product_details_view` AS SELECT pm.id, pm.name, pm.description, pm.features, pm.is_featured, pm.created_at, pm.updated_at, (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', c.id, 'name', c.name, 'slug', c.slug, 'description', c.description, 'category_type', c.category_type)) FROM product_categories pc JOIN categories c ON pc.category_id = c.id WHERE pc.product_id = pm.id) AS categories, (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', g.id, 'name', g.name, 'slug', g.slug, 'description', g.description)) FROM product_genders pg JOIN genders g ON pg.gender_id = g.id WHERE pg.product_id = pm.id) AS genders, (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', pv.id, 'color_id', pv.color_id, 'size_id', pv.size_id, 'sku', pv.sku, 'price', pv.price, 'stock_quantity', pv.stock_quantity, 'is_active', pv.is_active)) FROM product_variants pv WHERE pv.model_id = pm.id) AS variants, (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', pi.id, 'color_id', pi.color_id, 'image_url', pi.image_url, 'alt_text', pi.alt_text, 'is_primary', pi.is_primary, 'sort_order', pi.sort_order)) FROM product_images pi WHERE pi.model_id = pm.id) AS images FROM product_models pm 
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
