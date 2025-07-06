<?php
/**
 * SEO Analytics Settings Controller
 * Analytics ve izleme kodları ayarlarını yönetir
 */

require_once 'BaseSeoController.php';

class SeoAnalyticsController extends BaseSeoController {
    
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_analytics_settings') {
            $this->updateAnalyticsSettings();
        }
    }

    /**
     * Analytics ayarlarını güncelle
     */
    private function updateAnalyticsSettings() {
        $analyticsKeys = [
            'google_analytics_id', 
            'google_tag_manager_id', 
            'facebook_pixel_id', 
            'google_search_console', 
            'bing_webmaster', 
            'yandex_verification'
        ];

        $this->updateSettingsAndRespond(
            $analyticsKeys,
            'analytics',
            'Analytics ayarları başarıyla güncellendi.',
            'Analytics ayarları güncellenirken bir hata oluştu.',
            'seo-settings.php#analytics'
        );
    }

    /**
     * View için gerekli verileri hazırla
     */
    public function getViewData() {
        $analyticsSettings = $this->settingsService->getSeoSettingsByType('analytics');
        $defaultSeo = $this->settingsService->getDefaultSeoSettings();
        
        return $this->mergeWithDefaults($analyticsSettings, $defaultSeo, 'analytics');
    }
}
