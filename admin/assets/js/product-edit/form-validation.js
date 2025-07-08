/**
 * Form Validation JavaScript Modülü
 * Ürün düzenleme formunun validation ve UX işlemleri
 */

class FormValidation {
    constructor() {
        this.form = document.querySelector('#productEditForm');
        this.nameInput = document.querySelector('#name');
        this.descriptionInput = document.querySelector('#description');
        this.priceInput = document.querySelector('#base_price');
        this.categoryCheckboxes = document.querySelectorAll('input[name="category_ids[]"]');
        this.featuresInput = document.querySelector('#features');
        this.isFeaturedCheckbox = document.querySelector('#is_featured');
        
        this.init();
    }

    init() {
        if (!this.form) return;
        
        this.addEventListeners();
        this.initAutoResize();
        this.initKeyboardShortcuts();
        this.initBeforeUnloadWarning();
        this.detectChanges();
    }

    addEventListeners() {
        // Real-time validation
        if (this.nameInput) {
            this.nameInput.addEventListener('blur', () => {
                this.validateField(this.nameInput, this.nameInput.value.trim().length >= 2, 'Ürün adı en az 2 karakter olmalıdır.');
            });
        }

        if (this.descriptionInput) {
            this.descriptionInput.addEventListener('blur', () => {
                this.validateField(this.descriptionInput, this.descriptionInput.value.trim().length >= 10, 'Açıklama en az 10 karakter olmalıdır.');
            });
        }

        if (this.priceInput) {
            this.priceInput.addEventListener('blur', () => {
                const price = parseFloat(this.priceInput.value);
                this.validateField(this.priceInput, price > 0, 'Geçerli bir fiyat giriniz.');
            });
        }

        // Form alanlarını izle
        const watchedElements = [
            this.nameInput, 
            this.descriptionInput, 
            this.priceInput, 
            this.featuresInput,
            this.isFeaturedCheckbox,
            ...this.categoryCheckboxes
        ].filter(element => element);

        watchedElements.forEach(element => {
            element.addEventListener('input', () => this.detectChanges());
            element.addEventListener('change', () => this.detectChanges());
        });

        // Form submission loading
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleFormSubmission(e));
        }
    }

    validateField(field, condition, message) {
        const errorElement = field.parentNode.querySelector('.error-message');
        
        if (condition) {
            field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            field.classList.add('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
            if (errorElement) errorElement.remove();
        } else {
            field.classList.remove('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
            field.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            
            if (!errorElement) {
                const error = document.createElement('p');
                error.className = 'error-message text-red-600 text-xs mt-1';
                error.textContent = message;
                field.parentNode.appendChild(error);
            }
        }
    }

    detectChanges() {
        // Bu fonksiyonu şimdilik devre dışı bırakıyoruz
        return true;
    }

    handleFormSubmission(event) {
        const submitBtn = event.submitter;
        if (!submitBtn) return;

        const btnText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Kaydediliyor...';
        
        // Re-enable after timeout as fallback
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = btnText;
        }, 5000);

        // Mark form as submitted
        this.formSubmitted = true;
    }

    initAutoResize() {
        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            
            // Initial resize
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        });
    }

    initKeyboardShortcuts() {
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                if (this.detectChanges()) {
                    const continueBtn = this.form.querySelector('button[name="save_and_continue"]');
                    if (continueBtn) {
                        continueBtn.click();
                    }
                }
            }
        });
    }

    initBeforeUnloadWarning() {
        // Sayfa terk etme uyarısı
        this.formSubmitted = false;

        window.addEventListener('beforeunload', (e) => {
            if (this.detectChanges() && !this.formSubmitted) {
                e.preventDefault();
                e.returnValue = 'Kaydedilmemiş değişiklikler var. Sayfadan ayrılmak istediğinizden emin misiniz?';
            }
        });
    }

    // Utility methods
    showFieldError(field, message) {
        this.validateField(field, false, message);
    }

    clearFieldError(field) {
        this.validateField(field, true, '');
    }

    validateForm() {
        let isValid = true;

        // Name validation
        if (this.nameInput && this.nameInput.value.trim().length < 2) {
            this.showFieldError(this.nameInput, 'Ürün adı en az 2 karakter olmalıdır.');
            isValid = false;
        }

        // Description validation
        if (this.descriptionInput && this.descriptionInput.value.trim().length < 10) {
            this.showFieldError(this.descriptionInput, 'Açıklama en az 10 karakter olmalıdır.');
            isValid = false;
        }

        // Price validation
        if (this.priceInput) {
            const price = parseFloat(this.priceInput.value);
            if (isNaN(price) || price <= 0) {
                this.showFieldError(this.priceInput, 'Geçerli bir fiyat giriniz.');
                isValid = false;
            }
        }

        // Category validation
        const checkedCategories = Array.from(this.categoryCheckboxes).filter(cb => cb.checked);
        if (checkedCategories.length === 0) {
            // Show category error (could be implemented)
            isValid = false;
        }

        return isValid;
    }

    resetForm() {
        // Clear all error messages
        document.querySelectorAll('.error-message').forEach(error => error.remove());
        
        // Reset field styles
        document.querySelectorAll('.border-red-300, .border-green-300').forEach(field => {
            field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            field.classList.remove('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
        });
    }

    // Public API
    isFormValid() {
        return this.validateForm();
    }

    markFormSubmitted() {
        this.formSubmitted = true;
    }
}

// Global access için
window.FormValidation = FormValidation;
