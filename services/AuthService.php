<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../lib/DatabaseFactory.php';

class AuthService {
    private $authUrl;
    private $apiKey;
    private $db;

    public function __construct() {
        $this->authUrl = rtrim(SUPABASE_URL, '/') . '/auth/v1';
        $this->apiKey = SUPABASE_KEY;
        $this->db = database(); // Genel veritabanı istemcisi
    }

    private function request($endpoint, $method = 'POST', $data = [], $headers = []) {
        $url = $this->authUrl . '/' . ltrim($endpoint, '/');
        
        $defaultHeaders = [
            'apikey: ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        if (isset($_SESSION['user_session']['access_token'])) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $_SESSION['user_session']['access_token'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Geliştirme ortamında SSL doğrulamasını atla
        if (defined('APP_ENV') && APP_ENV === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $body = json_decode($response, true);
        return ['http_code' => $http_code, 'body' => $body];
    }

    public function registerUser($email, $password, $options = []) {
        $response = $this->request('signup', 'POST', [
            'email' => $email,
            'password' => $password,
            'data' => $options['data'] ?? new stdClass()
        ]);

        if ($response['http_code'] === 200 && isset($response['body']['id'])) {
            return ['success' => true, 'user' => $response['body']];
        }
        
        // Hata ayıklama için detaylı loglama
        $errorMessage = $response['body']['msg'] ?? 'Bilinmeyen bir hata oluştu.';
        error_log('Supabase Kayıt Hatası: ' . json_encode($response));
        
        return ['success' => false, 'message' => $errorMessage];
    }

    public function loginUser($email, $password) {
        $response = $this->request('token?grant_type=password', 'POST', [
            'email' => $email,
            'password' => $password
        ]);

        if ($response['http_code'] === 200 && isset($response['body']['access_token'])) {
            $_SESSION['user_session'] = $response['body'];
            return ['success' => true, 'session' => $response['body']];
        }
        return ['success' => false, 'message' => 'E-posta veya şifre hatalı.'];
    }

    public function logoutUser() {
        if (isset($_SESSION['user_session'])) {
            $this->request('logout', 'POST');
        }
        unset($_SESSION['user_session']);
        session_destroy();
        return ['success' => true];
    }
    
    public function getCurrentUser() {
        return $_SESSION['user_session']['user'] ?? null;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_session']);
    }

    public function sendPasswordResetEmail($email) {
        $this->request('recover', 'POST', ['email' => $email]);
        // Güvenlik nedeniyle her zaman başarılı döner
        return ['success' => true];
    }

    public function updateUserPassword($newPassword) {
        // Bu işlem, kullanıcının e-postasındaki linke tıkladığında aldığı
        // access_token ile frontend tarafında yapılmalıdır.
        // Ancak, bu projede doğrudan backend'den yapıyorsak, access token'ı session'dan almalıyız.
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'Oturum bulunamadı.'];
        }
        
        $response = $this->request('user', 'PUT', ['password' => $newPassword]);

        if ($response['http_code'] === 200) {
            return ['success' => true, 'user' => $response['body']];
        }
        return ['success' => false, 'message' => $response['body']['msg'] ?? 'Şifre güncellenemedi.'];
    }

    public function getUserProfile($userId) {
        try {
            $response = $this->db->from('users')->select('*')->eq('id', $userId)->single()->execute();
            return $response;
        } catch (Exception $e) {
            return null;
        }
    }
}

function auth_service() {
    return new AuthService();
}