<?php


require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/Product/FavoriteService.php';
require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/../lib/DatabaseFactory.php';

class AuthService
{
    private $db;
    private $favoriteService;
    private $emailService;

    public function __construct()
    {
        $this->db = database();
        $this->startSession();
        $this->favoriteService = new FavoriteService($this->db);
        $this->emailService = new EmailService();
    }


    public function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            SessionConfig::init();
        }
    }

    public function generateCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken($token)
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function checkSessionSecurity()
    {
        if (session_status() === PHP_SESSION_NONE) {
            SessionConfig::init();
            return true;
        }

        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
            if (!isset($_SESSION['user_id'])) {
                $this->logout(false, 'invalid_session');
                return false;
            }

            if (isset($_SESSION['user_last_activity'])) {
                $inactiveTime = time() - $_SESSION['user_last_activity'];
                if ($inactiveTime > 1800) {
                    $this->logout(false, 'timeout');
                    return false;
                }
            }


            if (!isset($_SESSION['last_activity_update']) || (time() - $_SESSION['last_activity_update'] > 300)) {
                if ($this->db) {
                    SessionConfig::updateSessionActivity($_SESSION['user_id'], $this->db);
                }
                $_SESSION['last_activity_update'] = time();
            }
        }

        return true;
    }

    public function register($email, $password, $options = [])
    {
        if (!$this->db) {
            return $this->getDemoRegisterResponse($email, $password, $options);
        }
        
        try {
            $existingUser = $this->db->select('users', ['email' => $email], '*', ['limit' => 1]);
            if (!empty($existingUser)) {
                return ['success' => false, 'message' => 'Bu e-posta adresi zaten kullanılıyor.'];
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $userData = [
                'id' => generate_uuid(),
                'email' => $email,
                'password_hash' => $passwordHash,
                'first_name' => $options['first_name'] ?? null,
                'last_name' => $options['last_name'] ?? null,
                'phone_number' => $options['phone_number'] ?? null,
                'gender' => $options['gender'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->insert('users', $userData);

            if ($result !== false) {
                try {
                    $emailResult = $this->emailService->sendRegistrationConfirmation(
                        $email,
                        $options['first_name'] ?? '',
                        $options['last_name'] ?? ''
                    );

                    if (!$emailResult['success']) {
                        error_log("Registration email failed for {$email}: " . $emailResult['message']);
                    }
                } catch (Exception $emailException) {
                    error_log("Registration email exception for {$email}: " . $emailException->getMessage());
                }

                return ['success' => true, 'message' => 'Hesabınız başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz.'];
            }

            return ['success' => false, 'message' => 'Hesap oluşturulurken bir hata oluştu.'];

        } catch (Exception $e) {
            error_log("Register error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
        }
    }

    public function login($email, $password, $rememberMe = false)
    {
        if (!$this->db) {
            return $this->getDemoLoginResponse($email, $password, $rememberMe);
        }
        
        try {

            $users = $this->db->select('users', ['email' => $email], '*', ['limit' => 1]);

            if (empty($users)) {
                return ['success' => false, 'message' => 'E-posta veya şifre hatalı.'];
            }

            $user = $users[0];


            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'E-posta veya şifre hatalı.'];
            }


            $this->createUserSession($user);


            SessionConfig::saveUserSession($user['id'], $this->db);

            if ($rememberMe) {
                $this->createRememberMeToken($user['id']);
            }

            return ['success' => true, 'message' => 'Giriş başarılı.'];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
        }
    }


    public function logout($isUserLogout = true, $reason = 'user')
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$this->db) {
            // Demo modunda session temizliği
            if (isset($_COOKIE['remember_me'])) {
                setcookie('remember_me', '', time() - 3600, '/');
            }
            SessionConfig::destroySession($isUserLogout);
            return ['success' => true, 'message' => 'Çıkış yapıldı (Demo Modu).'];
        }


        if (isset($_COOKIE['remember_me'])) {
            list($selector, $validator) = explode(':', $_COOKIE['remember_me']);
            $this->db->delete('auth_tokens', ['selector' => $selector]);
            setcookie('remember_me', '', time() - 3600, '/');
        }

        try {

            if ($userId) {
                SessionConfig::clearUserSession($userId, $this->db);
            }


            SessionConfig::destroySession($isUserLogout);


            if ($reason !== 'user') {
                error_log("User logout reason: " . $reason);
            }

            return ['success' => true, 'message' => 'Çıkış yapıldı.'];

        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Çıkış yapılırken hata oluştu.'];
        }
    }

    private function createRememberMeToken($userId)
    {
        if (!$this->db) {
            return; // Demo modunda remember me token oluşturulmaz
        }
        
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + (86400 * 30));

        $this->db->insert('auth_tokens', [
            'user_id' => $userId,
            'selector' => $selector,
            'hashed_validator' => $hashedValidator,
            'expires_at' => $expiresAt
        ]);

        setcookie('remember_me', $selector . ':' . $validator, time() + (86400 * 30), '/');
    }

    public function loginWithRememberMeCookie()
    {
        if (!$this->db) {
            return false; // Demo modunda remember me çalışmaz
        }
        
        if (isset($_COOKIE['remember_me'])) {
            list($selector, $validator) = explode(':', $_COOKIE['remember_me']);

            $token = $this->db->select('auth_tokens', ['selector' => $selector], '*', ['limit' => 1]);

            if (!empty($token)) {
                $token = $token[0];
                if (password_verify($validator, $token['hashed_validator'])) {
                    if (strtotime($token['expires_at']) > time()) {
                        $user = $this->db->select('users', ['id' => $token['user_id']], '*', ['limit' => 1]);
                        if (!empty($user)) {
                            $this->createUserSession($user[0]);
                            return true;
                        }
                    } else {
                        $this->db->delete('auth_tokens', ['id' => $token['id']]);
                    }
                }
            }
        }
        return false;
    }


    public function isLoggedIn()
    {

        $this->checkSessionSecurity();


        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }


        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            return false;
        }


        $_SESSION['user_last_activity'] = time();

        return true;
    }


    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'first_name' => $_SESSION['user_first_name'] ?? null,
            'last_name' => $_SESSION['user_last_name'] ?? null,
            'phone_number' => $_SESSION['user_phone_number'] ?? null,
            'gender' => $_SESSION['user_gender'] ?? null,
            'full_name' => trim(($_SESSION['user_first_name'] ?? '') . ' ' . ($_SESSION['user_last_name'] ?? ''))
        ];
    }


    public function getUserProfile($userId)
    {
        if (!$this->db) {
            return $this->getDemoUserProfile($userId);
        }
        
        try {
            $users = $this->db->select('users', ['id' => $userId], 'id,email,first_name,last_name,phone_number,gender,created_at', ['limit' => 1]);
            return $users[0] ?? null;
        } catch (Exception $e) {
            error_log("Get user profile error: " . $e->getMessage());
            return null;
        }
    }


    public function updateUserProfile($userId, $data)
    {
        if (!$this->db) {
            return $this->getDemoUpdateProfile($userId, $data);
        }
        
        try {

            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id'] !== $userId) {
                return ['success' => false, 'message' => 'Yetkiniz yok.'];
            }


            $allowedFields = ['first_name', 'last_name', 'phone_number', 'gender'];
            $updateData = [];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                return ['success' => false, 'message' => 'Güncellenecek veri bulunamadı.'];
            }


            $result = $this->db->update('users', $updateData, ['id' => $userId]);

            if ($result !== false) {

                $this->updateUserSession($updateData);
                return ['success' => true, 'message' => 'Profil başarıyla güncellendi.'];
            }

            return ['success' => false, 'message' => 'Profil güncellenirken hata oluştu.'];

        } catch (Exception $e) {
            error_log("Update user profile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
        }
    }


    public function createPasswordResetToken($email)
    {
        if (!$this->db) {
            return $this->getDemoPasswordReset($email);
        }
        
        try {

            $users = $this->db->select('users', ['email' => $email], '*', ['limit' => 1]);
            if (empty($users)) {
                return false;
            }

            $user = $users[0];


            $token = bin2hex(random_bytes(32));


            $this->db->delete('password_resets', ['email' => $email]);


            $result = $this->db->insert('password_resets', [
                'email' => $email,
                'token' => $token,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($result !== false) {

                try {
                    $emailResult = $this->emailService->sendPasswordResetEmail(
                        $email,
                        $token,
                        $user['first_name'] ?? '',
                        $user['last_name'] ?? ''
                    );

                    if ($emailResult['success']) {
                        return true;
                    } else {

                        error_log("Password reset email failed for {$email}: " . $emailResult['message']);
                        return false;
                    }
                } catch (Exception $emailException) {
                    error_log("Password reset email exception for {$email}: " . $emailException->getMessage());
                    return false;
                }
            }

            return false;

        } catch (Exception $e) {
            error_log("Create password reset token error: " . $e->getMessage());
            return false;
        }
    }


    public function resetPassword($token, $newPassword)
    {
        if (!$this->db) {
            return $this->getDemoResetPassword($token, $newPassword);
        }
        
        try {

            $tokenData = $this->db->select('password_resets', ['token' => $token], '*', ['limit' => 1]);

            if (empty($tokenData)) {
                return ['success' => false, 'message' => 'Geçersiz token.'];
            }

            $tokenInfo = $tokenData[0];


            $tokenTime = strtotime($tokenInfo['created_at']);
            if (time() - $tokenTime > 3600) {

                $this->db->delete('password_resets', ['token' => $token]);
                return ['success' => false, 'message' => 'Token\'ın süresi dolmuş.'];
            }


            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);


            $result = $this->db->update('users', ['password_hash' => $passwordHash], ['email' => $tokenInfo['email']]);

            if ($result !== false) {

                $this->db->delete('password_resets', ['token' => $token]);
                return ['success' => true, 'message' => 'Şifre başarıyla güncellendi.'];
            }

            return ['success' => false, 'message' => 'Şifre güncellenirken hata oluştu.'];

        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
        }
    }


    private function createUserSession($user)
    {

        SessionConfig::regenerateSession();

        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name'] = $user['last_name'];
        $_SESSION['user_phone_number'] = $user['phone_number'];
        $_SESSION['user_gender'] = $user['gender'];
        $_SESSION['user_login_time'] = time();
        $_SESSION['user_last_activity'] = time();


        error_log("User session created for user ID: " . $user['id'] . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }


    private function updateUserSession($data)
    {
        foreach ($data as $key => $value) {
            $_SESSION['user_' . $key] = $value;
        }
    }


    public function getSessionDebugInfo()
    {
        if (!$this->isLoggedIn()) {
            return ['error' => 'Kullanıcı giriş yapmamış'];
        }

        $debugInfo = SessionConfig::getDebugInfo();
        $debugInfo['user_id'] = $_SESSION['user_id'] ?? null;
        $debugInfo['user_email'] = $_SESSION['user_email'] ?? null;
        $debugInfo['login_time'] = $_SESSION['user_login_time'] ?? null;
        $debugInfo['login_duration'] = isset($_SESSION['user_login_time']) ? (time() - $_SESSION['user_login_time']) : null;

        return $debugInfo;
    }


    public function checkSessionHealth()
    {
        $health = [
            'status' => 'healthy',
            'issues' => [],
            'warnings' => []
        ];


        if (isset($_SESSION['user_last_activity'])) {
            $inactiveTime = time() - $_SESSION['user_last_activity'];
            if ($inactiveTime > 1500) {
                $health['warnings'][] = 'Session yakında timeout olacak';
            }
        }


        if (isset($_SESSION['last_regeneration'])) {
            $lastRegeneration = time() - $_SESSION['last_regeneration'];
            if ($lastRegeneration > 1900) {
                $health['warnings'][] = 'Session ID yakında yenilenecek';
            }
        }


        if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            $health['status'] = 'critical';
            $health['issues'][] = 'IP adresi değişmiş - güvenlik riski';
        }

        return $health;
    }


    public function getActiveSessions($userId)
    {
        if (!$this->db) {
            return $this->getDemoActiveSessions($userId);
        }
        
        try {
            return $this->db->select('user_sessions', ['user_id' => $userId], '*');
        } catch (Exception $e) {
            error_log("Get active sessions error: " . $e->getMessage());
            return [];
        }
    }


    public function terminateSession($userId, $sessionId)
    {
        if (!$this->db) {
            return false; // Demo modunda session sonlandırma devre dışı
        }
        
        try {
            $result = $this->db->delete('user_sessions', [
                'user_id' => $userId,
                'session_id' => $sessionId
            ]);

            if ($result) {
                error_log("Session terminated: User ID $userId, Session ID $sessionId");
                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log("Terminate session error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Demo kullanıcı kaydı yanıtı
     */
    private function getDemoRegisterResponse($email, $password, $options)
    {
        error_log("DEMO MODE: Kullanıcı kaydı denemesi - Email: $email (GÜVENLİK NEDENİYLE REDDEDILDI)");
        
        return [
            'success' => false,
            'message' => 'Demo modunda kullanıcı kaydı devre dışıdır. Demo için mevcut hesapları kullanabilirsiniz.',
            'demo_info' => [
                'demo_users' => [
                    'demo@example.com',
                    'test@example.com',
                    'user@demo.com'
                ]
            ]
        ];
    }

    /**
     * Demo giriş yanıtı
     */
    private function getDemoLoginResponse($email, $password, $rememberMe)
    {
        error_log("DEMO MODE: Kullanıcı girişi denemesi - Email: $email (GÜVENLİK NEDENİYLE REDDEDILDI)");
        
        return [
            'success' => false,
            'message' => 'Demo modunda kullanıcı girişi devre dışıdır. Site demo kullanıcısı olarak gezebilirsiniz.',
            'demo_info' => [
                'demo_message' => 'Demo modunda çalışıyorsunuz',
                'available_features' => [
                    'Ürün görüntüleme',
                    'Kategori gezinme',
                    'Blog okuma',
                    'İletişim bilgilerini görme'
                ],
                'disabled_features' => [
                    'Kullanıcı kaydı',
                    'Kullanıcı girişi',
                    'Sepet işlemleri',
                    'Sipariş verme'
                ]
            ]
        ];
    }

    /**
     * Demo kullanıcı profili
     */
    private function getDemoUserProfile($userId)
    {
        $demoUsers = [
            'demo-user-1' => [
                'id' => 'demo-user-1',
                'email' => 'demo@example.com',
                'first_name' => 'Demo',
                'last_name' => 'Kullanıcı',
                'phone_number' => '+90 555 123 4567',
                'gender' => 'erkek',
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
            ],
            'demo-user-2' => [
                'id' => 'demo-user-2',
                'email' => 'test@example.com',
                'first_name' => 'Test',
                'last_name' => 'Kullanıcısı',
                'phone_number' => '+90 555 987 6543',
                'gender' => 'kadın',
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
            ]
        ];

        return $demoUsers[$userId] ?? null;
    }

    /**
     * Demo profil güncelleme
     */
    private function getDemoUpdateProfile($userId, $data)
    {
        return [
            'success' => false,
            'message' => 'Demo modunda profil güncelleme devre dışıdır.',
            'demo_info' => [
                'user_id' => $userId,
                'attempted_update' => $data
            ]
        ];
    }

    /**
     * Demo şifre sıfırlama
     */
    private function getDemoPasswordReset($email)
    {
        error_log("DEMO MODE: Şifre sıfırlama talebi - Email: $email (DEMO MOD)");
        return false; // Demo modunda şifre sıfırlama çalışmaz
    }

    /**
     * Demo şifre sıfırlama token
     */
    private function getDemoResetPassword($token, $newPassword)
    {
        return [
            'success' => false,
            'message' => 'Demo modunda şifre sıfırlama devre dışıdır.',
            'demo_info' => [
                'token' => substr($token, 0, 10) . '...',
                'mode' => 'demo'
            ]
        ];
    }

    /**
     * Demo aktif sessionlar
     */
    private function getDemoActiveSessions($userId)
    {
        return [
            [
                'id' => 1,
                'user_id' => $userId,
                'session_id' => 'demo-session-1',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Demo Browser',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'last_activity' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
            ],
            [
                'id' => 2,
                'user_id' => $userId,
                'session_id' => 'demo-session-2',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Demo Mobile Browser',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'last_activity' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
            ]
        ];
    }
}