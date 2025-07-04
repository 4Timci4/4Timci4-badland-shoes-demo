// Sayfa yüklendiğinde çalışacak fonksiyonlar
document.addEventListener('DOMContentLoaded', function() {
    // Slider işlevselliği
    initSlider();
});

// Slider fonksiyonu
function initSlider() {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    
    // İlk slaytı aktif yap
    if (slides.length > 0) {
        slides[0].classList.add('active');
        if (dots.length > 0) {
            dots[0].classList.add('active');
        }
    }
    
    // Otomatik slayt değiştirme
    function nextSlide() {
        // Aktif slaytı kaldır
        slides[currentSlide].classList.remove('active');
        if (dots.length > 0) {
            dots[currentSlide].classList.remove('active');
        }
        
        // Sonraki slayta geç
        currentSlide = (currentSlide + 1) % slides.length;
        
        // Yeni slaytı aktif yap
        slides[currentSlide].classList.add('active');
        if (dots.length > 0) {
            dots[currentSlide].classList.add('active');
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
                slides[currentSlide].classList.remove('active');
                dots[currentSlide].classList.remove('active');
                
                // Tıklanan slayta geç
                currentSlide = index;
                
                // Yeni slaytı aktif yap
                slides[currentSlide].classList.add('active');
                dots[currentSlide].classList.add('active');
                
                // Otomatik değiştirmeyi yeniden başlat
                slideInterval = setInterval(nextSlide, 5000);
            });
        });
    }
}

// Mobil menü işlevselliği (Daha sonra eklenebilir)
function toggleMobileMenu() {
    const nav = document.querySelector('nav ul');
    nav.classList.toggle('show');
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