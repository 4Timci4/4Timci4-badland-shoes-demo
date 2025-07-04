<?php
/**
 * Veritabanı Bağlantı Konfigürasyonu
 * 
 * Bu dosya Supabase bağlantı bilgilerini içerir.
 */

// Supabase Bağlantı Bilgileri
// Vercel ortam değişkenlerinden veya yerel geliştirme için doğrudan tanımlanır
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://rfxleyiyvpygdpdbnmib.supabase.co/');
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJmeGxleWl5dnB5Z2RwZGJubWliIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTE0NTQ1ODYsImV4cCI6MjA2NzAzMDU4Nn0.ze5ip-K5ZwYpajdasSSGQayPUiFJILvkX9LJVrKsu08');

/**
 * Supabase'e HTTP isteği gönderen yardımcı fonksiyon
 *
 * @param string $endpoint API endpoint'i
 * @param string $method HTTP metodu (GET, POST, PATCH, DELETE)
 * @param array $data İstek gövdesi (opsiyonel)
 * @return array|null Yanıt verisi veya hata durumunda null
 */
function supabase_request($endpoint, $method = 'GET', $data = null) {
    $url = rtrim(SUPABASE_URL, '/') . '/' . ltrim($endpoint, '/');
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    // SSL doğrulamasını devre dışı bırak (geliştirme ortamı için)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if ($method !== 'GET' && $data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    if ($curl_error) {
        error_log("Supabase cURL Error: $curl_error");
        return [];
    }

    if ($http_code >= 200 && $http_code < 300) {
        $result = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }
        error_log("Supabase API Error: Invalid JSON response. Body: $response");
        return [];
    }
    
    error_log("Supabase API Error: HTTP $http_code - $response");
    return [];
}

/**
 * Ürün modellerini getiren fonksiyon - Düzeltilmiş basit SQL sorgusu ile
 * 
 * @param int $limit Maksimum ürün sayısı
 * @param int $offset Başlangıç indeksi
 * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
 * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
 * @param string|null $sort Sıralama seçeneği (opsiyonel)
 * @return array Ürün modelleri
 */
function get_product_models($limit = 10, $offset = 0, $category_slugs = null, $featured = null, $sort = null) {
    error_log("get_product_models çağrıldı: limit=$limit, offset=$offset, category_slugs=" . json_encode($category_slugs));
    
    // SQL sorgusu oluştur
    $query = [
        'select' => 'pm.id,pm.name,pm.description,pm.base_price,pm.is_featured,c.name as category_name,c.slug as category_slug,pi.image_url',
        'from' => 'product_models.pm,categories.c,product_images.pi',
        'where' => 'pm.category_id=c.id and pi.model_id=pm.id and pi.is_primary=true',
        'order' => 'pm.id.asc',
        'limit' => $limit,
        'offset' => $offset
    ];
    
    // Kategori filtresi ekle
    if (!empty($category_slugs)) {
        if (is_array($category_slugs)) {
            $slugList = implode("','", array_map(function($slug) {
                return str_replace("'", "''", $slug); // SQL enjeksiyonuna karşı koruma
            }, $category_slugs));
            $query['where'] .= " and c.slug in ('$slugList')";
        } else {
            $safeSlug = str_replace("'", "''", $category_slugs); // SQL enjeksiyonuna karşı koruma
            $query['where'] .= " and c.slug = '$safeSlug'";
        }
    }
    
    // Öne çıkan filtresi ekle
    if ($featured !== null) {
        $featuredValue = $featured ? 'true' : 'false';
        $query['where'] .= " and pm.is_featured = $featuredValue";
    }
    
    // Manuel SQL sorgusu oluştur (Supabase URL yapısı çok karmaşık olduğu için)
    $sqlQuery = "
    SELECT
        pm.id, 
        pm.name, 
        pm.description, 
        pm.base_price,
        pm.is_featured,
        c.name as category_name,
        c.slug as category_slug,
        pi.image_url
    FROM 
        product_models pm
    JOIN 
        categories c ON pm.category_id = c.id
    LEFT JOIN 
        (SELECT DISTINCT ON (model_id) model_id, image_url FROM product_images WHERE is_primary = true) pi 
        ON pm.id = pi.model_id
    ";
    
    // Kategori filtresi ekle
    if (!empty($category_slugs)) {
        if (is_array($category_slugs)) {
            $placeholders = implode(',', array_fill(0, count($category_slugs), '?'));
            $sqlQuery .= " WHERE c.slug IN ($placeholders)";
        } else {
            $sqlQuery .= " WHERE c.slug = '$category_slugs'";
        }
    }
    
    // Sıralama
    $sqlQuery .= " ORDER BY pm.id ASC";
    
    // Sayfalama
    $sqlQuery .= " LIMIT $limit OFFSET $offset";
    
    error_log("SQL Sorgusu: $sqlQuery");
    
    // HTTP isteği yerine doğrudan PostgreSQL RPC çağrısı kullan
    $result = supabase_request('/rest/v1/rpc/execute_sql', 'POST', [
        'query' => $sqlQuery
    ]);
    
    // Hata kontrolü
    if (empty($result) || !isset($result[0]) || empty($result[0])) {
        error_log("Supabase RPC execute_sql sonucu boş döndü.");
        
        // Plan B: Temel SQL sorgusu
        $basic_query = [
            'select' => '*',
            'limit' => $limit,
            'offset' => $offset
        ];
        
        if (!empty($category_slugs)) {
            $category_query = [
                'select' => 'id',
                'slug' => is_array($category_slugs) ? 'in.(' . implode(',', $category_slugs) . ')' : 'eq.' . $category_slugs
            ];
            $category_result = supabase_request('/rest/v1/categories?' . http_build_query($category_query));
            
            if (!empty($category_result)) {
                $category_ids = array_column($category_result, 'id');
                $basic_query['category_id'] = 'in.(' . implode(',', $category_ids) . ')';
            }
        }
        
        $products = supabase_request('/rest/v1/product_models?' . http_build_query($basic_query));
        
        // Kategori ve görsel bilgilerini ekle
        foreach ($products as &$product) {
            // Kategori bilgisi
            $cat_query = [
                'select' => 'name,slug',
                'id' => 'eq.' . $product['category_id'],
                'limit' => 1
            ];
            $cat_result = supabase_request('/rest/v1/categories?' . http_build_query($cat_query));
            
            if (!empty($cat_result)) {
                $product['category_name'] = $cat_result[0]['name'];
                $product['category_slug'] = $cat_result[0]['slug'];
            }
            
            // Görsel bilgisi
            $img_query = [
                'select' => 'image_url',
                'model_id' => 'eq.' . $product['id'],
                'is_primary' => 'eq.true',
                'limit' => 1
            ];
            $img_result = supabase_request('/rest/v1/product_images?' . http_build_query($img_query));
            
            if (!empty($img_result)) {
                $product['image_url'] = $img_result[0]['image_url'];
            }
        }
        
        return $products;
    }
    
    return $result;
}

/**
 * Belirli bir ürün modelini ID'ye göre getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array|null Ürün modeli veya bulunamazsa null
 */
function get_product_model($model_id) {
    // RPC fonksiyonu yerine doğrudan SQL tabloları kullanılıyor
    $query = [
        'select' => 'id,name,description,features,base_price,is_featured,created_at,category_id',
        'id' => 'eq.' . $model_id,
        'limit' => 1
    ];
    
    $queryString = http_build_query($query);
    $product_result = supabase_request('/rest/v1/product_models?' . $queryString);
    
    error_log('Ürün sorgusu: /rest/v1/product_models?' . $queryString);
    error_log('Ürün sonucu: ' . json_encode($product_result));
    
    if (empty($product_result)) {
        return [];
    }
    
    // Kategori bilgisini al
    $category_id = isset($product_result[0]['category_id']) ? $product_result[0]['category_id'] : 0;
    $category_query = [
        'select' => 'name,slug',
        'id' => 'eq.' . $category_id,
        'limit' => 1
    ];
    
    $category_queryString = http_build_query($category_query);
    $category_result = supabase_request('/rest/v1/categories?' . $category_queryString);
    
    error_log('Kategori sorgusu: /rest/v1/categories?' . $category_queryString);
    error_log('Kategori sonucu: ' . json_encode($category_result));
    
    // Ürün görselini al
    $image_query = [
        'select' => 'image_url',
        'model_id' => 'eq.' . $model_id,
        'is_primary' => 'eq.true',
        'limit' => 1
    ];
    
    $image_queryString = http_build_query($image_query);
    $image_result = supabase_request('/rest/v1/product_images?' . $image_queryString);
    
    error_log('Görsel sorgusu: /rest/v1/product_images?' . $image_queryString);
    error_log('Görsel sonucu: ' . json_encode($image_result));
    
    // Ürün ve kategori verilerini birleştir
    $product = $product_result[0];
    $product['price'] = $product['base_price']; // Tutarlılık için
    
    if (!empty($category_result)) {
        $product['category_name'] = $category_result[0]['name'];
        $product['category_slug'] = $category_result[0]['slug'];
    }
    
    if (!empty($image_result)) {
        $product['image_url'] = $image_result[0]['image_url'];
    }
    
    return [$product];
}

/**
 * Bir ürün modeline ait varyantları getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array Ürün varyantları
 */
function get_product_variants($model_id) {
    return supabase_request('/rest/v1/product_variants?model_id=eq.' . $model_id);
}

/**
 * Bir ürün modeline ait görselleri getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array Ürün görselleri
 */
function get_product_images($model_id) {
    return supabase_request('/rest/v1/product_images?model_id=eq.' . $model_id);
}

/**
 * Tüm renkleri getiren fonksiyon
 * 
 * @return array Renkler
 */
function get_colors() {
    return supabase_request('/rest/v1/colors');
}

/**
 * Tüm bedenleri getiren fonksiyon
 * 
 * @return array Bedenler
 */
function get_sizes() {
    return supabase_request('/rest/v1/sizes');
}

/**
 * Tüm kategorileri getiren fonksiyon
 * 
 * @return array Kategoriler
 */
function get_categories() {
    return supabase_request('/rest/v1/categories');
}

/**
 * Toplam ürün sayısını getiren fonksiyon
 *
 * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
 * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
 * @return int Toplam ürün sayısı
 */
function get_total_product_count($category_slugs = null, $featured = null) {
    $query = [
        'select' => 'count',
    ];
    
    if (!empty($category_slugs)) {
        if (is_array($category_slugs)) {
            $query['category_slug'] = 'in.(' . implode(',', $category_slugs) . ')';
        } else {
            $query['category_slug'] = 'eq.' . $category_slugs;
        }
    }
    
    if ($featured !== null) {
        $query['is_featured'] = 'eq.' . ($featured ? 'true' : 'false');
    }
    
    $queryString = http_build_query($query);
    $result = supabase_request('/rest/v1/product_models_view?' . $queryString);
    
    if (is_array($result) && isset($result[0]['count'])) {
        return (int)$result[0]['count'];
    }
    
    return 0;
}

/**
 * Her kategori için ürün sayılarını getiren fonksiyon
 *
 * @return array Kategori slug'ı anahtar, ürün sayısı değer olan bir dizi
 */
function get_category_product_counts() {
    // Daha önce oluşturduğumuz veritabanı fonksiyonunu (RPC) çağır
    return supabase_request('/rest/v1/rpc/get_category_product_counts', 'POST');
}
