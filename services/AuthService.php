<?php
/**
 * Database-based Authentication Service
 *
 * Veritabanı tabanlı kimlik doğrulama servisi
 * Session yönetimi, şifre hash'leme ve güvenlik kontrolleri içerir
 */

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/Product/FavoriteService.php';
require_once __DIR__ . '/EmailService.php';

class AuthService {
    private $db;
    private $favoriteService;
    private $emailService;
    
    public function __construct() {
        $this->db = database();
        $this->startSession();
        $this->favoriteService = new FavoriteService($this->db);
        $this->emailService = new EmailService();
    }

    /**
     * Starts a secure session if not already started.
     */
    public function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            SessionConfig::init();
        }
    }

    /**
     * Generates and stores a CSRF token in the session.
     *
     * @return string The generated CSRF token.
     */
    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validates a given CSRF token against the one in the session.
     *
     * @param string $token The CSRF token to validate.
     * @return bool True if the token is valid, false otherwise.
     */
    public function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Session güvenlik kontrollerini yap
     *
     * @return bool Kontroller başarılı mı?
     */
    public function checkSessionSecurity() {
        // Session başlatılmamışsa başlat
        if (session_status() === PHP_SESSION_NONE) {
            SessionConfig::init();
            return true;
        }
        
        // Kullanıcı giriş yapmışsa ek kontroller yap
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
            // Kullanıcı ID'si var mı kontrol et
            if (!isset($_SESSION['user_id'])) {
                $this->logout(false, 'invalid_session');
                return false;
            }
            
            // Son aktivite zamanı kontrolü
            if (isset($_SESSION['user_last_activity'])) {
                $inactiveTime = time() - $_SESSION['user_last_activity'];
                if ($inactiveTime > 1800) { // 30 dakika
                    $this->logout(false, 'timeout');
                    return false;
                }
            }
            
            // Session activity'sini güncelle (veritabanında - 5 dakikada bir)
            if (!isset($_SESSION['last_activity_update']) || (time() - $_SESSION['last_activity_update'] > 300)) {
                SessionConfig::updateSessionActivity($_SESSION['user_id'], $this->db);
                $_SESSION['last_activity_update'] = time();
            }
        }
        
        return true;
    }
    
    /**
     * Kullanıcı kaydı
     * 
     * @param string $email E-posta adresi
     * @param string $password Şifre
     * @param array $options Ek kullanıcı bilgileri
     * @return array Başarı durumu ve mesaj
     */
    public function register($email, $password, $options = []) {
        try {
            // E-posta benzersizlik kontrolü
            $existingUser = $this->db->select('users', ['email' => $email], '*', ['limit' => 1]);
            if (!empty($existingUser)) {
                return ['success' => false, 'message' => 'Bu e-posta adresi zaten kullanılıyor.'];
            }
            
            // Şifre hash'leme
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Kullanıcı verilerini hazırla
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
            
            // Veritabanına kaydet
            $result = $this->db->insert('users', $userData);
            
            if ($result !== false) {
                // Başarılı kayıt sonrası hoş geldin e-postası gönder
                try {
                    $emailResult = $this->emailService->sendRegistrationConfirmation(
                        $email,
                        $options['first_name'] ?? '',
                        $options['last_name'] ?? ''
                    );
                    
                    if (!$emailResult['success']) {
                        // E-posta gönderiminde hata olsa bile kayıt başarılı sayılır
                        // Hata loglanır ama kullanıcıya hata gösterilmez
                        error_log("Registration email failed for {$email}: " . $emailResult['message']);
                    }
                } catch (Exception $emailException) {
                    // E-posta gönderiminde kritik hata olsa bile kayıt başarılı sayılır
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
    
    /**
     * Kullanıcı girişi
     * 
     * @param string $email E-posta adresi
     * @param string $password Şifre
     * @return array Başarı durumu ve mesaj
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Kullanıcıyı veritabanından getir
            $users = $this->db->select('users', ['email' => $email], '*', ['limit' => 1]);
            
            if (empty($users)) {
                return ['success' => false, 'message' => 'E-posta veya şifre hatalı.'];
            }
            
            $user = $users[0];
            
            // Şifre doğrulama
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'E-posta veya şifre hatalı.'];
            }
            
            // Session'a kullanıcı bilgilerini kaydet
            $this->createUserSession($user);

            // Session'ı veritabanına kaydet
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
    
    /**
     * Kullanıcı çıkışı
     *
     * @param bool $isUserLogout Kullanıcı bilinçli olarak çıkış yapıyorsa true
     * @param string $reason Çıkış nedeni ('user', 'timeout', 'security', vb.)
     * @return array Başarı durumu
     */
    public function logout($isUserLogout = true, $reason = 'user') {
        $userId = $_SESSION['user_id'] ?? null;
        
        // Remember me token temizle
        if (isset($_COOKIE['remember_me'])) {
            list($selector, $validator) = explode(':', $_COOKIE['remember_me']);
            $this->db->delete('auth_tokens', ['selector' => $selector]);
            setcookie('remember_me', '', time() - 3600, '/');
        }

        try {
            // Session'ı veritabanından temizle
            if ($userId) {
                SessionConfig::clearUserSession($userId, $this->db);
            }
            
            // Session'ı güvenli şekilde yok et - bilinçli kullanıcı çıkışı mı belirt
            SessionConfig::destroySession($isUserLogout);
            
            // Log kaydı - çıkış nedeni
            if ($reason !== 'user') {
                error_log("User logout reason: " . $reason);
            }
            
            return ['success' => true, 'message' => 'Çıkış yapıldı.'];
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Çıkış yapılırken hata oluştu.'];
        }
    }

    private function createRememberMeToken($userId) {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days

        $this->db->insert('auth_tokens', [
            'user_id' => $userId,
            'selector' => $selector,
            'hashed_validator' => $hashedValidator,
            'expires_at' => $expiresAt
        ]);

        setcookie('remember_me', $selector . ':' . $validator, time() + (86400 * 30), '/');
    }

    public function loginWithRememberMeCookie() {
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
    
    /**
     * Kullanıcı giriş kontrolü
     *
     * @return bool Giriş yapılmış mı?
     */
    public function isLoggedIn() {
        // Session güvenlik kontrollerini yap
        $this->checkSessionSecurity();
        
        // Session başlatılmamışsa false döndür
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        // Basit kontrol - session değişkeni var mı?
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            return false;
        }
        
        // Session aktivitesini güncelle (her sayfa yüklendiğinde)
        $_SESSION['user_last_activity'] = time();
        
        return true;
    }
    
    /**
     * Aktif kullanıcı bilgilerini getir
     * 
     * @return array|null Kullanıcı bilgileri veya null
     */
    public function getCurrentUser() {
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
    
    /**
     * Kullanıcı profil bilgilerini getir
     * 
     * @param string $userId Kullanıcı ID'si
     * @return array|null Kullanıcı profili veya null
     */
    public function getUserProfile($userId) {
        try {
            $users = $this->db->select('users', ['id' => $userId], 'id,email,first_name,last_name,phone_number,gender,created_at', ['limit' => 1]);
            return $users[0] ?? null;
        } catch (Exception $e) {
            error_log("Get user profile error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Kullanıcı profil bilgilerini güncelle
     * 
     * @param string $userId Kullanıcı ID'si
     * @param array $data Güncellenecek veriler
     * @return array Başarı durumu
     */
    public function updateUserProfile($userId, $data) {
        try {
            // Güvenlik kontrolü - sadece giriş yapmış kullanıcı kendi profilini güncelleyebilir
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id'] !== $userId) {
                return ['success' => false, 'message' => 'Yetkiniz yok.'];
            }
            
            // Güncellenebilir alanları filtrele
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
            
            // Veritabanını güncelle
            $result = $this->db->update('users', $updateData, ['id' => $userId]);
            
            if ($result !== false) {
                // Session'daki bilgileri de güncelle
                $this->updateUserSession($updateData);
                return ['success' => true, 'message' => 'Profil başarıyla güncellendi.'];
            }
            
            return ['success' => false, 'message' => 'Profil güncellenirken hata oluştu.'];
            
        } catch (Exception $e) {
            error_log("Update user profile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
        }
    }
    
    /**
     * Şifre sıfırlama token'ı oluştur
     * 
     * @param string $email E-posta adresi
     * @return array Başarı durumu
     */
    public function createPasswordResetToken($email) {
        try {
            // Kullanıcı kontrolü
            $users = $this->db->select('users', ['email' => $email], '*', ['limit' => 1]);
            if (empty($users)) {
                return ['success' => false, 'message' => 'Bu e-posta adresi ile kayıtlı kullanıcı bulunamadı.'];
            }
            
            // Token oluştur
            $token = bin2hex(random_bytes(32));
            
            // Eski token'ları sil
            $this->db->delete('password_resets', ['email' => $email]);
            
            // Yeni token'ı kaydet
            $result = $this->db->insert('password_resets', [
                'email' => $email,
                'token' => $token,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result !== false) {
                return ['success' => true, 'token' => $token, 'message' => 'Şifre sıfırlama token\'ı oluşturuldu.'];
            }
            
            return ['success' => false, 'message' => 'Token oluşturulurken hata oluştu.'];
            
        } catch (Exception $e) {
            error_log("Create password reset token error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
        }
    }
    
    /**
     * Şifre sıfırlama token'ını doğrula ve şifre güncelle
     * 
     * @param string $token Token
     * @param string $newPassword Yeni şifre
     * @return array Başarı durumu
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Token kontrolü (1 saat geçerli)
            $tokenData = $this->db->select('password_resets', ['token' => $token], '*', ['limit' => 1]);
            
            if (empty($tokenData)) {
                return ['success' => false, 'message' => 'Geçersiz token.'];
            }
            
            $tokenInfo = $tokenData[0];
            
            // Token süre kontrolü (1 saat)
            $tokenTime = strtotime($tokenInfo['created_at']);
            if (time() - $tokenTime > 3600) {
                // Süresi dolmuş token'ları sil
                $this->db->delete('password_resets', ['token' => $token]);
                return ['success' => false, 'message' => 'Token\'ın süresi dolmuş.'];
            }
            
            // Yeni şifre hash'le
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Şifreyi güncelle
            $result = $this->db->update('users', ['password_hash' => $passwordHash], ['email' => $tokenInfo['email']]);
            
            if ($result !== false) {
                // Token'ı sil
                $this->db->delete('password_resets', ['token' => $token]);
                return ['success' => true, 'message' => 'Şifre başarıyla güncellendi.'];
            }
            
            return ['success' => false, 'message' => 'Şifre güncellenirken hata oluştu.'];
            
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
        }
    }
    
    /**
     * Kullanıcı session'ı oluştur
     *
     * @param array $user Kullanıcı bilgileri
     */
    private function createUserSession($user) {
        // Session fixation saldırılarını önlemek için session ID'yi yenile
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
        
        // Log kayıt
        error_log("User session created for user ID: " . $user['id'] . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
    
    /**
     * Kullanıcı session'ını güncelle
     * 
     * @param array $data Güncellenecek veriler
     */
    private function updateUserSession($data) {
        foreach ($data as $key => $value) {
            $_SESSION['user_' . $key] = $value;
        }
    }
    
    /**
     * Session debug bilgilerini getir
     *
     * @return array Debug bilgileri
     */
    public function getSessionDebugInfo() {
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
    
    /**
     * Session sağlık durumunu kontrol et
     *
     * @return array Sağlık durumu
     */
    public function checkSessionHealth() {
        $health = [
            'status' => 'healthy',
            'issues' => [],
            'warnings' => []
        ];
        
        // Session timeout kontrolü
        if (isset($_SESSION['user_last_activity'])) {
            $inactiveTime = time() - $_SESSION['user_last_activity'];
            if ($inactiveTime > 1500) { // 25 dakika
                $health['warnings'][] = 'Session yakında timeout olacak';
            }
        }
        
        // Session regeneration kontrolü
        if (isset($_SESSION['last_regeneration'])) {
            $lastRegeneration = time() - $_SESSION['last_regeneration'];
            if ($lastRegeneration > 1900) { // 31 dakika
                $health['warnings'][] = 'Session ID yakında yenilenecek';
            }
        }
        
        // IP adresi kontrolü
        if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            $health['status'] = 'critical';
            $health['issues'][] = 'IP adresi değişmiş - güvenlik riski';
        }
        
        return $health;
    }
    
    /**
     * Kullanıcının aktif session'larını getir
     *
     * @param int $userId Kullanıcı ID'si
     * @return array Aktif session'lar
     */
    public function getActiveSessions($userId) {
        try {
            return $this->db->select('user_sessions', ['user_id' => $userId], '*');
        } catch (Exception $e) {
            error_log("Get active sessions error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Belirli bir session'ı sonlandır
     *
     * @param int $userId Kullanıcı ID'si
     * @param string $sessionId Session ID'si
     * @return bool Başarı durumu
     */
    public function terminateSession($userId, $sessionId) {
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
}