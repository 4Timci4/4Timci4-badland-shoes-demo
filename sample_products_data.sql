-- ÖRNEK ÜRÜN VERİLERİ
-- products.php sayfasındaki ürünleri veritabanına aktarım

-- Önce ürün modellerini ekleyelim
INSERT INTO product_models (name, category_id, description, base_price, is_featured) VALUES
('Air Max 270', 1, 'Nike Air Max 270 tarzında spor ayakkabı, günlük kullanım için ideal', 299.00, true),
('Ultraboost 22', 1, 'Adidas Ultraboost tarzında koşu ayakkabısı, maksimum konfor', 399.00, true),
('RS-X Classic', 1, 'Puma RS-X tarzında retro spor ayakkabı', 189.00, false),
('All Star Canvas', 2, 'Converse All Star tarzında canvas ayakkabı', 159.00, false),
('Old Skool Skate', 2, 'Vans Old Skool tarzında skate ayakkabısı', 179.00, false),
('Classic Leather', 2, 'Reebok Classic tarzında deri ayakkabı', 149.00, false),
('990v5 Premium', 1, 'New Balance 990v5 tarzında premium koşu ayakkabısı', 499.00, true),
('Air Jordan 1', 1, 'Nike Air Jordan 1 tarzında basketbol ayakkabısı', 359.00, true),
('Stan Smith', 3, 'Adidas Stan Smith tarzında klasik beyaz ayakkabı', 219.00, false);

-- Her model için farklı renk varyantları ve bedenler oluşturalım
-- Model 1: Air Max 270 (Siyah, Beyaz, Mavi)
INSERT INTO product_variants (model_id, color_id, size_id, sku, price, stock_quantity) 
SELECT 
    1 as model_id, 
    c.id as color_id, 
    s.id as size_id,
    CONCAT('AIR-MAX-270-', c.name, '-', s.size_value) as sku,
    299.00 as price,
    FLOOR(RANDOM() * 50 + 10) as stock_quantity
FROM colors c 
CROSS JOIN sizes s 
WHERE c.name IN ('Siyah', 'Beyaz', 'Mavi');

-- Model 2: Ultraboost 22 (Siyah, Beyaz, Gri)
INSERT INTO product_variants (model_id, color_id, size_id, sku, price, stock_quantity) 
SELECT 
    2 as model_id, 
    c.id as color_id, 
    s.id as size_id,
    CONCAT('ULTRABOOST-22-', c.name, '-', s.size_value) as sku,
    399.00 as price,
    FLOOR(RANDOM() * 50 + 10) as stock_quantity
FROM colors c 
CROSS JOIN sizes s 
WHERE c.name IN ('Siyah', 'Beyaz', 'Gri');

-- Model 3: RS-X Classic (Kırmızı, Beyaz, Mavi) - İndirimli
INSERT INTO product_variants (model_id, color_id, size_id, sku, price, original_price, stock_quantity) 
SELECT 
    3 as model_id, 
    c.id as color_id, 
    s.id as size_id,
    CONCAT('RSX-CLASSIC-', c.name, '-', s.size_value) as sku,
    189.00 as price,
    235.00 as original_price,
    FLOOR(RANDOM() * 50 + 10) as stock_quantity
FROM colors c 
CROSS JOIN sizes s 
WHERE c.name IN ('Kırmızı', 'Beyaz', 'Mavi');

-- Model 4: All Star Canvas (Siyah, Beyaz, Kırmızı)
INSERT INTO product_variants (model_id, color_id, size_id, sku, price, stock_quantity) 
SELECT 
    4 as model_id, 
    c.id as color_id, 
    s.id as size_id,
    CONCAT('ALL-STAR-', c.name, '-', s.size_value) as sku,
    159.00 as price,
    FLOOR(RANDOM() * 50 + 10) as stock_quantity
FROM colors c 
CROSS JOIN sizes s 
WHERE c.name IN ('Siyah', 'Beyaz', 'Kırmızı');

-- Model 5: Old Skool Skate (Siyah, Beyaz, Mavi)
INSERT INTO product_variants (model_id, color_id, size_id, sku, price, stock_quantity) 
SELECT 
    5 as model_id, 
    c.id as color_id, 
    s.id as size_id,
    CONCAT('OLD-SKOOL-', c.name, '-', s.size_value) as sku,
    179.00 as price,
    FLOOR(RANDOM() * 50 + 10) as stock_quantity
FROM colors c 
CROSS JOIN sizes s 
WHERE c.name IN ('Siyah', 'Beyaz', 'Mavi');

-- Model 6: Classic Leather (Beyaz, Gri, Kahverengi) - İndirimli
INSERT INTO product_variants (model_id, color_id, size_id, sku, price, original_price, stock_quantity) 
SELECT 
    6 as model_id, 
    c.id as color_id, 
    s.id as size_id,
    CONCAT('CLASSIC-LEATHER-', c.name, '-', s.size_value) as sku,
    149.00 as price,
    199.00 as original_price,
    FLOOR(RANDOM() * 50 + 10) as stock_quantity
FROM colors c 
CROSS JOIN sizes s 
WHERE c.name IN ('Beyaz', 'Gri', 'Kahverengi');

-- Model 7: 990v5 Premium (Gri, Siyah, Beyaz)
INSERT INTO product_variants (model_id, color_id, size_id, sku, price, stock_quantity) 
SELECT 
    7 as model_id, 
    c.id as color_id, 
    s.id as size_id,
    CONCAT('990V5-PREMIUM-', c.name, '-', s.size_value) as sku,
    499.00 as price,
    FLOOR(RANDOM() * 50 + 10) as stock_quantity
FROM colors c 
CROSS JOIN sizes s 
WHERE c.name IN ('Gri', 'Siyah', 'Beyaz');

-- Model 8: Air Jordan 1 (Siyah, Kırmızı, Beyaz)
INSERT INTO product_variants (model_id, color_id, size_id, sku, price, stock_quantity) 
SELECT 
    8 as model_id, 
    c.id as color_id, 
    s.id as size_id,
    CONCAT('AIR-JORDAN-1-', c.name, '-', s.size_value) as sku,
    359.00 as price,
    FLOOR(RANDOM() * 50 + 10) as stock_quantity
FROM colors c 
CROSS JOIN sizes s 
WHERE c.name IN ('Siyah', 'Kırmızı', 'Beyaz');

-- Model 9: Stan Smith (Beyaz, Yeşil)
INSERT INTO product_variants (model_id, color_id, size_id, sku, price, stock_quantity) 
SELECT 
    9 as model_id, 
    c.id as color_id, 
    s.id as size_id,
    CONCAT('STAN-SMITH-', c.name, '-', s.size_value) as sku,
    219.00 as price,
    FLOOR(RANDOM() * 50 + 10) as stock_quantity
FROM colors c 
CROSS JOIN sizes s 
WHERE c.name IN ('Beyaz', 'Yeşil');

-- Ürün görselleri ekleyelim (her model için ana renk görseli)
INSERT INTO product_images (model_id, color_id, image_url, alt_text, is_primary, sort_order) VALUES
(1, 1, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', 'Air Max 270 Siyah', true, 1),
(1, 2, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', 'Air Max 270 Beyaz', false, 2),
(2, 1, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', 'Ultraboost 22 Siyah', true, 1),
(3, 4, 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', 'RS-X Classic Kırmızı', true, 1),
(4, 1, 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', 'All Star Canvas Siyah', true, 1),
(5, 1, 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', 'Old Skool Skate Siyah', true, 1),
(6, 2, 'https://images.unsplash.com/photo-1562183241-b937e95585b6?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', 'Classic Leather Beyaz', true, 1),
(7, 5, 'https://images.unsplash.com/photo-1605348532760-6753d2c43329?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', '990v5 Premium Gri', true, 1),
(8, 1, 'https://images.unsplash.com/photo-1584464491033-06628f3a6b7b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', 'Air Jordan 1 Siyah', true, 1),
(9, 2, 'https://images.unsplash.com/photo-1551107696-a4b57ec336b7?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=60', 'Stan Smith Beyaz', true, 1);