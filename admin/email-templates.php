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

$additional_js = ['https://cdn.tiny.cloud/1/80hxwwgls44a8ss05y32w3qn40riarmgyqdglc7dagch0gna/tinymce/6/tinymce.min.js'];
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

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">E-posta Konusu</label>
                            <input type="text" name="subject" value="<?= htmlspecialchars($edit_template['subject']) ?>" class="w-full p-2 border rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">HTML İçerik</label>
                            <textarea id="html-editor" name="body_html" rows="15" class="w-full p-2 border rounded-md font-mono text-sm"><?= htmlspecialchars($edit_template['body_html']) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Düz Metin İçerik</label>
                            <textarea name="body_text" rows="8" class="w-full p-2 border rounded-md font-mono text-sm"><?= htmlspecialchars($edit_template['body_text']) ?></textarea>
                        </div>
                        <div class="bg-gray-100 p-3 rounded-md">
                            <p class="text-sm text-gray-600 mb-2"><strong>Kullanılabilir Değişkenler:</strong></p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="variable-btn" data-variable="{{fullName}}">fullName</button>
                                <button type="button" class="variable-btn" data-variable="{{site_url}}">site_url</button>
                                <button type="button" class="variable-btn" data-variable="{{current_year}}">current_year</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 border-t">
                        <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium">Şablonu Kaydet</button>
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
    document.querySelectorAll('.variable-btn').forEach(button => {
        button.addEventListener('click', function() {
            const variable = this.getAttribute('data-variable');
            tinymce.get('html-editor').insertContent(variable);
        });
    });
});
</script>
<?php include 'includes/footer.php'; ?>