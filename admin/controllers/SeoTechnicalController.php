<?php
/**
 * SEO Technical Settings Controller
 * Teknik SEO ayarlarını yönetir
 */

require_once 'BaseSeoController.php';

class SeoTechnicalController extends BaseSeoController {
    
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_technical_seo_settings') {
            $this->updateTechnicalSettings();
        }
    }

    /**
     * Teknik SEO ayarlarını güncelle
     */
    private function updateTechnicalSettings() {
        $technicalKeys = [
            'canonical_enabled', 
            'sitemap_enabled', 
            'schema_enabled', 
            'breadcrumbs_enabled', 
            'amp_enabled'
        ];

        $this->updateSettingsAndRespond(
            $technicalKeys,
            'technical',
            'Teknik SEO ayarları başarıyla güncellendi.',
            'Teknik SEO ayarları güncellenirken bir hata oluştu.',
            'seo-settings.php#technical'
        );
    }

    /**
     * View için gerekli verileri hazırla
     */
    public function getViewData() {
        $technicalSettings = $this->settingsService->getSeoSettingsByType('technical');
        $defaultSeo = $this->settingsService->getDefaultSeoSettings();
        
        return $this->mergeWithDefaults($technicalSettings, $defaultSeo, 'technical');
    }
}
