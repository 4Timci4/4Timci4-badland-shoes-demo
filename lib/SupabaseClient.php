<?php
/**
 * Supabase İstemci Sınıfı
 * 
 * Bu dosya Supabase API ile iletişim kurmak için gerekli sınıfı içerir.
 */

// Ortam değişkenleri gerekli
require_once __DIR__ . '/../config/env.php';

/**
 * Supabase ile iletişim için sınıf
 * 
 * Bu sınıf, Supabase API ile iletişim kurmak için gereken metodları sağlar.
 */
class SupabaseClient {
    private $baseUrl;
    private $apiKey;
    private $requestTimeout = 30;
    private $connectTimeout = 10;
    private $useCache = true;
    private $cacheExpiry = 1; // 5 dakika
    private $cacheDir;
    
    /**
     * SupabaseClient sınıfını başlatır
     * 
     * @param string $baseUrl Supabase API URL'i
     * @param string $apiKey Supabase API anahtarı
     * @param array $options Ek yapılandırma seçenekleri
     */
    public function __construct($baseUrl, $apiKey, $options = []) {
        $this->baseUrl = rtrim($baseUrl, '/') . '/rest/v1';
        $this->apiKey = $apiKey;
        
        // Opsiyonel yapılandırma seçeneklerini ayarla
        if (isset($options['requestTimeout'])) {
            $this->requestTimeout = $options['requestTimeout'];
        }
        
        if (isset($options['connectTimeout'])) {
            $this->connectTimeout = $options['connectTimeout'];
        }
        
        if (isset($options['useCache'])) {
            $this->useCache = $options['useCache'];
        }
        
        if (isset($options['cacheExpiry'])) {
            $this->cacheExpiry = $options['cacheExpiry'];
        }
        
        // Önbellek dizinini ayarla
        $this->cacheDir = sys_get_temp_dir() . '/supabase_cache';
        if (!file_exists($this->cacheDir) && $this->useCache) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Supabase'e HTTP isteği gönderen metod
     *
     * @param string $endpoint API endpoint'i
     * @param string $method HTTP metodu (GET, POST, PATCH, DELETE)
     * @param array|null $data İstek gövdesi (opsiyonel)
     * @param bool $useCache Önbellek kullanılsın mı? (GET istekleri için)
     * @return array Yanıt verisi veya hata durumunda boş dizi
     * @throws Exception İstek başarısız olduğunda
     */
    public function request($endpoint, $method = 'GET', $data = null, $headers = [], $useCache = true) {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $cacheKey = null;
        $useCache = $this->useCache && $useCache && !isset($headers['Prefer']); // Prefer header varsa cache kullanma

        // GET istekleri için önbelleği kontrol et
        if ($method === 'GET' && $useCache) {
            $cacheKey = md5($url . json_encode($data) . json_encode($headers));
            $cachedData = $this->getCache($cacheKey);
            if ($cachedData !== null) {
                return $cachedData;
            }
        }

        $defaultHeaders = [
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        // Gelen özel başlıkları varsayılanlarla birleştir
        $finalHeaders = array_merge($defaultHeaders, $headers);
        // Prefer başlığını özel olarak ele al
        if (isset($headers['Prefer'])) {
            foreach ($finalHeaders as $key => $value) {
                if (strpos($value, 'Prefer:') === 0) {
                    unset($finalHeaders[$key]);
                }
            }
            $finalHeaders[] = 'Prefer: ' . $headers['Prefer'];
        }
        $finalHeaders = array_values(array_unique($finalHeaders));


        $ch = curl_init();
        $responseHeaders = [];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $finalHeaders);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // Boş veya geçersiz başlık
                return $len;

            $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);
            return $len;
        });
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        
        // SSL doğrulamasını sadece geliştirme ortamında devre dışı bırak
        if (defined('APP_ENV') && APP_ENV === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        if ($method !== 'GET' && $data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);

        curl_close($ch);

        if ($curl_error) {
            error_log("Supabase cURL Error: $curl_error");
            throw new Exception("Supabase cURL Error: $curl_error", 500);
        }

        if ($http_code >= 200 && $http_code < 300) {
            $body = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Supabase API Error: Invalid JSON response. Body: $response");
                throw new Exception("Supabase API Error: Invalid JSON response", 500);
            }
            
            $result = [
                'body' => $body,
                'headers' => $responseHeaders
            ];

            // GET istekleri için önbelleğe al
            if ($method === 'GET' && $useCache && $cacheKey) {
                $this->setCache($cacheKey, $result);
            }
            return $result;
        }
        
        $error_message = "Supabase API Error: HTTP $http_code";
        $decoded_response = json_decode($response, true);
        
        // Hata mesajını daha güvenli bir şekilde işle
        if ($decoded_response) {
            // Gelen JSON yanıtını (array veya object) güvenli bir şekilde string'e çevir
            $error_details = json_encode($decoded_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            $error_message .= " - Details: " . $error_details;
        } elseif (!empty($response)) {
            // JSON değilse, ham yanıtı ekle
            $error_message .= " - Response: " . $response;
        }

        error_log($error_message);
        throw new Exception($error_message, $http_code);
    }
    
    /**
     * Önbellekten veri çeker
     * 
     * @param string $key Önbellek anahtarı
     * @return array|null Önbellekteki veri veya null
     */
    private function getCache($key) {
        if (!$this->useCache) {
            return null;
        }
        
        $cacheFile = $this->cacheDir . '/' . $key;
        
        if (file_exists($cacheFile)) {
            $cachedData = file_get_contents($cacheFile);
            $data = unserialize($cachedData);
            
            // Süre dolmuş mu kontrol et
            if (time() - $data['time'] < $this->cacheExpiry) {
                return $data['data'];
            }
            
            // Süre dolmuşsa dosyayı sil
            unlink($cacheFile);
        }
        
        return null;
    }
    
    /**
     * Veriyi önbelleğe kaydeder
     * 
     * @param string $key Önbellek anahtarı
     * @param array $data Kaydedilecek veri
     */
    private function setCache($key, $data) {
        if (!$this->useCache) {
            return;
        }
        
        $cacheFile = $this->cacheDir . '/' . $key;
        $cacheData = [
            'time' => time(),
            'data' => $data
        ];
        
        file_put_contents($cacheFile, serialize($cacheData));
    }
    
    /**
     * Önbelleği temizler
     * 
     * @param string|null $key Belirli bir anahtarı temizle (null ise tüm önbelleği temizle)
     */
    public function clearCache($key = null) {
        if (!$this->useCache) {
            return;
        }
        
        if ($key) {
            $cacheFile = $this->cacheDir . '/' . md5($key);
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
            return;
        }
        
        // Tüm önbelleği temizle
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * SQL sorgusunun güvenli bir şekilde çalıştırılması için hazırlar
     * 
     * @param string $sql SQL sorgusu
     * @param array $params Sorgu parametreleri
     * @return string Hazırlanmış SQL sorgusu
     */
    public function prepareSql($sql, $params = []) {
        foreach ($params as $key => $value) {
            $placeholder = is_numeric($key) ? '?' : ':' . $key;
            
            // Değeri uygun şekilde hazırla
            if (is_null($value)) {
                $value = 'NULL';
            } elseif (is_bool($value)) {
                $value = $value ? 'TRUE' : 'FALSE';
            } elseif (is_int($value) || is_float($value)) {
                $value = (string)$value;
            } elseif (is_array($value)) {
                $items = array_map(function($item) {
                    if (is_string($item)) {
                        return "'" . str_replace("'", "''", $item) . "'";
                    }
                    return (string)$item;
                }, $value);
                $value = '(' . implode(',', $items) . ')';
            } else {
                $value = "'" . str_replace("'", "''", $value) . "'";
            }
            
            // İlk eşleşmeyi değiştir
            $pos = strpos($sql, $placeholder);
            if ($pos !== false) {
                $sql = substr_replace($sql, $value, $pos, strlen($placeholder));
            }
        }
        
        return $sql;
    }
    
    /**
     * SQL sorgusunu RPC üzerinden çalıştırır
     * 
     * @param string $sql SQL sorgusu
     * @param array $params Sorgu parametreleri
     * @return array Sorgu sonucu
     */
    public function executeRawSql($sql, $params = []) {
        $preparedSql = $this->prepareSql($sql, $params);
        
        return $this->request('rpc/execute_sql', 'POST', [
            'query' => $preparedSql
        ], []); // Önbellek kullanma ve header göndermeme
    }
}
