# Supabase Veritabanı Dokümantasyonu

## Genel Bakış

Bu proje, Supabase PostgreSQL veritabanı kullanarak modern bir e-ticaret sitesi geliştirmiştir. Veritabanı 14 tablo içermekte ve bu tablolar 3 ana kategoriye ayrılmaktadır:

- **E-Ticaret Tabloları**: Ürün, kategori, varyant yönetimi
- **İçerik Yönetimi Tabloları**: Blog, slider, hakkımızda içerikleri
- **İletişim Tabloları**: İletişim bilgileri ve mesajlar

## Veritabanı Mimarisi

### 1. E-TİCARET TABLOLARI

#### 1.1 categories (Kategoriler)
**Tablo Açıklaması**: Ürün kategorilerini saklar
**Satır Sayısı**: 6 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | integer | Primary Key | NOT NULL, AUTO_INCREMENT |
| name | varchar | Kategori adı | NOT NULL, UNIQUE |
| slug | varchar | URL dostu kategori adı | NOT NULL, UNIQUE |
| description | text | Kategori açıklaması | NULL |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |

**İlişkiler**:
- `product_models.category_id` → `categories.id` (1:N)

**Service Dosyası**: `services/CategoryService.php`

**Kullanılan Fonksiyonlar**:
```php
getCategories() // Tüm kategorileri getir
getCategoryBySlug($slug) // Slug'a göre kategori getir
createCategory($data) // Yeni kategori oluştur
updateCategory($category_id, $data) // Kategori güncelle
deleteCategory($category_id) // Kategori sil
getCategoryById($category_id) // ID'ye göre kategori getir
getCategoriesWithProductCounts() // Ürün sayılarıyla kategoriler
```

#### 1.2 product_models (Ürün Modelleri)
**Tablo Açıklaması**: Ana ürün bilgilerini saklar
**Satır Sayısı**: 11 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | integer | Primary Key | NOT NULL, AUTO_INCREMENT |
| name | varchar | Ürün adı | NOT NULL |
| category_id | integer | Kategori ID | FOREIGN KEY → categories.id |
| description | text | Ürün açıklaması | NULL |
| base_price | numeric | Temel fiyat | NOT NULL |
| is_featured | boolean | Öne çıkan ürün | DEFAULT: false |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |
| updated_at | timestamptz | Güncelleme tarihi | DEFAULT: now() |
| features | text | Ürün özellikleri | NULL |

**İlişkiler**:
- `product_models.category_id` → `categories.id`
- `product_variants.model_id` → `product_models.id` (1:N)
- `product_images.model_id` → `product_models.id` (1:N)

**Service Dosyası**: `services/ProductService.php`

**Kullanılan Fonksiyonlar**:
```php
getProductModels($limit, $offset, $category_slugs, $featured, $sort) // Ürün listesi
getProductModel($model_id) // Tek ürün getir
getProductVariants($model_id) // Ürün varyantları
getProductImages($model_id) // Ürün resimleri
getTotalProductCount($category_slugs, $featured) // Toplam ürün sayısı
getAdminProducts($limit, $offset, $search, $category_filter, $status_filter) // Admin ürün listesi
deleteProduct($product_id) // Ürün sil
updateProductStatus($product_id, $is_featured) // Ürün durumu güncelle
```

#### 1.3 colors (Renkler)
**Tablo Açıklaması**: Ürün renk seçeneklerini saklar
**Satır Sayısı**: 10 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | integer | Primary Key | NOT NULL, AUTO_INCREMENT |
| name | varchar | Renk adı | NOT NULL, UNIQUE |
| hex_code | varchar | Hex renk kodu | NULL |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |

**İlişkiler**:
- `product_variants.color_id` → `colors.id` (1:N)
- `product_images.color_id` → `colors.id` (1:N)

#### 1.4 sizes (Bedenler)
**Tablo Açıklaması**: Ürün beden seçeneklerini saklar
**Satır Sayısı**: 12 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | integer | Primary Key | NOT NULL, AUTO_INCREMENT |
| size_value | varchar | Beden değeri | NOT NULL |
| size_type | varchar | Beden tipi (EU, US, UK) | DEFAULT: 'EU' |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |

**İlişkiler**:
- `product_variants.size_id` → `sizes.id` (1:N)

#### 1.5 product_variants (Ürün Varyantları)
**Tablo Açıklaması**: Ürün renk/beden kombinasyonlarını ve stok bilgilerini saklar
**Satır Sayısı**: 316 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | integer | Primary Key | NOT NULL, AUTO_INCREMENT |
| model_id | integer | Ürün model ID | FOREIGN KEY → product_models.id |
| color_id | integer | Renk ID | FOREIGN KEY → colors.id |
| size_id | integer | Beden ID | FOREIGN KEY → sizes.id |
| sku | varchar | Stok kodu | NOT NULL, UNIQUE |
| price | numeric | Fiyat | NOT NULL |
| original_price | numeric | Orjinal fiyat | NULL |
| stock_quantity | integer | Stok miktarı | DEFAULT: 0 |
| is_active | boolean | Aktif durum | DEFAULT: true |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |
| updated_at | timestamptz | Güncelleme tarihi | DEFAULT: now() |

**Service Dosyası**: `services/VariantService.php`

**Kullanılan Fonksiyonlar**:
```php
getAllColors() // Tüm renkleri getir
getAllSizes() // Tüm bedenleri getir
getProductVariants($model_id) // Ürün varyantları (detaylı)
createVariant($data) // Yeni varyant oluştur
updateVariant($variant_id, $data) // Varyant güncelle
deleteVariant($variant_id) // Varyant sil
createBulkVariants($model_id, $variants) // Toplu varyant oluştur
updateStock($variant_id, $quantity) // Stok güncelle
getVariantById($variant_id) // Varyant detayları
getTotalStock($model_id) // Toplam stok
getProductColors($model_id) // Ürün renkleri
getProductSizes($model_id) // Ürün bedenleri
```

#### 1.6 product_images (Ürün Resimleri)
**Tablo Açıklaması**: Ürün resimlerini saklar
**Satır Sayısı**: 10 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | integer | Primary Key | NOT NULL, AUTO_INCREMENT |
| model_id | integer | Ürün model ID | FOREIGN KEY → product_models.id |
| color_id | integer | Renk ID | FOREIGN KEY → colors.id |
| image_url | text | Resim URL | NOT NULL |
| alt_text | varchar | Alt metin | NULL |
| is_primary | boolean | Ana resim | DEFAULT: false |
| sort_order | integer | Sıralama | DEFAULT: 0 |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |

### 2. İÇERİK YÖNETİMİ TABLOLARI

#### 2.1 blogs (Blog Yazıları)
**Tablo Açıklaması**: Blog yazılarını saklar
**Satır Sayısı**: 2 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | bigint | Primary Key | IDENTITY, NOT NULL |
| title | text | Yazı başlığı | NOT NULL |
| excerpt | text | Yazı özeti | NULL |
| content | text | Yazı içeriği | NULL |
| image_url | text | Yazı resmi | NULL |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |
| category | text | Kategori | NULL |
| tags | text[] | Etiketler | NULL |

**Service Dosyası**: `services/BlogService.php`

**Kullanılan Fonksiyonlar**:
```php
get_posts($page, $perPage, $category, $tag) // Sayfalı blog listesi
get_post_by_id($id) // Tek blog yazısı
get_related_posts($current_id, $category, $limit) // Benzer yazılar
getAllBlogs($limit) // Tüm bloglar (admin)
```

#### 2.2 about_settings (Hakkımızda Ayarları)
**Tablo Açıklaması**: Hakkımızda sayfası ayarlarını saklar
**Satır Sayısı**: 14 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | bigint | Primary Key | IDENTITY, NOT NULL |
| meta_key | text | Ayar anahtarı | NOT NULL, UNIQUE |
| meta_value | text | Ayar değeri | NULL |
| section | text | Bölüm | NULL |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |
| updated_at | timestamptz | Güncelleme tarihi | DEFAULT: now() |

#### 2.3 about_content_blocks (Hakkımızda İçerik Blokları)
**Tablo Açıklaması**: Hakkımızda sayfası dinamik içerik bloklarını saklar
**Satır Sayısı**: 8 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | bigint | Primary Key | IDENTITY, NOT NULL |
| section | text | Bölüm | NOT NULL |
| title | text | Başlık | NULL |
| subtitle | text | Alt başlık | NULL |
| content | text | İçerik | NULL |
| image_url | text | Resim URL | NULL |
| icon | text | İkon | NULL |
| sort_order | integer | Sıralama | DEFAULT: 0 |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |
| updated_at | timestamptz | Güncelleme tarihi | DEFAULT: now() |

**Service Dosyası**: `services/AboutService.php`

**Kullanılan Fonksiyonlar**:
```php
getAboutPageContent() // Hakkımızda sayfası içeriği
getHomePageAboutSection() // Anasayfa hakkımızda bölümü
updateSetting($meta_key, $meta_value, $section) // Ayar güncelle
updateMultipleSettings($settings) // Çoklu ayar güncelle
createContentBlock($data) // İçerik bloğu oluştur
updateContentBlock($id, $data) // İçerik bloğu güncelle
deleteContentBlock($id) // İçerik bloğu sil
getContentBlockById($id) // İçerik bloğu getir
updateContentBlockOrder($section, $orderData) // Sıralama güncelle
```

#### 2.4 slider_items (Slider Öğeleri)
**Tablo Açıklaması**: Anasayfa slider öğelerini saklar
**Satır Sayısı**: 3 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | bigint | Primary Key | IDENTITY, NOT NULL |
| title | text | Slider başlığı | NOT NULL |
| description | text | Slider açıklaması | NULL |
| image_url | text | Arka plan resmi | NULL |
| bg_color | varchar | Arka plan rengi | DEFAULT: '#f0f0f0' |
| button_text | varchar | Buton metni | NOT NULL |
| button_url | varchar | Buton URL | NOT NULL |
| is_active | boolean | Aktif durum | DEFAULT: true |
| sort_order | integer | Sıralama | DEFAULT: 0 |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |

**Service Dosyası**: `services/SliderService.php`

**Kullanılan Fonksiyonlar**:
```php
getActiveSliders() // Aktif sliderlar
getAllSliders() // Tüm sliderlar
getSliderById($id) // Slider detayı
createSlider($data) // Yeni slider
updateSlider($id, $data) // Slider güncelle
deleteSlider($id) // Slider sil
toggleSliderStatus($id) // Durum değiştir
updateSliderOrder($orderData) // Sıralama güncelle
```

#### 2.5 seasonal_collections (Sezonluk Koleksiyonlar)
**Tablo Açıklaması**: Anasayfa sezonluk koleksiyonlar bölümü
**Satır Sayısı**: 2 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | bigint | Primary Key | AUTO_INCREMENT |
| title | text | Koleksiyon başlığı | NOT NULL |
| description | text | Koleksiyon açıklaması | NULL |
| image_url | text | Koleksiyon resmi | NULL |
| button_url | text | Buton linki | NULL |
| sort_order | integer | Sıralama | DEFAULT: 0 |
| layout_type | varchar | Düzen tipi | DEFAULT: 'left', CHECK: 'left' OR 'right' |
| created_at | timestamptz | Oluşturma tarihi | DEFAULT: now() |
| updated_at | timestamptz | Güncelleme tarihi | DEFAULT: now() |

**Service Dosyası**: `services/SeasonalCollectionsService.php`

**Kullanılan Fonksiyonlar**:
```php
getActiveCollections() // Aktif koleksiyonlar
getCollectionById($id) // Koleksiyon detayı
```

### 3. İLETİŞİM TABLOLARI

#### 3.1 contact_info (İletişim Bilgileri)
**Tablo Açıklaması**: İletişim sayfası bilgilerini saklar
**Satır Sayısı**: 17 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | integer | Primary Key | NOT NULL, AUTO_INCREMENT |
| section | varchar | Bölüm | NOT NULL |
| field | varchar | Alan | NOT NULL |
| value | text | Değer | NOT NULL |
| created_at | timestamp | Oluşturma tarihi | DEFAULT: now() |
| updated_at | timestamp | Güncelleme tarihi | DEFAULT: now() |

#### 3.2 social_media_links (Sosyal Medya Linkleri)
**Tablo Açıklaması**: Sosyal medya linklerini saklar
**Satır Sayısı**: 5 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | integer | Primary Key | NOT NULL, AUTO_INCREMENT |
| platform | varchar | Platform adı | NOT NULL |
| url | text | Link URL | NOT NULL |
| icon_class | varchar | İkon sınıfı | NULL |
| order_index | integer | Sıralama | DEFAULT: 0 |
| is_active | boolean | Aktif durum | DEFAULT: true |
| created_at | timestamp | Oluşturma tarihi | DEFAULT: now() |
| updated_at | timestamp | Güncelleme tarihi | DEFAULT: now() |

#### 3.3 contact_messages (İletişim Mesajları)
**Tablo Açıklaması**: İletişim formu mesajlarını saklar
**Satır Sayısı**: 0 satır

| Sütun | Veri Tipi | Açıklama | Kısıtlamalar |
|-------|-----------|----------|-------------|
| id | integer | Primary Key | NOT NULL, AUTO_INCREMENT |
| name | varchar | Gönderen adı | NOT NULL |
| email | varchar | E-posta | NOT NULL |
| subject | varchar | Konu | NOT NULL |
| message | text | Mesaj | NOT NULL |
| created_at | timestamp | Oluşturma tarihi | DEFAULT: now() |

**Service Dosyası**: `services/ContactService.php`

**Kullanılan Fonksiyonlar**:
```php
getContactInfo() // İletişim bilgileri
getSocialMediaLinks() // Sosyal medya linkleri
submitContactForm($formData) // İletişim formu gönder
```

## Supabase Bağlantı Mimarisi

### SupabaseClient.php
Ana bağlantı sınıfı şu özelliklere sahiptir:

```php
class SupabaseClient {
    private $baseUrl;           // Supabase API URL
    private $apiKey;            // API anahtarı
    private $requestTimeout;    // İstek timeout (30s)
    private $connectTimeout;    // Bağlantı timeout (10s)
    private $useCache;          // Önbellek kullanımı
    private $cacheExpiry;       // Önbellek süresi (300s)
    private $cacheDir;          // Önbellek dizini
}
```

**Ana Metodlar**:
- `request($endpoint, $method, $data, $headers)` - HTTP isteği gönder
- `executeRawSql($sql, $params)` - SQL sorgusu çalıştır
- `prepareSql($sql, $params)` - SQL sorgusu hazırla
- `getCache($key)` - Önbellekten al
- `setCache($key, $data)` - Önbelleğe kaydet
- `clearCache($key)` - Önbelleği temizle

### Service Pattern
Her tablo için ayrı service sınıfı:

```php
// Örnek kullanım
$productService = new ProductService();
$products = $productService->getProductModels(10, 0);

// Singleton kullanımı
$products = product_service()->getProductModels(10, 0);
```

## Veri İlişkileri Şeması

```
categories (1) ←→ (N) product_models
    ↓
product_models (1) ←→ (N) product_variants
    ↓                        ↓
product_images (N) ←→ (1) colors
    ↓                        ↓
sizes (1) ←→ (N) product_variants
```

## Performans Optimizasyonları

### 1. Önbellek Sistemi
- GET istekleri için otomatik önbellek
- 5 dakikalık önbellek süresi
- Sistem temp dizininde dosya tabanlı önbellek

### 2. SQL Optimizasyonları
- JOIN kullanımı ile tek sorguda ilişkili veriler
- DISTINCT ON kullanımı
- Index kullanımı (id, slug, is_active)

### 3. Fallback Mekanizması
- API başarısız olursa alternatif yöntemler
- Dummy data ile graceful degradation
- Kapsamlı error handling

## Güvenlik Önlemleri

### 1. SQL Injection Koruması
- Parametreli sorgular
- prepareSql metodu ile güvenli SQL hazırlama
- Input sanitization

### 2. API Güvenliği
- Bearer token authentication
- SSL/TLS zorunluluğu (production'da)
- Rate limiting (Supabase seviyesinde)

### 3. Error Handling
- Detaylı error logging
- Kullanıcıya güvenli error mesajları
- Exception handling

## Geliştirme Önerileri

### 1. Eksik Özellikler
- Ürün yorumları tablosu
- Sipariş yönetimi tabloları
- Kullanıcı yönetimi tabloları
- Favori ürünler tablosu

### 2. Performans İyileştirmeleri
- Database indexing
- Query optimization
- Lazy loading
- Pagination optimization

### 3. Monitoring ve Analytics
- Query performance tracking
- Error monitoring
- User behavior analytics
- Database usage metrics

## API Endpoint Örnekleri

### Ürün Listeleme
```
GET /rest/v1/product_models?select=*,categories(name,slug)&limit=20
```

### Ürün Varyantları
```
GET /rest/v1/product_variants?model_id=eq.1&select=*,colors(name,hex_code),sizes(size_value)
```

### Blog Yazıları
```
GET /rest/v1/blogs?select=*&order=created_at.desc&limit=10
```

### Aktif Sliderlar
```
GET /rest/v1/slider_items?select=*&is_active=eq.true&order=sort_order.asc
```

## Veritabanı Backup ve Maintenance

### Backup Stratejisi
- Günlük otomatik backup (Supabase)
- Point-in-time recovery
- Cross-region replication

### Maintenance
- Düzenli VACUUM operasyonları
- Index maintenance
- Query performance monitoring
- Dead tuple cleanup

Bu dokümantasyon, Supabase veritabanının kapsamlı bir rehberi olarak gelecekteki geliştirmelere yardımcı olacaktır.
