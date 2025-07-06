<?php
/**
 * SEO Social Media Settings Controller
 * Sosyal medya paylaşım ayarlarını yönetir
 */

require_once 'BaseSeoController.php';

class SeoSocialController extends BaseSeoController {
    
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_social_settings') {
            $this->updateSocialSettings();
        }
    }

    /**
     * Sosyal medya ayarlarını güncelle
     */
    private function updateSocialSettings() {
        $socialKeys = [
            'og_site_name', 
            'og_type', 
            'og_image', 
            'twitter_card', 
            'twitter_site', 
            'facebook_app_id', 
            'linkedin_company'
        ];

        $this->updateSettingsAndRespond(
            $socialKeys,
            'social',
            'Sosyal medya ayarları başarıyla güncellendi.',
            'Sosyal medya ayarları güncellenirken bir hata oluştu.',
            'seo-settings.php#social'
        );
    }

    /**
     * View için gerekli verileri hazırla
     */
    public function getViewData() {
        $socialSettings = $this->settingsService->getSeoSettingsByType('social');
        $defaultSeo = $this->settingsService->getDefaultSeoSettings();
        
        return $this->mergeWithDefaults($socialSettings, $defaultSeo, 'social');
    }
}
