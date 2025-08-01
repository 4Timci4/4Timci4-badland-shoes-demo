<?php


class SecurityManager
{
    private static $instance = null;


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    private function __construct()
    {

    }


    public function generateCSRFToken($form_name = 'default')
    {

        return 'csrf_disabled';
    }


    public function verifyCSRFToken($token, $form_name = 'default')
    {

        return true;
    }


    public function getCSRFTokenHTML($form_name = 'default')
    {
        $token = $this->generateCSRFToken($form_name);
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }


    public function sanitizeInput($input, $type = 'string')
    {
        if (is_array($input)) {
            return array_map(function ($item) use ($type) {
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

                $allowed_tags = '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img>';
                return strip_tags(trim($input), $allowed_tags);

            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }


    public function validateInput($input, $rules)
    {
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


    public function checkRateLimit($identifier = null, $max_requests = null, $time_window = null)
    {

        return true;
    }


    private function getClientIP()
    {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

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


    public function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $max_size = 5242880)
    {
        $errors = [];

        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = 'Geçersiz dosya parametresi.';
            return $errors;
        }


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


        if ($file['size'] > $max_size) {
            $errors[] = 'Dosya boyutu ' . ($max_size / 1024 / 1024) . 'MB\'dan büyük olamaz.';
        }


        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file['tmp_name']);
        } else {

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


        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $image_info = getimagesize($file['tmp_name']);
            if ($image_info === false) {
                $errors[] = 'Geçersiz resim dosyası.';
            }
        }

        return $errors;
    }


    public function generateSecureToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }


    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3,
        ]);
    }


    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }


    public function destroySession()
    {

        return true;
    }


    public function logSecurityEvent($event_type, $message, $data = [])
    {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'event_type' => $event_type,
            'message' => $message,
            'data' => $data
        ];


        error_log('SECURITY: ' . json_encode($log_entry));
    }


    public function detectSuspiciousActivity($request_data)
    {
        $suspicious_patterns = [
            'sql_injection' => ['union', 'select', 'insert', 'delete', 'drop', 'alter', '--', ';'],
            'xss' => ['<script', 'javascript:', 'onload=', 'onerror=', 'alert(', 'eval('],
            'path_traversal' => ['../', '..\\', '/etc/passwd', '/windows/system32'],
            'command_injection' => ['&&', '||', ';', '|', '`', '$()']
        ];

        $alerts = [];

        foreach ($request_data as $key => $value) {
            if (!is_string($value))
                continue;

            $value_lower = strtolower($value);

            foreach ($suspicious_patterns as $attack_type => $patterns) {
                foreach ($patterns as $pattern) {
                    if (strpos($value_lower, $pattern) !== false) {
                        $alerts[] = [
                            'type' => $attack_type,
                            'field' => $key,
                            'pattern' => $pattern,
                            'value' => substr($value, 0, 100)
                        ];

                        $this->logSecurityEvent(
                            'suspicious_activity',
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


function security()
{
    return SecurityManager::getInstance();
}
