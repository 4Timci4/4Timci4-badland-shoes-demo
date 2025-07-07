# Bandland Shoes PHP Projesi - Cline Geliştirme Kuralları

Bu belge, "Bandland Shoes" projesinde geliştirme yaparken Cline'ın uyması gereken kuralları ve standartları tanımlar. Amaç, kod kalitesini, tutarlılığını ve sürdürülebilirliğini sağlamaktır.

## 1. Mimari ve Kod Yapısı

- **Servis Katmanı Zorunluluğu:** Tüm iş mantığı (veritabanı işlemleri, harici API çağrıları, karmaşık hesaplamalar) mutlaka `services/` klasörü altındaki ilgili servis sınıflarında yer almalıdır. PHP arayüz dosyaları (`.php`) doğrudan veritabanı sorgusu yapmamalıdır.
- **Singleton Deseni:** Servis sınıflarına erişim için `product_service()` gibi dosyanın sonunda tanımlanmış singleton fonksiyonları kullanılmalıdır. Bu, kaynakların verimli kullanılmasını sağlar.
- **Veritabanı Erişimi:** Tüm veritabanı etkileşimleri, yalnızca `lib/SupabaseClient.php` içinde tanımlanan `supabase()` singleton fonksiyonu aracılığıyla yapılmalıdır.
- **Yapılandırma:** Proje yapılandırması `config/` klasöründeki dosyalardan yönetilir. Hassas bilgiler ve ortama özgü değişkenler için `.env` dosyası yapısı korunmalıdır.
- **API Uç Noktaları:** Yeni API uç noktaları `api/` klasörü altında oluşturulmalı, `Content-Type: application/json` başlığını ayarlamalı ve yanıtları standart bir JSON formatında (başarı veya hata durumu için) döndürmelidir.

## 2. Güvenlik Kuralları

- **XSS Koruması (Zorunlu):** Veritabanından veya kullanıcıdan gelen ve ekrana yazdırılacak **tüm** veriler, `htmlspecialchars()` fonksiyonundan geçirilmelidir. İstisna yoktur.
- **CSRF Koruması (Zorunlu):** `POST`, `PATCH`, `DELETE` gibi durum değiştiren tüm form gönderimleri, `admin/config/auth.php` içindeki `generate_csrf_token()` ve `verify_csrf_token()` fonksiyonları kullanılarak CSRF saldırılarına karşı korunmalıdır.
- **SQL Enjeksiyonu:** Doğrudan SQL sorguları yerine Supabase'in PostgREST arayüzü tercih edilmelidir. Ham SQL gerektiğinde, `executeRawSql` ve `prepareSql` metodları dikkatli kullanılmalıdır.
- **Yetkilendirme:** Admin paneli içindeki tüm sayfalara erişim, sayfanın en başında `check_admin_auth()` fonksiyonu çağrılarak kontrol edilmelidir.

## 3. Kodlama Standartları ve Stil

- **Dil:** Tüm kod, yorumlar, değişken isimleri ve kullanıcıya dönük metinler **Türkçe** olmalıdır.
- **Frontend:** Tüm arayüz bileşenleri (hem admin paneli hem de kullanıcı tarafı) **Tailwind CSS** kullanılarak oluşturulmalıdır. Mevcut tasarım diline ve bileşen yapısına sadık kalınmalıdır.
- **Şablonlama:** Arayüz sayfaları, `includes/header.php` ve `includes/footer.php` dosyalarını kullanarak tutarlı bir yapı izlemelidir.
- **Hata Yönetimi:** Kod, `try-catch` blokları ile hataları yakalamalı ve `error_log()` fonksiyonu ile detaylı hata mesajlarını kaydetmelidir. Kullanıcıya asla teknik hata detayları gösterilmemelidir.

## 4. Veritabanı ve Veri Yönetimi

- **Veri Bütünlüğü:** Bir ana kayıt silindiğinde (örneğin bir ürün), ona bağlı tüm alt kayıtların (varyantlar, resimler vb.) ilgili servis metodu içinde manuel olarak silindiğinden emin olunmalıdır (`deleteProduct` metodundaki gibi).
- **Optimizasyon:** Veri çekerken, özellikle döngü içinde tekrar tekrar sorgu yapmaktan (N+1 problemi) kaçınılmalıdır. Supabase'in `select=*,ilişkili_tablo(*)` gibi yetenekleri kullanılarak ilişkili veriler tek bir sorguda çekilmelidir.

## 5. Database Abstraction Layer Kuralları (YENİ)

- **Database Factory Kullanımı:** Tüm yeni geliştirmeler `database()` veya `supabase()` singleton fonksiyonlarını kullanmalıdır. Doğrudan `SupabaseClient` veya `MariaDBClient` oluşturmak yasaktır.
- **Interface Metodları Tercihi:** Mümkün olduğunca `DatabaseInterface`'in metodlarını (`select()`, `insert()`, `update()`, `delete()`) kullanın. Ham `request()` çağrıları sadece gerekli durumlarda yapılmalıdır.
- **Database Bağımsızlığı:** Yeni kod yazarken, hem Supabase hem de MariaDB ile uyumlu olacak şekilde geliştirme yapılmalıdır. Database'e özgü syntax'tan kaçınılmalıdır.
- **Transaction Kullanımı:** MariaDB kullanırken büyük veri işlemleri için `beginTransaction()`, `commit()`, `rollback()` metodları kullanılmalıdır.
- **Cache Yönetimi:** Performans kritik işlemlerde `clearCache()` metodunu kullanarak cache yönetimi yapılmalıdır.

## 6. Geleceğe Yönelik Geliştirmeler

- **Bağımlılık Yönetimi:** Projeye yeni bir harici kütüphane ekleneceği zaman, bu işlem **Composer** kullanılarak yapılmalıdır. `require_once` ile manuel kütüphane eklemesi yapılmamalıdır.
- **Yönlendirme (Routing):** Yeni sayfalar oluşturulurken, projenin mevcut dosya tabanlı yönlendirme yapısı (`/sayfa.php`) takip edilmelidir. İleride bir yönlendirme kütüphanesi eklenirse bu kural güncellenecektir.
- **Database Migration:** MariaDB kullanırken schema değişiklikleri için `database/` klasöründe migration dosyaları oluşturulmalıdır.
