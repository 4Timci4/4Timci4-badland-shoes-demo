<?php
/**
 * Güvenlik Yöneticisi
 * 
 * CSRF koruması, input validation, rate limiting ve session yönetimi
 */

class SecurityManager {
    private static $instance = null;
    private $session_started = false;
    
    // CSRF token timeout (saniye)
    private const CSRF_TOKEN_LIFETIME = 3600; // 1 saat
    
    // Rate limiting ayarları
    private const RATE_LIMIT_MAX_REQUESTS = 100;
    private const RATE_LIMIT_TIME_WINDOW = 3600; // 1 saat
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - Session'ı başlat
     */
    private function __construct() {
        $this->startSecureSession();
    }
    
    /**
     * Güvenli session başlatma
     */
    private function startSecureSession() {
        if (!$this->session_started && session_status() === PHP_SESSION_NONE) {
            // Güvenli session ayarları
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_lifetime', 0); // Browser kapanana kadar
            
            // Session hijacking koruması
            ini_set('session.entropy_length', 32);
            ini_set('session.entropy_file', '/dev/urandom');
            
            session_start();
            $this->session_started = true;
            
            // Session fixation koruması
            if (!isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
                $_SESSION['initiated'] = true;
                $_SESSION['created_at'] = time();
            }
            
            // Session timeout kontrolü devre dışı - ana uygulamanın session management'ı bu işi hallediyor
            // SecurityManager sadece CSRF, validation ve rate limiting için kullanılıyor
            // Session lifecycle ana config/session.php ile yönetiliyor
        }
    }
    
    /**
     * CSRF token oluştur
     */
    public function generateCSRFToken($form_name = 'default') {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$form_name] = [
            'token' => $token,
            'timestamp' => time()
        ];
        
        // Eski tokenları temizle
        $this->cleanExpiredCSRFTokens();
        
        return $token;
    }
    
    /**
     * CSRF token doğrula
     */
    public function verifyCSRFToken($token, $form_name = 'default') {
        if (!isset($_SESSION['csrf_tokens'][$form_name])) {
            return false;
        }
        
        $stored_token = $_SESSION['csrf_tokens'][$form_name];
        
        // Token süresi dolmuş mu?
        if (time() - $stored_token['timestamp'] > self::CSRF_TOKEN_LIFETIME) {
            unset($_SESSION['csrf_tokens'][$form_name]);
            return false;
        }
        
        // Token eşleşiyor mu?
        if (!hash_equals($stored_token['token'], $token)) {
            return false;
        }
        
        // Tek kullanımlık token - kullanıldıktan sonra sil
        unset($_SESSION['csrf_tokens'][$form_name]);
        
        return true;
    }
    
    /**
     * Süresi dolmuş CSRF tokenları temizle
     */
    private function cleanExpiredCSRFTokens() {
        if (!isset($_SESSION['csrf_tokens'])) {
            return;
        }
        
        $current_time = time();
        foreach ($_SESSION['csrf_tokens'] as $form_name => $token_data) {
            if ($current_time - $token_data['timestamp'] > self::CSRF_TOKEN_LIFETIME) {
                unset($_SESSION['csrf_tokens'][$form_name]);
            }
        }
    }
    
    /**
     * CSRF token HTML input elementi oluştur
     */
    public function getCSRFTokenHTML($form_name = 'default') {
        $token = $this->generateCSRFToken($form_name);
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Input validation - XSS koruması
     */
    public function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return $this->sanitizeInput($item, $type);
            }, $input);
        }
        
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
                
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
                
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'html':
                // HTML için daha güvenli temizleme (gerçek uygulamada HTMLPurifier kullanılmalı)
                $allowed_tags = '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img>';
                return strip_tags(trim($input), $allowed_tags);
                
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Input validation - Veri doğrulama
     */
    public function validateInput($input, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule_set) {
            $value = $input[$field] ?? null;
            
            foreach ($rule_set as $rule => $params) {
                switch ($rule) {
                    case 'required':
                        if ($params && (empty($value) && $value !== '0')) {
                            $errors[$field][] = ucfirst($field) . ' alanı zorunludur.';
                        }
                        break;
                        
                    case 'min_length':
                        if (!empty($value) && strlen($value) < $params) {
                            $errors[$field][] = ucfirst($field) . ' en az ' . $params . ' karakter olmalıdır.';
                        }
                        break;
                        
                    case 'max_length':
                        if (!empty($value) && strlen($value) > $params) {
                            $errors[$field][] = ucfirst($field) . ' en fazla ' . $params . ' karakter olmalıdır.';
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = 'Geçerli bir e-posta adresi giriniz.';
                        }
                        break;
                        
                    case 'url':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $errors[$field][] = 'Geçerli bir URL giriniz.';
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = ucfirst($field) . ' sayısal bir değer olmalıdır.';
                        }
                        break;
                        
                    case 'alpha':
                        if (!empty($value) && !ctype_alpha($value)) {
                            $errors[$field][] = ucfirst($field) . ' sadece harf içermelidir.';
                        }
                        break;
                        
                    case 'alphanumeric':
                        if (!empty($value) && !ctype_alnum($value)) {
                            $errors[$field][] = ucfirst($field) . ' sadece harf ve rakam içermelidir.';
                        }
                        break;
                        
                    case 'regex':
                        if (!empty($value) && !preg_match($params, $value)) {
                            $errors[$field][] = ucfirst($field) . ' geçerli bir formatta değil.';
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Rate limiting - IP bazlı istek sınırlaması
     */
    public function checkRateLimit($identifier = null, $max_requests = null, $time_window = null) {
        $identifier = $identifier ?: $this->getClientIP();
        $max_requests = $max_requests ?: self::RATE_LIMIT_MAX_REQUESTS;
        $time_window = $time_window ?: self::RATE_LIMIT_TIME_WINDOW;
        
        $key = 'rate_limit_' . hash('sha256', $identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'requests' => 1,
                'reset_time' => time() + $time_window
            ];
            return true;
        }
        
        $rate_data = $_SESSION[$key];
        
        // Zaman penceresi dolmuş mu?
        if (time() >= $rate_data['reset_time']) {
            $_SESSION[$key] = [
                'requests' => 1,
                'reset_time' => time() + $time_window
            ];
            return true;
        }
        
        // İstek sayısı sınırı aşıldı mı?
        if ($rate_data['requests'] >= $max_requests) {
            return false;
        }
        
        // İstek sayısını artır
        $_SESSION[$key]['requests']++;
        return true;
    }
    
    /**
     * Gerçek client IP adresini al
     */
    private function getClientIP() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 
                   'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Güvenli dosya yükleme kontrolü
     */
    public function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $max_size = 5242880) { // 5MB
        $errors = [];
        
        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = 'Geçersiz dosya parametresi.';
            return $errors;
        }
        
        // Upload hatalarını kontrol et
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'Dosya seçilmedi.';
                return $errors;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'Dosya boyutu çok büyük.';
                return $errors;
            default:
                $errors[] = 'Bilinmeyen dosya yükleme hatası.';
                return $errors;
        }
        
        // Dosya boyutunu kontrol et
        if ($file['size'] > $max_size) {
            $errors[] = 'Dosya boyutu ' . ($max_size / 1024 / 1024) . 'MB\'dan büyük olamaz.';
        }
        
        // MIME type kontrolü (finfo alternatifi)
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file['tmp_name']);
        } else {
            // Fallback: extension'dan mime type belirle
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime_types = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf'
            ];
            $mime_type = $mime_types[$extension] ?? 'application/octet-stream';
        }
        
        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf'
        ];
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowed_types)) {
            $errors[] = 'İzin verilen dosya türleri: ' . implode(', ', $allowed_types);
        }
        
        if (!in_array($mime_type, array_values($mime_types))) {
            $errors[] = 'Geçersiz dosya türü.';
        }
        
        // Dosya içeriğini kontrol et (ek güvenlik)
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $image_info = getimagesize($file['tmp_name']);
            if ($image_info === false) {
                $errors[] = 'Geçersiz resim dosyası.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Güvenli random string oluştur
     */
    public function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Password hash oluştur
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);
    }
    
    /**
     * Password doğrula
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Session'ı güvenli şekilde yok et
     */
    public function destroySession() {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        $this->session_started = false;
    }
    
    /**
     * Güvenlik logları
     */
    public function logSecurityEvent($event_type, $message, $data = []) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'event_type' => $event_type,
            'message' => $message,
            'data' => $data
        ];
        
        // Log dosyasına yaz (gerçek uygulamada harici log servisi kullanılmalı)
        error_log('SECURITY: ' . json_encode($log_entry));
    }
    
    /**
     * Suspicious activity kontrolü
     */
    public function detectSuspiciousActivity($request_data) {
        $suspicious_patterns = [
            'sql_injection' => ['union', 'select', 'insert', 'delete', 'drop', 'alter', '--', ';'],
            'xss' => ['<script', 'javascript:', 'onload=', 'onerror=', 'alert(', 'eval('],
            'path_traversal' => ['../', '..\\', '/etc/passwd', '/windows/system32'],
            'command_injection' => ['&&', '||', ';', '|', '`', '$()']
        ];
        
        $alerts = [];
        
        foreach ($request_data as $key => $value) {
            if (!is_string($value)) continue;
            
            $value_lower = strtolower($value);
            
            foreach ($suspicious_patterns as $attack_type => $patterns) {
                foreach ($patterns as $pattern) {
                    if (strpos($value_lower, $pattern) !== false) {
                        $alerts[] = [
                            'type' => $attack_type,
                            'field' => $key,
                            'pattern' => $pattern,
                            'value' => substr($value, 0, 100) // İlk 100 karakter
                        ];
                        
                        $this->logSecurityEvent('suspicious_activity', 
                            "Potential $attack_type detected in field $key", 
                            ['pattern' => $pattern, 'value' => substr($value, 0, 100)]
                        );
                    }
                }
            }
        }
        
        return $alerts;
    }
}

/**
 * Global security manager fonksiyonu
 */
function security() {
    return SecurityManager::getInstance();
}
