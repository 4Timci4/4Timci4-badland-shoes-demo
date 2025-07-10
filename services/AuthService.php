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
        $this->db = database();
    }

    private function request($endpoint, $method = 'POST', $data = [], $headers = []) {
        $url = $this->authUrl . '/' . ltrim($endpoint, '/');
        
        $defaultHeaders = [
            'apikey: ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        $hasExternalAuth = false;
        foreach ($headers as $header) {
            if (stripos($header, 'Authorization:') === 0) {
                $hasExternalAuth = true;
                break;
            }
        }
        if (!$hasExternalAuth && isset($_SESSION['user_session']['access_token'])) {
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
            'Authorization: Bearer ' . $this->apiKey,
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
        $userData = [];
        if (!empty($options['first_name']) && !empty($options['last_name'])) {
            $userData['full_name'] = trim($options['first_name'] . ' ' . $options['last_name']);
        }
        
        $response = $this->request('signup', 'POST', [
            'email'    => $email,
            'password' => $password,
            'data'     => (object)$userData
        ]);

        if ($response['http_code'] === 200 && isset($response['body']['user']['id'])) {
            $session = $response['body'];
            $user = $session['user'];
            
            if (isset($session['access_token']) && !empty($options['phone_number'])) {
                $this->request(
                    'user',
                    'PUT',
                    ['phone' => $options['phone_number']],
                    ['Authorization: Bearer ' . $session['access_token']]
                );
            }

            // The user profile is likely created by a trigger.
            // We will update it with the additional information.
            $profileData = [
                'first_name' => $options['first_name'] ?? null,
                'last_name' => $options['last_name'] ?? null,
                'phone_number' => $options['phone_number'] ?? null,
                'gender' => !empty($options['gender']) ? $options['gender'] : null
            ];

            // Filter out null values to avoid overwriting existing data unnecessarily
            $profileData = array_filter($profileData, fn($value) => $value !== null);

            if (!empty($profileData)) {
                $profileResponse = $this->dbRequest('users?id=eq.' . urlencode($user['id']), 'PATCH', $profileData);

                // Log an error if the profile update fails, but don't block the user.
                // The user is already created in auth.users and can log in.
                if ($profileResponse['http_code'] < 200 || $profileResponse['http_code'] >= 300) {
                    error_log('Kullanıcı profili güncelleme hatası: ' . json_encode($profileResponse));
                    // This is not a fatal error for the registration flow.
                    // We can return success and let the user update their profile later.
                }
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
        
        // Session verilerini temizle
        $_SESSION = array();
        
        // Session cookie'sini temizle
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Session'ı yok et
        session_destroy();
        
        // Yeni bir temiz session başlat
        session_start();
        session_regenerate_id(true);
        
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

    public function updateUserProfile($userId, $data) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'Oturum bulunamadı.'];
        }

        $updateData = [];
        if (isset($data['first_name']) && isset($data['last_name'])) {
            $updateData['data']['full_name'] = trim($data['first_name'] . ' ' . $data['last_name']);
        }
        if (isset($data['phone_number'])) {
            $updateData['phone'] = $data['phone_number'];
        }
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        
        if (!empty($updateData)) {
            $this->request('user', 'PUT', $updateData);
        }

        $publicUsersUpdateData = [];
        if (isset($data['first_name'])) $publicUsersUpdateData['first_name'] = $data['first_name'];
        if (isset($data['last_name'])) $publicUsersUpdateData['last_name'] = $data['last_name'];
        if (isset($data['phone_number'])) $publicUsersUpdateData['phone_number'] = $data['phone_number'];
        if (isset($data['email'])) $publicUsersUpdateData['email'] = $data['email'];
        if (array_key_exists('gender', $data)) {
            $publicUsersUpdateData['gender'] = $data['gender'];
        }

        $profileUpdateResponse = $this->dbRequest('users?id=eq.' . urlencode($userId), 'PATCH', $publicUsersUpdateData);

        if ($profileUpdateResponse['http_code'] === 200) {
            return ['success' => true];
        }

        error_log('public.users güncelleme hatası: ' . json_encode($profileUpdateResponse));
        return ['success' => false, 'message' => 'Profil güncellenirken bir hata oluştu.'];
    }
}

function auth_service() {
    return new AuthService();
}