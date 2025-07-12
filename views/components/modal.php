<div id="modal-container" class="fixed inset-0 z-50 hidden overflow-y-auto">
  <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
    <div id="modal-backdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>

    <div id="modal-content"
      class="inline-block transform overflow-hidden rounded-lg bg-white p-5 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
      <div class="mb-4 flex items-center justify-between">
        <h3 id="modal-title" class="text-lg font-medium leading-6 text-gray-900"></h3>
        <button id="modal-close" type="button"
          class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
          <span class="sr-only">Kapat</span>
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div id="modal-body" class="mt-2">
        <p class="text-sm text-gray-500"></p>
      </div>

      <div id="modal-footer" class="mt-5 flex justify-end space-x-2">
      </div>
    </div>
  </div>
</div>

<script>
  const modal = {
    container: document.getElementById('modal-container'),
    backdrop: document.getElementById('modal-backdrop'),
    content: document.getElementById('modal-content'),
    title: document.getElementById('modal-title'),
    body: document.getElementById('modal-body').querySelector('p'),
    footer: document.getElementById('modal-footer'),
    closeBtn: document.getElementById('modal-close'),

    open: function (options) {
      const defaults = {
        title: '',
        message: '',
        icon: null, // 'success', 'error', 'warning', 'info'
        buttons: [],
        onClose: null
      };

      const settings = { ...defaults, ...options };

      this.title.textContent = settings.title;
      this.body.textContent = settings.message;

      if (settings.icon) {
        const iconHTML = this.getIconHTML(settings.icon);
        this.body.innerHTML = `<div class="flex items-center mb-2">
          ${iconHTML}
          <span class="ml-2">${settings.message}</span>
        </div>`;
      }

      // Footer'ı temizle
      this.footer.innerHTML = '';

      // Butonları ekle
      settings.buttons.forEach(button => {
        const btn = document.createElement('button');
        btn.textContent = button.text;
        btn.className = button.class || 'px-4 py-2 text-sm font-medium rounded-md';

        if (button.primary) {
          btn.className += ' bg-primary text-white hover:bg-primary-dark';
        } else {
          btn.className += ' bg-white border border-gray-300 text-gray-700 hover:bg-gray-50';
        }

        if (button.onClick) {
          btn.addEventListener('click', () => {
            button.onClick();
            if (button.closeOnClick !== false) {
              this.close();
            }
          });
        } else {
          btn.addEventListener('click', () => this.close());
        }

        this.footer.appendChild(btn);
      });

      // Kapatma olayı
      const closeModal = () => {
        this.close();
        if (settings.onClose) settings.onClose();
      };

      this.closeBtn.addEventListener('click', closeModal);
      this.backdrop.addEventListener('click', closeModal);

      // Animasyon sınıfları
      this.container.classList.remove('hidden');
      this.backdrop.classList.add('opacity-0');
      this.content.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

      setTimeout(() => {
        this.backdrop.classList.remove('opacity-0');
        this.content.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        this.content.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
      }, 10);

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
      });
    },

    close: function () {
      this.backdrop.classList.add('opacity-0');
      this.content.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
      this.content.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

      setTimeout(() => {
        this.container.classList.add('hidden');
      }, 300);
    },

    getIconHTML: function (type) {
      const iconClass = {
        success: 'text-green-500',
        error: 'text-red-500',
        warning: 'text-yellow-500',
        info: 'text-blue-500'
      };

      const iconSVG = {
        success: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        error: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
        warning: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
        info: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
      };

      return `<span class="${iconClass[type]}">${iconSVG[type]}</span>`;
    },

    alert: function (message, title = 'Bilgi', icon = 'info') {
      this.open({
        title: title,
        message: message,
        icon: icon,
        buttons: [
          { text: 'Tamam', primary: true }
        ]
      });
    },

    confirm: function (message, callback, title = 'Onay', icon = 'warning') {
      this.open({
        title: title,
        message: message,
        icon: icon,
        buttons: [
          {
            text: 'İptal',
            primary: false,
            onClick: () => callback(false)
          },
          {
            text: 'Onayla',
            primary: true,
            onClick: () => callback(true)
          }
        ]
      });
    },

    success: function (message, title = 'Başarılı') {
      this.alert(message, title, 'success');
    },

    error: function (message, title = 'Hata') {
      this.alert(message, title, 'error');
    }
  };

  window.modal = modal;
</script>