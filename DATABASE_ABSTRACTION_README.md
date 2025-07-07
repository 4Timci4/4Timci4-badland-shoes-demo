# Database Abstraction Layer - Kullanım Rehberi

Bu rehber, Bandland Shoes projesinde Supabase ve MariaDB/MySQL arasında geçiş yapmanızı sağlayan Database Abstraction Layer'ın kullanımını açıklar.

## 🚀 Hızlı Başlangıç

### 1. Database Türünü Seçin

`.env` dosyanızda `DB_TYPE` değerini ayarlayın:

```env
# Supabase kullanmak için
DB_TYPE=supabase

# MariaDB/MySQL kullanmak için  
DB_TYPE=mariadb
```

### 2. Bağlantı Bilgilerini Ayarlayın

#### Supabase için:
```env
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-or-service-role-key
```

#### MariaDB/MySQL için:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=bandland_shoes
DB_USER=root
DB_PASS=your_password
```

### 3. MariaDB Kurulumu (İsteğe Bağlı)

MariaDB kullanacaksanız, veritabanını oluşturun:

```bash
mysql -u root -p < database/mariadb_migration.sql
```

## 📝 Kullanım Örnekleri

### Mevcut Kod (Değişiklik Gerekmez)

Mevcut kodlarınız çalışmaya devam edecek:

```php
// Eski kullanım - hala çalışır
$products = supabase()->request('product_models?select=*');

// Yeni kullanım - aynı sonuç
$products = database()->select('product_models');
```

### Yeni Interface Metodları

#### 1. Basit Veri Seçme
```php
// Tüm ürünleri getir
$products = database()->select('product_models');

// Belirli koşullarla
$products = database()->select('product_models', [
    'is_featured' => true,
    'base_price' => ['>', 100]
]);

// Belirli sütunları seç
$products = database()->select('product_models', [], ['id', 'name', 'base_price']);
```

#### 2. Veri Ekleme
```php
$newProduct = database()->insert('product_models', [
    'name' => 'Yeni Ürün',
    'description' => 'Ürün açıklaması',
    'base_price' => 299.99,
    'is_featured' => false
], ['returning' => true]);
```

#### 3. Veri Güncelleme
```php
$updated = database()->update('product_models', 
    ['base_price' => 249.99], 
    ['id' => 1],
    ['returning' => true]
);
```

#### 4. Veri Silme
```php
$deleted = database()->delete('product_models', 
    ['id' => 1],
    ['returning' => true]
);
```

#### 5. Sayfalama
```php
$result = database()->paginate('product_models', 
    ['is_featured' => true], 
    1, 10, 
    ['order' => 'created_at DESC']
);
// $result['data'] - veriler
// $result['total'] - toplam kayıt
// $result['pages'] - toplam sayfa
```

#### 6. JOIN İşlemleri
```php
// MariaDB için
$result = database()->selectWithJoins('product_models', [
    [
        'type' => 'LEFT',
        'table' => 'categories', 
        'condition' => 'product_models.category_id = categories.id'
    ]
], [], 'product_models.*, categories.name as category_name');

// Supabase için (nested select)
$result = database()->selectWithJoins('product_models', [
    [
        'table' => 'categories',
        'select' => 'name,slug'
    ]
]);
```

#### 7. Transaction Kullanımı (Sadece MariaDB)
```php
try {
    database()->beginTransaction();
    
    $product = database()->insert('product_models', $productData);
    $variant = database()->insert('product_variants', $variantData);
    
    database()->commit();
} catch (Exception $e) {
    database()->rollback();
    throw $e;
}
```

#### 8. Ham SQL
```php
// Her iki database için çalışır
$result = database()->executeRawSql('SELECT COUNT(*) as total FROM product_models WHERE base_price > ?', [100]);
```

## 🔄 Database Değişimi

### Supabase'den MariaDB'ye Geçiş

1. `.env` dosyasında `DB_TYPE=mariadb` yapın
2. MariaDB bağlantı bilgilerini ekleyin
3. Migration script'ini çalıştırın
4. Veri aktarımı yapın (isteğe bağlı)

### MariaDB'den Supabase'e Geçiş

1. `.env` dosyasında `DB_TYPE=supabase` yapın
2. Supabase bağlantı bilgilerini ekleyin
3. Veri aktarımı yapın (isteğe bağlı)

## 🛠️ Gelişmiş Kullanım

### Özel Database Client Oluşturma

```php
// Özel yapılandırmayla client oluştur
$customDb = DatabaseFactory::create('mariadb', [
    'host' => 'custom-host',
    'database' => 'custom-db',
    'username' => 'custom-user',
    'password' => 'custom-pass'
]);
```

### Cache Kontrolü

```php
// Cache'i devre dışı bırak
$result = database()->select('product_models', [], '*', ['no_cache' => true]);

// Cache'i temizle
database()->clearCache();
```

### Hata Yönetimi

```php
$db = database();

try {
    $result = $db->select('product_models');
} catch (Exception $e) {
    $error = $db->getLastError();
    error_log("Database hatası: " . $error);
}
```

## 📊 Performans İpuçları

### Supabase için:
- Nested select kullanın: `select=*,categories(name)`
- Pagination için limit/offset kullanın
- Cache'den yararlanın

### MariaDB için:
- INDEX'leri kullanın
- EXPLAIN ile sorguları optimize edin
- Transaction'ları büyük işlemler için kullanın
- JOIN'ları verimli kullanın

## 🔍 Troubleshooting

### Yaygın Hatalar

1. **Bağlantı Hatası**: 
   - `.env` dosyasını kontrol edin
   - Database servisinin çalıştığından emin olun

2. **Tablo Bulunamadı**:
   - Migration script'ini çalıştırdığınızdan emin olun
   - Tablo isimlerinin doğru olduğunu kontrol edin

3. **Syntax Hatası**:
   - Database türünüze uygun syntax kullandığınızdan emin olun
   - Interface metodlarını kullanmayı tercih edin

### Debug

```php
// Mevcut database türünü öğren
echo "Mevcut DB: " . DatabaseFactory::getCurrentType();

// Bağlantı durumunu kontrol et
if (database()->isConnected()) {
    echo "Bağlantı başarılı";
} else {
    echo "Bağlantı hatası: " . database()->getLastError();
}
```

## 📚 Daha Fazla Bilgi

- `lib/DatabaseInterface.php` - Tam interface dokümantasyonu
- `lib/adapters/SupabaseAdapter.php` - Supabase implementasyonu
- `lib/clients/MariaDBClient.php` - MariaDB implementasyonu
- `database/mariadb_migration.sql` - Veritabanı şeması

## 🤝 Katkıda Bulunma

Yeni database türü eklemek için:

1. `DatabaseInterface` implement eden yeni client oluşturun
2. `DatabaseFactory`'ye yeni türü ekleyin
3. Migration script'i oluşturun
4. Test edin

Bu abstraction layer sayesinde projeniz database bağımsız hale geldi ve gelecekte farklı database sistemlerine kolayca geçiş yapabilirsiniz!
