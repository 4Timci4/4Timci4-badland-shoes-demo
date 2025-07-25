# Bandland Shoes - E-Ticaret Projesi

Bandland Shoes, PHP tabanlı, modern ve esnek bir e-ticaret platformudur. Bu proje, ayakkabı satışı üzerine odaklanmış olup hem son kullanıcılar için kullanıcı dostu bir alışveriş deneyimi sunar hem de site yöneticileri için kapsamlı bir yönetim paneli içerir.

## Öne Çıkan Özellikler

- **Kapsamlı Yönetim Paneli:** Ürün, kategori, blog, slider, SEO ayarları ve daha fazlasını yönetmek için gelişmiş bir admin paneli.
- **Detaylı Ürün Yönetimi:** Ürünlerin farklı renk ve beden varyantlarını, stok durumunu, fiyatlarını ve resimlerini yönetme imkanı.
- **Servis Odaklı Mimari:** Kod tekrarını önleyen, bakımı kolay ve modüler bir yapı için servis katmanı tasarımı ([`ProductService`](services/ProductService.php:13), [`AuthService`](services/AuthService.php), vb.).
- **Esnek Veritabanı Desteği:** [`DatabaseFactory`](lib/DatabaseFactory.php:8) sayesinde hem **MariaDB/MySQL** hem de **Supabase** desteği. Ortam değişkeni ile kolayca veritabanı türü değiştirilebilir.
- **SEO Yönetimi:** Yönetici panelinden site genelindeki meta etiketleri, sosyal medya kartları, teknik SEO ayarları (sitemap, robots.txt vb.) ve analiz araçları (Google Analytics vb.) yönetimi.
- **Dinamik İçerik Yönetimi:** Hakkımızda, iletişim gibi sayfaların içeriklerini ve blog yazılarını panelden düzenleme.
- **Kullanıcı İşlemleri:** Üye olma, giriş yapma, şifre sıfırlama ve favori ürünler gibi standart kullanıcı özellikleri.
- **Bakım Modu:** Tek bir ayarla siteyi kolayca bakım moduna alma özelliği.

## Teknik Detaylar

- **Backend:** PHP
- **Veritabanı:** MariaDB (varsayılan) veya Supabase
- **Frontend:** HTML, CSS, JavaScript (jQuery)
- **Tasarım Desenleri:** Factory Pattern ([`DatabaseFactory`](lib/DatabaseFactory.php:8)), Singleton (Servis sınıflarında)
- **Mimari Yaklaşım:** Servis Odaklı Mimari (SOA)

## Kurulum

1.  **Projeyi Klonlayın:**
    ```bash
    git clone <proje-repo-adresi>
    cd bandland-shoes-phpp
    ```

2.  **Veritabanını Oluşturun:**
    - Bir MariaDB/MySQL sunucusunda `bandland_shoes` adında yeni bir veritabanı oluşturun.
    - [`sql.sql`](sql.sql) dosyasını bu veritabanına içe aktararak tabloları ve başlangıç verilerini yükleyin.

3.  **Ortam Değişkenlerini Ayarlayın:**
    - `config/` dizini altında `.env` adında bir dosya oluşturun. (Eğer yoksa `.env.example` dosyasını kopyalayabilirsiniz).
    - `.env` dosyasını açın ve veritabanı bağlantı bilgilerinizi girin:
      ```env
      DB_TYPE=mariadb
      DB_HOST=localhost
      DB_NAME=bandland_shoes
      DB_USER=root
      DB_PASS=
      ```
    - Eğer Supabase kullanmak isterseniz:
      ```env
      DB_TYPE=supabase
      SUPABASE_URL=https://proje-id.supabase.co
      SUPABASE_KEY=anon-public-key
      ```

4.  **Web Sunucusunu Yapılandırın:**
    - Proje dizinini Apache veya Nginx gibi bir web sunucusunun hizmet verdiği dizine yerleştirin.
    - Sanal konak (virtual host) ayarlarınızın proje dizinini işaret ettiğinden emin olun.

5.  **Siteyi Ziyaret Edin:**
    - Web tarayıcınızdan sitenin adresine gidin.
    - Yönetici paneline erişmek için `/admin` yolunu kullanın. Varsayılan yönetici bilgileri:
      - **Kullanıcı Adı:** `admin`
      - **Şifre:** `admin` (Veritabanındaki hash'e göre değiştirmeniz gerekebilir, `sql.sql` dosyasındaki `admins` tablosunu kontrol edin.)

## Proje Yapısı

```
.
├── admin/                # Yönetim paneli dosyaları
│   ├── controllers/
│   ├── includes/
│   ├── assets/
│   └── ...
├── api/                  # AJAX ve API endpoint'leri
├── assets/               # CSS, JS, resimler gibi statik dosyalar
├── config/               # Proje yapılandırma dosyaları (bootstrap, db, session)
├── includes/             # Genel header, footer gibi dahil edilen dosyalar
├── lib/                  # Çekirdek kütüphaneler (DatabaseFactory, SecurityManager vb.)
│   ├── adapters/         # Veritabanı adaptörleri (SupabaseAdapter)
│   └── clients/          # Veritabanı istemcileri (MariaDBClient)
├── services/             # İş mantığının bulunduğu servis katmanı
│   └── Product/          # Ürün servisine ait alt servisler
├── user/                 # Kullanıcı profili ve işlemleri
└── ...                   # Ana dizin PHP dosyaları (index.php, products.php vb.)
```

## Katkıda Bulunma

Katkılarınız için lütfen bir "pull request" oluşturun. Yeni özellikler veya hata düzeltmeleri için "issue" açabilirsiniz.