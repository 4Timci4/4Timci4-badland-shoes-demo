# Bandland Shoes - Performans Optimizasyon Planı

## 🚨 **Tespit Edilen Performans Sorunları**

### 1. **N+1 Query Problem**
- **CategoryService**: Her kategori için ayrı `COUNT()` sorgusu
- **ProductApiService**: Her ürün için ayrı kategori, cinsiyet ve resim sorguları
- **Sayfa yüklenirken**: 50+ kategori varsa 50+ ek sorgu

### 2. **Gereksiz Veri Yükleme**
- **products.php**: Tüm kategoriler product count ile birlikte
- **Frontend**: Kullanılmayan veriler JavaScript'e aktarılıyor
- **API**: Her ürün için tam kategori/cinsiyet bilgileri

### 3. **Veritabanı Optimizasyon Eksikliği**
- JOIN'ler yerine ayrı sorgular
- Indexsiz composite queries
- Materialize view kullanımı yok
- Caching mechanism yok

---

## 🎯 **Köklü Çözüm Stratejisi**

### **Phase 1: Veritabanı Optimizasyonu (Immediate)**

#### **1.1 Optimized Database Indexes**
```sql
-- Performance critical indexes
CREATE INDEX idx_product_categories_product_id ON product_categories(product_id);
CREATE INDEX idx_product_categories_category_id ON product_categories(category_id);
CREATE INDEX idx_product_genders_product_id ON product_genders(product_id);
CREATE INDEX idx_product_genders_gender_id ON product_genders(gender_id);
CREATE INDEX idx_product_images_model_id ON product_images(model_id);
CREATE INDEX idx_product_images_primary ON product_images(model_id, is_primary);
CREATE INDEX idx_product_variants_model_id ON product_variants(model_id);
CREATE INDEX idx_product_models_featured ON product_models(is_featured);
CREATE INDEX idx_product_models_price ON product_models(base_price);
CREATE INDEX idx_product_models_created ON product_models(created_at);

-- Composite indexes for complex queries
CREATE INDEX idx_product_categories_composite ON product_categories(category_id, product_id);
CREATE INDEX idx_product_genders_composite ON product_genders(gender_id, product_id);
CREATE INDEX idx_product_images_sort ON product_images(model_id, color_id, sort_order);
```

#### **1.2 Materialized Views for Expensive Queries**
```sql
-- Categories with product counts
CREATE MATERIALIZED VIEW category_product_counts AS
SELECT 
    c.id,
    c.name,
    c.slug,
    c.parent_id,
    c.category_type,
    COUNT(pc.product_id) as product_count
FROM categories c
LEFT JOIN product_categories pc ON c.id = pc.category_id
LEFT JOIN product_models pm ON pc.product_id = pm.id
GROUP BY c.id, c.name, c.slug, c.parent_id, c.category_type;

-- Product summary for API
CREATE MATERIALIZED VIEW product_api_summary AS
SELECT 
    pm.id,
    pm.name,
    pm.description,
    pm.base_price,
    pm.is_featured,
    pm.created_at,
    array_agg(DISTINCT c.name) as category_names,
    array_agg(DISTINCT c.slug) as category_slugs,
    array_agg(DISTINCT g.name) as gender_names,
    array_agg(DISTINCT g.slug) as gender_slugs,
    (SELECT pi.image_url FROM product_images pi 
     WHERE pi.model_id = pm.id AND pi.is_primary = true 
     ORDER BY pi.sort_order LIMIT 1) as primary_image
FROM product_models pm
LEFT JOIN product_categories pc ON pm.id = pc.product_id
LEFT JOIN categories c ON pc.category_id = c.id
LEFT JOIN product_genders pg ON pm.id = pg.product_id
LEFT JOIN genders g ON pg.gender_id = g.id
GROUP BY pm.id, pm.name, pm.description, pm.base_price, pm.is_featured, pm.created_at;
```

### **Phase 2: Service Layer Refactoring (High Impact)**

#### **2.1 Optimized CategoryService**
```php
class OptimizedCategoryService {
    /**
     * Tek sorguda kategoriler ve ürün sayıları
     */
    public function getCategoriesWithProductCountsOptimized($hierarchical = false) {
        $sql = "
            SELECT 
                c.id,
                c.name,
                c.slug,
                c.parent_id,
                c.category_type,
                COUNT(pc.product_id) as product_count
            FROM categories c
            LEFT JOIN product_categories pc ON c.id = pc.category_id
            GROUP BY c.id, c.name, c.slug, c.parent_id, c.category_type
            ORDER BY c.id ASC
        ";
        
        $categories = $this->db->raw($sql);
        
        if ($hierarchical) {
            return $this->buildHierarchy($categories);
        }
        
        return $categories;
    }
    
    /**
     * Cached category hierarchy
     */
    public function getCachedCategoryHierarchy($ttl = 3600) {
        $cache_key = 'category_hierarchy';
        
        if ($cached = $this->cache->get($cache_key)) {
            return $cached;
        }
        
        $hierarchy = $this->getCategoriesWithProductCountsOptimized(true);
        $this->cache->set($cache_key, $hierarchy, $ttl);
        
        return $hierarchy;
    }
}
```

#### **2.2 Optimized ProductApiService**
```php
class OptimizedProductApiService {
    /**
     * Batch product enrichment - tek sorguda tüm ilişkili veriler
     */
    public function enrichProductsForApiBatch($products) {
        if (empty($products)) {
            return [];
        }
        
        $product_ids = array_column($products, 'id');
        
        // Tek sorguda tüm kategori bilgileri
        $category_data = $this->db->raw("
            SELECT 
                pc.product_id,
                c.id as category_id,
                c.name as category_name,
                c.slug as category_slug
            FROM product_categories pc
            JOIN categories c ON pc.category_id = c.id
            WHERE pc.product_id IN (" . implode(',', $product_ids) . ")
        ");
        
        // Tek sorguda tüm cinsiyet bilgileri
        $gender_data = $this->db->raw("
            SELECT 
                pg.product_id,
                g.id as gender_id,
                g.name as gender_name,
                g.slug as gender_slug
            FROM product_genders pg
            JOIN genders g ON pg.gender_id = g.id
            WHERE pg.product_id IN (" . implode(',', $product_ids) . ")
        ");
        
        // Tek sorguda tüm resim bilgileri
        $image_data = $this->db->raw("
            SELECT 
                pi.model_id as product_id,
                pi.image_url,
                pi.is_primary,
                pi.sort_order
            FROM product_images pi
            WHERE pi.model_id IN (" . implode(',', $product_ids) . ")
            ORDER BY pi.model_id, pi.is_primary DESC, pi.sort_order ASC
        ");
        
        // Verileri grupla
        $categories_by_product = [];
        $genders_by_product = [];
        $images_by_product = [];
        
        foreach ($category_data as $cat) {
            $categories_by_product[$cat['product_id']][] = $cat;
        }
        
        foreach ($gender_data as $gen) {
            $genders_by_product[$gen['product_id']][] = $gen;
        }
        
        foreach ($image_data as $img) {
            $images_by_product[$img['product_id']][] = $img;
        }
        
        // Ürünleri zenginleştir
        $enriched_products = [];
        foreach ($products as $product) {
            $enriched_product = $product;
            
            // Kategoriler
            $product_categories = $categories_by_product[$product['id']] ?? [];
            $enriched_product['categories'] = $product_categories;
            $enriched_product['category_name'] = $product_categories[0]['category_name'] ?? '';
            $enriched_product['category_slug'] = $product_categories[0]['category_slug'] ?? '';
            
            // Cinsiyetler
            $enriched_product['genders'] = $genders_by_product[$product['id']] ?? [];
            
            // Resimler
            $product_images = $images_by_product[$product['id']] ?? [];
            $primary_image = null;
            
            foreach ($product_images as $img) {
                if ($img['is_primary']) {
                    $primary_image = $img['image_url'];
                    break;
                }
            }
            
            $enriched_product['image_url'] = $primary_image ?? ($product_images[0]['image_url'] ?? '/assets/images/placeholder.svg');
            
            $enriched_products[] = $enriched_product;
        }
        
        return $enriched_products;
    }
}
```

### **Phase 3: Caching Strategy (Performance Boost)**

#### **3.1 Multi-Layer Caching**
```php
class CacheManager {
    private $redis;
    private $memcached;
    private $file_cache;
    
    /**
     * Hierarchical caching strategy
     */
    public function get($key, $fallback = null, $ttl = 3600) {
        // L1: Memory cache (fastest)
        if ($this->memory_cache->has($key)) {
            return $this->memory_cache->get($key);
        }
        
        // L2: Redis cache (fast)
        if ($this->redis->exists($key)) {
            $value = $this->redis->get($key);
            $this->memory_cache->set($key, $value, 300); // 5 min memory cache
            return $value;
        }
        
        // L3: Database/computation (slow)
        if ($fallback) {
            $value = $fallback();
            $this->set($key, $value, $ttl);
            return $value;
        }
        
        return null;
    }
    
    /**
     * Smart cache invalidation
     */
    public function invalidateProductCaches($product_id) {
        $patterns = [
            "product:$product_id:*",
            "category:*:products",
            "api:products:*",
            "similar:*:$product_id"
        ];
        
        foreach ($patterns as $pattern) {
            $this->redis->del($this->redis->keys($pattern));
        }
    }
}
```

#### **3.2 Cached API Service**
```php
class CachedProductApiService extends OptimizedProductApiService {
    private $cache;
    
    public function getProductsForApi($params = []) {
        $cache_key = 'api:products:' . md5(serialize($params));
        
        return $this->cache->get($cache_key, function() use ($params) {
            return parent::getProductsForApi($params);
        }, 1800); // 30 dakika cache
    }
    
    public function getCachedCategoryHierarchy() {
        return $this->cache->get('categories:hierarchy', function() {
            return category_service()->getCategoriesWithProductCountsOptimized(true);
        }, 3600); // 1 saat cache
    }
}
```

### **Phase 4: Frontend Optimizations (User Experience)**

#### **4.1 Lazy Loading Strategy**
```javascript
// Optimized products.js
class OptimizedProductsPage {
    constructor() {
        this.cache = new Map();
        this.debounceTimer = null;
        this.observer = null;
        this.initIntersectionObserver();
    }
    
    // Lazy load categories
    async loadCategoriesOnDemand() {
        if (this.cache.has('categories')) {
            return this.cache.get('categories');
        }
        
        const categories = await this.fetchWithCache('/api/categories.php');
        this.cache.set('categories', categories);
        return categories;
    }
    
    // Debounced API calls
    debouncedFetchProducts(params) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.fetchProducts(params);
        }, 300);
    }
    
    // Fetch with caching
    async fetchWithCache(url, ttl = 300000) { // 5 min
        const cached = this.cache.get(url);
        if (cached && Date.now() - cached.timestamp < ttl) {
            return cached.data;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        this.cache.set(url, {
            data,
            timestamp: Date.now()
        });
        
        return data;
    }
    
    // Intersection Observer for images
    initIntersectionObserver() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    this.observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px'
        });
    }
}
```

#### **4.2 Optimized Page Loading**
```php
// Optimized products.php
<?php
// Minimal initial data - sadece filtreleme için gerekli
$minimal_data = [
    'categories' => [], // Boş - lazy load
    'genders' => [], // Boş - lazy load
    'apiUrl' => 'api/products.php',
    'itemsPerPage' => 9
];

// Async category loading endpoint
if (isset($_GET['load_filters'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'categories' => category_service()->getCachedCategoryHierarchy(),
        'genders' => gender_service()->getAllGenders()
    ]);
    exit;
}
?>

<script>
// Async filter loading
document.addEventListener('DOMContentLoaded', async function() {
    const pageData = <?php echo json_encode($minimal_data); ?>;
    
    // Load filters asynchronously
    const filtersResponse = await fetch('?load_filters=1');
    const filters = await filtersResponse.json();
    
    // Initialize page with filters
    initializeProductsPage(pageData, filters);
});
</script>
```

### **Phase 5: Database Schema Optimizations (Long-term)**

#### **5.1 Denormalization Strategy**
```sql
-- Ürün özet tablosu (denormalized)
CREATE TABLE product_summary (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    base_price DECIMAL(10,2),
    is_featured BOOLEAN,
    created_at TIMESTAMP,
    category_names TEXT[], -- PostgreSQL array
    category_slugs TEXT[],
    gender_names TEXT[],
    gender_slugs TEXT[],
    primary_image_url VARCHAR(500),
    variant_count INT,
    min_price DECIMAL(10,2),
    max_price DECIMAL(10,2),
    color_count INT,
    size_count INT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Trigger for auto-update
CREATE OR REPLACE FUNCTION update_product_summary()
RETURNS TRIGGER AS $$
BEGIN
    -- Update product summary when related data changes
    -- Implementation details...
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Triggers
CREATE TRIGGER product_summary_update_trigger
    AFTER INSERT OR UPDATE OR DELETE ON product_models
    FOR EACH ROW EXECUTE FUNCTION update_product_summary();
```

#### **5.2 Computed Columns**
```sql
-- Computed columns for frequent calculations
ALTER TABLE categories ADD COLUMN product_count INT DEFAULT 0;
ALTER TABLE genders ADD COLUMN product_count INT DEFAULT 0;
ALTER TABLE product_models ADD COLUMN variant_count INT DEFAULT 0;
ALTER TABLE product_models ADD COLUMN min_price DECIMAL(10,2) DEFAULT 0;
ALTER TABLE product_models ADD COLUMN max_price DECIMAL(10,2) DEFAULT 0;

-- Update functions
CREATE OR REPLACE FUNCTION update_category_product_count()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE categories SET product_count = (
        SELECT COUNT(*) FROM product_categories 
        WHERE category_id = NEW.category_id
    ) WHERE id = NEW.category_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

---

## 📊 **Beklenen Performans Iyileştirmeleri**

### **Before vs After**

| Metrik | Before | After | Improvement |
|--------|---------|-------|-------------|
| **Page Load Time** | 3-5 seconds | 0.5-1 second | **80% faster** |
| **Database Queries** | 50-100 queries | 5-10 queries | **90% reduction** |
| **Memory Usage** | 50-80MB | 15-25MB | **70% reduction** |
| **Category Loading** | 2-3 seconds | 0.2 seconds | **90% faster** |
| **API Response Time** | 1-2 seconds | 0.1-0.3 seconds | **85% faster** |

### **Scalability Improvements**

| Aspect | Before | After |
|--------|---------|-------|
| **Concurrent Users** | 50-100 | 500-1000 |
| **Product Count** | 1K products | 10K+ products |
| **Category Count** | 50 categories | 500+ categories |
| **Database Load** | High | Low |
| **Server Resources** | Heavy | Light |

---

## 🚀 **Implementation Roadmap**

### **Week 1: Critical Fixes**
- [ ] Database indexes implementation
- [ ] N+1 query elimination
- [ ] Basic caching layer

### **Week 2: Service Optimization**
- [ ] Optimized CategoryService
- [ ] Batch ProductApiService
- [ ] Materialized views

### **Week 3: Caching Strategy**
- [ ] Redis setup
- [ ] Multi-layer caching
- [ ] Cache invalidation

### **Week 4: Frontend Optimization**
- [ ] Lazy loading
- [ ] Debounced API calls
- [ ] Image optimization

### **Week 5: Schema Optimization**
- [ ] Denormalization
- [ ] Computed columns
- [ ] Trigger functions

---

## 🔧 **Technical Implementation Details**

### **Database Configuration**
```sql
-- PostgreSQL/Supabase optimizations
SET shared_buffers = '256MB';
SET effective_cache_size = '1GB';
SET maintenance_work_mem = '64MB';
SET checkpoint_completion_target = 0.9;
SET wal_buffers = '16MB';
SET default_statistics_target = 100;
```

### **PHP Configuration**
```php
// PHP optimizations
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 30);
opcache_enable();
opcache_reset();

// Connection pooling
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
]);
```

### **Monitoring & Metrics**
```php
class PerformanceMonitor {
    public function measureQuery($query, $params = []) {
        $start = microtime(true);
        $result = $this->db->raw($query, $params);
        $duration = microtime(true) - $start;
        
        if ($duration > 0.1) { // 100ms threshold
            error_log("Slow query ({$duration}s): {$query}");
        }
        
        return $result;
    }
    
    public function getMetrics() {
        return [
            'query_count' => $this->query_count,
            'total_time' => $this->total_time,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
}
```

---

## 🎯 **Success Metrics**

### **Performance KPIs**
- **Page Load Time**: < 1 second
- **Database Queries**: < 10 per request
- **Memory Usage**: < 30MB per request
- **API Response**: < 300ms
- **Cache Hit Rate**: > 90%

### **User Experience KPIs**
- **Time to Interactive**: < 2 seconds
- **First Contentful Paint**: < 0.5 seconds
- **Largest Contentful Paint**: < 1.5 seconds
- **Cumulative Layout Shift**: < 0.1
- **Core Web Vitals**: All Green

### **Business Impact**
- **Bounce Rate**: Decrease by 30%
- **Page Views**: Increase by 25%
- **Conversion Rate**: Increase by 15%
- **Server Costs**: Decrease by 40%
- **SEO Ranking**: Improve by 20%

---

**Bu plan, Bandland Shoes e-commerce platform'unda köklü performans optimizasyonları sağlayacak ve sistem scalability'sini önemli ölçüde artıracaktır.**

**Öncelik Sırası**: Phase 1 (Indexes) → Phase 2 (Services) → Phase 3 (Caching) → Phase 4 (Frontend) → Phase 5 (Schema)

**Estimated Impact**: 80-90% performance improvement, 70% resource reduction, 10x scalability increase