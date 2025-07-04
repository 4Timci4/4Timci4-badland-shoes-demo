<?php
/**
 * Veritabanı Bağlantı Konfigürasyonu
 * 
 * Bu dosya Supabase bağlantı bilgilerini içerir.
 */

// Supabase Bağlantı Bilgileri
define('SUPABASE_URL', 'https://rfxleyiyvpygdpdbnmib.supabase.co/');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJmeGxleWl5dnB5Z2RwZGJubWliIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTE0NTQ1ODYsImV4cCI6MjA2NzAzMDU4Nn0.ze5ip-K5ZwYpajdasSSGQayPUiFJILvkX9LJVrKsu08');

/**
 * Supabase'e HTTP isteği gönderen yardımcı fonksiyon
 *
 * Not: Bu fonksiyon şu anda demo verileri döndürür.
 * Gerçek bir uygulamada, curl veya file_get_contents ile HTTP istekleri yapılır.
 *
 * @param string $endpoint API endpoint'i
 * @param string $method HTTP metodu (GET, POST, PATCH, DELETE)
 * @param array $data İstek gövdesi (opsiyonel)
 * @return array|null Yanıt verisi veya hata durumunda null
 */
function supabase_request($endpoint, $method = 'GET', $data = null) {
    // cURL kütüphanesinin yüklü olup olmadığını kontrol et
    if (!function_exists('curl_init')) {
        error_log('Supabase API Error: cURL library is not installed.');
        return [];
    }

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

    if ($method !== 'GET' && $data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    error_log('Supabase API Request (cURL): ' . $method . ' ' . $url);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    if ($curl_error) {
        error_log('Supabase cURL Error: ' . $curl_error);
        return [];
    }

    if ($http_code >= 200 && $http_code < 300) {
        $result = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }
        error_log('Supabase API Error: Invalid JSON response. Body: ' . $response);
        return [];
    }
    
    error_log('Supabase API Error: HTTP ' . $http_code . ' - ' . $response);
    return [];
}

/**
 * Ürün modellerini getiren fonksiyon
 * 
 * @param int $limit Maksimum ürün sayısı
 * @param int $offset Başlangıç indeksi
 * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
 * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
 * @param string|null $sort Sıralama seçeneği (opsiyonel)
 * @return array Ürün modelleri
 */
function get_product_models($limit = 10, $offset = 0, $category_slugs = null, $featured = null, $sort = null) {
    $query = [
        'select' => '*',
        'limit' => $limit,
        'offset' => $offset,
    ];

    if ($sort) {
        $sort_params = explode('-', $sort);
        $column = $sort_params[0];
        $direction = count($sort_params) > 1 ? $sort_params[1] : 'asc';
        $query['order'] = $column . '.' . $direction;
    }
    
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
    return supabase_request('/rest/v1/product_models?' . $queryString);
}

/**
 * Belirli bir ürün modelini ID'ye göre getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array|null Ürün modeli veya bulunamazsa null
 */
function get_product_model($model_id) {
    return supabase_request('/rest/v1/product_models?id=eq.' . $model_id . '&limit=1');
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
    $result = supabase_request('/rest/v1/product_models?' . $queryString);
    
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
    $categories = get_categories();
    $category_counts = [];
    
    foreach ($categories as $category) {
        $query = [
            'select' => 'count',
            'category_id' => 'eq.' . $category['id']
        ];
        
        $queryString = http_build_query($query);
        $result = supabase_request('/rest/v1/product_models?' . $queryString);
        
        if (is_array($result) && isset($result[0]['count'])) {
            $category_counts[$category['slug']] = (int)$result[0]['count'];
        } else {
            $category_counts[$category['slug']] = 0;
        }
    }
    
    return $category_counts;
}