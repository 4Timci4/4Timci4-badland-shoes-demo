# Schön Ayakkabı Mağazası Web Sitesi

Bu proje, "Schön" adlı online ayakkabı mağazası için PHP tabanlı bir web sitesidir.

## Kullanılan Teknolojiler

- **PHP** - Sunucu taraflı işlemler için
- **HTML** - Sayfa yapısı için
- **Tailwind CSS** - Utility-first CSS framework (CDN üzerinden)
- **JavaScript** - İnteraktif elementler için
- **Font Awesome** - İkonlar için (CDN üzerinden)

## Dosya Yapısı

### Ana Sayfalar
- **index.php** - Ana sayfa (slider ve öne çıkan ürünler)
- **products.php** - Ürün listeleme sayfası
- **product-details.php** - Ürün detay sayfası
- **blog.php** - Blog listeleme sayfası
- **blog-detail.php** - Blog yazı detay sayfası
- **about.php** - Hakkımızda sayfası
- **contact.php** - İletişim bilgileri ve formu

### Yeniden Kullanılabilir Bileşenler
- **includes/header.php** - Üst menü ve logo
- **includes/footer.php** - Alt bilgi

### CSS Framework
- **Tailwind CSS** - CDN üzerinden yüklenen utility-first CSS framework
- Özel renk paleti (primary: #e91e63, secondary: #333, light: #f4f4f4, dark: #222)
- Responsive tasarım breakpoints'leri
- Hover ve transition efektleri

### JavaScript Dosyaları
- **assets/js/script.js** - İnteraktif elementler için JavaScript

### Görsel Dosyaları
- **assets/images/mt-logo.png** - Marka logosu

## Özellikler

- **Responsive tasarım** (mobil, tablet ve masaüstü)
- **Ana sayfada slider** (JavaScript ile otomatik geçiş)
- **Ürün filtreleme** (kategori bazlı)
- **İletişim formu** (PHP ile form işleme)
- **Blog makaleleri** (dinamik içerik)
- **Modüler kod yapısı** (header, footer bileşenleri)
- **Tailwind CSS** ile utility-first yaklaşım
- **Mobil menü** (hamburger menü)
- **Hover efektleri** ve **smooth transitions**

## Kurulum

1. Projeyi bir PHP sunucusuna yükleyin
2. Web tarayıcısından erişim sağlayın

## Notlar

- Proje, PHP 7.0 ve üzeri sürümlerde test edilmiştir
- Tarayıcı uyumluluğu: Chrome, Firefox, Safari, Edge