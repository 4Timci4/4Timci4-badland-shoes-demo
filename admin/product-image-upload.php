<?php
/**
 * Ürün Resim Yükleme Sayfası
 */

require_once 'config/auth.php';
check_admin_auth();

require_once '../config/database.php';
require_once '../services/Product/ProductImageService.php';
require_once '../services/ProductService.php';
require_once '../services/CategoryService.php';

$pageTitle = 'Ürün Resim Yönetimi';
$productImageService = productImageService();
$productService = product_service();

// Ürün ID'si kontrolü
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Ürün bilgilerini getir
$product = $productService->getProductModel($product_id);
if (empty($product)) {
    header('Location: products.php');
    exit;
}

// Renkleri getir
$colors = get_colors();

// Mevcut resimleri getir
$product_images = $productImageService->getProductImagesByColors($product_id);

// POST işlemleri
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error_message = 'Güvenlik hatası oluştu.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'upload':
                $color_id = !empty($_POST['color_id']) ? intval($_POST['color_id']) : null;
                
                if (!empty($_FILES['images']['name'][0])) {
                    $result = $productImageService->uploadProductImages($product_id, $color_id, $_FILES['images']);
                    
                    if ($result['success']) {
                        $success_message = "Başarıyla {$result['uploaded_count']} resim yüklendi.";
                        if (!empty($result['errors'])) {
                            $error_message = implode('<br>', $result['errors']);
                        }
                        // Sayfayı yenile
                        header('Location: product-image-upload.php?id=' . $product_id);
                        exit;
                    } else {
                        $error_message = implode('<br>', $result['errors']);
                    }
                } else {
                    $error_message = 'Lütfen yüklenecek resim seçin.';
                }
                break;
                
            case 'set_primary':
                $image_id = intval($_POST['image_id']);
                if ($productImageService->setPrimaryImage($image_id)) {
                    $success_message = 'Ana resim başarıyla ayarlandı.';
                } else {
                    $error_message = 'Ana resim ayarlanırken hata oluştu.';
                }
                break;
                
            case 'delete':
                $image_id = intval($_POST['image_id']);
                if ($productImageService->deleteImage($image_id)) {
                    $success_message = 'Resim başarıyla silindi.';
                } else {
                    $error_message = 'Resim silinirken hata oluştu.';
                }
                break;
                
            case 'reorder':
                $order_data = json_decode($_POST['order_data'], true);
                if ($productImageService->reorderImages($order_data)) {
                    $success_message = 'Resim sıralaması güncellendi.';
                } else {
                    $error_message = 'Sıralama güncellenirken hata oluştu.';
                }
                break;
        }
        
        // Resimleri yeniden getir
        $product_images = $productImageService->getProductImagesByColors($product_id);
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Sayfa başlığı -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">
                    <i class="fas fa-images me-2"></i><?= $pageTitle ?>
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="products.php">Ürünler</a></li>
                        <li class="breadcrumb-item active">Resim Yönetimi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Ürün bilgisi -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">
                            <?= htmlspecialchars($product['name']) ?>
                        </h5>
                        <span class="badge bg-info ms-2">ID: <?= $product['id'] ?></span>
                        <a href="product-edit.php?id=<?= $product['id'] ?>" class="btn btn-outline-secondary btn-sm ms-auto">
                            <i class="fas fa-edit"></i> Ürünü Düzenle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mesajlar -->
    <?php if ($success_message): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Resim yükleme formu -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Yeni Resim Yükle
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="upload-form">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="upload">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Renk Seçimi <small class="text-muted">(Opsiyonel)</small></label>
                                    <select name="color_id" class="form-select">
                                        <option value="">Tüm Renkler</option>
                                        <?php foreach ($colors as $color): ?>
                                            <option value="<?= $color['id'] ?>">
                                                <?= htmlspecialchars($color['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Belirli bir renge ait resimler için renk seçin.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Resimler <span class="text-danger">*</span></label>
                                    <input type="file" name="images[]" class="form-control" multiple accept="image/*" required>
                                    <div class="form-text">
                                        Çoklu resim seçebilirsiniz. Desteklenen formatlar: JPG, PNG, GIF, WebP
                                        <br>Maksimum dosya boyutu: 5MB, Önerilen boyut: 1200x1200px
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div id="image-preview" class="row"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Resimleri Yükle
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mevcut resimler -->
    <?php if (!empty($product_images)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-images me-2"></i>Mevcut Resimler
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Renk tabları -->
                        <ul class="nav nav-tabs" id="colorTabs" role="tablist">
                            <?php $tab_index = 0; ?>
                            <?php foreach ($product_images as $color_id => $images): ?>
                                <?php
                                $color_name = 'Genel';
                                if ($color_id !== 'default') {
                                    foreach ($colors as $color) {
                                        if ($color['id'] == $color_id) {
                                            $color_name = $color['name'];
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $tab_index === 0 ? 'active' : '' ?>" 
                                            id="color-<?= $color_id ?>-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#color-<?= $color_id ?>" 
                                            type="button" role="tab">
                                        <?= htmlspecialchars($color_name) ?>
                                        <span class="badge bg-secondary ms-1"><?= count($images) ?></span>
                                    </button>
                                </li>
                                <?php $tab_index++; ?>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Tab içerikleri -->
                        <div class="tab-content mt-3" id="colorTabsContent">
                            <?php $tab_index = 0; ?>
                            <?php foreach ($product_images as $color_id => $images): ?>
                                <div class="tab-pane fade <?= $tab_index === 0 ? 'show active' : '' ?>" 
                                     id="color-<?= $color_id ?>" 
                                     role="tabpanel">
                                    
                                    <div class="row" id="sortable-<?= $color_id ?>">
                                        <?php foreach ($images as $image): ?>
                                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-image-id="<?= $image['id'] ?>">
                                                <div class="card image-card">
                                                    <div class="position-relative">
                                                        <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                                                             class="card-img-top" 
                                                             style="height: 200px; object-fit: cover;"
                                                             alt="<?= htmlspecialchars($image['alt_text']) ?>">
                                                        
                                                        <?php if ($image['is_primary']): ?>
                                                            <span class="badge bg-warning position-absolute top-0 start-0 m-2">
                                                                <i class="fas fa-star"></i> Ana Resim
                                                            </span>
                                                        <?php endif; ?>
                                                        
                                                        <div class="position-absolute top-0 end-0 m-2">
                                                            <div class="btn-group-vertical">
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-light"
                                                                        onclick="viewImage('<?= htmlspecialchars($image['original_url']) ?>')"
                                                                        title="Büyük Görüntüle">
                                                                    <i class="fas fa-search-plus"></i>
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-light"
                                                                        onclick="downloadImage('<?= htmlspecialchars($image['original_url']) ?>')"
                                                                        title="İndir">
                                                                    <i class="fas fa-download"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="card-body p-2">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <small class="text-muted">
                                                                Sıra: <?= $image['sort_order'] ?>
                                                            </small>
                                                            <small class="text-muted">
                                                                <?= number_format($image['file_size'] / 1024, 1) ?> KB
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="btn-group w-100" role="group">
                                                            <?php if (!$image['is_primary']): ?>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                                    <input type="hidden" name="action" value="set_primary">
                                                                    <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                                                    <button type="submit" class="btn btn-outline-warning btn-sm" title="Ana Resim Yap">
                                                                        <i class="fas fa-star"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                            
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Bu resmi silmek istediğinizden emin misiniz?')">
                                                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Sil">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if (count($images) > 1): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Resimlerin sırasını değiştirmek için sürükleyip bırakın.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php $tab_index++; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Henüz resim yüklenmemiş</h5>
                        <p class="text-muted">Bu ürün için henüz hiç resim yüklenmemiş. Yukarıdaki formu kullanarak resim yükleyebilirsiniz.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Resim görüntüleme modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resim Görüntüle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modal-image" src="" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>

<script>
// Resim önizleme
document.querySelector('input[name="images[]"]').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    Array.from(e.target.files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-lg-2 col-md-3 col-sm-4 col-6 mb-2';
                col.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;">
                        <div class="card-body p-1">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `;
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        }
    });
});

// Resim modal
function viewImage(url) {
    document.getElementById('modal-image').src = url;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

// Resim indirme
function downloadImage(url) {
    const link = document.createElement('a');
    link.href = url;
    link.download = url.split('/').pop();
    link.click();
}

// Sortable (sürükle-bırak)
<?php if (!empty($product_images)): ?>
    <?php foreach (array_keys($product_images) as $color_id): ?>
        new Sortable(document.getElementById('sortable-<?= $color_id ?>'), {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                const items = evt.to.children;
                const orderData = [];
                
                for (let i = 0; i < items.length; i++) {
                    const imageId = items[i].getAttribute('data-image-id');
                    orderData.push({
                        id: parseInt(imageId),
                        sort_order: i + 1
                    });
                }
                
                // AJAX ile sıralamayı güncelle
                const formData = new FormData();
                formData.append('csrf_token', '<?= generate_csrf_token() ?>');
                formData.append('action', 'reorder');
                formData.append('order_data', JSON.stringify(orderData));
                
                fetch('', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    // Sayfa yenilenmeden güncelleme bildirimi
                    // Toast notification burada eklenebilir
                });
            }
        });
    <?php endforeach; ?>
<?php endif; ?>
</script>

<style>
.image-card {
    transition: transform 0.2s;
}

.image-card:hover {
    transform: translateY(-2px);
}

.sortable-ghost {
    opacity: 0.4;
}

.sortable-chosen {
    transform: scale(1.02);
}

.sortable-drag {
    transform: rotate(5deg);
}

#image-preview .card {
    border: 2px dashed #dee2e6;
}
</style>

<?php include 'includes/footer.php'; ?>