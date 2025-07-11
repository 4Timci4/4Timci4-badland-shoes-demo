// Tab sistemi işlevselliği
export function initializeTabSystem() {
    // Tab butonlarına event listener ekle
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Tüm tab butonlarını pasif yap
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Aktif tab butonunu işaretle
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-primary', 'text-primary');
            
            // Tüm tab içeriklerini gizle
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });
            
            // Seçili tab içeriğini göster
            const selectedPane = document.getElementById(tabId);
            if (selectedPane) {
                selectedPane.classList.remove('hidden');
            }
        });
    });
    
    // Public API
    return {
        activateTab: (tabId) => {
            const tabButton = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
            if (tabButton) {
                tabButton.click();
                return true;
            }
            return false;
        }
    };
}