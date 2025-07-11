<?php
/**
 * Database-based Authentication Service
 * 
 * Veritabanı tabanlı kimlik doğrulama servisi
 * Session yönetimi, şifre hash'leme ve güvenlik kontrolleri içerir
 */

require_once __DIR__ . '/../config/database.php';

class AuthService {
    private $db;
    
    public function __construct() {
        $this->db = database();
        $this->startSession();
    }
    
    /**
     * Session başlatma
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
                return ['success' => true, 'message' => 'Hesabınız başarıyla oluşturuldu.'];
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
    public function login($email, $password) {
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
            
            return ['success' => true, 'message' => 'Giriş başarılı.'];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
        }
    }
    
    /**
     * Kullanıcı çıkışı
     * 
     * @return array Başarı durumu
     */
    public function logout() {
        try {
            // Session'ı temizle
            if (session_status() !== PHP_SESSION_NONE) {
                session_unset();
                session_destroy();
            }
            
            return ['success' => true, 'message' => 'Çıkış yapıldı.'];
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Çıkış yapılırken hata oluştu.'];
        }
    }
    
    /**
     * Kullanıcı giriş kontrolü
     * 
     * @return bool Giriş yapılmış mı?
     */
    public function isLoggedIn() {
        $this->startSession();
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
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
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name'] = $user['last_name'];
        $_SESSION['user_phone_number'] = $user['phone_number'];
        $_SESSION['user_gender'] = $user['gender'];
        $_SESSION['user_login_time'] = time();
        $_SESSION['user_last_activity'] = time();
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
     * Benzersiz kullanıcı ID'si oluştur
     * 
     * @return string Kullanıcı ID'si
     */
}