<?php
/**
 * Admin Authentication Service
 * 
 * Veritabanı tabanlı admin kimlik doğrulama servisi
 */

class AdminAuthService {
    private $supabase;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->supabase = supabase();
    }
    
    /**
     * Admin kullanıcı girişi
     * 
     * @param string $username Kullanıcı adı
     * @param string $password Şifre
     * @return array|false Başarılı ise admin bilgileri, başarısız ise false
     */
    public function login($username, $password) {
        try {
            // Kullanıcıyı veritabanından getir
            $response = $this->supabase->request(
                "admins?select=*&username=eq.$username&is_active=eq.true"
            );
            
            if (empty($response['body'])) {
                return false; // Kullanıcı bulunamadı veya aktif değil
            }
            
            $admin = $response['body'][0];
            
            // PHP password_verify ile şifre kontrolü
            if (!password_verify($password, $admin['password_hash'])) {
                return false;
            }
            
            // Son giriş zamanını güncelle
            $this->updateLastLogin($admin['id']);
            
            // Şifreyi dönüş verisinden çıkar
            unset($admin['password_hash']);
            
            return $admin;
            
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Admin kullanıcısını ID ile getir
     * 
     * @param int $admin_id Admin ID
     * @return array|false Admin bilgileri veya false
     */
    public function getAdminById($admin_id) {
        try {
            $response = $this->supabase->request(
                "admins?select=id,username,full_name,email,is_active,last_login_at,created_at&id=eq.$admin_id&is_active=eq.true"
            );
            
            if (empty($response['body'])) {
                return false;
            }
            
            return $response['body'][0];
            
        } catch (Exception $e) {
            error_log("Get admin by ID error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Admin kullanıcısını username ile getir
     * 
     * @param string $username Kullanıcı adı
     * @return array|false Admin bilgileri veya false
     */
    public function getAdminByUsername($username) {
        try {
            $response = $this->supabase->request(
                "admins?select=id,username,full_name,email,is_active,last_login_at,created_at&username=eq.$username&is_active=eq.true"
            );
            
            if (empty($response['body'])) {
                return false;
            }
            
            return $response['body'][0];
            
        } catch (Exception $e) {
            error_log("Get admin by username error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Son giriş zamanını güncelle
     * 
     * @param int $admin_id Admin ID
     * @return bool Başarı durumu
     */
    public function updateLastLogin($admin_id) {
        try {
            $response = $this->supabase->request(
                "admins?id=eq.$admin_id",
                'PATCH',
                [
                    'last_login_at' => date('c') // ISO 8601 format
                ]
            );
            
            return isset($response['body']);
            
        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Admin şifresini güncelle
     * 
     * @param int $admin_id Admin ID
     * @param string $new_password Yeni şifre
     * @return bool Başarı durumu
     */
    public function updatePassword($admin_id, $new_password) {
        try {
            // PHP ile şifreyi hash'le
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $response = $this->supabase->request(
                "admins?id=eq.$admin_id",
                'PATCH',
                [
                    'password_hash' => $password_hash
                ]
            );
            
            return isset($response['body']);
            
        } catch (Exception $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Session'a admin bilgilerini kaydet
     * 
     * @param array $admin_data Admin bilgileri
     */
    public function createSession($admin_data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin_data['id'];
        $_SESSION['admin_username'] = $admin_data['username'];
        $_SESSION['admin_full_name'] = $admin_data['full_name'];
        $_SESSION['admin_last_login'] = $admin_data['last_login_at'];
        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_last_activity'] = time();
        
        // CSRF token oluştur
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    /**
     * Session'ı yok et (logout)
     */
    public function destroySession() {
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
        }
    }
    
    /**
     * Aktif session kontrolü
     * 
     * @return bool Session aktif mi?
     */
    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    /**
     * Session timeout kontrolü
     * 
     * @param int $timeout_seconds Timeout süresi (saniye)
     * @return bool Timeout oldu mu?
     */
    public function checkTimeout($timeout_seconds = 7200) { // 2 saat
        if (!$this->isLoggedIn()) {
            return true;
        }
        
        if (isset($_SESSION['admin_last_activity'])) {
            if (time() - $_SESSION['admin_last_activity'] > $timeout_seconds) {
                $this->destroySession();
                return true;
            }
        }
        
        // Son aktivite zamanını güncelle
        $_SESSION['admin_last_activity'] = time();
        return false;
    }
    
    /**
     * Aktif admin bilgilerini getir
     * 
     * @return array|false Admin bilgileri veya false
     */
    public function getCurrentAdmin() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return [
            'id' => $_SESSION['admin_id'] ?? null,
            'username' => $_SESSION['admin_username'] ?? null,
            'full_name' => $_SESSION['admin_full_name'] ?? null,
            'last_login' => $_SESSION['admin_last_login'] ?? null,
            'login_time' => $_SESSION['admin_login_time'] ?? null
        ];
    }
    
    /**
     * Tüm aktif admin kullanıcılarını listele
     * 
     * @return array Admin listesi
     */
    public function getAllAdmins() {
        try {
            $response = $this->supabase->request(
                "admins?select=id,username,full_name,email,is_active,last_login_at,created_at&order=created_at.desc"
            );
            
            return $response['body'] ?? [];
            
        } catch (Exception $e) {
            error_log("Get all admins error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * CSRF token oluştur/getir
     * 
     * @return string CSRF token
     */
    public function getCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * CSRF token doğrula
     * 
     * @param string $token Kontrol edilecek token
     * @return bool Token geçerli mi?
     */
    public function verifyCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Yeni admin kullanıcısı oluştur
     * 
     * @param array $data Admin verileri
     * @return bool|array Başarılı ise admin ID'si, başarısız ise false
     */
    public function createAdmin($data) {
        try {
            // Kullanıcı adı benzersizlik kontrolü
            $existing = $this->getAdminByUsername($data['username']);
            if ($existing) {
                return ['error' => 'Bu kullanıcı adı zaten kullanılıyor!'];
            }
            
            // Şifreyi hash'le
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $admin_data = [
                'username' => $data['username'],
                'password_hash' => $password_hash,
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'is_active' => $data['is_active'] ?? true
            ];
            
            $response = $this->supabase->request(
                'admins',
                'POST',
                $admin_data
            );
            
            if (isset($response['body'][0]['id'])) {
                return ['success' => true, 'id' => $response['body'][0]['id']];
            }
            
            return ['error' => 'Admin oluşturulamadı!'];
            
        } catch (Exception $e) {
            error_log("Create admin error: " . $e->getMessage());
            return ['error' => 'Sistem hatası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Admin kullanıcısını güncelle
     * 
     * @param int $admin_id Admin ID
     * @param array $data Güncellenecek veriler
     * @return bool|array Başarı durumu
     */
    public function updateAdmin($admin_id, $data) {
        try {
            // Geçerli admin kontrolü
            $existing = $this->getAdminById($admin_id);
            if (!$existing) {
                return ['error' => 'Admin bulunamadı!'];
            }
            
            // Kullanıcı adı benzersizlik kontrolü (kendisi hariç)
            if (isset($data['username'])) {
                $username_check = $this->supabase->request(
                    "admins?select=id&username=eq.{$data['username']}&id=neq.$admin_id"
                );
                
                if (!empty($username_check['body'])) {
                    return ['error' => 'Bu kullanıcı adı zaten kullanılıyor!'];
                }
            }
            
            $update_data = [];
            
            // Güncellenecek alanları hazırla
            if (isset($data['username'])) $update_data['username'] = $data['username'];
            if (isset($data['full_name'])) $update_data['full_name'] = $data['full_name'];
            if (isset($data['email'])) $update_data['email'] = $data['email'];
            if (isset($data['is_active'])) $update_data['is_active'] = $data['is_active'];
            
            // Şifre güncellenmişse hash'le
            if (isset($data['password']) && !empty($data['password'])) {
                $update_data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            if (empty($update_data)) {
                return ['error' => 'Güncellenecek veri bulunamadı!'];
            }
            
            $response = $this->supabase->request(
                "admins?id=eq.$admin_id",
                'PATCH',
                $update_data
            );
            
            if (isset($response['body'])) {
                // Eğer güncellenen admin şu anki login olan admin ise session'ı da güncelle
                $current_admin = $this->getCurrentAdmin();
                if ($current_admin && $current_admin['id'] == $admin_id) {
                    $this->updateCurrentSession($update_data);
                }
                
                return ['success' => true];
            }
            
            return ['error' => 'Admin güncellenemedi!'];
            
        } catch (Exception $e) {
            error_log("Update admin error: " . $e->getMessage());
            return ['error' => 'Sistem hatası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Admin kullanıcısını sil
     * 
     * @param int $admin_id Admin ID
     * @return bool|array Başarı durumu
     */
    public function deleteAdmin($admin_id) {
        try {
            // Kendini silme kontrolü
            $current_admin = $this->getCurrentAdmin();
            if ($current_admin && $current_admin['id'] == $admin_id) {
                return ['error' => 'Kendi hesabınızı silemezsiniz!'];
            }
            
            // Admin varlık kontrolü
            $existing = $this->getAdminById($admin_id);
            if (!$existing) {
                return ['error' => 'Admin bulunamadı!'];
            }
            
            $response = $this->supabase->request(
                "admins?id=eq.$admin_id",
                'DELETE'
            );
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Delete admin error: " . $e->getMessage());
            return ['error' => 'Sistem hatası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Admin sayısını getir
     * 
     * @return int Admin sayısı
     */
    public function getAdminCount() {
        try {
            $response = $this->supabase->request('admins?select=id');
            return count($response['body'] ?? []);
        } catch (Exception $e) {
            error_log("Get admin count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mevcut session bilgilerini güncelle
     * 
     * @param array $update_data Güncellenmiş veriler
     */
    private function updateCurrentSession($update_data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Session'daki bilgileri güncelle
        if (isset($update_data['username'])) {
            $_SESSION['admin_username'] = $update_data['username'];
        }
        
        if (isset($update_data['full_name'])) {
            $_SESSION['admin_full_name'] = $update_data['full_name'];
        }
        
        // Email session'da tutulmuyor ama gelecekte eklenmesi durumunda
        if (isset($update_data['email'])) {
            $_SESSION['admin_email'] = $update_data['email'];
        }
    }
}
?>
