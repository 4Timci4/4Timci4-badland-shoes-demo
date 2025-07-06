<?php
/**
 * SEO Meta Settings Controller
 * Meta etiketleri ve temel SEO ayarlarını yönetir
 */

require_once 'BaseSeoController.php';

class SeoMetaController extends BaseSeoController {
    
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_meta_settings') {
            $this->updateMetaSettings();
        }
    }

    /**
     * Meta ayarlarını güncelle
     */
    private function updateMetaSettings() {
        $metaKeys = [
            'default_title', 
            'title_separator', 
            'default_description', 
            'default_keywords', 
            'author', 
            'robots'
        ];

        $this->updateSettingsAndRespond(
            $metaKeys,
            'meta',
            'Meta ayarları başarıyla güncellendi.',
            'Meta ayarları güncellenirken bir hata oluştu.',
            'seo-settings.php'
        );
    }

    /**
     * View için gerekli verileri hazırla
     */
    public function getViewData() {
        $metaSettings = $this->settingsService->getSeoSettingsByType('meta');
        $defaultSeo = $this->settingsService->getDefaultSeoSettings();
        
        return $this->mergeWithDefaults($metaSettings, $defaultSeo, 'meta');
    }
}
