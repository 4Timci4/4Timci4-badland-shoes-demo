<?php
/**
 * Base SEO Controller
 * Tüm SEO controller'ların ortak işlevlerini içerir
 */

abstract class BaseSeoController {
    protected $settingsService;
    protected $pageTitle;
    protected $breadcrumbItems;

    public function __construct() {
        // Auth kontrolü
        require_once 'config/auth.php';
        check_admin_auth();

        // Veritabanı bağlantısı
        require_once '../config/database.php';
        require_once '../services/SettingsService.php';

        $this->settingsService = new SettingsService();
        $this->initializePageInfo();
    }

    /**
     * Sayfa bilgilerini başlat
     */
    protected function initializePageInfo() {
        $this->pageTitle = 'SEO Ayarları';
        $this->breadcrumbItems = [
            ['title' => 'Ayarlar', 'url' => '#', 'icon' => 'fas fa-cog'],
            ['title' => 'SEO Ayarları', 'url' => 'seo-settings.php', 'icon' => 'fas fa-search']
        ];
    }

    /**
     * CSRF token doğrulama
     */
    protected function verifyCsrfToken($token) {
        return verify_csrf_token($token);
    }

    /**
     * Flash mesaj ayarla
     */
    protected function setFlashMessage($type, $message) {
        set_flash_message($type, $message);
    }

    /**
     * Yönlendirme yap
     */
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    /**
     * Ayarları güncelle ve yanıt ver
     */
    protected function updateSettingsAndRespond($keys, $type, $successMessage, $errorMessage, $redirectUrl) {
        if (!$this->verifyCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Güvenlik hatası oluştu.');
            $this->redirect($redirectUrl);
        }

        $successCount = 0;
        
        foreach ($keys as $key) {
            $value = $_POST[$key] ?? '';
            
            // Checkbox kontrolü
            if (in_array($key, ['canonical_enabled', 'sitemap_enabled', 'schema_enabled', 'breadcrumbs_enabled', 'amp_enabled'])) {
                $value = isset($_POST[$key]) ? 'true' : 'false';
            }
            
            if ($this->settingsService->updateSeoSetting($key, $value, $type, true)) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            $this->setFlashMessage('success', $successMessage);
        } else {
            $this->setFlashMessage('error', $errorMessage);
        }

        $this->redirect($redirectUrl);
    }

    /**
     * Varsayılan değerlerle ayarları birleştir
     */
    protected function mergeWithDefaults($settings, $defaults, $type) {
        foreach ($defaults[$type] as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = ['value' => $value, 'is_active' => true];
            }
        }
        return $settings;
    }

    /**
     * Abstract metodlar
     */
    abstract public function handleRequest();
    abstract public function getViewData();
}
