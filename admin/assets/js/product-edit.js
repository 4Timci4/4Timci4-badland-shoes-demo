document.addEventListener('DOMContentLoaded', () => {
    const wizard = new ProductEditWizard();
    wizard.init();
});

class ProductEditWizard {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        this.steps = document.querySelectorAll('.wizard-step');
        this.nextBtn = document.getElementById('next-step-btn');
        this.prevBtn = document.getElementById('prev-step-btn');
        this.saveBtn = document.getElementById('save-product-btn');
        this.progressBar = document.getElementById('wizard-progress');
        this.stepTitle = document.getElementById('wizard-step-title');
        this.currentStepDisplay = document.getElementById('wizard-current-step');
        this.stepTitles = [
            "Adım 1: Temel Bilgiler",
            "Adım 2: Kategori & Fiyat",
            "Adım 3: Varyant Yönetimi",
            "Adım 4: Görsel Yönetimi"
        ];
    }

    init() {
        this.updateButtons();
        this.updateProgress();
        this.showStep(this.currentStep);

        this.nextBtn.addEventListener('click', () => this.nextStep());
        this.prevBtn.addEventListener('click', () => this.prevStep());
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
        const name = document.getElementById('name').value.trim();
        const description = document.getElementById('description').value.trim();
        let isValid = true;

        if (name === '') {
            alert('Ürün adı zorunludur.');
            isValid = false;
        }

        if (description === '') {
            alert('Ürün açıklaması zorunludur.');
            isValid = false;
        }

        return isValid;
    }

    validateCategoryAndPricing() {
        const categoryCheckboxes = document.querySelectorAll('input[name="category_ids[]"]:checked');
        const price = document.getElementById('base_price').value;
        let isValid = true;

        if (categoryCheckboxes.length === 0) {
            alert('En az bir kategori seçmelisiniz.');
            isValid = false;
        }

        if (parseFloat(price) <= 0 || price === '') {
            alert('Geçerli bir fiyat giriniz.');
            isValid = false;
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
        const progressPercentage = ((this.currentStep -1) / (this.totalSteps - 1)) * 100;
        this.progressBar.style.width = `${progressPercentage}%`;
    }
}