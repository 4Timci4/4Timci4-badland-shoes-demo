/**
 * Ürün Düzenleme Ana JavaScript Modülü
 * Tüm diğer modülleri koordine eder
 */

class ProductEditMain {
    constructor(productId, productBasePrice) {
        this.productId = productId;
        this.productBasePrice = productBasePrice;
        
        // Global değişken olarak da set et (geriye uyumluluk için)
        window.productBasePrice = productBasePrice;
        
        this.init();
    }

    init() {
        // DOM yüklendiğinde modülleri başlat
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeModules());
        } else {
            this.initializeModules();
        }
    }

    initializeModules() {
        try {
            // Form validation modülünü başlat
            if (window.FormValidation) {
                this.formValidation = new window.FormValidation();
                console.log('✅ Form Validation modülü başlatıldı');
            } else {
                console.warn('⚠️ FormValidation sınıfı bulunamadı');
            }

            // Variant management modülünü başlat
            if (window.VariantManagement && this.productId) {
                this.variantManagement = new window.VariantManagement(this.productId);
                console.log('✅ Variant Management modülü başlatıldı');
            } else {
                console.warn('⚠️ VariantManagement sınıfı bulunamadı veya productId eksik');
            }

            console.log('🚀 Product Edit modülleri başarıyla yüklendi');

        } catch (error) {
            console.error('❌ Modül başlatma hatası:', error);
        }
    }

    // Utility methods
    getProductId() {
        return this.productId;
    }

    getFormValidation() {
        return this.formValidation;
    }

    getVariantManagement() {
        return this.variantManagement;
    }

    // Public API methods
    validateForm() {
        return this.formValidation ? this.formValidation.isFormValid() : true;
    }

    addVariant(variantData) {
        if (this.variantManagement) {
            return this.variantManagement.addVariant(variantData);
        }
        console.warn('Variant Management modülü yüklenmemiş');
        return false;
    }

    showNotification(message, type = 'info') {
        if (this.variantManagement) {
            this.variantManagement.showNotification(message, type);
        } else {
            // Fallback notification
            alert(message);
        }
    }
}

// Global access için
window.ProductEditMain = ProductEditMain;

// Eski global fonksiyonları koruyalım (geriye uyumluluk için)
window.initProductEdit = function(productId, productBasePrice) {
    return new ProductEditMain(productId, productBasePrice);
};
