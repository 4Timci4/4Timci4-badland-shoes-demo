<?php
/**
 * SEO Settings Main View
 * SEO ayarları ana görünümü - tab navigasyonu ve içerik yönetimi
 */

// Controller'ları dahil et
require_once 'controllers/SeoMetaController.php';
require_once 'controllers/SeoSocialController.php'; 
require_once 'controllers/SeoAnalyticsController.php';
require_once 'controllers/SeoTechnicalController.php';

// Controller'ları başlat ve istekleri işle
$metaController = new SeoMetaController();
$socialController = new SeoSocialController();
$analyticsController = new SeoAnalyticsController();
$technicalController = new SeoTechnicalController();

// POST isteklerini ilgili controller'lara yönlendir
$metaController->handleRequest();
$socialController->handleRequest();
$analyticsController->handleRequest();
$technicalController->handleRequest();

// View verileri
$metaSettings = $metaController->getViewData();
$socialSettings = $socialController->getViewData();
$analyticsSettings = $analyticsController->getViewData();
$technicalSettings = $technicalController->getViewData();
?>

<!-- SEO Settings Content -->
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">SEO Ayarları</h1>
                <p class="text-gray-600">Arama motoru optimizasyonu ve sosyal medya ayarlarını yönetin</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6">
                <button onclick="showTab('meta')" 
                        id="tab-meta" 
                        class="tab-button py-4 px-1 border-b-2 border-primary-500 font-medium text-sm text-primary-600">
                    <i class="fas fa-tags mr-2"></i>
                    Meta Ayarları
                </button>
                <button onclick="showTab('social')" 
                        id="tab-social" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-share-alt mr-2"></i>
                    Sosyal Medya
                </button>
                <button onclick="showTab('analytics')" 
                        id="tab-analytics" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-chart-line mr-2"></i>
                    Analytics
                </button>
                <button onclick="showTab('technical')" 
                        id="tab-technical" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-cogs mr-2"></i>
                    Teknik SEO
                </button>
            </nav>
        </div>

        <!-- Meta Settings Tab -->
        <div id="content-meta" class="tab-content">
            <?php include 'meta-settings.php'; ?>
        </div>

        <!-- Social Media Settings Tab -->
        <div id="content-social" class="tab-content hidden">
            <?php include 'social-settings.php'; ?>
        </div>

        <!-- Analytics Settings Tab -->
        <div id="content-analytics" class="tab-content hidden">
            <?php include 'analytics-settings.php'; ?>
        </div>

        <!-- Technical SEO Settings Tab -->
        <div id="content-technical" class="tab-content hidden">
            <?php include 'technical-settings.php'; ?>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active styles from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-primary-500', 'text-primary-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active styles to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.remove('border-transparent', 'text-gray-500');
    activeButton.classList.add('border-primary-500', 'text-primary-600');
}

// Check URL hash to show correct tab
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.substring(1);
    if (['social', 'analytics', 'technical'].includes(hash)) {
        showTab(hash);
    }
});
</script>
