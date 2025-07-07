# ProductService Fragmentasyon Dokümantasyonu

Bu klasör, `ProductService.php` dosyasının güvenli fragmentasyonu sonucu oluşturulan servislerini içerir.

## 📁 Servis Yapısı

### 1. ProductQueryService.php
**Sorumluluk:** Temel ürün sorgu işlemleri
- `getProductModel()` - Tek ürün getirme
- `getProductVariants()` - Ürün varyantları
- `getProductImages()` - Ürün görselleri  
- `getProductBasicInfo()` - Hafif ürün bilgisi
- `getProductModelsByIds()` - Çoklu ürün getirme

**Kullanım:**
```php
$queryService = new ProductQueryService();
$product = $queryService->getProductModel(123);
```

### 2. ProductFilterService.php
**Sorumluluk:** Filtreleme ve kategori/cinsiyet sorgulamaları
- `getCategoryIdsBySlugs()` - Kategori slug → ID dönüşümü
- `getProductIdsByCategories()` - Kategoriye göre ürün ID'leri
- `getFilteredProductIds()` - Genel filtreleme
- `getCategoryBySlug()` - Kategori bilgisi
- `getActiveCategories()` - Aktif kategoriler

**Kullanım:**
```php
$filterService = new ProductFilterService();
$productIds = $filterService->getFilteredProductIds(['categories' => ['ayakkabi']]);
```

### 3. ProductApiService.php
**Sorumluluk:** Frontend API işlemleri
- `getProductsForApi()` - Ana API endpoint'i
- `getProductModelsWithMultiCategory()` - Çoklu kategori desteği
- `getTotalProductCount()` - Ürün sayısı
- `getPopularProducts()` - Popüler ürünler
- `getSimilarProducts()` - Benzer ürünler

**Kullanım:**
```php
$apiService = new ProductApiService();
$result = $apiService->getProductsForApi([
    'page' => 1,
    'limit' => 12,
    'categories' => ['spor-ayakkabi'],
    'sort' => 'price-asc'
]);
```

### 4. ProductAdminService.php
**Sorumluluk:** Admin panel özel işlemleri
- `getAdminProducts()` - Admin ürün listesi
- `getProductStats()` - İstatistikler
- `getCategoryProductCounts()` - Kategori istatistikleri
- `getRecentProducts()` - Son ürünler
- `getProductDetailForAdmin()` - Admin detay sayfası
- `getSearchSuggestions()` - Arama önerileri

**Kullanım:**
```php
$adminService = new ProductAdminService();
$products = $adminService->getAdminProducts(20, 0, ['search' => 'nike']);
$stats = $adminService->getProductStats();
```

### 5. ProductManagementService.php
**Sorumluluk:** CRUD işlemleri ve ürün yönetimi
- `createProduct()` - Ürün oluşturma
- `updateProduct()` - Ürün güncelleme
- `deleteProduct()` - Cascade delete
- `updateProductStatus()` - Durum güncelleme
- `addProductVariant()` - Varyant ekleme
- `deleteMultipleProducts()` - Toplu silme

**Kullanım:**
```php
$managementService = new ProductManagementService();
$productId = $managementService->createProduct([
    'name' => 'Yeni Ürün',
    'base_price' => 299.99,
    'category_ids' => [1, 2]
]);
```

## 🔄 Ana Koordinatör: ProductService.php

Ana `ProductService` sınıfı artık bir koordinatör olarak çalışır:

```php
class ProductService {
    private $queryService;
    private $filterService;
    private $apiService;
    private $adminService;
    private $managementService;
    
    public function getProductModel($id) {
        return $this->queryService->getProductModel($id);
    }
    
    public function getProductsForApi($params) {
        return $this->apiService->getProductsForApi($params);
    }
    
    // ... diğer yönlendirmeler
}
```

## 🔒 Geriye Uyumluluk

Tüm eski global fonksiyonlar çalışmaya devam eder:

```php
// Bu fonksiyonlar hala çalışıyor
$products = get_product_models(10, 0, 'spor-ayakkabi');
$product = get_product_model(123);
$variants = get_product_variants(123);
$images = get_product_images(123);
```

## 🏗️ Singleton Pattern

Her servis için singleton fonksiyonları mevcuttur:

```php
$queryService = product_query_service();
$filterService = product_filter_service();
$apiService = product_api_service();
$adminService = product_admin_service();
$managementService = product_management_service();
```

## 🔄 Database Abstraction

Tüm yeni servisler `database()` factory'sini kullanır:
- Hem Supabase hem MariaDB desteği
- Otomatik failover
- Performans optimizasyonu

## 📊 Avantajlar

### 1. **Modülerlik**
- Her servis tek bir sorumluluğa sahip
- Bağımsız test edilebilir
- Kolay bakım

### 2. **Performans**
- Sadece gerekli servisler yüklenir
- Daha az memory kullanımı
- Hızlı kod analizi

### 3. **Güvenlik**
- Daha kolay kod review
- Sınırlı yetki alanları
- Isolation

### 4. **Geliştirilme Kolaylığı**
- Yeni özellikler doğru servise eklenir
- Merge conflict'leri azalır
- Pair programming kolaylaşır

## 🚀 Gelecek Planları

### Kısa Vadeli
- Unit test'ler yazılacak
- Interface'ler eklenecek
- Dependency injection

### Uzun Vadeli
- Event-driven architecture
- Caching layer
- GraphQL API desteği

## ⚠️ Önemli Notlar

1. **Eski Supabase kodları:** Ana ProductService'te hala var, aşamalı temizlenecek
2. **Transaction desteği:** ManagementService'te mevcut
3. **Error handling:** Tüm servislerde standart
4. **Logging:** Her serviste detaylı hata logları

## 🔧 Kullanım Örnekleri

### Frontend API Çağrısı
```php
$apiService = product_api_service();
$result = $apiService->getProductsForApi([
    'page' => $_GET['page'] ?? 1,
    'categories' => $_GET['categories'] ?? [],
    'sort' => $_GET['sort'] ?? 'newest'
]);
```

### Admin Panel Listesi
```php
$adminService = product_admin_service();
$products = $adminService->getAdminProducts(20, 0, [
    'search' => $_POST['search'] ?? '',
    'category_id' => $_POST['category'] ?? null
]);
```

### Ürün Oluşturma
```php
$managementService = product_management_service();
$productId = $managementService->createProduct([
    'name' => $_POST['name'],
    'base_price' => $_POST['price'],
    'category_ids' => $_POST['categories'],
    'images' => $_FILES['images']
]);
```

Bu fragmentasyon ile kodun sürdürülebilirliği ve performansı önemli ölçüde artmıştır.
