<div class="lg:col-span-1 space-y-4">
    <div class="main-image-container relative bg-gray-100 rounded-lg overflow-hidden aspect-square group">
        <?php
        $main_image = null;

        if (!empty($current_images)) {
            foreach ($current_images as $img) {
                if ($img['is_primary']) {
                    $main_image = $img;
                    break;
                }
            }

            if (!$main_image) {
                $main_image = $current_images[0];
            }
        }
        ?>

        <?php if ($main_image): ?>
            <picture id="main-product-picture" class="w-full h-full">
                <?php if (!empty($main_image['webp_url'])): ?>
                    <source srcset="<?php echo htmlspecialchars($main_image['webp_url']); ?>" type="image/webp">
                <?php endif; ?>
                <img id="main-product-image" src="<?php echo htmlspecialchars($main_image['image_url']); ?>"
                    data-original="<?php echo htmlspecialchars($main_image['original_url'] ?? $main_image['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($main_image['alt_text'] ?? $product['name']); ?>"
                    class="w-full h-full object-cover cursor-zoom-in transition-transform duration-300 group-hover:scale-105"
                    loading="eager">
            </picture>

            <button type="button"
                class="absolute top-4 right-4 bg-black bg-opacity-50 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 hover:bg-opacity-70"
                onclick="openImageZoom()" title="Büyütmek için tıklayın">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                </svg>
            </button>
        <?php else: ?>
            <div class="w-full h-full flex items-center justify-center bg-gray-200">
                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($current_images) && count($current_images) > 1): ?>
        <div class="thumbnail-images">
            <div class="grid grid-cols-4 gap-2" id="thumbnail-container">
                <?php foreach ($current_images as $index => $image): ?>
                    <div class="thumbnail-item relative bg-gray-100 rounded-lg overflow-hidden aspect-square cursor-pointer border-2 transition-all duration-200 <?php echo $index === 0 ? 'border-blue-500 border-opacity-100' : 'border-transparent hover:border-gray-300'; ?>"
                        data-image-id="<?php echo $image['id']; ?>"
                        onclick="changeMainImage(<?php echo htmlspecialchars(json_encode($image)); ?>, this)">

                        <picture class="w-full h-full">
                            <?php if (!empty($image['webp_url'])): ?>
                                <source srcset="<?php echo htmlspecialchars($image['webp_url']); ?>" type="image/webp">
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($image['thumbnail_url'] ?? $image['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['name']); ?>"
                                class="w-full h-full object-cover" loading="lazy">
                        </picture>

                        <?php if ($image['is_primary']): ?>
                            <div class="absolute top-1 left-1">
                                <span class="bg-yellow-500 text-white text-xs px-1 py-0.5 rounded">★</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($current_images) > 4): ?>
                <div class="text-center mt-2">
                    <span class="text-sm text-gray-500">
                        <?php echo count($current_images); ?> resim
                    </span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div id="color-image-data" style="display: none;">
        <?php
        $images_by_color = [];
        if (!empty($product['images'])) {
            foreach ($product['images'] as $image) {
                $images_by_color[$image['color_id'] ?? 'default'][] = $image;
            }
        }
        echo htmlspecialchars(json_encode($images_by_color));
        ?>
    </div>
</div>

<div id="image-zoom-modal"
    class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-content-center">
    <div class="relative max-w-screen-lg max-h-screen-lg mx-auto p-4">
        <button type="button" class="absolute top-4 right-4 text-white text-2xl z-10 hover:text-gray-300"
            onclick="closeImageZoom()">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <img id="zoom-image" src="" alt="" class="max-w-full max-h-full object-contain">
    </div>
</div>

<script>
    function changeMainImage(imageData, thumbnailElement) {
        const mainPicture = document.getElementById('main-product-picture');
        const mainImage = document.getElementById('main-product-image');

        const existingSource = mainPicture.querySelector('source[type="image/webp"]');
        if (imageData.webp_url && existingSource) {
            existingSource.srcset = imageData.webp_url;
        } else if (imageData.webp_url && !existingSource) {
            const source = document.createElement('source');
            source.srcset = imageData.webp_url;
            source.type = 'image/webp';
            mainPicture.insertBefore(source, mainImage);
        }

        mainImage.src = imageData.image_url;
        mainImage.setAttribute('data-original', imageData.original_url || imageData.image_url);
        mainImage.alt = imageData.alt_text || '<?php echo htmlspecialchars($product['name']); ?>';

        document.querySelectorAll('.thumbnail-item').forEach(thumb => {
            thumb.classList.remove('border-blue-500', 'border-opacity-100');
            thumb.classList.add('border-transparent');
        });

        if (thumbnailElement) {
            thumbnailElement.classList.add('border-blue-500', 'border-opacity-100');
            thumbnailElement.classList.remove('border-transparent');
        }
    }

    function openImageZoom() {
        const mainImage = document.getElementById('main-product-image');
        const zoomModal = document.getElementById('image-zoom-modal');
        const zoomImage = document.getElementById('zoom-image');

        const originalSrc = mainImage.getAttribute('data-original') || mainImage.src;
        zoomImage.src = originalSrc;
        zoomImage.alt = mainImage.alt;

        zoomModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImageZoom() {
        const zoomModal = document.getElementById('image-zoom-modal');
        zoomModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeImageZoom();
        }
    });

    document.getElementById('image-zoom-modal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeImageZoom();
        }
    });

    function updateImagesForColor(colorId) {
        const colorImageData = JSON.parse(document.getElementById('color-image-data').textContent);
        const colorKey = colorId ? colorId.toString() : 'default';
        const mainImageContainer = document.querySelector('.main-image-container');

        if (mainImageContainer) {
            mainImageContainer.classList.add('loading');
        }

        if (colorImageData[colorKey] && colorImageData[colorKey].length > 0) {
            let colorImages = colorImageData[colorKey];

            colorImages.sort((a, b) => (a.sort_order || 999) - (b.sort_order || 999));

            let mainImageData = colorImages.find(img => img.is_primary) || colorImages[0];

            updateThumbnails(colorImages);

            changeMainImage(mainImageData);

            setTimeout(() => {
                if (mainImageContainer) {
                    mainImageContainer.classList.remove('loading');
                }
            }, 300);
        } else {
            setTimeout(() => {
                if (mainImageContainer) {
                    mainImageContainer.classList.remove('loading');
                }
            }, 100);
        }
    }

    function updateThumbnails(images) {
        const container = document.getElementById('thumbnail-container');
        if (!container) return;

        container.innerHTML = '';

        images.forEach((image, index) => {
            const thumbnailDiv = document.createElement('div');
            thumbnailDiv.className = `thumbnail-item relative bg-gray-100 rounded-lg overflow-hidden aspect-square cursor-pointer border-2 transition-all duration-200 ${index === 0 ? 'border-blue-500 border-opacity-100' : 'border-transparent hover:border-gray-300'}`;
            thumbnailDiv.setAttribute('data-image-id', image.id);
            thumbnailDiv.onclick = () => changeMainImage(image, thumbnailDiv);

            let pictureHTML = '<picture class="w-full h-full">';
            if (image.webp_url) {
                pictureHTML += `<source srcset="${image.webp_url}" type="image/webp">`;
            }
            pictureHTML += `<img src="${image.thumbnail_url || image.image_url}" alt="${image.alt_text || '<?php echo htmlspecialchars($product['name']); ?>'}" class="w-full h-full object-cover" loading="lazy">`;
            pictureHTML += '</picture>';

            if (image.is_primary) {
                pictureHTML += '<div class="absolute top-1 left-1"><span class="bg-yellow-500 text-white text-xs px-1 py-0.5 rounded">★</span></div>';
            }

            thumbnailDiv.innerHTML = pictureHTML;
            container.appendChild(thumbnailDiv);
        });
    }

    window.updateImagesForColor = updateImagesForColor;
</script>