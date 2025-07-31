<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class AdminAuthService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }

    public function login($username, $password)
    {
        if (!$this->db) {
            return $this->getDemoLoginResponse($username, $password);
        }
        
        try {
            $admins = $this->db->select('admins', [
                'username' => $username,
                'is_active' => 1
            ], '*', ['limit' => 1]);

            if (empty($admins)) {
                return false;
            }

            $admin = $admins;

            if (!password_verify($password, $admin['password_hash'])) {
                return false;
            }

            $this->updateLastLogin($admin['id']);

            unset($admin['password_hash']);

            return $admin;

        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            return false;
        }
    }

    public function getAdminById($admin_id)
    {
        if (!$this->db) {
            return $this->getDemoAdminById($admin_id);
        }
        
        try {
            $admins = $this->db->select('admins', [
                'id' => intval($admin_id),
                'is_active' => 1
            ], 'id,username,full_name,email,is_active,last_login_at,created_at', ['limit' => 1]);

            if (empty($admins)) {
                return false;
            }

            return $admins[0];

        } catch (Exception $e) {
            error_log("Get admin by ID error: " . $e->getMessage());
            return false;
        }
    }

    public function getAdminByUsername($username)
    {
        if (!$this->db) {
            return $this->getDemoAdminByUsername($username);
        }
        
        try {
            $admins = $this->db->select('admins', [
                'username' => $username,
                'is_active' => 1
            ], 'id,username,full_name,email,is_active,last_login_at,created_at', ['limit' => 1]);

            if (empty($admins)) {
                return false;
            }

            return $admins[0];

        } catch (Exception $e) {
            error_log("Get admin by username error: " . $e->getMessage());
            return false;
        }
    }

    public function updateLastLogin($admin_id)
    {
        if (!$this->db) {
            return false; // Demo modunda güncelleme devre dışı
        }
        
        try {
            $result = $this->db->update('admins', [
                'last_login_at' => date('Y-m-d H:i:s')
            ], ['id' => intval($admin_id)]);

            return $result !== false;

        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($admin_id, $new_password)
    {
        if (!$this->db) {
            return false; // Demo modunda şifre güncelleme devre dışı
        }
        
        try {

            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            $result = $this->db->update('admins', [
                'password_hash' => $password_hash
            ], ['id' => intval($admin_id)]);

            return $result !== false;

        } catch (Exception $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }

    public function createSession($admin_data)
    {
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

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public function destroySession()
    {
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
        }
    }

    public function isLoggedIn()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    public function checkTimeout($timeout_seconds = 7200)
    {
        if (!$this->isLoggedIn()) {
            return true;
        }

        if (isset($_SESSION['admin_last_activity'])) {
            if (time() - $_SESSION['admin_last_activity'] > $timeout_seconds) {
                $this->destroySession();
                return true;
            }
        }

        $_SESSION['admin_last_activity'] = time();
        return false;
    }

    public function getCurrentAdmin()
    {
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

    public function getAllAdmins()
    {
        if (!$this->db) {
            return $this->getDemoAllAdmins();
        }
        
        try {
            return $this->db->select(
                'admins',
                [],
                'id,username,full_name,email,is_active,last_login_at,created_at',
                ['order' => 'created_at DESC']
            );

        } catch (Exception $e) {
            error_log("Get all admins error: " . $e->getMessage());
            return [];
        }
    }

    public function getCsrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public function verifyCsrfToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }


    public function createAdmin($data)
    {
        if (!$this->db) {
            return ['error' => 'Demo modunda admin oluşturma işlemi devre dışıdır.'];
        }
        
        try {

            $existing = $this->getAdminByUsername($data['username']);
            if ($existing) {
                return ['error' => 'Bu kullanıcı adı zaten kullanılıyor!'];
            }


            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

            $admin_data = [
                'username' => $data['username'],
                'password_hash' => $password_hash,
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->insert('admins', $admin_data);

            if ($result !== false) {
                $admin_id = is_array($result) ? (isset($result['id']) ? $result['id'] : $result[0]['id'] ?? false) : $result;
                return ['success' => true, 'id' => $admin_id];
            }

            return ['error' => 'Admin oluşturulamadı!'];

        } catch (Exception $e) {
            error_log("Create admin error: " . $e->getMessage());
            return ['error' => 'Sistem hatası: ' . $e->getMessage()];
        }
    }


    public function updateAdmin($admin_id, $data)
    {
        if (!$this->db) {
            return ['error' => 'Demo modunda admin güncelleme işlemi devre dışıdır.'];
        }
        
        try {

            $existing = $this->getAdminById($admin_id);
            if (!$existing) {
                return ['error' => 'Admin bulunamadı!'];
            }


            if (isset($data['username'])) {
                $duplicates = $this->db->select('admins', [
                    'username' => $data['username'],
                    'id' => ['!=', intval($admin_id)]
                ], 'id', ['limit' => 1]);

                if (!empty($duplicates)) {
                    return ['error' => 'Bu kullanıcı adı zaten kullanılıyor!'];
                }
            }

            $update_data = [];


            if (isset($data['username']))
                $update_data['username'] = $data['username'];
            if (isset($data['full_name']))
                $update_data['full_name'] = $data['full_name'];
            if (isset($data['email']))
                $update_data['email'] = $data['email'];
            if (isset($data['is_active']))
                $update_data['is_active'] = intval($data['is_active']);


            if (isset($data['password']) && !empty($data['password'])) {
                $update_data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($update_data)) {
                return ['error' => 'Güncellenecek veri bulunamadı!'];
            }

            $result = $this->db->update('admins', $update_data, ['id' => intval($admin_id)]);

            if ($result !== false) {

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


    public function deleteAdmin($admin_id)
    {
        if (!$this->db) {
            return ['error' => 'Demo modunda admin silme işlemi devre dışıdır.'];
        }
        
        try {

            $current_admin = $this->getCurrentAdmin();
            if ($current_admin && $current_admin['id'] == $admin_id) {
                return ['error' => 'Kendi hesabınızı silemezsiniz!'];
            }


            $existing = $this->getAdminById($admin_id);
            if (!$existing) {
                return ['error' => 'Admin bulunamadı!'];
            }

            $result = $this->db->delete('admins', ['id' => intval($admin_id)]);

            if ($result !== false) {
                return ['success' => true];
            }

            return ['error' => 'Admin silinemedi!'];

        } catch (Exception $e) {
            error_log("Delete admin error: " . $e->getMessage());
            return ['error' => 'Sistem hatası: ' . $e->getMessage()];
        }
    }


    public function getAdminCount()
    {
        if (!$this->db) {
            return $this->getDemoAdminCount();
        }
        
        try {
            $admins = $this->db->select('admins', [], 'id');
            return count($admins);
        } catch (Exception $e) {
            error_log("Get admin count error: " . $e->getMessage());
            return 0;
        }
    }


    private function updateCurrentSession($update_data)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (isset($update_data['username'])) {
            $_SESSION['admin_username'] = $update_data['username'];
        }

        if (isset($update_data['full_name'])) {
            $_SESSION['admin_full_name'] = $update_data['full_name'];
        }


        if (isset($update_data['email'])) {
            $_SESSION['admin_email'] = $update_data['email'];
        }
    }

    /**
     * Demo login yanıtı - Güvenlik için login'e izin verilmiyor
     */
    private function getDemoLoginResponse($username, $password)
    {
        error_log("DEMO MODE: Admin login denemesi - Username: $username (GÜVENLİK NEDENİYLE REDDEDILDI)");
        
        // Güvenlik açısından demo modunda admin girişine izin verilmiyor
        return false;
    }

    /**
     * Demo admin bilgileri ID ile
     */
    private function getDemoAdminById($admin_id)
    {
        $admins = $this->getDemoAllAdmins();
        
        foreach ($admins as $admin) {
            if ($admin['id'] == $admin_id) {
                return $admin;
            }
        }
        
        return false;
    }

    /**
     * Demo admin bilgileri username ile
     */
    private function getDemoAdminByUsername($username)
    {
        $admins = $this->getDemoAllAdmins();
        
        foreach ($admins as $admin) {
            if ($admin['username'] === $username) {
                return $admin;
            }
        }
        
        return false;
    }

    /**
     * Demo tüm adminler
     */
    private function getDemoAllAdmins()
    {
        return [
            [
                'id' => 1,
                'username' => 'admin',
                'full_name' => 'Demo Admin',
                'email' => 'admin@demo.com',
                'is_active' => 1,
                'last_login_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
            ],
            [
                'id' => 2,
                'username' => 'moderator',
                'full_name' => 'Demo Moderatör',
                'email' => 'moderator@demo.com',
                'is_active' => 1,
                'last_login_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
            ],
            [
                'id' => 3,
                'username' => 'manager',
                'full_name' => 'Demo Yönetici',
                'email' => 'manager@demo.com',
                'is_active' => 0,
                'last_login_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-45 days'))
            ]
        ];
    }

    /**
     * Demo admin sayısı
     */
    private function getDemoAdminCount()
    {
        return count($this->getDemoAllAdmins());
    }
}
?>