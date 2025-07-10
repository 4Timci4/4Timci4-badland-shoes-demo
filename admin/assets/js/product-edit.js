document.addEventListener('DOMContentLoaded', () => {
    const wizard = new ProductEditWizard();
    wizard.init();
});

class ProductEditWizard {
    constructor() {
        this.currentStep = 1;
        this.steps = document.querySelectorAll('.wizard-step');
        this.nextBtn = document.getElementById('next-step-btn');
        this.prevBtn = document.getElementById('prev-step-btn');
        this.saveBtn = document.getElementById('save-product-btn');
        this.progressBar = document.getElementById('wizard-progress');
        this.stepTitle = document.getElementById('wizard-step-title');
        this.currentStepDisplay = document.getElementById('wizard-current-step');
        this.totalStepsDisplay = document.getElementById('wizard-total-steps');

        const isAddMode = window.location.pathname.includes('product-add.php');

        if (isAddMode) {
            this.totalSteps = 2;
            this.stepTitles = [
                "Adım 1: Temel Bilgiler",
                "Adım 2: Kategori & Durum"
            ];
        } else {
            this.totalSteps = 4;
            this.stepTitles = [
                "Adım 1: Temel Bilgiler",
                "Adım 2: Kategori & Durum",
                "Adım 3: Varyant Yönetimi",
                "Adım 4: Görsel Yönetimi"
            ];
        }
    }

    init() {
        this.updateButtons();
        this.updateProgress();
        this.showStep(this.currentStep);

        if (this.nextBtn) this.nextBtn.addEventListener('click', () => this.nextStep());
        if (this.prevBtn) this.prevBtn.addEventListener('click', () => this.prevStep());
        
        const addVariantBtn = document.getElementById('add-variant-btn');
        if (addVariantBtn) {
            addVariantBtn.addEventListener('click', () => this.addVariant());
        }
    }

    _showError(field, message) {
        field.classList.add('border-red-500', 'focus:border-red-500');
        field.classList.remove('border-gray-200', 'focus:border-primary-500');
        
        let errorElement = field.parentElement.querySelector('.error-message');
        if (!errorElement) {
            errorElement = document.createElement('p');
            errorElement.className = 'error-message text-red-600 text-xs mt-1';
            field.parentElement.appendChild(errorElement);
        }
        errorElement.textContent = message;
    }

    _clearError(field) {
        field.classList.remove('border-red-500', 'focus:border-red-500');
        field.classList.add('border-gray-200', 'focus:border-primary-500');
        
        const errorElement = field.parentElement.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
    }

    validateStep(stepNumber) {
        switch (stepNumber) {
            case 1:
                return this.validateBasicInfo();
            case 2:
                return this.validateCategoryAndPricing();
            // Diğer adımlar için doğrulamalar eklenecek
            default:
                return true;
        }
    }

    validateBasicInfo() {
        const nameInput = document.getElementById('name');
        const descriptionInput = document.getElementById('description');
        const name = nameInput.value.trim();
        const description = descriptionInput.value.trim();
        let isValid = true;

        if (name === '') {
            this._showError(nameInput, 'Ürün adı zorunludur.');
            isValid = false;
        } else {
            this._clearError(nameInput);
        }

        if (description === '') {
            this._showError(descriptionInput, 'Ürün açıklaması zorunludur.');
            isValid = false;
        } else {
            this._clearError(descriptionInput);
        }

        return isValid;
    }

    validateCategoryAndPricing() {
        const categoryCheckboxes = document.querySelectorAll('input[name="category_ids[]"]');
        const anyCategoryChecked = Array.from(categoryCheckboxes).some(cb => cb.checked);
        const categoryContainer = document.querySelector('#category-selection-container');
        let isValid = true;

        if (!anyCategoryChecked) {
            this._showError(categoryContainer, 'En az bir kategori seçmelisiniz.');
            isValid = false;
        } else {
            this._clearError(categoryContainer);
        }
        
        // Cinsiyet doğrulaması da eklenebilir
        const genderCheckboxes = document.querySelectorAll('input[name="gender_ids[]"]');
        const anyGenderChecked = Array.from(genderCheckboxes).some(cb => cb.checked);
        const genderContainer = document.querySelector('#gender-selection-container');

        if (!anyGenderChecked) {
            this._showError(genderContainer, 'En az bir cinsiyet seçmelisiniz.');
            isValid = false;
        } else {
            this._clearError(genderContainer);
        }


        return isValid;
    }

    nextStep() {
        if (this.validateStep(this.currentStep)) {
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.showStep(this.currentStep);
                this.updateButtons();
                this.updateProgress();
            }
        }
    }

    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.showStep(this.currentStep);
            this.updateButtons();
            this.updateProgress();
        }
    }

    showStep(stepNumber) {
        this.steps.forEach(step => {
            if (parseInt(step.dataset.step) === stepNumber) {
                step.classList.remove('hidden');
            } else {
                step.classList.add('hidden');
            }
        });
        this.stepTitle.textContent = this.stepTitles[stepNumber - 1];
        this.currentStepDisplay.textContent = stepNumber;
    }

    updateButtons() {
        this.prevBtn.disabled = this.currentStep === 1;
        this.nextBtn.classList.toggle('hidden', this.currentStep === this.totalSteps);
        this.saveBtn.classList.toggle('hidden', this.currentStep !== this.totalSteps);
    }

    updateProgress() {
        // Adım sayısına göre ilerleme yüzdesini güncelle
        const progressPercentage = (this.currentStep / this.totalSteps) * 100;
        this.progressBar.style.width = `${progressPercentage}%`;

        // Adım başlığını ve sayısını da güncelle
        if (this.stepTitles[this.currentStep - 1]) {
            this.stepTitle.textContent = this.stepTitles[this.currentStep - 1];
        }
        this.currentStepDisplay.textContent = this.currentStep;
        if (this.totalStepsDisplay) {
            this.totalStepsDisplay.textContent = this.totalSteps;
        }
    }
    async addVariant() {
        const colorSelect = document.getElementById('new-variant-color');
        const sizeSelect = document.getElementById('new-variant-size');
        const stockInput = document.getElementById('new-variant-stock');
        const activeCheckbox = document.getElementById('new-variant-active');
        
        const colorId = colorSelect.value;
        const sizeId = sizeSelect.value;
        const stock = stockInput.value;
        const isActive = activeCheckbox.checked;
        const productId = VENDOR_DATA.productId;
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        if (!colorId || !sizeId) {
            alert('Lütfen renk ve beden seçin.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_variant');
        formData.append('product_id', productId);
        formData.append('color_id', colorId);
        formData.append('size_id', sizeId);
        formData.append('stock', stock);
        formData.append('is_active', isActive);
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch('product-edit.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.prependVariantToTable(result.variant);
                // Formu temizle
                colorSelect.value = '';
                sizeSelect.value = '';
                stockInput.value = '0';
                activeCheckbox.checked = true;
                // "Varyant yok" mesajını gizle
                const noVariantMessage = document.querySelector('.text-center.py-8');
                if(noVariantMessage) noVariantMessage.style.display = 'none';

            } else {
                alert('Hata: ' + result.error);
            }
        } catch (error) {
            console.error('Varyant eklenirken bir hata oluştu:', error);
            alert('Varyant eklenirken bir ağ hatası oluştu.');
        }
    }

    prependVariantToTable(variant) {
        const tableBody = document.querySelector('.min-w-full.divide-y.divide-gray-200 tbody');
        if (!tableBody) return;

        const newRow = document.createElement('tr');
        newRow.className = 'hover:bg-gray-50';
        newRow.dataset.variantId = variant.id;
        newRow.dataset.colorId = variant.color_id;

        newRow.innerHTML = `
            <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="w-6 h-6 rounded-full border border-gray-300" style="background-color: ${variant.color_hex ?? '#cccccc'}"></div>
                    <span class="ml-3 text-sm text-gray-900">${variant.color_name ?? 'Renk Yok'}</span>
                </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${variant.size_value ?? 'Beden Yok'} ${variant.size_type ?? ''}
                </span>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-900 font-mono">${variant.sku}</span>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                <input type="number" value="${variant.stock_quantity}" min="0" class="variant-stock w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500" data-variant-id="${variant.id}">
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                <label class="inline-flex items-center">
                    <input type="checkbox" ${variant.is_active ? 'checked' : ''} class="variant-active form-checkbox h-4 w-4 text-primary-600" data-variant-id="${variant.id}">
                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                </label>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                <button type="button" class="save-variant-btn text-green-600 hover:text-green-900 mr-3" data-variant-id="${variant.id}"><i class="fas fa-save"></i></button>
                <button type="button" class="delete-variant-btn text-red-600 hover:text-red-900" data-variant-id="${variant.id}"><i class="fas fa-trash"></i></button>
            </td>
        `;

        tableBody.prepend(newRow);
    }
}