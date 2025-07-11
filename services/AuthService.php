<?php
require_once __DIR__ . '/../config/env.php';

class AuthService {
    private $authUrl;
    private $apiKey;

    public function __construct() {
        $this->authUrl = rtrim(SUPABASE_URL, '/') . '/auth/v1';
        $this->apiKey = SUPABASE_KEY;
    }

    private function request($endpoint, $method = 'POST', $data = [], $headers = []): array {
        $url = $this->authUrl . '/' . ltrim($endpoint, '/');
        
        $defaultHeaders = [
            'apikey: ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        // Disable SSL verification for local development
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error_num = curl_errno($ch);
        $curl_error_msg = curl_error($ch);
        curl_close($ch);

        if ($curl_error_num > 0) {
            error_log("cURL Error ($curl_error_num): $curl_error_msg");
            return ['http_code' => 0, 'body' => ['message' => "API bağlantı hatası: " . $curl_error_msg]];
        }

        $body = json_decode($response, true);
        return ['http_code' => $http_code, 'body' => $body];
    }

    public function login($email, $password) {
        $response = $this->request('token?grant_type=password', 'POST', [
            'email' => $email,
            'password' => $password
        ]);

        if ($response['http_code'] === 200 && isset($response['body']['access_token'])) {
            return ['success' => true, 'data' => $response['body']];
        }
        return ['success' => false, 'message' => 'E-posta veya şifre hatalı.'];
    }

    public function logout() {
        // Session temizlendi, sadece API çağrısı yapılacak
        return ['success' => true];
    }

    public function isLoggedIn() {
        // Session kaldırıldı, her zaman false döndür
        return false;
    }

    public function getCurrentUser() {
        // Session kaldırıldı, her zaman null döndür
        return null;
    }

    public function register($email, $password, $options = []) {
        $user_metadata = [];
        if (!empty($options['first_name']) && !empty($options['last_name'])) {
            $user_metadata['full_name'] = trim($options['first_name'] . ' ' . $options['last_name']);
        }

        $response = $this->request('signup', 'POST', [
            'email'    => $email,
            'password' => $password,
            'data'     => $user_metadata
        ]);

        if ($response['http_code'] === 200 && isset($response['body']['user']['id'])) {
            $user_id = $response['body']['user']['id'];
            
            $profile_data = [
                'id' => $user_id,
                'first_name' => $options['first_name'] ?? null,
                'last_name' => $options['last_name'] ?? null,
                'phone_number' => $options['phone_number'] ?? null,
                'gender' => $options['gender'] ?? null,
                'email' => $email
            ];

            $db = database();
            $db->insert('users', $profile_data, ['on_conflict' => 'id']);

            return ['success' => true];
        }
        
        error_log('Supabase Kayıt Hatası: ' . json_encode($response));
        $errorMessage = $response['body']['message'] ?? ($response['body']['msg'] ?? 'Bilinmeyen bir hata oluştu.');
        return ['success' => false, 'message' => $errorMessage];
    }

    public function getUserProfile($userId) {
        $db = database();
        $profile = $db->select('users', ['id' => ['=', $userId]], '*', ['limit' => 1]);
        return $profile[0] ?? null;
    }

    public function updateUserProfile($userId, $data) {
        $db = database();
        $db->update('users', $data, ['id' => ['=', $userId]]);
        return ['success' => true];
    }
}