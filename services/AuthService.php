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

    private function dbRequest($path, $method = 'POST', $data = [], $headers = []) {
        $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/' . ltrim($path, '/');
        
        $defaultHeaders = [
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey, // service_role key
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

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
        // 'data' alanını Supabase'in beklediği formatta oluştur
        $userData = [];
        if (!empty($options['full_name'])) {
            $userData['full_name'] = $options['full_name'];
        }
        if (!empty($options['phone_number'])) {
            $userData['phone_number'] = $options['phone_number'];
        }

        $response = $this->request('signup', 'POST', [
            'email'    => $email,
            'password' => $password,
            'data'     => (object)$userData
        ]);

        if ($response['http_code'] === 200 && isset($response['body']['id'])) {
            $user = $response['body'];
            
            // Profil kaydını manuel olarak public.users tablosuna ekle
            $profileData = [
                'id' => $user['id'],
                'email' => $user['email'],
                'full_name' => $options['full_name'] ?? null,
                'phone_number' => $options['phone_number'] ?? null
            ];
            
            $profileResponse = $this->dbRequest('users', 'POST', $profileData);

            if ($profileResponse['http_code'] !== 201) {
                error_log('Kullanıcı profili oluşturma hatası: ' . json_encode($profileResponse));
                return ['success' => false, 'message' => 'Kullanıcı oluşturuldu ancak profil bilgileri kaydedilemedi.'];
            }

            return ['success' => true, 'user' => $user];
        }
        
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
        return ['success' => true];
    }

    public function updateUserPassword($newPassword) {
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
        $profileResponse = $this->dbRequest('users?id=eq.' . urlencode($userId), 'GET');
        
        if ($profileResponse['http_code'] === 200 && !empty($profileResponse['body'])) {
            return $profileResponse['body'][0];
        }
        
        error_log('Kullanıcı profili alınamadı: ' . json_encode($profileResponse));
        return null;
    }
}

function auth_service() {
    return new AuthService();
}