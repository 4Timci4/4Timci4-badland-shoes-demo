/**
 * Cookie Manager - Çerez yönetimi için yardımcı fonksiyonlar
 */

class CookieManager {
    /**
     * Çerez ayarlama
     * @param {string} name - Çerez adı
     * @param {string} value - Çerez değeri
     * @param {number} days - Geçerlilik süresi (gün)
     */
    static setCookie(name, value, days = 30) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }

    /**
     * Çerez okuma
     * @param {string} name - Çerez adı
     * @returns {string|null} - Çerez değeri veya null
     */
    static getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
        return null;
    }

    /**
     * Çerez silme
     * @param {string} name - Çerez adı
     */
    static deleteCookie(name) {
        document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }

    /**
     * JSON formatında çerez ayarlama
     * @param {string} name - Çerez adı
     * @param {object} value - JSON olarak saklanacak değer
     * @param {number} days - Geçerlilik süresi (gün)
     */
    static setJSONCookie(name, value, days = 30) {
        this.setCookie(name, JSON.stringify(value), days);
    }

    /**
     * JSON formatında çerez okuma
     * @param {string} name - Çerez adı
     * @returns {object|null} - Parse edilmiş JSON değeri veya null
     */
    static getJSONCookie(name) {
        const value = this.getCookie(name);
        if (value) {
            try {
                return JSON.parse(value);
            } catch (e) {
                console.error('JSON parse hatası:', e);
                return null;
            }
        }
        return null;
    }
}

/**
 * Favorites Manager - Favori ürünleri yönetmek için özel sınıf
 */
class FavoritesManager {
    static COOKIE_NAME = 'user_favorites';
    static COOKIE_DURATION = 30; // 30 gün

    /**
     * Tüm favorileri getir
     * @returns {Array} - Favori varyant ID'leri dizisi
     */
    static getFavorites() {
        const favorites = CookieManager.getJSONCookie(this.COOKIE_NAME);
        return favorites || [];
    }

    /**
     * Favori ekleme
     * @param {number} variantId - Varyant ID
     * @param {number|null} colorId - Renk ID (opsiyonel)
     * @returns {boolean} - İşlem başarılı mı
     */
    static addFavorite(variantId, colorId = null) {
        if (!variantId) return false;

        const favorites = this.getFavorites();
        const favoriteItem = {
            variantId: parseInt(variantId),
            colorId: colorId ? parseInt(colorId) : null,
            addedAt: new Date().toISOString()
        };

        // Aynı varyant zaten var mı kontrol et
        const existingIndex = favorites.findIndex(fav => fav.variantId === favoriteItem.variantId);
        
        if (existingIndex === -1) {
            favorites.push(favoriteItem);
            CookieManager.setJSONCookie(this.COOKIE_NAME, favorites, this.COOKIE_DURATION);
            return true;
        }
        
        return false; // Zaten favorilerde
    }

    /**
     * Favori çıkarma
     * @param {number} variantId - Varyant ID
     * @returns {boolean} - İşlem başarılı mı
     */
    static removeFavorite(variantId) {
        if (!variantId) return false;

        const favorites = this.getFavorites();
        const filteredFavorites = favorites.filter(fav => fav.variantId !== parseInt(variantId));
        
        if (filteredFavorites.length !== favorites.length) {
            CookieManager.setJSONCookie(this.COOKIE_NAME, filteredFavorites, this.COOKIE_DURATION);
            return true;
        }
        
        return false; // Favorilerde bulunamadı
    }

    /**
     * Favori kontrolü
     * @param {number} variantId - Varyant ID
     * @returns {boolean} - Favorilerde var mı
     */
    static isFavorite(variantId) {
        if (!variantId) return false;
        
        const favorites = this.getFavorites();
        return favorites.some(fav => fav.variantId === parseInt(variantId));
    }

    /**
     * Favori sayısını getir
     * @returns {number} - Toplam favori sayısı
     */
    static getFavoriteCount() {
        return this.getFavorites().length;
    }

    /**
     * Sadece varyant ID'lerini getir (geriye uyumluluk için)
     * @returns {Array} - Varyant ID'leri dizisi
     */
    static getFavoriteVariantIds() {
        const favorites = this.getFavorites();
        return favorites.map(fav => fav.variantId);
    }

    /**
     * Tüm favorileri temizle
     */
    static clearFavorites() {
        CookieManager.deleteCookie(this.COOKIE_NAME);
    }

    /**
     * Favori toggle işlemi
     * @param {number} variantId - Varyant ID
     * @param {number|null} colorId - Renk ID (opsiyonel)
     * @returns {object} - İşlem sonucu {success: boolean, action: 'add'|'remove', message: string}
     */
    static toggleFavorite(variantId, colorId = null) {
        if (!variantId) {
            return {
                success: false,
                action: null,
                message: 'Geçersiz varyant ID'
            };
        }

        const isFav = this.isFavorite(variantId);
        
        if (isFav) {
            const success = this.removeFavorite(variantId);
            return {
                success: success,
                action: 'remove',
                message: success ? 'Ürün favorilerden kaldırıldı' : 'Favori kaldırma işlemi başarısız'
            };
        } else {
            const success = this.addFavorite(variantId, colorId);
            return {
                success: success,
                action: 'add',
                message: success ? 'Ürün favorilere eklendi' : 'Ürün zaten favorilerinizde'
            };
        }
    }
}

// Global erişim için window nesnesine ekle
window.CookieManager = CookieManager;
window.FavoritesManager = FavoritesManager;