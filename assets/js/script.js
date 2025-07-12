
document.addEventListener('DOMContentLoaded', function() {
    initSlider();
});

function initSlider() {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    let slideInterval;

    if (slides.length <= 1) {
        if (slides.length === 1) {
            slides[0].classList.remove('opacity-0');
            slides[0].classList.add('opacity-100');
        }
        if (dots.length > 0) {
            dots.forEach(d => d.style.display = 'none');
        }
        return;
    }

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('opacity-100', 'z-10');
            slide.classList.add('opacity-0', 'z-0');
            if (dots[i]) {
                dots[i].classList.remove('bg-opacity-100');
                dots[i].classList.add('bg-opacity-50');
            }
        });

        slides[index].classList.remove('opacity-0', 'z-0');
        slides[index].classList.add('opacity-100', 'z-10');
        if (dots[index]) {
            dots[index].classList.remove('bg-opacity-50');
            dots[index].classList.add('bg-opacity-100');
        }
        currentSlide = index;
    }

    function nextSlide() {
        showSlide((currentSlide + 1) % slides.length);
    }

    function startSlider() {
        stopSlider();
        slideInterval = setInterval(nextSlide, 5000);
    }

    function stopSlider() {
        clearInterval(slideInterval);
    }

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            startSlider();
        });
    });

    showSlide(0);
    startSlider();
}

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
