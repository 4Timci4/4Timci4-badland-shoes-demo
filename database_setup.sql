-- AYAKKABI SİTESİ VERİTABANI YAPISI
-- (Tek marka için basitleştirilmiş yapı)

-- 1. Kategoriler Tablosu
CREATE TABLE categories (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. Ürün Modelleri Tablosu (brand_id olmadan)
CREATE TABLE product_models (
  id SERIAL PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  category_id INTEGER REFERENCES categories(id),
  description TEXT,
  base_price DECIMAL(10,2) NOT NULL,
  is_featured BOOLEAN DEFAULT false,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 3. Renkler Tablosu
CREATE TABLE colors (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  hex_code VARCHAR(7),
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 4. Bedenler Tablosu
CREATE TABLE sizes (
  id SERIAL PRIMARY KEY,
  size_value VARCHAR(10) NOT NULL,
  size_type VARCHAR(20) DEFAULT 'EU', -- EU, US, UK
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 5. Ürün Varyantları Tablosu (renk + beden kombinasyonu)
CREATE TABLE product_variants (
  id SERIAL PRIMARY KEY,
  model_id INTEGER REFERENCES product_models(id),
  color_id INTEGER REFERENCES colors(id),
  size_id INTEGER REFERENCES sizes(id),
  sku VARCHAR(100) UNIQUE NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  original_price DECIMAL(10,2),
  stock_quantity INTEGER DEFAULT 0,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  UNIQUE(model_id, color_id, size_id)
);

-- 6. Ürün Görselleri Tablosu
CREATE TABLE product_images (
  id SERIAL PRIMARY KEY,
  model_id INTEGER REFERENCES product_models(id),
  color_id INTEGER REFERENCES colors(id),
  image_url TEXT NOT NULL,
  alt_text VARCHAR(200),
  is_primary BOOLEAN DEFAULT false,
  sort_order INTEGER DEFAULT 0,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Trigger fonksiyonu (updated_at otomatik güncelleme için)
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggerlar
CREATE TRIGGER update_product_models_updated_at 
  BEFORE UPDATE ON product_models 
  FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_product_variants_updated_at 
  BEFORE UPDATE ON product_variants 
  FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- İndeksler (performans için)
CREATE INDEX idx_product_variants_model_id ON product_variants(model_id);
CREATE INDEX idx_product_variants_color_id ON product_variants(color_id);
CREATE INDEX idx_product_variants_size_id ON product_variants(size_id);
CREATE INDEX idx_product_variants_active ON product_variants(is_active);
CREATE INDEX idx_product_images_model_id ON product_images(model_id);
CREATE INDEX idx_product_images_color_id ON product_images(color_id);
CREATE INDEX idx_product_models_category_id ON product_models(category_id);
CREATE INDEX idx_product_models_featured ON product_models(is_featured);

-- Örnek veriler
INSERT INTO categories (name, slug, description) VALUES
('Spor Ayakkabılar', 'spor', 'Koşu, fitness ve günlük spor aktiviteleri için'),
('Casual Ayakkabılar', 'casual', 'Günlük kullanım için rahat ayakkabılar'),
('Klasik Ayakkabılar', 'klasik', 'Resmi ve şık kullanım için'),
('Bot ve Çizme', 'bot', 'Kış ve outdoor aktiviteler için'),
('Sandalet', 'sandalet', 'Yaz ayları için');

INSERT INTO colors (name, hex_code) VALUES
('Siyah', '#000000'),
('Beyaz', '#FFFFFF'),
('Mavi', '#0066CC'),
('Kırmızı', '#CC0000'),
('Gri', '#808080'),
('Kahverengi', '#8B4513'),
('Yeşil', '#228B22'),
('Turuncu', '#FF8C00'),
('Mor', '#800080'),
('Pembe', '#FF69B4');

INSERT INTO sizes (size_value, size_type) VALUES
('36', 'EU'), ('37', 'EU'), ('38', 'EU'), ('39', 'EU'), ('40', 'EU'),
('41', 'EU'), ('42', 'EU'), ('43', 'EU'), ('44', 'EU'), ('45', 'EU'),
('46', 'EU'), ('47', 'EU');