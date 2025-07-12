<?php
/**
 * Admin Panel - E-posta Şablonları Yönetimi
 */

require_once 'config/auth.php';
check_admin_auth();

require_once '../config/database.php';
require_once 'includes/product-edit-helpers.php'; // Helper dosyasını dahil et

// Sayfa bilgileri
$page_title = 'E-posta Şablonları';
$breadcrumb_items = [
    ['title' => 'E-Posta Yönetimi', 'url' => '#', 'icon' => 'fas fa-at'],
    ['title' => 'E-posta Şablonları', 'url' => 'email-templates.php', 'icon' => 'fas fa-file-alt']
];

// POST işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $template_id = intval($_POST['template_id']);
        $subject = $_POST['subject'];
        $body_html = $_POST['body_html'];
        $body_text = $_POST['body_text'];

        try {
            database()->update('email_templates', 
                ['subject' => $subject, 'body_html' => $body_html, 'body_text' => $body_text],
                ['id' => $template_id]
            );
            set_flash_message('success', 'E-posta şablonu başarıyla güncellendi.');
        } catch (Exception $e) {
            set_flash_message('error', 'Şablon güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
        header('Location: email-templates.php?edit=' . $template_id);
        exit;
    }
}

// Verileri getir
try {
    $templates = database()->select('email_templates');
} catch (Exception $e) {
    $templates = [];
    set_flash_message('error', 'Şablonlar yüklenirken bir hata oluştu: ' . $e->getMessage());
}

$edit_mode = isset($_GET['edit']);
$edit_template = null;
if ($edit_mode) {
    $edit_id = intval($_GET['edit']);
    foreach ($templates as $template) {
        if ($template['id'] == $edit_id) {
            $edit_template = $template;
            break;
        }
    }
}

$additional_js = [
    'https://cdn.tiny.cloud/1/80hxwwgls44a8ss05y32w3qn40riarmgyqdglc7dagch0gna/tinymce/6/tinymce.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js'
];
$additional_css = [
    'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css'
];
include 'includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">E-posta Şablonları</h1>
        <p class="text-gray-600">Sistem tarafından gönderilen otomatik e-postaların içeriklerini düzenleyin.</p>
    </div>

    <?php render_flash_message(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Templates List -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                    <div class="p-4 border-b">
                        <h3 class="font-semibold text-lg"><i class="fas fa-stream mr-2"></i>Şablonlar</h3>
                    </div>
                    <div class="p-2 space-y-1">
                        <?php foreach ($templates as $template): ?>
                            <a href="?edit=<?= $template['id'] ?>"
                               class="flex items-center space-x-3 p-3 rounded-lg transition-colors <?= ($edit_mode && $edit_template['id'] == $template['id']) ? 'bg-primary-50 text-primary-700' : 'hover:bg-gray-100' ?>">
                                <i class="fas fa-file-alt text-gray-400"></i>
                                <div>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($template['description']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($template['name']) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
    
            <!-- Template Editor -->
            <div class="lg:col-span-3">
            <?php if ($edit_mode && $edit_template): ?>
                <form method="POST" class="bg-white rounded-2xl shadow-lg border border-gray-100">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="template_id" value="<?= $edit_template['id'] ?>">
                    
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-bold">"<?= htmlspecialchars($edit_template['description']) ?>" Şablonunu Düzenle</h3>
                    </div>

                    <div class="p-6">
                        <!-- Sekme Navigasyonu -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button type="button" class="tab-btn active py-2 px-1 border-b-2 border-primary-500 font-medium text-sm text-primary-600" data-tab="content">
                                    <i class="fas fa-edit mr-2"></i>İçerik Düzenle
                                </button>
                                <button type="button" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="preview">
                                    <i class="fas fa-eye mr-2"></i>Önizleme
                                </button>
                                <button type="button" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="variables">
                                    <i class="fas fa-code mr-2"></i>Değişkenler
                                </button>
                            </nav>
                        </div>

                        <!-- İçerik Düzenleme Sekmesi -->
                        <div id="content-tab" class="tab-content active space-y-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-envelope mr-1"></i>E-posta Konusu
                                    </label>
                                    <input type="text" name="subject" value="<?= htmlspecialchars($edit_template['subject']) ?>"
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                           placeholder="E-posta konusunu girin...">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>Şablon Tipi
                                    </label>
                                    <div class="p-3 bg-gray-50 rounded-lg border">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($edit_template['description']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-sm font-medium text-gray-700">
                                        <i class="fas fa-code mr-1"></i>HTML İçerik
                                    </label>
                                    <div class="flex space-x-2">
                                        <button type="button" id="insert-variables-btn" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                            <i class="fas fa-plus-circle mr-1"></i>Değişken Ekle
                                        </button>
                                        <button type="button" id="format-html-btn" class="text-sm text-gray-600 hover:text-gray-700 font-medium">
                                            <i class="fas fa-magic mr-1"></i>Formatla
                                        </button>
                                    </div>
                                </div>
                                <div class="border border-gray-300 rounded-lg overflow-hidden">
                                    <textarea id="html-editor" name="body_html" class="w-full"><?= htmlspecialchars($edit_template['body_html']) ?></textarea>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    <i class="fas fa-file-text mr-1"></i>Düz Metin İçerik
                                    <span class="text-xs text-gray-500 ml-2">(E-posta istemcileri HTML desteklemediğinde kullanılır)</span>
                                </label>
                                <textarea name="body_text" id="body_text" rows="8"
                                          class="w-full p-3 border border-gray-300 rounded-lg font-mono text-sm bg-gray-50"
                                          readonly><?= htmlspecialchars($edit_template['body_text']) ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Bu alan, HTML içerikten otomatik olarak oluşturulur.</p>
                            </div>
                        </div>

                        <!-- Önizleme Sekmesi -->
                        <div id="preview-tab" class="tab-content hidden">
                            <div class="bg-gray-50 rounded-lg border p-6">
                                <div class="bg-white rounded-lg shadow-lg max-w-2xl mx-auto">
                                    <div class="border-b border-gray-200 p-4 bg-gray-50 rounded-t-lg">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                            <span class="text-sm text-gray-600 ml-4">E-posta Önizlemesi</span>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <div class="mb-4 pb-4 border-b">
                                            <h3 class="font-semibold text-lg text-gray-800">Konu: <span id="preview-subject"></span></h3>
                                        </div>
                                        <div id="preview-content" class="prose max-w-none">
                                            <!-- İçerik buraya gelecek -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                       <!-- Değişkenler Sekmesi -->
                       <div id="variables-tab" class="tab-content hidden">
                           <div class="space-y-4">
                               <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                   <h4 class="font-semibold text-blue-900 mb-3"><i class="fas fa-user mr-2"></i>Kullanıcı Değişkenleri</h4>
                                   <p class="text-sm text-blue-800 mb-4">Bu değişkenler, e-postanın gönderildiği kullanıcıya göre dinamik olarak değişir.</p>
                                   <div class="space-y-2">
                                       <button type="button" class="variable-btn" data-variable="{{fullName}}"><code>{{fullName}}</code> - Kullanıcının tam adı</button>
                                   </div>
                               </div>
                               <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                   <h4 class="font-semibold text-green-900 mb-3"><i class="fas fa-globe mr-2"></i>Site Değişkenleri</h4>
                                   <p class="text-sm text-green-800 mb-4">Bu değişkenler, sitenizin genel ayarlarından alınır.</p>
                                   <div class="space-y-2">
                                       <button type="button" class="variable-btn" data-variable="{{site_url}}"><code>{{site_url}}</code> - Site ana URL'si</button>
                                       <button type="button" class="variable-btn" data-variable="{{current_year}}"><code>{{current_year}}</code> - Güncel yıl</button>
                                       <button type="button" class="variable-btn" data-variable="{{contact_url}}"><code>{{contact_url}}</code> - İletişim sayfası URL'si</button>
                                   </div>
                               </div>
                           </div>
                       </div>
                    </div>

                    <div class="p-6 bg-gray-50 border-t">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <button type="button" id="test-email-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="fas fa-paper-plane mr-2"></i>Test E-postası Gönder
                                </button>
                                <span class="text-sm text-gray-500">Son kayıt: <span id="last-saved">-</span></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <button type="button" id="save-draft-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="fas fa-save mr-2"></i>Taslak Kaydet
                                </button>
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                    <i class="fas fa-check mr-2"></i>Şablonu Kaydet
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center">
                    <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">Lütfen düzenlemek için bir şablon seçin.</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    tinymce.init({
        selector: '#html-editor',
        plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
        menubar: 'file edit view insert format tools table help',
        toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
        autosave_ask_before_unload: true,
        autosave_interval: '30s',
        autosave_prefix: '{path}{query}-{id}-',
        autosave_restore_when_empty: false,
        autosave_retention: '2m',
        height: 600,
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
        noneditable_noneditable_class: 'mceNonEditable',
        toolbar_mode: 'sliding',
        contextmenu: 'link image table',
        init_instance_callback: function (editor) {
            // Editör hazır olduğunda düz metin alanını doldur
            setTimeout(function() {
                updatePlainText();
                updatePreview();
            }, 500);
        }
    });
});
</script>
<style>
.variable-btn {
    background-color: #e0e7ff;
    color: #4338ca;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
    cursor: pointer;
    border: 1px solid #c7d2fe;
    transition: all 0.2s;
}
.variable-btn:hover {
    background-color: #c7d2fe;
    transform: translateY(-1px);
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tab functionality
    const tabs = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(item => {
                item.classList.remove('active', 'border-primary-500', 'text-primary-600');
                item.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            tab.classList.add('active', 'border-primary-500', 'text-primary-600');
            tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            
            const target = document.getElementById(tab.dataset.tab + '-tab');
            tabContents.forEach(content => content.classList.add('hidden'));
            target.classList.remove('hidden');

            if (tab.dataset.tab === 'preview') {
                updatePreview();
            }
        });
    });

    // Variable buttons
    document.querySelectorAll('.variable-btn').forEach(button => {
        button.addEventListener('click', function() {
            const variable = this.getAttribute('data-variable');
            if (tinymce.get('html-editor')) {
                tinymce.get('html-editor').insertContent(variable);
                updatePlainText(); // Değişken eklendikten sonra düz metni güncelle
            }
        });
    });

    // Fonksiyonları global hale getir
    window.updatePreview = function() {
        if (!tinymce.get('html-editor')) return;
        
        let editorContent = tinymce.get('html-editor').getContent();
        let subjectContent = document.querySelector('input[name="subject"]').value;
        
        // Replace placeholders for preview
        let previewHtml = editorContent.replace(/{{fullName}}/g, 'Ahmet Yılmaz')
                                       .replace(/{{site_url}}/g, '<?= (APP_ENV === 'development' ? 'http://localhost' : 'https://badlandshoes.com.tr') ?>')
                                       .replace(/{{current_year}}/g, new Date().getFullYear());
        
        let previewSubject = subjectContent.replace(/{{fullName}}/g, 'Ahmet Yılmaz');

        let previewFrame = document.getElementById('preview-content');
        if (previewFrame) {
            previewFrame.innerHTML = previewHtml;
        }
        
        let previewSubjectEl = document.getElementById('preview-subject');
        if (previewSubjectEl) {
            previewSubjectEl.innerText = previewSubject;
        }
    }

    window.updatePlainText = function() {
        if (!tinymce.get('html-editor')) return;
        
        let editorContent = tinymce.get('html-editor').getContent({format: 'text'});
        let bodyTextEl = document.getElementById('body_text');
        if (bodyTextEl) {
            bodyTextEl.value = editorContent;
        }
    }

    // TinyMCE editor olaylarını dinle
    function setupEditorEvents() {
        const editor = tinymce.get('html-editor');
        if (editor) {
            editor.on('keyup change', function() {
                updatePreview();
                updatePlainText();
            });
        }
    }

    // Editör hazır olup olmadığını kontrol et ve eventları bağla
    function checkEditorReady() {
        if (tinymce.get('html-editor')) {
            setupEditorEvents();
            // İlk yüklemede düz metin ve önizlemeyi güncelle
            updatePlainText();
            updatePreview();
        } else {
            // Editör henüz hazır değilse 100ms sonra tekrar dene
            setTimeout(checkEditorReady, 100);
        }
    }

    // Editör kontrol işlemini başlat
    checkEditorReady();

    // Test E-postası Gönderme
    document.getElementById('test-email-btn')?.addEventListener('click', function() {
        const toEmail = prompt('Test e-postasını göndermek istediğiniz adresi girin:', 'test@example.com');
        if (toEmail) {
            sendAjaxRequest('send_test_email', { to_email: toEmail });
        }
    });

    // Taslak Kaydetme
    document.getElementById('save-draft-btn')?.addEventListener('click', function() {
        sendAjaxRequest('save_draft');
    });

    function sendAjaxRequest(action, additionalData = {}) {
        const form = document.querySelector('form');
        const formData = new FormData(form);
        formData.append('action', action);
        formData.append('body_html', tinymce.get('html-editor').getContent());
        formData.append('body_text', document.getElementById('body_text').value);

        for (const key in additionalData) {
            formData.append(key, additionalData[key]);
        }

        fetch('ajax/email_template_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                if (data.last_saved) {
                    document.getElementById('last-saved').innerText = data.last_saved;
                }
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            alert('İstek gönderilirken bir hata oluştu.');
        });
    }
});
</script>
<?php include 'includes/footer.php'; ?>