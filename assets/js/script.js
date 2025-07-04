// Sayfa yüklendiğinde çalışacak fonksiyonlar
document.addEventListener('DOMContentLoaded', function() {
    // Slider işlevselliği
    initSlider();
    
    // Mobil menü işlevselliği
    initMobileMenu();
});

// Slider fonksiyonu
function initSlider() {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    
    if (slides.length === 0) return;
    
    // İlk slaytı aktif yap
    slides[0].classList.remove('opacity-0');
    slides[0].classList.add('opacity-100');
    if (dots.length > 0) {
        dots[0].classList.remove('bg-opacity-50');
        dots[0].classList.add('bg-opacity-100');
    }
    
    // Otomatik slayt değiştirme
    function nextSlide() {
        // Aktif slaytı kaldır
        slides[currentSlide].classList.remove('opacity-100');
        slides[currentSlide].classList.add('opacity-0');
        if (dots.length > 0) {
            dots[currentSlide].classList.remove('bg-opacity-100');
            dots[currentSlide].classList.add('bg-opacity-50');
        }
        
        // Sonraki slayta geç
        currentSlide = (currentSlide + 1) % slides.length;
        
        // Yeni slaytı aktif yap
        slides[currentSlide].classList.remove('opacity-0');
        slides[currentSlide].classList.add('opacity-100');
        if (dots.length > 0) {
            dots[currentSlide].classList.remove('bg-opacity-50');
            dots[currentSlide].classList.add('bg-opacity-100');
        }
    }
    
    // Her 5 saniyede bir slayt değiştir
    let slideInterval = setInterval(nextSlide, 5000);
    
    // Noktalara tıklandığında
    if (dots.length > 0) {
        dots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                // Otomatik değiştirmeyi durdur ve yeniden başlat
                clearInterval(slideInterval);
                
                // Aktif slaytı kaldır
                slides[currentSlide].classList.remove('opacity-100');
                slides[currentSlide].classList.add('opacity-0');
                dots[currentSlide].classList.remove('bg-opacity-100');
                dots[currentSlide].classList.add('bg-opacity-50');
                
                // Tıklanan slayta geç
                currentSlide = index;
                
                // Yeni slaytı aktif yap
                slides[currentSlide].classList.remove('opacity-0');
                slides[currentSlide].classList.add('opacity-100');
                dots[currentSlide].classList.remove('bg-opacity-50');
                dots[currentSlide].classList.add('bg-opacity-100');
                
                // Otomatik değiştirmeyi yeniden başlat
                slideInterval = setInterval(nextSlide, 5000);
            });
        });
    }
}

// Mobil menü işlevselliği
function initMobileMenu() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Ürün filtreleme işlevselliği (Daha sonra eklenebilir)
function filterProducts(category) {
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        if (category === 'all' || product.dataset.category === category) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}