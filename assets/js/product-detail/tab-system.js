
export function initializeTabSystem() {
    
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-primary', 'text-primary');
            
            
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });
            
            
            const selectedPane = document.getElementById(tabId);
            if (selectedPane) {
                selectedPane.classList.remove('hidden');
            }
        });
    });
    
    
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